<?php
/**
 * AI Agent Dashboard
 * Dedicated interface for managing OpenAI Assistants (Agents)
 * 
 * @package Uncle_Chans_Chicken
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add AI Agent Dashboard Menu
 */
function ucfc_add_ai_agent_dashboard_menu() {
    add_submenu_page(
        'restaurant-settings',
        'AI Agent Dashboard',
        '🤖 AI Agents',
        'manage_options',
        'ucfc-ai-agents',
        'ucfc_ai_agent_dashboard_page'
    );
}
add_action('admin_menu', 'ucfc_add_ai_agent_dashboard_menu', 100);

/**
 * AI Agent Dashboard Page
 */
function ucfc_ai_agent_dashboard_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Get current assistant configuration
    $use_assistant = get_option('ucfc_ai_use_assistant', '0');
    $assistant_id = get_option('ucfc_ai_assistant_id', '');
    $assistant_name = get_option('ucfc_assistant_name', 'Not Configured');
    $assistant_model = get_option('ucfc_assistant_model', 'N/A');
    $last_sync = get_option('ucfc_assistant_last_sync', 'Never');
    
    // Get OpenAI API key
    $openai_key = get_option('ucfc_ai_openai_key', '');
    $has_api_key = !empty($openai_key);
    
    ?>
    <div class="wrap ucfc-ai-agent-dashboard">
        <h1>🤖 AI Agent Dashboard</h1>
        <p class="description">Manage your OpenAI Assistant (Agent) with full thread and conversation management.</p>
        
        <?php if (!$has_api_key): ?>
            <div class="notice notice-error">
                <p>⚠️ <strong>OpenAI API Key Required</strong> - Please configure your API key in <a href="<?php echo admin_url('admin.php?page=restaurant-ai-assistant'); ?>">AI Assistant Settings</a> first.</p>
            </div>
        <?php endif; ?>
        
        <!-- Dashboard Grid -->
        <div class="ucfc-agent-grid">
            
            <!-- Agent Status Card -->
            <div class="ucfc-agent-card status-card">
                <div class="card-header">
                    <h2>🎯 Agent Status</h2>
                    <span class="status-badge <?php echo $use_assistant === '1' ? 'active' : 'inactive'; ?>">
                        <?php echo $use_assistant === '1' ? '● Active' : '○ Inactive'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="agent-info">
                        <div class="info-row">
                            <span class="label">Assistant Name:</span>
                            <span class="value"><?php echo esc_html($assistant_name); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Assistant ID:</span>
                            <span class="value code"><?php echo esc_html($assistant_id ?: 'Not Set'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Model:</span>
                            <span class="value"><?php echo esc_html($assistant_model); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Last Sync:</span>
                            <span class="value"><?php echo esc_html($last_sync); ?></span>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <?php if ($use_assistant === '1'): ?>
                            <button type="button" class="button button-secondary" id="ucfc-deactivate-agent">
                                ⏸️ Deactivate Agent
                            </button>
                        <?php else: ?>
                            <button type="button" class="button button-primary" id="ucfc-activate-agent" <?php echo !$assistant_id ? 'disabled' : ''; ?>>
                                ▶️ Activate Agent
                            </button>
                        <?php endif; ?>
                        
                        <button type="button" class="button button-secondary" id="ucfc-refresh-agent">
                            🔄 Refresh Status
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="ucfc-agent-card actions-card">
                <div class="card-header">
                    <h2>⚡ Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <button type="button" class="action-btn" id="ucfc-test-agent">
                            <span class="icon">💬</span>
                            <span class="text">Test Agent</span>
                        </button>
                        
                        <button type="button" class="action-btn" id="ucfc-view-threads">
                            <span class="icon">🧵</span>
                            <span class="text">View Threads</span>
                        </button>
                        
                        <button type="button" class="action-btn" id="ucfc-agent-settings">
                            <span class="icon">⚙️</span>
                            <span class="text">Configure</span>
                        </button>
                        
                        <button type="button" class="action-btn" id="ucfc-agent-analytics">
                            <span class="icon">📊</span>
                            <span class="text">Analytics</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Active Threads Card -->
            <div class="ucfc-agent-card threads-card">
                <div class="card-header">
                    <h2>🧵 Active Threads</h2>
                    <button type="button" class="button button-small" id="ucfc-create-thread">
                        + New Thread
                    </button>
                </div>
                <div class="card-body">
                    <div id="ucfc-threads-list" class="threads-list">
                        <p class="loading">Loading threads...</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity Card -->
            <div class="ucfc-agent-card activity-card">
                <div class="card-header">
                    <h2>📋 Recent Activity</h2>
                </div>
                <div class="card-body">
                    <div id="ucfc-recent-activity" class="activity-list">
                        <p class="loading">Loading activity...</p>
                    </div>
                </div>
            </div>
            
            <!-- Agent Metrics Card -->
            <div class="ucfc-agent-card metrics-card">
                <div class="card-header">
                    <h2>📈 Agent Metrics</h2>
                    <select id="ucfc-metrics-timeframe">
                        <option value="today">Today</option>
                        <option value="week" selected>Last 7 Days</option>
                        <option value="month">Last 30 Days</option>
                    </select>
                </div>
                <div class="card-body">
                    <div class="metrics-grid">
                        <div class="metric">
                            <span class="metric-value" id="metric-conversations">-</span>
                            <span class="metric-label">Conversations</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value" id="metric-threads">-</span>
                            <span class="metric-label">Threads</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value" id="metric-messages">-</span>
                            <span class="metric-label">Messages</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value" id="metric-functions">-</span>
                            <span class="metric-label">Function Calls</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value" id="metric-avg-time">-</span>
                            <span class="metric-label">Avg Response</span>
                        </div>
                        <div class="metric">
                            <span class="metric-value" id="metric-success-rate">-</span>
                            <span class="metric-label">Success Rate</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Agent Tools Card -->
            <div class="ucfc-agent-card tools-card">
                <div class="card-header">
                    <h2>🔧 Agent Tools</h2>
                </div>
                <div class="card-body">
                    <div id="ucfc-agent-tools" class="tools-list">
                        <p class="loading">Loading tools...</p>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Test Agent Modal -->
        <div id="ucfc-test-agent-modal" class="ucfc-modal">
            <div class="ucfc-modal-content large">
                <div class="modal-header">
                    <h3>💬 Test Agent</h3>
                    <span class="ucfc-modal-close">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="test-chat-container">
                        <div class="test-chat-messages" id="test-agent-messages">
                            <div class="chat-message assistant">
                                <div class="message-avatar">🤖</div>
                                <div class="message-content">
                                    <p>Hi! I'm your AI agent. This is a test conversation using OpenAI Assistant API with threads. Ask me anything!</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="test-chat-input">
                            <input type="text" id="test-agent-input" placeholder="Type a message..." />
                            <button type="button" class="button button-primary" id="test-agent-send">
                                Send
                            </button>
                            <button type="button" class="button button-secondary" id="test-agent-new-thread">
                                New Thread
                            </button>
                        </div>
                        
                        <div class="test-info">
                            <span class="label">Thread ID:</span>
                            <span class="value code" id="current-thread-id">Not created yet</span>
                            <button type="button" class="button button-small" id="copy-thread-id" style="display:none;">
                                📋 Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View Threads Modal -->
        <div id="ucfc-threads-modal" class="ucfc-modal">
            <div class="ucfc-modal-content large">
                <div class="modal-header">
                    <h3>🧵 Thread Manager</h3>
                    <span class="ucfc-modal-close">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="threads-manager">
                        <div class="threads-sidebar">
                            <div class="sidebar-header">
                                <h4>Threads</h4>
                                <button type="button" class="button button-small" id="threads-refresh">
                                    🔄
                                </button>
                            </div>
                            <div id="threads-sidebar-list" class="sidebar-list">
                                <p class="loading">Loading...</p>
                            </div>
                        </div>
                        
                        <div class="threads-content">
                            <div id="thread-messages-container">
                                <p class="empty-state">Select a thread to view messages</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <?php
    ucfc_ai_agent_dashboard_styles();
    ucfc_ai_agent_dashboard_scripts();
}

/**
 * Dashboard Styles
 */
function ucfc_ai_agent_dashboard_styles() {
    ?>
    <style>
        .ucfc-ai-agent-dashboard { max-width: 1600px; margin: 20px 0; }
        
        /* Grid Layout */
        .ucfc-agent-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-top: 30px; }
        
        /* Cards */
        .ucfc-agent-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; transition: all 0.3s ease; }
        .ucfc-agent-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
        
        .card-header { background: linear-gradient(135deg, #C92A2A, #A52222); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { margin: 0; font-size: 18px; font-weight: 600; }
        
        .card-body { padding: 25px; }
        
        /* Status Card */
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .status-badge.active { background: #4CAF50; color: white; }
        .status-badge.inactive { background: #9E9E9E; color: white; }
        
        .agent-info { margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .info-row:last-child { border-bottom: none; }
        .info-row .label { font-weight: 600; color: #666; }
        .info-row .value { color: #333; }
        .info-row .value.code { font-family: 'Consolas', monospace; font-size: 12px; background: #f5f5f5; padding: 4px 8px; border-radius: 4px; }
        
        .card-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        
        /* Quick Actions */
        .quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .action-btn { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 10px; padding: 20px; cursor: pointer; transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .action-btn:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4); }
        .action-btn .icon { font-size: 32px; }
        .action-btn .text { font-size: 14px; font-weight: 600; }
        
        /* Threads List */
        .threads-list { max-height: 300px; overflow-y: auto; }
        .thread-item { padding: 15px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.2s ease; }
        .thread-item:hover { background: #f9f9f9; }
        .thread-item .thread-id { font-family: 'Consolas', monospace; font-size: 12px; color: #666; }
        .thread-item .thread-date { font-size: 12px; color: #999; float: right; }
        
        /* Activity List */
        .activity-list { max-height: 300px; overflow-y: auto; }
        .activity-item { padding: 12px; border-left: 3px solid #4CAF50; background: #f9f9f9; margin-bottom: 10px; border-radius: 4px; }
        .activity-item .activity-time { font-size: 11px; color: #999; }
        .activity-item .activity-text { font-size: 13px; color: #333; margin-top: 5px; }
        
        /* Metrics Grid */
        .metrics-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .metric { text-align: center; padding: 15px; background: #f9f9f9; border-radius: 8px; }
        .metric-value { display: block; font-size: 32px; font-weight: 700; color: #C92A2A; margin-bottom: 5px; }
        .metric-label { display: block; font-size: 12px; color: #666; text-transform: uppercase; }
        
        /* Tools List */
        .tools-list { display: flex; flex-direction: column; gap: 10px; }
        .tool-item { padding: 12px; background: #f0f7ff; border-left: 3px solid #4CAF50; border-radius: 4px; }
        .tool-item .tool-name { font-weight: 600; color: #333; }
        .tool-item .tool-desc { font-size: 12px; color: #666; margin-top: 5px; }
        
        /* Loading & Empty States */
        .loading, .empty-state { text-align: center; color: #999; padding: 40px; }
        
        /* Modal Styles */
        .ucfc-modal { display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.7); }
        .ucfc-modal-content { background-color: #fefefe; margin: 3% auto; border-radius: 12px; width: 90%; max-width: 900px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        .ucfc-modal-content.large { max-width: 1200px; }
        
        .modal-header { background: linear-gradient(135deg, #C92A2A, #A52222); color: white; padding: 20px 30px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { margin: 0; font-size: 20px; }
        
        .ucfc-modal-close { color: white; font-size: 32px; font-weight: bold; cursor: pointer; line-height: 20px; }
        .ucfc-modal-close:hover { color: #F0B429; }
        
        .modal-body { padding: 30px; }
        
        /* Test Chat */
        .test-chat-container { display: flex; flex-direction: column; gap: 20px; }
        .test-chat-messages { background: #f9f9f9; border-radius: 10px; padding: 20px; min-height: 400px; max-height: 500px; overflow-y: auto; }
        .chat-message { display: flex; gap: 15px; margin-bottom: 20px; animation: fadeIn 0.3s ease; }
        .chat-message.user { flex-direction: row-reverse; }
        .message-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #C92A2A, #F0B429); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .chat-message.user .message-avatar { background: linear-gradient(135deg, #667eea, #764ba2); }
        .message-content { background: #f0f0f0; padding: 12px 18px; border-radius: 15px; max-width: 70%; }
        .chat-message.user .message-content { background: #C92A2A; color: white; }
        
        .test-chat-input { display: flex; gap: 10px; }
        .test-chat-input input { flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; }
        
        .test-info { padding: 15px; background: #f0f7ff; border-radius: 8px; display: flex; align-items: center; gap: 10px; }
        .test-info .label { font-weight: 600; color: #666; }
        .test-info .value { font-family: 'Consolas', monospace; font-size: 12px; color: #333; }
        
        /* Thread Manager */
        .threads-manager { display: grid; grid-template-columns: 300px 1fr; gap: 20px; height: 600px; }
        
        .threads-sidebar { background: #f9f9f9; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; }
        .sidebar-header { padding: 15px; background: #333; color: white; display: flex; justify-content: space-between; align-items: center; }
        .sidebar-header h4 { margin: 0; font-size: 16px; }
        
        .sidebar-list { flex: 1; overflow-y: auto; }
        
        .threads-content { background: white; border: 2px solid #f0f0f0; border-radius: 8px; padding: 20px; overflow-y: auto; }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .ucfc-agent-grid { grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 768px) {
            .ucfc-agent-grid { grid-template-columns: 1fr; }
            .quick-actions { grid-template-columns: 1fr; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
            .threads-manager { grid-template-columns: 1fr; height: auto; }
        }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <?php
}

/**
 * Dashboard Scripts
 */
function ucfc_ai_agent_dashboard_scripts() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        let currentThreadId = null;
        
        // Activate/Deactivate Agent
        $('#ucfc-activate-agent').on('click', function() {
            $.post(ajaxurl, {
                action: 'ucfc_toggle_agent',
                nonce: '<?php echo wp_create_nonce("ucfc_agent_action"); ?>',
                activate: true
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to activate agent: ' + response.data.message);
                }
            });
        });
        
        $('#ucfc-deactivate-agent').on('click', function() {
            if (confirm('Are you sure you want to deactivate the agent?')) {
                $.post(ajaxurl, {
                    action: 'ucfc_toggle_agent',
                    nonce: '<?php echo wp_create_nonce("ucfc_agent_action"); ?>',
                    activate: false
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                });
            }
        });
        
        // Refresh Status
        $('#ucfc-refresh-agent').on('click', function() {
            location.reload();
        });
        
        // Quick Actions
        $('#ucfc-test-agent').on('click', function() {
            $('#ucfc-test-agent-modal').fadeIn();
        });
        
        $('#ucfc-view-threads').on('click', function() {
            loadThreadsList();
            $('#ucfc-threads-modal').fadeIn();
        });
        
        $('#ucfc-agent-settings').on('click', function() {
            window.location.href = '<?php echo admin_url("admin.php?page=restaurant-ai-assistant"); ?>';
        });
        
        $('#ucfc-agent-analytics').on('click', function() {
            window.location.href = '<?php echo admin_url("admin.php?page=ucfc-ai-analytics"); ?>';
        });
        
        // Test Agent
        function sendTestMessage() {
            const message = $('#test-agent-input').val().trim();
            if (!message) return;
            
            // Add user message to UI
            $('#test-agent-messages').append(`
                <div class="chat-message user">
                    <div class="message-avatar">👤</div>
                    <div class="message-content"><p>${message}</p></div>
                </div>
            `);
            
            $('#test-agent-input').val('').prop('disabled', true);
            
            // Add typing indicator
            $('#test-agent-messages').append('<div class="typing-indicator">🤖 Agent is thinking...</div>');
            $('#test-agent-messages').scrollTop($('#test-agent-messages')[0].scrollHeight);
            
            // Send to backend
            $.post(ajaxurl, {
                action: 'ucfc_agent_test_chat',
                nonce: '<?php echo wp_create_nonce("ucfc_agent_action"); ?>',
                message: message,
                thread_id: currentThreadId
            }, function(response) {
                $('.typing-indicator').remove();
                
                if (response.success) {
                    currentThreadId = response.data.thread_id;
                    $('#current-thread-id').text(currentThreadId);
                    $('#copy-thread-id').show();
                    
                    $('#test-agent-messages').append(`
                        <div class="chat-message assistant">
                            <div class="message-avatar">🤖</div>
                            <div class="message-content"><p>${response.data.reply}</p></div>
                        </div>
                    `);
                } else {
                    $('#test-agent-messages').append(`
                        <div class="chat-message assistant">
                            <div class="message-avatar">❌</div>
                            <div class="message-content"><p>Error: ${response.data.message}</p></div>
                        </div>
                    `);
                }
                
                $('#test-agent-input').prop('disabled', false).focus();
                $('#test-agent-messages').scrollTop($('#test-agent-messages')[0].scrollHeight);
            });
        }
        
        $('#test-agent-send').on('click', sendTestMessage);
        $('#test-agent-input').on('keypress', function(e) {
            if (e.which === 13) sendTestMessage();
        });
        
        $('#test-agent-new-thread').on('click', function() {
            if (confirm('Start a new conversation thread?')) {
                currentThreadId = null;
                $('#current-thread-id').text('Not created yet');
                $('#copy-thread-id').hide();
                $('#test-agent-messages').html(`
                    <div class="chat-message assistant">
                        <div class="message-avatar">🤖</div>
                        <div class="message-content">
                            <p>New thread started! Ask me anything.</p>
                        </div>
                    </div>
                `);
            }
        });
        
        $('#copy-thread-id').on('click', function() {
            navigator.clipboard.writeText(currentThreadId).then(() => {
                $(this).text('✓ Copied');
                setTimeout(() => $(this).text('📋 Copy'), 2000);
            });
        });
        
        // Load Threads List
        function loadThreadsList() {
            $('#threads-sidebar-list').html('<p class="loading">Loading threads...</p>');
            
            $.post(ajaxurl, {
                action: 'ucfc_get_agent_threads',
                nonce: '<?php echo wp_create_nonce("ucfc_agent_action"); ?>'
            }, function(response) {
                if (response.success && response.data.threads.length > 0) {
                    let html = '';
                    response.data.threads.forEach(thread => {
                        html += `
                            <div class="thread-item" data-thread-id="${thread.id}">
                                <div class="thread-id">${thread.id}</div>
                                <div class="thread-date">${thread.date}</div>
                            </div>
                        `;
                    });
                    $('#threads-sidebar-list').html(html);
                } else {
                    $('#threads-sidebar-list').html('<p class="empty-state">No threads found</p>');
                }
            });
        }
        
        // Load Metrics
        function loadMetrics(timeframe) {
            $.post(ajaxurl, {
                action: 'ucfc_get_agent_metrics',
                nonce: '<?php echo wp_create_nonce("ucfc_agent_action"); ?>',
                timeframe: timeframe
            }, function(response) {
                if (response.success) {
                    const m = response.data;
                    $('#metric-conversations').text(m.conversations);
                    $('#metric-threads').text(m.threads);
                    $('#metric-messages').text(m.messages);
                    $('#metric-functions').text(m.functions);
                    $('#metric-avg-time').text(m.avg_time + 's');
                    $('#metric-success-rate').text(m.success_rate + '%');
                }
            });
        }
        
        $('#ucfc-metrics-timeframe').on('change', function() {
            loadMetrics($(this).val());
        });
        
        // Load Activity
        function loadActivity() {
            $.post(ajaxurl, {
                action: 'ucfc_get_agent_activity',
                nonce: '<?php echo wp_create_nonce("ucfc_agent_action"); ?>'
            }, function(response) {
                if (response.success && response.data.activities.length > 0) {
                    let html = '';
                    response.data.activities.forEach(activity => {
                        html += `
                            <div class="activity-item">
                                <div class="activity-time">${activity.time}</div>
                                <div class="activity-text">${activity.text}</div>
                            </div>
                        `;
                    });
                    $('#ucfc-recent-activity').html(html);
                } else {
                    $('#ucfc-recent-activity').html('<p class="empty-state">No recent activity</p>');
                }
            });
        }
        
        // Load Tools
        function loadTools() {
            $.post(ajaxurl, {
                action: 'ucfc_get_agent_tools',
                nonce: '<?php echo wp_create_nonce("ucfc_agent_action"); ?>'
            }, function(response) {
                if (response.success && response.data.tools.length > 0) {
                    let html = '';
                    response.data.tools.forEach(tool => {
                        html += `
                            <div class="tool-item">
                                <div class="tool-name">${tool.name}</div>
                                <div class="tool-desc">${tool.description}</div>
                            </div>
                        `;
                    });
                    $('#ucfc-agent-tools').html(html);
                } else {
                    $('#ucfc-agent-tools').html('<p class="empty-state">No tools configured</p>');
                }
            });
        }
        
        // Initial loads
        loadMetrics('week');
        loadActivity();
        loadTools();
        
        // Modal close
        $('.ucfc-modal-close').on('click', function() {
            $(this).closest('.ucfc-modal').fadeOut();
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
