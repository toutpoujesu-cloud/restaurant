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
        /* 🎨 RESTAURANT COMMAND CENTER - COMPLETE REDESIGN */
        
        /* Import Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');
        
        /* CSS Variables */
        :root {
            --coral-dawn: #FF6B6B;
            --sunset-orange: #FF8E3C;
            --golden-hour: #FFD166;
            --dark-charcoal: #1A1A1D;
            --slate-gray: #4A4A4F;
            --soft-gray: #9CA3AF;
            --off-white: #F9FAFB;
            --success-green: #10B981;
            --warning-amber: #F59E0B;
            --info-blue: #3B82F6;
            --font-display: 'Poppins', sans-serif;
            --font-body: 'Inter', sans-serif;
            --gradient-primary: linear-gradient(135deg, #FF6B6B 0%, #FF8E3C 50%, #FFD166 100%);
            --gradient-hero: linear-gradient(180deg, #FF6B6B 0%, #FF8E3C 100%);
        }
        
        /* Reset WordPress admin styles */
        #wpbody-content {
            padding: 0 !important;
        }
        
        #wpcontent {
            padding-left: 0 !important;
        }
        
        .modern-dashboard {
            font-family: var(--font-body);
            background: var(--off-white);
            padding: 0;
            margin: -20px auto -20px auto;
            width: 100%;
            max-width: 1600px;
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        /* Section Headers */
        .section-header {
            margin: 3rem 2rem 2rem;
        }
        
        .section-header h2 {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark-charcoal);
            margin: 0;
        }
        
        /* Header Section */
        .dashboard-header {
            background: var(--gradient-hero);
            padding: 2rem 2rem;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 1.5rem 1.5rem;
            box-shadow: 0 8px 32px rgba(255, 107, 107, 0.18);
            margin-bottom: 2.5rem;
        }
        
        .header-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }
        
        .gradient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            animation: float-orb 8s ease-in-out infinite;
        }
        
        .orb-1 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--golden-hour) 0%, transparent 70%);
            top: -200px;
            right: -100px;
        }
        
        .orb-2 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, var(--sunset-orange) 0%, transparent 70%);
            bottom: -150px;
            left: -50px;
            animation-delay: -4s;
        }
        
        @keyframes float-orb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }
        
        .dashboard-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        
        .header-title-wrapper {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        
        .header-title-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        
        .dashboard-header-content h1 {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 16px rgba(0, 0, 0, 0.2);
            margin: 0;
            letter-spacing: -0.02em;
        }
        
        .dashboard-header-content p {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.9);
            margin: 0.25rem 0 0 0;
            font-weight: 500;
        }
        
        .header-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 1rem;
            font-family: var(--font-display);
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-decoration: none;
        }
        
        .btn-icon {
            font-size: 1.25rem;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary {
            background: white;
            color: var(--coral-dawn);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }
        
        /* Stats Section */
        .stats-section {
            padding: 0 2.5rem;
            margin-top: -3rem;
            position: relative;
            z-index: 10;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3rem;
            padding: 0 3rem;
            margin-bottom: 3rem;
        }
        
        /* Stat Cards */
        .stat-card {
            width: 100%;
            aspect-ratio: auto;
            min-height: 140px;
            padding: 1.5rem;
            background: linear-gradient(145deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.85) 100%);
            border: 2px solid rgba(255, 107, 107, 0.1);
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(255, 107, 107, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            position: relative;
            overflow: visible;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 16px 48px rgba(255, 107, 107, 0.24), inset 0 1px 0 rgba(255, 255, 255, 1);
            border-color: rgba(255, 107, 107, 0.3);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--gradient-primary);
            border-radius: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(255, 107, 107, 0.3);
            animation: float 3s ease-in-out infinite;
            font-size: 32px;
        }
        
        .stat-value {
            font-family: var(--font-display);
            font-size: 2.75rem;
            font-weight: 700;
            color: var(--dark-charcoal);
            line-height: 1;
            letter-spacing: -0.02em;
            margin-top: 1.25rem;
            text-shadow: 0 2px 8px rgba(255, 107, 107, 0.15);
        }
        
        .currency {
            font-size: 2rem;
            margin-right: 0.25rem;
            opacity: 0.7;
        }
        
        .stat-label {
            font-family: var(--font-body);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--slate-gray);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.5rem;
        }
        
        .stat-trend {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 0.75rem;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
        }
        
        .stat-trend.negative {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            padding: 0 2rem;
            margin-bottom: 3rem;
        }
        
        .quick-actions-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        
        .quick-actions-title {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-charcoal);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        /* Action Cards */
        .action-card {
            position: relative;
            padding: 1.75rem;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            background: linear-gradient(145deg, #FFFFFF 0%, #F9FAFB 100%);
            border-radius: 1.5rem;
            border: 2px solid rgba(255, 107, 107, 0.1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 10px 30px rgba(255, 107, 107, 0.08);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            overflow: hidden;
            text-decoration: none !important;
            opacity: 0;
            transform: scale(0.9);
            animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        
        @keyframes scaleIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .action-card:nth-child(1) { animation-delay: 0.1s; }
        .action-card:nth-child(2) { animation-delay: 0.2s; }
        .action-card:nth-child(3) { animation-delay: 0.3s; }
        .action-card:nth-child(4) { animation-delay: 0.4s; }
        .action-card:nth-child(5) { animation-delay: 0.5s; }
        .action-card:nth-child(6) { animation-delay: 0.6s; }
        
        .action-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1), 0 25px 60px rgba(255, 107, 107, 0.2);
            border-color: rgba(255, 107, 107, 0.3);
        }
        
        .action-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.15) 0%, rgba(255, 142, 60, 0.15) 100%);
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 2px 0 rgba(255, 255, 255, 0.5), 0 4px 16px rgba(255, 107, 107, 0.2);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            font-size: 28px;
        }
        
        .action-card:hover .action-icon {
            transform: scale(1.1) rotateZ(5deg);
        }
        
        .action-content {
            flex: 1;
        }
        
        .action-title {
            font-family: var(--font-display);
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark-charcoal) !important;
            margin: 0 0 0.5rem 0;
        }
        
        .action-card:hover .action-title {
            color: var(--coral-dawn) !important;
        }
        
        .action-description {
            font-family: var(--font-body);
            font-size: 0.875rem;
            color: var(--slate-gray) !important;
            line-height: 1.6;
            margin: 0;
        }
        
        /* Dashboard Columns */
        .dashboard-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 3rem;
            padding: 0 2.5rem;
        }
        
        /* Chart Container */
        .sales-chart-container,
        .recent-activity-container {
            background: linear-gradient(145deg, #FFFFFF 0%, #F9FAFB 100%);
            border-radius: 1.5rem;
            padding: 2rem;
            border: 2px solid rgba(255, 107, 107, 0.1);
            box-shadow: 0 8px 32px rgba(255, 107, 107, 0.08);
        }
        
        .sales-chart-container canvas {
            max-height: 200px !important;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .chart-header h3,
        .recent-activity-container h3 {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-charcoal);
            margin: 0 0 2rem 0;
        }
        
        .time-period-selector {
            display: flex;
            gap: 0.5rem;
            background: rgba(255, 107, 107, 0.05);
            padding: 0.25rem;
            border-radius: 1rem;
        }
        
        .period-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            border-radius: 0.75rem;
            font-family: var(--font-display);
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--slate-gray);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .period-btn.active {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }
        
        .period-btn:hover:not(.active) {
            background: rgba(255, 107, 107, 0.1);
            color: var(--coral-dawn);
        }
        
        /* Recent Activity */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: linear-gradient(145deg, rgba(255, 107, 107, 0.03) 0%, rgba(255, 142, 60, 0.03) 100%);
            border-radius: 1rem;
            border: 1px solid rgba(255, 107, 107, 0.1);
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background: linear-gradient(145deg, rgba(255, 107, 107, 0.06) 0%, rgba(255, 142, 60, 0.06) 100%);
            transform: translateX(4px);
            border-color: rgba(255, 107, 107, 0.2);
        }
        
        .activity-icon {
            width: 48px;
            height: 48px;
            background: var(--gradient-primary);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.2);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-charcoal);
            margin-bottom: 0.25rem;
        }
        
        .activity-meta {
            font-size: 0.875rem;
            color: var(--soft-gray);
        }
        
        .activity-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .activity-badge.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
        }
        
        .activity-badge.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-amber);
        }
        
        .activity-badge.info {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info-blue);
        }
        
        /* Phase 4 Features Section */
        .phase4-features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            padding: 0 2rem;
            margin-bottom: 3rem;
        }
        
        .feature-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            border: 2px solid rgba(255, 107, 107, 0.1);
            border-radius: 1.5rem;
            padding: 2rem;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 20px rgba(255, 107, 107, 0.08);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards;
        }
        
        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }
        .feature-card:nth-child(4) { animation-delay: 0.4s; }
        
        .feature-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 40px rgba(255, 107, 107, 0.15);
            border-color: rgba(255, 107, 107, 0.3);
        }
        
        .feature-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        
        .feature-icon {
            width: 64px;
            height: 64px;
            background: var(--gradient-primary);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
            animation: float 3s ease-in-out infinite;
        }
        
        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-charcoal);
            font-family: var(--font-display);
        }
        
        .feature-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.75rem;
        }
        
        .feature-status.active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
        }
        
        .feature-status.active::before {
            content: '●';
            font-size: 1rem;
        }
        
        .feature-description {
            font-size: 0.9375rem;
            color: var(--soft-gray);
            line-height: 1.7;
            font-family: var(--font-body);
        }
        
        /* Two Column Layout */
        .two-column-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-top: 2.5rem;
            padding: 0 2rem;
        }
        
        /* Responsive Breakpoints */
        @media (max-width: 1400px) {
            .dashboard-header {
                padding: 2.5rem 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .quick-actions {
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            }
        }
        
        @media (max-width: 1200px) {
            .two-column-layout {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                padding: 2rem 1.5rem;
            }
            
            .action-card {
                min-height: 180px;
            }
            
            .phase4-features {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 992px) {
            .modern-dashboard {
                padding: 1.5rem;
            }
            
            .dashboard-header-content h1 {
                font-size: 2rem;
            }
            
            .stats-grid,
            .quick-actions {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 1.25rem;
            }
            
            .stat-value {
                font-size: 2.75rem;
            }
            
            .action-card {
                min-height: 160px;
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .modern-dashboard {
                padding: 1rem;
                margin: -20px -20px -20px -40px;
                width: calc(100% + 60px);
            }
            
            .dashboard-header {
                padding: 2rem 1.25rem;
                border-radius: 0 0 1.5rem 1.5rem;
            }
            
            .dashboard-header-content h1 {
                font-size: 1.75rem;
            }
            
            .dashboard-header-content p {
                font-size: 0.9375rem;
            }
            
            .header-actions {
                flex-direction: column;
                width: 100%;
                gap: 0.75rem;
            }
            
            .btn-primary,
            .btn-secondary {
                width: 100%;
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .action-card {
                min-height: 140px;
                padding: 1.25rem;
            }
            
            .action-icon {
                width: 56px;
                height: 56px;
                font-size: 24px;
            }
            
            .sales-chart-container,
            .recent-activity-container {
                padding: 1.5rem;
            }
            
            .time-period-selector {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .period-btn {
                flex: 1;
                min-width: 80px;
            }
            
            .phase4-features {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .feature-card {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .modern-dashboard {
                margin: -20px -10px;
                width: calc(100% + 20px);
            }
            
            .dashboard-header-content h1 {
                font-size: 1.5rem;
            }
            
            .stat-value {
                font-size: 2.25rem;
            }
            
            .action-title {
                font-size: 1.125rem;
            }
            
            .gradient-orb {
                width: 300px;
                height: 300px;
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
    
    <div class="modern-dashboard">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="header-bg">
                <div class="gradient-orb" style="top: -100px; left: -100px;"></div>
                <div class="gradient-orb" style="top: 100px; right: -150px; animation-delay: -4s;"></div>
            </div>
            <div class="dashboard-header-content">
                <div class="header-title-wrapper">
                    <div class="header-title-icon">
                        <span style="font-size: 48px;">🍗</span>
                    </div>
                    <div>
                        <h1>Restaurant Command Center</h1>
                        <p>Manage your restaurant operations with ease</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="<?php echo home_url(); ?>" class="btn-secondary" target="_blank">
                        <span class="btn-icon">🌐</span>
                        <span>View Site</span>
                    </a>
                    <a href="<?php echo home_url('/kitchen-display'); ?>" class="btn-primary" target="_blank" rel="noopener">
                        <span class="btn-icon">🍳</span>
                        <span>Kitchen Display</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">💰</div>
                    <div class="stat-trend">
                        <span>↗</span> +12.5%
                    </div>
                </div>
                <div class="stat-value">$<?php echo number_format($stats['revenue'], 0); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">📦</div>
                    <div class="stat-trend">
                        <span>↗</span> +8.3%
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">⏱️</div>
                    <div class="stat-trend">
                        <span>↗</span> <?php echo $stats['active_orders']; ?> active
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['today_orders']; ?></div>
                <div class="stat-label">Today's Orders</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-trend">
                        <span>↗</span> +15.2%
                    </div>
                </div>
                <div class="stat-value"><?php echo $stats['menu_items']; ?></div>
                <div class="stat-label">Menu Items</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="section-header">
            <h2>⚡ Quick Actions</h2>
        </div>
        <div class="quick-actions">
            <a href="<?php echo admin_url('edit.php?post_type=menu_item'); ?>" class="action-card">
                <div class="action-icon">🍽️</div>
                <div class="action-content">
                    <div class="action-title">Manage Menu</div>
                    <div class="action-description">Add, edit, or remove menu items</div>
                </div>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=ucfc-orders'); ?>" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-content">
                    <div class="action-title">View Orders</div>
                    <div class="action-description">Manage and track all orders</div>
                </div>
            </a>
            
            <a href="<?php echo home_url('/scan-pickup'); ?>" class="action-card" target="_blank">
                <div class="action-icon">📱</div>
                <div class="action-content">
                    <div class="action-title">QR Scanner</div>
                    <div class="action-description">Scan QR codes for pickup</div>
                </div>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=restaurant-settings-panel'); ?>" class="action-card">
                <div class="action-icon">⚙️</div>
                <div class="action-content">
                    <div class="action-title">Settings</div>
                    <div class="action-description">Configure restaurant options</div>
                </div>
            </a>
            
            <a href="<?php echo admin_url('edit.php?post_type=special_offer'); ?>" class="action-card">
                <div class="action-icon">🎁</div>
                <div class="action-content">
                    <div class="action-title">Special Offers</div>
                    <div class="action-description">Create promotional deals</div>
                </div>
            </a>
            
            <a href="<?php echo admin_url('edit.php?post_type=customer_review'); ?>" class="action-card">
                <div class="action-icon">💬</div>
                <div class="action-content">
                    <div class="action-title">Reviews</div>
                    <div class="action-description">Manage customer feedback</div>
                </div>
            </a>
        </div>
        
        <!-- Two Column Layout -->
        <div class="two-column-layout">
            <!-- Sales Chart -->
            <div class="sales-chart-container">
                <div class="chart-header">
                    <h3>📈 Sales Overview</h3>
                    <div class="time-period-selector">
                        <button class="period-btn active">7 Days</button>
                        <button class="period-btn">30 Days</button>
                        <button class="period-btn">90 Days</button>
                    </div>
                </div>
                <canvas id="ucfc-sales-chart" style="max-height: 200px;"></canvas>
            </div>
            
            <!-- Recent Activity -->
            <div class="recent-activity-container">
                <h3>🔔 Recent Activity</h3>
                <ul class="activity-list">
                    <?php foreach ($stats['recent_activity'] as $activity): ?>
                    <li class="activity-item">
                        <div class="activity-icon"><?php echo $activity['icon']; ?></div>
                        <div class="activity-content">
                            <div class="activity-title"><?php echo esc_html($activity['title']); ?></div>
                            <div class="activity-meta"><?php echo esc_html($activity['time']); ?></div>
                        </div>
                        <span class="activity-badge <?php echo $activity['status']; ?>"><?php echo esc_html($activity['badge']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Phase 4 Features Status -->
        <div class="section-header">
            <h2>🚀 Advanced Features</h2>
        </div>
        <div class="phase4-features">
            <div class="feature-card">
                <div class="feature-header">
                    <div class="feature-icon">📱</div>
                    <div class="feature-title">SMS Notifications</div>
                </div>
                <div class="feature-description">
                    Twilio integration for real-time order updates via SMS
                </div>
                <div class="feature-status active">
                    Active • <?php echo $stats['sms_sent']; ?> sent today
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-header">
                    <div class="feature-icon">🔔</div>
                    <div class="feature-title">Push Notifications</div>
                </div>
                <div class="feature-description">
                    Browser push notifications for instant customer updates
                </div>
                <div class="feature-status active">
                    Active • <?php echo $stats['push_subscribers']; ?> subscribers
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-header">
                    <div class="feature-icon">🍳</div>
                    <div class="feature-title">Kitchen Display</div>
                </div>
                <div class="feature-description">
                    Real-time order management for kitchen operations
                </div>
                <div class="feature-status active">
                    <a href="<?php echo home_url('/kitchen-display'); ?>" style="color: inherit; text-decoration: none;">Active • Open Display →</a>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-header">
                    <div class="feature-icon">📱</div>
                    <div class="feature-title">QR Code Scanner</div>
                </div>
                <div class="feature-description">
                    Quick and secure order pickup verification system
                </div>
                <div class="feature-status active">
                    Active • <?php echo $stats['qr_scans']; ?> scans today
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
                    aspectRatio: 2,
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
