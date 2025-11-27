<?php
/**
 * Create Cart and Checkout Pages
 * Run once to set up necessary pages
 */

require_once('/var/www/html/wp-load.php');

// Create Cart Page
$cart_page = array(
    'post_title'    => 'Shopping Cart',
    'post_name'     => 'cart',
    'post_content'  => '<!-- Cart page content managed by page-cart.php template -->',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_author'   => 1,
    'page_template' => 'page-cart.php'
);

$cart_page_id = wp_insert_post($cart_page);

if ($cart_page_id && !is_wp_error($cart_page_id)) {
    update_post_meta($cart_page_id, '_wp_page_template', 'page-cart.php');
    echo "✅ Cart page created! ID: $cart_page_id\n";
    echo "   URL: " . get_permalink($cart_page_id) . "\n\n";
} else {
    echo "❌ Error creating cart page\n";
    if (is_wp_error($cart_page_id)) {
        echo "   Error: " . $cart_page_id->get_error_message() . "\n";
    }
}

// Create Checkout Page
$checkout_page = array(
    'post_title'    => 'Checkout',
    'post_name'     => 'checkout',
    'post_content'  => '<!-- Checkout page - Coming soon -->',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_author'   => 1
);

$checkout_page_id = wp_insert_post($checkout_page);

if ($checkout_page_id && !is_wp_error($checkout_page_id)) {
    echo "✅ Checkout page created! ID: $checkout_page_id\n";
    echo "   URL: " . get_permalink($checkout_page_id) . "\n\n";
} else {
    echo "❌ Error creating checkout page\n";
    if (is_wp_error($checkout_page_id)) {
        echo "   Error: " . $checkout_page_id->get_error_message() . "\n";
    }
}

echo "🎉 Pages created successfully!\n";
echo "\nCart URL: " . home_url('/cart') . "\n";
echo "Checkout URL: " . home_url('/checkout') . "\n";
?>
