<?php
/**
 * Create Kitchen Display System Page
 */

// Load WordPress
require_once(__DIR__ . '/../../../wp-load.php');

echo "🍳 Creating Kitchen Display System Page...\n\n";

// Check if page already exists
$existing_page = get_page_by_path('kitchen-display');

if ($existing_page) {
    echo "⚠️  Page already exists (ID: {$existing_page->ID})\n";
    echo "Updating existing page...\n";
    
    wp_update_post(array(
        'ID' => $existing_page->ID,
        'post_title' => 'Kitchen Display',
        'post_status' => 'publish',
        'post_type' => 'page',
        'page_template' => 'page-kitchen-display.php'
    ));
    
    $page_id = $existing_page->ID;
} else {
    // Create new page
    $page_id = wp_insert_post(array(
        'post_title' => 'Kitchen Display',
        'post_name' => 'kitchen-display',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => '<!-- Kitchen Display System -->',
        'page_template' => 'page-kitchen-display.php'
    ));
}

if ($page_id) {
    update_post_meta($page_id, '_wp_page_template', 'page-kitchen-display.php');
    
    $page_url = get_permalink($page_id);
    
    echo "✅ Kitchen Display page created (ID: {$page_id})\n";
    echo "📱 URL: {$page_url}\n";
    echo "📄 Template: page-kitchen-display.php\n";
    echo "\n🎉 Setup complete!\n";
    echo "\n📚 Access Instructions:\n";
    echo "1. Visit: {$page_url}\n";
    echo "2. Or go to: Restaurant → Kitchen Display (admin menu)\n";
    echo "3. Click fullscreen for best experience\n";
    echo "4. Auto-refreshes every 30 seconds\n";
    echo "\n⚠️  Note: Only accessible to admin users\n";
} else {
    echo "❌ Failed to create page\n";
}
