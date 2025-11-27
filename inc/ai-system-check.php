<?php
/**
 * AI System Health Check & Quick Test
 * 
 * Verifies all AI components are working correctly
 * Access: Restaurant → AI System Check
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add System Check submenu
 */
add_action('admin_menu', 'ucfc_add_system_check_menu', 101);
function ucfc_add_system_check_menu() {
    add_submenu_page(
        'restaurant-settings',
        'AI System Check',
        '🔧 AI System Check',
        'manage_options',
        'ucfc-ai-system-check',
        'ucfc_ai_system_check_page'
    );
}

/**
 * System Check Page
 */
function ucfc_ai_system_check_page() {
    global $ucfc_conversation_logger, $wpdb;
    
    // Run tests
    $results = array();
    
    // 1. Check database table
    $table_name = $wpdb->prefix . 'ucfc_ai_conversations';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    $results[] = array(
        'test' => 'Database Table',
        'status' => $table_exists,
        'message' => $table_exists ? "Table '{$table_name}' exists" : "Table missing - click 'Create Tables' button",
        'fix' => !$table_exists ? 'create_tables' : null
    );
    
    // 2. Check AI enabled
    $ai_enabled = get_option('ucfc_ai_enabled', '0') === '1';
    $results[] = array(
        'test' => 'AI Enabled',
        'status' => $ai_enabled,
        'message' => $ai_enabled ? 'AI Assistant is enabled' : 'AI Assistant is disabled',
        'fix' => !$ai_enabled ? 'enable_ai' : null
    );
    
    // 3. Check API keys configured
    $provider = get_option('ucfc_ai_model_provider', 'openai');
    $api_key = '';
    switch ($provider) {
        case 'openai':
            $api_key = get_option('ucfc_ai_openai_key');
            break;
        case 'claude':
            $api_key = get_option('ucfc_ai_claude_key');
            break;
        case 'gemini':
            $api_key = get_option('ucfc_ai_gemini_key');
            break;
    }
    $has_key = !empty($api_key);
    $results[] = array(
        'test' => 'API Key Configured',
        'status' => $has_key,
        'message' => $has_key ? "API key set for {$provider}" : "No API key for {$provider}",
        'fix' => !$has_key ? 'add_api_key' : null
    );
    
    // 4. Check conversation count
    if ($table_exists) {
        $conversation_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        $results[] = array(
            'test' => 'Conversation History',
            'status' => true,
            'message' => "{$conversation_count} conversations logged",
            'info' => true
        );
    }
    
    // 5. Check file permissions
    $upload_dir = wp_upload_dir();
    $is_writable = is_writable($upload_dir['path']);
    $results[] = array(
        'test' => 'Upload Directory',
        'status' => $is_writable,
        'message' => $is_writable ? 'Upload directory is writable' : 'Cannot write to uploads directory',
        'fix' => !$is_writable ? 'fix_permissions' : null
    );
    
    // 6. Check required PHP extensions
    $curl_enabled = function_exists('curl_version');
    $results[] = array(
        'test' => 'cURL Extension',
        'status' => $curl_enabled,
        'message' => $curl_enabled ? 'cURL is enabled' : 'cURL extension is required',
        'fix' => !$curl_enabled ? 'install_curl' : null
    );
    
    $json_enabled = function_exists('json_encode');
    $results[] = array(
        'test' => 'JSON Extension',
        'status' => $json_enabled,
        'message' => $json_enabled ? 'JSON is enabled' : 'JSON extension is required',
        'fix' => !$json_enabled ? 'install_json' : null
    );
    
    // 7. Check memory limit
    $memory_limit = ini_get('memory_limit');
    $memory_mb = intval($memory_limit);
    $memory_ok = $memory_mb >= 128 || $memory_limit === '-1';
    $results[] = array(
        'test' => 'PHP Memory Limit',
        'status' => $memory_ok,
        'message' => "Memory limit: {$memory_limit}" . ($memory_ok ? ' (OK)' : ' (Increase to 128M)'),
        'info' => $memory_ok
    );
    
    // Calculate overall status
    $total_tests = count(array_filter($results, function($r) { return !isset($r['info']); }));
    $passed_tests = count(array_filter($results, function($r) { return $r['status'] && !isset($r['info']); }));
    $health_percentage = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100) : 0;
    
    ?>
    <div class="wrap ucfc-system-check">
        <h1>🔧 AI System Health Check</h1>
        
        <div class="health-score <?php echo $health_percentage >= 80 ? 'healthy' : ($health_percentage >= 60 ? 'warning' : 'critical'); ?>">
            <div class="score-circle">
                <svg viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="45" fill="none" stroke="#e0e0e0" stroke-width="8"/>
                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8"
                            stroke-dasharray="<?php echo $health_percentage * 2.827; ?> 282.7"
                            transform="rotate(-90 50 50)"/>
                </svg>
                <div class="score-text">
                    <span class="score-number"><?php echo $health_percentage; ?>%</span>
                    <span class="score-label">Health</span>
                </div>
            </div>
            <div class="health-status">
                <h2><?php 
                    if ($health_percentage >= 80) echo '✅ System Healthy';
                    elseif ($health_percentage >= 60) echo '⚠️ Needs Attention';
                    else echo '❌ Critical Issues';
                ?></h2>
                <p><?php echo $passed_tests; ?> of <?php echo $total_tests; ?> checks passed</p>
            </div>
        </div>
        
        <div class="test-results">
            <?php foreach ($results as $result): ?>
                <div class="test-item <?php echo $result['status'] ? 'passed' : 'failed'; ?> <?php echo isset($result['info']) ? 'info' : ''; ?>">
                    <div class="test-header">
                        <span class="test-icon">
                            <?php echo $result['status'] ? '✅' : '❌'; ?>
                        </span>
                        <h3><?php echo esc_html($result['test']); ?></h3>
                    </div>
                    <p class="test-message"><?php echo esc_html($result['message']); ?></p>
                    <?php if (isset($result['fix'])): ?>
                        <button class="button fix-button" data-fix="<?php echo esc_attr($result['fix']); ?>">
                            Fix This Issue
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="quick-actions">
            <h2>🚀 Quick Actions</h2>
            <div class="action-buttons">
                <a href="?page=ucfc-ai-assistant" class="button button-primary">⚙️ Configure AI Settings</a>
                <a href="?page=ucfc-ai-analytics" class="button button-primary">📊 View Analytics</a>
                <button id="test-ai-connection" class="button button-secondary">🔌 Test AI Connection</button>
                <button id="simulate-conversation" class="button button-secondary">💬 Simulate Conversation</button>
                <button id="create-tables" class="button">🗄️ Create/Repair Tables</button>
                <button id="clear-logs" class="button button-link-delete">🗑️ Clear Old Logs (90+ days)</button>
            </div>
        </div>
        
        <div class="system-info">
            <h2>📋 System Information</h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td><strong>WordPress Version:</strong></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>PHP Version:</strong></td>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Theme Version:</strong></td>
                        <td>2.0 (Phase 2 Complete)</td>
                    </tr>
                    <tr>
                        <td><strong>Active AI Provider:</strong></td>
                        <td><?php echo ucfirst(get_option('ucfc_ai_model_provider', 'none')); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Active AI Model:</strong></td>
                        <td><?php echo get_option('ucfc_ai_model_version', 'none'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Database Prefix:</strong></td>
                        <td><?php echo $wpdb->prefix; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Upload Directory:</strong></td>
                        <td><?php echo $upload_dir['path']; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div id="test-output" class="notice" style="display:none; margin-top: 20px;"></div>
    </div>
    
    <style>
        .ucfc-system-check {
            max-width: 1200px;
        }
        
        .health-score {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 40px;
            margin: 20px 0 30px;
        }
        
        .health-score.healthy { border-left: 6px solid #10b981; }
        .health-score.warning { border-left: 6px solid #f59e0b; }
        .health-score.critical { border-left: 6px solid #ef4444; }
        
        .score-circle {
            position: relative;
            width: 150px;
            height: 150px;
        }
        
        .score-circle svg {
            width: 100%;
            height: 100%;
        }
        
        .health-score.healthy circle:last-child { stroke: #10b981; }
        .health-score.warning circle:last-child { stroke: #f59e0b; }
        .health-score.critical circle:last-child { stroke: #ef4444; }
        
        .score-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        
        .score-number {
            display: block;
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
        }
        
        .score-label {
            display: block;
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .health-status h2 {
            margin: 0 0 10px;
            font-size: 28px;
        }
        
        .health-status p {
            margin: 0;
            color: #666;
            font-size: 16px;
        }
        
        .test-results {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .test-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #ccc;
        }
        
        .test-item.passed { border-left-color: #10b981; }
        .test-item.failed { border-left-color: #ef4444; }
        .test-item.info { border-left-color: #3b82f6; }
        
        .test-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .test-icon {
            font-size: 24px;
        }
        
        .test-item h3 {
            margin: 0;
            font-size: 16px;
        }
        
        .test-message {
            color: #666;
            margin: 0 0 10px;
        }
        
        .fix-button {
            background: #ef4444;
            color: white;
            border: none;
        }
        
        .fix-button:hover {
            background: #dc2626;
        }
        
        .quick-actions,
        .system-info {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .quick-actions h2,
        .system-info h2 {
            margin-top: 0;
        }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .system-info table {
            margin-top: 15px;
        }
        
        .system-info td {
            padding: 12px;
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Test AI Connection
        $('#test-ai-connection').on('click', function() {
            const $btn = $(this);
            const $output = $('#test-output');
            
            $btn.prop('disabled', true).text('Testing...');
            $output.removeClass('notice-success notice-error').hide();
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_test_api_connection',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $output.addClass('notice-success')
                               .html('<p><strong>✅ Success!</strong><br>' + response.data.message + '</p>')
                               .slideDown();
                    } else {
                        $output.addClass('notice-error')
                               .html('<p><strong>❌ Failed!</strong><br>' + response.data.message + '</p>')
                               .slideDown();
                    }
                    $btn.prop('disabled', false).text('🔌 Test AI Connection');
                },
                error: function() {
                    $output.addClass('notice-error')
                           .html('<p><strong>❌ Connection Error</strong></p>')
                           .slideDown();
                    $btn.prop('disabled', false).text('🔌 Test AI Connection');
                }
            });
        });
        
        // Simulate Conversation
        $('#simulate-conversation').on('click', function() {
            const $btn = $(this);
            const $output = $('#test-output');
            
            $btn.prop('disabled', true).text('Simulating...');
            $output.removeClass('notice-success notice-error').hide();
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_simulate_conversation',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $output.addClass('notice-success')
                               .html('<p><strong>✅ Conversation Simulated!</strong><br>' + 
                                     'Response: ' + response.data.reply + '<br>' +
                                     'Response Time: ' + response.data.response_time + 's<br>' +
                                     'Tokens Used: ' + response.data.tokens + '</p>')
                               .slideDown();
                        
                        // Reload page after 2 seconds to show updated stats
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $output.addClass('notice-error')
                               .html('<p><strong>❌ Simulation Failed!</strong><br>' + response.data.message + '</p>')
                               .slideDown();
                    }
                    $btn.prop('disabled', false).text('💬 Simulate Conversation');
                },
                error: function() {
                    $output.addClass('notice-error')
                           .html('<p><strong>❌ Error</strong></p>')
                           .slideDown();
                    $btn.prop('disabled', false).text('💬 Simulate Conversation');
                }
            });
        });
        
        // Create Tables
        $('#create-tables').on('click', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).text('Creating...');
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_create_tables',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>'
                },
                success: function() {
                    alert('✅ Tables created successfully!');
                    location.reload();
                },
                error: function() {
                    alert('❌ Failed to create tables');
                    $btn.prop('disabled', false).text('🗄️ Create/Repair Tables');
                }
            });
        });
        
        // Clear Old Logs
        $('#clear-logs').on('click', function() {
            if (!confirm('Delete conversations older than 90 days?')) return;
            
            const $btn = $(this);
            $btn.prop('disabled', true).text('Deleting...');
            
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'ucfc_clear_old_logs',
                    nonce: '<?php echo wp_create_nonce("ucfc_ai_test"); ?>'
                },
                success: function(response) {
                    alert('✅ Deleted ' + response.data.count + ' old conversations');
                    location.reload();
                },
                error: function() {
                    alert('❌ Failed to delete logs');
                    $btn.prop('disabled', false).text('🗑️ Clear Old Logs (90+ days)');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * AJAX: Simulate Conversation
 */
add_action('wp_ajax_ucfc_simulate_conversation', function() {
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    global $ucfc_ai_engine;
    
    $test_message = "What are your most popular chicken items?";
    $start_time = microtime(true);
    
    $result = $ucfc_ai_engine->chat($test_message, array());
    
    $response_time = round(microtime(true) - $start_time, 2);
    
    if ($result['success']) {
        // Log the simulated conversation
        global $ucfc_conversation_logger;
        $ucfc_conversation_logger->log_conversation(array(
            'user_message' => $test_message,
            'ai_response' => $result['reply'],
            'response_time' => $response_time,
            'tokens_used' => isset($result['usage']['total_tokens']) ? $result['usage']['total_tokens'] : 0
        ));
        
        wp_send_json_success(array(
            'reply' => $result['reply'],
            'response_time' => $response_time,
            'tokens' => isset($result['usage']['total_tokens']) ? $result['usage']['total_tokens'] : 0
        ));
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
});

/**
 * AJAX: Create Tables
 */
add_action('wp_ajax_ucfc_create_tables', function() {
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    global $ucfc_conversation_logger;
    $ucfc_conversation_logger->create_tables();
    
    wp_send_json_success();
});

/**
 * AJAX: Clear Old Logs
 */
add_action('wp_ajax_ucfc_clear_old_logs', function() {
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    global $ucfc_conversation_logger;
    $count = $ucfc_conversation_logger->delete_old_conversations(90);
    
    wp_send_json_success(array('count' => $count));
});
