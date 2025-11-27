<?php
/**
 * Template Name: My Orders
 * 
 * Customer order tracking and history page
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

get_header();

// Check if user is logged in or has guest orders
$user_id = get_current_user_id();
$guest_email = isset($_COOKIE['ucfc_guest_email']) ? sanitize_email($_COOKIE['ucfc_guest_email']) : '';

// Guest login form if not logged in and no email in cookie
if (!$user_id && empty($guest_email)) {
    ?>
    <div class="order-tracking-page">
        <div class="container">
            <div class="tracking-login-card">
                <div class="login-header">
                    <i class="fas fa-receipt"></i>
                    <h1>Track Your Orders</h1>
                    <p>Enter your email to view your order history</p>
                </div>
                
                <form id="guest-tracking-form" class="guest-form">
                    <div class="form-group">
                        <label for="guest_email">Email Address</label>
                        <input type="email" id="guest_email" name="guest_email" required placeholder="you@example.com">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> View My Orders
                    </button>
                </form>
                
                <div class="login-footer">
                    <p>Have an account? <a href="<?php echo wp_login_url(get_permalink()); ?>">Log in</a> for faster checkout and order tracking.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#guest-tracking-form').on('submit', function(e) {
            e.preventDefault();
            const email = $('#guest_email').val();
            
            // Set cookie and reload
            document.cookie = 'ucfc_guest_email=' + email + '; path=/; max-age=2592000'; // 30 days
            location.reload();
        });
    });
    </script>
    <?php
    get_footer();
    return;
}

// Get orders for user or guest
global $wpdb;

if ($user_id) {
    // Logged-in user orders
    $orders = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}orders WHERE user_id = %d ORDER BY created_at DESC",
        $user_id
    ));
    $display_email = wp_get_current_user()->user_email;
} else {
    // Guest orders by email
    $orders = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}orders WHERE customer_email = %s ORDER BY created_at DESC",
        $guest_email
    ));
    $display_email = $guest_email;
}
?>

<div class="order-tracking-page">
    <div class="container">
        
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <h1><i class="fas fa-receipt"></i> My Orders</h1>
                <p>Viewing orders for: <strong><?php echo esc_html($display_email); ?></strong></p>
            </div>
            <div class="header-actions">
                <?php if (!$user_id): ?>
                    <button id="change-email-btn" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Change Email
                    </button>
                <?php endif; ?>
                <a href="<?php echo home_url('/'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Order
                </a>
            </div>
        </div>
        
        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div class="no-orders-card">
                <i class="fas fa-inbox"></i>
                <h2>No Orders Yet</h2>
                <p>You haven't placed any orders yet. Time to try some delicious chicken!</p>
                <a href="<?php echo home_url('/'); ?>" class="btn btn-primary">
                    <i class="fas fa-utensils"></i> Browse Menu
                </a>
            </div>
        <?php else: ?>
            <div class="orders-grid">
                <?php foreach ($orders as $order): ?>
                    <?php
                    // Get order items
                    $items = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}order_items WHERE order_id = %d",
                        $order->id
                    ));
                    
                    // Calculate time remaining
                    $order_time = strtotime($order->created_at);
                    $estimated_ready_time = $order_time + ($order->estimated_time * 60);
                    $current_time = current_time('timestamp');
                    $time_remaining = max(0, $estimated_ready_time - $current_time);
                    $minutes_remaining = ceil($time_remaining / 60);
                    
                    // Status colors
                    $status_colors = [
                        'pending' => 'yellow',
                        'confirmed' => 'blue',
                        'preparing' => 'orange',
                        'ready' => 'green',
                        'completed' => 'light-green',
                        'cancelled' => 'red'
                    ];
                    $status_color = $status_colors[$order->order_status] ?? 'gray';
                    ?>
                    
                    <div class="order-card" data-order-id="<?php echo $order->id; ?>" data-order-status="<?php echo $order->order_status; ?>">
                        <!-- Order Header -->
                        <div class="order-card-header">
                            <div class="order-info">
                                <h3>Order #<?php echo esc_html($order->order_number); ?></h3>
                                <p class="order-date"><?php echo date('F j, Y \a\t g:i A', strtotime($order->created_at)); ?></p>
                            </div>
                            <div class="order-status status-<?php echo $status_color; ?>">
                                <?php echo ucfirst($order->order_status); ?>
                            </div>
                        </div>
                        
                        <!-- Order Progress -->
                        <div class="order-progress">
                            <div class="progress-bar">
                                <div class="progress-fill progress-<?php echo $order->order_status; ?>"></div>
                            </div>
                            <div class="progress-steps">
                                <div class="progress-step <?php echo in_array($order->order_status, ['pending', 'confirmed', 'preparing', 'ready', 'completed']) ? 'active' : ''; ?>">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Received</span>
                                </div>
                                <div class="progress-step <?php echo in_array($order->order_status, ['confirmed', 'preparing', 'ready', 'completed']) ? 'active' : ''; ?>">
                                    <i class="fas fa-clipboard-check"></i>
                                    <span>Confirmed</span>
                                </div>
                                <div class="progress-step <?php echo in_array($order->order_status, ['preparing', 'ready', 'completed']) ? 'active' : ''; ?>">
                                    <i class="fas fa-fire"></i>
                                    <span>Preparing</span>
                                </div>
                                <div class="progress-step <?php echo in_array($order->order_status, ['ready', 'completed']) ? 'active' : ''; ?>">
                                    <i class="fas fa-check-double"></i>
                                    <span>Ready</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Timeline Button -->
                        <button class="view-timeline-btn" data-order-id="<?php echo $order->id; ?>">
                            <i class="fas fa-history"></i> View Status History
                        </button>
                        
                        <!-- Status Timeline (hidden by default) -->
                        <div class="status-timeline" id="timeline-<?php echo $order->id; ?>" style="display: none;">
                            <div class="timeline-loading">
                                <i class="fas fa-spinner fa-spin"></i> Loading timeline...
                            </div>
                        </div>
                        
                        <!-- Estimated Time -->
                        <?php if (in_array($order->order_status, ['pending', 'confirmed', 'preparing']) && $minutes_remaining > 0): ?>
                            <div class="order-eta">
                                <i class="fas fa-clock"></i>
                                <span>Estimated ready in: <strong><?php echo $minutes_remaining; ?> minutes</strong></span>
                            </div>
                        <?php elseif ($order->order_status === 'ready'): ?>
                            <div class="order-eta ready">
                                <i class="fas fa-check-circle"></i>
                                <span><strong>Your order is ready for <?php echo $order->order_type === 'delivery' ? 'delivery' : 'pickup'; ?>!</strong></span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Order Type -->
                        <div class="order-type-badge type-<?php echo $order->order_type; ?>">
                            <i class="fas fa-<?php echo $order->order_type === 'pickup' ? 'shopping-bag' : ($order->order_type === 'delivery' ? 'truck' : 'utensils'); ?>"></i>
                            <?php echo ucfirst($order->order_type); ?>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="order-items-summary">
                            <h4><?php echo count($items); ?> Item<?php echo count($items) > 1 ? 's' : ''; ?></h4>
                            <ul>
                                <?php foreach ($items as $item): ?>
                                    <li><?php echo esc_html($item->product_name); ?> <span class="qty">×<?php echo $item->quantity; ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Order Total -->
                        <div class="order-total">
                            <span>Total:</span>
                            <strong>$<?php echo number_format($order->total, 2); ?></strong>
                        </div>
                        
                        <!-- Payment Status -->
                        <div class="payment-status payment-<?php echo $order->payment_status; ?>">
                            <i class="fas fa-<?php echo $order->payment_status === 'paid' ? 'check-circle' : 'clock'; ?>"></i>
                            Payment: <?php echo ucfirst($order->payment_status); ?>
                        </div>
                        
                        <!-- Actions -->
                        <div class="order-actions">
                            <a href="<?php echo home_url('/order-confirmation?order=' . $order->order_number); ?>" class="btn btn-secondary">
                                <i class="fas fa-file-invoice"></i> View Receipt
                            </a>
                            <?php if ($order->order_status === 'completed'): ?>
                                <button class="btn btn-primary reorder-btn" data-order-id="<?php echo $order->id; ?>">
                                    <i class="fas fa-redo"></i> Reorder
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<style>
.order-tracking-page {
    padding: 60px 0;
    background: #f5f7fa;
    min-height: 80vh;
}

/* Login Card */
.tracking-login-card {
    max-width: 500px;
    margin: 0 auto;
    background: white;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    text-align: center;
}

.login-header i {
    font-size: 4rem;
    color: #d92027;
    margin-bottom: 20px;
}

.login-header h1 {
    margin-bottom: 10px;
}

.guest-form {
    margin: 30px 0;
}

.guest-form .form-group {
    text-align: left;
}

.login-footer {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #f0f0f0;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding: 30px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.header-content h1 {
    margin: 0 0 10px 0;
    color: #333;
}

.header-content p {
    margin: 0;
    color: #666;
}

.header-actions {
    display: flex;
    gap: 15px;
}

/* No Orders Card */
.no-orders-card {
    text-align: center;
    padding: 80px 40px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.no-orders-card i {
    font-size: 5rem;
    color: #ddd;
    margin-bottom: 20px;
}

/* Orders Grid */
.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 30px;
}

/* Order Card */
.order-card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.order-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.order-info h3 {
    margin: 0 0 5px 0;
    font-size: 1.3rem;
}

.order-date {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.order-status {
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
}

.status-yellow { background: #fff3cd; color: #856404; }
.status-blue { background: #d1ecf1; color: #0c5460; }
.status-orange { background: #ffe5cc; color: #cc5200; }
.status-green { background: #d4edda; color: #155724; }
.status-light-green { background: #c8e6c9; color: #2e7d32; }
.status-red { background: #f8d7da; color: #721c24; }

/* Order Progress */
.order-progress {
    margin: 25px 0;
}

.progress-bar {
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    margin-bottom: 15px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #d92027, #ff6b6b);
    transition: width 0.5s;
}

.progress-pending { width: 25%; }
.progress-confirmed { width: 50%; }
.progress-preparing { width: 75%; }
.progress-ready { width: 100%; }
.progress-completed { width: 100%; }

.progress-steps {
    display: flex;
    justify-content: space-between;
}

.progress-step {
    text-align: center;
    flex: 1;
    color: #ccc;
    font-size: 0.8rem;
}

.progress-step.active {
    color: #d92027;
}

.progress-step i {
    display: block;
    font-size: 1.5rem;
    margin-bottom: 5px;
}

/* ETA */
.order-eta {
    padding: 15px;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 8px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.order-eta.ready {
    background: #d4edda;
    border-left-color: #28a745;
}

.order-eta i {
    font-size: 1.5rem;
    color: #856404;
}

.order-eta.ready i {
    color: #155724;
}

/* Order Type Badge */
.order-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    margin: 15px 0;
}

.type-pickup { background: #e3f2fd; color: #1976d2; }
.type-delivery { background: #ffe5cc; color: #e65100; }
.type-dine-in { background: #f3e5f5; color: #7b1fa2; }

/* Order Items Summary */
.order-items-summary {
    margin: 20px 0;
}

.order-items-summary h4 {
    margin: 0 0 10px 0;
    font-size: 1rem;
    color: #666;
}

.order-items-summary ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.order-items-summary li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
}

.order-items-summary li:last-child {
    border-bottom: none;
}

.qty {
    color: #d92027;
    font-weight: 600;
}

/* Order Total */
.order-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-top: 2px solid #333;
    font-size: 1.3rem;
    margin-top: 15px;
}

.order-total strong {
    color: #d92027;
    font-size: 1.5rem;
}

/* Payment Status */
.payment-status {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    border-radius: 8px;
    font-size: 0.9rem;
    margin: 15px 0;
}

.payment-pending {
    background: #fff3cd;
    color: #856404;
}

.payment-paid {
    background: #d4edda;
    color: #155724;
}

/* Order Actions */
.order-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.order-actions .btn {
    flex: 1;
}

/* Timeline Styles */
.view-timeline-btn {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.view-timeline-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.status-timeline {
    margin-top: 15px;
    padding: 20px;
    background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    border: 2px solid #e0e0e0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.timeline-items {
    position: relative;
    padding-left: 50px;
}

.timeline-items::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 10px;
    bottom: 10px;
    width: 3px;
    background: linear-gradient(to bottom, #d92027 0%, #ff6b6b 50%, #51cf66 100%);
    border-radius: 2px;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
    animation: fadeInUp 0.5s ease-out forwards;
    opacity: 0;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.timeline-item:nth-child(1) { animation-delay: 0.1s; }
.timeline-item:nth-child(2) { animation-delay: 0.2s; }
.timeline-item:nth-child(3) { animation-delay: 0.3s; }
.timeline-item:nth-child(4) { animation-delay: 0.4s; }
.timeline-item:nth-child(5) { animation-delay: 0.5s; }

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -50px;
    top: 0;
    width: 40px;
    height: 40px;
    background: white;
    border: 3px solid #d92027;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    z-index: 1;
}

.timeline-item.active .timeline-marker {
    background: linear-gradient(135deg, #d92027 0%, #ff6b6b 100%);
    border-color: #d92027;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(217, 32, 39, 0.7);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(217, 32, 39, 0);
    }
}

.timeline-marker i {
    color: #d92027;
    font-size: 16px;
}

.timeline-item.active .timeline-marker i {
    color: white;
}

.timeline-content {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    border-left: 3px solid #d92027;
}

.timeline-item.active .timeline-content {
    background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
    border-left: 3px solid #d92027;
    box-shadow: 0 4px 12px rgba(217, 32, 39, 0.15);
}

.timeline-content h4 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 16px;
    font-weight: 600;
}

.timeline-time {
    margin: 0 0 4px 0;
    color: #d92027;
    font-size: 14px;
    font-weight: 600;
}

.timeline-full-time {
    margin: 0 0 8px 0;
    color: #888;
    font-size: 12px;
}

.timeline-notes {
    margin: 8px 0 0 0;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 4px;
    color: #666;
    font-size: 13px;
    font-style: italic;
}

.timeline-loading {
    text-align: center;
    padding: 40px;
    color: #666;
}

.timeline-loading i {
    font-size: 32px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 20px;
    }
    
    .header-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .orders-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Change email button
    $('#change-email-btn').on('click', function() {
        document.cookie = 'ucfc_guest_email=; path=/; max-age=0';
        location.reload();
    });
    
    // Reorder button
    $('.reorder-btn').on('click', function() {
        const orderId = $(this).data('order-id');
        
        if (confirm('Add all items from this order to your cart?')) {
            $(this).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
            
            $.ajax({
                url: ucfcCart.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_reorder',
                    nonce: ucfcCart.nonce,
                    order_id: orderId
                },
                success: function(response) {
                    if (response.success) {
                        alert('Items added to cart!');
                        window.location.href = '<?php echo home_url('/cart'); ?>';
                    } else {
                        alert('Error: ' + response.data.message);
                        location.reload();
                    }
                }
            });
        }
    });
    
    // View timeline button
    $('.view-timeline-btn').on('click', function() {
        const orderId = $(this).data('order-id');
        const $timeline = $('#timeline-' + orderId);
        const $btn = $(this);
        
        // Toggle timeline visibility
        if ($timeline.is(':visible')) {
            $timeline.slideUp(300);
            $btn.html('<i class="fas fa-history"></i> View Status History');
            return;
        }
        
        // Show timeline
        $timeline.slideDown(300);
        $btn.html('<i class="fas fa-times"></i> Hide Status History');
        
        // Load timeline if not loaded yet
        if ($timeline.data('loaded')) {
            return;
        }
        
        $.ajax({
            url: ucfcCart.ajax_url,
            type: 'POST',
            data: {
                action: 'ucfc_get_order_history',
                nonce: ucfcCart.nonce,
                order_id: orderId
            },
            success: function(response) {
                if (response.success && response.data.timeline.length > 0) {
                    let html = '<div class="timeline-items">';
                    
                    response.data.timeline.forEach(function(item, index) {
                        const isLast = index === response.data.timeline.length - 1;
                        const statusIcons = {
                            'pending': 'fa-clock',
                            'confirmed': 'fa-check',
                            'preparing': 'fa-fire',
                            'ready': 'fa-check-double',
                            'completed': 'fa-flag-checkered',
                            'cancelled': 'fa-times-circle'
                        };
                        const statusLabels = {
                            'pending': 'Order Received',
                            'confirmed': 'Order Confirmed',
                            'preparing': 'Preparing Your Order',
                            'ready': 'Order Ready',
                            'completed': 'Order Completed',
                            'cancelled': 'Order Cancelled'
                        };
                        const icon = statusIcons[item.status] || 'fa-circle';
                        const label = statusLabels[item.status] || item.status;
                        
                        html += '<div class="timeline-item' + (isLast ? ' active' : '') + '">';
                        html += '    <div class="timeline-marker">';
                        html += '        <i class="fas ' + icon + '"></i>';
                        html += '    </div>';
                        html += '    <div class="timeline-content">';
                        html += '        <h4>' + label + '</h4>';
                        html += '        <p class="timeline-time">' + item.timestamp + '</p>';
                        html += '        <p class="timeline-full-time">' + item.full_timestamp + '</p>';
                        if (item.notes) {
                            html += '        <p class="timeline-notes">' + item.notes + '</p>';
                        }
                        html += '    </div>';
                        html += '</div>';
                    });
                    
                    html += '</div>';
                    $timeline.html(html).data('loaded', true);
                } else {
                    $timeline.html('<p style="text-align: center; color: #666;">No status history available</p>');
                }
            },
            error: function() {
                $timeline.html('<p style="text-align: center; color: #d92027;">Error loading timeline</p>');
            }
        });
    });
    
    // Auto-refresh orders every 30 seconds to update status
    setInterval(function() {
        $('.order-card').each(function() {
            const orderStatus = $(this).data('order-status');
            // Only refresh if order is active
            if (['pending', 'confirmed', 'preparing', 'ready'].includes(orderStatus)) {
                location.reload();
            }
        });
    }, 30000); // 30 seconds
});
</script>

<?php get_footer(); ?>
