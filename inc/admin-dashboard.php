<?php
/**
 * Admin Dashboard Widget
 * 
 * Shows restaurant system overview and quick stats
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Dashboard Widget
 */
function ucfc_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'ucfc_restaurant_overview',
        '🍗 Restaurant System Overview',
        'ucfc_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'ucfc_add_dashboard_widget');

/**
 * Dashboard Widget Content
 */
function ucfc_dashboard_widget_content() {
    // Get counts
    $menu_items = wp_count_posts('menu_item');
    $locations = wp_count_posts('location');
    $offers = wp_count_posts('special_offer');
    $reviews = wp_count_posts('customer_review');
    $subscribers = wp_count_posts('email_subscriber');
    
    // Get settings status
    $instagram_connected = !empty(get_option('ucfc_instagram_token'));
    $popup_enabled = get_option('ucfc_popup_enabled', '0') === '1';
    $delivery_enabled = get_option('ucfc_delivery_enabled', '1') === '1';
    
    ?>
    <div class="ucfc-dashboard-widget">
        <style>
            .ucfc-dashboard-widget { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
            .ucfc-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px; }
            .ucfc-stat-card { background: linear-gradient(135deg, #f7f7f7, #e9e9e9); padding: 20px; border-radius: 10px; text-align: center; border-left: 4px solid #C92A2A; }
            .ucfc-stat-number { font-size: 36px; font-weight: 700; color: #C92A2A; margin: 0; }
            .ucfc-stat-label { font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }
            .ucfc-features-list { list-style: none; padding: 0; margin: 20px 0; }
            .ucfc-features-list li { padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
            .ucfc-features-list li:last-child { border-bottom: none; }
            .ucfc-status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
            .ucfc-status-badge.active { background: #4CAF50; color: white; }
            .ucfc-status-badge.inactive { background: #f44336; color: white; }
            .ucfc-quick-actions { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
            .ucfc-quick-action-btn { flex: 1; min-width: 150px; padding: 12px 20px; background: #C92A2A; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: 600; transition: all 0.3s ease; display: inline-block; }
            .ucfc-quick-action-btn:hover { background: #A02020; transform: translateY(-2px); color: white; }
            .ucfc-quick-action-btn.secondary { background: #F0B429; color: #1a1a1a; }
            .ucfc-quick-action-btn.secondary:hover { background: #D4A027; color: #1a1a1a; }
        </style>
        
        <div class="ucfc-stats-grid">
            <div class="ucfc-stat-card">
                <div class="ucfc-stat-number"><?php echo $menu_items->publish; ?></div>
                <div class="ucfc-stat-label">Menu Items</div>
            </div>
            <div class="ucfc-stat-card">
                <div class="ucfc-stat-number"><?php echo $offers->publish; ?></div>
                <div class="ucfc-stat-label">Active Offers</div>
            </div>
            <div class="ucfc-stat-card">
                <div class="ucfc-stat-number"><?php echo $reviews->publish; ?></div>
                <div class="ucfc-stat-label">Reviews</div>
            </div>
            <div class="ucfc-stat-card">
                <div class="ucfc-stat-number"><?php echo $subscribers ? $subscribers->publish : 0; ?></div>
                <div class="ucfc-stat-label">Subscribers</div>
            </div>
        </div>
        
        <h3 style="margin: 25px 0 15px; font-size: 16px; color: #333;">System Features</h3>
        <ul class="ucfc-features-list">
            <li>
                <span>🍗 Menu Management</span>
                <span class="ucfc-status-badge active">Active</span>
            </li>
            <li>
                <span>📸 Instagram Feed</span>
                <span class="ucfc-status-badge <?php echo $instagram_connected ? 'active' : 'inactive'; ?>">
                    <?php echo $instagram_connected ? 'Connected' : 'Not Connected'; ?>
                </span>
            </li>
            <li>
                <span>📧 Email Popup</span>
                <span class="ucfc-status-badge <?php echo $popup_enabled ? 'active' : 'inactive'; ?>">
                    <?php echo $popup_enabled ? 'Enabled' : 'Disabled'; ?>
                </span>
            </li>
            <li>
                <span>🚚 Delivery System</span>
                <span class="ucfc-status-badge <?php echo $delivery_enabled ? 'active' : 'inactive'; ?>">
                    <?php echo $delivery_enabled ? 'Enabled' : 'Disabled'; ?>
                </span>
            </li>
            <li>
                <span>🤖 AI Chat Assistant</span>
                <span class="ucfc-status-badge active">Active</span>
            </li>
            <li>
                <span>⭐ Review System</span>
                <span class="ucfc-status-badge active">Active</span>
            </li>
        </ul>
        
        <div class="ucfc-quick-actions">
            <a href="<?php echo admin_url('post-new.php?post_type=menu_item'); ?>" class="ucfc-quick-action-btn">
                + Add Menu Item
            </a>
            <a href="<?php echo admin_url('post-new.php?post_type=special_offer'); ?>" class="ucfc-quick-action-btn secondary">
                + Create Offer
            </a>
            <a href="<?php echo admin_url('admin.php?page=restaurant-settings'); ?>" class="ucfc-quick-action-btn">
                ⚙️ Settings
            </a>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #F0B429; border-radius: 5px;">
            <strong>💡 Pro Tip:</strong> Keep your menu updated, run seasonal offers, and respond to reviews to keep customers engaged!
        </div>
    </div>
    <?php
}

/**
 * Add admin notices for missing configurations
 */
function ucfc_admin_notices() {
    $screen = get_current_screen();
    
    // Only show on dashboard
    if ($screen->id !== 'dashboard') {
        return;
    }
    
    $notices = array();
    
    // Check for missing Instagram token
    if (empty(get_option('ucfc_instagram_token'))) {
        $notices[] = array(
            'type' => 'warning',
            'message' => 'Instagram feed is not connected. <a href="' . admin_url('admin.php?page=restaurant-social') . '">Connect now</a> to display your photos.'
        );
    }
    
    // Check for email popup disabled
    if (get_option('ucfc_popup_enabled', '0') !== '1') {
        $notices[] = array(
            'type' => 'info',
            'message' => 'Email popup is disabled. <a href="' . admin_url('admin.php?page=restaurant-popup') . '">Enable it</a> to grow your subscriber list!'
        );
    }
    
    // Check for no menu items
    $menu_count = wp_count_posts('menu_item');
    if ($menu_count->publish === 0) {
        $notices[] = array(
            'type' => 'error',
            'message' => 'You have no menu items yet! <a href="' . admin_url('post-new.php?post_type=menu_item') . '">Add your first menu item</a> to get started.'
        );
    }
    
    foreach ($notices as $notice) {
        ?>
        <div class="notice notice-<?php echo $notice['type']; ?> is-dismissible">
            <p><strong>Uncle Chan's System:</strong> <?php echo $notice['message']; ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'ucfc_admin_notices');
