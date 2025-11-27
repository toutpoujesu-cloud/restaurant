<?php
/**
 * AI Rate Limiter
 * 
 * Prevents API abuse and manages costs
 * 
 * Features:
 * - Per-user rate limiting
 * - IP-based rate limiting
 * - Configurable limits
 * - Automatic lockout
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class UCFC_AI_Rate_Limiter {
    
    private $max_requests_per_hour = 20;
    private $max_requests_per_day = 100;
    
    public function __construct() {
        $this->max_requests_per_hour = intval(get_option('ucfc_ai_rate_limit_hour', 20));
        $this->max_requests_per_day = intval(get_option('ucfc_ai_rate_limit_day', 100));
    }
    
    /**
     * Get user identifier (IP or user ID)
     */
    private function get_identifier() {
        if (is_user_logged_in()) {
            return 'user_' . get_current_user_id();
        }
        
        // Get IP address
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return 'ip_' . md5($ip);
    }
    
    /**
     * Check if request is allowed
     */
    public function is_allowed() {
        // Check if rate limiting is enabled
        if (get_option('ucfc_ai_enable_rate_limit', '1') !== '1') {
            return array('allowed' => true);
        }
        
        // Admin users bypass rate limits
        if (current_user_can('manage_options')) {
            return array('allowed' => true);
        }
        
        $identifier = $this->get_identifier();
        
        // Check hourly limit
        $hourly_key = 'ucfc_rate_limit_hour_' . $identifier;
        $hourly_count = intval(get_transient($hourly_key));
        
        if ($hourly_count >= $this->max_requests_per_hour) {
            return array(
                'allowed' => false,
                'reason' => 'hourly_limit',
                'message' => 'Too many requests this hour. Please try again later.',
                'retry_after' => 3600
            );
        }
        
        // Check daily limit
        $daily_key = 'ucfc_rate_limit_day_' . $identifier;
        $daily_count = intval(get_transient($daily_key));
        
        if ($daily_count >= $this->max_requests_per_day) {
            return array(
                'allowed' => false,
                'reason' => 'daily_limit',
                'message' => 'Daily limit reached. Please try again tomorrow.',
                'retry_after' => 86400
            );
        }
        
        return array(
            'allowed' => true,
            'hourly_remaining' => $this->max_requests_per_hour - $hourly_count,
            'daily_remaining' => $this->max_requests_per_day - $daily_count
        );
    }
    
    /**
     * Record a request
     */
    public function record_request() {
        if (get_option('ucfc_ai_enable_rate_limit', '1') !== '1') {
            return;
        }
        
        if (current_user_can('manage_options')) {
            return; // Don't count admin requests
        }
        
        $identifier = $this->get_identifier();
        
        // Increment hourly counter
        $hourly_key = 'ucfc_rate_limit_hour_' . $identifier;
        $hourly_count = intval(get_transient($hourly_key));
        set_transient($hourly_key, $hourly_count + 1, 3600);
        
        // Increment daily counter
        $daily_key = 'ucfc_rate_limit_day_' . $identifier;
        $daily_count = intval(get_transient($daily_key));
        set_transient($daily_key, $daily_count + 1, 86400);
    }
    
    /**
     * Get user's current usage
     */
    public function get_usage() {
        $identifier = $this->get_identifier();
        
        $hourly_key = 'ucfc_rate_limit_hour_' . $identifier;
        $daily_key = 'ucfc_rate_limit_day_' . $identifier;
        
        $hourly_count = intval(get_transient($hourly_key));
        $daily_count = intval(get_transient($daily_key));
        
        return array(
            'hourly_used' => $hourly_count,
            'hourly_limit' => $this->max_requests_per_hour,
            'hourly_remaining' => max(0, $this->max_requests_per_hour - $hourly_count),
            'daily_used' => $daily_count,
            'daily_limit' => $this->max_requests_per_day,
            'daily_remaining' => max(0, $this->max_requests_per_day - $daily_count)
        );
    }
    
    /**
     * Reset user's limits (admin function)
     */
    public function reset_user_limits($identifier) {
        delete_transient('ucfc_rate_limit_hour_' . $identifier);
        delete_transient('ucfc_rate_limit_day_' . $identifier);
    }
}

// Initialize rate limiter
global $ucfc_rate_limiter;
$ucfc_rate_limiter = new UCFC_AI_Rate_Limiter();

/**
 * Add rate limit settings
 */
add_action('admin_init', 'ucfc_register_rate_limit_settings');
function ucfc_register_rate_limit_settings() {
    register_setting('ucfc_ai_assistant', 'ucfc_ai_enable_rate_limit');
    register_setting('ucfc_ai_assistant', 'ucfc_ai_rate_limit_hour');
    register_setting('ucfc_ai_assistant', 'ucfc_ai_rate_limit_day');
}
