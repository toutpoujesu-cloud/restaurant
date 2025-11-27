<?php
/**
 * Template Name: Order Confirmation
 * 
 * Thank you page after successful checkout
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

get_header();

// Get order number from URL
$order_number = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : '';

if (empty($order_number)) {
    wp_redirect(home_url());
    exit;
}

// Get order from database
global $wpdb;
$order = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}orders WHERE order_number = %s",
    $order_number
));

if (!$order) {
    wp_redirect(home_url());
    exit;
}

// Get order items
$items = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}order_items WHERE order_id = %d",
    $order->id
));
?>

<div class="order-confirmation-page">
    <div class="container">
        
        <!-- Success Animation -->
        <div class="success-animation">
            <div class="checkmark-circle">
                <div class="checkmark"></div>
            </div>
            <h1>Order Confirmed!</h1>
            <p class="subtitle">Thank you for your order, <?php echo esc_html($order->customer_name); ?>!</p>
        </div>
        
        <!-- Order Details Card -->
        <div class="order-details-card">
            <div class="order-header">
                <div>
                    <h2>Order #<?php echo esc_html($order->order_number); ?></h2>
                    <p class="order-date"><?php echo date('F j, Y \a\t g:i A', strtotime($order->created_at)); ?></p>
                </div>
                <div class="order-status-badge status-<?php echo $order->order_status; ?>">
                    <?php echo ucfirst($order->order_status); ?>
                </div>
            </div>
            
            <div class="order-info-grid">
                <div class="info-box">
                    <div class="info-icon">
                        <i class="fas fa-<?php echo $order->order_type === 'pickup' ? 'shopping-bag' : ($order->order_type === 'delivery' ? 'truck' : 'utensils'); ?>"></i>
                    </div>
                    <div class="info-content">
                        <h4><?php echo ucfirst($order->order_type); ?></h4>
                        <p><?php echo $order->order_type === 'delivery' ? 'We\'ll deliver to you' : ($order->order_type === 'pickup' ? 'Pick up at restaurant' : 'Dine with us'); ?></p>
                    </div>
                </div>
                
                <div class="info-box">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h4>Estimated Time</h4>
                        <p><?php echo $order->estimated_time; ?> minutes</p>
                    </div>
                </div>
                
                <div class="info-box">
                    <div class="info-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="info-content">
                        <h4>Total Amount</h4>
                        <p class="amount">$<?php echo number_format($order->total, 2); ?></p>
                    </div>
                </div>
                
                <div class="info-box">
                    <div class="info-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="info-content">
                        <h4>Payment</h4>
                        <p><?php echo ucfirst(str_replace('_', ' ', $order->payment_method)); ?></p>
                        <span class="payment-badge payment-<?php echo $order->payment_status; ?>"><?php echo ucfirst($order->payment_status); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($order->order_type === 'delivery' && $order->customer_address): ?>
                <div class="delivery-address">
                    <h4><i class="fas fa-map-marker-alt"></i> Delivery Address</h4>
                    <p><?php echo nl2br(esc_html($order->customer_address)); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($order->special_instructions): ?>
                <div class="special-instructions">
                    <h4><i class="fas fa-clipboard-list"></i> Special Instructions</h4>
                    <p><?php echo nl2br(esc_html($order->special_instructions)); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Order Items -->
            <div class="order-items-section">
                <h3>Your Order</h3>
                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                        <?php $thumbnail = get_the_post_thumbnail_url($item->product_id, 'thumbnail'); ?>
                        <div class="order-item">
                            <div class="item-image">
                                <?php if ($thumbnail): ?>
                                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($item->product_name); ?>">
                                <?php else: ?>
                                    <i class="fas fa-utensils"></i>
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <h4><?php echo esc_html($item->product_name); ?></h4>
                                <p>Quantity: <?php echo $item->quantity; ?></p>
                            </div>
                            <div class="item-price">
                                $<?php echo number_format($item->subtotal, 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($order->subtotal, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Tax (8%):</span>
                        <span>$<?php echo number_format($order->tax, 2); ?></span>
                    </div>
                    <?php if ($order->delivery_fee > 0): ?>
                        <div class="total-row">
                            <span>Delivery Fee:</span>
                            <span>$<?php echo number_format($order->delivery_fee, 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="total-row total-final">
                        <span>Total:</span>
                        <span>$<?php echo number_format($order->total, 2); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="confirmation-footer">
                <div class="contact-box">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Confirmation Email Sent</strong>
                        <p>Check <?php echo esc_html($order->customer_email); ?></p>
                    </div>
                </div>
                
                <div class="contact-box">
                    <i class="fas fa-phone"></i>
                    <div>
                        <strong>Questions?</strong>
                        <p>Call us at <?php echo esc_html($order->customer_phone); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="<?php echo home_url('/my-orders'); ?>" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Orders
                </a>
                <a href="<?php echo home_url(); ?>" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <button onclick="window.print();" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
        
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Real-time order status updates
    const orderNumber = '<?php echo esc_js($order->order_number); ?>';
    const currentStatus = '<?php echo esc_js($order->order_status); ?>';
    
    // Only poll for updates if order is still in progress
    if (['pending', 'confirmed', 'preparing', 'ready'].includes(currentStatus)) {
        setInterval(function() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'ucfc_get_order_status',
                    nonce: '<?php echo wp_create_nonce('ucfc_cart_nonce'); ?>',
                    order_number: orderNumber
                },
                success: function(response) {
                    if (response.success) {
                        const newStatus = response.data.order_status;
                        
                        // Reload page if status changed
                        if (newStatus !== currentStatus) {
                            location.reload();
                        }
                    }
                }
            });
        }, 15000); // Check every 15 seconds
    }
});
</script>

<style>
.order-confirmation-page {
    padding: 60px 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 90vh;
}

.success-animation {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeInDown 0.6s;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.checkmark-circle {
    width: 120px;
    height: 120px;
    background: #4caf50;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes scaleIn {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

.checkmark {
    width: 60px;
    height: 60px;
    border-right: 8px solid white;
    border-bottom: 8px solid white;
    transform: rotate(45deg);
    margin-top: -15px;
}

.success-animation h1 {
    font-size: 3rem;
    color: #333;
    margin-bottom: 10px;
}

.subtitle {
    font-size: 1.3rem;
    color: #666;
}

.order-details-card {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    padding: 40px;
    animation: fadeInUp 0.6s 0.2s backwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 25px;
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 30px;
}

.order-header h2 {
    margin: 0 0 5px 0;
    font-size: 2rem;
    color: #333;
}

.order-date {
    color: #666;
    margin: 0;
}

.order-status-badge {
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d1ecf1; color: #0c5460; }
.status-preparing { background: #f8d7da; color: #721c24; }
.status-ready { background: #d4edda; color: #155724; }

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.info-box {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.info-icon {
    width: 50px;
    height: 50px;
    background: #d92027;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.info-content h4 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    color: #666;
    text-transform: uppercase;
}

.info-content p {
    margin: 0;
    font-size: 1.1rem;
    color: #333;
    font-weight: 600;
}

.amount {
    color: #d92027 !important;
    font-size: 1.5rem !important;
}

.payment-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-top: 5px;
}

.payment-pending { background: #fff3cd; color: #856404; }
.payment-paid { background: #d4edda; color: #155724; }

.delivery-address, .special-instructions {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.delivery-address h4, .special-instructions h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.delivery-address i, .special-instructions i {
    color: #d92027;
    margin-right: 8px;
}

.order-items-section {
    margin-top: 30px;
}

.order-items-section h3 {
    margin-bottom: 20px;
    color: #333;
}

.items-list {
    margin-bottom: 25px;
}

.order-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item .item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.order-item .item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item .item-image i {
    font-size: 1.5rem;
    color: #ccc;
}

.order-item .item-details {
    flex: 1;
}

.order-item h4 {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
}

.order-item p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.order-item .item-price {
    font-weight: 600;
    font-size: 1.2rem;
    color: #d92027;
}

.order-totals {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
}

.total-final {
    border-top: 2px solid #333;
    margin-top: 10px;
    padding-top: 15px;
    font-size: 1.3rem;
    font-weight: 700;
}

.total-final span:last-child {
    color: #d92027;
    font-size: 1.8rem;
}

.confirmation-footer {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 30px 0;
    padding: 30px 0;
    border-top: 2px solid #f0f0f0;
    border-bottom: 2px solid #f0f0f0;
}

.contact-box {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.contact-box i {
    font-size: 2rem;
    color: #d92027;
}

.contact-box strong {
    display: block;
    margin-bottom: 5px;
}

.contact-box p {
    margin: 0;
    color: #666;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

@media print {
    .action-buttons, header, footer { display: none !important; }
    .order-confirmation-page { background: white; padding: 0; }
}

@media (max-width: 768px) {
    .order-info-grid {
        grid-template-columns: 1fr;
    }
    
    .confirmation-footer {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<?php get_footer(); ?>
