<?php
/**
 * Modern Settings Panel
 * 
 * Consolidated settings with modern tabbed interface
 * 
 * @package Uncle_Chans_Chicken
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Settings Panel
 */
function ucfc_register_settings_panel() {
    add_submenu_page(
        'restaurant-hub',
        __('Settings', 'uncle-chans'),
        __('⚙️ Settings', 'uncle-chans'),
        'manage_options',
        'restaurant-settings-panel',
        'ucfc_render_settings_panel'
    );
}
add_action('admin_menu', 'ucfc_register_settings_panel', 10);

/**
 * Register Settings
 */
function ucfc_register_all_settings() {
    // General Settings
    register_setting('ucfc_general_settings', 'restaurant_name');
    register_setting('ucfc_general_settings', 'restaurant_tagline');
    register_setting('ucfc_general_settings', 'restaurant_story');
    register_setting('ucfc_general_settings', 'primary_color');
    register_setting('ucfc_general_settings', 'secondary_color');
    register_setting('ucfc_general_settings', 'restaurant_email');
    register_setting('ucfc_general_settings', 'restaurant_phone');
    register_setting('ucfc_general_settings', 'restaurant_address');
    
    // Social Media Settings
    register_setting('ucfc_social_settings', 'facebook_url');
    register_setting('ucfc_social_settings', 'instagram_url');
    register_setting('ucfc_social_settings', 'twitter_url');
    register_setting('ucfc_social_settings', 'youtube_url');
    
    // Email Popup Settings
    register_setting('ucfc_popup_settings', 'enable_email_popup');
    register_setting('ucfc_popup_settings', 'popup_title');
    register_setting('ucfc_popup_settings', 'popup_description');
    register_setting('ucfc_popup_settings', 'popup_delay');
    
    // Delivery Settings
    register_setting('ucfc_delivery_settings', 'enable_delivery');
    register_setting('ucfc_delivery_settings', 'delivery_fee');
    register_setting('ucfc_delivery_settings', 'min_order_delivery');
    register_setting('ucfc_delivery_settings', 'delivery_radius');
    
    // SMS Settings
    register_setting('ucfc_sms_settings', 'twilio_account_sid');
    register_setting('ucfc_sms_settings', 'twilio_auth_token');
    register_setting('ucfc_sms_settings', 'twilio_phone_number');
    register_setting('ucfc_sms_settings', 'enable_sms_notifications');
    
    // Push Notification Settings
    register_setting('ucfc_push_settings', 'enable_push_notifications');
    register_setting('ucfc_push_settings', 'push_notification_title');
    register_setting('ucfc_push_settings', 'push_notification_icon');
}
add_action('admin_init', 'ucfc_register_all_settings');

/**
 * Render Settings Panel
 */
function ucfc_render_settings_panel() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Handle form submission
    if (isset($_POST['ucfc_settings_submit'])) {
        // Settings are saved automatically by WordPress Settings API
        echo '<div class="updated"><p>Settings saved successfully!</p></div>';
    }
    ?>
    
    <style>
        /* Modern Settings Panel */
        .ucfc-settings-wrap {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px;
            margin-left: -20px;
        }
        
        .ucfc-settings-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .ucfc-settings-header {
            padding: 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .ucfc-settings-title {
            font-size: 36px;
            font-weight: 800;
            color: white;
            margin: 0 0 10px 0;
        }
        
        .ucfc-settings-subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        /* Tabs */
        .ucfc-tabs {
            display: flex;
            background: rgba(0, 0, 0, 0.2);
            padding: 0;
            margin: 0;
            list-style: none;
            overflow-x: auto;
        }
        
        .ucfc-tab {
            flex: 1;
            min-width: 150px;
        }
        
        .ucfc-tab-button {
            width: 100%;
            padding: 20px 25px;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .ucfc-tab-button:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }
        
        .ucfc-tab-button.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-bottom-color: #f5576c;
        }
        
        /* Tab Content */
        .ucfc-tab-content {
            padding: 40px;
            display: none;
        }
        
        .ucfc-tab-content.active {
            display: block;
        }
        
        /* Form Fields */
        .ucfc-form-group {
            margin-bottom: 30px;
        }
        
        .ucfc-form-label {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: white;
            margin-bottom: 10px;
        }
        
        .ucfc-form-input,
        .ucfc-form-textarea,
        .ucfc-form-select {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 10px;
            color: white;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .ucfc-form-input:focus,
        .ucfc-form-textarea:focus,
        .ucfc-form-select:focus {
            outline: none;
            border-color: #f5576c;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.2);
        }
        
        .ucfc-form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .ucfc-form-description {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.65);
            margin-top: 8px;
        }
        
        /* Checkbox */
        .ucfc-checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .ucfc-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        /* Color Picker */
        .ucfc-color-input {
            width: 80px;
            height: 50px;
            padding: 5px;
            cursor: pointer;
        }
        
        /* Save Button */
        .ucfc-save-btn {
            padding: 16px 40px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .ucfc-save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
        }
        
        /* Info Box */
        .ucfc-info-box {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            color: #93c5fd;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        
        .ucfc-info-box-icon {
            font-size: 20px;
        }
    </style>
    
    <div class="ucfc-settings-wrap">
        <div class="ucfc-settings-container">
            <div class="ucfc-settings-header">
                <h1 class="ucfc-settings-title">⚙️ Restaurant Settings</h1>
                <p class="ucfc-settings-subtitle">Configure your restaurant's information and features</p>
            </div>
            
            <ul class="ucfc-tabs">
                <li class="ucfc-tab">
                    <button class="ucfc-tab-button active" data-tab="general">
                        🏪 General
                    </button>
                </li>
                <li class="ucfc-tab">
                    <button class="ucfc-tab-button" data-tab="social">
                        📱 Social Media
                    </button>
                </li>
                <li class="ucfc-tab">
                    <button class="ucfc-tab-button" data-tab="delivery">
                        🚗 Delivery
                    </button>
                </li>
                <li class="ucfc-tab">
                    <button class="ucfc-tab-button" data-tab="sms">
                        💬 SMS
                    </button>
                </li>
                <li class="ucfc-tab">
                    <button class="ucfc-tab-button" data-tab="push">
                        🔔 Push
                    </button>
                </li>
                <li class="ucfc-tab">
                    <button class="ucfc-tab-button" data-tab="popup">
                        ✉️ Email Popup
                    </button>
                </li>
            </ul>
            
            <form method="post" action="">
                <?php wp_nonce_field('ucfc_settings_nonce'); ?>
                
                <!-- General Tab -->
                <div class="ucfc-tab-content active" id="general">
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Restaurant Name</label>
                        <input type="text" name="restaurant_name" class="ucfc-form-input" value="<?php echo esc_attr(get_option('restaurant_name', 'Uncle Chan\'s Fried Chicken')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Tagline</label>
                        <input type="text" name="restaurant_tagline" class="ucfc-form-input" value="<?php echo esc_attr(get_option('restaurant_tagline', 'Crispy, Juicy, Delicious!')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Restaurant Story</label>
                        <textarea name="restaurant_story" class="ucfc-form-textarea"><?php echo esc_textarea(get_option('restaurant_story', '')); ?></textarea>
                        <p class="ucfc-form-description">Tell your restaurant's story</p>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Contact Information</label>
                        <input type="email" name="restaurant_email" class="ucfc-form-input" placeholder="Email" value="<?php echo esc_attr(get_option('restaurant_email', '')); ?>" style="margin-bottom: 15px;">
                        <input type="text" name="restaurant_phone" class="ucfc-form-input" placeholder="Phone" value="<?php echo esc_attr(get_option('restaurant_phone', '')); ?>" style="margin-bottom: 15px;">
                        <textarea name="restaurant_address" class="ucfc-form-textarea" placeholder="Address" style="min-height: 80px;"><?php echo esc_textarea(get_option('restaurant_address', '')); ?></textarea>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Brand Colors</label>
                        <div style="display: flex; gap: 20px;">
                            <div>
                                <label style="display: block; color: rgba(255, 255, 255, 0.8); margin-bottom: 8px; font-size: 13px;">Primary Color</label>
                                <input type="color" name="primary_color" class="ucfc-color-input" value="<?php echo esc_attr(get_option('primary_color', '#d92027')); ?>">
                            </div>
                            <div>
                                <label style="display: block; color: rgba(255, 255, 255, 0.8); margin-bottom: 8px; font-size: 13px;">Secondary Color</label>
                                <input type="color" name="secondary_color" class="ucfc-color-input" value="<?php echo esc_attr(get_option('secondary_color', '#f39c12')); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="ucfc_settings_submit" class="ucfc-save-btn">💾 Save General Settings</button>
                </div>
                
                <!-- Social Media Tab -->
                <div class="ucfc-tab-content" id="social">
                    <div class="ucfc-info-box">
                        <span class="ucfc-info-box-icon">ℹ️</span>
                        <div>Enter your social media profile URLs. Leave blank to hide icons on your website.</div>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Facebook URL</label>
                        <input type="url" name="facebook_url" class="ucfc-form-input" placeholder="https://facebook.com/yourbusiness" value="<?php echo esc_attr(get_option('facebook_url', '')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Instagram URL</label>
                        <input type="url" name="instagram_url" class="ucfc-form-input" placeholder="https://instagram.com/yourbusiness" value="<?php echo esc_attr(get_option('instagram_url', '')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Twitter URL</label>
                        <input type="url" name="twitter_url" class="ucfc-form-input" placeholder="https://twitter.com/yourbusiness" value="<?php echo esc_attr(get_option('twitter_url', '')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">YouTube URL</label>
                        <input type="url" name="youtube_url" class="ucfc-form-input" placeholder="https://youtube.com/yourchannel" value="<?php echo esc_attr(get_option('youtube_url', '')); ?>">
                    </div>
                    
                    <button type="submit" name="ucfc_settings_submit" class="ucfc-save-btn">💾 Save Social Settings</button>
                </div>
                
                <!-- Delivery Tab -->
                <div class="ucfc-tab-content" id="delivery">
                    <div class="ucfc-form-group">
                        <div class="ucfc-checkbox-wrapper">
                            <input type="checkbox" name="enable_delivery" class="ucfc-checkbox" value="1" <?php checked(get_option('enable_delivery'), '1'); ?>>
                            <label class="ucfc-form-label" style="margin: 0;">Enable Delivery Service</label>
                        </div>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Delivery Fee ($)</label>
                        <input type="number" name="delivery_fee" class="ucfc-form-input" step="0.01" min="0" value="<?php echo esc_attr(get_option('delivery_fee', '5.00')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Minimum Order for Delivery ($)</label>
                        <input type="number" name="min_order_delivery" class="ucfc-form-input" step="0.01" min="0" value="<?php echo esc_attr(get_option('min_order_delivery', '20.00')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Delivery Radius (miles)</label>
                        <input type="number" name="delivery_radius" class="ucfc-form-input" step="0.1" min="0" value="<?php echo esc_attr(get_option('delivery_radius', '5')); ?>">
                    </div>
                    
                    <button type="submit" name="ucfc_settings_submit" class="ucfc-save-btn">💾 Save Delivery Settings</button>
                </div>
                
                <!-- SMS Tab -->
                <div class="ucfc-tab-content" id="sms">
                    <div class="ucfc-info-box">
                        <span class="ucfc-info-box-icon">ℹ️</span>
                        <div>Configure Twilio SMS notifications. Get your credentials from <a href="https://www.twilio.com/console" target="_blank" style="color: white; text-decoration: underline;">Twilio Console</a>.</div>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <div class="ucfc-checkbox-wrapper">
                            <input type="checkbox" name="enable_sms_notifications" class="ucfc-checkbox" value="1" <?php checked(get_option('enable_sms_notifications'), '1'); ?>>
                            <label class="ucfc-form-label" style="margin: 0;">Enable SMS Notifications</label>
                        </div>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Twilio Account SID</label>
                        <input type="text" name="twilio_account_sid" class="ucfc-form-input" value="<?php echo esc_attr(get_option('twilio_account_sid', '')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Twilio Auth Token</label>
                        <input type="password" name="twilio_auth_token" class="ucfc-form-input" value="<?php echo esc_attr(get_option('twilio_auth_token', '')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Twilio Phone Number</label>
                        <input type="text" name="twilio_phone_number" class="ucfc-form-input" placeholder="+1234567890" value="<?php echo esc_attr(get_option('twilio_phone_number', '')); ?>">
                        <p class="ucfc-form-description">Format: +1234567890</p>
                    </div>
                    
                    <button type="submit" name="ucfc_settings_submit" class="ucfc-save-btn">💾 Save SMS Settings</button>
                </div>
                
                <!-- Push Tab -->
                <div class="ucfc-tab-content" id="push">
                    <div class="ucfc-info-box">
                        <span class="ucfc-info-box-icon">ℹ️</span>
                        <div>Configure browser push notifications. VAPID keys are generated automatically.</div>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <div class="ucfc-checkbox-wrapper">
                            <input type="checkbox" name="enable_push_notifications" class="ucfc-checkbox" value="1" <?php checked(get_option('enable_push_notifications'), '1'); ?>>
                            <label class="ucfc-form-label" style="margin: 0;">Enable Push Notifications</label>
                        </div>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Default Notification Title</label>
                        <input type="text" name="push_notification_title" class="ucfc-form-input" value="<?php echo esc_attr(get_option('push_notification_title', 'Uncle Chan\'s Fried Chicken')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Notification Icon URL</label>
                        <input type="url" name="push_notification_icon" class="ucfc-form-input" placeholder="https://yoursite.com/icon.png" value="<?php echo esc_attr(get_option('push_notification_icon', '')); ?>">
                    </div>
                    
                    <button type="submit" name="ucfc_settings_submit" class="ucfc-save-btn">💾 Save Push Settings</button>
                </div>
                
                <!-- Email Popup Tab -->
                <div class="ucfc-tab-content" id="popup">
                    <div class="ucfc-form-group">
                        <div class="ucfc-checkbox-wrapper">
                            <input type="checkbox" name="enable_email_popup" class="ucfc-checkbox" value="1" <?php checked(get_option('enable_email_popup'), '1'); ?>>
                            <label class="ucfc-form-label" style="margin: 0;">Enable Email Popup</label>
                        </div>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Popup Title</label>
                        <input type="text" name="popup_title" class="ucfc-form-input" value="<?php echo esc_attr(get_option('popup_title', 'Get 10% Off Your First Order!')); ?>">
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Popup Description</label>
                        <textarea name="popup_description" class="ucfc-form-textarea"><?php echo esc_textarea(get_option('popup_description', 'Subscribe to our newsletter and receive exclusive deals!')); ?></textarea>
                    </div>
                    
                    <div class="ucfc-form-group">
                        <label class="ucfc-form-label">Popup Delay (seconds)</label>
                        <input type="number" name="popup_delay" class="ucfc-form-input" min="0" value="<?php echo esc_attr(get_option('popup_delay', '3')); ?>">
                        <p class="ucfc-form-description">How long to wait before showing the popup</p>
                    </div>
                    
                    <button type="submit" name="ucfc_settings_submit" class="ucfc-save-btn">💾 Save Popup Settings</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Tab switching
        $('.ucfc-tab-button').on('click', function() {
            const tab = $(this).data('tab');
            
            // Update buttons
            $('.ucfc-tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Update content
            $('.ucfc-tab-content').removeClass('active');
            $('#' + tab).addClass('active');
        });
    });
    </script>
    
    <?php
}
