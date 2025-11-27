<?php
/**
 * Assign page templates via WordPress admin
 * Place in theme root and execute from WordPress dashboard
 */

// Check if we're in WordPress context
if (!defined('ABSPATH')) {
    // Load WordPress (adjust path based on theme location)
    $wp_load = dirname(__FILE__) . '/../../../wp-load.php';
    if (file_exists($wp_load)) {
        require_once($wp_load);
    } else {
        die('Cannot find wp-load.php');
    }
}

// Security check - only admins can run this
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator.');
}

echo '<!DOCTYPE html><html><head><title>Assign Templates</title></head><body>';
echo '<h1>Assigning Page Templates...</h1>';

// Assign checkout template to Checkout page (ID 46)
$result1 = update_post_meta(46, '_wp_page_template', 'page-checkout.php');
if ($result1 !== false) {
    echo '<p style="color: green;">✅ Assigned page-checkout.php to Checkout page (ID: 46)</p>';
} else {
    echo '<p style="color: orange;">⚠️ Checkout page template may already be assigned</p>';
}

// Check if Order Confirmation page exists
$existing_confirmation = get_page_by_path('order-confirmation');

if ($existing_confirmation) {
    // Update existing page
    $result2 = update_post_meta($existing_confirmation->ID, '_wp_page_template', 'page-order-confirmation.php');
    echo '<p style="color: orange;">⚠️ Order Confirmation page already exists (ID: ' . $existing_confirmation->ID . ')</p>';
    echo '<p style="color: green;">✅ Updated template to page-order-confirmation.php</p>';
} else {
    // Create Order Confirmation page
    $confirmation_page = array(
        'post_title'    => 'Order Confirmation',
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => 'order-confirmation',
    );

    $page_id = wp_insert_post($confirmation_page);

    if ($page_id && !is_wp_error($page_id)) {
        // Assign template
        update_post_meta($page_id, '_wp_page_template', 'page-order-confirmation.php');
        echo '<p style="color: green;">✅ Created Order Confirmation page (ID: ' . $page_id . ') at /order-confirmation</p>';
        echo '<p style="color: green;">✅ Assigned page-order-confirmation.php template</p>';
    } else {
        echo '<p style="color: red;">❌ Failed to create Order Confirmation page</p>';
        if (is_wp_error($page_id)) {
            echo '<p style="color: red;">Error: ' . $page_id->get_error_message() . '</p>';
        }
    }
}

echo '<h2 style="color: green;">🎉 Templates Assignment Complete!</h2>';
echo '<p><a href="' . admin_url() . '">← Back to Dashboard</a> | <a href="' . home_url() . '">View Site</a></p>';
echo '</body></html>';
