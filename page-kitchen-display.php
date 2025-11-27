<?php
/**
 * Template Name: Kitchen Display System
 * 
 * Real-time kitchen dashboard for order management
 * Shows active orders with timers and status controls
 * 
 * @package Uncle_Chans_Chicken
 */

// Redirect non-admin users
if (!current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}

get_header();

global $wpdb;

// Get active orders
$active_orders = $wpdb->get_results("
    SELECT o.*, 
           TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as elapsed_minutes,
           CASE 
               WHEN o.order_type = 'delivery' THEN 40
               WHEN o.order_type = 'pickup' THEN 18
               ELSE 25
           END as target_time
    FROM {$wpdb->prefix}ucfc_orders o
    WHERE o.order_status IN ('pending', 'confirmed', 'preparing')
    ORDER BY 
        CASE o.order_status
            WHEN 'preparing' THEN 1
            WHEN 'confirmed' THEN 2
            WHEN 'pending' THEN 3
        END,
        o.created_at ASC
");

?>

<div class="kds-wrapper">
    <!-- KDS Header -->
    <div class="kds-header">
        <div class="kds-header-left">
            <h1>
                <i class="fas fa-utensils"></i>
                Kitchen Display System
            </h1>
            <div class="kds-stats">
                <span class="stat" id="total-orders">
                    <i class="fas fa-receipt"></i>
                    <strong>0</strong> Active
                </span>
                <span class="stat" id="preparing-count">
                    <i class="fas fa-fire"></i>
                    <strong>0</strong> Preparing
                </span>
                <span class="stat" id="overdue-count">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>0</strong> Overdue
                </span>
            </div>
        </div>
        <div class="kds-header-right">
            <div class="kds-time" id="kds-current-time"></div>
            <button id="kds-fullscreen" class="kds-btn kds-btn-icon" title="Fullscreen">
                <i class="fas fa-expand"></i>
            </button>
            <button id="kds-refresh" class="kds-btn kds-btn-icon" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button id="kds-settings" class="kds-btn kds-btn-icon" title="Settings">
                <i class="fas fa-cog"></i>
            </button>
        </div>
    </div>

    <!-- KDS Main Content -->
    <div class="kds-content" id="kds-orders-grid">
        <?php if (empty($active_orders)): ?>
            <div class="kds-empty">
                <i class="fas fa-check-circle"></i>
                <h2>All Caught Up!</h2>
                <p>No active orders at the moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($active_orders as $order): 
                // Get order items
                $items = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}ucfc_order_items WHERE order_id = %d",
                    $order->id
                ));
                
                // Calculate urgency
                $urgency = 'normal';
                $progress_percent = 0;
                
                if ($order->elapsed_minutes >= $order->target_time) {
                    $urgency = 'overdue';
                    $progress_percent = 100;
                } elseif ($order->elapsed_minutes >= $order->target_time * 0.8) {
                    $urgency = 'warning';
                    $progress_percent = ($order->elapsed_minutes / $order->target_time) * 100;
                } else {
                    $urgency = 'good';
                    $progress_percent = ($order->elapsed_minutes / $order->target_time) * 100;
                }
                
                $order_number = str_pad($order->id, 6, '0', STR_PAD_LEFT);
                ?>
                
                <div class="kds-order-card urgency-<?php echo $urgency; ?> status-<?php echo $order->order_status; ?>" 
                     data-order-id="<?php echo $order->id; ?>"
                     data-status="<?php echo $order->order_status; ?>"
                     data-elapsed="<?php echo $order->elapsed_minutes; ?>"
                     data-target="<?php echo $order->target_time; ?>">
                    
                    <!-- Card Header -->
                    <div class="kds-card-header">
                        <div class="kds-order-number">
                            <span class="order-label">Order</span>
                            <span class="order-num">#<?php echo $order_number; ?></span>
                        </div>
                        <div class="kds-order-type">
                            <i class="fas fa-<?php echo $order->order_type === 'delivery' ? 'truck' : ($order->order_type === 'pickup' ? 'shopping-bag' : 'chair'); ?>"></i>
                            <?php echo ucfirst($order->order_type); ?>
                        </div>
                    </div>
                    
                    <!-- Timer -->
                    <div class="kds-timer">
                        <div class="timer-progress">
                            <div class="timer-bar" style="width: <?php echo min($progress_percent, 100); ?>%"></div>
                        </div>
                        <div class="timer-text">
                            <span class="elapsed"><?php echo $order->elapsed_minutes; ?> min</span>
                            <span class="separator">/</span>
                            <span class="target"><?php echo $order->target_time; ?> min</span>
                        </div>
                        <?php if ($urgency === 'overdue'): ?>
                            <div class="overdue-badge">
                                <i class="fas fa-exclamation-triangle"></i>
                                OVERDUE
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Customer Info -->
                    <div class="kds-customer">
                        <div class="customer-name">
                            <i class="fas fa-user"></i>
                            <?php echo esc_html($order->customer_name); ?>
                        </div>
                        <?php if ($order->order_type === 'delivery' && !empty($order->customer_address)): ?>
                            <div class="customer-address">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo esc_html($order->customer_address); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($order->customer_phone)): ?>
                            <div class="customer-phone">
                                <i class="fas fa-phone"></i>
                                <?php echo esc_html($order->customer_phone); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="kds-items">
                        <?php foreach ($items as $item): ?>
                            <div class="kds-item">
                                <span class="item-qty"><?php echo $item->quantity; ?>×</span>
                                <span class="item-name"><?php echo esc_html($item->product_name); ?></span>
                                <?php if (!empty($item->options)): ?>
                                    <span class="item-options">
                                        <?php 
                                        $options = json_decode($item->options, true);
                                        if (is_array($options)) {
                                            foreach ($options as $key => $value) {
                                                echo '<span class="option-tag">' . esc_html($value) . '</span>';
                                            }
                                        }
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Special Instructions -->
                    <?php if (!empty($order->special_instructions) && strpos($order->special_instructions, '[Payment Intent:') === false): ?>
                        <div class="kds-notes">
                            <i class="fas fa-sticky-note"></i>
                            <strong>Notes:</strong>
                            <?php echo esc_html(str_replace('[Payment Intent: pi_', '', $order->special_instructions)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="kds-actions">
                        <?php if ($order->order_status === 'pending'): ?>
                            <button class="kds-btn kds-btn-confirm" data-action="confirm">
                                <i class="fas fa-check"></i>
                                Confirm Order
                            </button>
                        <?php elseif ($order->order_status === 'confirmed'): ?>
                            <button class="kds-btn kds-btn-preparing" data-action="preparing">
                                <i class="fas fa-fire"></i>
                                Start Preparing
                            </button>
                        <?php elseif ($order->order_status === 'preparing'): ?>
                            <button class="kds-btn kds-btn-ready" data-action="ready">
                                <i class="fas fa-check-circle"></i>
                                Mark as Ready
                            </button>
                        <?php endif; ?>
                        
                        <button class="kds-btn kds-btn-view" data-action="view">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <!-- Order Time -->
                    <div class="kds-order-time">
                        <i class="fas fa-clock"></i>
                        <?php echo date('g:i A', strtotime($order->created_at)); ?>
                    </div>
                </div>
                
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
* {
    box-sizing: border-box;
}

body.admin-bar .kds-wrapper {
    padding-top: 32px;
}

.kds-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #1a1a1a;
    color: #fff;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.kds-wrapper.fullscreen {
    z-index: 999999;
}

/* Header */
.kds-header {
    background: #2d2d2d;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #d92027;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.kds-header-left h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 12px;
}

.kds-header-left h1 i {
    color: #d92027;
}

.kds-stats {
    display: flex;
    gap: 25px;
    font-size: 14px;
}

.kds-stats .stat {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #aaa;
}

.kds-stats .stat strong {
    color: #fff;
    font-size: 20px;
}

.kds-stats .stat i {
    color: #d92027;
}

.kds-header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.kds-time {
    font-size: 24px;
    font-weight: 600;
    color: #fff;
    letter-spacing: 1px;
}

.kds-btn {
    background: #3a3a3a;
    border: none;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.kds-btn:hover {
    background: #4a4a4a;
    transform: translateY(-2px);
}

.kds-btn-icon {
    padding: 12px;
    font-size: 18px;
}

/* Content Area */
.kds-content {
    flex: 1;
    overflow-y: auto;
    padding: 30px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 25px;
    align-content: start;
}

.kds-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 100px 20px;
    color: #666;
}

.kds-empty i {
    font-size: 100px;
    color: #3a3a3a;
    margin-bottom: 20px;
}

.kds-empty h2 {
    font-size: 32px;
    margin: 0 0 10px 0;
    color: #aaa;
}

.kds-empty p {
    font-size: 18px;
    margin: 0;
}

/* Order Cards */
.kds-order-card {
    background: #2d2d2d;
    border-radius: 12px;
    padding: 20px;
    border: 3px solid transparent;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.kds-order-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: #4caf50;
}

.kds-order-card.urgency-warning::before {
    background: #ff9800;
}

.kds-order-card.urgency-overdue::before {
    background: #f44336;
    animation: pulse 1s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.kds-order-card.status-preparing {
    border-color: #ff9800;
    box-shadow: 0 0 20px rgba(255, 152, 0, 0.3);
}

/* Card Header */
.kds-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.kds-order-number {
    display: flex;
    flex-direction: column;
}

.order-label {
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.order-num {
    font-size: 32px;
    font-weight: 700;
    color: #fff;
    line-height: 1;
}

.kds-order-type {
    background: #3a3a3a;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Timer */
.kds-timer {
    margin-bottom: 15px;
    position: relative;
}

.timer-progress {
    height: 8px;
    background: #3a3a3a;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.timer-bar {
    height: 100%;
    background: linear-gradient(90deg, #4caf50, #8bc34a);
    transition: width 1s linear;
}

.urgency-warning .timer-bar {
    background: linear-gradient(90deg, #ff9800, #ffb74d);
}

.urgency-overdue .timer-bar {
    background: linear-gradient(90deg, #f44336, #e57373);
}

.timer-text {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 16px;
    font-weight: 600;
}

.elapsed {
    color: #fff;
    font-size: 20px;
}

.separator {
    color: #666;
}

.target {
    color: #888;
}

.overdue-badge {
    position: absolute;
    top: -5px;
    right: 0;
    background: #f44336;
    color: #fff;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 5px;
    animation: shake 0.5s ease-in-out infinite;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Customer Info */
.kds-customer {
    background: #3a3a3a;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
}

.kds-customer > div {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}

.kds-customer > div:last-child {
    margin-bottom: 0;
}

.kds-customer i {
    color: #d92027;
    width: 16px;
}

.customer-name {
    font-weight: 600;
    font-size: 16px;
}

.customer-address {
    color: #ccc;
}

.customer-phone {
    color: #aaa;
}

/* Order Items */
.kds-items {
    margin-bottom: 15px;
}

.kds-item {
    background: #242424;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.item-qty {
    background: #d92027;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
}

.item-name {
    flex: 1;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
}

.item-options {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.option-tag {
    background: #4a4a4a;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #aaa;
}

/* Notes */
.kds-notes {
    background: #3a3a3a;
    border-left: 4px solid #ff9800;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
    line-height: 1.5;
}

.kds-notes i {
    color: #ff9800;
    margin-right: 8px;
}

/* Actions */
.kds-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.kds-actions .kds-btn {
    flex: 1;
}

.kds-btn-confirm {
    background: linear-gradient(135deg, #2196f3, #1976d2);
}

.kds-btn-confirm:hover {
    background: linear-gradient(135deg, #1976d2, #1565c0);
}

.kds-btn-preparing {
    background: linear-gradient(135deg, #ff9800, #f57c00);
}

.kds-btn-preparing:hover {
    background: linear-gradient(135deg, #f57c00, #e65100);
}

.kds-btn-ready {
    background: linear-gradient(135deg, #4caf50, #388e3c);
}

.kds-btn-ready:hover {
    background: linear-gradient(135deg, #388e3c, #2e7d32);
}

.kds-btn-view {
    flex: 0 0 auto;
    padding: 12px 15px;
    background: #3a3a3a;
}

/* Order Time */
.kds-order-time {
    text-align: center;
    color: #666;
    font-size: 12px;
    padding-top: 10px;
    border-top: 1px solid #3a3a3a;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* Responsive */
@media (max-width: 1400px) {
    .kds-content {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }
}

@media (max-width: 1024px) {
    .kds-content {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        padding: 20px;
    }
    
    .kds-header {
        flex-direction: column;
        gap: 15px;
    }
}

@media (max-width: 768px) {
    .kds-content {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    const KDS = {
        refreshInterval: null,
        
        init: function() {
            this.updateClock();
            this.updateStats();
            this.startAutoRefresh();
            this.setupEventListeners();
            this.updateTimers();
            
            setInterval(() => this.updateClock(), 1000);
            setInterval(() => this.updateTimers(), 1000);
        },
        
        updateClock: function() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            $('#kds-current-time').text(timeStr);
        },
        
        updateStats: function() {
            const cards = $('.kds-order-card');
            const totalOrders = cards.length;
            const preparingCount = cards.filter('.status-preparing').length;
            const overdueCount = cards.filter('.urgency-overdue').length;
            
            $('#total-orders strong').text(totalOrders);
            $('#preparing-count strong').text(preparingCount);
            $('#overdue-count strong').text(overdueCount);
        },
        
        updateTimers: function() {
            $('.kds-order-card').each(function() {
                const $card = $(this);
                let elapsed = parseInt($card.data('elapsed'));
                const target = parseInt($card.data('target'));
                
                elapsed++;
                $card.data('elapsed', elapsed);
                
                // Update timer text
                $card.find('.elapsed').text(elapsed + ' min');
                
                // Update progress bar
                const percent = Math.min((elapsed / target) * 100, 100);
                $card.find('.timer-bar').css('width', percent + '%');
                
                // Update urgency class
                $card.removeClass('urgency-good urgency-warning urgency-overdue');
                
                if (elapsed >= target) {
                    $card.addClass('urgency-overdue');
                    if ($card.find('.overdue-badge').length === 0) {
                        $card.find('.kds-timer').append('<div class="overdue-badge"><i class="fas fa-exclamation-triangle"></i> OVERDUE</div>');
                    }
                } else if (elapsed >= target * 0.8) {
                    $card.addClass('urgency-warning');
                } else {
                    $card.addClass('urgency-good');
                }
            });
            
            this.updateStats();
        },
        
        startAutoRefresh: function() {
            this.refreshInterval = setInterval(() => {
                this.refreshOrders();
            }, 30000); // 30 seconds
        },
        
        refreshOrders: function() {
            $('#kds-refresh i').addClass('fa-spin');
            location.reload();
        },
        
        setupEventListeners: function() {
            const self = this;
            
            // Fullscreen toggle
            $('#kds-fullscreen').on('click', function() {
                const elem = $('.kds-wrapper')[0];
                
                if (!document.fullscreenElement) {
                    elem.requestFullscreen().then(() => {
                        $('.kds-wrapper').addClass('fullscreen');
                        $(this).find('i').removeClass('fa-expand').addClass('fa-compress');
                    });
                } else {
                    document.exitFullscreen().then(() => {
                        $('.kds-wrapper').removeClass('fullscreen');
                        $(this).find('i').removeClass('fa-compress').addClass('fa-expand');
                    });
                }
            });
            
            // Manual refresh
            $('#kds-refresh').on('click', function() {
                self.refreshOrders();
            });
            
            // Status change buttons
            $(document).on('click', '.kds-actions .kds-btn[data-action]', function() {
                const action = $(this).data('action');
                const $card = $(this).closest('.kds-order-card');
                const orderId = $card.data('order-id');
                
                if (action === 'view') {
                    window.open('<?php echo admin_url('admin.php?page=ucfc-orders'); ?>&order_id=' + orderId, '_blank');
                    return;
                }
                
                self.updateOrderStatus(orderId, action, $card);
            });
        },
        
        updateOrderStatus: function(orderId, action, $card) {
            const statusMap = {
                'confirm': 'confirmed',
                'preparing': 'preparing',
                'ready': 'ready'
            };
            
            const newStatus = statusMap[action];
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'ucfc_update_order_status',
                    nonce: '<?php echo wp_create_nonce('ucfc_order_status'); ?>',
                    order_id: orderId,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Remove card if ready
                        if (newStatus === 'ready') {
                            $card.fadeOut(300, function() {
                                $(this).remove();
                                KDS.updateStats();
                            });
                        } else {
                            // Update card status
                            $card.attr('data-status', newStatus);
                            $card.removeClass('status-pending status-confirmed status-preparing');
                            $card.addClass('status-' + newStatus);
                            
                            // Update buttons
                            const $actions = $card.find('.kds-actions');
                            $actions.find('.kds-btn[data-action]').not('.kds-btn-view').remove();
                            
                            if (newStatus === 'confirmed') {
                                $actions.prepend('<button class="kds-btn kds-btn-preparing" data-action="preparing"><i class="fas fa-fire"></i> Start Preparing</button>');
                            } else if (newStatus === 'preparing') {
                                $actions.prepend('<button class="kds-btn kds-btn-ready" data-action="ready"><i class="fas fa-check-circle"></i> Mark as Ready</button>');
                            }
                            
                            KDS.updateStats();
                        }
                    } else {
                        alert('Failed to update status: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Error updating order status');
                }
            });
        }
    };
    
    KDS.init();
});
</script>

<?php
// Don't load footer for clean display
?>
