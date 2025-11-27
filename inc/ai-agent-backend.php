<?php
/**
 * AI Agent Backend Handlers
 * OpenAI Assistant API integration with threads and runs
 * 
 * @package Uncle_Chans_Chicken
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Toggle Agent Activation
 */
function ucfc_toggle_agent() {
    check_ajax_referer('ucfc_agent_action', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    $activate = isset($_POST['activate']) && $_POST['activate'];
    
    update_option('ucfc_ai_use_assistant', $activate ? '1' : '0');
    
    wp_send_json_success(array(
        'message' => $activate ? 'Agent activated' : 'Agent deactivated',
        'status' => $activate
    ));
}
add_action('wp_ajax_ucfc_toggle_agent', 'ucfc_toggle_agent');

/**
 * Test Agent Chat (with Thread Management)
 */
function ucfc_agent_test_chat() {
    check_ajax_referer('ucfc_agent_action', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
    $thread_id = isset($_POST['thread_id']) ? sanitize_text_field($_POST['thread_id']) : null;
    
    if (empty($message)) {
        wp_send_json_error(array('message' => 'Message required'));
        return;
    }
    
    // Get OpenAI credentials
    $api_key = get_option('ucfc_ai_openai_key', '');
    $assistant_id = get_option('ucfc_ai_assistant_id', '');
    
    if (empty($api_key) || empty($assistant_id)) {
        wp_send_json_error(array('message' => 'OpenAI API key or Assistant ID not configured'));
        return;
    }
    
    try {
        // Create or use existing thread
        if (empty($thread_id)) {
            $thread = ucfc_create_openai_thread($api_key);
            $thread_id = $thread['id'];
        }
        
        // Add message to thread
        ucfc_add_message_to_thread($api_key, $thread_id, $message);
        
        // Create and poll run
        $run = ucfc_create_run($api_key, $thread_id, $assistant_id);
        
        // Wait for completion
        $completed_run = ucfc_poll_run($api_key, $thread_id, $run['id']);
        
        if ($completed_run['status'] === 'completed') {
            // Get assistant's response
            $messages = ucfc_get_thread_messages($api_key, $thread_id);
            $assistant_message = $messages[0]['content'][0]['text']['value'];
            
            wp_send_json_success(array(
                'reply' => $assistant_message,
                'thread_id' => $thread_id,
                'run_id' => $run['id']
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Run failed with status: ' . $completed_run['status']
            ));
        }
        
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
add_action('wp_ajax_ucfc_agent_test_chat', 'ucfc_agent_test_chat');

/**
 * Create OpenAI Thread
 */
function ucfc_create_openai_thread($api_key) {
    $response = wp_remote_post('https://api.openai.com/v1/threads', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
            'OpenAI-Beta' => 'assistants=v2'
        ),
        'body' => json_encode(array()),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        throw new Exception('Failed to create thread: ' . $response->get_error_message());
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        throw new Exception('OpenAI Error: ' . $body['error']['message']);
    }
    
    return $body;
}

/**
 * Add Message to Thread
 */
function ucfc_add_message_to_thread($api_key, $thread_id, $message) {
    $response = wp_remote_post("https://api.openai.com/v1/threads/{$thread_id}/messages", array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
            'OpenAI-Beta' => 'assistants=v2'
        ),
        'body' => json_encode(array(
            'role' => 'user',
            'content' => $message
        )),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        throw new Exception('Failed to add message: ' . $response->get_error_message());
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        throw new Exception('OpenAI Error: ' . $body['error']['message']);
    }
    
    return $body;
}

/**
 * Create Run
 */
function ucfc_create_run($api_key, $thread_id, $assistant_id) {
    $response = wp_remote_post("https://api.openai.com/v1/threads/{$thread_id}/runs", array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
            'OpenAI-Beta' => 'assistants=v2'
        ),
        'body' => json_encode(array(
            'assistant_id' => $assistant_id
        )),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        throw new Exception('Failed to create run: ' . $response->get_error_message());
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        throw new Exception('OpenAI Error: ' . $body['error']['message']);
    }
    
    return $body;
}

/**
 * Poll Run Until Completion
 */
function ucfc_poll_run($api_key, $thread_id, $run_id, $max_attempts = 30) {
    $attempts = 0;
    
    while ($attempts < $max_attempts) {
        $response = wp_remote_get("https://api.openai.com/v1/threads/{$thread_id}/runs/{$run_id}", array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'OpenAI-Beta' => 'assistants=v2'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Failed to poll run: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            throw new Exception('OpenAI Error: ' . $body['error']['message']);
        }
        
        $status = $body['status'];
        
        if ($status === 'completed' || $status === 'failed' || $status === 'cancelled' || $status === 'expired') {
            return $body;
        }
        
        sleep(1);
        $attempts++;
    }
    
    throw new Exception('Run polling timeout');
}

/**
 * Get Thread Messages
 */
function ucfc_get_thread_messages($api_key, $thread_id) {
    $response = wp_remote_get("https://api.openai.com/v1/threads/{$thread_id}/messages", array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'OpenAI-Beta' => 'assistants=v2'
        ),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        throw new Exception('Failed to get messages: ' . $response->get_error_message());
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        throw new Exception('OpenAI Error: ' . $body['error']['message']);
    }
    
    return $body['data'];
}

/**
 * Get Agent Threads
 */
function ucfc_get_agent_threads() {
    check_ajax_referer('ucfc_agent_action', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    // Get stored threads from database
    global $wpdb;
    $table = $wpdb->prefix . 'ucfc_ai_conversations';
    
    $threads = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT JSON_EXTRACT(metadata, '$.thread_id') as thread_id, created_at 
         FROM {$table} 
         WHERE JSON_EXTRACT(metadata, '$.thread_id') IS NOT NULL 
         ORDER BY created_at DESC 
         LIMIT %d",
        20
    ));
    
    $formatted_threads = array();
    foreach ($threads as $thread) {
        if ($thread->thread_id) {
            $formatted_threads[] = array(
                'id' => trim($thread->thread_id, '"'),
                'date' => mysql2date('M j, Y g:i A', $thread->created_at)
            );
        }
    }
    
    wp_send_json_success(array('threads' => $formatted_threads));
}
add_action('wp_ajax_ucfc_get_agent_threads', 'ucfc_get_agent_threads');

/**
 * Get Agent Metrics
 */
function ucfc_get_agent_metrics() {
    check_ajax_referer('ucfc_agent_action', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    $timeframe = isset($_POST['timeframe']) ? sanitize_text_field($_POST['timeframe']) : 'week';
    
    // Calculate date range
    $date_ranges = array(
        'today' => '1 day',
        'week' => '7 days',
        'month' => '30 days'
    );
    
    $days = isset($date_ranges[$timeframe]) ? $date_ranges[$timeframe] : '7 days';
    
    global $wpdb;
    $table = $wpdb->prefix . 'ucfc_ai_conversations';
    
    // Get metrics
    $start_date = date('Y-m-d H:i:s', strtotime("-{$days}"));
    
    $conversations = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s",
        $start_date
    ));
    
    $threads = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT JSON_EXTRACT(metadata, '$.thread_id')) 
         FROM {$table} 
         WHERE created_at >= %s 
         AND JSON_EXTRACT(metadata, '$.thread_id') IS NOT NULL",
        $start_date
    ));
    
    $avg_time = $wpdb->get_var($wpdb->prepare(
        "SELECT AVG(response_time) FROM {$table} WHERE created_at >= %s",
        $start_date
    ));
    
    $functions = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s AND function_called IS NOT NULL",
        $start_date
    ));
    
    $success_rate = $wpdb->get_var($wpdb->prepare(
        "SELECT (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM {$table} WHERE created_at >= %s)) 
         FROM {$table} 
         WHERE created_at >= %s 
         AND ai_response NOT LIKE 'ERROR:%'",
        $start_date,
        $start_date
    ));
    
    wp_send_json_success(array(
        'conversations' => (int) $conversations,
        'threads' => (int) $threads,
        'messages' => (int) $conversations * 2, // User + AI = 2 messages per conversation
        'functions' => (int) $functions,
        'avg_time' => number_format($avg_time, 2),
        'success_rate' => number_format($success_rate, 1)
    ));
}
add_action('wp_ajax_ucfc_get_agent_metrics', 'ucfc_get_agent_metrics');

/**
 * Get Recent Activity
 */
function ucfc_get_agent_activity() {
    check_ajax_referer('ucfc_agent_action', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'ucfc_ai_conversations';
    
    $recent = $wpdb->get_results($wpdb->prepare(
        "SELECT user_message, function_called, created_at 
         FROM {$table} 
         ORDER BY created_at DESC 
         LIMIT %d",
        10
    ));
    
    $activities = array();
    foreach ($recent as $row) {
        $text = substr($row->user_message, 0, 60) . '...';
        if ($row->function_called) {
            $text .= ' [Function: ' . $row->function_called . ']';
        }
        
        $activities[] = array(
            'time' => human_time_diff(strtotime($row->created_at), current_time('timestamp')) . ' ago',
            'text' => $text
        );
    }
    
    wp_send_json_success(array('activities' => $activities));
}
add_action('wp_ajax_ucfc_get_agent_activity', 'ucfc_get_agent_activity');

/**
 * Get Agent Tools
 */
function ucfc_get_agent_tools() {
    check_ajax_referer('ucfc_agent_action', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    // Get tools from saved configuration
    $tools_json = get_option('ucfc_assistant_tools_edit', '[]');
    $tools = json_decode($tools_json, true);
    
    if (!is_array($tools)) {
        $tools = array();
    }
    
    $formatted_tools = array();
    foreach ($tools as $tool) {
        if (isset($tool['function'])) {
            $formatted_tools[] = array(
                'name' => $tool['function']['name'],
                'description' => isset($tool['function']['description']) ? $tool['function']['description'] : 'No description'
            );
        } elseif ($tool['type'] === 'code_interpreter') {
            $formatted_tools[] = array(
                'name' => 'Code Interpreter',
                'description' => 'Run Python code and analyze data'
            );
        } elseif ($tool['type'] === 'retrieval') {
            $formatted_tools[] = array(
                'name' => 'File Search',
                'description' => 'Search through uploaded files'
            );
        }
    }
    
    wp_send_json_success(array('tools' => $formatted_tools));
}
add_action('wp_ajax_ucfc_get_agent_tools', 'ucfc_get_agent_tools');
