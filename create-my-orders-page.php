<?php
/**
 * Create My Orders page
 * Run once: docker exec uncle-chans-wordpress php /var/www/html/wp-content/themes/uncle-chans-chicken/create-my-orders-page.php
 */

require_once(__DIR__ . '/../../../wp-load.php');

echo "Creating My Orders page...\n";

// Check if page already exists
$existing = get_page_by_path('my-orders');

if ($existing) {
    echo "⚠️  My Orders page already exists (ID: {$existing->ID})\n";
    echo "Updating template...\n";
    
    update_post_meta($existing->ID, '_wp_page_template', 'page-my-orders.php');
    echo "✅ Template updated to page-my-orders.php\n";
} else {
    // Create new page
    $page_data = array(
        'post_title'    => 'My Orders',
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => 'my-orders',
        'comment_status' => 'closed',
        'ping_status'   => 'closed'
    );

    $page_id = wp_insert_post($page_data);

    if ($page_id && !is_wp_error($page_id)) {
        // Assign template
        update_post_meta($page_id, '_wp_page_template', 'page-my-orders.php');
        
        echo "✅ My Orders page created (ID: $page_id)\n";
        echo "✅ Template assigned: page-my-orders.php\n";
        echo "📍 URL: " . home_url('/my-orders') . "\n";
    } else {
        echo "❌ Failed to create page\n";
        if (is_wp_error($page_id)) {
            echo "Error: " . $page_id->get_error_message() . "\n";
        }
    }
}

echo "\n🎉 Setup complete!\n";
