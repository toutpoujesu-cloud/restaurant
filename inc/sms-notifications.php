<?php
/**
 * SMS Notifications System with Twilio Integration
 * 
 * Features:
 * - Order confirmation SMS
 * - Order status update SMS
 * - Pickup reminder SMS (15 mins before)
 * - SMS queue with retry logic
 * - Twilio API integration
 * - Settings panel for credentials
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class UCFC_SMS_Notifications {
    
    private $twilio_sid;
    private $twilio_token;
    private $twilio_phone;
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ucfc_sms_queue';
        
        // Get Twilio credentials from wp-config.php or options
        $this->twilio_sid = defined('TWILIO_ACCOUNT_SID') ? TWILIO_ACCOUNT_SID : get_option('ucfc_twilio_sid');
        $this->twilio_token = defined('TWILIO_AUTH_TOKEN') ? TWILIO_AUTH_TOKEN : get_option('ucfc_twilio_token');
        $this->twilio_phone = defined('TWILIO_PHONE_NUMBER') ? TWILIO_PHONE_NUMBER : get_option('ucfc_twilio_phone');
        
        // Hooks
        add_action('init', array($this, 'create_sms_queue_table'));
        add_action('ucfc_order_created', array($this, 'send_order_confirmation_sms'), 10, 2);
        add_action('ucfc_order_status_changed', array($this, 'send_status_update_sms'), 10, 3);
        add_action('ucfc_process_sms_queue', array($this, 'process_sms_queue'));
        add_action('admin_menu', array($this, 'add_sms_settings_page'));
        add_action('admin_init', array($this, 'register_sms_settings'));
        
        // Schedule SMS queue processing (every 5 minutes)
        if (!wp_next_scheduled('ucfc_process_sms_queue')) {
            wp_schedule_event(time(), 'five_minutes', 'ucfc_process_sms_queue');
        }
        
        // Schedule pickup reminders (every 5 minutes)
        add_action('ucfc_check_pickup_reminders', array($this, 'send_pickup_reminders'));
        if (!wp_next_scheduled('ucfc_check_pickup_reminders')) {
            wp_schedule_event(time(), 'five_minutes', 'ucfc_check_pickup_reminders');
        }
    }
    
    /**
     * Create SMS queue table
     */
    public function create_sms_queue_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            phone_number varchar(20) NOT NULL,
            message text NOT NULL,
            message_type varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            attempts int(11) NOT NULL DEFAULT 0,
            twilio_sid varchar(50),
            error_message text,
            created_at datetime NOT NULL,
            sent_at datetime,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Check if SMS is enabled
     */
    public function is_enabled() {
        return !empty($this->twilio_sid) && !empty($this->twilio_token) && !empty($this->twilio_phone);
    }
    
    /**
     * Format phone number for Twilio (E.164 format)
     */
    private function format_phone_number($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add +1 for US numbers if not present (assuming US/Canada)
        if (strlen($phone) == 10) {
            $phone = '+1' . $phone;
        } elseif (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
            $phone = '+' . $phone;
        } elseif (substr($phone, 0, 1) != '+') {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Queue an SMS for sending
     */
    public function queue_sms($order_id, $phone_number, $message, $message_type = 'general') {
        global $wpdb;
        
        if (!$this->is_enabled()) {
            error_log('SMS not enabled - missing Twilio credentials');
            return false;
        }
        
        $phone_number = $this->format_phone_number($phone_number);
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'order_id' => $order_id,
                'phone_number' => $phone_number,
                'message' => $message,
                'message_type' => $message_type,
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Send SMS via Twilio API
     */
    private function send_sms_via_twilio($to, $message) {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->twilio_sid}/Messages.json";
        
        $data = array(
            'From' => $this->twilio_phone,
            'To' => $to,
            'Body' => $message
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode("{$this->twilio_sid}:{$this->twilio_token}")
            ),
            'body' => $data,
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code >= 200 && $status_code < 300 && isset($body['sid'])) {
            return array(
                'success' => true,
                'sid' => $body['sid']
            );
        }
        
        return array(
            'success' => false,
            'error' => isset($body['message']) ? $body['message'] : 'Unknown Twilio error'
        );
    }
    
    /**
     * Process SMS queue
     */
    public function process_sms_queue() {
        global $wpdb;
        
        if (!$this->is_enabled()) {
            return;
        }
        
        // Get pending SMS (max 3 attempts)
        $pending_sms = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} 
            WHERE status = 'pending' 
            AND attempts < 3 
            ORDER BY created_at ASC 
            LIMIT 10"
        );
        
        foreach ($pending_sms as $sms) {
            // Send SMS via Twilio
            $result = $this->send_sms_via_twilio($sms->phone_number, $sms->message);
            
            if ($result['success']) {
                // Update as sent
                $wpdb->update(
                    $this->table_name,
                    array(
                        'status' => 'sent',
                        'twilio_sid' => $result['sid'],
                        'sent_at' => current_time('mysql')
                    ),
                    array('id' => $sms->id),
                    array('%s', '%s', '%s'),
                    array('%d')
                );
            } else {
                // Increment attempts and log error
                $wpdb->update(
                    $this->table_name,
                    array(
                        'attempts' => $sms->attempts + 1,
                        'error_message' => $result['error'],
                        'status' => ($sms->attempts + 1 >= 3) ? 'failed' : 'pending'
                    ),
                    array('id' => $sms->id),
                    array('%d', '%s', '%s'),
                    array('%d')
                );
                
                error_log("SMS failed for order #{$sms->order_id}: {$result['error']}");
            }
            
            // Rate limiting - sleep for 1 second between messages
            sleep(1);
        }
    }
    
    /**
     * Send order confirmation SMS
     */
    public function send_order_confirmation_sms($order_id, $order) {
        if (empty($order->customer_phone)) {
            return;
        }
        
        $site_name = get_bloginfo('name');
        $order_number = str_pad($order_id, 6, '0', STR_PAD_LEFT);
        
        $message = "🍗 {$site_name}\n\n";
        $message .= "Order #{$order_number} confirmed!\n\n";
        $message .= "Type: " . ucfirst($order->order_type) . "\n";
        
        if ($order->order_type === 'pickup' && !empty($order->pickup_time)) {
            $pickup_time = date('g:i A', strtotime($order->pickup_time));
            $message .= "Pickup Time: {$pickup_time}\n";
        }
        
        if (!empty($order->estimated_ready_time)) {
            $ready_time = date('g:i A', strtotime($order->estimated_ready_time));
            $message .= "Ready by: {$ready_time}\n";
        }
        
        $message .= "Total: $" . number_format($order->total_amount, 2) . "\n\n";
        $message .= "Track your order: " . home_url('/my-orders');
        
        $this->queue_sms($order_id, $order->customer_phone, $message, 'order_confirmation');
    }
    
    /**
     * Send order status update SMS
     */
    public function send_status_update_sms($order_id, $new_status, $old_status) {
        global $wpdb;
        
        // Get order details
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ucfc_orders WHERE id = %d",
            $order_id
        ));
        
        if (!$order || empty($order->customer_phone)) {
            return;
        }
        
        // Only send SMS for specific status changes
        $sms_statuses = array('confirmed', 'preparing', 'ready');
        if (!in_array($new_status, $sms_statuses)) {
            return;
        }
        
        $site_name = get_bloginfo('name');
        $order_number = str_pad($order_id, 6, '0', STR_PAD_LEFT);
        
        $message = "🍗 {$site_name}\n\n";
        $message .= "Order #{$order_number} update:\n\n";
        
        switch ($new_status) {
            case 'confirmed':
                $message .= "✅ Your order has been confirmed!\n";
                $message .= "We're preparing your delicious meal.";
                break;
                
            case 'preparing':
                $message .= "👨‍🍳 Your order is being prepared!\n";
                if (!empty($order->estimated_ready_time)) {
                    $ready_time = date('g:i A', strtotime($order->estimated_ready_time));
                    $message .= "Ready by: {$ready_time}";
                }
                break;
                
            case 'ready':
                $message .= "✨ Your order is READY!\n\n";
                if ($order->order_type === 'pickup') {
                    $message .= "Come pick it up while it's hot! 🔥";
                } else if ($order->order_type === 'delivery') {
                    $message .= "Our driver is on the way! 🚗";
                } else {
                    $message .= "Your table is ready!";
                }
                break;
        }
        
        $this->queue_sms($order_id, $order->customer_phone, $message, 'status_update');
    }
    
    /**
     * Send pickup reminder SMS (15 minutes before scheduled pickup)
     */
    public function send_pickup_reminders() {
        global $wpdb;
        
        if (!$this->is_enabled()) {
            return;
        }
        
        // Get orders with pickup time in the next 15-20 minutes
        $now = current_time('mysql');
        $reminder_start = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $reminder_end = date('Y-m-d H:i:s', strtotime('+20 minutes'));
        
        $orders = $wpdb->get_results($wpdb->prepare(
            "SELECT o.* FROM {$wpdb->prefix}ucfc_orders o
            LEFT JOIN {$this->table_name} s ON o.id = s.order_id AND s.message_type = 'pickup_reminder'
            WHERE o.order_type = 'pickup'
            AND o.order_status IN ('confirmed', 'preparing', 'ready')
            AND o.pickup_time BETWEEN %s AND %s
            AND s.id IS NULL",
            $reminder_start,
            $reminder_end
        ));
        
        foreach ($orders as $order) {
            if (empty($order->customer_phone)) {
                continue;
            }
            
            $site_name = get_bloginfo('name');
            $order_number = str_pad($order->id, 6, '0', STR_PAD_LEFT);
            $pickup_time = date('g:i A', strtotime($order->pickup_time));
            
            $message = "🍗 {$site_name}\n\n";
            $message .= "⏰ Reminder: Order #{$order_number}\n\n";
            $message .= "Your pickup time is at {$pickup_time} (in 15 minutes)!\n\n";
            
            if ($order->order_status === 'ready') {
                $message .= "✨ Your order is ready and waiting!";
            } else {
                $message .= "We're getting your order ready for pickup.";
            }
            
            $this->queue_sms($order->id, $order->customer_phone, $message, 'pickup_reminder');
        }
    }
    
    /**
     * Add SMS settings page to admin menu
     */
    public function add_sms_settings_page() {
        add_submenu_page(
            'ucfc-dashboard',
            'SMS Notifications Settings',
            'SMS Settings',
            'manage_options',
            'ucfc-sms-settings',
            array($this, 'render_sms_settings_page')
        );
    }
    
    /**
     * Register SMS settings
     */
    public function register_sms_settings() {
        register_setting('ucfc_sms_settings', 'ucfc_twilio_sid');
        register_setting('ucfc_sms_settings', 'ucfc_twilio_token');
        register_setting('ucfc_sms_settings', 'ucfc_twilio_phone');
        register_setting('ucfc_sms_settings', 'ucfc_sms_enabled');
    }
    
    /**
     * Render SMS settings page
     */
    public function render_sms_settings_page() {
        // Handle test SMS
        if (isset($_POST['send_test_sms']) && check_admin_referer('ucfc_test_sms')) {
            $test_phone = sanitize_text_field($_POST['test_phone']);
            $test_message = "🍗 Test SMS from " . get_bloginfo('name') . "\n\nIf you received this, SMS notifications are working! ✅";
            
            $result = $this->send_sms_via_twilio($this->format_phone_number($test_phone), $test_message);
            
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>✅ Test SMS sent successfully! (SID: ' . esc_html($result['sid']) . ')</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ Test SMS failed: ' . esc_html($result['error']) . '</p></div>';
            }
        }
        
        ?>
        <div class="wrap">
            <h1>📱 SMS Notifications Settings</h1>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Twilio Configuration</h2>
                <p>Configure your Twilio credentials to enable SMS notifications for order updates.</p>
                
                <?php if ($this->is_enabled()): ?>
                    <div class="notice notice-success inline">
                        <p>✅ <strong>SMS Notifications are ENABLED</strong></p>
                    </div>
                <?php else: ?>
                    <div class="notice notice-warning inline">
                        <p>⚠️ <strong>SMS Notifications are DISABLED</strong> - Configure Twilio credentials below</p>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="options.php">
                    <?php settings_fields('ucfc_sms_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ucfc_twilio_sid">Twilio Account SID</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="ucfc_twilio_sid" 
                                       name="ucfc_twilio_sid" 
                                       value="<?php echo esc_attr(get_option('ucfc_twilio_sid')); ?>" 
                                       class="regular-text"
                                       placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                                <p class="description">Find this in your Twilio Console dashboard</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ucfc_twilio_token">Twilio Auth Token</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="ucfc_twilio_token" 
                                       name="ucfc_twilio_token" 
                                       value="<?php echo esc_attr(get_option('ucfc_twilio_token')); ?>" 
                                       class="regular-text"
                                       placeholder="********************************">
                                <p class="description">Your Twilio Auth Token (keep this secret!)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ucfc_twilio_phone">Twilio Phone Number</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="ucfc_twilio_phone" 
                                       name="ucfc_twilio_phone" 
                                       value="<?php echo esc_attr(get_option('ucfc_twilio_phone')); ?>" 
                                       class="regular-text"
                                       placeholder="+15555551234">
                                <p class="description">Your Twilio phone number in E.164 format (e.g., +15555551234)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Save Settings'); ?>
                </form>
            </div>
            
            <?php if ($this->is_enabled()): ?>
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>🧪 Test SMS</h2>
                <p>Send a test SMS to verify your Twilio configuration.</p>
                
                <form method="post">
                    <?php wp_nonce_field('ucfc_test_sms'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="test_phone">Phone Number</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="test_phone" 
                                       name="test_phone" 
                                       class="regular-text" 
                                       placeholder="+15555551234"
                                       required>
                                <p class="description">Enter a phone number to receive the test SMS</p>
                            </td>
                        </tr>
                    </table>
                    
                    <button type="submit" name="send_test_sms" class="button button-secondary">
                        📤 Send Test SMS
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>📊 SMS Queue Status</h2>
                <?php
                global $wpdb;
                $stats = $wpdb->get_row("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                    FROM {$this->table_name}
                ");
                ?>
                
                <table class="widefat" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total SMS</td>
                            <td><strong><?php echo number_format($stats->total); ?></strong></td>
                        </tr>
                        <tr>
                            <td>✅ Sent</td>
                            <td><?php echo number_format($stats->sent); ?></td>
                        </tr>
                        <tr>
                            <td>⏳ Pending</td>
                            <td><?php echo number_format($stats->pending); ?></td>
                        </tr>
                        <tr>
                            <td>❌ Failed</td>
                            <td><?php echo number_format($stats->failed); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>ℹ️ Setup Instructions</h2>
                <ol>
                    <li><strong>Create a Twilio Account:</strong> Sign up at <a href="https://www.twilio.com/try-twilio" target="_blank">twilio.com/try-twilio</a></li>
                    <li><strong>Get a Phone Number:</strong> Purchase a phone number from the Twilio Console</li>
                    <li><strong>Find Your Credentials:</strong> Copy your Account SID and Auth Token from the dashboard</li>
                    <li><strong>Configure Above:</strong> Enter your credentials in the form above</li>
                    <li><strong>Test:</strong> Send a test SMS to verify everything works</li>
                    <li><strong>(Optional) Add to wp-config.php:</strong> For better security, define constants:
                        <pre style="background: #f5f5f5; padding: 10px; margin-top: 10px;">define('TWILIO_ACCOUNT_SID', 'ACxxxx...');
define('TWILIO_AUTH_TOKEN', 'your_token');
define('TWILIO_PHONE_NUMBER', '+15555551234');</pre>
                    </li>
                </ol>
                
                <h3>SMS Types Sent:</h3>
                <ul>
                    <li>✅ <strong>Order Confirmation:</strong> Sent immediately when order is placed</li>
                    <li>📱 <strong>Status Updates:</strong> Sent when order status changes (Confirmed, Preparing, Ready)</li>
                    <li>⏰ <strong>Pickup Reminders:</strong> Sent 15 minutes before scheduled pickup time</li>
                </ul>
            </div>
        </div>
        <?php
    }
}

// Initialize SMS notifications
new UCFC_SMS_Notifications();
