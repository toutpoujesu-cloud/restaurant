<?php
/**
 * AI Settings Dashboard
 * Manages LLM selection, API keys, knowledge base, and RAG configuration
 * Integrates with LangChain and LlamaIndex
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register AI Settings page in Restaurant Hub
add_action('wp_ajax_save_ai_settings', 'restaurant_save_ai_settings');
add_action('wp_ajax_nopriv_save_ai_settings', 'restaurant_save_ai_settings');

function restaurant_save_ai_settings() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_settings_nonce')) {
        wp_send_json_error(['message' => 'Security verification failed']);
    }

    // Check user capability
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    $llm_provider = sanitize_text_field($_POST['llm_provider'] ?? '');
    $api_keys = [];
    $knowledge_base = [];

    // Handle API keys based on provider
    if ($llm_provider === 'claude') {
        $api_keys['claude'] = sanitize_text_field($_POST['claude_api_key'] ?? '');
    } elseif ($llm_provider === 'gemini') {
        $api_keys['gemini'] = sanitize_text_field($_POST['gemini_api_key'] ?? '');
    } elseif ($llm_provider === 'openai') {
        $api_keys['openai'] = sanitize_text_field($_POST['openai_api_key'] ?? '');
        $api_keys['openai_assistant_id'] = sanitize_text_field($_POST['openai_assistant_id'] ?? '');
    }

    // Save to WordPress options
    update_option('restaurant_llm_provider', $llm_provider);
    update_option('restaurant_api_keys', $api_keys);

    // Handle knowledge base categories and files
    if (isset($_FILES['knowledge_files'])) {
        $knowledge_base = restaurant_process_knowledge_files($_FILES['knowledge_files']);
        update_option('restaurant_knowledge_base', $knowledge_base);
    }

    wp_send_json_success([
        'message' => 'AI Settings saved successfully',
        'provider' => $llm_provider
    ]);
}

/**
 * Process uploaded knowledge base files
 */
function restaurant_process_knowledge_files($files) {
    $knowledge_base = get_option('restaurant_knowledge_base', []);
    
    if (empty($files['name'])) {
        return $knowledge_base;
    }

    $upload_dir = wp_upload_dir();
    $ai_dir = $upload_dir['basedir'] . '/restaurant-ai-knowledge/';

    // Create directory if it doesn't exist
    if (!is_dir($ai_dir)) {
        wp_mkdir_p($ai_dir);
    }

    foreach ($files['name'] as $key => $filename) {
        if (!empty($filename)) {
            $category = sanitize_text_field($_POST['knowledge_category'][$key] ?? 'general');
            
            // Initialize category if not exists
            if (!isset($knowledge_base[$category])) {
                $knowledge_base[$category] = [];
            }

            // Move uploaded file
            $source = $files['tmp_name'][$key];
            $destination = $ai_dir . basename($filename);

            if (move_uploaded_file($source, $destination)) {
                $knowledge_base[$category][] = [
                    'filename' => basename($filename),
                    'path' => $destination,
                    'uploaded_at' => current_time('mysql'),
                    'file_type' => wp_check_filetype($filename)['ext']
                ];
            }
        }
    }

    return $knowledge_base;
}

function restaurant_render_ai_settings_tab() {
    $llm_provider = get_option('restaurant_llm_provider', 'claude');
    $api_keys = get_option('restaurant_api_keys', []);
    $knowledge_base = get_option('restaurant_knowledge_base', []);
    $nonce = wp_create_nonce('ai_settings_nonce');
    ?>
    
    <style>
        /* AI Settings Styles */
        .ai-settings-container {
            max-width: 1200px;
            margin: 20px 0;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .ai-settings-header {
            margin-bottom: 30px;
        }
        
        .ai-section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1A1A1D;
            margin: 0 0 10px 0;
        }
        
        .ai-section-subtitle {
            color: #666;
            font-size: 1rem;
            margin: 0;
        }
    </style>
    
    <div class="ai-settings-container">
        <!-- Header Section -->
        <div class="ai-settings-header">
            <h2 class="ai-section-title">🤖 AI Agent Configuration</h2>
            <p class="ai-section-subtitle">Configure your AI chat assistant with LangChain & LlamaIndex integration</p>
        </div>

        <!-- LLM Provider Selection -->
        <div class="ai-form-section">
            <h3 class="ai-subsection-title">📊 LLM Provider Selection</h3>
            
            <div class="llm-provider-grid">
                <!-- Claude Option -->
                <div class="llm-provider-card" data-provider="claude">
                    <div class="llm-card-header">
                        <h4>Claude</h4>
                        <span class="llm-badge">Anthropic</span>
                    </div>
                    <div class="llm-card-body">
                        <p>Advanced reasoning and nuanced understanding</p>
                        <div class="form-group">
                            <label class="form-label">API Key</label>
                            <input 
                                type="password" 
                                class="form-input" 
                                id="claude_api_key" 
                                placeholder="sk-ant-..." 
                                value="<?php echo esc_attr($api_keys['claude'] ?? ''); ?>"
                            >
                            <span class="form-hint">Get your key from <a href="https://console.anthropic.com" target="_blank">Anthropic Console</a></span>
                        </div>
                    </div>
                    <input type="radio" name="llm_provider" value="claude" <?php checked($llm_provider, 'claude'); ?> class="provider-radio">
                </div>

                <!-- Gemini Option -->
                <div class="llm-provider-card" data-provider="gemini">
                    <div class="llm-card-header">
                        <h4>Gemini</h4>
                        <span class="llm-badge">Google</span>
                    </div>
                    <div class="llm-card-body">
                        <p>Multimodal AI with vision capabilities</p>
                        <div class="form-group">
                            <label class="form-label">API Key</label>
                            <input 
                                type="password" 
                                class="form-input" 
                                id="gemini_api_key" 
                                placeholder="AIza..." 
                                value="<?php echo esc_attr($api_keys['gemini'] ?? ''); ?>"
                            >
                            <span class="form-hint">Get your key from <a href="https://ai.google.dev" target="_blank">Google AI Studio</a></span>
                        </div>
                    </div>
                    <input type="radio" name="llm_provider" value="gemini" <?php checked($llm_provider, 'gemini'); ?> class="provider-radio">
                </div>

                <!-- OpenAI Option -->
                <div class="llm-provider-card" data-provider="openai">
                    <div class="llm-card-header">
                        <h4>OpenAI</h4>
                        <span class="llm-badge">GPT-4</span>
                    </div>
                    <div class="llm-card-body">
                        <p>Latest models with GPT-4 and Assistants API</p>
                        <div class="form-group">
                            <label class="form-label">API Key</label>
                            <input 
                                type="password" 
                                class="form-input" 
                                id="openai_api_key" 
                                placeholder="sk-..." 
                                value="<?php echo esc_attr($api_keys['openai'] ?? ''); ?>"
                            >
                            <span class="form-hint">Get your key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI API Keys</a></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Assistant ID (Optional)</label>
                            <input 
                                type="text" 
                                class="form-input" 
                                id="openai_assistant_id" 
                                placeholder="asst_..." 
                                value="<?php echo esc_attr($api_keys['openai_assistant_id'] ?? ''); ?>"
                            >
                            <span class="form-hint">Import existing OpenAI Assistant or create new one</span>
                            <button type="button" class="import-assistant-btn">📥 Import Assistant</button>
                        </div>
                    </div>
                    <input type="radio" name="llm_provider" value="openai" <?php checked($llm_provider, 'openai'); ?> class="provider-radio">
                </div>
            </div>
        </div>

        <!-- Knowledge Base Section -->
        <div class="ai-form-section">
            <h3 class="ai-subsection-title">📚 Knowledge Base & RAG Configuration</h3>
            <p class="ai-section-hint">Upload documents and files to enhance AI responses with business context</p>
            
            <div class="knowledge-base-container">
                <!-- Existing Categories -->
                <div class="kb-categories">
                    <h4>Knowledge Categories</h4>
                    <div class="kb-category-list">
                        <?php if (!empty($knowledge_base)): ?>
                            <?php foreach ($knowledge_base as $category => $files): ?>
                                <div class="kb-category-item">
                                    <div class="kb-category-header">
                                        <span class="kb-category-name"><?php echo esc_html($category); ?></span>
                                        <span class="kb-file-count"><?php echo count($files); ?> files</span>
                                    </div>
                                    <div class="kb-file-list">
                                        <?php foreach ($files as $file): ?>
                                            <div class="kb-file-item">
                                                <span class="kb-file-name"><?php echo esc_html($file['filename']); ?></span>
                                                <span class="kb-file-type"><?php echo esc_html($file['file_type']); ?></span>
                                                <button type="button" class="remove-file-btn" data-file="<?php echo esc_attr($file['filename']); ?>">×</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-state">No knowledge base files uploaded yet</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upload New Files -->
                <div class="kb-upload">
                    <h4>Add New Files to Knowledge Base</h4>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-input" id="knowledge_category">
                            <option value="general">General</option>
                            <option value="menu">Menu & Dishes</option>
                            <option value="policies">Policies & Guidelines</option>
                            <option value="procedures">Operating Procedures</option>
                            <option value="faqs">FAQs</option>
                            <option value="custom">Custom Category</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Upload Files (PDF, TXT, DOCX, CSV)</label>
                        <div class="file-upload-zone">
                            <input 
                                type="file" 
                                id="knowledge_files" 
                                multiple 
                                accept=".pdf,.txt,.docx,.csv,.doc"
                                class="file-input-hidden"
                            >
                            <div class="upload-icon">📄</div>
                            <p>Drag and drop files here or click to select</p>
                            <span class="upload-hint">Max 10MB per file, multiple files supported</span>
                        </div>
                        <div id="file-preview" class="file-preview"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Framework Configuration -->
        <div class="ai-form-section">
            <h3 class="ai-subsection-title">⚙️ LangChain & LlamaIndex Configuration</h3>
            
            <div class="info-box">
                <p><strong>Framework Stack:</strong> Your AI agent uses LangChain for orchestration and LlamaIndex for document indexing and retrieval. These work together with your selected LLM.</p>
            </div>

            <div class="form-group">
                <label class="form-toggle">
                    <input type="checkbox" class="toggle-input" id="enable_memory" checked>
                    <span class="toggle-switch"></span>
                    <span class="toggle-label">Enable Conversation Memory</span>
                </label>
                <span class="form-hint">Keep conversation context for better follow-up responses</span>
            </div>

            <div class="form-group">
                <label class="form-label">Temperature (Creativity)</label>
                <input 
                    type="range" 
                    min="0" 
                    max="1" 
                    step="0.1" 
                    value="0.7" 
                    class="form-range"
                    id="temperature_slider"
                >
                <div class="slider-value"><span id="temp-display">0.7</span></div>
                <span class="form-hint">Lower = more focused, Higher = more creative</span>
            </div>

            <div class="form-group">
                <label class="form-label">Max Response Tokens</label>
                <input 
                    type="number" 
                    class="form-input" 
                    id="max_tokens" 
                    value="2000"
                    min="100"
                    max="4000"
                >
                <span class="form-hint">Maximum length of AI responses</span>
            </div>
        </div>

        <!-- Save Button -->
        <div class="ai-form-actions">
            <button type="button" class="submit-btn save-ai-settings" data-nonce="<?php echo esc_attr($nonce); ?>">
                💾 Save AI Settings
            </button>
            <button type="button" class="secondary-btn test-ai-btn">
                🧪 Test AI Connection
            </button>
        </div>

        <input type="hidden" id="ai_settings_nonce" value="<?php echo esc_attr($nonce); ?>">
    </div>

    <style>
        .ai-settings-container {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
        }

        .ai-settings-header {
            margin-bottom: 3rem;
        }

        .ai-section-title {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark-charcoal);
            margin-bottom: 0.5rem;
        }

        .ai-section-subtitle {
            color: var(--slate-gray);
            font-size: 1rem;
        }

        .ai-form-section {
            margin-bottom: 3rem;
            padding: 2rem;
            background: rgba(255, 107, 107, 0.02);
            border-radius: 1.25rem;
            border: 2px solid rgba(255, 107, 107, 0.1);
        }

        .ai-subsection-title {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-charcoal);
            margin-bottom: 1.5rem;
        }

        .ai-section-hint {
            color: var(--soft-gray);
            font-size: 0.9375rem;
            margin-bottom: 1.5rem;
        }

        /* LLM Provider Grid */
        .llm-provider-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .llm-provider-card {
            position: relative;
            border: 2px solid rgba(255, 107, 107, 0.15);
            border-radius: 1.25rem;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: white;
        }

        .llm-provider-card:hover {
            border-color: var(--coral-dawn);
            box-shadow: 0 8px 24px rgba(255, 107, 107, 0.15);
            transform: translateY(-4px);
        }

        .llm-provider-card input[type="radio"]:checked + .llm-provider-card,
        .provider-radio:checked ~ .llm-provider-card {
            border-color: var(--coral-dawn);
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.05) 0%, rgba(255, 142, 60, 0.05) 100%);
        }

        .llm-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .llm-card-header h4 {
            font-family: var(--font-display);
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark-charcoal);
        }

        .llm-badge {
            background: var(--gradient-primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .llm-card-body p {
            color: var(--slate-gray);
            font-size: 0.9375rem;
            margin-bottom: 1rem;
        }

        .provider-radio {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .import-assistant-btn {
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 107, 107, 0.1);
            border: 2px solid var(--coral-dawn);
            border-radius: 0.75rem;
            color: var(--coral-dawn);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .import-assistant-btn:hover {
            background: var(--coral-dawn);
            color: white;
        }

        /* Knowledge Base */
        .knowledge-base-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .kb-categories,
        .kb-upload {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            border: 2px solid rgba(255, 107, 107, 0.1);
        }

        .kb-categories h4,
        .kb-upload h4 {
            font-family: var(--font-display);
            font-size: 1.0625rem;
            font-weight: 600;
            color: var(--dark-charcoal);
            margin-bottom: 1rem;
        }

        .kb-category-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .kb-category-item {
            background: rgba(255, 107, 107, 0.02);
            padding: 1rem;
            border-radius: 0.75rem;
            border-left: 4px solid var(--coral-dawn);
        }

        .kb-category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .kb-category-name {
            font-weight: 600;
            color: var(--dark-charcoal);
        }

        .kb-file-count {
            background: var(--coral-dawn);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .kb-file-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .kb-file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem;
            background: white;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .kb-file-name {
            flex: 1;
            color: var(--slate-gray);
        }

        .kb-file-type {
            background: rgba(255, 107, 107, 0.1);
            color: var(--coral-dawn);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .remove-file-btn {
            background: transparent;
            border: none;
            color: #EF4444;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state {
            color: var(--soft-gray);
            text-align: center;
            padding: 2rem;
            font-style: italic;
        }

        /* File Upload */
        .file-upload-zone {
            border: 3px dashed var(--coral-dawn);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 107, 107, 0.02);
        }

        .file-upload-zone:hover {
            background: rgba(255, 107, 107, 0.05);
            border-color: var(--sunset-orange);
        }

        .file-upload-zone.dragover {
            background: rgba(255, 107, 107, 0.1);
            border-color: var(--sunset-orange);
        }

        .upload-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .file-upload-zone p {
            color: var(--dark-charcoal);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .upload-hint {
            display: block;
            color: var(--soft-gray);
            font-size: 0.875rem;
        }

        .file-input-hidden {
            display: none;
        }

        .file-preview {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .file-preview-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success-green);
            border-radius: 0.5rem;
        }

        .file-preview-name {
            color: var(--dark-charcoal);
            font-weight: 500;
        }

        /* Form Range Slider */
        .form-range {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: linear-gradient(135deg, var(--coral-dawn) 0%, var(--sunset-orange) 100%);
            outline: none;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: white;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
        }

        .form-range::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: white;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
            border: none;
        }

        .slider-value {
            text-align: center;
            margin-top: 0.5rem;
            font-weight: 600;
            color: var(--coral-dawn);
        }

        /* Buttons */
        .ai-form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
        }

        .save-ai-settings,
        .test-ai-btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 1rem;
            font-family: var(--font-display);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            font-size: 1rem;
        }

        .save-ai-settings {
            background: var(--gradient-primary);
            color: white;
            flex: 1;
        }

        .save-ai-settings:hover {
            box-shadow: 0 8px 24px rgba(255, 107, 107, 0.3);
            transform: translateY(-2px);
        }

        .secondary-btn,
        .test-ai-btn {
            background: rgba(255, 107, 107, 0.1);
            color: var(--coral-dawn);
            border: 2px solid var(--coral-dawn);
        }

        .test-ai-btn:hover {
            background: var(--coral-dawn);
            color: white;
        }

        .info-box {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(96, 165, 250, 0.1) 100%);
            border-left: 4px solid #3B82F6;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }

        .info-box p {
            color: var(--slate-gray);
            margin: 0;
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .llm-provider-grid {
                grid-template-columns: 1fr;
            }

            .knowledge-base-container {
                grid-template-columns: 1fr;
            }

            .ai-form-actions {
                flex-direction: column;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Temperature slider
            const tempSlider = document.getElementById('temperature_slider');
            const tempDisplay = document.getElementById('temp-display');
            if (tempSlider) {
                tempSlider.addEventListener('input', function() {
                    tempDisplay.textContent = this.value;
                });
            }

            // File upload handling
            const fileUploadZone = document.querySelector('.file-upload-zone');
            const fileInput = document.getElementById('knowledge_files');
            const filePreview = document.getElementById('file-preview');

            if (fileUploadZone) {
                fileUploadZone.addEventListener('click', () => fileInput.click());

                // Drag and drop
                fileUploadZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    fileUploadZone.classList.add('dragover');
                });

                fileUploadZone.addEventListener('dragleave', () => {
                    fileUploadZone.classList.remove('dragover');
                });

                fileUploadZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    fileUploadZone.classList.remove('dragover');
                    fileInput.files = e.dataTransfer.files;
                    updateFilePreview();
                });

                fileInput.addEventListener('change', updateFilePreview);
            }

            function updateFilePreview() {
                filePreview.innerHTML = '';
                if (fileInput.files.length > 0) {
                    Array.from(fileInput.files).forEach(file => {
                        const item = document.createElement('div');
                        item.className = 'file-preview-item';
                        item.innerHTML = `
                            <span class="file-preview-name">✓ ${file.name}</span>
                            <span>${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                        `;
                        filePreview.appendChild(item);
                    });
                }
            }

            // Save settings
            document.querySelector('.save-ai-settings')?.addEventListener('click', function() {
                const provider = document.querySelector('input[name="llm_provider"]:checked').value;
                const nonce = this.dataset.nonce;
                
                const formData = new FormData();
                formData.append('action', 'save_ai_settings');
                formData.append('nonce', nonce);
                formData.append('llm_provider', provider);
                
                // Add API keys
                if (provider === 'claude') {
                    formData.append('claude_api_key', document.getElementById('claude_api_key').value);
                } else if (provider === 'gemini') {
                    formData.append('gemini_api_key', document.getElementById('gemini_api_key').value);
                } else if (provider === 'openai') {
                    formData.append('openai_api_key', document.getElementById('openai_api_key').value);
                    formData.append('openai_assistant_id', document.getElementById('openai_assistant_id').value);
                }

                // Add knowledge files
                if (fileInput.files.length > 0) {
                    Array.from(fileInput.files).forEach(file => {
                        formData.append('knowledge_files[]', file);
                        formData.append('knowledge_category[]', document.getElementById('knowledge_category').value);
                    });
                }

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    alert(data.data.message);
                    if (data.success) {
                        // Reset file input
                        fileInput.value = '';
                        filePreview.innerHTML = '';
                    }
                })
                .catch(e => alert('Error: ' + e.message));
            });

            // Test connection
            document.querySelector('.test-ai-btn')?.addEventListener('click', function() {
                const provider = document.querySelector('input[name="llm_provider"]:checked').value;
                alert('Testing ' + provider + ' connection...\n(Implementation pending)');
            });

            // Import OpenAI Assistant
            document.querySelector('.import-assistant-btn')?.addEventListener('click', function() {
                alert('OpenAI Assistant import dialog\n(Implementation pending)');
            });
        });
    </script>
    <?php
}

// Hook to display AI settings in dashboard
add_action('restaurant_hub_sections', 'add_ai_settings_section');

function add_ai_settings_section() {
    echo '<div class="restaurant-tab-content" id="ai-settings-tab">';
    restaurant_render_ai_settings_tab();
    echo '</div>';
}
