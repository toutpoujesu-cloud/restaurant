<?php
/**
 * Meta Boxes for Custom Post Types
 * 
 * Add custom fields to admin panels for:
 * - Menu Items (price, calories, spice level, sides, extras)
 * - Locations (address, phone, hours, map coordinates)
 * - Special Offers (discount, expiry date, terms)
 * - Reviews (rating, customer name, verified status)
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Menu Item Meta Boxes
 */
function ucfc_add_menu_item_meta_boxes() {
    add_meta_box(
        'menu_item_details',
        __('Menu Item Details', 'uncle-chans'),
        'ucfc_menu_item_details_callback',
        'menu_item',
        'normal',
        'high'
    );
    
    add_meta_box(
        'menu_item_extras',
        __('Sides & Extras', 'uncle-chans'),
        'ucfc_menu_item_extras_callback',
        'menu_item',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ucfc_add_menu_item_meta_boxes');

/**
 * Menu Item Details Meta Box Callback
 */
function ucfc_menu_item_details_callback($post) {
    wp_nonce_field('ucfc_menu_item_details', 'ucfc_menu_item_details_nonce');
    
    $price = get_post_meta($post->ID, '_menu_item_price', true);
    $calories = get_post_meta($post->ID, '_menu_item_calories', true);
    $spice_level = get_post_meta($post->ID, '_menu_item_spice_level', true);
    $is_popular = get_post_meta($post->ID, '_menu_item_is_popular', true);
    $is_new = get_post_meta($post->ID, '_menu_item_is_new', true);
    $is_featured = get_post_meta($post->ID, '_menu_item_is_featured', true);
    $allergens = get_post_meta($post->ID, '_menu_item_allergens', true);
    $ingredients = get_post_meta($post->ID, '_menu_item_ingredients', true);
    ?>
    
    <style>
        .ucfc-meta-box { padding: 15px; }
        .ucfc-meta-row { margin-bottom: 20px; }
        .ucfc-meta-row label { display: block; font-weight: 600; margin-bottom: 8px; }
        .ucfc-meta-row input[type="text"],
        .ucfc-meta-row input[type="number"],
        .ucfc-meta-row select,
        .ucfc-meta-row textarea { width: 100%; padding: 8px; }
        .ucfc-meta-row textarea { height: 100px; }
        .ucfc-checkbox-group { display: flex; gap: 20px; margin-top: 8px; }
        .ucfc-checkbox-group label { display: flex; align-items: center; gap: 8px; font-weight: normal; }
        .ucfc-help-text { color: #666; font-size: 13px; margin-top: 5px; }
    </style>
    
    <div class="ucfc-meta-box">
        <div class="ucfc-meta-row">
            <label for="menu_item_price"><?php _e('Price ($)', 'uncle-chans'); ?></label>
            <input type="number" id="menu_item_price" name="menu_item_price" value="<?php echo esc_attr($price); ?>" step="0.01" min="0" />
            <p class="ucfc-help-text"><?php _e('Enter the price in dollars (e.g., 12.99)', 'uncle-chans'); ?></p>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="menu_item_calories"><?php _e('Calories', 'uncle-chans'); ?></label>
            <input type="number" id="menu_item_calories" name="menu_item_calories" value="<?php echo esc_attr($calories); ?>" min="0" />
            <p class="ucfc-help-text"><?php _e('Approximate calories per serving', 'uncle-chans'); ?></p>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="menu_item_spice_level"><?php _e('Spice Level', 'uncle-chans'); ?></label>
            <select id="menu_item_spice_level" name="menu_item_spice_level">
                <option value=""><?php _e('None', 'uncle-chans'); ?></option>
                <option value="mild" <?php selected($spice_level, 'mild'); ?>><?php _e('🌶️ Mild', 'uncle-chans'); ?></option>
                <option value="medium" <?php selected($spice_level, 'medium'); ?>><?php _e('🌶️🌶️ Medium', 'uncle-chans'); ?></option>
                <option value="hot" <?php selected($spice_level, 'hot'); ?>><?php _e('🌶️🌶️🌶️ Hot', 'uncle-chans'); ?></option>
                <option value="extra-hot" <?php selected($spice_level, 'extra-hot'); ?>><?php _e('🌶️🌶️🌶️🌶️ Extra Hot', 'uncle-chans'); ?></option>
            </select>
        </div>
        
        <div class="ucfc-meta-row">
            <label><?php _e('Badges & Tags', 'uncle-chans'); ?></label>
            <div class="ucfc-checkbox-group">
                <label>
                    <input type="checkbox" name="menu_item_is_popular" value="1" <?php checked($is_popular, '1'); ?> />
                    <?php _e('Popular Item', 'uncle-chans'); ?>
                </label>
                <label>
                    <input type="checkbox" name="menu_item_is_new" value="1" <?php checked($is_new, '1'); ?> />
                    <?php _e('New Item', 'uncle-chans'); ?>
                </label>
                <label>
                    <input type="checkbox" name="menu_item_is_featured" value="1" <?php checked($is_featured, '1'); ?> />
                    <?php _e('Featured', 'uncle-chans'); ?>
                </label>
            </div>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="menu_item_allergens"><?php _e('Allergens', 'uncle-chans'); ?></label>
            <input type="text" id="menu_item_allergens" name="menu_item_allergens" value="<?php echo esc_attr($allergens); ?>" />
            <p class="ucfc-help-text"><?php _e('Separate with commas (e.g., Gluten, Dairy, Nuts)', 'uncle-chans'); ?></p>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="menu_item_ingredients"><?php _e('Key Ingredients', 'uncle-chans'); ?></label>
            <textarea id="menu_item_ingredients" name="menu_item_ingredients"><?php echo esc_textarea($ingredients); ?></textarea>
            <p class="ucfc-help-text"><?php _e('List the main ingredients that make this item special', 'uncle-chans'); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Menu Item Extras Meta Box Callback
 */
function ucfc_menu_item_extras_callback($post) {
    $sides = get_post_meta($post->ID, '_menu_item_sides', true);
    $extras = get_post_meta($post->ID, '_menu_item_extras', true);
    
    // Decode JSON if exists
    $sides_data = $sides ? json_decode($sides, true) : array();
    $extras_data = $extras ? json_decode($extras, true) : array();
    ?>
    
    <div class="ucfc-meta-box">
        <div class="ucfc-meta-row">
            <label><?php _e('Available Sides', 'uncle-chans'); ?></label>
            <p class="ucfc-help-text"><?php _e('Add sides that customers can choose with this item', 'uncle-chans'); ?></p>
            <div id="sides-container">
                <?php
                if (!empty($sides_data)) {
                    foreach ($sides_data as $index => $side) {
                        ?>
                        <div class="side-item" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                            <input type="text" name="sides[<?php echo $index; ?>][name]" value="<?php echo esc_attr($side['name'] ?? ''); ?>" placeholder="Side name (e.g., Coleslaw)" style="width: 48%; margin-right: 2%;" />
                            <input type="number" name="sides[<?php echo $index; ?>][price]" value="<?php echo esc_attr($side['price'] ?? '0'); ?>" placeholder="Price" step="0.01" style="width: 30%; margin-right: 2%;" />
                            <button type="button" class="button remove-side" style="width: 18%;">Remove</button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <button type="button" id="add-side" class="button button-secondary"><?php _e('+ Add Side', 'uncle-chans'); ?></button>
        </div>
        
        <div class="ucfc-meta-row" style="margin-top: 30px;">
            <label><?php _e('Available Extras', 'uncle-chans'); ?></label>
            <p class="ucfc-help-text"><?php _e('Add extra toppings or add-ons customers can purchase', 'uncle-chans'); ?></p>
            <div id="extras-container">
                <?php
                if (!empty($extras_data)) {
                    foreach ($extras_data as $index => $extra) {
                        ?>
                        <div class="extra-item" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                            <input type="text" name="extras[<?php echo $index; ?>][name]" value="<?php echo esc_attr($extra['name'] ?? ''); ?>" placeholder="Extra name (e.g., Extra Cheese)" style="width: 48%; margin-right: 2%;" />
                            <input type="number" name="extras[<?php echo $index; ?>][price]" value="<?php echo esc_attr($extra['price'] ?? '0'); ?>" placeholder="Price" step="0.01" style="width: 30%; margin-right: 2%;" />
                            <button type="button" class="button remove-extra" style="width: 18%;">Remove</button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <button type="button" id="add-extra" class="button button-secondary"><?php _e('+ Add Extra', 'uncle-chans'); ?></button>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        let sideIndex = <?php echo count($sides_data); ?>;
        let extraIndex = <?php echo count($extras_data); ?>;
        
        // Add Side
        $('#add-side').on('click', function() {
            const html = `
                <div class="side-item" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                    <input type="text" name="sides[${sideIndex}][name]" placeholder="Side name (e.g., Coleslaw)" style="width: 48%; margin-right: 2%;" />
                    <input type="number" name="sides[${sideIndex}][price]" placeholder="Price" step="0.01" value="0" style="width: 30%; margin-right: 2%;" />
                    <button type="button" class="button remove-side" style="width: 18%;">Remove</button>
                </div>
            `;
            $('#sides-container').append(html);
            sideIndex++;
        });
        
        // Remove Side
        $(document).on('click', '.remove-side', function() {
            $(this).closest('.side-item').remove();
        });
        
        // Add Extra
        $('#add-extra').on('click', function() {
            const html = `
                <div class="extra-item" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                    <input type="text" name="extras[${extraIndex}][name]" placeholder="Extra name (e.g., Extra Cheese)" style="width: 48%; margin-right: 2%;" />
                    <input type="number" name="extras[${extraIndex}][price]" placeholder="Price" step="0.01" value="0" style="width: 30%; margin-right: 2%;" />
                    <button type="button" class="button remove-extra" style="width: 18%;">Remove</button>
                </div>
            `;
            $('#extras-container').append(html);
            extraIndex++;
        });
        
        // Remove Extra
        $(document).on('click', '.remove-extra', function() {
            $(this).closest('.extra-item').remove();
        });
    });
    </script>
    <?php
}

/**
 * Save Menu Item Meta Data
 */
function ucfc_save_menu_item_meta($post_id) {
    // Check nonce
    if (!isset($_POST['ucfc_menu_item_details_nonce']) || 
        !wp_verify_nonce($_POST['ucfc_menu_item_details_nonce'], 'ucfc_menu_item_details')) {
        return;
    }
    
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save basic fields
    if (isset($_POST['menu_item_price'])) {
        update_post_meta($post_id, '_menu_item_price', sanitize_text_field($_POST['menu_item_price']));
    }
    
    if (isset($_POST['menu_item_calories'])) {
        update_post_meta($post_id, '_menu_item_calories', intval($_POST['menu_item_calories']));
    }
    
    if (isset($_POST['menu_item_spice_level'])) {
        update_post_meta($post_id, '_menu_item_spice_level', sanitize_text_field($_POST['menu_item_spice_level']));
    }
    
    if (isset($_POST['menu_item_allergens'])) {
        update_post_meta($post_id, '_menu_item_allergens', sanitize_text_field($_POST['menu_item_allergens']));
    }
    
    if (isset($_POST['menu_item_ingredients'])) {
        update_post_meta($post_id, '_menu_item_ingredients', sanitize_textarea_field($_POST['menu_item_ingredients']));
    }
    
    // Save checkboxes
    update_post_meta($post_id, '_menu_item_is_popular', isset($_POST['menu_item_is_popular']) ? '1' : '0');
    update_post_meta($post_id, '_menu_item_is_new', isset($_POST['menu_item_is_new']) ? '1' : '0');
    update_post_meta($post_id, '_menu_item_is_featured', isset($_POST['menu_item_is_featured']) ? '1' : '0');
    
    // Save sides
    if (isset($_POST['sides'])) {
        $sides = array();
        foreach ($_POST['sides'] as $side) {
            if (!empty($side['name'])) {
                $sides[] = array(
                    'name' => sanitize_text_field($side['name']),
                    'price' => floatval($side['price'])
                );
            }
        }
        update_post_meta($post_id, '_menu_item_sides', wp_json_encode($sides));
    } else {
        delete_post_meta($post_id, '_menu_item_sides');
    }
    
    // Save extras
    if (isset($_POST['extras'])) {
        $extras = array();
        foreach ($_POST['extras'] as $extra) {
            if (!empty($extra['name'])) {
                $extras[] = array(
                    'name' => sanitize_text_field($extra['name']),
                    'price' => floatval($extra['price'])
                );
            }
        }
        update_post_meta($post_id, '_menu_item_extras', wp_json_encode($extras));
    } else {
        delete_post_meta($post_id, '_menu_item_extras');
    }
}
add_action('save_post_menu_item', 'ucfc_save_menu_item_meta');

/**
 * Add Location Meta Boxes
 */
function ucfc_add_location_meta_boxes() {
    add_meta_box(
        'location_details',
        __('Location Details', 'uncle-chans'),
        'ucfc_location_details_callback',
        'location',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ucfc_add_location_meta_boxes');

/**
 * Location Details Meta Box Callback
 */
function ucfc_location_details_callback($post) {
    wp_nonce_field('ucfc_location_details', 'ucfc_location_details_nonce');
    
    $address = get_post_meta($post->ID, '_location_address', true);
    $phone = get_post_meta($post->ID, '_location_phone', true);
    $email = get_post_meta($post->ID, '_location_email', true);
    $latitude = get_post_meta($post->ID, '_location_latitude', true);
    $longitude = get_post_meta($post->ID, '_location_longitude', true);
    $hours = get_post_meta($post->ID, '_location_hours', true);
    $delivery_radius = get_post_meta($post->ID, '_location_delivery_radius', true);
    ?>
    
    <div class="ucfc-meta-box">
        <div class="ucfc-meta-row">
            <label for="location_address"><?php _e('Street Address', 'uncle-chans'); ?></label>
            <input type="text" id="location_address" name="location_address" value="<?php echo esc_attr($address); ?>" />
        </div>
        
        <div class="ucfc-meta-row">
            <label for="location_phone"><?php _e('Phone Number', 'uncle-chans'); ?></label>
            <input type="text" id="location_phone" name="location_phone" value="<?php echo esc_attr($phone); ?>" />
        </div>
        
        <div class="ucfc-meta-row">
            <label for="location_email"><?php _e('Email Address', 'uncle-chans'); ?></label>
            <input type="email" id="location_email" name="location_email" value="<?php echo esc_attr($email); ?>" />
        </div>
        
        <div class="ucfc-meta-row">
            <label for="location_latitude"><?php _e('Latitude', 'uncle-chans'); ?></label>
            <input type="text" id="location_latitude" name="location_latitude" value="<?php echo esc_attr($latitude); ?>" />
            <p class="ucfc-help-text"><?php _e('For Google Maps integration (e.g., 40.7128)', 'uncle-chans'); ?></p>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="location_longitude"><?php _e('Longitude', 'uncle-chans'); ?></label>
            <input type="text" id="location_longitude" name="location_longitude" value="<?php echo esc_attr($longitude); ?>" />
            <p class="ucfc-help-text"><?php _e('For Google Maps integration (e.g., -74.0060)', 'uncle-chans'); ?></p>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="location_delivery_radius"><?php _e('Delivery Radius (miles)', 'uncle-chans'); ?></label>
            <input type="number" id="location_delivery_radius" name="location_delivery_radius" value="<?php echo esc_attr($delivery_radius); ?>" step="0.1" min="0" />
        </div>
        
        <div class="ucfc-meta-row">
            <label for="location_hours"><?php _e('Business Hours', 'uncle-chans'); ?></label>
            <textarea id="location_hours" name="location_hours"><?php echo esc_textarea($hours); ?></textarea>
            <p class="ucfc-help-text"><?php _e('Enter hours in format: Mon-Fri: 11am-10pm, Sat-Sun: 10am-11pm', 'uncle-chans'); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Save Location Meta Data
 */
function ucfc_save_location_meta($post_id) {
    if (!isset($_POST['ucfc_location_details_nonce']) || 
        !wp_verify_nonce($_POST['ucfc_location_details_nonce'], 'ucfc_location_details')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $fields = array('address', 'phone', 'email', 'latitude', 'longitude', 'hours', 'delivery_radius');
    
    foreach ($fields as $field) {
        if (isset($_POST['location_' . $field])) {
            update_post_meta($post_id, '_location_' . $field, sanitize_text_field($_POST['location_' . $field]));
        }
    }
}
add_action('save_post_location', 'ucfc_save_location_meta');

/**
 * Add Special Offer Meta Boxes
 */
function ucfc_add_offer_meta_boxes() {
    add_meta_box(
        'offer_details',
        __('Offer Details', 'uncle-chans'),
        'ucfc_offer_details_callback',
        'special_offer',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ucfc_add_offer_meta_boxes');

/**
 * Special Offer Details Meta Box Callback
 */
function ucfc_offer_details_callback($post) {
    wp_nonce_field('ucfc_offer_details', 'ucfc_offer_details_nonce');
    
    $discount_amount = get_post_meta($post->ID, '_offer_discount_amount', true);
    $discount_type = get_post_meta($post->ID, '_offer_discount_type', true);
    $expiry_date = get_post_meta($post->ID, '_offer_expiry_date', true);
    $offer_code = get_post_meta($post->ID, '_offer_code', true);
    $is_featured = get_post_meta($post->ID, '_offer_is_featured', true);
    $terms = get_post_meta($post->ID, '_offer_terms', true);
    ?>
    
    <div class="ucfc-meta-box">
        <div class="ucfc-meta-row">
            <label for="offer_discount_type"><?php _e('Discount Type', 'uncle-chans'); ?></label>
            <select id="offer_discount_type" name="offer_discount_type">
                <option value="percentage" <?php selected($discount_type, 'percentage'); ?>><?php _e('Percentage (%)', 'uncle-chans'); ?></option>
                <option value="fixed" <?php selected($discount_type, 'fixed'); ?>><?php _e('Fixed Amount ($)', 'uncle-chans'); ?></option>
            </select>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="offer_discount_amount"><?php _e('Discount Amount', 'uncle-chans'); ?></label>
            <input type="number" id="offer_discount_amount" name="offer_discount_amount" value="<?php echo esc_attr($discount_amount); ?>" step="0.01" min="0" />
            <p class="ucfc-help-text"><?php _e('Enter 10 for 10% or $10', 'uncle-chans'); ?></p>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="offer_code"><?php _e('Promo Code', 'uncle-chans'); ?></label>
            <input type="text" id="offer_code" name="offer_code" value="<?php echo esc_attr($offer_code); ?>" style="text-transform: uppercase;" />
            <p class="ucfc-help-text"><?php _e('e.g., WELCOME10, FAMILY25', 'uncle-chans'); ?></p>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="offer_expiry_date"><?php _e('Expiry Date', 'uncle-chans'); ?></label>
            <input type="date" id="offer_expiry_date" name="offer_expiry_date" value="<?php echo esc_attr($expiry_date); ?>" />
        </div>
        
        <div class="ucfc-meta-row">
            <label>
                <input type="checkbox" name="offer_is_featured" value="1" <?php checked($is_featured, '1'); ?> />
                <?php _e('Mark as Featured Offer (appears first)', 'uncle-chans'); ?>
            </label>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="offer_terms"><?php _e('Terms & Conditions', 'uncle-chans'); ?></label>
            <textarea id="offer_terms" name="offer_terms" rows="4"><?php echo esc_textarea($terms); ?></textarea>
            <p class="ucfc-help-text"><?php _e('Any restrictions or special conditions', 'uncle-chans'); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Save Special Offer Meta Data
 */
function ucfc_save_offer_meta($post_id) {
    if (!isset($_POST['ucfc_offer_details_nonce']) || 
        !wp_verify_nonce($_POST['ucfc_offer_details_nonce'], 'ucfc_offer_details')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $fields = array('discount_amount', 'discount_type', 'expiry_date', 'offer_code', 'terms');
    
    foreach ($fields as $field) {
        if (isset($_POST['offer_' . $field])) {
            update_post_meta($post_id, '_offer_' . $field, sanitize_text_field($_POST['offer_' . $field]));
        }
    }
    
    update_post_meta($post_id, '_offer_is_featured', isset($_POST['offer_is_featured']) ? '1' : '0');
}
add_action('save_post_special_offer', 'ucfc_save_offer_meta');

/**
 * Add Customer Review Meta Boxes
 */
function ucfc_add_review_meta_boxes() {
    add_meta_box(
        'review_details',
        __('Review Details', 'uncle-chans'),
        'ucfc_review_details_callback',
        'customer_review',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ucfc_add_review_meta_boxes');

/**
 * Customer Review Details Meta Box Callback
 */
function ucfc_review_details_callback($post) {
    wp_nonce_field('ucfc_review_details', 'ucfc_review_details_nonce');
    
    $rating = get_post_meta($post->ID, '_review_rating', true);
    $customer_name = get_post_meta($post->ID, '_review_customer_name', true);
    $customer_location = get_post_meta($post->ID, '_review_customer_location', true);
    $is_verified = get_post_meta($post->ID, '_review_is_verified', true);
    $order_items = get_post_meta($post->ID, '_review_order_items', true);
    ?>
    
    <div class="ucfc-meta-box">
        <div class="ucfc-meta-row">
            <label for="review_customer_name"><?php _e('Customer Name', 'uncle-chans'); ?></label>
            <input type="text" id="review_customer_name" name="review_customer_name" value="<?php echo esc_attr($customer_name); ?>" />
        </div>
        
        <div class="ucfc-meta-row">
            <label for="review_customer_location"><?php _e('Customer Location', 'uncle-chans'); ?></label>
            <input type="text" id="review_customer_location" name="review_customer_location" value="<?php echo esc_attr($customer_location); ?>" placeholder="e.g., New York, NY" />
        </div>
        
        <div class="ucfc-meta-row">
            <label for="review_rating"><?php _e('Rating', 'uncle-chans'); ?></label>
            <select id="review_rating" name="review_rating">
                <option value="5" <?php selected($rating, '5'); ?>>⭐⭐⭐⭐⭐ 5 Stars</option>
                <option value="4" <?php selected($rating, '4'); ?>>⭐⭐⭐⭐ 4 Stars</option>
                <option value="3" <?php selected($rating, '3'); ?>>⭐⭐⭐ 3 Stars</option>
                <option value="2" <?php selected($rating, '2'); ?>>⭐⭐ 2 Stars</option>
                <option value="1" <?php selected($rating, '1'); ?>>⭐ 1 Star</option>
            </select>
        </div>
        
        <div class="ucfc-meta-row">
            <label for="review_order_items"><?php _e('Items Ordered', 'uncle-chans'); ?></label>
            <input type="text" id="review_order_items" name="review_order_items" value="<?php echo esc_attr($order_items); ?>" placeholder="e.g., Spicy Chicken Bucket, Coleslaw" />
        </div>
        
        <div class="ucfc-meta-row">
            <label>
                <input type="checkbox" name="review_is_verified" value="1" <?php checked($is_verified, '1'); ?> />
                <?php _e('Verified Purchase (show badge)', 'uncle-chans'); ?>
            </label>
        </div>
    </div>
    
    <style>
        .ucfc-meta-box select#review_rating { font-size: 16px; }
    </style>
    <?php
}

/**
 * Save Customer Review Meta Data
 */
function ucfc_save_review_meta($post_id) {
    if (!isset($_POST['ucfc_review_details_nonce']) || 
        !wp_verify_nonce($_POST['ucfc_review_details_nonce'], 'ucfc_review_details')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $fields = array('rating', 'customer_name', 'customer_location', 'order_items');
    
    foreach ($fields as $field) {
        if (isset($_POST['review_' . $field])) {
            update_post_meta($post_id, '_review_' . $field, sanitize_text_field($_POST['review_' . $field]));
        }
    }
    
    update_post_meta($post_id, '_review_is_verified', isset($_POST['review_is_verified']) ? '1' : '0');
}
add_action('save_post_customer_review', 'ucfc_save_review_meta');
