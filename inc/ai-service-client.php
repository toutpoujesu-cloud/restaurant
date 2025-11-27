<?php
/**
 * AI Service Integration
 * Connects WordPress to the professional Python LLamaIndex service
 * Provides natural language responses with document context
 */

if (!defined('ABSPATH')) {
    exit;
}

class RestaurantAIServiceClient {
    private $service_url;
    private $timeout = 30;

    public function __construct() {
        $this->service_url = rtrim(get_option('ai_service_url', 'http://localhost:8000'), '/');
    }

    /**
     * Health check - verify service is running
     */
    public function check_health() {
        $response = wp_remote_get(
            "{$this->service_url}/health",
            ['timeout' => 5]
        );

        if (is_wp_error($response)) {
            return [
                'healthy' => false,
                'error' => $response->get_error_message()
            ];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    /**
     * Create a new knowledge base category
     */
    public function create_category($name, $description = '', $icon = '📁') {
        $response = wp_remote_post(
            "{$this->service_url}/categories/create",
            [
                'timeout' => $this->timeout,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'name' => $name,
                    'description' => $description,
                    'icon' => $icon
                ])
            ]
        );

        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    /**
     * Upload document to category
     */
    public function upload_document($file_path, $category) {
        if (!file_exists($file_path)) {
            return ['success' => false, 'error' => 'File not found'];
        }

        // Use cURL for multipart file upload
        $ch = curl_init();
        $file_handle = fopen($file_path, 'r');
        $file_data = fread($file_handle, filesize($file_path));
        fclose($file_handle);

        $boundary = '----WebKitFormBoundary' . uniqid();
        $body = '';
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"category\"\r\n\r\n";
        $body .= "{$category}\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"" . basename($file_path) . "\"\r\n";
        $body .= "Content-Type: application/octet-stream\r\n\r\n";
        $body .= $file_data . "\r\n";
        $body .= "--{$boundary}--\r\n";

        curl_setopt_array($ch, [
            CURLOPT_URL => "{$this->service_url}/documents/upload",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                "Content-Type: multipart/form-data; boundary={$boundary}"
            ]
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($response, true);
        }

        return ['success' => false, 'error' => 'Upload failed', 'status' => $http_code];
    }

    /**
     * Perform semantic search across documents
     */
    public function semantic_search($query, $category, $top_k = 5) {
        $response = wp_remote_post(
            "{$this->service_url}/search/semantic",
            [
                'timeout' => $this->timeout,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'query' => $query,
                    'category' => $category,
                    'top_k' => $top_k
                ])
            ]
        );

        if (is_wp_error($response)) {
            return null;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    /**
     * Get natural language response with RAG context
     * This is the main method that produces human-like responses
     */
    public function chat_with_context($message, $category, $conversation_history = []) {
        $response = wp_remote_post(
            "{$this->service_url}/chat",
            [
                'timeout' => $this->timeout,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'message' => $message,
                    'category' => $category,
                    'conversation_history' => $conversation_history
                ])
            ]
        );

        if (is_wp_error($response)) {
            return [
                'response' => "Sorry, I'm having trouble connecting to the AI service right now. Please try again in a moment.",
                'sources' => [],
                'confidence' => 0
            ];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    /**
     * Delete a document from a category
     */
    public function delete_document($category, $filename) {
        $response = wp_remote_request(
            "{$this->service_url}/documents/{$category}/" . urlencode($filename),
            [
                'method' => 'DELETE',
                'timeout' => $this->timeout,
                'headers' => ['Content-Type' => 'application/json']
            ]
        );

        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    /**
     * Get category statistics
     */
    public function get_category_stats($category) {
        $response = wp_remote_get(
            "{$this->service_url}/categories/{$category}/stats",
            ['timeout' => $this->timeout]
        );

        if (is_wp_error($response)) {
            return null;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}

// Instantiate globally for easy access
global $restaurant_ai_service;
$restaurant_ai_service = new RestaurantAIServiceClient();

/**
 * AJAX handler - Check service health
 */
add_action('wp_ajax_check_ai_service', function() {
    global $restaurant_ai_service;
    
    $health = $restaurant_ai_service->check_health();
    
    if ($health['healthy'] ?? false) {
        wp_send_json_success($health);
    } else {
        wp_send_json_error(['message' => 'AI Service is not responding']);
    }
});

/**
 * AJAX handler - Get natural language response
 * This powers the public chat widget with human-like responses
 */
add_action('wp_ajax_ai_chat_message', function() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_chat_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    $message = sanitize_text_field($_POST['message'] ?? '');
    $category = sanitize_text_field($_POST['category'] ?? 'default');
    $conversation = isset($_POST['conversation']) ? json_decode($_POST['conversation'], true) : [];

    if (empty($message)) {
        wp_send_json_error(['message' => 'Message cannot be empty']);
    }

    global $restaurant_ai_service;
    $response = $restaurant_ai_service->chat_with_context($message, $category, $conversation);

    wp_send_json_success($response);
});

/**
 * Get AI response (non-AJAX, for direct PHP calls)
 */
function restaurant_get_ai_response($message, $category = 'default', $conversation = []) {
    global $restaurant_ai_service;
    return $restaurant_ai_service->chat_with_context($message, $category, $conversation);
}
