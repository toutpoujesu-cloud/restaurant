<?php
/**
 * Payment Gateway Integration (Stripe)
 * 
 * Handles payment processing, Stripe API integration, and webhooks
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stripe Payment Gateway Class
 */
class UCFC_Payment_Gateway {
    
    /**
     * Stripe API Key (Secret)
     * Set in wp-config.php: define('STRIPE_SECRET_KEY', 'sk_test_...');
     */
    private $secret_key;
    
    /**
     * Stripe API Key (Publishable)
     * Set in wp-config.php: define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');
     */
    private $publishable_key;
    
    /**
     * Stripe Webhook Secret
     * Set in wp-config.php: define('STRIPE_WEBHOOK_SECRET', 'whsec_...');
     */
    private $webhook_secret;
    
    /**
     * API Version
     */
    private $api_version = '2023-10-16';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Get API keys from wp-config.php or settings
        $this->secret_key = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';
        $this->publishable_key = defined('STRIPE_PUBLISHABLE_KEY') ? STRIPE_PUBLISHABLE_KEY : '';
        $this->webhook_secret = defined('STRIPE_WEBHOOK_SECRET') ? STRIPE_WEBHOOK_SECRET : '';
        
        // Register AJAX handlers
        add_action('wp_ajax_ucfc_create_payment_intent', array($this, 'ajax_create_payment_intent'));
        add_action('wp_ajax_nopriv_ucfc_create_payment_intent', array($this, 'ajax_create_payment_intent'));
        
        add_action('wp_ajax_ucfc_confirm_payment', array($this, 'ajax_confirm_payment'));
        add_action('wp_ajax_nopriv_ucfc_confirm_payment', array($this, 'ajax_confirm_payment'));
        
        // Register webhook endpoint
        add_action('wp_ajax_ucfc_stripe_webhook', array($this, 'handle_webhook'));
        add_action('wp_ajax_nopriv_ucfc_stripe_webhook', array($this, 'handle_webhook'));
        
        // Enqueue Stripe.js on checkout page
        add_action('wp_enqueue_scripts', array($this, 'enqueue_stripe_scripts'));
    }
    
    /**
     * Check if Stripe is configured
     */
    public function is_configured() {
        return !empty($this->secret_key) && !empty($this->publishable_key);
    }
    
    /**
     * Get publishable key (safe to expose to frontend)
     */
    public function get_publishable_key() {
        return $this->publishable_key;
    }
    
    /**
     * Enqueue Stripe.js and custom payment scripts
     */
    public function enqueue_stripe_scripts() {
        if (is_page('checkout')) {
            // Stripe.js library
            wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', array(), null, true);
            
            // Custom payment handler
            wp_enqueue_script(
                'ucfc-stripe-handler',
                get_template_directory_uri() . '/assets/js/stripe-handler.js',
                array('jquery', 'stripe-js'),
                '1.0.0',
                true
            );
            
            // Localize script with Stripe publishable key and AJAX URL
            wp_localize_script('ucfc-stripe-handler', 'ucfcStripe', array(
                'publishableKey' => $this->publishable_key,
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ucfc_stripe_nonce'),
            ));
        }
    }
    
    /**
     * Create Payment Intent (Step 1 of Stripe payment flow)
     * Called via AJAX before user enters card details
     */
    public function ajax_create_payment_intent() {
        check_ajax_referer('ucfc_stripe_nonce', 'nonce');
        
        if (!$this->is_configured()) {
            wp_send_json_error(array('message' => 'Payment system not configured.'));
            return;
        }
        
        // Get cart totals
        $cart = ucfc_get_cart();
        $totals = $cart->get_totals();
        
        // Get order type to calculate delivery fee
        $order_type = isset($_POST['order_type']) ? sanitize_text_field($_POST['order_type']) : 'pickup';
        $delivery_fee = $order_type === 'delivery' ? 5.00 : 0.00;
        
        // Calculate final amount
        $amount = $totals['total'] + $delivery_fee;
        
        // Stripe requires amount in cents
        $amount_cents = intval($amount * 100);
        
        if ($amount_cents < 50) { // Stripe minimum is $0.50
            wp_send_json_error(array('message' => 'Order amount is too low for payment processing.'));
            return;
        }
        
        // Create Payment Intent via Stripe API
        $payment_intent = $this->create_payment_intent($amount_cents);
        
        if (is_wp_error($payment_intent)) {
            wp_send_json_error(array('message' => $payment_intent->get_error_message()));
            return;
        }
        
        wp_send_json_success(array(
            'clientSecret' => $payment_intent['client_secret'],
            'amount' => $amount,
        ));
    }
    
    /**
     * Create Payment Intent via Stripe API
     * 
     * @param int $amount_cents Amount in cents
     * @return array|WP_Error Payment intent data or error
     */
    private function create_payment_intent($amount_cents) {
        $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Stripe-Version' => $this->api_version,
            ),
            'body' => array(
                'amount' => $amount_cents,
                'currency' => 'usd',
                'automatic_payment_methods[enabled]' => 'true',
                'description' => 'Uncle Chan\'s Fried Chicken Order',
                'metadata[source]' => 'uncle_chans_website',
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code !== 200) {
            $message = isset($body['error']['message']) ? $body['error']['message'] : 'Payment system error';
            return new WP_Error('stripe_error', $message);
        }
        
        return $body;
    }
    
    /**
     * Confirm Payment (called after successful payment on frontend)
     */
    public function ajax_confirm_payment() {
        check_ajax_referer('ucfc_stripe_nonce', 'nonce');
        
        $payment_intent_id = isset($_POST['payment_intent_id']) ? sanitize_text_field($_POST['payment_intent_id']) : '';
        
        if (empty($payment_intent_id)) {
            wp_send_json_error(array('message' => 'Missing payment intent ID.'));
            return;
        }
        
        // Retrieve Payment Intent from Stripe to verify status
        $payment_intent = $this->retrieve_payment_intent($payment_intent_id);
        
        if (is_wp_error($payment_intent)) {
            wp_send_json_error(array('message' => $payment_intent->get_error_message()));
            return;
        }
        
        // Check payment status
        if ($payment_intent['status'] !== 'succeeded') {
            wp_send_json_error(array('message' => 'Payment not completed. Status: ' . $payment_intent['status']));
            return;
        }
        
        wp_send_json_success(array(
            'status' => 'succeeded',
            'amount' => $payment_intent['amount'] / 100, // Convert cents to dollars
            'payment_intent_id' => $payment_intent_id,
        ));
    }
    
    /**
     * Retrieve Payment Intent from Stripe
     * 
     * @param string $payment_intent_id Payment Intent ID
     * @return array|WP_Error Payment intent data or error
     */
    private function retrieve_payment_intent($payment_intent_id) {
        $response = wp_remote_get('https://api.stripe.com/v1/payment_intents/' . $payment_intent_id, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Stripe-Version' => $this->api_version,
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code !== 200) {
            $message = isset($body['error']['message']) ? $body['error']['message'] : 'Payment verification failed';
            return new WP_Error('stripe_error', $message);
        }
        
        return $body;
    }
    
    /**
     * Handle Stripe Webhook
     * Called by Stripe when payment events occur
     */
    public function handle_webhook() {
        $payload = @file_get_contents('php://input');
        $sig_header = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';
        
        if (empty($this->webhook_secret)) {
            http_response_code(400);
            exit('Webhook secret not configured');
        }
        
        // Verify webhook signature
        $event = $this->verify_webhook_signature($payload, $sig_header);
        
        if (is_wp_error($event)) {
            http_response_code(400);
            exit($event->get_error_message());
        }
        
        // Handle different event types
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->handle_payment_succeeded($event['data']['object']);
                break;
                
            case 'payment_intent.payment_failed':
                $this->handle_payment_failed($event['data']['object']);
                break;
                
            case 'charge.refunded':
                $this->handle_charge_refunded($event['data']['object']);
                break;
        }
        
        http_response_code(200);
        exit('Webhook received');
    }
    
    /**
     * Verify Webhook Signature
     * 
     * @param string $payload Request body
     * @param string $sig_header Stripe signature header
     * @return array|WP_Error Event data or error
     */
    private function verify_webhook_signature($payload, $sig_header) {
        // Parse signature header
        $sig_parts = array();
        foreach (explode(',', $sig_header) as $part) {
            $kv = explode('=', $part, 2);
            if (count($kv) === 2) {
                $sig_parts[$kv[0]] = $kv[1];
            }
        }
        
        if (!isset($sig_parts['t']) || !isset($sig_parts['v1'])) {
            return new WP_Error('invalid_signature', 'Invalid signature format');
        }
        
        // Compute expected signature
        $signed_payload = $sig_parts['t'] . '.' . $payload;
        $expected_sig = hash_hmac('sha256', $signed_payload, $this->webhook_secret);
        
        // Compare signatures
        if (!hash_equals($expected_sig, $sig_parts['v1'])) {
            return new WP_Error('invalid_signature', 'Signature verification failed');
        }
        
        // Check timestamp to prevent replay attacks (within 5 minutes)
        $timestamp = intval($sig_parts['t']);
        if (abs(time() - $timestamp) > 300) {
            return new WP_Error('expired_signature', 'Signature expired');
        }
        
        return json_decode($payload, true);
    }
    
    /**
     * Handle successful payment
     * 
     * @param array $payment_intent Payment Intent object
     */
    private function handle_payment_succeeded($payment_intent) {
        global $wpdb;
        
        $payment_intent_id = $payment_intent['id'];
        
        // Find order by payment intent ID (stored in order meta or notes)
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}orders WHERE special_instructions LIKE %s",
            '%' . $wpdb->esc_like($payment_intent_id) . '%'
        ));
        
        if ($order && $order->payment_status !== 'paid') {
            // Update order payment status
            $wpdb->update(
                $wpdb->prefix . 'orders',
                array('payment_status' => 'paid'),
                array('id' => $order->id),
                array('%s'),
                array('%d')
            );
            
            // Log status change
            $wpdb->insert(
                $wpdb->prefix . 'order_status_history',
                array(
                    'order_id' => $order->id,
                    'old_status' => $order->payment_status,
                    'new_status' => 'paid',
                    'changed_by' => 0,
                    'notes' => 'Payment confirmed via Stripe webhook',
                    'created_at' => current_time('mysql'),
                )
            );
            
            // Send notification email to staff
            $admin_email = get_option('admin_email');
            wp_mail(
                $admin_email,
                'Payment Confirmed - Order #' . $order->order_number,
                "Payment has been confirmed for order #{$order->order_number}\n\nAmount: $" . number_format($order->total, 2) . "\nPayment Intent: {$payment_intent_id}",
                array('Content-Type: text/plain; charset=UTF-8')
            );
        }
    }
    
    /**
     * Handle failed payment
     * 
     * @param array $payment_intent Payment Intent object
     */
    private function handle_payment_failed($payment_intent) {
        // Log payment failure
        error_log('Payment failed: ' . $payment_intent['id'] . ' - ' . 
                  ($payment_intent['last_payment_error']['message'] ?? 'Unknown error'));
    }
    
    /**
     * Handle refunded charge
     * 
     * @param array $charge Charge object
     */
    private function handle_charge_refunded($charge) {
        global $wpdb;
        
        $payment_intent_id = $charge['payment_intent'];
        
        // Find order
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}orders WHERE special_instructions LIKE %s",
            '%' . $wpdb->esc_like($payment_intent_id) . '%'
        ));
        
        if ($order) {
            // Update order status to cancelled
            $wpdb->update(
                $wpdb->prefix . 'orders',
                array(
                    'order_status' => 'cancelled',
                    'payment_status' => 'refunded',
                ),
                array('id' => $order->id)
            );
            
            // Log refund
            $wpdb->insert(
                $wpdb->prefix . 'order_status_history',
                array(
                    'order_id' => $order->id,
                    'old_status' => $order->order_status,
                    'new_status' => 'cancelled',
                    'changed_by' => 0,
                    'notes' => 'Order refunded via Stripe - Amount: $' . number_format($charge['amount_refunded'] / 100, 2),
                    'created_at' => current_time('mysql'),
                )
            );
        }
    }
}

/**
 * Initialize Payment Gateway
 */
function ucfc_init_payment_gateway() {
    return new UCFC_Payment_Gateway();
}
add_action('init', 'ucfc_init_payment_gateway');

/**
 * Get Payment Gateway instance
 */
function ucfc_payment_gateway() {
    static $gateway = null;
    if ($gateway === null) {
        $gateway = new UCFC_Payment_Gateway();
    }
    return $gateway;
}
