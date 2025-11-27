<?php
/**
 * Front-Page AI Chat Widget
 * Displays AI chat interface on public website using configured settings
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue AI chat scripts for front-end
add_action('wp_enqueue_scripts', 'enqueue_frontend_ai_chat');

function enqueue_frontend_ai_chat() {
    // Only enqueue on front-end pages
    if (is_admin()) {
        return;
    }

    wp_enqueue_style('restaurant-ai-chat', get_template_directory_uri() . '/assets/css/ai-chat-widget.css', [], '1.0');
    wp_enqueue_script('restaurant-ai-chat', get_template_directory_uri() . '/assets/js/ai-chat-widget.js', ['jquery'], '1.0', true);
    
    wp_localize_script('restaurant-ai-chat', 'RestaurantAIChat', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('restaurant_ai_nonce'),
        'llmProvider' => get_option('restaurant_llm_provider', 'claude'),
        'enableMemory' => get_option('restaurant_enable_memory', true)
    ]);
}

// Register shortcode for AI chat widget
add_shortcode('restaurant_ai_chat', 'render_restaurant_ai_chat_widget');

function render_restaurant_ai_chat_widget() {
    $llm_provider = get_option('restaurant_llm_provider', 'claude');
    
    ob_start();
    ?>
    <div class="restaurant-ai-chat-widget">
        <!-- Chat Header -->
        <div class="ai-chat-header">
            <div class="ai-chat-title">
                <span class="ai-chat-icon">🤖</span>
                <span class="ai-chat-name">Uncle Chan's AI Assistant</span>
            </div>
            <div class="ai-chat-provider">
                <span class="provider-badge" data-provider="<?php echo esc_attr($llm_provider); ?>">
                    <?php echo ucfirst($llm_provider); ?>
                </span>
            </div>
            <button class="ai-chat-close" aria-label="Close chat">✕</button>
        </div>

        <!-- Chat Messages Container -->
        <div class="ai-chat-messages">
            <div class="ai-message ai-welcome">
                <div class="ai-message-avatar">🤖</div>
                <div class="ai-message-content">
                    <p>Welcome to Uncle Chan's! I'm here to help you with menu information, orders, delivery, and more. What can I assist you with today?</p>
                </div>
            </div>
        </div>

        <!-- Chat Input Area -->
        <div class="ai-chat-input-area">
            <div class="ai-chat-input-wrapper">
                <input 
                    type="text" 
                    class="ai-chat-input" 
                    placeholder="Ask me anything about Uncle Chan's..."
                    aria-label="Chat message input"
                >
                <button class="ai-chat-send" aria-label="Send message">
                    <span>📤</span>
                </button>
            </div>
            <div class="ai-chat-disclaimer">
                <small>Powered by <?php echo ucfirst($llm_provider); ?> with RAG Knowledge Base</small>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div class="ai-chat-loading" style="display: none;">
            <div class="loader"></div>
        </div>
    </div>

    <style>
        .restaurant-ai-chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 380px;
            max-width: 90vw;
            height: 600px;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            border: 2px solid rgba(255, 107, 107, 0.1);
            font-family: 'Inter', sans-serif;
        }

        .ai-chat-header {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E3C 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 1.25rem 1.25rem 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .ai-chat-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .ai-chat-icon {
            font-size: 1.5rem;
        }

        .ai-chat-name {
            font-weight: 600;
        }

        .provider-badge {
            background: rgba(255, 255, 255, 0.3);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .ai-chat-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }

        .ai-chat-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .ai-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: #F9FAFB;
        }

        .ai-message {
            display: flex;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .ai-message-avatar {
            font-size: 1.5rem;
            min-width: 32px;
            display: flex;
            align-items: center;
        }

        .ai-message-content {
            background: white;
            padding: 1rem;
            border-radius: 1rem;
            border: 1px solid rgba(255, 107, 107, 0.1);
            max-width: 85%;
        }

        .ai-message-content p {
            margin: 0;
            color: #1A1A1D;
            line-height: 1.5;
            font-size: 0.9375rem;
        }

        .ai-message.ai-welcome .ai-message-content {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.05) 0%, rgba(255, 142, 60, 0.05) 100%);
            border-color: #FF6B6B;
        }

        .user-message {
            justify-content: flex-end;
        }

        .user-message .ai-message-content {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E3C 100%);
            color: white;
            order: -1;
        }

        .user-message .ai-message-avatar {
            order: 1;
        }

        .ai-chat-input-area {
            padding: 1rem;
            border-top: 1px solid rgba(255, 107, 107, 0.1);
            background: white;
        }

        .ai-chat-input-wrapper {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .ai-chat-input {
            flex: 1;
            border: 2px solid rgba(255, 107, 107, 0.2);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .ai-chat-input:focus {
            outline: none;
            border-color: #FF6B6B;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }

        .ai-chat-send {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E3C 100%);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 0.75rem;
            cursor: pointer;
            font-size: 1.25rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ai-chat-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }

        .ai-chat-send:active {
            transform: translateY(0);
        }

        .ai-chat-disclaimer {
            text-align: center;
            color: #9CA3AF;
            font-size: 0.75rem;
        }

        .ai-chat-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .loader {
            border: 3px solid rgba(255, 107, 107, 0.1);
            border-top: 3px solid #FF6B6B;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .restaurant-ai-chat-widget {
                width: calc(100vw - 20px);
                height: 70vh;
                max-height: 600px;
            }
        }

        /* Scrollbar styling */
        .ai-chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .ai-chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .ai-chat-messages::-webkit-scrollbar-thumb {
            background: rgba(255, 107, 107, 0.3);
            border-radius: 3px;
        }

        .ai-chat-messages::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 107, 107, 0.5);
        }
    </style>

    <?php
    return ob_get_clean();
}

// Add AI chat widget to footer
add_action('wp_footer', 'add_ai_chat_widget_to_footer');

function add_ai_chat_widget_to_footer() {
    if (!is_admin() && get_option('restaurant_llm_provider')) {
        echo do_shortcode('[restaurant_ai_chat]');
    }
}
