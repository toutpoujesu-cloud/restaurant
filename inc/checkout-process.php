<?php
/**
 * Checkout Processing
 * 
 * Handles checkout form submission:
 * - Validates customer data
 * - Creates order in database
 * - Generates order number
 * - Clears cart
 * - Sends confirmation emails
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Process checkout via AJAX
 */
function ucfc_ajax_process_checkout() {
    // Verify nonce
    check_ajax_referer('ucfc_cart_nonce', 'nonce');
    
    global $wpdb;
    
    // Get and validate form data
    $order_type = isset($_POST['order_type']) ? sanitize_text_field($_POST['order_type']) : '';
    $customer_name = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
    $customer_email = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
    $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '';
    $customer_address = isset($_POST['customer_address']) ? sanitize_textarea_field($_POST['customer_address']) : '';
    $special_instructions = isset($_POST['special_instructions']) ? sanitize_textarea_field($_POST['special_instructions']) : '';
    $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : 'card';
    
    // Validate required fields
    if (empty($order_type) || empty($customer_name) || empty($customer_email) || empty($customer_phone)) {
        wp_send_json_error(['message' => 'Please fill in all required fields']);
    }
    
    // Validate email
    if (!is_email($customer_email)) {
        wp_send_json_error(['message' => 'Please enter a valid email address']);
    }
    
    // Validate delivery address if delivery selected
    if ($order_type === 'delivery' && empty($customer_address)) {
        wp_send_json_error(['message' => 'Delivery address is required']);
    }
    
    // Get cart
    $cart = ucfc_get_cart();
    $items = $cart->get_cart();
    $totals = $cart->get_totals();
    
    // Check cart not empty
    if (empty($items)) {
        wp_send_json_error(['message' => 'Your cart is empty']);
    }
    
    // Calculate totals
    $subtotal = floatval($totals['subtotal']);
    $tax = floatval($totals['tax']);
    $delivery_fee = ($order_type === 'delivery') ? 5.00 : 0.00;
    $total = $subtotal + $tax + $delivery_fee;
    
    // Generate order number
    $order_number = 'UC-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Check if order number exists (very unlikely but check anyway)
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}orders WHERE order_number = %s",
        $order_number
    ));
    
    if ($exists) {
        $order_number .= '-' . rand(10, 99);
    }
    
    // Get user ID if logged in
    $user_id = is_user_logged_in() ? get_current_user_id() : null;
    
    // Get payment intent ID if provided (from Stripe payment)
    $payment_intent_id = isset($_POST['payment_intent_id']) ? sanitize_text_field($_POST['payment_intent_id']) : '';
    
    // Determine payment status
    $payment_status = 'pending';
    if (!empty($payment_intent_id)) {
        $payment_status = 'paid'; // Stripe payment confirmed
    } elseif ($payment_method === 'cash') {
        $payment_status = 'pending'; // Will be paid on delivery/pickup
    }
    
    // Append payment intent ID to special instructions for webhook lookup
    $special_instructions_with_payment = $special_instructions;
    if (!empty($payment_intent_id)) {
        $special_instructions_with_payment .= ' [Payment Intent: ' . $payment_intent_id . ']';
    }
    
    // Insert order
    $order_data = [
        'order_number' => $order_number,
        'user_id' => $user_id,
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'customer_address' => $customer_address,
        'order_type' => $order_type,
        'payment_method' => $payment_method,
        'payment_status' => $payment_status,
        'order_status' => 'pending',
        'subtotal' => $subtotal,
        'tax' => $tax,
        'delivery_fee' => $delivery_fee,
        'total' => $total,
        'special_instructions' => $special_instructions_with_payment,
        'estimated_time' => ($order_type === 'delivery') ? 40 : 18
    ];
    
    $inserted = $wpdb->insert(
        $wpdb->prefix . 'orders',
        $order_data,
        ['%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f', '%s', '%d']
    );
    
    if (!$inserted) {
        wp_send_json_error(['message' => 'Failed to create order. Please try again.']);
    }
    
    $order_id = $wpdb->insert_id;
    
    // Insert order items
    foreach ($items as $item) {
        $wpdb->insert(
            $wpdb->prefix . 'order_items',
            [
                'order_id' => $order_id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
                'options' => !empty($item->options) ? json_encode($item->options) : null
            ],
            ['%d', '%d', '%s', '%d', '%f', '%f', '%s']
        );
        
        // Update sold count
        $sold_count = get_post_meta($item->product_id, '_ucfc_sold_count', true);
        update_post_meta($item->product_id, '_ucfc_sold_count', intval($sold_count) + $item->quantity);
        
        // Update stock
        $current_stock = get_post_meta($item->product_id, '_ucfc_stock', true);
        if ($current_stock !== '') {
            $new_stock = max(0, intval($current_stock) - $item->quantity);
            update_post_meta($item->product_id, '_ucfc_stock', $new_stock);
        }
    }
    
    // Log initial status
    $wpdb->insert(
        $wpdb->prefix . 'order_status_history',
        [
            'order_id' => $order_id,
            'old_status' => null,
            'new_status' => 'pending',
            'changed_by' => $user_id,
            'notes' => 'Order created'
        ],
        ['%d', '%s', '%s', '%d', '%s']
    );
    
    // Get full order object for email
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}orders WHERE id = %d",
        $order_id
    ));
    
    // Send enhanced confirmation email
    if (function_exists('ucfc_send_enhanced_order_confirmation')) {
        ucfc_send_enhanced_order_confirmation($order_id, $order);
    } else {
        // Fallback to basic email
        ucfc_send_order_confirmation_email($order_id, $order_number, $customer_email, $customer_name);
    }
    
    // Send enhanced notification to restaurant
    if (function_exists('ucfc_send_enhanced_staff_notification')) {
        ucfc_send_enhanced_staff_notification($order_id, $order);
    } else {
        // Fallback to basic email
        ucfc_send_order_notification_to_staff($order_id, $order_number);
    }
    
    // Trigger SMS notification hook
    do_action('ucfc_order_created', $order_id, $order);
    
    // Clear cart
    $cart->clear_cart();
    
    // Return success
    wp_send_json_success([
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'order_number' => $order_number
    ]);
}
add_action('wp_ajax_ucfc_process_checkout', 'ucfc_ajax_process_checkout');
add_action('wp_ajax_nopriv_ucfc_process_checkout', 'ucfc_ajax_process_checkout');

/**
 * Send order confirmation email to customer
 */
function ucfc_send_order_confirmation_email($order_id, $order_number, $customer_email, $customer_name) {
    global $wpdb;
    
    // Get order details
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}orders WHERE id = %d",
        $order_id
    ));
    
    $items = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}order_items WHERE order_id = %d",
        $order_id
    ));
    
    // Build email
    $subject = 'Order Confirmation #' . $order_number . ' - Uncle Chan\'s Fried Chicken';
    
    $message = '<html><body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
    $message .= '<div style="background: #d92027; color: white; padding: 30px; text-align: center;">';
    $message .= '<h1 style="margin: 0;">Uncle Chan\'s Fried Chicken</h1>';
    $message .= '</div>';
    
    $message .= '<div style="padding: 30px; background: #f8f9fa;">';
    $message .= '<h2>Thank you for your order!</h2>';
    $message .= '<p>Hi ' . esc_html($customer_name) . ',</p>';
    $message .= '<p>We\'ve received your order and we\'re getting it ready! Here are your order details:</p>';
    
    $message .= '<div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">';
    $message .= '<h3>Order #' . $order_number . '</h3>';
    $message .= '<p><strong>Order Type:</strong> ' . ucfirst($order->order_type) . '</p>';
    $message .= '<p><strong>Estimated Time:</strong> ' . $order->estimated_time . ' minutes</p>';
    
    if ($order->order_type === 'delivery') {
        $message .= '<p><strong>Delivery Address:</strong><br>' . nl2br(esc_html($order->customer_address)) . '</p>';
    }
    
    $message .= '<h4 style="border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;">Order Items</h4>';
    foreach ($items as $item) {
        $message .= '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">';
        $message .= '<div>' . esc_html($item->product_name) . ' x' . $item->quantity . '</div>';
        $message .= '<div><strong>$' . number_format($item->subtotal, 2) . '</strong></div>';
        $message .= '</div>';
    }
    
    $message .= '<div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #333;">';
    $message .= '<div style="display: flex; justify-content: space-between; padding: 5px 0;"><span>Subtotal:</span><span>$' . number_format($order->subtotal, 2) . '</span></div>';
    $message .= '<div style="display: flex; justify-content: space-between; padding: 5px 0;"><span>Tax:</span><span>$' . number_format($order->tax, 2) . '</span></div>';
    if ($order->delivery_fee > 0) {
        $message .= '<div style="display: flex; justify-content: space-between; padding: 5px 0;"><span>Delivery Fee:</span><span>$' . number_format($order->delivery_fee, 2) . '</span></div>';
    }
    $message .= '<div style="display: flex; justify-content: space-between; padding: 15px 0; font-size: 1.2em; font-weight: bold;"><span>Total:</span><span style="color: #d92027;">$' . number_format($order->total, 2) . '</span></div>';
    $message .= '</div>';
    $message .= '</div>';
    
    $message .= '<p style="color: #666; font-size: 14px;">If you have any questions, please contact us at ' . get_option('admin_email') . '</p>';
    $message .= '</div>';
    
    $message .= '<div style="background: #333; color: white; padding: 20px; text-align: center; font-size: 14px;">';
    $message .= '<p>© ' . date('Y') . ' Uncle Chan\'s Fried Chicken. All rights reserved.</p>';
    $message .= '</div>';
    $message .= '</body></html>';
    
    // Send email
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($customer_email, $subject, $message, $headers);
}

/**
 * Send order notification to restaurant staff
 */
function ucfc_send_order_notification_to_staff($order_id, $order_number) {
    global $wpdb;
    
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}orders WHERE id = %d",
        $order_id
    ));
    
    $items = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}order_items WHERE order_id = %d",
        $order_id
    ));
    
    $subject = '🔔 New Order #' . $order_number;
    
    $message = "New order received!\n\n";
    $message .= "Order #: " . $order_number . "\n";
    $message .= "Customer: " . $order->customer_name . "\n";
    $message .= "Phone: " . $order->customer_phone . "\n";
    $message .= "Email: " . $order->customer_email . "\n";
    $message .= "Order Type: " . ucfirst($order->order_type) . "\n\n";
    
    if ($order->order_type === 'delivery') {
        $message .= "Delivery Address:\n" . $order->customer_address . "\n\n";
    }
    
    $message .= "Items:\n";
    foreach ($items as $item) {
        $message .= "- " . $item->product_name . " x" . $item->quantity . " = $" . number_format($item->subtotal, 2) . "\n";
    }
    
    $message .= "\nTotal: $" . number_format($order->total, 2) . "\n\n";
    
    if ($order->special_instructions) {
        $message .= "Special Instructions:\n" . $order->special_instructions . "\n\n";
    }
    
    $message .= "View order in admin: " . admin_url('admin.php?page=ucfc-orders&action=view&order_id=' . $order_id);
    
    wp_mail(get_option('admin_email'), $subject, $message);
}
?>
