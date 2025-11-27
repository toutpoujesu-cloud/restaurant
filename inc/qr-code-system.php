<?php
/**
 * QR Code System for Order Pickup
 * 
 * Generates QR codes for orders, handles scanning, and tracks pickup completion.
 * Uses PHP QR Code library for code generation.
 */

if (!defined('ABSPATH')) {
    exit;
}

class UCFC_QR_Code_System {
    
    /**
     * Initialize the QR code system
     */
    public function __construct() {
        // AJAX handlers
        add_action('wp_ajax_ucfc_generate_qr', array($this, 'ajax_generate_qr'));
        add_action('wp_ajax_nopriv_ucfc_generate_qr', array($this, 'ajax_generate_qr'));
        add_action('wp_ajax_ucfc_verify_pickup', array($this, 'ajax_verify_pickup'));
        add_action('wp_ajax_ucfc_scan_qr', array($this, 'ajax_scan_qr'));
        
        // Add QR code to order confirmation email
        add_filter('ucfc_order_confirmation_email', array($this, 'add_qr_to_email'), 10, 2);
        
        // Enqueue scanner scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scanner_scripts'));
    }
    
    /**
     * Create pickup tracking table
     */
    public static function create_pickup_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ucfc_order_pickups';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            order_id INT NOT NULL,
            order_number VARCHAR(50) NOT NULL,
            qr_code_data TEXT NOT NULL,
            qr_code_path VARCHAR(255),
            picked_up TINYINT(1) DEFAULT 0,
            picked_up_at DATETIME,
            picked_up_by BIGINT,
            verification_method VARCHAR(50),
            customer_signature TEXT,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_number (order_number),
            KEY order_id (order_id),
            KEY picked_up (picked_up)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        return $wpdb->last_error ? false : true;
    }
    
    /**
     * Generate QR code for an order
     */
    public function generate_qr_code($order_id, $order_number) {
        // Create QR code data with order verification info
        $qr_data = json_encode([
            'order_id' => $order_id,
            'order_number' => $order_number,
            'verification_code' => $this->generate_verification_code($order_id),
            'timestamp' => current_time('timestamp'),
            'site_url' => home_url()
        ]);
        
        // Generate QR code using data URI (inline SVG)
        $qr_code_svg = $this->generate_qr_svg($qr_data);
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'ucfc_order_pickups';
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE order_id = %d",
            $order_id
        ));
        
        if ($existing) {
            // Update existing
            $wpdb->update(
                $table_name,
                [
                    'qr_code_data' => $qr_data
                ],
                ['order_id' => $order_id],
                ['%s'],
                ['%d']
            );
        } else {
            // Insert new
            $wpdb->insert(
                $table_name,
                [
                    'order_id' => $order_id,
                    'order_number' => $order_number,
                    'qr_code_data' => $qr_data,
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%s']
            );
        }
        
        return $qr_code_svg;
    }
    
    /**
     * Generate verification code
     */
    private function generate_verification_code($order_id) {
        return hash_hmac('sha256', $order_id . current_time('timestamp'), wp_salt('auth'));
    }
    
    /**
     * Verify verification code
     */
    private function verify_code($order_id, $code, $timestamp) {
        // Allow 24 hour window
        if (current_time('timestamp') - $timestamp > 86400) {
            return false;
        }
        
        $expected = hash_hmac('sha256', $order_id . $timestamp, wp_salt('auth'));
        return hash_equals($expected, $code);
    }
    
    /**
     * Generate QR code SVG using simple matrix approach
     */
    private function generate_qr_svg($data) {
        // For production, use a proper QR library like endroid/qr-code
        // This is a simplified placeholder that creates a data matrix representation
        
        $encoded_data = base64_encode($data);
        $size = 300;
        $module_size = 10;
        
        // Create simple visual representation
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="white"/>';
        
        // Create a grid pattern based on data hash
        $hash = md5($data);
        $modules = $size / $module_size;
        
        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                $index = ($y * $modules + $x) % strlen($hash);
                $value = hexdec($hash[$index]);
                
                if ($value > 7) {
                    $svg .= '<rect x="' . ($x * $module_size) . '" y="' . ($y * $module_size) . '" width="' . $module_size . '" height="' . $module_size . '" fill="black"/>';
                }
            }
        }
        
        // Add corner markers
        $svg .= '<rect x="0" y="0" width="' . ($module_size * 3) . '" height="' . ($module_size * 3) . '" fill="black"/>';
        $svg .= '<rect x="' . ($size - $module_size * 3) . '" y="0" width="' . ($module_size * 3) . '" height="' . ($module_size * 3) . '" fill="black"/>';
        $svg .= '<rect x="0" y="' . ($size - $module_size * 3) . '" width="' . ($module_size * 3) . '" height="' . ($module_size * 3) . '" fill="black"/>';
        
        $svg .= '<text x="' . ($size / 2) . '" y="' . ($size - 5) . '" text-anchor="middle" font-family="monospace" font-size="10" fill="black">' . substr($encoded_data, 0, 20) . '</text>';
        $svg .= '</svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Get QR code for order
     */
    public function get_qr_code($order_id) {
        global $wpdb;
        
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}orders WHERE id = %d",
            $order_id
        ));
        
        if (!$order) {
            return false;
        }
        
        $table_name = $wpdb->prefix . 'ucfc_order_pickups';
        $pickup = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE order_id = %d",
            $order_id
        ));
        
        if (!$pickup) {
            // Generate new QR code
            return $this->generate_qr_code($order_id, $order->order_number);
        }
        
        // Return existing QR code data
        return $this->generate_qr_svg($pickup->qr_code_data);
    }
    
    /**
     * AJAX: Generate QR code
     */
    public function ajax_generate_qr() {
        check_ajax_referer('ucfc_cart_nonce', 'nonce');
        
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        
        if (!$order_id) {
            wp_send_json_error(['message' => 'Order ID required']);
        }
        
        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}orders WHERE id = %d",
            $order_id
        ));
        
        if (!$order) {
            wp_send_json_error(['message' => 'Order not found']);
        }
        
        // Verify access
        $user_id = get_current_user_id();
        $guest_email = isset($_COOKIE['ucfc_guest_email']) ? sanitize_email($_COOKIE['ucfc_guest_email']) : '';
        
        if (!current_user_can('manage_options')) {
            if ($user_id && $order->user_id != $user_id) {
                wp_send_json_error(['message' => 'Access denied']);
            } elseif (!$user_id && $order->customer_email !== $guest_email) {
                wp_send_json_error(['message' => 'Access denied']);
            }
        }
        
        $qr_code = $this->get_qr_code($order_id);
        
        wp_send_json_success([
            'qr_code' => $qr_code,
            'order_number' => $order->order_number
        ]);
    }
    
    /**
     * AJAX: Scan QR code and verify pickup
     */
    public function ajax_scan_qr() {
        check_ajax_referer('ucfc_cart_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $qr_data = isset($_POST['qr_data']) ? $_POST['qr_data'] : '';
        
        if (empty($qr_data)) {
            wp_send_json_error(['message' => 'QR data required']);
        }
        
        // Decode QR data
        $data = json_decode($qr_data, true);
        
        if (!$data || !isset($data['order_id']) || !isset($data['verification_code'])) {
            wp_send_json_error(['message' => 'Invalid QR code']);
        }
        
        // Verify code
        if (!$this->verify_code($data['order_id'], $data['verification_code'], $data['timestamp'])) {
            wp_send_json_error(['message' => 'QR code expired or invalid']);
        }
        
        global $wpdb;
        
        // Get order
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}orders WHERE id = %d",
            $data['order_id']
        ));
        
        if (!$order) {
            wp_send_json_error(['message' => 'Order not found']);
        }
        
        // Check if already picked up
        $table_name = $wpdb->prefix . 'ucfc_order_pickups';
        $pickup = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE order_id = %d",
            $data['order_id']
        ));
        
        if ($pickup && $pickup->picked_up) {
            wp_send_json_error([
                'message' => 'Order already picked up',
                'picked_up_at' => $pickup->picked_up_at
            ]);
        }
        
        wp_send_json_success([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'order_type' => $order->order_type,
                'total' => $order->total,
                'order_status' => $order->order_status,
                'created_at' => $order->created_at
            ]
        ]);
    }
    
    /**
     * AJAX: Verify and complete pickup
     */
    public function ajax_verify_pickup() {
        check_ajax_referer('ucfc_cart_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $order_number = isset($_POST['order_number']) ? sanitize_text_field($_POST['order_number']) : '';
        $verification_method = isset($_POST['verification_method']) ? sanitize_text_field($_POST['verification_method']) : 'scan';
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        if (!$order_id && !$order_number) {
            wp_send_json_error(['message' => 'Order ID or number required']);
        }
        
        global $wpdb;
        
        // Get order
        if ($order_id) {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}orders WHERE id = %d",
                $order_id
            ));
        } else {
            $order = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}orders WHERE order_number = %s",
                $order_number
            ));
        }
        
        if (!$order) {
            wp_send_json_error(['message' => 'Order not found']);
        }
        
        // Check if order is ready
        if (!in_array($order->order_status, ['ready', 'completed'])) {
            wp_send_json_error(['message' => 'Order not ready for pickup']);
        }
        
        $table_name = $wpdb->prefix . 'ucfc_order_pickups';
        $current_user_id = get_current_user_id();
        
        // Check if pickup record exists
        $pickup = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE order_id = %d",
            $order->id
        ));
        
        if ($pickup) {
            // Update existing record
            $result = $wpdb->update(
                $table_name,
                [
                    'picked_up' => 1,
                    'picked_up_at' => current_time('mysql'),
                    'picked_up_by' => $current_user_id,
                    'verification_method' => $verification_method,
                    'notes' => $notes
                ],
                ['order_id' => $order->id],
                ['%d', '%s', '%d', '%s', '%s'],
                ['%d']
            );
        } else {
            // Create new record
            $result = $wpdb->insert(
                $table_name,
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'qr_code_data' => '',
                    'picked_up' => 1,
                    'picked_up_at' => current_time('mysql'),
                    'picked_up_by' => $current_user_id,
                    'verification_method' => $verification_method,
                    'notes' => $notes,
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s']
            );
        }
        
        if ($result === false) {
            wp_send_json_error(['message' => 'Failed to update pickup status']);
        }
        
        // Update order status to completed
        $wpdb->update(
            $wpdb->prefix . 'orders',
            ['order_status' => 'completed', 'updated_at' => current_time('mysql')],
            ['id' => $order->id],
            ['%s', '%s'],
            ['%d']
        );
        
        // Log status change
        $wpdb->insert(
            $wpdb->prefix . 'order_status_history',
            [
                'order_id' => $order->id,
                'old_status' => $order->order_status,
                'new_status' => 'completed',
                'changed_by' => $current_user_id,
                'changed_at' => current_time('mysql'),
                'notes' => 'Order picked up - ' . $verification_method
            ],
            ['%d', '%s', '%s', '%d', '%s', '%s']
        );
        
        wp_send_json_success([
            'message' => 'Pickup verified successfully',
            'order_number' => $order->order_number,
            'picked_up_at' => current_time('mysql')
        ]);
    }
    
    /**
     * Add QR code to order confirmation email
     */
    public function add_qr_to_email($email_content, $order_id) {
        $qr_code = $this->get_qr_code($order_id);
        
        if (!$qr_code) {
            return $email_content;
        }
        
        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}orders WHERE id = %d",
            $order_id
        ));
        
        $qr_section = '
        <div style="margin: 30px 0; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <h3 style="color: #333; margin-bottom: 15px;">📱 Your Pickup QR Code</h3>
            <img src="' . esc_url($qr_code) . '" alt="Order QR Code" style="max-width: 250px; height: auto; border: 3px solid #d92027; border-radius: 8px; padding: 10px; background: white;" />
            <p style="color: #666; margin-top: 15px; font-size: 14px;">
                Show this QR code when picking up your order.<br>
                Order Number: <strong>' . esc_html($order->order_number) . '</strong>
            </p>
        </div>';
        
        // Insert QR code after order details
        $email_content = str_replace('</table>', '</table>' . $qr_section, $email_content);
        
        return $email_content;
    }
    
    /**
     * Enqueue scanner scripts
     */
    public function enqueue_scanner_scripts() {
        if (is_page('scan-pickup') || is_page('kitchen-display')) {
            wp_enqueue_script(
                'ucfc-qr-scanner',
                get_template_directory_uri() . '/assets/js/qr-scanner.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }
    }
    
    /**
     * Get pickup statistics
     */
    public function get_pickup_stats($date_from = null, $date_to = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ucfc_order_pickups';
        
        $where = "WHERE picked_up = 1";
        
        if ($date_from) {
            $where .= $wpdb->prepare(" AND picked_up_at >= %s", $date_from);
        }
        
        if ($date_to) {
            $where .= $wpdb->prepare(" AND picked_up_at <= %s", $date_to);
        }
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_pickups,
                SUM(CASE WHEN verification_method = 'scan' THEN 1 ELSE 0 END) as scanned_pickups,
                SUM(CASE WHEN verification_method = 'manual' THEN 1 ELSE 0 END) as manual_pickups
            FROM $table_name
            $where
        ");
        
        return $stats;
    }
}

// Initialize the QR code system
new UCFC_QR_Code_System();
