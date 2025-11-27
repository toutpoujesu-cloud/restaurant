<?php
/**
 * AI Agent Framework Integration
 * Handles LangChain + LlamaIndex orchestration with selected LLM
 */

if (!defined('ABSPATH')) {
    exit;
}

class RestaurantAIAgent {
    private $llm_provider;
    private $api_keys;
    private $knowledge_base;
    private $config;

    public function __construct() {
        $this->llm_provider = get_option('restaurant_llm_provider', 'claude');
        $this->api_keys = get_option('restaurant_api_keys', []);
        $this->knowledge_base = get_option('restaurant_knowledge_base', []);
        $this->config = [
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'enable_memory' => true,
            'framework' => 'langchain' // with llamaindex for RAG
        ];
    }

    /**
     * Initialize AI Agent with selected LLM
     */
    public function initialize() {
        switch ($this->llm_provider) {
            case 'claude':
                return $this->init_claude();
            case 'gemini':
                return $this->init_gemini();
            case 'openai':
                return $this->init_openai();
            default:
                return false;
        }
    }

    /**
     * Initialize Claude LLM
     */
    private function init_claude() {
        $api_key = $this->api_keys['claude'] ?? null;
        
        if (!$api_key) {
            error_log('Claude API key not configured');
            return false;
        }

        return [
            'provider' => 'claude',
            'model' => 'claude-3-5-sonnet',
            'api_key' => $api_key,
            'endpoint' => 'https://api.anthropic.com/v1/messages',
            'headers' => [
                'x-api-key' => $api_key,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json'
            ],
            'capabilities' => [
                'vision' => false,
                'tool_use' => true,
                'max_tokens' => 4096,
                'supports_system_prompt' => true
            ]
        ];
    }

    /**
     * Initialize Gemini LLM
     */
    private function init_gemini() {
        $api_key = $this->api_keys['gemini'] ?? null;
        
        if (!$api_key) {
            error_log('Gemini API key not configured');
            return false;
        }

        return [
            'provider' => 'gemini',
            'model' => 'gemini-2.0-flash',
            'api_key' => $api_key,
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
            'headers' => [
                'content-type' => 'application/json'
            ],
            'capabilities' => [
                'vision' => true,
                'tool_use' => true,
                'max_tokens' => 4000,
                'supports_system_prompt' => true
            ]
        ];
    }

    /**
     * Initialize OpenAI LLM
     */
    private function init_openai() {
        $api_key = $this->api_keys['openai'] ?? null;
        $assistant_id = $this->api_keys['openai_assistant_id'] ?? null;
        
        if (!$api_key) {
            error_log('OpenAI API key not configured');
            return false;
        }

        return [
            'provider' => 'openai',
            'model' => 'gpt-4-turbo',
            'api_key' => $api_key,
            'assistant_id' => $assistant_id,
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'content-type' => 'application/json'
            ],
            'capabilities' => [
                'vision' => true,
                'tool_use' => true,
                'max_tokens' => 4096,
                'supports_system_prompt' => true,
                'assistants_api' => true
            ]
        ];
    }

    /**
     * Build RAG-enhanced prompt with knowledge base
     */
    public function build_rag_context($user_query) {
        $context = "You are a helpful AI assistant for Uncle Chan's Fried Chicken restaurant.\n\n";
        
        // Add relevant knowledge base files
        $relevant_docs = $this->retrieve_relevant_documents($user_query);
        
        if (!empty($relevant_docs)) {
            $context .= "## Context from Knowledge Base:\n";
            foreach ($relevant_docs as $doc) {
                $context .= "- " . $doc['content'] . "\n";
            }
            $context .= "\n";
        }

        // Add system instructions
        $context .= "## Instructions:\n";
        $context .= "- Be helpful and professional\n";
        $context .= "- Use context from the knowledge base to provide accurate information\n";
        $context .= "- If you don't know something, say so clearly\n";
        $context .= "- Always maintain a friendly, service-oriented tone\n\n";

        return $context;
    }

    /**
     * Retrieve relevant documents from knowledge base using LlamaIndex
     */
    private function retrieve_relevant_documents($query, $top_k = 5) {
        // In production, this would use LlamaIndex vector search
        // For now, return mock implementation
        
        $documents = [];
        $upload_dir = wp_upload_dir();
        $kb_dir = $upload_dir['basedir'] . '/restaurant-ai-knowledge/';

        foreach ($this->knowledge_base as $category => $files) {
            foreach ($files as $file) {
                // In production, would use semantic search via LlamaIndex
                // This is simplified implementation
                if (file_exists($file['path'])) {
                    $documents[] = [
                        'category' => $category,
                        'filename' => $file['filename'],
                        'content' => $this->extract_file_content($file['path']),
                        'relevance_score' => $this->calculate_relevance($query, $file['filename'])
                    ];
                }
            }
        }

        // Sort by relevance and return top-k
        usort($documents, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });

        return array_slice($documents, 0, $top_k);
    }

    /**
     * Extract content from uploaded file
     */
    private function extract_file_content($file_path) {
        // Simplified - in production use proper file parsing
        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }
        return '';
    }

    /**
     * Calculate relevance score between query and document
     */
    private function calculate_relevance($query, $filename) {
        // Simple keyword matching - in production use semantic similarity
        $query_words = array_filter(explode(' ', strtolower($query)));
        $filename_lower = strtolower($filename);
        
        $matches = 0;
        foreach ($query_words as $word) {
            if (strpos($filename_lower, $word) !== false) {
                $matches++;
            }
        }
        
        return $matches / (count($query_words) ?: 1);
    }

    /**
     * Process user message through AI Agent
     */
    public function process_message($user_message, $conversation_history = []) {
        $llm_config = $this->initialize();
        
        if (!$llm_config) {
            return [
                'success' => false,
                'error' => 'LLM not properly configured'
            ];
        }

        // Build RAG context
        $rag_context = $this->build_rag_context($user_message);

        // Prepare messages
        $messages = [];
        if ($this->config['enable_memory'] && !empty($conversation_history)) {
            $messages = $conversation_history;
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $user_message
        ];

        // Route to appropriate LLM
        $response = match ($this->llm_provider) {
            'claude' => $this->call_claude($llm_config, $rag_context, $messages),
            'gemini' => $this->call_gemini($llm_config, $rag_context, $messages),
            'openai' => $this->call_openai($llm_config, $rag_context, $messages),
            default => null
        };

        return $response;
    }

    /**
     * Call Claude API
     */
    private function call_claude($config, $context, $messages) {
        $request_body = [
            'model' => $config['model'],
            'max_tokens' => $this->config['max_tokens'],
            'system' => $context,
            'messages' => array_map(function($msg) {
                return [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }, $messages)
        ];

        $response = wp_remote_post($config['endpoint'], [
            'headers' => $config['headers'],
            'body' => json_encode($request_body),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message()
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        return [
            'success' => true,
            'message' => $body['content'][0]['text'] ?? 'No response',
            'provider' => 'claude',
            'metadata' => [
                'model' => $config['model'],
                'usage' => $body['usage'] ?? []
            ]
        ];
    }

    /**
     * Call Gemini API
     */
    private function call_gemini($config, $context, $messages) {
        $contents = [];
        
        // Add system context as first turn
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $context]]
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'I understand the context. How can I help you?']]
        ];

        // Add conversation
        foreach ($messages as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]]
            ];
        }

        $request_body = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->config['temperature'],
                'maxOutputTokens' => $this->config['max_tokens']
            ]
        ];

        $url = $config['endpoint'] . '?key=' . $config['api_key'];
        
        $response = wp_remote_post($url, [
            'headers' => $config['headers'],
            'body' => json_encode($request_body),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message()
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        return [
            'success' => true,
            'message' => $body['candidates'][0]['content']['parts'][0]['text'] ?? 'No response',
            'provider' => 'gemini',
            'metadata' => [
                'model' => $config['model'],
                'usage' => $body['usageMetadata'] ?? []
            ]
        ];
    }

    /**
     * Call OpenAI API
     */
    private function call_openai($config, $context, $messages) {
        $request_messages = [
            [
                'role' => 'system',
                'content' => $context
            ]
        ];

        foreach ($messages as $msg) {
            $request_messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }

        $request_body = [
            'model' => $config['model'],
            'messages' => $request_messages,
            'temperature' => $this->config['temperature'],
            'max_tokens' => $this->config['max_tokens']
        ];

        $response = wp_remote_post($config['endpoint'], [
            'headers' => $config['headers'],
            'body' => json_encode($request_body),
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message()
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        return [
            'success' => true,
            'message' => $body['choices'][0]['message']['content'] ?? 'No response',
            'provider' => 'openai',
            'metadata' => [
                'model' => $config['model'],
                'usage' => $body['usage'] ?? []
            ]
        ];
    }

    /**
     * Test connection to configured LLM
     */
    public function test_connection() {
        $test_response = $this->process_message('Hello, this is a test. Please respond with "Test successful".');
        
        return $test_response['success'];
    }
}

// Register AJAX endpoint for chat
add_action('wp_ajax_restaurant_ai_chat', 'handle_restaurant_ai_chat');

function handle_restaurant_ai_chat() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'restaurant_ai_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    $user_message = sanitize_text_field($_POST['message'] ?? '');
    $conversation_history = isset($_POST['history']) ? sanitize_text_field($_POST['history']) : [];

    if (empty($user_message)) {
        wp_send_json_error(['message' => 'Empty message']);
    }

    $ai_agent = new RestaurantAIAgent();
    $response = $ai_agent->process_message($user_message, $conversation_history);

    wp_send_json($response);
}
