<?php
/**
 * Update Menu Items with Images from Frontend
 */

define('WP_USE_THEMES', false);
require_once('/var/www/html/wp-load.php');

echo "===========================================\n";
echo "Updating Menu Items with Images\n";
echo "===========================================\n\n";

// Image URLs from landing page
$images = array(
    'Wing Combo' => 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80',
    'Family Box' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80',
    'Mega Feast' => 'https://images.unsplash.com/photo-1608039755401-742074f0548d?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80',
    'Wednesday Wing Deal' => 'https://images.unsplash.com/photo-1567620832903-9fc6debc209f?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80',
    'Wednesday Family Deal' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80',
    'Wednesday Mega Deal' => 'https://images.unsplash.com/photo-1608039755401-742074f0548d?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80',
    '1-Piece Catfish Meal' => 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80',
    '2-Piece Catfish Meal' => 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?ixlib=rb-4.0.1&auto=format&fit=crop&w=800&q=80'
);

$updated_count = 0;

foreach ($images as $title => $image_url) {
    // Find the post
    $post = get_page_by_title($title, OBJECT, 'menu_item');
    
    if (!$post) {
        echo "  ⚠️  Not found: $title\n";
        continue;
    }
    
    // Check if it already has a featured image
    if (has_post_thumbnail($post->ID)) {
        echo "  ℹ️  Already has image: $title\n";
        continue;
    }
    
    // Download and attach the image
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // Download file to temp location
    $tmp = download_url($image_url);
    
    if (is_wp_error($tmp)) {
        echo "  ❌ Failed to download: $title - " . $tmp->get_error_message() . "\n";
        continue;
    }
    
    // Set up the file array
    $file_array = array(
        'name' => basename($image_url) . '.jpg',
        'tmp_name' => $tmp
    );
    
    // Upload file to media library
    $attachment_id = media_handle_sideload($file_array, $post->ID, $title . ' Image');
    
    // Check for errors
    if (is_wp_error($attachment_id)) {
        @unlink($file_array['tmp_name']);
        echo "  ❌ Failed to attach: $title - " . $attachment_id->get_error_message() . "\n";
        continue;
    }
    
    // Set as featured image
    set_post_thumbnail($post->ID, $attachment_id);
    
    echo "  ✅ Updated: $title (Attachment ID: $attachment_id)\n";
    $updated_count++;
}

echo "\n";
echo "===========================================\n";
echo "Update Complete!\n";
echo "===========================================\n";
echo "Images attached: $updated_count\n";
echo "\nView dashboard at:\n";
echo "http://unclechans.local:8080/wp-admin/admin.php?page=ucfc-menu-dashboard\n";
