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

// Handle service URL save
add_action('wp_ajax_save_ai_service_url', 'restaurant_save_ai_service_url');

// Handle file upload
add_action('wp_ajax_upload_kb_file', 'restaurant_upload_kb_file');

// Handle category creation
add_action('wp_ajax_add_kb_category', 'restaurant_add_kb_category');

// Handle file deletion
add_action('wp_ajax_delete_kb_file', 'restaurant_delete_kb_file');

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
 * Handle AI service URL configuration
 */
function restaurant_save_ai_service_url() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_settings_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    $url = esc_url_raw($_POST['url'] ?? '');

    if (!$url) {
        wp_send_json_error(['message' => 'Invalid URL']);
    }

    update_option('ai_service_url', $url);
    wp_send_json_success(['message' => 'Service URL saved']);
}

/**
 * Handle knowledge base file upload
 */
function restaurant_upload_kb_file() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_settings_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    if (empty($_FILES['file'])) {
        wp_send_json_error(['message' => 'No file provided']);
    }

    $category = sanitize_text_field($_POST['category'] ?? 'general');
    $uploaded_file = $_FILES['file'];

    // Validate file
    $allowed_types = ['txt', 'pdf', 'csv', 'doc', 'docx'];
    $file_type = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_type, $allowed_types)) {
        wp_send_json_error(['message' => 'File type not allowed']);
    }

    if ($uploaded_file['size'] > 10 * 1024 * 1024) { // 10MB limit
        wp_send_json_error(['message' => 'File too large']);
    }

    // Create upload directory
    $upload_dir = wp_upload_dir();
    $kb_dir = $upload_dir['basedir'] . '/restaurant-kb/';
    if (!is_dir($kb_dir)) {
        wp_mkdir_p($kb_dir);
    }

    // Move file
    $filename = sanitize_file_name($uploaded_file['name']);
    $file_path = $kb_dir . time() . '_' . $filename;

    if (!move_uploaded_file($uploaded_file['tmp_name'], $file_path)) {
        wp_send_json_error(['message' => 'Failed to upload file']);
    }

    // Index document with RAG engine
    global $restaurant_rag_engine;
    if ($restaurant_rag_engine) {
        $result = $restaurant_rag_engine->upload_document($file_path, $category, $filename);
        
        if (!$result['success']) {
            wp_send_json_error(['message' => $result['error']]);
        }

        wp_send_json_success([
            'message' => 'File uploaded and indexed successfully',
            'document_id' => $result['document_id'],
            'filename' => $filename,
            'chunks' => $result['chunks']
        ]);
    }

    wp_send_json_error(['message' => 'RAG engine not initialized']);
}

/**
 * Handle category creation
 */
function restaurant_add_kb_category() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_settings_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    $name = sanitize_text_field($_POST['name'] ?? '');
    $description = sanitize_text_field($_POST['description'] ?? '');
    $icon = sanitize_text_field($_POST['icon'] ?? '📁');

    if (empty($name)) {
        wp_send_json_error(['message' => 'Category name required']);
    }

    global $restaurant_rag_engine;
    if ($restaurant_rag_engine) {
        $category_id = $restaurant_rag_engine->add_category($name, $description, $icon);
        wp_send_json_success([
            'message' => 'Category created successfully',
            'category_id' => $category_id
        ]);
    }

    wp_send_json_error(['message' => 'RAG engine not initialized']);
}

/**
 * Handle file deletion
 */
function restaurant_delete_kb_file() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_settings_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    $document_id = intval($_POST['document_id'] ?? 0);

    if (!$document_id) {
        wp_send_json_error(['message' => 'Invalid document ID']);
    }

    global $restaurant_rag_engine;
    if ($restaurant_rag_engine) {
        $result = $restaurant_rag_engine->delete_document($document_id);
        
        if ($result) {
            wp_send_json_success(['message' => 'Document deleted successfully']);
        }
    }

    wp_send_json_error(['message' => 'Failed to delete document']);
}

/**
 * Render AI Settings Page
 */
function restaurant_render_ai_settings_tab() {
    $llm_provider = get_option('restaurant_llm_provider', 'claude');
    $api_keys = get_option('restaurant_api_keys', []);
    $nonce = wp_create_nonce('ai_settings_nonce');

    // Get RAG engine for categories and documents
    global $restaurant_rag_engine;
    $categories = $restaurant_rag_engine ? $restaurant_rag_engine->get_categories() : [];
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

        <!-- Service Configuration Section -->
        <div class="ai-section" style="border-left-color: #10B981; background: #f0fdf4;">
            <h3>🚀 AI Service Configuration</h3>
            <div class="form-group">
                <label class="form-label">AI Service URL</label>
                <input type="url" class="form-input" id="ai_service_url" placeholder="http://localhost:8000" value="<?php echo esc_attr(get_option('ai_service_url', 'http://localhost:8000')); ?>">
                <span class="form-hint">Default: http://localhost:8000 (running on this machine)</span>
            </div>
            <button class="submit-btn" id="test-service-btn" style="background: #10B981;">🔍 Test Connection</button>
            <div id="service-status" style="margin-top: 10px;"></div>
        </div>

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

        <!-- Knowledge Base Section -->
        <div class="ai-section" style="margin-top: 40px;">
            <h3>📚 Knowledge Base Management</h3>
            <p style="color: #666; margin: 10px 0 20px 0;">Upload documents for AI to learn from (PDF, TXT, CSV, DOCX)</p>

            <!-- Add New Category -->
            <div style="margin-bottom: 30px; padding: 20px; background: white; border-radius: 6px;">
                <h4 style="margin-top: 0;">Create New Category</h4>
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" id="new_category_name" class="form-input" placeholder="e.g., Menu, Policies, FAQs">
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <input type="text" id="new_category_desc" class="form-input" placeholder="Brief description">
                </div>
                <button class="submit-btn" id="add-category-btn" style="background: #10B981;">➕ Create Category</button>
            </div>

            <!-- Categories & Documents -->
            <div id="kb-categories-container">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="kb-category" style="margin-bottom: 25px; padding: 20px; border: 2px solid #ddd; border-radius: 6px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h4 style="margin: 0;">📁 <?php echo esc_html($cat->name); ?></h4>
                                <span style="color: #999; font-size: 12px;">ID: <?php echo $cat->id; ?></span>
                            </div>
                            <?php if ($cat->description): ?>
                                <p style="color: #666; margin: 0 0 15px 0;"><?php echo esc_html($cat->description); ?></p>
                            <?php endif; ?>

                            <!-- File Upload for Category -->
                            <div style="margin-bottom: 15px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
                                <label class="form-label">Upload Files to <?php echo esc_html($cat->name); ?></label>
                                <input type="file" class="kb-file-input" data-category="<?php echo $cat->id; ?>" data-category-name="<?php echo esc_attr($cat->name); ?>" multiple accept=".pdf,.txt,.csv,.doc,.docx">
                                <div class="upload-status" data-category="<?php echo $cat->id; ?>" style="margin-top: 10px;"></div>
                            </div>

                            <!-- Files in Category -->
                            <div class="kb-files" data-category="<?php echo $cat->id; ?>">
                                <p style="color: #999; font-size: 13px;">Loading documents...</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 30px; text-align: center; background: #f5f5f5; border-radius: 6px;">
                        <p style="color: #999;">No categories created yet. Create one above to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Test AI Service Connection
        $('#test-service-btn').click(function() {
            var serviceUrl = $('#ai_service_url').val().trim();
            var $status = $('#service-status');
            
            if (!serviceUrl) {
                $status.html('❌ Please enter a service URL');
                return;
            }

            $status.html('⏳ Testing connection...');

            $.ajax({
                url: serviceUrl + '/health',
                type: 'GET',
                timeout: 5000,
                success: function(response) {
                    $status.html('✅ Service is healthy! (' + response.version + ')');
                    $status.css('color', '#10B981');
                    
                    // Save the URL
                    $.post('<?php echo admin_url("admin-ajax.php"); ?>', {
                        action: 'save_ai_service_url',
                        nonce: '<?php echo $nonce; ?>',
                        url: serviceUrl
                    });
                },
                error: function(xhr, status, error) {
                    $status.html('❌ Service not responding. Make sure it\'s running at ' + serviceUrl);
                    $status.css('color', '#EF4444');
                }
            });
        });

        // Select provider
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

        // Handle category creation
        $('#add-category-btn').click(function() {
            var name = $('#new_category_name').val().trim();
            var desc = $('#new_category_desc').val().trim();

            if (!name) {
                alert('Please enter a category name');
                return;
            }

            var formData = {
                action: 'add_kb_category',
                nonce: '<?php echo $nonce; ?>',
                name: name,
                description: desc,
                icon: '📁'
            };

            $.post('<?php echo admin_url("admin-ajax.php"); ?>', formData, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            });
        });

        // Handle file upload
        $('.kb-file-input').on('change', function() {
            var category = $(this).data('category');
            var categoryName = $(this).data('category-name');
            var files = this.files;
            var $status = $('.upload-status[data-category="' + category + '"]');

            if (files.length === 0) return;

            for (var i = 0; i < files.length; i++) {
                uploadFile(files[i], category, categoryName, $status);
            }
        });

        function uploadFile(file, category, categoryName, $status) {
            var formData = new FormData();
            formData.append('action', 'upload_kb_file');
            formData.append('nonce', '<?php echo $nonce; ?>');
            formData.append('file', file);
            formData.append('category', categoryName);

            $status.html('⏳ Uploading ' + file.name + '...');

            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $status.html('✓ ' + file.name + ' uploaded and indexed (' + response.data.chunks + ' chunks)');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $status.html('✗ Error: ' + response.data.message);
                    }
                },
                error: function() {
                    $status.html('✗ Upload failed');
                }
            });
        }

        // Load documents for each category
        function loadCategoryDocuments() {
            // This would load documents from server
            // For now, just show placeholder
            $('.kb-files').html('<p style="color: #999; font-size: 13px;">Documents will appear here after upload</p>');
        }

        loadCategoryDocuments();
    });
    </script>

    <?php
}
