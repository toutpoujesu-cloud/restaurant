<?php
/**
 * Orders Admin Dashboard
 * 
 * Complete order management system for restaurant staff:
 * - View all orders with filters
 * - Order details page
 * - Status management
 * - Order statistics
 * - Print functionality
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Orders menu to WordPress admin
 */
function ucfc_add_orders_dashboard() {
    add_menu_page(
        'Orders',
        'Orders',
        'manage_options',
        'ucfc-orders',
        'ucfc_render_orders_dashboard',
        'dashicons-cart',
        25
    );
    
    add_submenu_page(
        'ucfc-orders',
        'All Orders',
        'All Orders',
        'manage_options',
        'ucfc-orders',
        'ucfc_render_orders_dashboard'
    );
    
    add_submenu_page(
        'ucfc-orders',
        'Order Stats',
        'Statistics',
        'manage_options',
        'ucfc-orders-stats',
        'ucfc_render_orders_stats'
    );
}
add_action('admin_menu', 'ucfc_add_orders_dashboard');

/**
 * Render orders dashboard
 */
function ucfc_render_orders_dashboard() {
    global $wpdb;
    
    // Get filter parameters
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $per_page = 20;
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $offset = ($paged - 1) * $per_page;
    
    // Build query
    $where = ['1=1'];
    if ($status_filter !== 'all') {
        $where[] = $wpdb->prepare('order_status = %s', $status_filter);
    }
    if ($search) {
        $where[] = $wpdb->prepare('(order_number LIKE %s OR customer_name LIKE %s OR customer_email LIKE %s)', 
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%'
        );
    }
    
    $where_sql = implode(' AND ', $where);
    
    // Get orders
    $orders = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}orders 
        WHERE $where_sql 
        ORDER BY created_at DESC 
        LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));
    
    // Get total count
    $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}orders WHERE $where_sql");
    $total_pages = ceil($total_orders / $per_page);
    
    // Get status counts
    $status_counts = $wpdb->get_results(
        "SELECT order_status, COUNT(*) as count FROM {$wpdb->prefix}orders GROUP BY order_status",
        OBJECT_K
    );
    
    ?>
    <div class="wrap ucfc-orders-dashboard">
        <h1 class="wp-heading-inline">Orders</h1>
        <a href="#" class="page-title-action" onclick="window.print(); return false;">
            <span class="dashicons dashicons-printer"></span> Print Orders
        </a>
        <hr class="wp-header-end">
        
        <!-- Status Filters -->
        <ul class="subsubsub">
            <li class="all">
                <a href="?page=ucfc-orders" class="<?php echo $status_filter === 'all' ? 'current' : ''; ?>">
                    All <span class="count">(<?php echo $total_orders; ?>)</span>
                </a> |
            </li>
            <li class="pending">
                <a href="?page=ucfc-orders&status=pending" class="<?php echo $status_filter === 'pending' ? 'current' : ''; ?>">
                    Pending <span class="count">(<?php echo isset($status_counts['pending']) ? $status_counts['pending']->count : 0; ?>)</span>
                </a> |
            </li>
            <li class="confirmed">
                <a href="?page=ucfc-orders&status=confirmed" class="<?php echo $status_filter === 'confirmed' ? 'current' : ''; ?>">
                    Confirmed <span class="count">(<?php echo isset($status_counts['confirmed']) ? $status_counts['confirmed']->count : 0; ?>)</span>
                </a> |
            </li>
            <li class="preparing">
                <a href="?page=ucfc-orders&status=preparing" class="<?php echo $status_filter === 'preparing' ? 'current' : ''; ?>">
                    Preparing <span class="count">(<?php echo isset($status_counts['preparing']) ? $status_counts['preparing']->count : 0; ?>)</span>
                </a> |
            </li>
            <li class="ready">
                <a href="?page=ucfc-orders&status=ready" class="<?php echo $status_filter === 'ready' ? 'current' : ''; ?>">
                    Ready <span class="count">(<?php echo isset($status_counts['ready']) ? $status_counts['ready']->count : 0; ?>)</span>
                </a> |
            </li>
            <li class="completed">
                <a href="?page=ucfc-orders&status=completed" class="<?php echo $status_filter === 'completed' ? 'current' : ''; ?>">
                    Completed <span class="count">(<?php echo isset($status_counts['completed']) ? $status_counts['completed']->count : 0; ?>)</span>
                </a> |
            </li>
            <li class="cancelled">
                <a href="?page=ucfc-orders&status=cancelled" class="<?php echo $status_filter === 'cancelled' ? 'current' : ''; ?>">
                    Cancelled <span class="count">(<?php echo isset($status_counts['cancelled']) ? $status_counts['cancelled']->count : 0; ?>)</span>
                </a>
            </li>
        </ul>
        
        <!-- Search Box -->
        <p class="search-box">
            <label class="screen-reader-text" for="order-search-input">Search Orders:</label>
            <input type="search" id="order-search-input" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search orders...">
            <button type="button" class="button" onclick="window.location.href='?page=ucfc-orders&s=' + document.getElementById('order-search-input').value;">Search Orders</button>
        </p>
        
        <!-- Orders Table -->
        <table class="wp-list-table widefat fixed striped orders">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            <div class="no-orders">
                                <span class="dashicons dashicons-cart" style="font-size: 48px; opacity: 0.3;"></span>
                                <p>No orders found</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $items = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}order_items WHERE order_id = %d",
                            $order->id
                        ));
                        $item_count = array_sum(array_column($items, 'quantity'));
                        ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="?page=ucfc-orders&action=view&order_id=<?php echo $order->id; ?>">
                                        #<?php echo esc_html($order->order_number); ?>
                                    </a>
                                </strong>
                            </td>
                            <td>
                                <?php echo esc_html($order->customer_name); ?><br>
                                <small><?php echo esc_html($order->customer_email); ?></small>
                            </td>
                            <td><?php echo $item_count; ?> item<?php echo $item_count !== 1 ? 's' : ''; ?></td>
                            <td><strong>$<?php echo number_format($order->total, 2); ?></strong></td>
                            <td>
                                <span class="order-type-badge order-type-<?php echo $order->order_type; ?>">
                                    <?php echo ucfirst($order->order_type); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo ucfc_render_order_status_badge($order->order_status, $order->id); ?>
                            </td>
                            <td>
                                <span class="payment-status-badge payment-<?php echo $order->payment_status; ?>">
                                    <?php echo ucfirst($order->payment_status); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo date('M j, Y', strtotime($order->created_at)); ?><br>
                                <small><?php echo date('g:i A', strtotime($order->created_at)); ?></small>
                            </td>
                            <td>
                                <a href="?page=ucfc-orders&action=view&order_id=<?php echo $order->id; ?>" class="button button-small">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $total_orders; ?> items</span>
                    <span class="pagination-links">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a class="<?php echo $paged === $i ? 'current' : ''; ?>" 
                               href="?page=ucfc-orders&status=<?php echo $status_filter; ?>&paged=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
    .ucfc-orders-dashboard {
        background: white;
        padding: 20px;
        margin: 20px 0;
        border-radius: 8px;
    }
    .order-type-badge, .payment-status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .order-type-pickup { background: #e3f2fd; color: #1976d2; }
    .order-type-delivery { background: #fff3e0; color: #f57c00; }
    .order-type-dine-in { background: #f3e5f5; color: #7b1fa2; }
    .payment-pending { background: #fff8e1; color: #f57f17; }
    .payment-paid { background: #e8f5e9; color: #388e3c; }
    .payment-failed { background: #ffebee; color: #c62828; }
    .no-orders { text-align: center; color: #999; }
    </style>
    <?php
}

/**
 * Render order status badge with quick update dropdown
 */
function ucfc_render_order_status_badge($status, $order_id) {
    $statuses = [
        'pending' => ['label' => 'Pending', 'color' => '#ffc107'],
        'confirmed' => ['label' => 'Confirmed', 'color' => '#2196f3'],
        'preparing' => ['label' => 'Preparing', 'color' => '#ff9800'],
        'ready' => ['label' => 'Ready', 'color' => '#4caf50'],
        'completed' => ['label' => 'Completed', 'color' => '#8bc34a'],
        'cancelled' => ['label' => 'Cancelled', 'color' => '#f44336']
    ];
    
    $current = $statuses[$status];
    
    ob_start();
    ?>
    <div class="order-status-dropdown" style="position: relative; display: inline-block;">
        <button class="order-status-btn" style="background: <?php echo $current['color']; ?>; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">
            <?php echo $current['label']; ?> <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 14px; margin-top: 2px;"></span>
        </button>
        <div class="status-dropdown-menu" style="display: none; position: absolute; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.15); border-radius: 4px; z-index: 1000; min-width: 150px; margin-top: 5px;">
            <?php foreach ($statuses as $key => $stat): ?>
                <?php if ($key !== $status): ?>
                    <a href="#" class="status-option" data-order-id="<?php echo $order_id; ?>" data-status="<?php echo $key; ?>" style="display: block; padding: 8px 12px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                        <?php echo $stat['label']; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('.order-status-btn').on('click', function(e) {
            e.stopPropagation();
            $('.status-dropdown-menu').not($(this).siblings('.status-dropdown-menu')).hide();
            $(this).siblings('.status-dropdown-menu').toggle();
        });
        
        $(document).on('click', function() {
            $('.status-dropdown-menu').hide();
        });
        
        $('.status-option').on('click', function(e) {
            e.preventDefault();
            var orderId = $(this).data('order-id');
            var newStatus = $(this).data('status');
            
            if (confirm('Change order status to ' + $(this).text().trim() + '?')) {
                $.post(ajaxurl, {
                    action: 'ucfc_update_order_status',
                    order_id: orderId,
                    status: newStatus,
                    nonce: '<?php echo wp_create_nonce('ucfc_order_status'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating status');
                    }
                });
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

/**
 * AJAX: Update order status
 */
function ucfc_ajax_update_order_status() {
    check_ajax_referer('ucfc_order_status', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied']);
    }
    
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    
    if (!$order_id || !$new_status) {
        wp_send_json_error(['message' => 'Invalid parameters']);
    }
    
    global $wpdb;
    
    // Get old status
    $old_status = $wpdb->get_var($wpdb->prepare(
        "SELECT order_status FROM {$wpdb->prefix}orders WHERE id = %d",
        $order_id
    ));
    
    // Update order status
    $updated = $wpdb->update(
        $wpdb->prefix . 'orders',
        ['order_status' => $new_status],
        ['id' => $order_id],
        ['%s'],
        ['%d']
    );
    
    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to update status']);
    }
    
    // Log status change
    $wpdb->insert(
        $wpdb->prefix . 'order_status_history',
        [
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'changed_by' => get_current_user_id()
        ],
        ['%d', '%s', '%s', '%d']
    );
    
    // Trigger SMS notification hook for status change
    do_action('ucfc_order_status_changed', $order_id, $new_status, $old_status);
    
    wp_send_json_success(['message' => 'Status updated']);
}
add_action('wp_ajax_ucfc_update_order_status', 'ucfc_ajax_update_order_status');

/**
 * Render order statistics
 */
function ucfc_render_orders_stats() {
    global $wpdb;
    
    // Get date range
    $range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : '7days';
    $date_from = date('Y-m-d 00:00:00', strtotime("-$range"));
    
    // Get stats
    $total_orders = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}orders WHERE created_at >= %s",
        $date_from
    ));
    
    $total_revenue = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(total) FROM {$wpdb->prefix}orders WHERE created_at >= %s AND payment_status = 'paid'",
        $date_from
    ));
    
    $avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;
    
    ?>
    <div class="wrap">
        <h1>Order Statistics</h1>
        
        <div class="stat-cards">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p class="stat-value"><?php echo number_format($total_orders); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p class="stat-value">$<?php echo number_format($total_revenue, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Average Order Value</h3>
                <p class="stat-value">$<?php echo number_format($avg_order_value, 2); ?></p>
            </div>
        </div>
    </div>
    
    <style>
    .stat-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }
    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .stat-card h3 {
        margin: 0 0 10px 0;
        color: #666;
        font-size: 14px;
        text-transform: uppercase;
    }
    .stat-value {
        font-size: 36px;
        font-weight: 600;
        color: #d92027;
        margin: 0;
    }
    </style>
    <?php
}
?>
