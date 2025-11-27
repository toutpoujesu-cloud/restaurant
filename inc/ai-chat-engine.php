<?php
/**
 * AI Chat Engine
 * 
 * Core AI functionality:
 * - Multi-provider support (OpenAI, Claude, Gemini)
 * - Function calling framework
 * - Menu integration
 * - Conversation management
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class UCFC_AI_Engine {
    
    private $provider;
    private $model;
    private $api_key;
    private $temperature;
    private $max_tokens;
    private $system_prompt;
    
    public function __construct() {
        $this->load_configuration();
    }
    
    /**
     * Load AI configuration from options
     */
    private function load_configuration() {
        $this->provider = get_option('ucfc_ai_model_provider', 'openai');
        $this->model = get_option('ucfc_ai_model_version', 'gpt-4-turbo');
        $this->temperature = floatval(get_option('ucfc_ai_temperature', 0.7));
        $this->max_tokens = intval(get_option('ucfc_ai_max_tokens', 1000));
        
        // Get API key based on provider
        switch ($this->provider) {
            case 'openai':
                $this->api_key = get_option('ucfc_ai_openai_key');
                break;
            case 'claude':
                $this->api_key = get_option('ucfc_ai_claude_key');
                break;
            case 'gemini':
                $this->api_key = get_option('ucfc_ai_gemini_key');
                break;
        }
        
        // Build system prompt
        $this->system_prompt = $this->build_system_prompt();
    }
    
    /**
     * Build system prompt with restaurant context
     */
    private function build_system_prompt() {
        $prompt = get_option('ucfc_ai_system_prompt');
        
        if (empty($prompt)) {
            $restaurant_name = get_option('ucfc_restaurant_name', get_bloginfo('name'));
            $prompt = "You are a helpful AI assistant for {$restaurant_name}.";
        }
        
        // Add restaurant context
        $context = $this->get_restaurant_context();
        $prompt .= "\n\n" . $context;
        
        // Add function descriptions
        if (get_option('ucfc_ai_enable_menu_search', '1') === '1') {
            $prompt .= "\n\nYou can search the menu using the get_menu_items function.";
        }
        
        if (get_option('ucfc_ai_enable_order_placement', '1') === '1') {
            $prompt .= "\n\nYou can add items to the customer's cart using add_to_cart.";
        }
        
        return $prompt;
    }
    
    /**
     * Get restaurant context (menu, hours, specials)
     */
    private function get_restaurant_context() {
        $context = "RESTAURANT INFORMATION:\n";
        
        // Basic info
        $restaurant_name = get_option('ucfc_restaurant_name', get_bloginfo('name'));
        $phone = get_option('ucfc_phone_number', '');
        $email = get_option('ucfc_email_address', '');
        
        $context .= "Name: {$restaurant_name}\n";
        if ($phone) $context .= "Phone: {$phone}\n";
        if ($email) $context .= "Email: {$email}\n";
        
        // Menu categories
        $categories = get_terms(array(
            'taxonomy' => 'menu_category',
            'hide_empty' => false
        ));
        
        if (!empty($categories) && !is_wp_error($categories)) {
            $context .= "\nMENU CATEGORIES:\n";
            foreach ($categories as $category) {
                if (is_object($category) && isset($category->name)) {
                    $context .= "- {$category->name}\n";
                }
            }
        }
        
        // Sample menu items (top 5 popular)
        $menu_items = get_posts(array(
            'post_type' => 'menu_item',
            'posts_per_page' => 5,
            'meta_key' => '_menu_item_is_popular',
            'meta_value' => '1'
        ));
        
        if (!empty($menu_items)) {
            $context .= "\nPOPULAR ITEMS:\n";
            foreach ($menu_items as $item) {
                $price = get_post_meta($item->ID, '_menu_item_price', true);
                $context .= "- {$item->post_title}" . ($price ? " (\${$price})" : "") . "\n";
            }
        }
        
        // Current specials
        $specials = get_posts(array(
            'post_type' => 'special_offer',
            'posts_per_page' => 3
        ));
        
        if (!empty($specials)) {
            $context .= "\nCURRENT SPECIALS:\n";
            foreach ($specials as $special) {
                $code = get_post_meta($special->ID, '_offer_code', true);
                $context .= "- {$special->post_title}" . ($code ? " (Code: {$code})" : "") . "\n";
            }
        }
        
        return $context;
    }
    
    /**
     * Send message to AI and get response
     */
    public function chat($message, $conversation_history = array()) {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => 'API key not configured'
            );
        }
        
        // Check cache first
        global $ucfc_ai_cache;
        if ($ucfc_ai_cache) {
            $cached = $ucfc_ai_cache->get($message, $this->provider, $this->model);
            if ($cached !== false) {
                return array(
                    'success' => true,
                    'reply' => $cached['response']['reply'],
                    'usage' => isset($cached['response']['usage']) ? $cached['response']['usage'] : array(),
                    'cached' => true
                );
            }
        }
        
        // Call AI provider
        switch ($this->provider) {
            case 'openai':
                $result = $this->chat_openai($message, $conversation_history);
                break;
            case 'claude':
                $result = $this->chat_claude($message, $conversation_history);
                break;
            case 'gemini':
                $result = $this->chat_gemini($message, $conversation_history);
                break;
            default:
                return array('success' => false, 'message' => 'Invalid provider');
        }
        
        // Cache successful responses
        if ($result['success'] && $ucfc_ai_cache) {
            $ucfc_ai_cache->set($message, $this->provider, $this->model, $result);
        }
        
        return $result;
    }
    
    /**
     * Chat with OpenAI
     */
    private function chat_openai($message, $history) {
        $messages = array();
        
        // Add system message
        $messages[] = array(
            'role' => 'system',
            'content' => $this->system_prompt
        );
        
        // Add conversation history
        foreach ($history as $msg) {
            $messages[] = array(
                'role' => $msg['role'],
                'content' => $msg['content']
            );
        }
        
        // Add current message
        $messages[] = array(
            'role' => 'user',
            'content' => $message
        );
        
        // Prepare functions/tools
        $tools = $this->get_openai_tools();
        
        $body = array(
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_tokens' => $this->max_tokens
        );
        
        if (!empty($tools)) {
            $body['tools'] = $tools;
            $body['tool_choice'] = 'auto';
        }
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body)
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return array(
                'success' => false,
                'message' => $body['error']['message']
            );
        }
        
        $reply = $body['choices'][0]['message']['content'];
        
        // Handle function calls
        if (isset($body['choices'][0]['message']['tool_calls'])) {
            $tool_calls = $body['choices'][0]['message']['tool_calls'];
            $reply = $this->handle_function_calls($tool_calls, $reply);
        }
        
        return array(
            'success' => true,
            'reply' => $reply,
            'usage' => $body['usage']
        );
    }
    
    /**
     * Chat with Claude
     */
    private function chat_claude($message, $history) {
        $messages = array();
        
        // Add conversation history
        foreach ($history as $msg) {
            $messages[] = array(
                'role' => $msg['role'],
                'content' => $msg['content']
            );
        }
        
        // Add current message
        $messages[] = array(
            'role' => 'user',
            'content' => $message
        );
        
        $body = array(
            'model' => $this->model,
            'messages' => $messages,
            'system' => $this->system_prompt,
            'temperature' => $this->temperature,
            'max_tokens' => $this->max_tokens
        );
        
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', array(
            'timeout' => 30,
            'headers' => array(
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body)
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return array(
                'success' => false,
                'message' => $body['error']['message']
            );
        }
        
        $reply = $body['content'][0]['text'];
        
        return array(
            'success' => true,
            'reply' => $reply,
            'usage' => $body['usage']
        );
    }
    
    /**
     * Chat with Gemini
     */
    private function chat_gemini($message, $history) {
        $contents = array();
        
        // Add conversation history
        foreach ($history as $msg) {
            $role = $msg['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = array(
                'role' => $role,
                'parts' => array(array('text' => $msg['content']))
            );
        }
        
        // Add current message
        $contents[] = array(
            'role' => 'user',
            'parts' => array(array('text' => $this->system_prompt . "\n\n" . $message))
        );
        
        $body = array(
            'contents' => $contents,
            'generationConfig' => array(
                'temperature' => $this->temperature,
                'maxOutputTokens' => $this->max_tokens
            )
        );
        
        $url = "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent?key={$this->api_key}";
        
        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body)
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return array(
                'success' => false,
                'message' => $body['error']['message']
            );
        }
        
        $reply = $body['candidates'][0]['content']['parts'][0]['text'];
        
        return array(
            'success' => true,
            'reply' => $reply
        );
    }
    
    /**
     * Get OpenAI tools/functions definition
     */
    private function get_openai_tools() {
        $tools = array();
        
        // Menu search function
        if (get_option('ucfc_ai_enable_menu_search', '1') === '1') {
            $tools[] = array(
                'type' => 'function',
                'function' => array(
                    'name' => 'get_menu_items',
                    'description' => 'Search and retrieve menu items by category, dietary restrictions, or keywords',
                    'parameters' => array(
                        'type' => 'object',
                        'properties' => array(
                            'category' => array(
                                'type' => 'string',
                                'description' => 'Menu category (e.g., "Chicken", "Sides", "Drinks")'
                            ),
                            'search' => array(
                                'type' => 'string',
                                'description' => 'Search keyword (e.g., "spicy", "vegetarian")'
                            ),
                            'max_items' => array(
                                'type' => 'integer',
                                'description' => 'Maximum number of items to return',
                                'default' => 5
                            )
                        )
                    )
                )
            );
        }
        
        // Order placement function
        if (get_option('ucfc_ai_enable_order_placement', '1') === '1') {
            $tools[] = array(
                'type' => 'function',
                'function' => array(
                    'name' => 'add_to_cart',
                    'description' => 'Add menu item to customer cart',
                    'parameters' => array(
                        'type' => 'object',
                        'properties' => array(
                            'item_name' => array(
                                'type' => 'string',
                                'description' => 'Name of the menu item'
                            ),
                            'quantity' => array(
                                'type' => 'integer',
                                'description' => 'Quantity to add',
                                'default' => 1
                            ),
                            'customizations' => array(
                                'type' => 'object',
                                'description' => 'Item customizations (sides, extras, special instructions)'
                            )
                        ),
                        'required' => array('item_name')
                    )
                )
            );
        }
        
        return $tools;
    }
    
    /**
     * Handle function calls from AI
     */
    private function handle_function_calls($tool_calls, $original_reply) {
        $results = array();
        
        foreach ($tool_calls as $tool_call) {
            $function_name = $tool_call['function']['name'];
            $arguments = json_decode($tool_call['function']['arguments'], true);
            
            switch ($function_name) {
                case 'get_menu_items':
                    $results[] = $this->function_get_menu_items($arguments);
                    break;
                case 'add_to_cart':
                    $results[] = $this->function_add_to_cart($arguments);
                    break;
            }
        }
        
        // If functions were called, append results to reply
        if (!empty($results)) {
            return $original_reply . "\n\n" . implode("\n", $results);
        }
        
        return $original_reply;
    }
    
    /**
     * Function: Get menu items
     */
    private function function_get_menu_items($args) {
        $query_args = array(
            'post_type' => 'menu_item',
            'posts_per_page' => $args['max_items'] ?? 5
        );
        
        if (!empty($args['category'])) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'menu_category',
                    'field' => 'slug',
                    'terms' => sanitize_title($args['category'])
                )
            );
        }
        
        if (!empty($args['search'])) {
            $query_args['s'] = $args['search'];
        }
        
        $items = get_posts($query_args);
        
        if (empty($items)) {
            return "No menu items found matching those criteria.";
        }
        
        $result = "Here are the menu items:\n\n";
        foreach ($items as $item) {
            $price = get_post_meta($item->ID, '_menu_item_price', true);
            $description = get_the_excerpt($item);
            
            $result .= "**{$item->post_title}**";
            if ($price) $result .= " - \${$price}";
            $result .= "\n";
            if ($description) $result .= "{$description}\n";
            $result .= "\n";
        }
        
        return $result;
    }
    
    /**
     * Function: Add to cart
     */
    private function function_add_to_cart($args) {
        // This would integrate with your cart system
        // For now, return a confirmation message
        
        $item_name = $args['item_name'];
        $quantity = $args['quantity'] ?? 1;
        
        return "✅ Added {$quantity}x {$item_name} to cart! Would you like to add anything else?";
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        $test_message = "Say 'hello' in 5 words or less.";
        $result = $this->chat($test_message, array());
        
        if ($result['success']) {
            return array(
                'success' => true,
                'message' => "Connected successfully!\n\nModel: {$this->model}\nResponse: {$result['reply']}"
            );
        }
        
        return $result;
    }
}

// Initialize AI Engine
$ucfc_ai_engine = new UCFC_AI_Engine();

/**
 * AJAX Handler: Test AI Chat
 */
add_action('wp_ajax_ucfc_test_ai_chat', function() {
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    global $ucfc_ai_engine;
    
    $message = sanitize_text_field($_POST['message']);
    $history = isset($_POST['history']) ? $_POST['history'] : array();
    
    $result = $ucfc_ai_engine->chat($message, $history);
    
    if ($result['success']) {
        wp_send_json_success(array('reply' => $result['reply']));
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
});

/**
 * AJAX Handler: Test API Connection
 */
add_action('wp_ajax_ucfc_test_api_connection', function() {
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    global $ucfc_ai_engine;
    
    $result = $ucfc_ai_engine->test_connection();
    
    if ($result['success']) {
        wp_send_json_success(array('message' => $result['message']));
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
});

/**
 * AJAX Handler: Fetch OpenAI Assistant
 */
add_action('wp_ajax_ucfc_fetch_openai_assistant', function() {
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    $assistant_id = sanitize_text_field($_POST['assistant_id']);
    $api_key = get_option('ucfc_ai_openai_key');
    
    if (empty($api_key)) {
        wp_send_json_error(array('message' => 'OpenAI API key not configured'));
    }
    
    $response = wp_remote_get("https://api.openai.com/v1/assistants/{$assistant_id}", array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'OpenAI-Beta' => 'assistants=v2'
        )
    ));
    
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => $response->get_error_message()));
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        wp_send_json_error(array('message' => $body['error']['message']));
    }
    
    wp_send_json_success(array('assistant' => $body));
});

/**
 * Frontend AJAX Handler: Customer Chat
 */
add_action('wp_ajax_nopriv_ucfc_ai_chat', 'ucfc_handle_customer_chat');
add_action('wp_ajax_ucfc_ai_chat', 'ucfc_handle_customer_chat');

function ucfc_handle_customer_chat() {
    check_ajax_referer('uncle_chans_nonce', 'nonce');
    
    // Check if AI is enabled
    if (get_option('ucfc_ai_enabled', '0') !== '1') {
        wp_send_json_error(array('message' => 'AI assistant is currently disabled'));
    }
    
    // Check rate limits
    global $ucfc_rate_limiter;
    if ($ucfc_rate_limiter) {
        $limit_check = $ucfc_rate_limiter->is_allowed();
        if (!$limit_check['allowed']) {
            wp_send_json_error(array('message' => $limit_check['message']));
        }
    }
    
    global $ucfc_ai_engine, $ucfc_conversation_logger;
    
    $message = sanitize_text_field($_POST['message']);
    $history = isset($_POST['history']) ? $_POST['history'] : array();
    
    // Track start time for response time calculation
    $start_time = microtime(true);
    
    $result = $ucfc_ai_engine->chat($message, $history);
    
    // Calculate response time
    $response_time = round(microtime(true) - $start_time, 2);
    
    if ($result['success']) {
        // Record rate limit usage
        if ($ucfc_rate_limiter) {
            $ucfc_rate_limiter->record_request();
        }
        
        // Log conversation to database (skip if cached)
        if (!isset($result['cached']) || !$result['cached']) {
            $ucfc_conversation_logger->log_conversation(array(
                'user_message' => $message,
                'ai_response' => $result['reply'],
                'response_time' => $response_time,
                'tokens_used' => isset($result['usage']['total_tokens']) ? $result['usage']['total_tokens'] : 0,
                'function_called' => isset($result['function_called']) ? $result['function_called'] : null,
                'function_result' => isset($result['function_result']) ? json_encode($result['function_result']) : null
            ));
        }
        
        wp_send_json_success(array(
            'reply' => $result['reply'],
            'cached' => isset($result['cached']) ? $result['cached'] : false
        ));
    } else {
        // Log failed conversation too
        $ucfc_conversation_logger->log_conversation(array(
            'user_message' => $message,
            'ai_response' => 'ERROR: ' . $result['message'],
            'response_time' => $response_time,
            'tokens_used' => 0
        ));
        
        wp_send_json_error(array('message' => $result['message']));
    }
}

/**
 * Save OpenAI Assistant Configuration
 */
function ucfc_save_assistant_config() {
    // Verify nonce
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    // Get configuration from AJAX
    $config = isset($_POST['config']) ? $_POST['config'] : array();
    
    if (empty($config)) {
        wp_send_json_error(array('message' => 'No configuration provided'));
        return;
    }
    
    // Validate JSON fields
    if (!empty($config['tools'])) {
        $tools = json_decode($config['tools'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(array('message' => 'Invalid tools JSON: ' . json_last_error_msg()));
            return;
        }
    }
    
    if (!empty($config['custom_functions'])) {
        $custom = json_decode($config['custom_functions'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(array('message' => 'Invalid custom functions JSON: ' . json_last_error_msg()));
            return;
        }
    }
    
    // Save configuration to WordPress options
    update_option('ucfc_ai_assistant_id', sanitize_text_field($config['assistant_id']));
    update_option('ucfc_assistant_instructions_edit', wp_kses_post($config['instructions']));
    update_option('ucfc_assistant_tools_edit', $config['tools']);
    update_option('ucfc_assistant_custom_functions', $config['custom_functions']);
    
    // Store metadata
    update_option('ucfc_assistant_name', sanitize_text_field($config['name']));
    update_option('ucfc_assistant_model', sanitize_text_field($config['model']));
    update_option('ucfc_assistant_last_sync', current_time('mysql'));
    
    wp_send_json_success(array(
        'message' => 'Assistant configuration saved successfully',
        'timestamp' => current_time('mysql')
    ));
}
add_action('wp_ajax_ucfc_save_assistant_config', 'ucfc_save_assistant_config');

