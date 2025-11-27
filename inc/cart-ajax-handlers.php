<?php
/**
 * Cart AJAX Handlers
 * 
 * Handles all AJAX requests for cart operations:
 * - Add to cart
 * - Remove from cart
 * - Update quantity
 * - Get cart data
 * - Clear cart
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add item to cart via AJAX
 */
function ucfc_ajax_add_to_cart() {
    // Verify nonce
    check_ajax_referer('ucfc_cart_nonce', 'nonce');
    
    // Get parameters
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
    $options = isset($_POST['options']) ? $_POST['options'] : [];
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Product ID is required']);
    }
    
    // Get cart instance
    $cart = ucfc_get_cart();
    
    // Add item
    $result = $cart->add_item($product_id, $quantity, $options);
    
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }
    
    // Get updated cart data
    $totals = $cart->get_totals();
    $items = $cart->get_cart();
    
    wp_send_json_success([
        'message' => 'Item added to cart',
        'cart_count' => $totals['item_count'],
        'cart_subtotal' => '$' . $totals['subtotal'],
        'cart_total' => '$' . $totals['total'],
        'cart_items' => $items
    ]);
}
add_action('wp_ajax_ucfc_add_to_cart', 'ucfc_ajax_add_to_cart');
add_action('wp_ajax_nopriv_ucfc_add_to_cart', 'ucfc_ajax_add_to_cart');

/**
 * Remove item from cart via AJAX
 */
function ucfc_ajax_remove_from_cart() {
    // Verify nonce
    check_ajax_referer('ucfc_cart_nonce', 'nonce');
    
    // Get cart item ID
    $cart_item_id = isset($_POST['cart_item_id']) ? absint($_POST['cart_item_id']) : 0;
    
    if (!$cart_item_id) {
        wp_send_json_error(['message' => 'Cart item ID is required']);
    }
    
    // Get cart instance
    $cart = ucfc_get_cart();
    
    // Remove item
    $result = $cart->remove_item($cart_item_id);
    
    if (!$result) {
        wp_send_json_error(['message' => 'Failed to remove item']);
    }
    
    // Get updated cart data
    $totals = $cart->get_totals();
    $items = $cart->get_cart();
    
    wp_send_json_success([
        'message' => 'Item removed from cart',
        'cart_count' => $totals['item_count'],
        'cart_subtotal' => '$' . $totals['subtotal'],
        'cart_total' => '$' . $totals['total'],
        'cart_items' => $items
    ]);
}
add_action('wp_ajax_ucfc_remove_from_cart', 'ucfc_ajax_remove_from_cart');
add_action('wp_ajax_nopriv_ucfc_remove_from_cart', 'ucfc_ajax_remove_from_cart');

/**
 * Update cart item quantity via AJAX
 */
function ucfc_ajax_update_cart_quantity() {
    // Verify nonce
    check_ajax_referer('ucfc_cart_nonce', 'nonce');
    
    // Get parameters
    $cart_item_id = isset($_POST['cart_item_id']) ? absint($_POST['cart_item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 0;
    
    if (!$cart_item_id) {
        wp_send_json_error(['message' => 'Cart item ID is required']);
    }
    
    // Get cart instance
    $cart = ucfc_get_cart();
    
    // Update quantity
    $result = $cart->update_quantity($cart_item_id, $quantity);
    
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }
    
    // Get updated cart data
    $totals = $cart->get_totals();
    $items = $cart->get_cart();
    
    wp_send_json_success([
        'message' => 'Cart updated',
        'cart_count' => $totals['item_count'],
        'cart_subtotal' => '$' . $totals['subtotal'],
        'cart_total' => '$' . $totals['total'],
        'cart_items' => $items
    ]);
}
add_action('wp_ajax_ucfc_update_cart_quantity', 'ucfc_ajax_update_cart_quantity');
add_action('wp_ajax_nopriv_ucfc_update_cart_quantity', 'ucfc_ajax_update_cart_quantity');

/**
 * Get cart data via AJAX
 */
function ucfc_ajax_get_cart() {
    // Verify nonce
    check_ajax_referer('ucfc_cart_nonce', 'nonce');
    
    // Get cart instance
    $cart = ucfc_get_cart();
    
    // Get cart data
    $totals = $cart->get_totals();
    $items = $cart->get_cart();
    
    wp_send_json_success([
        'cart_count' => $totals['item_count'],
        'cart_subtotal' => '$' . $totals['subtotal'],
        'cart_tax' => '$' . $totals['tax'],
        'cart_total' => '$' . $totals['total'],
        'cart_items' => $items
    ]);
}
add_action('wp_ajax_ucfc_get_cart', 'ucfc_ajax_get_cart');
add_action('wp_ajax_nopriv_ucfc_get_cart', 'ucfc_ajax_get_cart');

/**
 * Clear cart via AJAX
 */
function ucfc_ajax_clear_cart() {
    // Verify nonce
    check_ajax_referer('ucfc_cart_nonce', 'nonce');
    
    // Get cart instance
    $cart = ucfc_get_cart();
    
    // Clear cart
    $result = $cart->clear_cart();
    
    if (!$result) {
        wp_send_json_error(['message' => 'Failed to clear cart']);
    }
    
    wp_send_json_success([
        'message' => 'Cart cleared',
        'cart_count' => 0,
        'cart_subtotal' => '$0.00',
        'cart_total' => '$0.00',
        'cart_items' => []
    ]);
}
add_action('wp_ajax_ucfc_clear_cart', 'ucfc_ajax_clear_cart');
add_action('wp_ajax_nopriv_ucfc_clear_cart', 'ucfc_ajax_clear_cart');

/**
 * Enqueue cart scripts and localize data
 */
function ucfc_enqueue_cart_scripts() {
    // Enqueue cart JavaScript
    wp_enqueue_script(
        'ucfc-cart',
        get_template_directory_uri() . '/assets/js/cart.js',
        ['jquery'],
        '1.0.0',
        true
    );
    
    // Localize cart data
    wp_localize_script('ucfc-cart', 'ucfcCart', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ucfc_cart_nonce'),
        'cart_url' => home_url('/cart'),
        'checkout_url' => home_url('/checkout'),
        'messages' => [
            'added' => __('Added to cart!', 'uncle-chans-chicken'),
            'removed' => __('Removed from cart', 'uncle-chans-chicken'),
            'updated' => __('Cart updated', 'uncle-chans-chicken'),
            'cleared' => __('Cart cleared', 'uncle-chans-chicken'),
            'error' => __('An error occurred', 'uncle-chans-chicken')
        ]
    ]);
}
add_action('wp_enqueue_scripts', 'ucfc_enqueue_cart_scripts');
