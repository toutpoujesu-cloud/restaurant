<?php
/**
 * Menu Builder System
 * Universal Restaurant Menu Management for ANY type of restaurant
 * 
 * Features:
 * - Menu Items (Dishes/Products)
 * - Categories (Pizza, Sushi, Burgers, etc.)
 * - Modifiers (Sauces, Sides, Toppings, Size, Temperature, etc.)
 * - Combo Deals & Meal Packages
 * - Dietary Tags (Vegan, Halal, Gluten-Free, etc.)
 * - Pricing Variations (Size-based, Time-based)
 * 
 * @package Uncle_Chans_Chicken
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Menu Item Post Type
 */
function ucfc_register_menu_items() {
    $labels = array(
        'name'               => 'Menu Items',
        'singular_name'      => 'Menu Item',
        'menu_name'          => 'Menu Items',
        'add_new'            => 'Add New Item',
        'add_new_item'       => 'Add New Menu Item',
        'edit_item'          => 'Edit Menu Item',
        'new_item'           => 'New Menu Item',
        'view_item'          => 'View Menu Item',
        'search_items'       => 'Search Menu Items',
        'not_found'          => 'No menu items found',
        'not_found_in_trash' => 'No menu items found in trash'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => 'restaurant-settings',
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-food',
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'rewrite'             => array('slug' => 'menu'),
        'capability_type'     => 'post',
        'menu_position'       => 20
    );

    register_post_type('menu_item', $args);
}
add_action('init', 'ucfc_register_menu_items');

/**
 * Flush rewrite rules on theme activation
 */
function ucfc_flush_rewrite_rules() {
    ucfc_register_menu_items();
    ucfc_register_modifier_groups();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'ucfc_flush_rewrite_rules');

/**
 * Register Menu Categories Taxonomy
 */
function ucfc_register_menu_categories() {
    $labels = array(
        'name'              => 'Menu Categories',
        'singular_name'     => 'Category',
        'search_items'      => 'Search Categories',
        'all_items'         => 'All Categories',
        'parent_item'       => 'Parent Category',
        'parent_item_colon' => 'Parent Category:',
        'edit_item'         => 'Edit Category',
        'update_item'       => 'Update Category',
        'add_new_item'      => 'Add New Category',
        'new_item_name'     => 'New Category Name',
        'menu_name'         => 'Categories',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'menu-category'),
    );

    register_taxonomy('menu_category', array('menu_item'), $args);
}
add_action('init', 'ucfc_register_menu_categories');

/**
 * Register Dietary Tags Taxonomy
 */
function ucfc_register_dietary_tags() {
    $labels = array(
        'name'              => 'Dietary Tags',
        'singular_name'     => 'Dietary Tag',
        'search_items'      => 'Search Tags',
        'all_items'         => 'All Tags',
        'edit_item'         => 'Edit Tag',
        'update_item'       => 'Update Tag',
        'add_new_item'      => 'Add New Tag',
        'new_item_name'     => 'New Tag Name',
        'menu_name'         => 'Dietary Tags',
    );

    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'dietary'),
    );

    register_taxonomy('dietary_tag', array('menu_item'), $args);
}
add_action('init', 'ucfc_register_dietary_tags');

/**
 * Register Modifier Groups Post Type (Sauces, Sides, Toppings, etc.)
 */
function ucfc_register_modifier_groups() {
    $labels = array(
        'name'               => 'Modifier Groups',
        'singular_name'      => 'Modifier Group',
        'add_new'            => 'Add New Group',
        'add_new_item'       => 'Add New Modifier Group',
        'edit_item'          => 'Edit Modifier Group',
        'new_item'           => 'New Modifier Group',
        'view_item'          => 'View Modifier Group',
        'search_items'       => 'Search Modifier Groups',
        'not_found'          => 'No modifier groups found'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => 'restaurant-settings',
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-plus-alt2',
        'supports'            => array('title'),
        'capability_type'     => 'post',
    );

    register_post_type('modifier_group', $args);
}
add_action('init', 'ucfc_register_modifier_groups');

/**
 * Add Meta Boxes for Menu Items
 */
function ucfc_add_menu_item_metaboxes() {
    // Pricing Meta Box
    add_meta_box(
        'ucfc_menu_pricing',
        '💰 Pricing & Sizes',
        'ucfc_menu_pricing_metabox',
        'menu_item',
        'normal',
        'high'
    );

    // Modifiers Meta Box
    add_meta_box(
        'ucfc_menu_modifiers',
        '🎛️ Modifiers & Add-ons',
        'ucfc_menu_modifiers_metabox',
        'menu_item',
        'normal',
        'high'
    );

    // Nutritional Info Meta Box
    add_meta_box(
        'ucfc_menu_nutrition',
        '🥗 Nutritional Information',
        'ucfc_menu_nutrition_metabox',
        'menu_item',
        'side',
        'default'
    );

    // Availability Meta Box
    add_meta_box(
        'ucfc_menu_availability',
        '🕐 Availability & Stock',
        'ucfc_menu_availability_metabox',
        'menu_item',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'ucfc_add_menu_item_metaboxes');

/**
 * Pricing Meta Box Content
 */
function ucfc_menu_pricing_metabox($post) {
    wp_nonce_field('ucfc_menu_pricing_nonce', 'ucfc_menu_pricing_nonce');
    
    $price_type = get_post_meta($post->ID, '_ucfc_price_type', true) ?: 'simple';
    $base_price = get_post_meta($post->ID, '_ucfc_base_price', true);
    $sizes = get_post_meta($post->ID, '_ucfc_sizes', true) ?: array();
    ?>
    <div class="ucfc-pricing-options">
        <p>
            <label><strong>Pricing Type:</strong></label><br>
            <select name="ucfc_price_type" id="ucfc_price_type" style="width: 100%; max-width: 300px;">
                <option value="simple" <?php selected($price_type, 'simple'); ?>>Simple (One Price)</option>
                <option value="variable" <?php selected($price_type, 'variable'); ?>>Variable (Multiple Sizes)</option>
            </select>
        </p>

        <div id="simple-price" style="<?php echo $price_type === 'variable' ? 'display:none;' : ''; ?>">
            <p>
                <label><strong>Price (€):</strong></label><br>
                <input type="number" step="0.01" name="ucfc_base_price" value="<?php echo esc_attr($base_price); ?>" 
                       style="width: 150px;" placeholder="9.99">
            </p>
        </div>

        <div id="variable-price" style="<?php echo $price_type === 'simple' ? 'display:none;' : ''; ?>">
            <p><strong>Size Variations:</strong></p>
            <div id="size-variations">
                <?php
                if (!empty($sizes)) {
                    foreach ($sizes as $index => $size) {
                        ?>
                        <div class="size-row" style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-left: 3px solid #2271b1;">
                            <input type="text" name="ucfc_sizes[<?php echo $index; ?>][name]" 
                                   value="<?php echo esc_attr($size['name']); ?>" 
                                   placeholder="Size Name (e.g., Small, Medium, Large)" style="width: 200px; margin-right: 10px;">
                            <input type="number" step="0.01" name="ucfc_sizes[<?php echo $index; ?>][price]" 
                                   value="<?php echo esc_attr($size['price']); ?>" 
                                   placeholder="Price" style="width: 100px; margin-right: 10px;">
                            <button type="button" class="button remove-size">Remove</button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <button type="button" class="button" id="add-size">+ Add Size</button>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#ucfc_price_type').change(function() {
            if ($(this).val() === 'simple') {
                $('#simple-price').show();
                $('#variable-price').hide();
            } else {
                $('#simple-price').hide();
                $('#variable-price').show();
            }
        });

        let sizeIndex = <?php echo count($sizes); ?>;
        $('#add-size').click(function() {
            const html = `
                <div class="size-row" style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-left: 3px solid #2271b1;">
                    <input type="text" name="ucfc_sizes[${sizeIndex}][name]" placeholder="Size Name" style="width: 200px; margin-right: 10px;">
                    <input type="number" step="0.01" name="ucfc_sizes[${sizeIndex}][price]" placeholder="Price" style="width: 100px; margin-right: 10px;">
                    <button type="button" class="button remove-size">Remove</button>
                </div>
            `;
            $('#size-variations').append(html);
            sizeIndex++;
        });

        $(document).on('click', '.remove-size', function() {
            $(this).closest('.size-row').remove();
        });
    });
    </script>
    <?php
}

/**
 * Modifiers Meta Box Content
 */
function ucfc_menu_modifiers_metabox($post) {
    wp_nonce_field('ucfc_menu_modifiers_nonce', 'ucfc_menu_modifiers_nonce');
    
    $modifier_groups = get_posts(array(
        'post_type' => 'modifier_group',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    
    $assigned_modifiers = get_post_meta($post->ID, '_ucfc_modifiers', true) ?: array();
    ?>
    <div class="ucfc-modifiers">
        <p>Select which modifier groups apply to this menu item:</p>
        <?php if (empty($modifier_groups)): ?>
            <p style="color: #666; font-style: italic;">
                No modifier groups created yet. 
                <a href="<?php echo admin_url('post-new.php?post_type=modifier_group'); ?>">Create your first modifier group</a> 
                (e.g., Sauces, Sides, Toppings, Cooking Temperature, etc.)
            </p>
        <?php else: ?>
            <?php foreach ($modifier_groups as $group): ?>
                <label style="display: block; margin: 10px 0;">
                    <input type="checkbox" name="ucfc_modifiers[]" value="<?php echo $group->ID; ?>" 
                           <?php checked(in_array($group->ID, $assigned_modifiers)); ?>>
                    <strong><?php echo esc_html($group->post_title); ?></strong>
                </label>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Nutritional Info Meta Box Content
 */
function ucfc_menu_nutrition_metabox($post) {
    wp_nonce_field('ucfc_menu_nutrition_nonce', 'ucfc_menu_nutrition_nonce');
    
    $calories = get_post_meta($post->ID, '_ucfc_calories', true);
    $protein = get_post_meta($post->ID, '_ucfc_protein', true);
    $carbs = get_post_meta($post->ID, '_ucfc_carbs', true);
    $fat = get_post_meta($post->ID, '_ucfc_fat', true);
    $allergens = get_post_meta($post->ID, '_ucfc_allergens', true);
    ?>
    <p>
        <label><strong>Calories:</strong></label><br>
        <input type="number" name="ucfc_calories" value="<?php echo esc_attr($calories); ?>" 
               style="width: 100%;" placeholder="420">
    </p>
    <p>
        <label><strong>Protein (g):</strong></label><br>
        <input type="number" step="0.1" name="ucfc_protein" value="<?php echo esc_attr($protein); ?>" 
               style="width: 100%;" placeholder="25.5">
    </p>
    <p>
        <label><strong>Carbs (g):</strong></label><br>
        <input type="number" step="0.1" name="ucfc_carbs" value="<?php echo esc_attr($carbs); ?>" 
               style="width: 100%;" placeholder="45.2">
    </p>
    <p>
        <label><strong>Fat (g):</strong></label><br>
        <input type="number" step="0.1" name="ucfc_fat" value="<?php echo esc_attr($fat); ?>" 
               style="width: 100%;" placeholder="15.8">
    </p>
    <p>
        <label><strong>Allergens:</strong></label><br>
        <textarea name="ucfc_allergens" rows="3" style="width: 100%;" 
                  placeholder="Contains: Milk, Eggs, Wheat"><?php echo esc_textarea($allergens); ?></textarea>
    </p>
    <?php
}

/**
 * Availability Meta Box Content
 */
function ucfc_menu_availability_metabox($post) {
    wp_nonce_field('ucfc_menu_availability_nonce', 'ucfc_menu_availability_nonce');
    
    $available = get_post_meta($post->ID, '_ucfc_available', true) !== '0';
    $stock_status = get_post_meta($post->ID, '_ucfc_stock_status', true) ?: 'in_stock';
    $available_days = get_post_meta($post->ID, '_ucfc_available_days', true) ?: array();
    ?>
    <p>
        <label>
            <input type="checkbox" name="ucfc_available" value="1" <?php checked($available); ?>>
            <strong>Available for ordering</strong>
        </label>
    </p>
    <p>
        <label><strong>Stock Status:</strong></label><br>
        <select name="ucfc_stock_status" style="width: 100%;">
            <option value="in_stock" <?php selected($stock_status, 'in_stock'); ?>>In Stock</option>
            <option value="low_stock" <?php selected($stock_status, 'low_stock'); ?>>Low Stock</option>
            <option value="out_of_stock" <?php selected($stock_status, 'out_of_stock'); ?>>Out of Stock</option>
            <option value="seasonal" <?php selected($stock_status, 'seasonal'); ?>>Seasonal Only</option>
        </select>
    </p>
    <p>
        <label><strong>Available Days:</strong></label><br>
        <?php
        $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        foreach ($days as $day) {
            $checked = empty($available_days) || in_array($day, $available_days);
            ?>
            <label style="display: inline-block; margin-right: 10px;">
                <input type="checkbox" name="ucfc_available_days[]" value="<?php echo $day; ?>" 
                       <?php checked($checked); ?>>
                <?php echo $day; ?>
            </label>
            <?php
        }
        ?>
    </p>
    <?php
}

/**
 * Save Menu Builder Meta Data
 */
function ucfc_save_menu_builder_meta($post_id) {
    // Pricing
    if (isset($_POST['ucfc_menu_pricing_nonce']) && wp_verify_nonce($_POST['ucfc_menu_pricing_nonce'], 'ucfc_menu_pricing_nonce')) {
        if (isset($_POST['ucfc_price_type'])) {
            update_post_meta($post_id, '_ucfc_price_type', sanitize_text_field($_POST['ucfc_price_type']));
        }
        if (isset($_POST['ucfc_base_price'])) {
            update_post_meta($post_id, '_ucfc_base_price', floatval($_POST['ucfc_base_price']));
        }
        if (isset($_POST['ucfc_sizes'])) {
            $sizes = array();
            foreach ($_POST['ucfc_sizes'] as $size) {
                if (!empty($size['name']) && !empty($size['price'])) {
                    $sizes[] = array(
                        'name' => sanitize_text_field($size['name']),
                        'price' => floatval($size['price'])
                    );
                }
            }
            update_post_meta($post_id, '_ucfc_sizes', $sizes);
        }
    }

    // Modifiers
    if (isset($_POST['ucfc_menu_modifiers_nonce']) && wp_verify_nonce($_POST['ucfc_menu_modifiers_nonce'], 'ucfc_menu_modifiers_nonce')) {
        $modifiers = isset($_POST['ucfc_modifiers']) ? array_map('intval', $_POST['ucfc_modifiers']) : array();
        update_post_meta($post_id, '_ucfc_modifiers', $modifiers);
    }

    // Nutrition
    if (isset($_POST['ucfc_menu_nutrition_nonce']) && wp_verify_nonce($_POST['ucfc_menu_nutrition_nonce'], 'ucfc_menu_nutrition_nonce')) {
        update_post_meta($post_id, '_ucfc_calories', sanitize_text_field($_POST['ucfc_calories'] ?? ''));
        update_post_meta($post_id, '_ucfc_protein', sanitize_text_field($_POST['ucfc_protein'] ?? ''));
        update_post_meta($post_id, '_ucfc_carbs', sanitize_text_field($_POST['ucfc_carbs'] ?? ''));
        update_post_meta($post_id, '_ucfc_fat', sanitize_text_field($_POST['ucfc_fat'] ?? ''));
        update_post_meta($post_id, '_ucfc_allergens', sanitize_textarea_field($_POST['ucfc_allergens'] ?? ''));
    }

    // Availability
    if (isset($_POST['ucfc_menu_availability_nonce']) && wp_verify_nonce($_POST['ucfc_menu_availability_nonce'], 'ucfc_menu_availability_nonce')) {
        update_post_meta($post_id, '_ucfc_available', isset($_POST['ucfc_available']) ? '1' : '0');
        update_post_meta($post_id, '_ucfc_stock_status', sanitize_text_field($_POST['ucfc_stock_status'] ?? 'in_stock'));
        $days = isset($_POST['ucfc_available_days']) ? array_map('sanitize_text_field', $_POST['ucfc_available_days']) : array();
        update_post_meta($post_id, '_ucfc_available_days', $days);
    }
}
add_action('save_post_menu_item', 'ucfc_save_menu_builder_meta');
