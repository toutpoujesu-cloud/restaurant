<?php
/**
 * Admin Settings Panel
 * 
 * Complete settings page for restaurant configuration:
 * - Restaurant Profile (name, logo, colors, story)
 * - Social Media Integration (Instagram, Facebook, etc.)
 * - Email Popup Settings
 * - Delivery Settings
 * - Business Information
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Restaurant Settings Menu
 */
function ucfc_register_settings_menu() {
    add_menu_page(
        __('Restaurant Settings', 'uncle-chans'),
        __('Restaurant', 'uncle-chans'),
        'manage_options',
        'restaurant-settings',
        'ucfc_settings_page_html',
        'dashicons-store',
        3
    );
    
    // Add submenu pages
    add_submenu_page(
        'restaurant-settings',
        __('General Settings', 'uncle-chans'),
        __('General', 'uncle-chans'),
        'manage_options',
        'restaurant-settings',
        'ucfc_settings_page_html'
    );
    
    add_submenu_page(
        'restaurant-settings',
        __('Social Media', 'uncle-chans'),
        __('Social Media', 'uncle-chans'),
        'manage_options',
        'restaurant-social',
        'ucfc_social_settings_page_html'
    );
    
    add_submenu_page(
        'restaurant-settings',
        __('Email Popup', 'uncle-chans'),
        __('Email Popup', 'uncle-chans'),
        'manage_options',
        'restaurant-popup',
        'ucfc_popup_settings_page_html'
    );
    
    add_submenu_page(
        'restaurant-settings',
        __('Delivery Settings', 'uncle-chans'),
        __('Delivery', 'uncle-chans'),
        'manage_options',
        'restaurant-delivery',
        'ucfc_delivery_settings_page_html'
    );
    
    // Kitchen Display System - Direct link to frontend page
    add_submenu_page(
        'restaurant-settings',
        __('Kitchen Display', 'uncle-chans'),
        __('🍳 Kitchen Display', 'uncle-chans'),
        'manage_options',
        home_url('/kitchen-display'),
        '',
        99
    );
}
add_action('admin_menu', 'ucfc_register_settings_menu');

/**
 * Register Settings
 */
function ucfc_register_settings() {
    // General Settings
    register_setting('ucfc_general_settings', 'ucfc_restaurant_name');
    register_setting('ucfc_general_settings', 'ucfc_restaurant_tagline');
    register_setting('ucfc_general_settings', 'ucfc_restaurant_story');
    register_setting('ucfc_general_settings', 'ucfc_primary_color');
    register_setting('ucfc_general_settings', 'ucfc_secondary_color');
    register_setting('ucfc_general_settings', 'ucfc_phone_number');
    register_setting('ucfc_general_settings', 'ucfc_email_address');
    register_setting('ucfc_general_settings', 'ucfc_military_badge_text');
    
    // Social Media Settings
    register_setting('ucfc_social_settings', 'ucfc_instagram_token');
    register_setting('ucfc_social_settings', 'ucfc_instagram_feed_limit');
    register_setting('ucfc_social_settings', 'ucfc_instagram_hashtag');
    register_setting('ucfc_social_settings', 'ucfc_facebook_url');
    register_setting('ucfc_social_settings', 'ucfc_twitter_url');
    register_setting('ucfc_social_settings', 'ucfc_youtube_url');
    register_setting('ucfc_social_settings', 'ucfc_tiktok_url');
    
    // Email Popup Settings
    register_setting('ucfc_popup_settings', 'ucfc_popup_enabled');
    register_setting('ucfc_popup_settings', 'ucfc_popup_delay');
    register_setting('ucfc_popup_settings', 'ucfc_popup_title');
    register_setting('ucfc_popup_settings', 'ucfc_popup_description');
    register_setting('ucfc_popup_settings', 'ucfc_popup_discount_text');
    register_setting('ucfc_popup_settings', 'ucfc_popup_button_text');
    register_setting('ucfc_popup_settings', 'ucfc_popup_image');
    register_setting('ucfc_popup_settings', 'ucfc_exit_intent_enabled');
    
    // Delivery Settings
    register_setting('ucfc_delivery_settings', 'ucfc_delivery_enabled');
    register_setting('ucfc_delivery_settings', 'ucfc_delivery_fee');
    register_setting('ucfc_delivery_settings', 'ucfc_minimum_order');
    register_setting('ucfc_delivery_settings', 'ucfc_free_delivery_threshold');
    register_setting('ucfc_delivery_settings', 'ucfc_estimated_time');
    register_setting('ucfc_delivery_settings', 'ucfc_google_maps_api_key');
}
add_action('admin_init', 'ucfc_register_settings');

/**
 * General Settings Page HTML
 */
function ucfc_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Save message
    if (isset($_GET['settings-updated'])) {
        add_settings_error('ucfc_messages', 'ucfc_message', __('Settings Saved', 'uncle-chans'), 'updated');
    }
    
    settings_errors('ucfc_messages');
    ?>
    
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <form action="options.php" method="post">
            <?php
            settings_fields('ucfc_general_settings');
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ucfc_restaurant_name"><?php _e('Restaurant Name', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_restaurant_name" name="ucfc_restaurant_name" 
                               value="<?php echo esc_attr(get_option('ucfc_restaurant_name', "Uncle Chan's Fried Chicken")); ?>" 
                               class="regular-text" />
                        <p class="description"><?php _e('Your restaurant\'s name as it appears on the website', 'uncle-chans'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_restaurant_tagline"><?php _e('Tagline', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_restaurant_tagline" name="ucfc_restaurant_tagline" 
                               value="<?php echo esc_attr(get_option('ucfc_restaurant_tagline', 'Served with Honor, Made with Love')); ?>" 
                               class="regular-text" />
                        <p class="description"><?php _e('Short description that appears with your logo', 'uncle-chans'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_restaurant_story"><?php _e('Restaurant Story', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <?php
                        $story = get_option('ucfc_restaurant_story', '');
                        wp_editor($story, 'ucfc_restaurant_story', array(
                            'textarea_name' => 'ucfc_restaurant_story',
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                        ));
                        ?>
                        <p class="description"><?php _e('Your restaurant\'s origin story for the "Our Story" section', 'uncle-chans'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_primary_color"><?php _e('Primary Color', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="color" id="ucfc_primary_color" name="ucfc_primary_color" 
                               value="<?php echo esc_attr(get_option('ucfc_primary_color', '#C92A2A')); ?>" />
                        <p class="description"><?php _e('Main brand color used throughout the site', 'uncle-chans'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_secondary_color"><?php _e('Accent Color', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="color" id="ucfc_secondary_color" name="ucfc_secondary_color" 
                               value="<?php echo esc_attr(get_option('ucfc_secondary_color', '#F0B429')); ?>" />
                        <p class="description"><?php _e('Secondary color for accents and highlights', 'uncle-chans'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_phone_number"><?php _e('Phone Number', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="tel" id="ucfc_phone_number" name="ucfc_phone_number" 
                               value="<?php echo esc_attr(get_option('ucfc_phone_number', '(555) 123-4567')); ?>" 
                               class="regular-text" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_email_address"><?php _e('Email Address', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="ucfc_email_address" name="ucfc_email_address" 
                               value="<?php echo esc_attr(get_option('ucfc_email_address', 'hello@unclechans.com')); ?>" 
                               class="regular-text" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_military_badge_text"><?php _e('Military Badge Text', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_military_badge_text" name="ucfc_military_badge_text" 
                               value="<?php echo esc_attr(get_option('ucfc_military_badge_text', 'Veteran Owned')); ?>" 
                               class="regular-text" />
                        <p class="description"><?php _e('Text for the military badge in header (leave empty to hide)', 'uncle-chans'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Save Settings', 'uncle-chans')); ?>
        </form>
    </div>
    
    <style>
        .wrap { max-width: 1200px; }
        .form-table th { width: 200px; }
    </style>
    <?php
}

/**
 * Social Media Settings Page HTML
 */
function ucfc_social_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_GET['settings-updated'])) {
        add_settings_error('ucfc_messages', 'ucfc_message', __('Settings Saved', 'uncle-chans'), 'updated');
    }
    
    settings_errors('ucfc_messages');
    ?>
    
    <div class="wrap">
        <h1><?php _e('Social Media Settings', 'uncle-chans'); ?></h1>
        
        <form action="options.php" method="post">
            <?php settings_fields('ucfc_social_settings'); ?>
            
            <h2><?php _e('Instagram Feed', 'uncle-chans'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ucfc_instagram_token"><?php _e('Instagram Access Token', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_instagram_token" name="ucfc_instagram_token" 
                               value="<?php echo esc_attr(get_option('ucfc_instagram_token')); ?>" 
                               class="large-text" />
                        <p class="description">
                            <?php _e('Get your token from ', 'uncle-chans'); ?>
                            <a href="https://developers.facebook.com/docs/instagram-basic-display-api/" target="_blank">Instagram Basic Display API</a>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_instagram_feed_limit"><?php _e('Number of Posts', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="ucfc_instagram_feed_limit" name="ucfc_instagram_feed_limit" 
                               value="<?php echo esc_attr(get_option('ucfc_instagram_feed_limit', '6')); ?>" 
                               min="1" max="20" />
                        <p class="description"><?php _e('How many Instagram posts to display', 'uncle-chans'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_instagram_hashtag"><?php _e('Featured Hashtag', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_instagram_hashtag" name="ucfc_instagram_hashtag" 
                               value="<?php echo esc_attr(get_option('ucfc_instagram_hashtag', '#UncleChansFriedChicken')); ?>" 
                               class="regular-text" />
                        <p class="description"><?php _e('Hashtag to encourage customer posts', 'uncle-chans'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h2><?php _e('Social Media Links', 'uncle-chans'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ucfc_facebook_url"><?php _e('Facebook URL', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="ucfc_facebook_url" name="ucfc_facebook_url" 
                               value="<?php echo esc_url(get_option('ucfc_facebook_url')); ?>" 
                               class="regular-text" placeholder="https://facebook.com/yourpage" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_twitter_url"><?php _e('Twitter/X URL', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="ucfc_twitter_url" name="ucfc_twitter_url" 
                               value="<?php echo esc_url(get_option('ucfc_twitter_url')); ?>" 
                               class="regular-text" placeholder="https://twitter.com/yourpage" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_youtube_url"><?php _e('YouTube URL', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="ucfc_youtube_url" name="ucfc_youtube_url" 
                               value="<?php echo esc_url(get_option('ucfc_youtube_url')); ?>" 
                               class="regular-text" placeholder="https://youtube.com/yourchannel" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_tiktok_url"><?php _e('TikTok URL', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="ucfc_tiktok_url" name="ucfc_tiktok_url" 
                               value="<?php echo esc_url(get_option('ucfc_tiktok_url')); ?>" 
                               class="regular-text" placeholder="https://tiktok.com/@yourpage" />
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Save Settings', 'uncle-chans')); ?>
        </form>
    </div>
    <?php
}

/**
 * Email Popup Settings Page HTML
 */
function ucfc_popup_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_GET['settings-updated'])) {
        add_settings_error('ucfc_messages', 'ucfc_message', __('Settings Saved', 'uncle-chans'), 'updated');
    }
    
    settings_errors('ucfc_messages');
    ?>
    
    <div class="wrap">
        <h1><?php _e('Email Popup Settings', 'uncle-chans'); ?></h1>
        
        <form action="options.php" method="post">
            <?php settings_fields('ucfc_popup_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ucfc_popup_enabled"><?php _e('Enable Popup', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="ucfc_popup_enabled" name="ucfc_popup_enabled" value="1" 
                                   <?php checked(get_option('ucfc_popup_enabled'), '1'); ?> />
                            <?php _e('Show email signup popup to visitors', 'uncle-chans'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_popup_delay"><?php _e('Popup Delay (seconds)', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="ucfc_popup_delay" name="ucfc_popup_delay" 
                               value="<?php echo esc_attr(get_option('ucfc_popup_delay', '5')); ?>" 
                               min="0" max="60" />
                        <p class="description"><?php _e('How long to wait before showing popup', 'uncle-chans'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_popup_title"><?php _e('Popup Title', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_popup_title" name="ucfc_popup_title" 
                               value="<?php echo esc_attr(get_option('ucfc_popup_title', 'Get 10% Off Your First Order!')); ?>" 
                               class="regular-text" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_popup_description"><?php _e('Description', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <textarea id="ucfc_popup_description" name="ucfc_popup_description" rows="3" class="large-text"><?php 
                            echo esc_textarea(get_option('ucfc_popup_description', 'Join our family and get exclusive deals, special offers, and birthday surprises!')); 
                        ?></textarea>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_popup_discount_text"><?php _e('Discount Badge Text', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_popup_discount_text" name="ucfc_popup_discount_text" 
                               value="<?php echo esc_attr(get_option('ucfc_popup_discount_text', '10% OFF')); ?>" 
                               class="regular-text" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_popup_button_text"><?php _e('Button Text', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_popup_button_text" name="ucfc_popup_button_text" 
                               value="<?php echo esc_attr(get_option('ucfc_popup_button_text', 'Claim My Discount')); ?>" 
                               class="regular-text" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_exit_intent_enabled"><?php _e('Exit-Intent Popup', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="ucfc_exit_intent_enabled" name="ucfc_exit_intent_enabled" value="1" 
                                   <?php checked(get_option('ucfc_exit_intent_enabled'), '1'); ?> />
                            <?php _e('Show popup when user tries to leave the page', 'uncle-chans'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Save Settings', 'uncle-chans')); ?>
        </form>
        
        <div class="notice notice-info">
            <p><strong><?php _e('Pro Tip:', 'uncle-chans'); ?></strong> 
            <?php _e('Email signups will be saved and can be exported for your email marketing campaigns. Integrate with Mailchimp, SendGrid, or export as CSV.', 'uncle-chans'); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Delivery Settings Page HTML
 */
function ucfc_delivery_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_GET['settings-updated'])) {
        add_settings_error('ucfc_messages', 'ucfc_message', __('Settings Saved', 'uncle-chans'), 'updated');
    }
    
    settings_errors('ucfc_messages');
    ?>
    
    <div class="wrap">
        <h1><?php _e('Delivery Settings', 'uncle-chans'); ?></h1>
        
        <form action="options.php" method="post">
            <?php settings_fields('ucfc_delivery_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ucfc_delivery_enabled"><?php _e('Enable Delivery', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="ucfc_delivery_enabled" name="ucfc_delivery_enabled" value="1" 
                                   <?php checked(get_option('ucfc_delivery_enabled', '1'), '1'); ?> />
                            <?php _e('Offer delivery service', 'uncle-chans'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_delivery_fee"><?php _e('Delivery Fee ($)', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="ucfc_delivery_fee" name="ucfc_delivery_fee" 
                               value="<?php echo esc_attr(get_option('ucfc_delivery_fee', '4.99')); ?>" 
                               step="0.01" min="0" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_minimum_order"><?php _e('Minimum Order ($)', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="ucfc_minimum_order" name="ucfc_minimum_order" 
                               value="<?php echo esc_attr(get_option('ucfc_minimum_order', '15.00')); ?>" 
                               step="0.01" min="0" />
                        <p class="description"><?php _e('Minimum order amount for delivery', 'uncle-chans'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_free_delivery_threshold"><?php _e('Free Delivery Over ($)', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="ucfc_free_delivery_threshold" name="ucfc_free_delivery_threshold" 
                               value="<?php echo esc_attr(get_option('ucfc_free_delivery_threshold', '30.00')); ?>" 
                               step="0.01" min="0" />
                        <p class="description"><?php _e('Orders above this amount get free delivery', 'uncle-chans'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_estimated_time"><?php _e('Estimated Delivery Time', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_estimated_time" name="ucfc_estimated_time" 
                               value="<?php echo esc_attr(get_option('ucfc_estimated_time', '30-45 minutes')); ?>" 
                               class="regular-text" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ucfc_google_maps_api_key"><?php _e('Google Maps API Key', 'uncle-chans'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="ucfc_google_maps_api_key" name="ucfc_google_maps_api_key" 
                               value="<?php echo esc_attr(get_option('ucfc_google_maps_api_key')); ?>" 
                               class="large-text" />
                        <p class="description">
                            <?php _e('Get your API key from ', 'uncle-chans'); ?>
                            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Google Cloud Console</a>
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Save Settings', 'uncle-chans')); ?>
        </form>
    </div>
    <?php
}
