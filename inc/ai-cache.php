<?php
/**
 * AI Response Cache
 * 
 * Caches AI responses to reduce API calls and improve performance
 * 
 * Features:
 * - Cache identical questions
 * - Smart cache invalidation
 * - Configurable TTL
 * - Cache statistics
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class UCFC_AI_Cache {
    
    private $cache_group = 'ucfc_ai_responses';
    private $ttl = 3600; // 1 hour default
    
    public function __construct() {
        $this->ttl = intval(get_option('ucfc_ai_cache_ttl', 3600));
    }
    
    /**
     * Generate cache key from message
     */
    private function get_cache_key($message, $provider, $model) {
        return 'ai_' . md5(strtolower(trim($message)) . '_' . $provider . '_' . $model);
    }
    
    /**
     * Get cached response
     */
    public function get($message, $provider, $model) {
        // Check if caching is enabled
        if (get_option('ucfc_ai_enable_cache', '1') !== '1') {
            return false;
        }
        
        $key = $this->get_cache_key($message, $provider, $model);
        $cached = get_transient($key);
        
        if ($cached !== false) {
            // Increment hit counter
            $this->increment_stat('hits');
            
            return $cached;
        }
        
        // Increment miss counter
        $this->increment_stat('misses');
        
        return false;
    }
    
    /**
     * Store response in cache
     */
    public function set($message, $provider, $model, $response, $ttl = null) {
        if (get_option('ucfc_ai_enable_cache', '1') !== '1') {
            return false;
        }
        
        $key = $this->get_cache_key($message, $provider, $model);
        $ttl = $ttl !== null ? $ttl : $this->ttl;
        
        $cache_data = array(
            'response' => $response,
            'cached_at' => current_time('mysql'),
            'provider' => $provider,
            'model' => $model
        );
        
        return set_transient($key, $cache_data, $ttl);
    }
    
    /**
     * Clear all AI caches
     */
    public function clear_all() {
        global $wpdb;
        
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ai_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ai_%'");
        
        // Reset stats
        update_option('ucfc_ai_cache_hits', 0);
        update_option('ucfc_ai_cache_misses', 0);
        
        return true;
    }
    
    /**
     * Get cache statistics
     */
    public function get_stats() {
        $hits = intval(get_option('ucfc_ai_cache_hits', 0));
        $misses = intval(get_option('ucfc_ai_cache_misses', 0));
        $total = $hits + $misses;
        
        $hit_rate = $total > 0 ? round(($hits / $total) * 100, 2) : 0;
        
        // Count cached items
        global $wpdb;
        $cached_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_ai_%'");
        
        return array(
            'hits' => $hits,
            'misses' => $misses,
            'total_requests' => $total,
            'hit_rate' => $hit_rate,
            'cached_items' => intval($cached_count)
        );
    }
    
    /**
     * Increment statistics
     */
    private function increment_stat($type) {
        $option = 'ucfc_ai_cache_' . $type;
        $current = intval(get_option($option, 0));
        update_option($option, $current + 1);
    }
    
    /**
     * Clear expired caches
     */
    public function clear_expired() {
        global $wpdb;
        
        $time = time();
        
        // Get all AI cache timeouts
        $expired = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_timeout_ai_%' 
             AND option_value < %d",
            $time
        ));
        
        $deleted = 0;
        foreach ($expired as $timeout) {
            $transient = str_replace('_transient_timeout_', '', $timeout);
            delete_transient($transient);
            $deleted++;
        }
        
        return $deleted;
    }
}

// Initialize cache
global $ucfc_ai_cache;
$ucfc_ai_cache = new UCFC_AI_Cache();

/**
 * Add cache settings to AI Assistant page
 */
add_action('admin_init', 'ucfc_register_cache_settings');
function ucfc_register_cache_settings() {
    register_setting('ucfc_ai_assistant', 'ucfc_ai_enable_cache');
    register_setting('ucfc_ai_assistant', 'ucfc_ai_cache_ttl');
}

/**
 * AJAX: Clear cache
 */
add_action('wp_ajax_ucfc_clear_cache', function() {
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    global $ucfc_ai_cache;
    $ucfc_ai_cache->clear_all();
    
    wp_send_json_success(array('message' => 'Cache cleared successfully'));
});

/**
 * AJAX: Get cache stats
 */
add_action('wp_ajax_ucfc_get_cache_stats', function() {
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    global $ucfc_ai_cache;
    $stats = $ucfc_ai_cache->get_stats();
    
    wp_send_json_success($stats);
});
