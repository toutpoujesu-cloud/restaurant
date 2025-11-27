<?php
/**
 * Test Cart System
 * Add items, check cart, verify persistence
 */

require_once('/var/www/html/wp-load.php');

echo "🧪 TESTING CART SYSTEM\n";
echo "==========================================\n\n";

// Get cart instance
$cart = ucfc_get_cart();

// Test 1: Add Wing Combo to cart
echo "Test 1: Adding Wing Combo (ID: 29) to cart...\n";
$result = $cart->add_item(29, 2);
if (is_wp_error($result)) {
    echo "❌ Error: " . $result->get_error_message() . "\n";
} else {
    echo "✅ Successfully added Wing Combo x2\n";
}

// Test 2: Add Family Box to cart
echo "\nTest 2: Adding Family Box (ID: 30) to cart...\n";
$result = $cart->add_item(30, 1);
if (is_wp_error($result)) {
    echo "❌ Error: " . $result->get_error_message() . "\n";
} else {
    echo "✅ Successfully added Family Box x1\n";
}

// Test 3: Get cart contents
echo "\nTest 3: Retrieving cart contents...\n";
$items = $cart->get_cart();
echo "📦 Cart has " . count($items) . " unique item(s)\n";
foreach ($items as $item) {
    echo "   - {$item->product_name} x{$item->quantity} = \${$item->subtotal}\n";
}

// Test 4: Get cart totals
echo "\nTest 4: Calculating cart totals...\n";
$totals = $cart->get_totals();
echo "   Item Count: {$totals['item_count']}\n";
echo "   Subtotal: \${$totals['subtotal']}\n";
echo "   Tax (8%): \${$totals['tax']}\n";
echo "   Total: \${$totals['total']}\n";

// Test 5: Update quantity
echo "\nTest 5: Updating Wing Combo quantity to 3...\n";
$item_id = $items[0]->id;
$result = $cart->update_quantity($item_id, 3);
if (is_wp_error($result)) {
    echo "❌ Error: " . $result->get_error_message() . "\n";
} else {
    echo "✅ Successfully updated quantity\n";
    $totals = $cart->get_totals();
    echo "   New Total: \${$totals['total']}\n";
}

// Test 6: Check stock validation
echo "\nTest 6: Testing stock validation (trying to add 1000 items)...\n";
$result = $cart->add_item(29, 1000);
if (is_wp_error($result)) {
    echo "✅ Stock validation working: " . $result->get_error_message() . "\n";
} else {
    echo "❌ Stock validation failed - should have been blocked\n";
}

// Test 7: Get item count
echo "\nTest 7: Getting cart item count...\n";
$count = $cart->get_item_count();
echo "🛒 Cart badge should show: $count items\n";

// Test 8: Database verification
echo "\nTest 8: Verifying database records...\n";
global $wpdb;
$db_items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cart_items ORDER BY id DESC LIMIT 5");
echo "   Found " . count($db_items) . " items in database\n";
foreach ($db_items as $db_item) {
    echo "   - ID: {$db_item->id}, Product: {$db_item->product_name}, Qty: {$db_item->quantity}\n";
}

$sessions = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cart_sessions ORDER BY created_at DESC LIMIT 3");
echo "\n   Found " . count($sessions) . " active session(s)\n";
foreach ($sessions as $session) {
    $expires = strtotime($session->expires_at) - time();
    $days = round($expires / 86400);
    echo "   - Session: " . substr($session->session_id, 0, 16) . "... (expires in ~$days days)\n";
}

echo "\n==========================================\n";
echo "✅ ALL TESTS COMPLETED!\n";
echo "\n🌐 Visit these URLs to see the cart in action:\n";
echo "   Frontend: http://unclechans.local:8080/\n";
echo "   Cart Page: http://unclechans.local:8080/cart\n";
echo "   Menu Dashboard: http://unclechans.local:8080/wp-admin/admin.php?page=ucfc-menu-dashboard\n";
?>
