<?php
/**
 * Order Tracking AJAX Handlers
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Reorder - Add all items from previous order to cart
 */
function ucfc_ajax_reorder() {
    check_ajax_referer('ucfc_cart_nonce', 'nonce');
    
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    
    if (!$order_id) {
        wp_send_json_error(['message' => 'Invalid order ID']);
    }
    
    global $wpdb;
    
    // Get order items
    $items = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}order_items WHERE order_id = %d",
        $order_id
    ));
    
    if (empty($items)) {
        wp_send_json_error(['message' => 'Order not found']);
    }
    
    // Get cart instance
    $cart = ucfc_get_cart();
    
    // Add each item to cart
    $added_count = 0;
    foreach ($items as $item) {
        // Check if product still exists
        $product = get_post($item->product_id);
        if (!$product || $product->post_status !== 'publish') {
            continue;
        }
        
        // Check stock
        $stock = get_post_meta($item->product_id, '_ucfc_stock', true);
        if ($stock !== '' && intval($stock) < $item->quantity) {
            continue; // Skip if not enough stock
        }
        
        // Add to cart
        $options = !empty($item->options) ? json_decode($item->options, true) : [];
        $cart->add_item($item->product_id, $item->quantity, $options);
        $added_count++;
    }
    
    if ($added_count === 0) {
        wp_send_json_error(['message' => 'None of the items are currently available']);
    }
    
    // Get updated cart totals
    $totals = $cart->get_totals();
    
    wp_send_json_success([
        'message' => "$added_count items added to cart",
        'cart_count' => $totals['item_count'],
        'cart_total' => $totals['total']
    ]);
}
add_action('wp_ajax_ucfc_reorder', 'ucfc_ajax_reorder');
add_action('wp_ajax_nopriv_ucfc_reorder', 'ucfc_ajax_reorder');

/**
 * Get order status updates (for real-time tracking)
 */
function ucfc_ajax_get_order_status() {
    check_ajax_referer('ucfc_cart_nonce', 'nonce');
    
    $order_number = isset($_POST['order_number']) ? sanitize_text_field($_POST['order_number']) : '';
    
    if (empty($order_number)) {
        wp_send_json_error(['message' => 'Order number required']);
    }
    
    global $wpdb;
    
    // Get order
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}orders WHERE order_number = %s",
        $order_number
    ));
    
    if (!$order) {
        wp_send_json_error(['message' => 'Order not found']);
    }
    
    // Calculate time remaining
    $order_time = strtotime($order->created_at);
    $estimated_ready_time = $order_time + ($order->estimated_time * 60);
    $current_time = current_time('timestamp');
    $time_remaining = max(0, $estimated_ready_time - $current_time);
    $minutes_remaining = ceil($time_remaining / 60);
    
    wp_send_json_success([
        'order_status' => $order->order_status,
        'payment_status' => $order->payment_status,
        'minutes_remaining' => $minutes_remaining,
        'estimated_ready_time' => date('g:i A', $estimated_ready_time)
    ]);
}
add_action('wp_ajax_ucfc_get_order_status', 'ucfc_ajax_get_order_status');
add_action('wp_ajax_nopriv_ucfc_get_order_status', 'ucfc_ajax_get_order_status');
