<?php
/**
 * Direct Menu Import Script
 * Run via: docker exec uncle-chans-wordpress php /var/www/html/wp-content/themes/uncle-chans-chicken/import-direct.php
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('/var/www/html/wp-load.php');

echo "===========================================\n";
echo "Uncle Chan's Menu Import Script\n";
echo "===========================================\n\n";

// Menu data
$categories_data = array(
    array('name' => 'Fried Chicken', 'slug' => 'chicken'),
    array('name' => 'Wednesday Wings', 'slug' => 'wings'),
    array('name' => 'Friday Catfish', 'slug' => 'catfish')
);

$items_data = array(
    array('title' => 'Wing Combo', 'desc' => 'Get your fix with 10 pieces of golden, crispy wings. Each piece is hand-breaded with our secret family recipe!', 'price' => 12, 'cost' => 5.5, 'cat' => 'chicken', 'stock' => 100, 'sold' => 156, 'rating' => 4.5),
    array('title' => 'Family Box', 'desc' => 'Our bestseller! 20 pieces of mouthwatering fried chicken. Crispy, seasoned, and made fresh just for you.', 'price' => 21, 'cost' => 9.5, 'cat' => 'chicken', 'stock' => 150, 'sold' => 287, 'rating' => 4.8, 'featured' => true),
    array('title' => 'Mega Feast', 'desc' => 'Feed the whole crew! 30 pieces of our legendary chicken plus 3 sides. Perfect for parties, squad nights, or serious leftovers.', 'price' => 30, 'cost' => 13.5, 'cat' => 'chicken', 'stock' => 80, 'sold' => 198, 'rating' => 4.7),
    array('title' => 'Wednesday Wing Deal', 'desc' => 'Wednesday special! 10 pieces of our signature wings with 2 sides. Perfect mid-week treat!', 'price' => 12, 'cost' => 5.5, 'cat' => 'wings', 'stock' => 200, 'sold' => 243, 'rating' => 4.6, 'featured' => true),
    array('title' => 'Wednesday Family Deal', 'desc' => 'Wednesday special! 20 pieces of our delicious fried chicken with 2 sides. Feed the whole family!', 'price' => 21, 'cost' => 9.5, 'cat' => 'wings', 'stock' => 150, 'sold' => 187, 'rating' => 4.5),
    array('title' => 'Wednesday Mega Deal', 'desc' => 'Wednesday special! 30 pieces of our legendary chicken with 3 sides. Perfect for squad nights!', 'price' => 30, 'cost' => 13.5, 'cat' => 'wings', 'stock' => 100, 'sold' => 145, 'rating' => 4.4),
    array('title' => '1-Piece Catfish Meal', 'desc' => 'Friday Catfish Special! One piece of golden fried catfish with hush puppies and 2 sides.', 'price' => 9, 'cost' => 4, 'cat' => 'catfish', 'stock' => 120, 'sold' => 178, 'rating' => 4.7, 'featured' => true),
    array('title' => '2-Piece Catfish Meal', 'desc' => 'Friday Catfish Special! Two pieces of golden fried catfish with hush puppies and 2 sides.', 'price' => 12, 'cost' => 5.5, 'cat' => 'catfish', 'stock' => 100, 'sold' => 134, 'rating' => 4.6)
);

// Create categories
echo "Creating categories...\n";
$category_ids = array();
$categories_created = 0;

foreach ($categories_data as $cat) {
    $term = term_exists($cat['name'], 'menu_category');
    if (!$term) {
        $result = wp_insert_term($cat['name'], 'menu_category', array('slug' => $cat['slug']));
        if (!is_wp_error($result)) {
            $category_ids[$cat['slug']] = $result['term_id'];
            echo "  ✅ Created: {$cat['name']} (ID: {$result['term_id']})\n";
            $categories_created++;
        } else {
            echo "  ❌ Error: {$cat['name']} - " . $result->get_error_message() . "\n";
        }
    } else {
        $category_ids[$cat['slug']] = $term['term_id'];
        echo "  ℹ️  Exists: {$cat['name']} (ID: {$term['term_id']})\n";
    }
}

echo "\n";

// Import menu items
echo "Importing menu items...\n";
$imported = 0;
$skipped = 0;

foreach ($items_data as $item) {
    // Check if exists
    $existing = get_page_by_title($item['title'], OBJECT, 'menu_item');
    if ($existing) {
        echo "  ⚠️  Skipped: {$item['title']} (already exists)\n";
        $skipped++;
        continue;
    }
    
    // Create post
    $post_id = wp_insert_post(array(
        'post_title' => $item['title'],
        'post_content' => $item['desc'],
        'post_excerpt' => wp_trim_words($item['desc'], 15),
        'post_status' => 'publish',
        'post_type' => 'menu_item',
        'post_author' => 1
    ));
    
    if (!is_wp_error($post_id) && $post_id) {
        // Add metadata
        update_post_meta($post_id, '_ucfc_price', $item['price']);
        update_post_meta($post_id, '_ucfc_cost', $item['cost']);
        update_post_meta($post_id, '_ucfc_stock', $item['stock']);
        update_post_meta($post_id, '_ucfc_sold_count', $item['sold']);
        update_post_meta($post_id, '_ucfc_rating', $item['rating']);
        
        $profit_margin = (($item['price'] - $item['cost']) / $item['price']) * 100;
        update_post_meta($post_id, '_ucfc_profit_margin', round($profit_margin));
        
        if (isset($item['featured']) && $item['featured']) {
            update_post_meta($post_id, '_menu_item_is_featured', '1');
        }
        
        // Set category
        if (isset($category_ids[$item['cat']])) {
            wp_set_post_terms($post_id, array($category_ids[$item['cat']]), 'menu_category');
        }
        
        echo "  ✅ Imported: {$item['title']} (€{$item['price']}, ID: {$post_id})\n";
        $imported++;
    } else {
        $error_msg = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error';
        echo "  ❌ Failed: {$item['title']} - {$error_msg}\n";
    }
}

echo "\n";
echo "===========================================\n";
echo "Import Complete!\n";
echo "===========================================\n";
echo "Categories created: $categories_created\n";
echo "Items imported: $imported\n";
echo "Items skipped: $skipped\n";
echo "\nView your menu at:\n";
echo "Dashboard: http://unclechans.local:8080/wp-admin/admin.php?page=ucfc-menu-dashboard\n";
echo "Menu Items: http://unclechans.local:8080/wp-admin/edit.php?post_type=menu_item\n";
