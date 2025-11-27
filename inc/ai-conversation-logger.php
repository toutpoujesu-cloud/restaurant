<?php
/**
 * AI Conversation Logger
 * 
 * Logs all AI conversations for analytics, training, and compliance
 * 
 * Features:
 * - Save all conversations to database
 * - Track user satisfaction
 * - Monitor response times
 * - Analyze common questions
 * - Export conversation data
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class UCFC_Conversation_Logger {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ucfc_ai_conversations';
        
        // Create table on activation
        add_action('after_switch_theme', array($this, 'create_tables'));
    }
    
    /**
     * Create conversation logging tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            user_message text NOT NULL,
            ai_response text NOT NULL,
            ai_provider varchar(50) NOT NULL,
            ai_model varchar(100) NOT NULL,
            tokens_used int DEFAULT 0,
            response_time float DEFAULT 0,
            function_called varchar(255) DEFAULT NULL,
            function_result text DEFAULT NULL,
            user_satisfaction tinyint DEFAULT NULL,
            user_ip varchar(100) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            metadata text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY created_at (created_at),
            KEY ai_provider (ai_provider)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Log a conversation
     */
    public function log_conversation($data) {
        global $wpdb;
        
        $defaults = array(
            'session_id' => $this->get_session_id(),
            'user_id' => get_current_user_id(),
            'user_message' => '',
            'ai_response' => '',
            'ai_provider' => get_option('ucfc_ai_model_provider', 'openai'),
            'ai_model' => get_option('ucfc_ai_model_version', 'gpt-4-turbo'),
            'tokens_used' => 0,
            'response_time' => 0,
            'function_called' => null,
            'function_result' => null,
            'user_satisfaction' => null,
            'user_ip' => $this->get_user_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'metadata' => null
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Convert metadata array to JSON if needed
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }
        
        $wpdb->insert($this->table_name, $data);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get or create session ID
     */
    private function get_session_id() {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['ucfc_ai_session_id'])) {
            $_SESSION['ucfc_ai_session_id'] = uniqid('ucfc_', true);
        }
        
        return $_SESSION['ucfc_ai_session_id'];
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Get conversation by ID
     */
    public function get_conversation($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Get conversations by session
     */
    public function get_session_conversations($session_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE session_id = %s ORDER BY created_at ASC",
            $session_id
        ));
    }
    
    /**
     * Get recent conversations
     */
    public function get_recent_conversations($limit = 50) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
    }
    
    /**
     * Update satisfaction rating
     */
    public function update_satisfaction($conversation_id, $rating) {
        global $wpdb;
        return $wpdb->update(
            $this->table_name,
            array('user_satisfaction' => intval($rating)),
            array('id' => intval($conversation_id))
        );
    }
    
    /**
     * Get analytics data
     */
    public function get_analytics($date_from = null, $date_to = null) {
        global $wpdb;
        
        $where = "1=1";
        if ($date_from) {
            $where .= $wpdb->prepare(" AND created_at >= %s", $date_from);
        }
        if ($date_to) {
            $where .= $wpdb->prepare(" AND created_at <= %s", $date_to);
        }
        
        // Total conversations
        $total_conversations = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE {$where}");
        
        // Unique sessions
        $unique_sessions = $wpdb->get_var("SELECT COUNT(DISTINCT session_id) FROM {$this->table_name} WHERE {$where}");
        
        // Average response time
        $avg_response_time = $wpdb->get_var("SELECT AVG(response_time) FROM {$this->table_name} WHERE {$where}");
        
        // Total tokens used
        $total_tokens = $wpdb->get_var("SELECT SUM(tokens_used) FROM {$this->table_name} WHERE {$where}");
        
        // Average satisfaction
        $avg_satisfaction = $wpdb->get_var("SELECT AVG(user_satisfaction) FROM {$this->table_name} WHERE {$where} AND user_satisfaction IS NOT NULL");
        
        // Provider breakdown
        $provider_stats = $wpdb->get_results("
            SELECT ai_provider, COUNT(*) as count 
            FROM {$this->table_name} 
            WHERE {$where} 
            GROUP BY ai_provider
        ");
        
        // Most common questions
        $common_questions = $wpdb->get_results("
            SELECT user_message, COUNT(*) as count 
            FROM {$this->table_name} 
            WHERE {$where} 
            GROUP BY user_message 
            ORDER BY count DESC 
            LIMIT 10
        ", ARRAY_A);
        
        // Function usage
        $function_usage = $wpdb->get_results("
            SELECT function_called, COUNT(*) as count 
            FROM {$this->table_name} 
            WHERE {$where} AND function_called IS NOT NULL 
            GROUP BY function_called 
            ORDER BY count DESC
        ", ARRAY_A);
        
        // Hourly distribution
        $hourly_distribution = $wpdb->get_results("
            SELECT HOUR(created_at) as hour, COUNT(*) as count 
            FROM {$this->table_name} 
            WHERE {$where} 
            GROUP BY HOUR(created_at) 
            ORDER BY hour
        ", ARRAY_A);
        
        return array(
            'total_conversations' => intval($total_conversations),
            'unique_sessions' => intval($unique_sessions),
            'avg_response_time' => round(floatval($avg_response_time), 2),
            'total_tokens' => intval($total_tokens),
            'avg_satisfaction' => round(floatval($avg_satisfaction), 2),
            'provider_stats' => $provider_stats,
            'common_questions' => $common_questions,
            'function_usage' => $function_usage,
            'hourly_distribution' => $hourly_distribution
        );
    }
    
    /**
     * Export conversations to CSV
     */
    public function export_to_csv($date_from = null, $date_to = null) {
        global $wpdb;
        
        $where = "1=1";
        if ($date_from) {
            $where .= $wpdb->prepare(" AND created_at >= %s", $date_from);
        }
        if ($date_to) {
            $where .= $wpdb->prepare(" AND created_at <= %s", $date_to);
        }
        
        $conversations = $wpdb->get_results("SELECT * FROM {$this->table_name} WHERE {$where} ORDER BY created_at DESC", ARRAY_A);
        
        if (empty($conversations)) {
            return false;
        }
        
        $filename = 'ai-conversations-' . date('Y-m-d-H-i-s') . '.csv';
        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['path'] . '/' . $filename;
        
        $fp = fopen($filepath, 'w');
        
        // Headers
        fputcsv($fp, array_keys($conversations[0]));
        
        // Data
        foreach ($conversations as $conversation) {
            fputcsv($fp, $conversation);
        }
        
        fclose($fp);
        
        return $upload_dir['url'] . '/' . $filename;
    }
    
    /**
     * Delete old conversations
     */
    public function delete_old_conversations($days = 90) {
        global $wpdb;
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }
    
    /**
     * Get conversation trends
     */
    public function get_trends($days = 30) {
        global $wpdb;
        
        $date_from = date('Y-m-d', strtotime("-{$days} days"));
        
        $daily_counts = $wpdb->get_results($wpdb->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM {$this->table_name} 
            WHERE created_at >= %s 
            GROUP BY DATE(created_at) 
            ORDER BY date ASC
        ", $date_from), ARRAY_A);
        
        return $daily_counts;
    }
}

// Initialize logger
global $ucfc_conversation_logger;
$ucfc_conversation_logger = new UCFC_Conversation_Logger();
