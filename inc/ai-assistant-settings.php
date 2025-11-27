<?php
/**
 * AI Assistant Settings & Configuration
 * 
 * Complete AI chat system with multi-model support:
 * - OpenAI (GPT-4, GPT-3.5)
 * - Claude (Anthropic)
 * - Gemini (Google)
 * 
 * Features:
 * - API key management (encrypted)
 * - System prompt builder
 * - Function calling configuration
 * - Test chat interface
 * - Menu integration
 * - OpenAI Assistant import
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add AI Assistant to Restaurant Menu
 */
function ucfc_add_ai_assistant_menu() {
    add_submenu_page(
        'restaurant-settings',
        __('AI Assistant', 'uncle-chans'),
        __('🤖 AI Assistant', 'uncle-chans'),
        'manage_options',
        'restaurant-ai-assistant',
        'ucfc_ai_assistant_page_html'
    );
}
add_action('admin_menu', 'ucfc_add_ai_assistant_menu', 98);

/**
 * Register AI Assistant Settings
 */
function ucfc_register_ai_settings() {
    // AI Model Settings
    register_setting('ucfc_ai_settings', 'ucfc_ai_enabled');
    register_setting('ucfc_ai_settings', 'ucfc_ai_model_provider');
    register_setting('ucfc_ai_settings', 'ucfc_ai_model_version');
    register_setting('ucfc_ai_settings', 'ucfc_ai_openai_key');
    register_setting('ucfc_ai_settings', 'ucfc_ai_claude_key');
    register_setting('ucfc_ai_settings', 'ucfc_ai_gemini_key');
    
    // AI Behavior Settings
    register_setting('ucfc_ai_settings', 'ucfc_ai_system_prompt');
    register_setting('ucfc_ai_settings', 'ucfc_ai_personality');
    register_setting('ucfc_ai_settings', 'ucfc_ai_temperature');
    register_setting('ucfc_ai_settings', 'ucfc_ai_max_tokens');
    register_setting('ucfc_ai_settings', 'ucfc_ai_top_p');
    
    // Function Calling
    register_setting('ucfc_ai_settings', 'ucfc_ai_enable_menu_search');
    register_setting('ucfc_ai_settings', 'ucfc_ai_enable_order_placement');
    register_setting('ucfc_ai_settings', 'ucfc_ai_enable_recommendations');
    register_setting('ucfc_ai_settings', 'ucfc_ai_enable_reservations');
    
    // Advanced
    register_setting('ucfc_ai_settings', 'ucfc_ai_custom_functions');
    register_setting('ucfc_ai_settings', 'ucfc_ai_knowledge_base');
    
    // OpenAI Assistant Import
    register_setting('ucfc_ai_settings', 'ucfc_ai_assistant_id');
    register_setting('ucfc_ai_settings', 'ucfc_ai_use_assistant');
    register_setting('ucfc_ai_settings', 'ucfc_assistant_instructions_edit');
    register_setting('ucfc_ai_settings', 'ucfc_assistant_tools_edit');
    register_setting('ucfc_ai_settings', 'ucfc_assistant_custom_functions');
}
add_action('admin_init', 'ucfc_register_ai_settings');

/**
 * AI Assistant Settings Page HTML
 */
function ucfc_ai_assistant_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Handle form submission
    if (isset($_POST['ucfc_ai_save_settings']) && check_admin_referer('ucfc_ai_settings_save', 'ucfc_ai_nonce')) {
        // Settings are auto-saved by WordPress Settings API
        add_settings_error('ucfc_ai_messages', 'ucfc_ai_message', __('AI Settings Saved Successfully!', 'uncle-chans'), 'updated');
    }
    
    settings_errors('ucfc_ai_messages');
    
    // Get current settings
    $enabled = get_option('ucfc_ai_enabled', '0');
    $provider = get_option('ucfc_ai_model_provider', 'openai');
    $model_version = get_option('ucfc_ai_model_version', 'gpt-4-turbo');
    $personality = get_option('ucfc_ai_personality', 'friendly');
    $temperature = get_option('ucfc_ai_temperature', '0.7');
    $max_tokens = get_option('ucfc_ai_max_tokens', '1000');
    $top_p = get_option('ucfc_ai_top_p', '1');
    
    // Check if keys are set
    $openai_key = get_option('ucfc_ai_openai_key');
    $claude_key = get_option('ucfc_ai_claude_key');
    $gemini_key = get_option('ucfc_ai_gemini_key');
    
    $openai_connected = !empty($openai_key);
    $claude_connected = !empty($claude_key);
    $gemini_connected = !empty($gemini_key);
    ?>
    
    <div class="wrap ucfc-ai-assistant-page">
        <h1>🤖 AI Assistant Configuration</h1>
        <p class="description">Transform your restaurant with intelligent AI that knows your menu, takes orders, and delights customers 24/7.</p>
        
        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper ucfc-ai-tabs">
            <a href="#general" class="nav-tab nav-tab-active" data-tab="general">⚙️ General</a>
            <a href="#behavior" class="nav-tab" data-tab="behavior">🎭 Behavior</a>
            <a href="#functions" class="nav-tab" data-tab="functions">🔧 Functions</a>
            <a href="#import" class="nav-tab" data-tab="import">📥 Import Assistant</a>
            <a href="#test" class="nav-tab" data-tab="test">🧪 Test Chat</a>
        </nav>
        
        <form method="post" action="">
            <?php wp_nonce_field('ucfc_ai_settings_save', 'ucfc_ai_nonce'); ?>
            
            <!-- General Tab -->
            <div id="tab-general" class="ucfc-ai-tab-content active">
                <h2>AI Model Configuration</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_enabled">Enable AI Assistant</label>
                        </th>
                        <td>
                            <label class="ucfc-toggle-switch">
                                <input type="checkbox" id="ucfc_ai_enabled" name="ucfc_ai_enabled" value="1" <?php checked($enabled, '1'); ?> />
                                <span class="ucfc-toggle-slider"></span>
                            </label>
                            <p class="description">Turn on AI-powered chat for your customers</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_model_provider">AI Model Provider</label>
                        </th>
                        <td>
                            <div class="ucfc-model-selector">
                                <label class="ucfc-model-card <?php echo $provider === 'openai' ? 'selected' : ''; ?>">
                                    <input type="radio" name="ucfc_ai_model_provider" value="openai" <?php checked($provider, 'openai'); ?> />
                                    <div class="model-card-content">
                                        <div class="model-logo">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/openai-logo.png" alt="OpenAI" onerror="this.style.display='none'" />
                                            <span class="model-icon">🟢</span>
                                        </div>
                                        <h3>OpenAI</h3>
                                        <p>GPT-4, GPT-3.5 Turbo</p>
                                        <span class="status-badge <?php echo $openai_connected ? 'connected' : 'disconnected'; ?>">
                                            <?php echo $openai_connected ? '✓ Connected' : '○ Not Connected'; ?>
                                        </span>
                                    </div>
                                </label>
                                
                                <label class="ucfc-model-card <?php echo $provider === 'claude' ? 'selected' : ''; ?>">
                                    <input type="radio" name="ucfc_ai_model_provider" value="claude" <?php checked($provider, 'claude'); ?> />
                                    <div class="model-card-content">
                                        <div class="model-logo">
                                            <span class="model-icon">🟣</span>
                                        </div>
                                        <h3>Claude</h3>
                                        <p>Claude 3.5 Sonnet, Opus</p>
                                        <span class="status-badge <?php echo $claude_connected ? 'connected' : 'disconnected'; ?>">
                                            <?php echo $claude_connected ? '✓ Connected' : '○ Not Connected'; ?>
                                        </span>
                                    </div>
                                </label>
                                
                                <label class="ucfc-model-card <?php echo $provider === 'gemini' ? 'selected' : ''; ?>">
                                    <input type="radio" name="ucfc_ai_model_provider" value="gemini" <?php checked($provider, 'gemini'); ?> />
                                    <div class="model-card-content">
                                        <div class="model-logo">
                                            <span class="model-icon">🔵</span>
                                        </div>
                                        <h3>Gemini</h3>
                                        <p>Gemini Pro, Ultra</p>
                                        <span class="status-badge <?php echo $gemini_connected ? 'connected' : 'disconnected'; ?>">
                                            <?php echo $gemini_connected ? '✓ Connected' : '○ Not Connected'; ?>
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- OpenAI Settings -->
                    <tr class="provider-settings openai-settings" style="<?php echo $provider !== 'openai' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ucfc_ai_openai_key">OpenAI API Key</label>
                        </th>
                        <td>
                            <input type="password" id="ucfc_ai_openai_key" name="ucfc_ai_openai_key" 
                                   value="<?php echo esc_attr($openai_key); ?>" 
                                   class="large-text code" 
                                   placeholder="sk-..." />
                            <button type="button" class="button button-secondary toggle-key-visibility">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <p class="description">
                                Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                            </p>
                        </td>
                    </tr>
                    
                    <tr class="provider-settings openai-settings" style="<?php echo $provider !== 'openai' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ucfc_ai_model_version_openai">Model Version</label>
                        </th>
                        <td>
                            <select id="ucfc_ai_model_version_openai" name="ucfc_ai_model_version" class="regular-text">
                                <option value="gpt-4-turbo" <?php selected($model_version, 'gpt-4-turbo'); ?>>GPT-4 Turbo (Recommended)</option>
                                <option value="gpt-4" <?php selected($model_version, 'gpt-4'); ?>>GPT-4</option>
                                <option value="gpt-3.5-turbo" <?php selected($model_version, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo (Faster, Cheaper)</option>
                            </select>
                            <p class="description">GPT-4 Turbo offers the best balance of quality and speed</p>
                        </td>
                    </tr>
                    
                    <!-- Claude Settings -->
                    <tr class="provider-settings claude-settings" style="<?php echo $provider !== 'claude' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ucfc_ai_claude_key">Claude API Key</label>
                        </th>
                        <td>
                            <input type="password" id="ucfc_ai_claude_key" name="ucfc_ai_claude_key" 
                                   value="<?php echo esc_attr($claude_key); ?>" 
                                   class="large-text code" 
                                   placeholder="sk-ant-..." />
                            <button type="button" class="button button-secondary toggle-key-visibility">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <p class="description">
                                Get your API key from <a href="https://console.anthropic.com/settings/keys" target="_blank">Anthropic Console</a>
                            </p>
                        </td>
                    </tr>
                    
                    <tr class="provider-settings claude-settings" style="<?php echo $provider !== 'claude' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ucfc_ai_model_version_claude">Model Version</label>
                        </th>
                        <td>
                            <select id="ucfc_ai_model_version_claude" name="ucfc_ai_model_version" class="regular-text">
                                <option value="claude-3-5-sonnet-20241022" <?php selected($model_version, 'claude-3-5-sonnet-20241022'); ?>>Claude 3.5 Sonnet (Recommended)</option>
                                <option value="claude-3-opus-20240229" <?php selected($model_version, 'claude-3-opus-20240229'); ?>>Claude 3 Opus (Most Capable)</option>
                                <option value="claude-3-haiku-20240307" <?php selected($model_version, 'claude-3-haiku-20240307'); ?>>Claude 3 Haiku (Fastest)</option>
                            </select>
                            <p class="description">Claude 3.5 Sonnet offers exceptional performance</p>
                        </td>
                    </tr>
                    
                    <!-- Gemini Settings -->
                    <tr class="provider-settings gemini-settings" style="<?php echo $provider !== 'gemini' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ucfc_ai_gemini_key">Gemini API Key</label>
                        </th>
                        <td>
                            <input type="password" id="ucfc_ai_gemini_key" name="ucfc_ai_gemini_key" 
                                   value="<?php echo esc_attr($gemini_key); ?>" 
                                   class="large-text code" 
                                   placeholder="AIza..." />
                            <button type="button" class="button button-secondary toggle-key-visibility">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <p class="description">
                                Get your API key from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                            </p>
                        </td>
                    </tr>
                    
                    <tr class="provider-settings gemini-settings" style="<?php echo $provider !== 'gemini' ? 'display:none;' : ''; ?>">
                        <th scope="row">
                            <label for="ucfc_ai_model_version_gemini">Model Version</label>
                        </th>
                        <td>
                            <select id="ucfc_ai_model_version_gemini" name="ucfc_ai_model_version" class="regular-text">
                                <option value="gemini-pro" <?php selected($model_version, 'gemini-pro'); ?>>Gemini Pro (Recommended)</option>
                                <option value="gemini-pro-vision" <?php selected($model_version, 'gemini-pro-vision'); ?>>Gemini Pro Vision (With Images)</option>
                            </select>
                            <p class="description">Gemini Pro is optimized for text conversations</p>
                        </td>
                    </tr>
                    
                    <!-- Model Parameters -->
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_temperature">Temperature</label>
                        </th>
                        <td>
                            <input type="range" id="ucfc_ai_temperature" name="ucfc_ai_temperature" 
                                   min="0" max="2" step="0.1" value="<?php echo esc_attr($temperature); ?>" 
                                   class="ucfc-slider" />
                            <span class="ucfc-slider-value"><?php echo esc_html($temperature); ?></span>
                            <p class="description">
                                <strong>0 = Focused & Consistent</strong> | 1 = Balanced | <strong>2 = Creative & Varied</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_max_tokens">Max Response Length</label>
                        </th>
                        <td>
                            <input type="number" id="ucfc_ai_max_tokens" name="ucfc_ai_max_tokens" 
                                   value="<?php echo esc_attr($max_tokens); ?>" 
                                   min="100" max="4000" step="100" class="small-text" />
                            <p class="description">Maximum tokens in AI response (1000 = ~750 words)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h3 style="margin-top:20px;">⚡ Performance Settings</h3></th>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_enable_cache">Response Caching</label>
                        </th>
                        <td>
                            <label class="ucfc-toggle-switch">
                                <input type="checkbox" id="ucfc_ai_enable_cache" name="ucfc_ai_enable_cache" value="1" 
                                       <?php checked(get_option('ucfc_ai_enable_cache', '1'), '1'); ?> />
                                <span class="ucfc-toggle-slider"></span>
                            </label>
                            <p class="description">Cache identical questions to reduce API calls and improve speed</p>
                            <?php 
                            global $ucfc_ai_cache;
                            if ($ucfc_ai_cache) {
                                $stats = $ucfc_ai_cache->get_stats();
                                echo '<p class="description"><strong>Cache Stats:</strong> ' . $stats['cached_items'] . ' items cached, ' . $stats['hit_rate'] . '% hit rate</p>';
                            }
                            ?>
                            <button type="button" id="clear-cache-btn" class="button">🗑️ Clear Cache</button>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_cache_ttl">Cache Duration</label>
                        </th>
                        <td>
                            <select id="ucfc_ai_cache_ttl" name="ucfc_ai_cache_ttl" class="regular-text">
                                <option value="900" <?php selected(get_option('ucfc_ai_cache_ttl', 3600), 900); ?>>15 minutes</option>
                                <option value="1800" <?php selected(get_option('ucfc_ai_cache_ttl', 3600), 1800); ?>>30 minutes</option>
                                <option value="3600" <?php selected(get_option('ucfc_ai_cache_ttl', 3600), 3600); ?>>1 hour (Recommended)</option>
                                <option value="7200" <?php selected(get_option('ucfc_ai_cache_ttl', 3600), 7200); ?>>2 hours</option>
                                <option value="21600" <?php selected(get_option('ucfc_ai_cache_ttl', 3600), 21600); ?>>6 hours</option>
                                <option value="86400" <?php selected(get_option('ucfc_ai_cache_ttl', 3600), 86400); ?>>24 hours</option>
                            </select>
                            <p class="description">How long to cache AI responses</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_enable_rate_limit">Rate Limiting</label>
                        </th>
                        <td>
                            <label class="ucfc-toggle-switch">
                                <input type="checkbox" id="ucfc_ai_enable_rate_limit" name="ucfc_ai_enable_rate_limit" value="1" 
                                       <?php checked(get_option('ucfc_ai_enable_rate_limit', '1'), '1'); ?> />
                                <span class="ucfc-toggle-slider"></span>
                            </label>
                            <p class="description">Prevent abuse by limiting requests per user</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_rate_limit_hour">Hourly Limit</label>
                        </th>
                        <td>
                            <input type="number" id="ucfc_ai_rate_limit_hour" name="ucfc_ai_rate_limit_hour" 
                                   value="<?php echo esc_attr(get_option('ucfc_ai_rate_limit_hour', 20)); ?>" 
                                   min="1" max="100" class="small-text" /> requests per hour
                            <p class="description">Maximum AI requests per user per hour</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_rate_limit_day">Daily Limit</label>
                        </th>
                        <td>
                            <input type="number" id="ucfc_ai_rate_limit_day" name="ucfc_ai_rate_limit_day" 
                                   value="<?php echo esc_attr(get_option('ucfc_ai_rate_limit_day', 100)); ?>" 
                                   min="1" max="500" class="small-text" /> requests per day
                            <p class="description">Maximum AI requests per user per day</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Behavior Tab -->
            <div id="tab-behavior" class="ucfc-ai-tab-content">
                <h2>AI Personality & Behavior</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_personality">Personality Preset</label>
                        </th>
                        <td>
                            <select id="ucfc_ai_personality" name="ucfc_ai_personality" class="regular-text">
                                <option value="friendly" <?php selected($personality, 'friendly'); ?>>😊 Friendly Server - Warm, welcoming, casual</option>
                                <option value="professional" <?php selected($personality, 'professional'); ?>>👔 Professional - Courteous, efficient, formal</option>
                                <option value="fun" <?php selected($personality, 'fun'); ?>>🎉 Fun & Playful - Energetic, emoji-rich, exciting</option>
                                <option value="concierge" <?php selected($personality, 'concierge'); ?>>🎩 Fine Dining Concierge - Elegant, knowledgeable</option>
                                <option value="custom" <?php selected($personality, 'custom'); ?>>⚙️ Custom - Use system prompt below</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_system_prompt">System Prompt</label>
                        </th>
                        <td>
                            <?php
                            $system_prompt = get_option('ucfc_ai_system_prompt', '');
                            if (empty($system_prompt)) {
                                $restaurant_name = get_option('ucfc_restaurant_name', get_bloginfo('name'));
                                $system_prompt = "You are a helpful AI assistant for {$restaurant_name}, a delicious restaurant serving amazing food.

Your role:
- Help customers explore the menu and make great choices
- Answer questions about ingredients, allergens, and preparation
- Take orders and add items to their cart
- Suggest popular items and combos
- Provide information about delivery, hours, and location
- Be friendly, patient, and enthusiastic about our food

Guidelines:
- Always be warm and welcoming
- Use natural, conversational language
- Proactively suggest sides, drinks, and desserts
- Mention special offers when relevant
- If you don't know something, be honest and offer to help another way
- Never make up menu items or prices - only use real menu data";
                            }
                            ?>
                            <textarea id="ucfc_ai_system_prompt" name="ucfc_ai_system_prompt" 
                                      rows="15" class="large-text code"><?php echo esc_textarea($system_prompt); ?></textarea>
                            <p class="description">
                                Define how your AI behaves. Use <code>{restaurant_name}</code>, <code>{menu}</code>, <code>{specials}</code> as placeholders.
                                <br><strong>Tip:</strong> Be specific about tone, knowledge boundaries, and when to upsell!
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Knowledge Base</th>
                        <td>
                            <div class="ucfc-knowledge-base">
                                <label>
                                    <input type="checkbox" checked disabled />
                                    <strong>✓ Full Menu Access</strong> - AI knows all your menu items, prices, descriptions
                                </label>
                                <label>
                                    <input type="checkbox" checked disabled />
                                    <strong>✓ Restaurant Info</strong> - Hours, location, contact details
                                </label>
                                <label>
                                    <input type="checkbox" checked disabled />
                                    <strong>✓ Special Offers</strong> - Current promotions and deals
                                </label>
                                <label>
                                    <input type="checkbox" checked disabled />
                                    <strong>✓ Customer Reviews</strong> - Social proof and testimonials
                                </label>
                            </div>
                            <p class="description">Your AI automatically knows everything in your WordPress database!</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Functions Tab -->
            <div id="tab-functions" class="ucfc-ai-tab-content">
                <h2>Function Calling & Capabilities</h2>
                <p class="description">Enable AI actions that interact with your restaurant system.</p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enabled Functions</th>
                        <td>
                            <div class="ucfc-functions-list">
                                <label class="ucfc-function-card">
                                    <input type="checkbox" name="ucfc_ai_enable_menu_search" value="1" 
                                           <?php checked(get_option('ucfc_ai_enable_menu_search', '1'), '1'); ?> />
                                    <div class="function-content">
                                        <h4>🔍 Menu Search</h4>
                                        <p>AI can search menu by category, dietary restrictions, price range</p>
                                        <code>get_menu_items(category, filters)</code>
                                    </div>
                                </label>
                                
                                <label class="ucfc-function-card">
                                    <input type="checkbox" name="ucfc_ai_enable_order_placement" value="1" 
                                           <?php checked(get_option('ucfc_ai_enable_order_placement', '1'), '1'); ?> />
                                    <div class="function-content">
                                        <h4>🛒 Order Placement</h4>
                                        <p>AI can add items to cart, modify quantities, apply promo codes</p>
                                        <code>add_to_cart(item_id, quantity, customizations)</code>
                                    </div>
                                </label>
                                
                                <label class="ucfc-function-card">
                                    <input type="checkbox" name="ucfc_ai_enable_recommendations" value="1" 
                                           <?php checked(get_option('ucfc_ai_enable_recommendations', '1'), '1'); ?> />
                                    <div class="function-content">
                                        <h4>💡 Smart Recommendations</h4>
                                        <p>AI suggests items based on preferences, past orders, popular combos</p>
                                        <code>recommend_items(preferences, dietary_needs)</code>
                                    </div>
                                </label>
                                
                                <label class="ucfc-function-card">
                                    <input type="checkbox" name="ucfc_ai_enable_reservations" value="1" 
                                           <?php checked(get_option('ucfc_ai_enable_reservations', '0'), '1'); ?> />
                                    <div class="function-content">
                                        <h4>📅 Reservations</h4>
                                        <p>AI can check availability and book tables</p>
                                        <code>check_availability(date, time, party_size)</code>
                                        <span class="badge">Coming Soon</span>
                                    </div>
                                </label>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_custom_functions">Custom Functions (JSON)</label>
                        </th>
                        <td>
                            <textarea id="ucfc_ai_custom_functions" name="ucfc_ai_custom_functions" 
                                      rows="10" class="large-text code"><?php 
                                echo esc_textarea(get_option('ucfc_ai_custom_functions', '')); 
                            ?></textarea>
                            <p class="description">
                                Advanced: Define custom function calling schemas in JSON format.
                                <a href="#" class="ucfc-show-example">Show Example</a> | 
                                <button type="button" id="ucfc-validate-json" class="button">✓ Validate JSON</button>
                            </p>
                            <div id="ucfc-validation-result" class="notice" style="display:none;margin-top:10px;"></div>
                            <div class="ucfc-code-example" style="display:none;">
                                <h4>Example Custom Function:</h4>
                                <pre><code>[{
  "name": "check_delivery_time",
  "description": "Estimate delivery time to customer address",
  "parameters": {
    "type": "object",
    "properties": {
      "zipcode": {
        "type": "string",
        "description": "Customer's ZIP code"
      }
    },
    "required": ["zipcode"]
  }
},
{
  "name": "check_allergens",
  "description": "Check menu items for allergens",
  "parameters": {
    "type": "object",
    "properties": {
      "allergen": {
        "type": "string",
        "description": "Allergen name (nuts, dairy, gluten, etc.)"
      }
    },
    "required": ["allergen"]
  }
}]</code></pre>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Import Assistant Tab -->
            <div id="tab-import" class="ucfc-ai-tab-content">
                <h2>Import OpenAI Assistant</h2>
                <p class="description">Already have a trained assistant on OpenAI? Import it here!</p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ucfc_ai_use_assistant">Use OpenAI Assistant</label>
                        </th>
                        <td>
                            <label class="ucfc-toggle-switch">
                                <input type="checkbox" id="ucfc_ai_use_assistant" name="ucfc_ai_use_assistant" value="1" 
                                       <?php checked(get_option('ucfc_ai_use_assistant', '0'), '1'); ?> />
                                <span class="ucfc-toggle-slider"></span>
                            </label>
                            <p class="description">Use pre-configured OpenAI Assistant instead of custom configuration</p>
                        </td>
                    </tr>
                    
                    <tr class="assistant-import-fields">
                        <th scope="row">
                            <label for="ucfc_ai_assistant_id">Assistant ID</label>
                        </th>
                        <td>
                            <input type="text" id="ucfc_ai_assistant_id" name="ucfc_ai_assistant_id" 
                                   value="<?php echo esc_attr(get_option('ucfc_ai_assistant_id', '')); ?>" 
                                   class="regular-text code" 
                                   placeholder="asst_..." />
                            <button type="button" class="button button-secondary" id="ucfc-fetch-assistant">
                                Fetch Assistant Details
                            </button>
                            <p class="description">
                                Find your Assistant ID in <a href="https://platform.openai.com/assistants" target="_blank">OpenAI Dashboard</a>
                            </p>
                        </td>
                    </tr>
                    
                    <tr class="assistant-import-fields">
                        <th scope="row">Assistant Info</th>
                        <td>
                            <div id="ucfc-assistant-info" class="ucfc-assistant-info">
                                <p class="description">Enter Assistant ID and click "Fetch Assistant Details" to view info</p>
                            </div>
                        </td>
                    </tr>
                </table>
                
                <!-- Enhanced Assistant Configuration -->
                <div id="ucfc-assistant-config" style="display:none; margin-top: 30px;">
                    <h3>📋 Assistant Configuration</h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label>Assistant Name</label>
                            </th>
                            <td>
                                <input type="text" id="ucfc_assistant_name" class="regular-text" readonly />
                                <p class="description">Name of the imported assistant (read-only)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label>Model</label>
                            </th>
                            <td>
                                <input type="text" id="ucfc_assistant_model" class="regular-text" readonly />
                                <p class="description">AI model used by this assistant (read-only)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ucfc_assistant_instructions_edit">Instructions</label>
                            </th>
                            <td>
                                <textarea id="ucfc_assistant_instructions_edit" name="ucfc_assistant_instructions_edit" 
                                          rows="8" class="large-text code"><?php echo esc_textarea(get_option('ucfc_assistant_instructions_edit', '')); ?></textarea>
                                <p class="description">Edit assistant instructions. This overrides the imported instructions.</p>
                                <button type="button" class="button button-secondary" id="ucfc-reset-instructions">
                                    Reset to Original
                                </button>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ucfc_assistant_tools_edit">Tools & Functions</label>
                            </th>
                            <td>
                                <textarea id="ucfc_assistant_tools_edit" name="ucfc_assistant_tools_edit" 
                                          rows="12" class="large-text code"><?php echo esc_textarea(get_option('ucfc_assistant_tools_edit', '')); ?></textarea>
                                <p class="description">Edit tools configuration (JSON format). Merge OpenAI tools with custom functions.</p>
                                <button type="button" class="button button-secondary" id="ucfc-validate-tools">
                                    ✓ Validate JSON
                                </button>
                                <button type="button" class="button button-secondary" id="ucfc-reset-tools">
                                    Reset to Original
                                </button>
                                <span id="ucfc-tools-validation" style="margin-left: 10px;"></span>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ucfc_assistant_custom_functions">Custom Platform Functions</label>
                            </th>
                            <td>
                                <textarea id="ucfc_assistant_custom_functions" name="ucfc_assistant_custom_functions" 
                                          rows="10" class="large-text code"><?php echo esc_textarea(get_option('ucfc_assistant_custom_functions', '')); ?></textarea>
                                <p class="description">Add custom restaurant platform functions (JSON array). These will be merged with imported tools.</p>
                                <button type="button" class="button button-primary" id="ucfc-merge-functions">
                                    🔀 Merge with Tools
                                </button>
                                <button type="button" class="button button-secondary" id="ucfc-load-platform-functions">
                                    📥 Load Platform Functions
                                </button>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label>Configuration Diff</label>
                            </th>
                            <td>
                                <div id="ucfc-config-diff" class="ucfc-config-diff">
                                    <p class="description">Changes you've made to the imported assistant:</p>
                                    <ul id="ucfc-diff-list"></ul>
                                </div>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="ucfc-assistant-actions" style="margin-top: 20px;">
                        <button type="button" class="button button-primary" id="ucfc-save-assistant-config">
                            💾 Save Configuration
                        </button>
                        <button type="button" class="button button-secondary" id="ucfc-sync-assistant">
                            🔄 Sync from OpenAI
                        </button>
                        <button type="button" class="button button-secondary" id="ucfc-view-full-json">
                            📄 View Full JSON
                        </button>
                    </div>
                </div>
                
                <!-- Full JSON Viewer Modal -->
                <div id="ucfc-json-modal" class="ucfc-modal" style="display:none;">
                    <div class="ucfc-modal-content">
                        <span class="ucfc-modal-close">&times;</span>
                        <h3>Full Assistant JSON Configuration</h3>
                        <textarea id="ucfc-full-json" readonly class="large-text code" rows="30"></textarea>
                        <div style="margin-top: 15px;">
                            <button type="button" class="button button-secondary" id="ucfc-copy-json">
                                📋 Copy to Clipboard
                            </button>
                            <button type="button" class="button button-secondary ucfc-modal-close">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Test Chat Tab -->
            <div id="tab-test" class="ucfc-ai-tab-content">
                <h2>Test Your AI Assistant</h2>
                <p class="description">Chat with your AI to see how it responds. This uses your current configuration.</p>
                
                <div class="ucfc-test-chat-container">
                    <div class="ucfc-test-chat-messages" id="ucfc-test-chat-messages">
                        <div class="chat-message assistant">
                            <div class="message-avatar">🤖</div>
                            <div class="message-content">
                                <p>Hi! I'm your AI assistant. Ask me anything about the menu, place an order, or test my capabilities!</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ucfc-test-chat-input">
                        <input type="text" id="ucfc-test-chat-input" placeholder="Type a message to test your AI..." />
                        <button type="button" class="button button-primary" id="ucfc-test-chat-send">
                            Send
                        </button>
                        <button type="button" class="button button-secondary" id="ucfc-test-chat-clear">
                            Clear
                        </button>
                    </div>
                    
                    <div class="ucfc-test-chat-info">
                        <p><strong>💡 Try asking:</strong></p>
                        <ul>
                            <li>"What's on the menu?"</li>
                            <li>"I want spicy chicken with fries"</li>
                            <li>"What do you recommend for a family of 4?"</li>
                            <li>"Do you have vegetarian options?"</li>
                            <li>"Add 2 chicken buckets to my cart"</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <p class="submit">
                <button type="submit" name="ucfc_ai_save_settings" class="button button-primary button-hero">
                    💾 Save AI Configuration
                </button>
                <button type="button" class="button button-secondary button-hero" id="ucfc-test-connection">
                    🔌 Test API Connection
                </button>
            </p>
        </form>
    </div>
    
    <?php ucfc_ai_assistant_styles(); ?>
    <?php ucfc_ai_assistant_scripts(); ?>
    <?php
}

/**
 * AI Assistant Admin Styles
 */
function ucfc_ai_assistant_styles() {
    ?>
    <style>
        .ucfc-ai-assistant-page { max-width: 1400px; }
        
        /* Tab Navigation */
        .ucfc-ai-tabs { margin: 25px 0; border-bottom: 1px solid #ccc; }
        .ucfc-ai-tab-content { display: none; padding: 30px 0; }
        .ucfc-ai-tab-content.active { display: block; }
        
        /* Model Selector Cards */
        .ucfc-model-selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 15px; }
        .ucfc-model-card { position: relative; padding: 20px; background: #f9f9f9; border: 3px solid #ddd; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; }
        .ucfc-model-card:hover { border-color: #C92A2A; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .ucfc-model-card input[type="radio"] { position: absolute; opacity: 0; }
        .ucfc-model-card.selected { border-color: #C92A2A; background: #fff; box-shadow: 0 8px 25px rgba(201,42,42,0.2); }
        .model-card-content { text-align: center; }
        .model-logo { font-size: 48px; margin-bottom: 15px; }
        .model-icon { font-size: 48px; }
        .model-card-content h3 { margin: 10px 0 5px; font-size: 20px; font-weight: 600; }
        .model-card-content p { color: #666; font-size: 13px; margin: 0 0 10px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-badge.connected { background: #4CAF50; color: white; }
        .status-badge.disconnected { background: #ccc; color: #666; }
        
        /* Toggle Switch */
        .ucfc-toggle-switch { position: relative; display: inline-block; width: 60px; height: 30px; }
        .ucfc-toggle-switch input { opacity: 0; width: 0; height: 0; }
        .ucfc-toggle-slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; transition: .4s; border-radius: 30px; }
        .ucfc-toggle-slider:before { position: absolute; content: ""; height: 22px; width: 22px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        .ucfc-toggle-switch input:checked + .ucfc-toggle-slider { background-color: #4CAF50; }
        .ucfc-toggle-switch input:checked + .ucfc-toggle-slider:before { transform: translateX(30px); }
        
        /* Slider */
        .ucfc-slider { width: 300px; }
        .ucfc-slider-value { display: inline-block; margin-left: 15px; font-weight: 600; font-size: 16px; color: #C92A2A; min-width: 40px; }
        
        /* Functions List */
        .ucfc-functions-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .ucfc-function-card { display: block; padding: 20px; background: #f9f9f9; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; transition: all 0.3s ease; }
        .ucfc-function-card:hover { border-color: #C92A2A; transform: translateY(-2px); }
        .ucfc-function-card input[type="checkbox"] { float: left; margin: 5px 15px 0 0; transform: scale(1.3); }
        .ucfc-function-card input[type="checkbox"]:checked ~ .function-content h4 { color: #C92A2A; }
        .function-content h4 { margin: 0 0 8px; font-size: 16px; }
        .function-content p { margin: 0 0 8px; color: #666; font-size: 13px; }
        .function-content code { background: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block; margin-top: 5px; }
        .function-content .badge { background: #F0B429; color: #1a1a1a; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; }
        
        /* Knowledge Base */
        .ucfc-knowledge-base label { display: block; padding: 12px; background: #f0f7ff; border-left: 4px solid #4CAF50; margin-bottom: 10px; border-radius: 4px; }
        
        /* Test Chat */
        .ucfc-test-chat-container { background: #f9f9f9; border-radius: 15px; padding: 20px; max-width: 900px; }
        .ucfc-test-chat-messages { background: white; border-radius: 10px; padding: 20px; min-height: 400px; max-height: 500px; overflow-y: auto; margin-bottom: 20px; }
        .chat-message { display: flex; gap: 15px; margin-bottom: 20px; animation: fadeIn 0.3s ease; }
        .chat-message.user { flex-direction: row-reverse; }
        .message-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #C92A2A, #F0B429); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .chat-message.user .message-avatar { background: linear-gradient(135deg, #667eea, #764ba2); }
        .message-content { background: #f0f0f0; padding: 12px 18px; border-radius: 15px; max-width: 70%; }
        .chat-message.user .message-content { background: #C92A2A; color: white; }
        .ucfc-test-chat-input { display: flex; gap: 10px; }
        .ucfc-test-chat-input input { flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; }
        .ucfc-test-chat-info { margin-top: 20px; padding: 15px; background: #fff; border-left: 4px solid #F0B429; border-radius: 5px; }
        .ucfc-test-chat-info ul { margin: 10px 0; padding-left: 20px; }
        .ucfc-test-chat-info li { margin: 5px 0; color: #666; }
        
        /* Assistant Info */
        .ucfc-assistant-info { padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .ucfc-assistant-info.loaded { background: #f0f7ff; border-left: 4px solid #4CAF50; }
        
        /* Assistant Configuration Panel */
        #ucfc-assistant-config { background: #f9f9f9; padding: 25px; border-radius: 12px; margin-top: 30px; }
        #ucfc-assistant-config h3 { color: #C92A2A; margin-bottom: 20px; font-size: 18px; }
        #ucfc-assistant-config textarea.code { font-family: 'Consolas', 'Monaco', monospace; font-size: 13px; background: #2d2d2d; color: #f8f8f2; border-radius: 6px; }
        #ucfc-assistant-config .description { font-size: 13px; color: #666; }
        
        /* Config Diff */
        .ucfc-config-diff { background: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
        .ucfc-config-diff ul { margin: 10px 0 0 20px; }
        .ucfc-config-diff li { padding: 5px 0; font-size: 14px; }
        
        /* Assistant Actions */
        .ucfc-assistant-actions { padding: 20px; background: white; border-radius: 8px; display: flex; gap: 10px; flex-wrap: wrap; }
        .ucfc-assistant-actions .button-primary { background: #C92A2A; border-color: #C92A2A; text-shadow: none; box-shadow: none; }
        .ucfc-assistant-actions .button-primary:hover { background: #A52222; border-color: #A52222; }
        
        /* JSON Modal */
        .ucfc-modal { display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.7); }
        .ucfc-modal-content { background-color: #fefefe; margin: 3% auto; padding: 30px; border-radius: 12px; width: 90%; max-width: 1200px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        .ucfc-modal-close { color: #aaa; float: right; font-size: 32px; font-weight: bold; cursor: pointer; line-height: 20px; }
        .ucfc-modal-close:hover { color: #C92A2A; }
        #ucfc-full-json { width: 100%; background: #2d2d2d; color: #f8f8f2; padding: 20px; border-radius: 8px; border: none; font-family: 'Consolas', 'Monaco', monospace; font-size: 13px; }
        
        /* Code Example */
        .ucfc-code-example { margin-top: 15px; }
        .ucfc-code-example pre { background: #2d2d2d; color: #f8f8f2; padding: 20px; border-radius: 8px; overflow-x: auto; }
        
        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .ucfc-model-selector { grid-template-columns: 1fr; }
            .ucfc-functions-list { grid-template-columns: 1fr; }
        }
    </style>
    <?php
}

/**
 * AI Assistant Admin Scripts
 */
function ucfc_ai_assistant_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Tab Switching
        $('.ucfc-ai-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();
            const tab = $(this).data('tab');
            
            $('.ucfc-ai-tabs .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            $('.ucfc-ai-tab-content').removeClass('active');
            $(`#tab-${tab}`).addClass('active');
        });
        
        // Model Provider Selection
        $('input[name="ucfc_ai_model_provider"]').on('change', function() {
            const provider = $(this).val();
            
            // Update card styling
            $('.ucfc-model-card').removeClass('selected');
            $(this).closest('.ucfc-model-card').addClass('selected');
            
            // Show/hide provider-specific settings
            $('.provider-settings').hide();
            $(`.${provider}-settings`).show();
        });
        
        // Toggle API Key Visibility
        $('.toggle-key-visibility').on('click', function() {
            const $input = $(this).prev('input');
            const type = $input.attr('type');
            
            if (type === 'password') {
                $input.attr('type', 'text');
                $(this).find('.dashicons').removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                $input.attr('type', 'password');
                $(this).find('.dashicons').removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        });
        
        // Temperature Slider
        $('#ucfc_ai_temperature').on('input', function() {
            $(this).next('.ucfc-slider-value').text($(this).val());
        });
        
        // Validate JSON
        $('#ucfc-validate-json').on('click', function() {
            const json = $('#ucfc_ai_custom_functions').val().trim();
            const $result = $('#ucfc-validation-result');
            
            if (!json) {
                $result.removeClass('notice-success notice-error')
                       .addClass('notice-warning')
                       .html('<p>⚠️ No JSON to validate</p>')
                       .slideDown();
                return;
            }
            
            const $btn = $(this);
            $btn.prop('disabled', true).text('Validating...');
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_validate_function_json',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>',
                    json: json
                },
                success: function(response) {
                    if (response.success) {
                        $result.removeClass('notice-warning notice-error')
                               .addClass('notice-success')
                               .html('<p>' + response.data.message + '</p>')
                               .slideDown();
                    } else {
                        let errorHtml = '<p><strong>' + response.data.message + '</strong></p><ul>';
                        response.data.errors.forEach(function(error) {
                            errorHtml += '<li>' + error + '</li>';
                        });
                        errorHtml += '</ul>';
                        
                        $result.removeClass('notice-success notice-warning')
                               .addClass('notice-error')
                               .html(errorHtml)
                               .slideDown();
                    }
                    $btn.prop('disabled', false).text('✓ Validate JSON');
                },
                error: function() {
                    $result.removeClass('notice-success notice-warning')
                           .addClass('notice-error')
                           .html('<p>❌ Validation error occurred</p>')
                           .slideDown();
                    $btn.prop('disabled', false).text('✓ Validate JSON');
                }
            });
        });
        
        // Clear Cache
        $('#clear-cache-btn').on('click', function() {
            if (!confirm('Clear all cached AI responses?')) return;
            
            const $btn = $(this);
            $btn.prop('disabled', true).text('Clearing...');
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_clear_cache',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>'
                },
                success: function(response) {
                    alert('✅ Cache cleared successfully!');
                    location.reload();
                },
                error: function() {
                    alert('❌ Failed to clear cache');
                    $btn.prop('disabled', false).text('🗑️ Clear Cache');
                }
            });
        });
        
        // Show Code Example
        $('.ucfc-show-example').on('click', function(e) {
            e.preventDefault();
            $('.ucfc-code-example').slideToggle();
        });
        
        // Test Chat
        let chatHistory = [];
        
        function addMessage(role, content) {
            const avatar = role === 'user' ? '👤' : '🤖';
            const messageHtml = `
                <div class="chat-message ${role}">
                    <div class="message-avatar">${avatar}</div>
                    <div class="message-content">
                        <p>${content}</p>
                    </div>
                </div>
            `;
            
            $('#ucfc-test-chat-messages').append(messageHtml);
            $('#ucfc-test-chat-messages').scrollTop($('#ucfc-test-chat-messages')[0].scrollHeight);
            
            chatHistory.push({ role, content });
        }
        
        function sendTestMessage() {
            const message = $('#ucfc-test-chat-input').val().trim();
            if (!message) return;
            
            addMessage('user', message);
            $('#ucfc-test-chat-input').val('').prop('disabled', true);
            
            // Show typing indicator
            const typingHtml = `
                <div class="chat-message assistant typing-indicator">
                    <div class="message-avatar">🤖</div>
                    <div class="message-content">
                        <p>Thinking...</p>
                    </div>
                </div>
            `;
            $('#ucfc-test-chat-messages').append(typingHtml);
            $('#ucfc-test-chat-messages').scrollTop($('#ucfc-test-chat-messages')[0].scrollHeight);
            
            // AJAX call to AI
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_test_ai_chat',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>',
                    message: message,
                    history: chatHistory
                },
                success: function(response) {
                    $('.typing-indicator').remove();
                    
                    if (response.success) {
                        addMessage('assistant', response.data.reply);
                    } else {
                        addMessage('assistant', '❌ Error: ' + response.data.message);
                    }
                    
                    $('#ucfc-test-chat-input').prop('disabled', false).focus();
                },
                error: function() {
                    $('.typing-indicator').remove();
                    addMessage('assistant', '❌ Connection error. Please check your API key and try again.');
                    $('#ucfc-test-chat-input').prop('disabled', false).focus();
                }
            });
        }
        
        $('#ucfc-test-chat-send').on('click', sendTestMessage);
        $('#ucfc-test-chat-input').on('keypress', function(e) {
            if (e.which === 13) sendTestMessage();
        });
        
        $('#ucfc-test-chat-clear').on('click', function() {
            chatHistory = [];
            $('#ucfc-test-chat-messages').html(`
                <div class="chat-message assistant">
                    <div class="message-avatar">🤖</div>
                    <div class="message-content">
                        <p>Chat cleared! Ask me anything to continue testing.</p>
                    </div>
                </div>
            `);
        });
        
        // Test API Connection
        $('#ucfc-test-connection').on('click', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).text('🔄 Testing...');
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_test_api_connection',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ Success! API connection is working.\n\n' + response.data.message);
                    } else {
                        alert('❌ Connection Failed\n\n' + response.data.message);
                    }
                    $btn.prop('disabled', false).text('🔌 Test API Connection');
                },
                error: function() {
                    alert('❌ Connection error. Please check your settings.');
                    $btn.prop('disabled', false).text('🔌 Test API Connection');
                }
            });
        });
        
        // Store original assistant data
        let originalAssistant = null;
        
        // Fetch OpenAI Assistant
        function fetchAssistant(assistantId) {
            if (!assistantId) {
                alert('Please enter an Assistant ID');
                return;
            }
            
            const $btn = $('#ucfc-fetch-assistant');
            $btn.prop('disabled', true).text('Fetching...');
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_fetch_openai_assistant',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>',
                    assistant_id: assistantId
                },
                success: function(response) {
                    if (response.success) {
                        const assistant = response.data.assistant;
                        originalAssistant = assistant;
                        
                        // Update info card
                        $('#ucfc-assistant-info').addClass('loaded').html(`
                            <h4>✓ Assistant Found</h4>
                            <p><strong>Name:</strong> ${assistant.name}</p>
                            <p><strong>Model:</strong> ${assistant.model}</p>
                            <p><strong>Instructions:</strong> ${assistant.instructions.substring(0, 200)}...</p>
                            <p><strong>Tools:</strong> ${assistant.tools.length} enabled</p>
                        `);
                        
                        // Populate editable fields
                        $('#ucfc_assistant_name').val(assistant.name);
                        $('#ucfc_assistant_model').val(assistant.model);
                        $('#ucfc_assistant_instructions_edit').val(assistant.instructions);
                        $('#ucfc_assistant_tools_edit').val(JSON.stringify(assistant.tools, null, 2));
                        
                        // Show configuration panel
                        $('#ucfc-assistant-config').slideDown();
                        
                        // Update diff
                        updateConfigDiff();
                    } else {
                        alert('Failed to fetch assistant: ' + response.data.message);
                    }
                    $btn.prop('disabled', false).text('Fetch Assistant Details');
                },
                error: function() {
                    alert('Connection error');
                    $btn.prop('disabled', false).text('Fetch Assistant Details');
                }
            });
        }
        
        $('#ucfc-fetch-assistant').on('click', function() {
            const assistantId = $('#ucfc_ai_assistant_id').val().trim();
            fetchAssistant(assistantId);
        });
        
        // Sync from OpenAI (re-fetch)
        $('#ucfc-sync-assistant').on('click', function() {
            if (confirm('This will reload the assistant from OpenAI. Any unsaved changes will be lost. Continue?')) {
                const assistantId = $('#ucfc_ai_assistant_id').val().trim();
                fetchAssistant(assistantId);
            }
        });
        
        // Reset Instructions
        $('#ucfc-reset-instructions').on('click', function() {
            if (originalAssistant && confirm('Reset instructions to original?')) {
                $('#ucfc_assistant_instructions_edit').val(originalAssistant.instructions);
                updateConfigDiff();
            }
        });
        
        // Reset Tools
        $('#ucfc-reset-tools').on('click', function() {
            if (originalAssistant && confirm('Reset tools to original?')) {
                $('#ucfc_assistant_tools_edit').val(JSON.stringify(originalAssistant.tools, null, 2));
                updateConfigDiff();
            }
        });
        
        // Validate Tools JSON
        $('#ucfc-validate-tools').on('click', function() {
            const toolsJson = $('#ucfc_assistant_tools_edit').val();
            try {
                const tools = JSON.parse(toolsJson);
                if (!Array.isArray(tools)) {
                    throw new Error('Tools must be an array');
                }
                $('#ucfc-tools-validation').html('<span style="color: green;">✓ Valid JSON</span>');
                setTimeout(() => $('#ucfc-tools-validation').html(''), 3000);
            } catch (e) {
                $('#ucfc-tools-validation').html('<span style="color: red;">✗ Invalid: ' + e.message + '</span>');
            }
        });
        
        // Load Platform Functions
        $('#ucfc-load-platform-functions').on('click', function() {
            const platformFunctions = [
                {
                    "type": "function",
                    "function": {
                        "name": "get_menu_items",
                        "description": "Get menu items from the restaurant. Can filter by category or search term.",
                        "parameters": {
                            "type": "object",
                            "properties": {
                                "category": {
                                    "type": "string",
                                    "description": "Menu category slug (chicken, sides, drinks, combos)"
                                },
                                "search": {
                                    "type": "string",
                                    "description": "Search term to filter items"
                                }
                            }
                        }
                    }
                },
                {
                    "type": "function",
                    "function": {
                        "name": "add_to_cart",
                        "description": "Add menu item to customer's cart",
                        "parameters": {
                            "type": "object",
                            "properties": {
                                "item_id": {
                                    "type": "integer",
                                    "description": "Menu item ID to add"
                                },
                                "quantity": {
                                    "type": "integer",
                                    "description": "Quantity to add (default: 1)"
                                }
                            },
                            "required": ["item_id"]
                        }
                    }
                },
                {
                    "type": "function",
                    "function": {
                        "name": "get_cart_contents",
                        "description": "Get current items in customer's shopping cart",
                        "parameters": {
                            "type": "object",
                            "properties": {}
                        }
                    }
                }
            ];
            
            $('#ucfc_assistant_custom_functions').val(JSON.stringify(platformFunctions, null, 2));
        });
        
        // Merge Custom Functions with Tools
        $('#ucfc-merge-functions').on('click', function() {
            try {
                const toolsJson = $('#ucfc_assistant_tools_edit').val();
                const customJson = $('#ucfc_assistant_custom_functions').val();
                
                let tools = toolsJson ? JSON.parse(toolsJson) : [];
                let customFuncs = customJson ? JSON.parse(customJson) : [];
                
                if (!Array.isArray(tools)) tools = [];
                if (!Array.isArray(customFuncs)) customFuncs = [];
                
                // Merge (remove duplicates by function name)
                const functionNames = new Set(tools.map(t => t.function?.name).filter(Boolean));
                customFuncs.forEach(cf => {
                    if (cf.function && !functionNames.has(cf.function.name)) {
                        tools.push(cf);
                    }
                });
                
                $('#ucfc_assistant_tools_edit').val(JSON.stringify(tools, null, 2));
                alert('✓ Merged ' + customFuncs.length + ' custom functions with tools');
                updateConfigDiff();
            } catch (e) {
                alert('Error merging: ' + e.message);
            }
        });
        
        // Update Config Diff
        function updateConfigDiff() {
            if (!originalAssistant) return;
            
            const currentInstructions = $('#ucfc_assistant_instructions_edit').val();
            const currentTools = $('#ucfc_assistant_tools_edit').val();
            
            const changes = [];
            
            if (currentInstructions !== originalAssistant.instructions) {
                changes.push('<li>✏️ Instructions modified</li>');
            }
            
            try {
                const toolsObj = JSON.parse(currentTools);
                if (JSON.stringify(toolsObj) !== JSON.stringify(originalAssistant.tools)) {
                    changes.push('<li>🔧 Tools configuration changed</li>');
                }
            } catch (e) {
                changes.push('<li>⚠️ Tools JSON invalid</li>');
            }
            
            if (changes.length === 0) {
                $('#ucfc-diff-list').html('<li style="color: green;">✓ No changes (using original configuration)</li>');
            } else {
                $('#ucfc-diff-list').html(changes.join(''));
            }
        }
        
        // Track changes
        $('#ucfc_assistant_instructions_edit, #ucfc_assistant_tools_edit').on('input', updateConfigDiff);
        
        // Save Assistant Configuration
        $('#ucfc-save-assistant-config').on('click', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');
            
            const config = {
                assistant_id: $('#ucfc_ai_assistant_id').val(),
                name: $('#ucfc_assistant_name').val(),
                model: $('#ucfc_assistant_model').val(),
                instructions: $('#ucfc_assistant_instructions_edit').val(),
                tools: $('#ucfc_assistant_tools_edit').val(),
                custom_functions: $('#ucfc_assistant_custom_functions').val()
            };
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_save_assistant_config',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>',
                    config: config
                },
                success: function(response) {
                    if (response.success) {
                        alert('✓ Assistant configuration saved!');
                    } else {
                        alert('Failed to save: ' + response.data.message);
                    }
                    $btn.prop('disabled', false).text('💾 Save Configuration');
                },
                error: function() {
                    alert('Connection error');
                    $btn.prop('disabled', false).text('💾 Save Configuration');
                }
            });
        });
        
        // View Full JSON
        $('#ucfc-view-full-json').on('click', function() {
            if (!originalAssistant) {
                alert('No assistant loaded');
                return;
            }
            
            const fullConfig = {
                ...originalAssistant,
                custom_instructions: $('#ucfc_assistant_instructions_edit').val(),
                custom_tools: $('#ucfc_assistant_tools_edit').val(),
                platform_functions: $('#ucfc_assistant_custom_functions').val()
            };
            
            $('#ucfc-full-json').val(JSON.stringify(fullConfig, null, 2));
            $('#ucfc-json-modal').fadeIn();
        });
        
        // Copy JSON
        $('#ucfc-copy-json').on('click', function() {
            const json = $('#ucfc-full-json').val();
            navigator.clipboard.writeText(json).then(() => {
                alert('✓ Copied to clipboard');
            }).catch(() => {
                alert('Failed to copy');
            });
        });
        
        // Modal close
        $('.ucfc-modal-close').on('click', function() {
            $('#ucfc-json-modal').fadeOut();
        });
        
        $(window).on('click', function(e) {
            if ($(e.target).hasClass('ucfc-modal')) {
                $('.ucfc-modal').fadeOut();
            }
        });
    });
    </script>
    <?php
}
