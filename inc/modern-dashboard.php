<?php
/**
 * Modern Unified Restaurant Dashboard
 * 
 * A stunning, all-in-one command center with glassmorphism design,
 * dark mode support, and the latest 2025 design trends.
 * 
 * @package Uncle_Chans_Chicken
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Unified Restaurant Dashboard
 */
function ucfc_register_modern_dashboard() {
    // Main Dashboard (replaces old scattered menu)
    add_menu_page(
        __('Restaurant Dashboard', 'uncle-chans'),
        __('🍗 Restaurant', 'uncle-chans'),
        'manage_options',
        'restaurant-hub',
        'ucfc_render_modern_dashboard',
        'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTAgMkM1LjU4IDIgMiA1LjU4IDIgMTBDMiAxNC40MiA1LjU4IDE4IDEwIDE4QzE0LjQyIDE4IDE4IDE0LjQyIDE4IDEwQzE4IDUuNTggMTQuNDIgMiAxMCAyWk0xMCAxNkM2LjY5IDE2IDQgMTMuMzEgNCAxMEM0IDYuNjkgNi42OSA0IDEwIDRDMTMuMzEgNCAxNiA2LjY5IDE2IDEwQzE2IDEzLjMxIDEzLjMxIDE2IDEwIDE2WiIgZmlsbD0iI0E3QUFBRCIvPjxwYXRoIGQ9Ik0xMCA2QzEyLjc2IDYgMTUgOC4yNCAxNSAxMUMxNSAxMy43NiAxMi43NiAxNiAxMCAxNkM3LjI0IDE2IDUgMTMuNzYgNSAxMUM1IDguMjQgNy4yNCA2IDEwIDZaIiBmaWxsPSIjQTdBQUFEIi8+PC9zdmc+',
        2
    );
    
    // Remove default submenu (Dashboard gets auto-added)
    remove_submenu_page('restaurant-hub', 'restaurant-hub');
}
add_action('admin_menu', 'ucfc_register_modern_dashboard', 9);

/**
 * Enqueue Dashboard Styles & Scripts
 */
function ucfc_dashboard_assets($hook) {
    if ($hook !== 'toplevel_page_restaurant-hub') {
        return;
    }
    
    // Chart.js for analytics
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);
    
    // Alpine.js for interactivity
    wp_enqueue_script('alpinejs', 'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js', array(), '3.13.3', true);
    
    // Custom dashboard script
    wp_enqueue_script('ucfc-dashboard', get_template_directory_uri() . '/assets/js/dashboard.js', array('jquery', 'chartjs'), '1.0.0', true);
    
    // Localize script
    wp_localize_script('ucfc-dashboard', 'ucfcDashboard', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ucfc_dashboard_nonce'),
        'site_url' => home_url()
    ));
}
add_action('admin_enqueue_scripts', 'ucfc_dashboard_assets');

/**
 * Render Modern Dashboard
 */
function ucfc_render_modern_dashboard() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Get stats
    global $wpdb;
    $stats = ucfc_get_dashboard_stats();
    ?>
    
    <style>
        /* 🎨 2025 MODERN DASHBOARD DESIGN */
        
        /* Reset WordPress admin styles */
        #wpbody-content {
            padding: 0 !important;
        }
        
        #wpcontent {
            padding-left: 0 !important;
        }
        
        .ucfc-modern-dashboard {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: calc(100vh - 32px);
            padding: 40px;
            margin: -20px -20px -20px -42px;
            width: calc(100% + 62px);
        }
        
        /* Glassmorphism Container */
        .ucfc-glass-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 40px;
            margin-bottom: 30px;
        }
        
        /* Header */
        .ucfc-dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        
        .ucfc-dashboard-title {
            font-size: 42px;
            font-weight: 800;
            color: white;
            margin: 0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .ucfc-dashboard-title .emoji {
            font-size: 48px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .ucfc-dashboard-actions {
            display: flex;
            gap: 15px;
        }
        
        .ucfc-btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .ucfc-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .ucfc-btn-primary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .ucfc-btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .ucfc-btn-icon {
            font-size: 18px;
        }
        
        /* Stats Grid */
        .ucfc-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .ucfc-stat-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .ucfc-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .ucfc-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }
        
        .ucfc-stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .ucfc-stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .ucfc-stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.1) 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        
        .ucfc-stat-trend {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .ucfc-stat-trend.negative {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .ucfc-stat-value {
            font-size: 48px;
            font-weight: 800;
            color: white;
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .ucfc-stat-label {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }
        
        /* Quick Actions Grid */
        .ucfc-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .ucfc-action-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            text-decoration: none !important;
            display: block;
            color: inherit;
        }
        
        .ucfc-action-card:hover {
            background: rgba(255, 255, 255, 0.18);
            transform: scale(1.02);
            border-color: rgba(255, 255, 255, 0.4);
        }
        
        .ucfc-action-card:focus {
            outline: 2px solid rgba(255, 255, 255, 0.5);
            outline-offset: 2px;
        }
        
        .ucfc-action-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.1) 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .ucfc-action-title {
            font-size: 18px;
            font-weight: 700;
            color: white !important;
            margin-bottom: 8px;
        }
        
        .ucfc-action-desc {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.75) !important;
            line-height: 1.5;
        }
        
        /* Chart Container */
        .ucfc-chart-container {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            margin-bottom: 40px;
        }
        
        .ucfc-chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .ucfc-chart-title {
            font-size: 24px;
            font-weight: 700;
            color: white;
        }
        
        .ucfc-chart-filters {
            display: flex;
            gap: 10px;
        }
        
        .ucfc-filter-btn {
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .ucfc-filter-btn.active {
            background: rgba(255, 255, 255, 0.3);
            border-color: white;
        }
        
        /* Recent Activity */
        .ucfc-activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .ucfc-activity-item {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s;
        }
        
        .ucfc-activity-item:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateX(5px);
        }
        
        .ucfc-activity-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .ucfc-activity-content {
            flex: 1;
        }
        
        .ucfc-activity-title {
            font-size: 16px;
            font-weight: 600;
            color: white;
            margin-bottom: 5px;
        }
        
        .ucfc-activity-meta {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.65);
        }
        
        .ucfc-activity-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }
        
        .ucfc-activity-badge.success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .ucfc-activity-badge.warning {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }
        
        /* Two Column Layout */
        .ucfc-two-column {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }
        
        @media (max-width: 1200px) {
            .ucfc-two-column {
                grid-template-columns: 1fr;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .ucfc-modern-dashboard {
                padding: 20px;
                margin: -20px;
                width: calc(100% + 40px);
            }
            
            .ucfc-dashboard-title {
                font-size: 32px;
            }
            
            .ucfc-dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            
            .ucfc-stats-grid,
            .ucfc-actions-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Fix WordPress admin bar conflict */
        body.admin-bar .ucfc-modern-dashboard {
            min-height: calc(100vh - 32px - 32px);
        }
        
        /* Loading State */
        .ucfc-loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Success Message */
        .ucfc-success-message {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            color: #10b981;
            font-weight: 500;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
    </style>
    
    <div class="ucfc-modern-dashboard">
        <!-- Header -->
        <div class="ucfc-dashboard-header">
            <h1 class="ucfc-dashboard-title">
                <span class="emoji">🍗</span>
                Restaurant Command Center
            </h1>
            <div class="ucfc-dashboard-actions">
                <a href="<?php echo home_url(); ?>" class="ucfc-btn ucfc-btn-secondary" target="_blank">
                    <span class="ucfc-btn-icon">🌐</span>
                    View Site
                </a>
                <a href="<?php echo home_url('/kitchen-display'); ?>" class="ucfc-btn ucfc-btn-primary" target="_blank">
                    <span class="ucfc-btn-icon">🍳</span>
                    Kitchen Display
                </a>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="ucfc-stats-grid">
            <div class="ucfc-stat-card">
                <div class="ucfc-stat-header">
                    <div class="ucfc-stat-icon">💰</div>
                    <div class="ucfc-stat-trend">
                        <span>↗</span> +12.5%
                    </div>
                </div>
                <div class="ucfc-stat-value">$<?php echo number_format($stats['revenue'], 0); ?></div>
                <div class="ucfc-stat-label">Total Revenue</div>
            </div>
            
            <div class="ucfc-stat-card">
                <div class="ucfc-stat-header">
                    <div class="ucfc-stat-icon">📦</div>
                    <div class="ucfc-stat-trend">
                        <span>↗</span> +8.3%
                    </div>
                </div>
                <div class="ucfc-stat-value"><?php echo $stats['total_orders']; ?></div>
                <div class="ucfc-stat-label">Total Orders</div>
            </div>
            
            <div class="ucfc-stat-card">
                <div class="ucfc-stat-header">
                    <div class="ucfc-stat-icon">⏱️</div>
                    <div class="ucfc-stat-trend">
                        <span>↗</span> <?php echo $stats['active_orders']; ?> active
                    </div>
                </div>
                <div class="ucfc-stat-value"><?php echo $stats['today_orders']; ?></div>
                <div class="ucfc-stat-label">Today's Orders</div>
            </div>
            
            <div class="ucfc-stat-card">
                <div class="ucfc-stat-header">
                    <div class="ucfc-stat-icon">⭐</div>
                    <div class="ucfc-stat-trend">
                        <span>↗</span> +15.2%
                    </div>
                </div>
                <div class="ucfc-stat-value"><?php echo $stats['menu_items']; ?></div>
                <div class="ucfc-stat-label">Menu Items</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="ucfc-glass-container">
            <h2 style="color: white; font-size: 28px; font-weight: 700; margin-bottom: 25px;">⚡ Quick Actions</h2>
            <div class="ucfc-actions-grid">
                <a href="<?php echo admin_url('edit.php?post_type=menu_item'); ?>" class="ucfc-action-card">
                    <div class="ucfc-action-icon">🍽️</div>
                    <div class="ucfc-action-title">Manage Menu</div>
                    <div class="ucfc-action-desc">Add, edit, or remove menu items</div>
                </a>
                
                <a href="<?php echo home_url('/orders-dashboard'); ?>" class="ucfc-action-card">
                    <div class="ucfc-action-icon">📋</div>
                    <div class="ucfc-action-title">View Orders</div>
                    <div class="ucfc-action-desc">Manage and track all orders</div>
                </a>
                
                <a href="<?php echo home_url('/scan-pickup'); ?>" class="ucfc-action-card">
                    <div class="ucfc-action-icon">📱</div>
                    <div class="ucfc-action-title">QR Scanner</div>
                    <div class="ucfc-action-desc">Scan QR codes for pickup</div>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=restaurant-settings-panel'); ?>" class="ucfc-action-card">
                    <div class="ucfc-action-icon">⚙️</div>
                    <div class="ucfc-action-title">Settings</div>
                    <div class="ucfc-action-desc">Configure restaurant options</div>
                </a>
                
                <a href="<?php echo admin_url('edit.php?post_type=special_offer'); ?>" class="ucfc-action-card">
                    <div class="ucfc-action-icon">🎁</div>
                    <div class="ucfc-action-title">Special Offers</div>
                    <div class="ucfc-action-desc">Create promotional deals</div>
                </a>
                
                <a href="<?php echo admin_url('edit.php?post_type=customer_review'); ?>" class="ucfc-action-card">
                    <div class="ucfc-action-icon">💬</div>
                    <div class="ucfc-action-title">Reviews</div>
                    <div class="ucfc-action-desc">Manage customer feedback</div>
                </a>
            </div>
        </div>
        
        <!-- Two Column Layout -->
        <div class="ucfc-two-column">
            <!-- Sales Chart -->
            <div class="ucfc-chart-container">
                <div class="ucfc-chart-header">
                    <h3 class="ucfc-chart-title">📈 Sales Overview</h3>
                    <div class="ucfc-chart-filters">
                        <button class="ucfc-filter-btn active">7 Days</button>
                        <button class="ucfc-filter-btn">30 Days</button>
                        <button class="ucfc-filter-btn">90 Days</button>
                    </div>
                </div>
                <canvas id="ucfc-sales-chart" height="300"></canvas>
            </div>
            
            <!-- Recent Activity -->
            <div class="ucfc-chart-container">
                <h3 class="ucfc-chart-title" style="margin-bottom: 20px;">🔔 Recent Activity</h3>
                <ul class="ucfc-activity-list">
                    <?php foreach ($stats['recent_activity'] as $activity): ?>
                    <li class="ucfc-activity-item">
                        <div class="ucfc-activity-icon"><?php echo $activity['icon']; ?></div>
                        <div class="ucfc-activity-content">
                            <div class="ucfc-activity-title"><?php echo esc_html($activity['title']); ?></div>
                            <div class="ucfc-activity-meta"><?php echo esc_html($activity['time']); ?></div>
                        </div>
                        <span class="ucfc-activity-badge <?php echo $activity['status']; ?>"><?php echo esc_html($activity['badge']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Phase 4 Features Status -->
        <div class="ucfc-glass-container">
            <h2 style="color: white; font-size: 28px; font-weight: 700; margin-bottom: 25px;">🚀 Advanced Features</h2>
            <div class="ucfc-stats-grid">
                <div class="ucfc-stat-card" style="background: rgba(16, 185, 129, 0.15);">
                    <div class="ucfc-stat-icon" style="background: rgba(16, 185, 129, 0.3);">📱</div>
                    <div class="ucfc-stat-value" style="font-size: 32px;">SMS</div>
                    <div class="ucfc-stat-label">Twilio Notifications</div>
                    <div style="margin-top: 10px; font-size: 13px; color: rgba(255, 255, 255, 0.7);">
                        <?php echo $stats['sms_sent']; ?> sent today
                    </div>
                </div>
                
                <div class="ucfc-stat-card" style="background: rgba(59, 130, 246, 0.15);">
                    <div class="ucfc-stat-icon" style="background: rgba(59, 130, 246, 0.3);">🔔</div>
                    <div class="ucfc-stat-value" style="font-size: 32px;">Push</div>
                    <div class="ucfc-stat-label">Browser Notifications</div>
                    <div style="margin-top: 10px; font-size: 13px; color: rgba(255, 255, 255, 0.7);">
                        <?php echo $stats['push_subscribers']; ?> subscribers
                    </div>
                </div>
                
                <div class="ucfc-stat-card" style="background: rgba(245, 158, 11, 0.15);">
                    <div class="ucfc-stat-icon" style="background: rgba(245, 158, 11, 0.3);">🍳</div>
                    <div class="ucfc-stat-value" style="font-size: 32px;">KDS</div>
                    <div class="ucfc-stat-label">Kitchen Display</div>
                    <div style="margin-top: 10px; font-size: 13px; color: rgba(255, 255, 255, 0.7);">
                        <a href="<?php echo home_url('/kitchen-display'); ?>" style="color: white; text-decoration: underline;">Open Display →</a>
                    </div>
                </div>
                
                <div class="ucfc-stat-card" style="background: rgba(139, 92, 246, 0.15);">
                    <div class="ucfc-stat-icon" style="background: rgba(139, 92, 246, 0.3);">📱</div>
                    <div class="ucfc-stat-value" style="font-size: 32px;">QR</div>
                    <div class="ucfc-stat-label">Pickup Scanner</div>
                    <div style="margin-top: 10px; font-size: 13px; color: rgba(255, 255, 255, 0.7);">
                        <?php echo $stats['qr_scans']; ?> scans today
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Sales Chart
        const ctx = document.getElementById('ucfc-sales-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($stats['chart_labels']); ?>,
                    datasets: [{
                        label: 'Revenue',
                        data: <?php echo json_encode($stats['chart_data']); ?>,
                        borderColor: 'rgba(245, 87, 108, 1)',
                        backgroundColor: 'rgba(245, 87, 108, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(245, 87, 108, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.8)',
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.8)'
                            }
                        }
                    }
                }
            });
        }
    });
    </script>
    
    <?php
}

/**
 * Get Dashboard Statistics
 */
function ucfc_get_dashboard_stats() {
    global $wpdb;
    
    // Orders stats
    $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}orders");
    $today_orders = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}orders WHERE DATE(created_at) = %s",
        current_time('Y-m-d')
    ));
    $active_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}orders WHERE order_status IN ('pending', 'confirmed', 'preparing', 'ready')");
    
    // Revenue
    $revenue = $wpdb->get_var("SELECT SUM(total) FROM {$wpdb->prefix}orders WHERE order_status = 'completed'");
    
    // Menu items
    $menu_items = wp_count_posts('menu_item')->publish;
    
    // SMS sent (from queue)
    $sms_sent = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}ucfc_sms_queue WHERE DATE(sent_at) = %s AND status = 'sent'",
        current_time('Y-m-d')
    ));
    
    // Push subscribers
    $push_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ucfc_push_subscriptions WHERE is_active = 1");
    
    // QR scans
    $qr_scans = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}ucfc_order_pickups WHERE DATE(picked_up_at) = %s",
        current_time('Y-m-d')
    ));
    
    // Chart data (last 7 days)
    $chart_labels = array();
    $chart_data = array();
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chart_labels[] = date('M j', strtotime($date));
        $daily_revenue = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total) FROM {$wpdb->prefix}orders WHERE DATE(created_at) = %s",
            $date
        ));
        $chart_data[] = floatval($daily_revenue);
    }
    
    // Recent activity
    $recent_orders = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}orders ORDER BY created_at DESC LIMIT 5");
    $recent_activity = array();
    foreach ($recent_orders as $order) {
        $time_ago = human_time_diff(strtotime($order->created_at), current_time('timestamp')) . ' ago';
        $recent_activity[] = array(
            'icon' => '🛒',
            'title' => 'New order #' . $order->order_number,
            'time' => $time_ago,
            'badge' => ucfirst($order->order_status),
            'status' => $order->order_status === 'completed' ? 'success' : 'warning'
        );
    }
    
    return array(
        'total_orders' => $total_orders ?: 0,
        'today_orders' => $today_orders ?: 0,
        'active_orders' => $active_orders ?: 0,
        'revenue' => $revenue ?: 0,
        'menu_items' => $menu_items ?: 0,
        'sms_sent' => $sms_sent ?: 0,
        'push_subscribers' => $push_subscribers ?: 0,
        'qr_scans' => $qr_scans ?: 0,
        'chart_labels' => $chart_labels,
        'chart_data' => $chart_data,
        'recent_activity' => $recent_activity
    );
}
