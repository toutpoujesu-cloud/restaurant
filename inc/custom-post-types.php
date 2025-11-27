<?php
/**
 * Custom Post Types for Restaurant Manager
 * 
 * Register all custom post types for the restaurant system
 * - Menu Items
 * - Locations
 * - Special Offers
 * - Customer Reviews
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Menu Items Post Type
 */
function ucfc_register_menu_items_post_type() {
    $labels = array(
        'name'                  => _x('Menu Items', 'Post Type General Name', 'uncle-chans'),
        'singular_name'         => _x('Menu Item', 'Post Type Singular Name', 'uncle-chans'),
        'menu_name'             => __('Menu Items', 'uncle-chans'),
        'name_admin_bar'        => __('Menu Item', 'uncle-chans'),
        'archives'              => __('Menu Item Archives', 'uncle-chans'),
        'attributes'            => __('Menu Item Attributes', 'uncle-chans'),
        'parent_item_colon'     => __('Parent Menu Item:', 'uncle-chans'),
        'all_items'             => __('All Menu Items', 'uncle-chans'),
        'add_new_item'          => __('Add New Menu Item', 'uncle-chans'),
        'add_new'               => __('Add New', 'uncle-chans'),
        'new_item'              => __('New Menu Item', 'uncle-chans'),
        'edit_item'             => __('Edit Menu Item', 'uncle-chans'),
        'update_item'           => __('Update Menu Item', 'uncle-chans'),
        'view_item'             => __('View Menu Item', 'uncle-chans'),
        'view_items'            => __('View Menu Items', 'uncle-chans'),
        'search_items'          => __('Search Menu Item', 'uncle-chans'),
        'not_found'             => __('Not found', 'uncle-chans'),
        'not_found_in_trash'    => __('Not found in Trash', 'uncle-chans'),
        'featured_image'        => __('Menu Item Image', 'uncle-chans'),
        'set_featured_image'    => __('Set menu item image', 'uncle-chans'),
        'remove_featured_image' => __('Remove menu item image', 'uncle-chans'),
        'use_featured_image'    => __('Use as menu item image', 'uncle-chans'),
        'insert_into_item'      => __('Insert into menu item', 'uncle-chans'),
        'uploaded_to_this_item' => __('Uploaded to this menu item', 'uncle-chans'),
        'items_list'            => __('Menu items list', 'uncle-chans'),
        'items_list_navigation' => __('Menu items list navigation', 'uncle-chans'),
        'filter_items_list'     => __('Filter menu items list', 'uncle-chans'),
    );
    
    $args = array(
        'label'                 => __('Menu Item', 'uncle-chans'),
        'description'           => __('Restaurant menu items', 'uncle-chans'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'taxonomies'            => array('menu_category'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-food',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    
    register_post_type('menu_item', $args);
}
add_action('init', 'ucfc_register_menu_items_post_type', 0);

/**
 * Register Menu Categories Taxonomy
 */
function ucfc_register_menu_categories_taxonomy() {
    $labels = array(
        'name'                       => _x('Menu Categories', 'Taxonomy General Name', 'uncle-chans'),
        'singular_name'              => _x('Menu Category', 'Taxonomy Singular Name', 'uncle-chans'),
        'menu_name'                  => __('Categories', 'uncle-chans'),
        'all_items'                  => __('All Categories', 'uncle-chans'),
        'parent_item'                => __('Parent Category', 'uncle-chans'),
        'parent_item_colon'          => __('Parent Category:', 'uncle-chans'),
        'new_item_name'              => __('New Category Name', 'uncle-chans'),
        'add_new_item'               => __('Add New Category', 'uncle-chans'),
        'edit_item'                  => __('Edit Category', 'uncle-chans'),
        'update_item'                => __('Update Category', 'uncle-chans'),
        'view_item'                  => __('View Category', 'uncle-chans'),
        'separate_items_with_commas' => __('Separate categories with commas', 'uncle-chans'),
        'add_or_remove_items'        => __('Add or remove categories', 'uncle-chans'),
        'choose_from_most_used'      => __('Choose from the most used', 'uncle-chans'),
        'popular_items'              => __('Popular Categories', 'uncle-chans'),
        'search_items'               => __('Search Categories', 'uncle-chans'),
        'not_found'                  => __('Not Found', 'uncle-chans'),
        'no_terms'                   => __('No categories', 'uncle-chans'),
        'items_list'                 => __('Categories list', 'uncle-chans'),
        'items_list_navigation'      => __('Categories list navigation', 'uncle-chans'),
    );
    
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'show_in_rest'               => true,
    );
    
    register_taxonomy('menu_category', array('menu_item'), $args);
}
add_action('init', 'ucfc_register_menu_categories_taxonomy', 0);

/**
 * Register Locations Post Type
 */
function ucfc_register_locations_post_type() {
    $labels = array(
        'name'                  => _x('Locations', 'Post Type General Name', 'uncle-chans'),
        'singular_name'         => _x('Location', 'Post Type Singular Name', 'uncle-chans'),
        'menu_name'             => __('Locations', 'uncle-chans'),
        'name_admin_bar'        => __('Location', 'uncle-chans'),
        'add_new_item'          => __('Add New Location', 'uncle-chans'),
        'add_new'               => __('Add New', 'uncle-chans'),
        'new_item'              => __('New Location', 'uncle-chans'),
        'edit_item'             => __('Edit Location', 'uncle-chans'),
        'update_item'           => __('Update Location', 'uncle-chans'),
        'view_item'             => __('View Location', 'uncle-chans'),
        'all_items'             => __('All Locations', 'uncle-chans'),
        'search_items'          => __('Search Location', 'uncle-chans'),
    );
    
    $args = array(
        'label'                 => __('Location', 'uncle-chans'),
        'description'           => __('Restaurant locations', 'uncle-chans'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-location',
        'show_in_admin_bar'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    
    register_post_type('location', $args);
}
add_action('init', 'ucfc_register_locations_post_type', 0);

/**
 * Register Special Offers Post Type
 */
function ucfc_register_offers_post_type() {
    $labels = array(
        'name'                  => _x('Special Offers', 'Post Type General Name', 'uncle-chans'),
        'singular_name'         => _x('Special Offer', 'Post Type Singular Name', 'uncle-chans'),
        'menu_name'             => __('Special Offers', 'uncle-chans'),
        'name_admin_bar'        => __('Special Offer', 'uncle-chans'),
        'add_new_item'          => __('Add New Offer', 'uncle-chans'),
        'add_new'               => __('Add New', 'uncle-chans'),
        'new_item'              => __('New Offer', 'uncle-chans'),
        'edit_item'             => __('Edit Offer', 'uncle-chans'),
        'update_item'           => __('Update Offer', 'uncle-chans'),
        'view_item'             => __('View Offer', 'uncle-chans'),
        'all_items'             => __('All Offers', 'uncle-chans'),
        'search_items'          => __('Search Offer', 'uncle-chans'),
    );
    
    $args = array(
        'label'                 => __('Special Offer', 'uncle-chans'),
        'description'           => __('Special offers and promotions', 'uncle-chans'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 7,
        'menu_icon'             => 'dashicons-tag',
        'show_in_admin_bar'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    
    register_post_type('special_offer', $args);
}
add_action('init', 'ucfc_register_offers_post_type', 0);

/**
 * Register Customer Reviews Post Type
 */
function ucfc_register_reviews_post_type() {
    $labels = array(
        'name'                  => _x('Customer Reviews', 'Post Type General Name', 'uncle-chans'),
        'singular_name'         => _x('Review', 'Post Type Singular Name', 'uncle-chans'),
        'menu_name'             => __('Reviews', 'uncle-chans'),
        'name_admin_bar'        => __('Review', 'uncle-chans'),
        'add_new_item'          => __('Add New Review', 'uncle-chans'),
        'add_new'               => __('Add New', 'uncle-chans'),
        'new_item'              => __('New Review', 'uncle-chans'),
        'edit_item'             => __('Edit Review', 'uncle-chans'),
        'update_item'           => __('Update Review', 'uncle-chans'),
        'view_item'             => __('View Review', 'uncle-chans'),
        'all_items'             => __('All Reviews', 'uncle-chans'),
        'search_items'          => __('Search Review', 'uncle-chans'),
    );
    
    $args = array(
        'label'                 => __('Review', 'uncle-chans'),
        'description'           => __('Customer reviews and testimonials', 'uncle-chans'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 8,
        'menu_icon'             => 'dashicons-star-filled',
        'show_in_admin_bar'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    
    register_post_type('customer_review', $args);
}
add_action('init', 'ucfc_register_reviews_post_type', 0);
