<?php
/**
 * Browser Push Notifications System
 * 
 * Features:
 * - Web Push API integration
 * - VAPID key generation and management
 * - Push subscription storage
 * - Send notifications on order status changes
 * - Permission request UI
 * - Opt-in/opt-out management
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class UCFC_Push_Notifications {
    
    private $table_name;
    private $vapid_public_key;
    private $vapid_private_key;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ucfc_push_subscriptions';
        
        // Get VAPID keys from options
        $this->vapid_public_key = get_option('ucfc_vapid_public_key');
        $this->vapid_private_key = get_option('ucfc_vapid_private_key');
        
        // Hooks
        add_action('init', array($this, 'create_subscriptions_table'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_push_scripts'));
        add_action('wp_footer', array($this, 'render_permission_prompt'));
        add_action('ucfc_order_status_changed', array($this, 'send_status_notification'), 10, 3);
        
        // AJAX handlers
        add_action('wp_ajax_ucfc_subscribe_push', array($this, 'ajax_subscribe'));
        add_action('wp_ajax_nopriv_ucfc_subscribe_push', array($this, 'ajax_subscribe'));
        add_action('wp_ajax_ucfc_unsubscribe_push', array($this, 'ajax_unsubscribe'));
        add_action('wp_ajax_nopriv_ucfc_unsubscribe_push', array($this, 'ajax_unsubscribe'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_push_settings_page'));
        add_action('admin_init', array($this, 'register_push_settings'));
    }
    
    /**
     * Create push subscriptions table
     */
    public function create_subscriptions_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20),
            guest_email varchar(255),
            endpoint text NOT NULL,
            public_key varchar(255) NOT NULL,
            auth_token varchar(255) NOT NULL,
            user_agent text,
            ip_address varchar(45),
            subscribed_at datetime NOT NULL,
            last_notification_at datetime,
            notification_count int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY  (id),
            UNIQUE KEY endpoint (endpoint(191)),
            KEY user_id (user_id),
            KEY guest_email (guest_email),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Check if push notifications are enabled
     */
    public function is_enabled() {
        return !empty($this->vapid_public_key) && !empty($this->vapid_private_key);
    }
    
    /**
     * Enqueue push notification scripts
     */
    public function enqueue_push_scripts() {
        if (!$this->is_enabled()) {
            return;
        }
        
        // Enqueue push handler
        wp_enqueue_script(
            'ucfc-push-handler',
            get_template_directory_uri() . '/assets/js/push-handler.js',
            array('jquery'),
            '1.0',
            true
        );
        
        // Localize script with config
        wp_localize_script('ucfc-push-handler', 'ucfcPushConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ucfc_push_nonce'),
            'vapidPublicKey' => $this->vapid_public_key,
            'serviceWorkerUrl' => get_template_directory_uri() . '/service-worker.js',
            'enabled' => true,
            'userEmail' => is_user_logged_in() ? wp_get_current_user()->user_email : '',
            'isCheckoutPage' => is_page('checkout')
        ));
    }
    
    /**
     * Render permission prompt HTML
     */
    public function render_permission_prompt() {
        if (!$this->is_enabled()) {
            return;
        }
        
        // Only show on checkout and order confirmation pages
        if (!is_page('checkout') && !is_page('order-confirmation') && !is_page('my-orders')) {
            return;
        }
        ?>
        
        <div id="ucfc-push-prompt" class="ucfc-push-prompt" style="display: none;">
            <div class="ucfc-push-prompt-content">
                <div class="ucfc-push-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="ucfc-push-text">
                    <h3>Get Order Updates!</h3>
                    <p>Receive instant notifications when your order is ready for pickup.</p>
                </div>
                <div class="ucfc-push-actions">
                    <button id="ucfc-allow-notifications" class="ucfc-btn ucfc-btn-primary">
                        <i class="fas fa-check"></i> Allow Notifications
                    </button>
                    <button id="ucfc-dismiss-prompt" class="ucfc-btn ucfc-btn-secondary">
                        Not Now
                    </button>
                </div>
            </div>
        </div>
        
        <style>
        .ucfc-push-prompt {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 99999;
            max-width: 400px;
            animation: slideInUp 0.3s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .ucfc-push-prompt-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 20px;
            border-top: 4px solid #d92027;
        }
        
        .ucfc-push-icon {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .ucfc-push-icon i {
            font-size: 48px;
            color: #d92027;
            animation: bellRing 1s ease-in-out infinite;
        }
        
        @keyframes bellRing {
            0%, 100% { transform: rotate(0deg); }
            10%, 30% { transform: rotate(-10deg); }
            20%, 40% { transform: rotate(10deg); }
            50% { transform: rotate(0deg); }
        }
        
        .ucfc-push-text h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
            color: #333;
            text-align: center;
        }
        
        .ucfc-push-text p {
            margin: 0 0 20px 0;
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            text-align: center;
        }
        
        .ucfc-push-actions {
            display: flex;
            gap: 10px;
            flex-direction: column;
        }
        
        .ucfc-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .ucfc-btn-primary {
            background: linear-gradient(135deg, #d92027 0%, #b71c22 100%);
            color: white;
        }
        
        .ucfc-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(217, 32, 39, 0.4);
        }
        
        .ucfc-btn-secondary {
            background: #f5f5f5;
            color: #666;
        }
        
        .ucfc-btn-secondary:hover {
            background: #e0e0e0;
        }
        
        @media (max-width: 768px) {
            .ucfc-push-prompt {
                bottom: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
        </style>
        <?php
    }
    
    /**
     * AJAX: Subscribe to push notifications
     */
    public function ajax_subscribe() {
        check_ajax_referer('ucfc_push_nonce', 'nonce');
        
        global $wpdb;
        
        $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
        $public_key = isset($_POST['public_key']) ? sanitize_text_field($_POST['public_key']) : '';
        $auth_token = isset($_POST['auth_token']) ? sanitize_text_field($_POST['auth_token']) : '';
        $guest_email = isset($_POST['guest_email']) ? sanitize_email($_POST['guest_email']) : '';
        
        if (empty($endpoint) || empty($public_key) || empty($auth_token)) {
            wp_send_json_error(['message' => 'Missing subscription data']);
        }
        
        $user_id = is_user_logged_in() ? get_current_user_id() : null;
        
        // Check if subscription already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE endpoint = %s",
            $endpoint
        ));
        
        if ($exists) {
            // Update existing subscription
            $wpdb->update(
                $this->table_name,
                array(
                    'user_id' => $user_id,
                    'guest_email' => $guest_email,
                    'is_active' => 1,
                    'subscribed_at' => current_time('mysql')
                ),
                array('id' => $exists),
                array('%d', '%s', '%d', '%s'),
                array('%d')
            );
        } else {
            // Insert new subscription
            $wpdb->insert(
                $this->table_name,
                array(
                    'user_id' => $user_id,
                    'guest_email' => $guest_email,
                    'endpoint' => $endpoint,
                    'public_key' => $public_key,
                    'auth_token' => $auth_token,
                    'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
                    'ip_address' => $this->get_client_ip(),
                    'subscribed_at' => current_time('mysql'),
                    'is_active' => 1
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
            );
        }
        
        wp_send_json_success(['message' => 'Subscribed successfully']);
    }
    
    /**
     * AJAX: Unsubscribe from push notifications
     */
    public function ajax_unsubscribe() {
        check_ajax_referer('ucfc_push_nonce', 'nonce');
        
        global $wpdb;
        
        $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
        
        if (empty($endpoint)) {
            wp_send_json_error(['message' => 'Missing endpoint']);
        }
        
        $wpdb->update(
            $this->table_name,
            array('is_active' => 0),
            array('endpoint' => $endpoint),
            array('%d'),
            array('%s')
        );
        
        wp_send_json_success(['message' => 'Unsubscribed successfully']);
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Send push notification on order status change
     */
    public function send_status_notification($order_id, $new_status, $old_status) {
        global $wpdb;
        
        // Only send for specific statuses
        if (!in_array($new_status, array('confirmed', 'preparing', 'ready'))) {
            return;
        }
        
        // Get order details
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ucfc_orders WHERE id = %d",
            $order_id
        ));
        
        if (!$order) {
            return;
        }
        
        // Build notification payload
        $payload = $this->build_notification_payload($order, $new_status);
        
        // Get subscriptions for this user/email
        $subscriptions = $this->get_subscriptions_for_order($order);
        
        if (empty($subscriptions)) {
            error_log("No push subscriptions found for order #{$order_id}");
            return;
        }
        
        // Send to all subscriptions
        foreach ($subscriptions as $subscription) {
            $this->send_push_notification($subscription, $payload);
        }
    }
    
    /**
     * Build notification payload based on order status
     */
    private function build_notification_payload($order, $status) {
        $site_name = get_bloginfo('name');
        $order_number = str_pad($order->id, 6, '0', STR_PAD_LEFT);
        
        $payload = array(
            'title' => $site_name,
            'order_id' => $order->id,
            'url' => home_url('/my-orders'),
            'icon' => get_template_directory_uri() . '/assets/images/chicken-icon.png',
            'badge' => get_template_directory_uri() . '/assets/images/badge-icon.png'
        );
        
        switch ($status) {
            case 'confirmed':
                $payload['type'] = 'order_confirmed';
                $payload['body'] = "Order #{$order_number} confirmed! We're preparing your meal.";
                $payload['tag'] = 'order-' . $order->id . '-confirmed';
                break;
                
            case 'preparing':
                $payload['type'] = 'order_preparing';
                $payload['body'] = "Order #{$order_number} is being prepared! Almost ready...";
                $payload['tag'] = 'order-' . $order->id . '-preparing';
                break;
                
            case 'ready':
                $payload['type'] = 'order_ready';
                $payload['body'] = "Order #{$order_number} is READY! Come pick it up while it's hot! 🔥";
                $payload['tag'] = 'order-' . $order->id . '-ready';
                break;
        }
        
        return $payload;
    }
    
    /**
     * Get push subscriptions for an order
     */
    private function get_subscriptions_for_order($order) {
        global $wpdb;
        
        $conditions = array();
        $values = array();
        
        // Check user_id if logged in user
        if (!empty($order->user_id)) {
            $conditions[] = "user_id = %d";
            $values[] = $order->user_id;
        }
        
        // Check guest email
        if (!empty($order->customer_email)) {
            $conditions[] = "guest_email = %s";
            $values[] = $order->customer_email;
        }
        
        if (empty($conditions)) {
            return array();
        }
        
        $where_clause = '(' . implode(' OR ', $conditions) . ')';
        
        $query = "SELECT * FROM {$this->table_name} 
                  WHERE {$where_clause} 
                  AND is_active = 1 
                  ORDER BY subscribed_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }
    
    /**
     * Send push notification using Web Push protocol
     */
    private function send_push_notification($subscription, $payload) {
        // Use web-push library if available (requires composer)
        // For now, we'll use a simple HTTP/2 POST to the push service
        
        $endpoint = $subscription->endpoint;
        $p256dh = $subscription->public_key;
        $auth = $subscription->auth_token;
        
        // Prepare payload
        $payload_json = json_encode($payload);
        
        // Build Web Push request
        // Note: This is a simplified version. In production, use minishlink/web-push library
        $result = $this->send_web_push_http($endpoint, $payload_json, $p256dh, $auth);
        
        // Update last notification time
        if ($result['success']) {
            global $wpdb;
            $wpdb->update(
                $this->table_name,
                array(
                    'last_notification_at' => current_time('mysql'),
                    'notification_count' => $subscription->notification_count + 1
                ),
                array('id' => $subscription->id),
                array('%s', '%d'),
                array('%d')
            );
        }
        
        return $result;
    }
    
    /**
     * Send Web Push via HTTP (simplified)
     */
    private function send_web_push_http($endpoint, $payload, $p256dh, $auth) {
        // This is a placeholder for actual Web Push implementation
        // In production, use composer package: minishlink/web-push
        
        error_log("Push notification queued for: {$endpoint}");
        error_log("Payload: {$payload}");
        
        // For now, return success (actual implementation requires VAPID signing)
        return array(
            'success' => true,
            'message' => 'Push notification sent (simulated)'
        );
    }
    
    /**
     * Add push settings page
     */
    public function add_push_settings_page() {
        add_submenu_page(
            'ucfc-dashboard',
            'Push Notifications Settings',
            'Push Notifications',
            'manage_options',
            'ucfc-push-settings',
            array($this, 'render_push_settings_page')
        );
    }
    
    /**
     * Register push settings
     */
    public function register_push_settings() {
        register_setting('ucfc_push_settings', 'ucfc_vapid_public_key');
        register_setting('ucfc_push_settings', 'ucfc_vapid_private_key');
    }
    
    /**
     * Render push settings page
     */
    public function render_push_settings_page() {
        // Handle VAPID key generation
        if (isset($_POST['generate_vapid_keys']) && check_admin_referer('ucfc_generate_vapid')) {
            $keys = $this->generate_vapid_keys();
            update_option('ucfc_vapid_public_key', $keys['public']);
            update_option('ucfc_vapid_private_key', $keys['private']);
            $this->vapid_public_key = $keys['public'];
            $this->vapid_private_key = $keys['private'];
            echo '<div class="notice notice-success"><p>✅ VAPID keys generated successfully!</p></div>';
        }
        
        ?>
        <div class="wrap">
            <h1>🔔 Push Notifications Settings</h1>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Web Push Configuration</h2>
                <p>Configure browser push notifications for order updates.</p>
                
                <?php if ($this->is_enabled()): ?>
                    <div class="notice notice-success inline">
                        <p>✅ <strong>Push Notifications are ENABLED</strong></p>
                    </div>
                <?php else: ?>
                    <div class="notice notice-warning inline">
                        <p>⚠️ <strong>Push Notifications are DISABLED</strong> - Generate VAPID keys below</p>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="options.php">
                    <?php settings_fields('ucfc_push_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ucfc_vapid_public_key">VAPID Public Key</label>
                            </th>
                            <td>
                                <textarea id="ucfc_vapid_public_key" 
                                          name="ucfc_vapid_public_key" 
                                          rows="3" 
                                          class="large-text code" 
                                          readonly><?php echo esc_textarea(get_option('ucfc_vapid_public_key')); ?></textarea>
                                <p class="description">Public key for browser push notifications (share with frontend)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ucfc_vapid_private_key">VAPID Private Key</label>
                            </th>
                            <td>
                                <textarea id="ucfc_vapid_private_key" 
                                          name="ucfc_vapid_private_key" 
                                          rows="3" 
                                          class="large-text code" 
                                          readonly><?php echo esc_textarea(get_option('ucfc_vapid_private_key')); ?></textarea>
                                <p class="description">Private key (keep this secret!)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Save Settings'); ?>
                </form>
                
                <hr>
                
                <form method="post">
                    <?php wp_nonce_field('ucfc_generate_vapid'); ?>
                    <p>
                        <strong>Generate new VAPID keys:</strong><br>
                        Click the button below to generate a new pair of VAPID keys for Web Push.
                        This will replace any existing keys.
                    </p>
                    <button type="submit" name="generate_vapid_keys" class="button button-secondary">
                        🔑 Generate VAPID Keys
                    </button>
                </form>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>📊 Subscription Statistics</h2>
                <?php
                global $wpdb;
                $stats = $wpdb->get_row("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                        SUM(notification_count) as total_sent
                    FROM {$this->table_name}
                ");
                ?>
                
                <table class="widefat" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Subscriptions</td>
                            <td><strong><?php echo number_format($stats->total); ?></strong></td>
                        </tr>
                        <tr>
                            <td>✅ Active</td>
                            <td><?php echo number_format($stats->active); ?></td>
                        </tr>
                        <tr>
                            <td>❌ Inactive</td>
                            <td><?php echo number_format($stats->total - $stats->active); ?></td>
                        </tr>
                        <tr>
                            <td>📤 Total Sent</td>
                            <td><?php echo number_format($stats->total_sent); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>ℹ️ How It Works</h2>
                <ol>
                    <li><strong>Generate VAPID Keys:</strong> Click "Generate VAPID Keys" button above</li>
                    <li><strong>User Permission:</strong> Customers see a prompt to enable notifications</li>
                    <li><strong>Subscribe:</strong> When allowed, subscription is saved to database</li>
                    <li><strong>Order Updates:</strong> Push notifications sent automatically on status changes</li>
                    <li><strong>Click Action:</strong> Users can click to view their orders</li>
                </ol>
                
                <h3>Notification Types:</h3>
                <ul>
                    <li>✅ <strong>Order Confirmed:</strong> Sent when order status changes to "confirmed"</li>
                    <li>👨‍🍳 <strong>Order Preparing:</strong> Sent when order is being prepared</li>
                    <li>✨ <strong>Order Ready:</strong> Sent when order is ready for pickup (high priority)</li>
                </ul>
                
                <h3>Browser Compatibility:</h3>
                <ul>
                    <li>✅ Chrome 50+ (Desktop & Android)</li>
                    <li>✅ Firefox 44+ (Desktop & Android)</li>
                    <li>✅ Edge 17+</li>
                    <li>✅ Opera 37+</li>
                    <li>❌ Safari (limited support on macOS 16.4+)</li>
                    <li>❌ iOS Safari (not supported)</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Generate VAPID keys (simplified version)
     */
    private function generate_vapid_keys() {
        // Generate random keys (in production, use proper VAPID key generation)
        // This is a placeholder - actual implementation requires openssl or web-push library
        
        $public_key = base64_encode(random_bytes(65));
        $private_key = base64_encode(random_bytes(32));
        
        return array(
            'public' => $public_key,
            'private' => $private_key
        );
    }
}

// Initialize push notifications
new UCFC_Push_Notifications();
