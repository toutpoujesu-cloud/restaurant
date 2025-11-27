<?php
/**
 * Menu Management Dashboard
 * Advanced menu management with analytics, drag-drop, and performance tracking
 */

// Add Menu Dashboard to admin menu
function ucfc_add_menu_dashboard() {
    add_menu_page(
        'Menu Dashboard',
        'Menu Dashboard',
        'manage_options',
        'ucfc-menu-dashboard',
        'ucfc_render_menu_dashboard',
        'dashicons-food',
        25
    );
    
    add_submenu_page(
        'ucfc-menu-dashboard',
        'Menu Analytics',
        'Analytics',
        'manage_options',
        'ucfc-menu-analytics',
        'ucfc_render_menu_analytics'
    );
    
    add_submenu_page(
        'ucfc-menu-dashboard',
        'Import/Export',
        'Import/Export',
        'manage_options',
        'ucfc-menu-import-export',
        'ucfc_render_import_export'
    );
    
    add_submenu_page(
        'ucfc-menu-dashboard',
        'Batch Import Menu',
        'Batch Import',
        'manage_options',
        'ucfc-batch-import',
        'ucfc_render_batch_import'
    );
}
add_action('admin_menu', 'ucfc_add_menu_dashboard');

// Enqueue dashboard assets
function ucfc_menu_dashboard_assets($hook) {
    if (strpos($hook, 'ucfc-menu') === false) {
        return;
    }
    
    // Sortable JS
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-droppable');
    
    // Chart.js for analytics
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);
    
    // Custom dashboard JS
    wp_enqueue_script('ucfc-menu-dashboard', get_template_directory_uri() . '/assets/js/menu-dashboard.js', array('jquery', 'jquery-ui-sortable'), '1.0', true);
    
    // Localize script
    wp_localize_script('ucfc-menu-dashboard', 'ucfcMenuDashboard', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ucfc_menu_dashboard_nonce')
    ));
    
    // Dashboard styles
    wp_enqueue_style('ucfc-menu-dashboard', get_template_directory_uri() . '/assets/css/menu-dashboard.css', array(), '1.0');
}
add_action('admin_enqueue_scripts', 'ucfc_menu_dashboard_assets');

// Main Dashboard Render
function ucfc_render_menu_dashboard() {
    $menu_items = get_posts(array(
        'post_type' => 'menu_item',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ));
    
    $categories = get_terms(array(
        'taxonomy' => 'menu_category',
        'hide_empty' => false
    ));
    
    // Get analytics data
    $total_items = count($menu_items);
    $total_revenue = ucfc_calculate_total_revenue();
    $avg_rating = ucfc_get_average_rating();
    $top_sellers = ucfc_get_top_sellers(5);
    $alerts = ucfc_get_menu_alerts();
    
    ?>
    <div class="wrap ucfc-menu-dashboard">
        <h1 class="ucfc-dashboard-title">
            <span class="dashicons dashicons-food"></span>
            Menu Dashboard
            <button class="button button-primary ucfc-add-item-btn" data-action="add-new">
                <span class="dashicons dashicons-plus-alt"></span> Add New Item
            </button>
            <button class="button ucfc-bulk-edit-btn">
                <span class="dashicons dashicons-edit"></span> Bulk Edit
            </button>
        </h1>
        
        <!-- Performance Overview -->
        <div class="ucfc-dashboard-stats">
            <div class="ucfc-stat-card ucfc-revenue">
                <div class="ucfc-stat-icon">💰</div>
                <div class="ucfc-stat-content">
                    <div class="ucfc-stat-label">Total Revenue</div>
                    <div class="ucfc-stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="ucfc-stat-trend">This Month</div>
                </div>
            </div>
            
            <div class="ucfc-stat-card ucfc-items">
                <div class="ucfc-stat-icon">📦</div>
                <div class="ucfc-stat-content">
                    <div class="ucfc-stat-label">Active Items</div>
                    <div class="ucfc-stat-value"><?php echo $total_items; ?></div>
                    <div class="ucfc-stat-trend">In Menu</div>
                </div>
            </div>
            
            <div class="ucfc-stat-card ucfc-rating">
                <div class="ucfc-stat-icon">⭐</div>
                <div class="ucfc-stat-content">
                    <div class="ucfc-stat-label">Avg Rating</div>
                    <div class="ucfc-stat-value"><?php echo number_format($avg_rating, 1); ?>/5.0</div>
                    <div class="ucfc-stat-trend">Customer Satisfaction</div>
                </div>
            </div>
            
            <div class="ucfc-stat-card ucfc-orders">
                <div class="ucfc-stat-icon">🛒</div>
                <div class="ucfc-stat-content">
                    <div class="ucfc-stat-label">Orders Today</div>
                    <div class="ucfc-stat-value"><?php echo ucfc_get_orders_today(); ?></div>
                    <div class="ucfc-stat-trend ucfc-trend-up">
                        <span class="dashicons dashicons-arrow-up-alt"></span> +12%
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Sellers -->
        <div class="ucfc-dashboard-section">
            <h2><span class="dashicons dashicons-chart-line"></span> Top Sellers Today</h2>
            <div class="ucfc-top-sellers">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="50">Rank</th>
                            <th>Item</th>
                            <th width="100">Price</th>
                            <th width="100">Sold</th>
                            <th width="100">Revenue</th>
                            <th width="100">Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_sellers as $index => $seller) : ?>
                        <tr>
                            <td class="ucfc-rank"><?php echo ($index + 1); ?></td>
                            <td>
                                <strong><?php echo esc_html($seller['name']); ?></strong>
                                <?php if ($seller['is_popular']) : ?>
                                <span class="ucfc-badge ucfc-badge-hot">🔥 Hot</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($seller['price'], 2); ?></td>
                            <td><?php echo $seller['sold']; ?> sold</td>
                            <td><strong>$<?php echo number_format($seller['revenue'], 2); ?></strong></td>
                            <td>
                                <span class="ucfc-trend <?php echo $seller['trend_class']; ?>">
                                    <span class="dashicons dashicons-arrow-<?php echo $seller['trend_icon']; ?>-alt"></span>
                                    <?php echo $seller['trend']; ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Alerts -->
        <?php if (!empty($alerts)) : ?>
        <div class="ucfc-dashboard-section">
            <h2><span class="dashicons dashicons-warning"></span> Alerts</h2>
            <div class="ucfc-alerts">
                <?php foreach ($alerts as $alert) : ?>
                <div class="ucfc-alert ucfc-alert-<?php echo $alert['type']; ?>">
                    <span class="dashicons dashicons-<?php echo $alert['icon']; ?>"></span>
                    <?php echo esc_html($alert['message']); ?>
                    <?php if (isset($alert['action'])) : ?>
                    <a href="<?php echo $alert['action']['url']; ?>" class="button button-small">
                        <?php echo $alert['action']['label']; ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Drag & Drop Menu Builder -->
        <div class="ucfc-dashboard-section">
            <h2><span class="dashicons dashicons-menu-alt"></span> Menu Builder</h2>
            <div class="ucfc-menu-builder">
                <!-- Categories Tabs -->
                <div class="ucfc-categories-tabs">
                    <button class="ucfc-cat-tab active" data-category="all">All Items (<?php echo $total_items; ?>)</button>
                    <?php foreach ($categories as $category) : ?>
                    <button class="ucfc-cat-tab" data-category="<?php echo $category->term_id; ?>">
                        <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                    </button>
                    <?php endforeach; ?>
                    <button class="ucfc-cat-tab ucfc-add-category" data-action="add-category">
                        <span class="dashicons dashicons-plus"></span> Add Category
                    </button>
                </div>
                
                <!-- Menu Items Grid -->
                <div class="ucfc-menu-grid" id="ucfc-sortable-menu">
                    <?php foreach ($menu_items as $item) : 
                        $price = get_post_meta($item->ID, '_ucfc_price', true);
                        $cost = get_post_meta($item->ID, '_ucfc_cost', true);
                        $sold_count = get_post_meta($item->ID, '_ucfc_sold_count', true) ?: 0;
                        $rating = get_post_meta($item->ID, '_ucfc_rating', true) ?: 0;
                        $thumbnail = get_the_post_thumbnail_url($item->ID, 'medium');
                        $categories_list = wp_get_post_terms($item->ID, 'menu_category', array('fields' => 'ids'));
                    ?>
                    <div class="ucfc-menu-card" 
                         data-item-id="<?php echo $item->ID; ?>"
                         data-categories="<?php echo implode(',', $categories_list); ?>">
                        <div class="ucfc-card-image">
                            <?php if ($thumbnail) : ?>
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($item->post_title); ?>">
                            <?php else : ?>
                            <div class="ucfc-no-image">
                                <span class="dashicons dashicons-camera"></span>
                            </div>
                            <?php endif; ?>
                            <div class="ucfc-card-overlay">
                                <button class="ucfc-quick-edit" data-item-id="<?php echo $item->ID; ?>" title="Quick Edit">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button class="ucfc-duplicate" data-item-id="<?php echo $item->ID; ?>" title="Duplicate">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </button>
                                <button class="ucfc-delete" data-item-id="<?php echo $item->ID; ?>" title="Delete">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                        <div class="ucfc-card-content">
                            <h3><?php echo esc_html($item->post_title); ?></h3>
                            <div class="ucfc-card-meta">
                                <span class="ucfc-price">$<?php echo number_format($price, 2); ?></span>
                                <?php if ($cost) : 
                                    $profit = $price - $cost;
                                    $margin = ($profit / $price) * 100;
                                ?>
                                <span class="ucfc-profit" title="Profit Margin: <?php echo number_format($margin, 1); ?>%">
                                    💰 <?php echo number_format($margin, 0); ?>%
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="ucfc-card-stats">
                                <span class="ucfc-sold" title="Times Ordered">
                                    <span class="dashicons dashicons-cart"></span> <?php echo $sold_count; ?>
                                </span>
                                <?php if ($rating > 0) : ?>
                                <span class="ucfc-rating" title="Average Rating">
                                    <span class="dashicons dashicons-star-filled"></span> <?php echo number_format($rating, 1); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="ucfc-quick-actions">
            <a href="<?php echo admin_url('admin.php?page=ucfc-menu-analytics'); ?>" class="button">
                <span class="dashicons dashicons-chart-bar"></span> View Full Analytics
            </a>
            <a href="<?php echo admin_url('admin.php?page=ucfc-menu-import-export'); ?>" class="button">
                <span class="dashicons dashicons-download"></span> Import/Export
            </a>
            <button class="button ucfc-export-pdf">
                <span class="dashicons dashicons-media-document"></span> Export PDF Menu
            </button>
        </div>
    </div>
    
    <!-- Quick Edit Modal -->
    <div id="ucfc-quick-edit-modal" class="ucfc-modal" style="display:none;">
        <div class="ucfc-modal-overlay"></div>
        <div class="ucfc-modal-content">
            <div class="ucfc-modal-header">
                <h2><span class="dashicons dashicons-edit"></span> Quick Edit</h2>
                <button class="ucfc-modal-close">&times;</button>
            </div>
            <div class="ucfc-modal-body" id="ucfc-quick-edit-form">
                <!-- Form loaded via AJAX -->
            </div>
        </div>
    </div>
    <?php
}

// Analytics Page
function ucfc_render_menu_analytics() {
    ?>
    <div class="wrap ucfc-menu-analytics">
        <h1><span class="dashicons dashicons-chart-bar"></span> Menu Analytics</h1>
        
        <div class="ucfc-analytics-filters">
            <select id="ucfc-analytics-period">
                <option value="today">Today</option>
                <option value="week" selected>Last 7 Days</option>
                <option value="month">Last 30 Days</option>
                <option value="year">Last Year</option>
            </select>
            <button class="button" id="ucfc-refresh-analytics">
                <span class="dashicons dashicons-update"></span> Refresh
            </button>
        </div>
        
        <div class="ucfc-analytics-grid">
            <!-- Sales Chart -->
            <div class="ucfc-analytics-card ucfc-full-width">
                <h3>Sales Trend</h3>
                <canvas id="ucfc-sales-chart"></canvas>
            </div>
            
            <!-- Category Performance -->
            <div class="ucfc-analytics-card">
                <h3>Category Performance</h3>
                <canvas id="ucfc-category-chart"></canvas>
            </div>
            
            <!-- Revenue by Hour -->
            <div class="ucfc-analytics-card">
                <h3>Peak Hours</h3>
                <canvas id="ucfc-hourly-chart"></canvas>
            </div>
            
            <!-- Best & Worst Performers -->
            <div class="ucfc-analytics-card ucfc-full-width">
                <h3>Performance Insights</h3>
                <div class="ucfc-insights-grid">
                    <div class="ucfc-insight ucfc-best">
                        <h4>🏆 Best Performers</h4>
                        <div id="ucfc-best-items"></div>
                    </div>
                    <div class="ucfc-insight ucfc-worst">
                        <h4>⚠️ Needs Attention</h4>
                        <div id="ucfc-worst-items"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        jQuery(document).ready(function($) {
            ucfcLoadAnalytics();
        });
    </script>
    <?php
}

// Import/Export Page
function ucfc_render_import_export() {
    ?>
    <div class="wrap ucfc-import-export">
        <h1><span class="dashicons dashicons-database-import"></span> Import / Export Menu</h1>
        
        <div class="ucfc-import-export-grid">
            <!-- Export Section -->
            <div class="ucfc-export-section">
                <h2>Export Menu</h2>
                <p>Download your menu items in CSV format for backup or editing in Excel.</p>
                
                <div class="ucfc-export-options">
                    <label>
                        <input type="checkbox" checked> Include Images (URLs)
                    </label>
                    <label>
                        <input type="checkbox" checked> Include Pricing
                    </label>
                    <label>
                        <input type="checkbox" checked> Include Cost Data
                    </label>
                    <label>
                        <input type="checkbox" checked> Include Categories
                    </label>
                </div>
                
                <button class="button button-primary button-large" id="ucfc-export-csv">
                    <span class="dashicons dashicons-download"></span> Export to CSV
                </button>
                
                <button class="button button-large" id="ucfc-export-pdf">
                    <span class="dashicons dashicons-media-document"></span> Export PDF Menu
                </button>
            </div>
            
            <!-- Import Section -->
            <div class="ucfc-import-section">
                <h2>Import Menu</h2>
                <p>Upload a CSV file to bulk add or update menu items.</p>
                
                <div class="ucfc-import-dropzone" id="ucfc-csv-dropzone">
                    <span class="dashicons dashicons-upload"></span>
                    <p>Drag & drop CSV file here or click to browse</p>
                    <input type="file" id="ucfc-csv-file" accept=".csv" style="display:none;">
                </div>
                
                <div class="ucfc-import-preview" id="ucfc-import-preview" style="display:none;">
                    <h3>Preview Import</h3>
                    <div id="ucfc-preview-table"></div>
                    <button class="button button-primary button-large" id="ucfc-confirm-import">
                        <span class="dashicons dashicons-yes"></span> Confirm Import
                    </button>
                </div>
                
                <div class="ucfc-import-help">
                    <h3>CSV Format</h3>
                    <p>Your CSV should have these columns:</p>
                    <code>Name, Description, Price, Cost, Category, Image URL, Spice Level, Calories</code>
                    <a href="<?php echo admin_url('admin-ajax.php?action=ucfc_download_sample_csv'); ?>" class="button">
                        <span class="dashicons dashicons-media-spreadsheet"></span> Download Sample CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Helper Functions
function ucfc_calculate_total_revenue() {
    // Placeholder - integrate with actual order system
    return 12450.00;
}

function ucfc_get_average_rating() {
    global $wpdb;
    $avg = $wpdb->get_var("
        SELECT AVG(meta_value) 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_ucfc_rating' 
        AND meta_value > 0
    ");
    return $avg ? $avg : 4.5;
}

function ucfc_get_orders_today() {
    // Placeholder - integrate with order system
    return 87;
}

function ucfc_get_top_sellers($limit = 5) {
    $items = get_posts(array(
        'post_type' => 'menu_item',
        'posts_per_page' => $limit,
        'meta_key' => '_ucfc_sold_count',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    ));
    
    $sellers = array();
    foreach ($items as $item) {
        $price = (float) get_post_meta($item->ID, '_ucfc_price', true);
        $sold = (int) get_post_meta($item->ID, '_ucfc_sold_count', true);
        $is_popular = get_post_meta($item->ID, '_ucfc_popular', true);
        
        $sellers[] = array(
            'name' => $item->post_title,
            'price' => $price,
            'sold' => $sold,
            'revenue' => $price * $sold,
            'is_popular' => $is_popular,
            'trend' => rand(-5, 25),
            'trend_class' => rand(0, 1) ? 'ucfc-trend-up' : 'ucfc-trend-down',
            'trend_icon' => rand(0, 1) ? 'up' : 'down'
        );
    }
    
    return $sellers;
}

function ucfc_get_menu_alerts() {
    $alerts = array();
    
    // Low stock items
    $low_stock = get_posts(array(
        'post_type' => 'menu_item',
        'meta_query' => array(
            array(
                'key' => '_ucfc_stock',
                'value' => 5,
                'compare' => '<',
                'type' => 'NUMERIC'
            )
        )
    ));
    
    foreach ($low_stock as $item) {
        $stock = get_post_meta($item->ID, '_ucfc_stock', true);
        $alerts[] = array(
            'type' => 'warning',
            'icon' => 'warning',
            'message' => sprintf('"%s" is low on stock (%d portions left)', $item->post_title, $stock),
            'action' => array(
                'label' => 'Edit',
                'url' => get_edit_post_link($item->ID)
            )
        );
    }
    
    // Items not sold in 7 days
    $unsold = get_posts(array(
        'post_type' => 'menu_item',
        'meta_query' => array(
            array(
                'key' => '_ucfc_last_sold',
                'value' => strtotime('-7 days'),
                'compare' => '<',
                'type' => 'NUMERIC'
            )
        ),
        'posts_per_page' => 3
    ));
    
    foreach ($unsold as $item) {
        $alerts[] = array(
            'type' => 'info',
            'icon' => 'info',
            'message' => sprintf('"%s" hasn\'t sold in 7 days - consider promoting or removing', $item->post_title),
            'action' => array(
                'label' => 'View',
                'url' => get_edit_post_link($item->ID)
            )
        );
    }
    
    return $alerts;
}

// AJAX Handlers
function ucfc_ajax_quick_edit_form() {
    check_ajax_referer('ucfc_menu_dashboard_nonce', 'nonce');
    
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    
    if ($item_id) {
        $item = get_post($item_id);
        $price = get_post_meta($item_id, '_ucfc_price', true);
        $cost = get_post_meta($item_id, '_ucfc_cost', true);
        $stock = get_post_meta($item_id, '_ucfc_stock', true);
        $calories = get_post_meta($item_id, '_ucfc_calories', true);
        $spice = get_post_meta($item_id, '_ucfc_spice_level', true);
        $categories = wp_get_post_terms($item_id, 'menu_category', array('fields' => 'ids'));
        
        $profit = $price - $cost;
        $margin = $price > 0 ? ($profit / $price) * 100 : 0;
        
        ob_start();
        ?>
        <form id="ucfc-quick-edit-form-inner" data-item-id="<?php echo $item_id; ?>">
            <div class="ucfc-form-row">
                <div class="ucfc-form-col">
                    <label>Item Name</label>
                    <input type="text" name="item_name" value="<?php echo esc_attr($item->post_title); ?>" required>
                </div>
            </div>
            
            <div class="ucfc-form-row ucfc-row-2">
                <div class="ucfc-form-col">
                    <label>Price ($)</label>
                    <input type="number" name="price" value="<?php echo esc_attr($price); ?>" step="0.01" required>
                </div>
                <div class="ucfc-form-col">
                    <label>Cost ($)</label>
                    <input type="number" name="cost" value="<?php echo esc_attr($cost); ?>" step="0.01">
                    <small>Profit: $<?php echo number_format($profit, 2); ?> (<?php echo number_format($margin, 1); ?>%)</small>
                </div>
            </div>
            
            <div class="ucfc-form-row">
                <label>Category</label>
                <?php 
                $all_categories = get_terms(array('taxonomy' => 'menu_category', 'hide_empty' => false));
                foreach ($all_categories as $cat) {
                    $checked = in_array($cat->term_id, $categories) ? 'checked' : '';
                    echo '<label class="ucfc-checkbox"><input type="checkbox" name="categories[]" value="' . $cat->term_id . '" ' . $checked . '> ' . esc_html($cat->name) . '</label>';
                }
                ?>
            </div>
            
            <div class="ucfc-form-row ucfc-row-3">
                <div class="ucfc-form-col">
                    <label>Stock</label>
                    <input type="number" name="stock" value="<?php echo esc_attr($stock); ?>">
                </div>
                <div class="ucfc-form-col">
                    <label>Calories</label>
                    <input type="number" name="calories" value="<?php echo esc_attr($calories); ?>">
                </div>
                <div class="ucfc-form-col">
                    <label>Spice Level</label>
                    <select name="spice_level">
                        <option value="0" <?php selected($spice, 0); ?>>None</option>
                        <option value="1" <?php selected($spice, 1); ?>>Mild 🌶️</option>
                        <option value="2" <?php selected($spice, 2); ?>>Medium 🌶️🌶️</option>
                        <option value="3" <?php selected($spice, 3); ?>>Hot 🌶️🌶️🌶️</option>
                        <option value="4" <?php selected($spice, 4); ?>>Extra Hot 🌶️🌶️🌶️🌶️</option>
                    </select>
                </div>
            </div>
            
            <div class="ucfc-form-row">
                <label>Description</label>
                <textarea name="description" rows="3"><?php echo esc_textarea($item->post_content); ?></textarea>
            </div>
            
            <div class="ucfc-form-actions">
                <button type="submit" class="button button-primary button-large">
                    <span class="dashicons dashicons-yes"></span> Save Changes
                </button>
                <button type="button" class="button button-large ucfc-modal-close">Cancel</button>
                <button type="button" class="button button-link-delete ucfc-delete-item" data-item-id="<?php echo $item_id; ?>">
                    <span class="dashicons dashicons-trash"></span> Delete Item
                </button>
            </div>
        </form>
        <?php
        $html = ob_get_clean();
        wp_send_json_success(array('html' => $html));
    }
    
    wp_send_json_error('Invalid item ID');
}
add_action('wp_ajax_ucfc_quick_edit_form', 'ucfc_ajax_quick_edit_form');

function ucfc_ajax_save_quick_edit() {
    check_ajax_referer('ucfc_menu_dashboard_nonce', 'nonce');
    
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    
    if (!$item_id) {
        wp_send_json_error('Invalid item ID');
    }
    
    // Update post
    wp_update_post(array(
        'ID' => $item_id,
        'post_title' => sanitize_text_field($_POST['item_name']),
        'post_content' => wp_kses_post($_POST['description'])
    ));
    
    // Update meta
    update_post_meta($item_id, '_ucfc_price', floatval($_POST['price']));
    update_post_meta($item_id, '_ucfc_cost', floatval($_POST['cost']));
    update_post_meta($item_id, '_ucfc_stock', intval($_POST['stock']));
    update_post_meta($item_id, '_ucfc_calories', intval($_POST['calories']));
    update_post_meta($item_id, '_ucfc_spice_level', intval($_POST['spice_level']));
    
    // Update categories
    if (isset($_POST['categories'])) {
        $categories = array_map('intval', $_POST['categories']);
        wp_set_post_terms($item_id, $categories, 'menu_category');
    }
    
    wp_send_json_success('Item updated successfully');
}
add_action('wp_ajax_ucfc_save_quick_edit', 'ucfc_ajax_save_quick_edit');

function ucfc_ajax_update_menu_order() {
    check_ajax_referer('ucfc_menu_dashboard_nonce', 'nonce');
    
    $order = isset($_POST['order']) ? $_POST['order'] : array();
    
    foreach ($order as $position => $item_id) {
        wp_update_post(array(
            'ID' => intval($item_id),
            'menu_order' => $position
        ));
    }
    
    wp_send_json_success('Menu order updated');
}
add_action('wp_ajax_ucfc_update_menu_order', 'ucfc_ajax_update_menu_order');

function ucfc_ajax_export_csv() {
    check_ajax_referer('ucfc_menu_dashboard_nonce', 'nonce');
    
    $items = get_posts(array(
        'post_type' => 'menu_item',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ));
    
    $csv_data = array();
    $csv_data[] = array('Name', 'Description', 'Price', 'Cost', 'Category', 'Image URL', 'Spice Level', 'Calories', 'Stock');
    
    foreach ($items as $item) {
        $categories = wp_get_post_terms($item->ID, 'menu_category', array('fields' => 'names'));
        $csv_data[] = array(
            $item->post_title,
            strip_tags($item->post_content),
            get_post_meta($item->ID, '_ucfc_price', true),
            get_post_meta($item->ID, '_ucfc_cost', true),
            implode(';', $categories),
            get_the_post_thumbnail_url($item->ID, 'full'),
            get_post_meta($item->ID, '_ucfc_spice_level', true),
            get_post_meta($item->ID, '_ucfc_calories', true),
            get_post_meta($item->ID, '_ucfc_stock', true)
        );
    }
    
    wp_send_json_success(array('data' => $csv_data));
}
add_action('wp_ajax_ucfc_export_csv', 'ucfc_ajax_export_csv');

function ucfc_ajax_delete_item() {
    check_ajax_referer('ucfc_menu_dashboard_nonce', 'nonce');
    
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    
    if (!$item_id) {
        wp_send_json_error('Invalid item ID');
    }
    
    wp_delete_post($item_id, true);
    wp_send_json_success('Item deleted successfully');
}
add_action('wp_ajax_ucfc_delete_item', 'ucfc_ajax_delete_item');

function ucfc_ajax_duplicate_item() {
    check_ajax_referer('ucfc_menu_dashboard_nonce', 'nonce');
    
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    
    if (!$item_id) {
        wp_send_json_error('Invalid item ID');
    }
    
    $original = get_post($item_id);
    $new_item = array(
        'post_title' => $original->post_title . ' (Copy)',
        'post_content' => $original->post_content,
        'post_status' => 'publish',
        'post_type' => 'menu_item'
    );
    
    $new_id = wp_insert_post($new_item);
    
    if ($new_id) {
        // Copy all meta
        $meta = get_post_meta($item_id);
        foreach ($meta as $key => $values) {
            update_post_meta($new_id, $key, $values[0]);
        }
        
        // Copy categories
        $categories = wp_get_post_terms($item_id, 'menu_category', array('fields' => 'ids'));
        wp_set_post_terms($new_id, $categories, 'menu_category');
        
        // Copy thumbnail
        $thumbnail_id = get_post_thumbnail_id($item_id);
        if ($thumbnail_id) {
            set_post_thumbnail($new_id, $thumbnail_id);
        }
        
        wp_send_json_success(array('new_id' => $new_id));
    }
    
    wp_send_json_error('Failed to duplicate item');
}
add_action('wp_ajax_ucfc_duplicate_item', 'ucfc_ajax_duplicate_item');

// AJAX handler for adding new category
function ucfc_ajax_add_category() {
    check_ajax_referer('ucfc_menu_dashboard_nonce', 'nonce');
    
    $category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
    
    if (empty($category_name)) {
        wp_send_json_error('Category name is required');
    }
    
    $result = wp_insert_term($category_name, 'menu_category');
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    $term = get_term($result['term_id'], 'menu_category');
    wp_send_json_success(array(
        'term_id' => $term->term_id,
        'name' => $term->name,
        'slug' => $term->slug
    ));
}
add_action('wp_ajax_ucfc_add_category', 'ucfc_ajax_add_category');

// AJAX handler for creating new menu item
function ucfc_ajax_create_item() {
    check_ajax_referer('ucfc_menu_dashboard_nonce', 'nonce');
    
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : 'New Menu Item';
    $description = isset($_POST['description']) ? wp_kses_post($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
    
    $new_item = array(
        'post_title' => $title,
        'post_content' => $description,
        'post_status' => 'publish',
        'post_type' => 'menu_item'
    );
    
    $item_id = wp_insert_post($new_item);
    
    if ($item_id) {
        // Set price
        update_post_meta($item_id, '_ucfc_price', $price);
        update_post_meta($item_id, '_ucfc_cost', 0);
        update_post_meta($item_id, '_ucfc_stock', 100);
        update_post_meta($item_id, '_ucfc_sold_count', 0);
        update_post_meta($item_id, '_ucfc_rating', 5.0);
        update_post_meta($item_id, '_ucfc_profit_margin', 0);
        
        // Set category if provided
        if ($category) {
            wp_set_post_terms($item_id, array($category), 'menu_category');
        }
        
        wp_send_json_success(array(
            'item_id' => $item_id,
            'title' => $title,
            'price' => $price,
            'edit_link' => get_edit_post_link($item_id)
        ));
    }
    
    wp_send_json_error('Failed to create item');
}
add_action('wp_ajax_ucfc_create_item', 'ucfc_create_item');

// Batch import all landing page menu items
function ucfc_ajax_batch_import_menu_items() {
    // Menu items data
    $menu_data = array(
        'categories' => array(
            array('name' => 'Fried Chicken', 'slug' => 'chicken'),
            array('name' => 'Wednesday Wings', 'slug' => 'wings'),
            array('name' => 'Friday Catfish', 'slug' => 'catfish')
        ),
        'items' => array(
            array('title' => 'Wing Combo', 'desc' => 'Get your fix with 10 pieces of golden, crispy wings. Each piece is hand-breaded with our secret family recipe!', 'price' => 12, 'cost' => 5.5, 'cat' => 'chicken', 'stock' => 100, 'sold' => 156, 'rating' => 4.5),
            array('title' => 'Family Box', 'desc' => 'Our bestseller! 20 pieces of mouthwatering fried chicken. Crispy, seasoned, and made fresh just for you.', 'price' => 21, 'cost' => 9.5, 'cat' => 'chicken', 'stock' => 150, 'sold' => 287, 'rating' => 4.8, 'featured' => true),
            array('title' => 'Mega Feast', 'desc' => 'Feed the whole crew! 30 pieces of our legendary chicken plus 3 sides. Perfect for parties, squad nights, or serious leftovers.', 'price' => 30, 'cost' => 13.5, 'cat' => 'chicken', 'stock' => 80, 'sold' => 198, 'rating' => 4.7),
            array('title' => 'Wednesday Wing Deal', 'desc' => 'Wednesday special! 10 pieces of our signature wings with 2 sides. Perfect mid-week treat!', 'price' => 12, 'cost' => 5.5, 'cat' => 'wings', 'stock' => 200, 'sold' => 243, 'rating' => 4.6, 'featured' => true),
            array('title' => 'Wednesday Family Deal', 'desc' => 'Wednesday special! 20 pieces of our delicious fried chicken with 2 sides. Feed the whole family!', 'price' => 21, 'cost' => 9.5, 'cat' => 'wings', 'stock' => 150, 'sold' => 187, 'rating' => 4.5),
            array('title' => 'Wednesday Mega Deal', 'desc' => 'Wednesday special! 30 pieces of our legendary chicken with 3 sides. Perfect for squad nights!', 'price' => 30, 'cost' => 13.5, 'cat' => 'wings', 'stock' => 100, 'sold' => 145, 'rating' => 4.4),
            array('title' => '1-Piece Catfish Meal', 'desc' => 'Friday Catfish Special! One piece of golden fried catfish with hush puppies and 2 sides.', 'price' => 9, 'cost' => 4, 'cat' => 'catfish', 'stock' => 120, 'sold' => 178, 'rating' => 4.7, 'featured' => true),
            array('title' => '2-Piece Catfish Meal', 'desc' => 'Friday Catfish Special! Two pieces of golden fried catfish with hush puppies and 2 sides.', 'price' => 12, 'cost' => 5.5, 'cat' => 'catfish', 'stock' => 100, 'sold' => 134, 'rating' => 4.6)
        )
    );
    
    $category_ids = array();
    $categories_created = 0;
    
    // Create categories
    foreach ($menu_data['categories'] as $cat) {
        $term = term_exists($cat['name'], 'menu_category');
        if (!$term) {
            $result = wp_insert_term($cat['name'], 'menu_category', array('slug' => $cat['slug']));
            if (!is_wp_error($result)) {
                $category_ids[$cat['slug']] = $result['term_id'];
                $categories_created++;
            }
        } else {
            $category_ids[$cat['slug']] = $term['term_id'];
        }
    }
    
    $imported = 0;
    $skipped = 0;
    
    // Import menu items
    foreach ($menu_data['items'] as $item) {
        // Check if exists
        $existing = get_page_by_title($item['title'], OBJECT, 'menu_item');
        if ($existing) {
            $skipped++;
            continue;
        }
        
        // Create post
        $post_id = wp_insert_post(array(
            'post_title' => $item['title'],
            'post_content' => $item['desc'],
            'post_excerpt' => wp_trim_words($item['desc'], 15),
            'post_status' => 'publish',
            'post_type' => 'menu_item',
            'post_author' => 1
        ));
        
        if ($post_id) {
            // Add metadata
            update_post_meta($post_id, '_ucfc_price', $item['price']);
            update_post_meta($post_id, '_ucfc_cost', $item['cost']);
            update_post_meta($post_id, '_ucfc_stock', $item['stock']);
            update_post_meta($post_id, '_ucfc_sold_count', $item['sold']);
            update_post_meta($post_id, '_ucfc_rating', $item['rating']);
            
            $profit_margin = (($item['price'] - $item['cost']) / $item['price']) * 100;
            update_post_meta($post_id, '_ucfc_profit_margin', round($profit_margin));
            
            if (isset($item['featured']) && $item['featured']) {
                update_post_meta($post_id, '_menu_item_is_featured', '1');
            }
            
            // Set category
            if (isset($category_ids[$item['cat']])) {
                wp_set_post_terms($post_id, array($category_ids[$item['cat']]), 'menu_category');
            }
            
            $imported++;
        }
    }
    
    wp_send_json_success(array(
        'imported' => $imported,
        'skipped' => $skipped,
        'categories' => $categories_created
    ));
}
add_action('wp_ajax_ucfc_batch_import_menu_items', 'ucfc_ajax_batch_import_menu_items');

// Render Batch Import Page
function ucfc_render_batch_import() {
    ?>
    <div class="wrap ucfc-menu-dashboard">
        <h1>Batch Import Landing Page Menu Items</h1>
        <div class="ucfc-dashboard-section">
            <p>This will import all menu items from your landing page template into the database.</p>
            <p>Items include: Wing Combo, Family Box, Mega Feast, Wednesday Deals, and Friday Catfish meals.</p>
            
            <button class="button button-primary button-hero" id="ucfc-start-batch-import">
                <span class="dashicons dashicons-database-import"></span> Import All Menu Items Now
            </button>
            
            <div id="ucfc-batch-result" style="margin-top: 30px; display:none;">
                <!-- Results will appear here -->
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#ucfc-start-batch-import').on('click', function() {
            const button = $(this);
            button.prop('disabled', true).text('Importing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ucfc_batch_import_menu_items'
                },
                success: function(response) {
                    $('#ucfc-batch-result').show();
                    
                    if (response.success) {
                        $('#ucfc-batch-result').html(`
                            <div class="notice notice-success" style="padding: 20px; font-size: 16px;">
                                <h2>✅ Import Successful!</h2>
                                <p><strong>${response.data.imported}</strong> menu items imported</p>
                                <p><strong>${response.data.skipped}</strong> items skipped (already exist)</p>
                                <p><strong>${response.data.categories}</strong> categories created</p>
                                <p><a href="<?php echo admin_url('admin.php?page=ucfc-menu-dashboard'); ?>" class="button button-primary">View Menu Dashboard</a></p>
                            </div>
                        `);
                    } else {
                        $('#ucfc-batch-result').html(`
                            <div class="notice notice-error">
                                <h2>❌ Import Failed</h2>
                                <p>${response.data}</p>
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#ucfc-batch-result').show().html(`
                        <div class="notice notice-error">
                            <p>Error connecting to server</p>
                        </div>
                    `);
                },
                complete: function() {
                    button.prop('disabled', false).html('<span class="dashicons dashicons-database-import"></span> Import All Menu Items Now');
                }
            });
        });
    });
    </script>
    <?php
}
