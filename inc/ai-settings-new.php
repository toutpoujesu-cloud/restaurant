<?php
/**
 * AI Settings Dashboard
 * Manages LLM selection, API keys, knowledge base, and RAG configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle AJAX save
add_action('wp_ajax_save_ai_settings', 'restaurant_save_ai_settings');
add_action('wp_ajax_nopriv_save_ai_settings', 'restaurant_save_ai_settings');

function restaurant_save_ai_settings() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_settings_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    $provider = sanitize_text_field($_POST['llm_provider'] ?? '');
    $api_keys = [];

    if ($provider === 'claude') {
        $api_keys['claude'] = sanitize_text_field($_POST['claude_api_key'] ?? '');
    } elseif ($provider === 'gemini') {
        $api_keys['gemini'] = sanitize_text_field($_POST['gemini_api_key'] ?? '');
    } elseif ($provider === 'openai') {
        $api_keys['openai'] = sanitize_text_field($_POST['openai_api_key'] ?? '');
        $api_keys['openai_assistant_id'] = sanitize_text_field($_POST['openai_assistant_id'] ?? '');
    }

    update_option('restaurant_llm_provider', $provider);
    update_option('restaurant_api_keys', $api_keys);

    wp_send_json_success(['message' => 'AI Settings saved successfully']);
}

/**
 * Render AI Settings Page
 */
function restaurant_render_ai_settings_tab() {
    $llm_provider = get_option('restaurant_llm_provider', 'claude');
    $api_keys = get_option('restaurant_api_keys', []);
    $nonce = wp_create_nonce('ai_settings_nonce');
    ?>

    <style>
        .ai-settings-wrap {
            max-width: 1000px;
            margin: 20px 0;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .ai-settings-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1d;
            margin: 0 0 10px 0;
        }

        .ai-settings-subtitle {
            color: #666;
            margin: 0 0 30px 0;
        }

        .ai-section {
            margin-bottom: 40px;
            padding: 25px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid #FF6B6B;
        }

        .ai-section h3 {
            margin-top: 0;
            color: #1a1a1d;
            font-size: 18px;
        }

        .llm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .llm-card {
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .llm-card:hover {
            border-color: #FF6B6B;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.2);
        }

        .llm-card.selected {
            border-color: #FF6B6B;
            background: rgba(255, 107, 107, 0.05);
        }

        .llm-card h4 {
            margin: 0 0 5px 0;
            color: #1a1a1d;
        }

        .llm-badge {
            display: inline-block;
            background: #FF6B6B;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .llm-card p {
            margin: 10px 0;
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #1a1a1d;
            font-weight: 600;
        }

        .form-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: #FF6B6B;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }

        .form-hint {
            display: block;
            margin-top: 5px;
            color: #999;
            font-size: 12px;
        }

        .form-hint a {
            color: #FF6B6B;
            text-decoration: none;
        }

        .form-hint a:hover {
            text-decoration: underline;
        }

        .submit-btn {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E3C 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }

        .success-message {
            padding: 12px 15px;
            background: #d4edda;
            color: #155724;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .info-box {
            padding: 15px;
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .info-box p {
            margin: 0;
            color: #01579b;
            font-size: 14px;
        }
    </style>

    <div class="ai-settings-wrap">
        <h1 class="ai-settings-title">🤖 AI Settings</h1>
        <p class="ai-settings-subtitle">Configure your AI chat assistant with LangChain & LlamaIndex integration</p>

        <div class="success-message" id="success-message">✓ Settings saved successfully</div>

        <!-- LLM Provider Section -->
        <div class="ai-section">
            <h3>📊 Select LLM Provider</h3>
            <div class="llm-grid">
                <!-- Claude -->
                <div class="llm-card" data-provider="claude" onclick="selectProvider('claude')">
                    <h4>Claude <span class="llm-badge">Anthropic</span></h4>
                    <p>Advanced reasoning and nuanced understanding</p>
                    <input type="radio" name="llm_provider" value="claude" <?php checked($llm_provider, 'claude'); ?> style="display:none;">
                    <div class="form-group">
                        <label class="form-label">API Key</label>
                        <input type="password" class="form-input" id="claude_api_key" placeholder="sk-ant-..." value="<?php echo esc_attr($api_keys['claude'] ?? ''); ?>">
                        <span class="form-hint">Get from <a href="https://console.anthropic.com" target="_blank">Anthropic Console</a></span>
                    </div>
                </div>

                <!-- Gemini -->
                <div class="llm-card" data-provider="gemini" onclick="selectProvider('gemini')">
                    <h4>Gemini <span class="llm-badge">Google</span></h4>
                    <p>Multimodal AI with vision capabilities</p>
                    <input type="radio" name="llm_provider" value="gemini" <?php checked($llm_provider, 'gemini'); ?> style="display:none;">
                    <div class="form-group">
                        <label class="form-label">API Key</label>
                        <input type="password" class="form-input" id="gemini_api_key" placeholder="AIza..." value="<?php echo esc_attr($api_keys['gemini'] ?? ''); ?>">
                        <span class="form-hint">Get from <a href="https://ai.google.dev" target="_blank">Google AI Studio</a></span>
                    </div>
                </div>

                <!-- OpenAI -->
                <div class="llm-card" data-provider="openai" onclick="selectProvider('openai')">
                    <h4>OpenAI <span class="llm-badge">GPT-4</span></h4>
                    <p>Latest models with GPT-4 and Assistants API</p>
                    <input type="radio" name="llm_provider" value="openai" <?php checked($llm_provider, 'openai'); ?> style="display:none;">
                    <div class="form-group">
                        <label class="form-label">API Key</label>
                        <input type="password" class="form-input" id="openai_api_key" placeholder="sk-..." value="<?php echo esc_attr($api_keys['openai'] ?? ''); ?>">
                        <span class="form-hint">Get from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI API Keys</a></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assistant ID (Optional)</label>
                        <input type="text" class="form-input" id="openai_assistant_id" placeholder="asst_..." value="<?php echo esc_attr($api_keys['openai_assistant_id'] ?? ''); ?>">
                        <span class="form-hint">Import existing OpenAI Assistant ID</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Framework Info -->
        <div class="ai-section">
            <h3>⚙️ Framework Configuration</h3>
            <div class="info-box">
                <p><strong>LangChain + LlamaIndex:</strong> Your AI uses these frameworks to orchestrate conversations and retrieve knowledge from uploaded documents (RAG - Retrieval Augmented Generation).</p>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" id="enable_memory" checked> Enable Conversation Memory
                </label>
                <span class="form-hint">Keeps conversation context for better follow-up responses</span>
            </div>
        </div>

        <!-- Save Button -->
        <button class="submit-btn" id="save-btn">💾 Save AI Settings</button>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function selectProvider(provider) {
            $('input[name="llm_provider"][value="' + provider + '"]').prop('checked', true);
            $('.llm-card').removeClass('selected');
            $('.llm-card[data-provider="' + provider + '"]').addClass('selected');
        }

        // Initialize selected provider on load
        var selected = $('input[name="llm_provider"]:checked').val();
        if (selected) {
            $('.llm-card[data-provider="' + selected + '"]').addClass('selected');
        }

        // Handle save button
        $('#save-btn').click(function() {
            var provider = $('input[name="llm_provider"]:checked').val();
            var formData = {
                action: 'save_ai_settings',
                nonce: '<?php echo $nonce; ?>',
                llm_provider: provider
            };

            // Add API keys based on provider
            if (provider === 'claude') {
                formData.claude_api_key = $('#claude_api_key').val();
            } else if (provider === 'gemini') {
                formData.gemini_api_key = $('#gemini_api_key').val();
            } else if (provider === 'openai') {
                formData.openai_api_key = $('#openai_api_key').val();
                formData.openai_assistant_id = $('#openai_assistant_id').val();
            }

            $.post('<?php echo admin_url("admin-ajax.php"); ?>', formData, function(response) {
                if (response.success) {
                    $('#success-message').addClass('show');
                    setTimeout(function() {
                        $('#success-message').removeClass('show');
                    }, 3000);
                } else {
                    alert('Error: ' + response.data.message);
                }
            });
        });
    });
    </script>

    <?php
}
