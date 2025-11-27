<?php
/**
 * Uncle Chan's AI Theme Functions
 * Enhanced with full AI Agent Dashboard and Restaurant Management
 */

function uncle_chans_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'uncle_chans_setup');

// Add custom cron schedule for SMS processing
function ucfc_add_cron_schedules($schedules) {
    $schedules['five_minutes'] = array(
        'interval' => 300, // 5 minutes in seconds
        'display' => __('Every 5 Minutes')
    );
    return $schedules;
}
add_filter('cron_schedules', 'ucfc_add_cron_schedules');

function uncle_chans_scripts() {
    // Styles
    wp_enqueue_style('uncle-chans-style', get_stylesheet_uri());
    wp_enqueue_style('uncle-chans-custom', get_template_directory_uri() . '/assets/css/custom.css', array(), '1.0');
    wp_enqueue_style('uncle-chans-popup', get_template_directory_uri() . '/assets/css/popup.css', array(), '1.0');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    
    // Scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('uncle-chans-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0', true);
    wp_enqueue_script('uncle-chans-popup', get_template_directory_uri() . '/assets/js/email-popup.js', array('jquery'), '1.0', true);
    wp_enqueue_script('uncle-chans-chat', get_template_directory_uri() . '/assets/js/ai-chat.js', array('jquery'), '1.0', true);
    
    // Localize scripts with AJAX URL and settings
    wp_localize_script('uncle-chans-main', 'uncleChansConfig', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('uncle_chans_nonce')
    ));
    
    wp_localize_script('uncle-chans-chat', 'uncleChansAIConfig', array(
        'apiKey' => get_option('uncle_chans_openai_key', ''),
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'uncle_chans_scripts');

// Include Restaurant System Files
require_once get_template_directory() . '/inc/custom-post-types.php';
require_once get_template_directory() . '/inc/meta-boxes.php';

// NEW: Include AI Settings FIRST before modern-dashboard
require_once get_template_directory() . '/inc/ai-settings.php';
require_once get_template_directory() . '/inc/ai-agent-framework.php';
require_once get_template_directory() . '/inc/ai-frontend-widget.php';

require_once get_template_directory() . '/inc/modern-dashboard.php';  // Modern unified dashboard (uses AI Settings functions)
require_once get_template_directory() . '/inc/settings-panel.php';    // Modern settings panel
require_once get_template_directory() . '/inc/menu-builder.php';
require_once get_template_directory() . '/inc/menu-dashboard.php';

// Include Cart System Files
require_once get_template_directory() . '/inc/cart-system.php';
require_once get_template_directory() . '/inc/cart-ajax-handlers.php';
require_once get_template_directory() . '/inc/orders-dashboard.php';
require_once get_template_directory() . '/inc/checkout-process.php';

// Include Payment System
require_once get_template_directory() . '/inc/payment-gateway.php';

// Include Enhanced Email Templates
require_once get_template_directory() . '/inc/email-templates.php';

// Include Order Tracking
require_once get_template_directory() . '/inc/order-tracking-ajax.php';

// Include SMS Notifications
require_once get_template_directory() . '/inc/sms-notifications.php';

// Include Push Notifications
require_once get_template_directory() . '/inc/push-notifications.php';

// Include QR Code System
require_once get_template_directory() . '/inc/qr-code-system.php';

// Include AI System Files
require_once get_template_directory() . '/inc/ai-assistant-settings.php';
require_once get_template_directory() . '/inc/ai-conversation-logger.php';
require_once get_template_directory() . '/inc/ai-function-validator.php';
require_once get_template_directory() . '/inc/ai-analytics-dashboard.php';
require_once get_template_directory() . '/inc/ai-agent-dashboard.php';
require_once get_template_directory() . '/inc/ai-agent-backend.php';
require_once get_template_directory() . '/inc/ai-chat-engine.php';
require_once get_template_directory() . '/inc/ai-cache.php';
require_once get_template_directory() . '/inc/ai-rate-limiter.php';
require_once get_template_directory() . '/inc/ai-system-check.php';

// Access via: Restaurant → AI Settings in WordPress Admin
