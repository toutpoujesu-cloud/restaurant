<?php
/**
 * Template Name: Shopping Cart
 * 
 * Displays the shopping cart with:
 * - Cart items list
 * - Quantity controls
 * - Remove buttons
 * - Cart totals
 * - Checkout button
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

get_header();

$cart = ucfc_get_cart();
$items = $cart->get_cart();
$totals = $cart->get_totals();
?>

<div class="cart-page-wrapper">
    <div class="container">
        <h1 class="page-title">Shopping Cart</h1>
        
        <?php if (empty($items)): ?>
            
            <!-- Empty Cart -->
            <div class="empty-cart-container">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Add some delicious items to get started!</p>
                <a href="<?php echo home_url('/menu'); ?>" class="btn btn-primary">
                    <i class="fas fa-utensils"></i> Browse Menu
                </a>
            </div>
            
        <?php else: ?>
            
            <!-- Cart Items -->
            <div class="cart-content-wrapper">
                <div class="cart-items-section">
                    <div class="cart-items-header">
                        <h2>Cart Items (<?php echo $totals['item_count']; ?>)</h2>
                        <button class="clear-cart-btn btn-link">
                            <i class="fas fa-trash"></i> Clear Cart
                        </button>
                    </div>
                    
                    <div class="cart-items-container">
                        <?php foreach ($items as $item): ?>
                            <?php
                            $product = get_post($item->product_id);
                            $thumbnail = get_the_post_thumbnail_url($product, 'medium');
                            $options = !empty($item->options) ? $item->options : [];
                            ?>
                            
                            <div class="cart-item" data-cart-item-id="<?php echo $item->id; ?>">
                                <div class="item-image">
                                    <?php if ($thumbnail): ?>
                                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($item->product_name); ?>">
                                    <?php else: ?>
                                        <div class="no-image"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-details">
                                    <h3 class="item-name"><?php echo esc_html($item->product_name); ?></h3>
                                    
                                    <?php if (!empty($options)): ?>
                                        <div class="item-options">
                                            <?php if (isset($options['size'])): ?>
                                                <span class="option-badge">Size: <?php echo esc_html($options['size']); ?></span>
                                            <?php endif; ?>
                                            <?php if (isset($options['spice'])): ?>
                                                <span class="option-badge">Spice: <?php echo esc_html($options['spice']); ?></span>
                                            <?php endif; ?>
                                            <?php if (isset($options['extras']) && is_array($options['extras'])): ?>
                                                <?php foreach ($options['extras'] as $extra): ?>
                                                    <span class="option-badge">+ <?php echo esc_html($extra); ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="item-price">
                                        $<?php echo number_format($item->price, 2); ?> each
                                    </div>
                                </div>
                                
                                <div class="item-quantity">
                                    <label>Quantity</label>
                                    <div class="quantity-controls">
                                        <button class="qty-decrease" type="button">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input 
                                            type="number" 
                                            class="cart-item-quantity" 
                                            value="<?php echo $item->quantity; ?>"
                                            min="1"
                                            data-cart-item-id="<?php echo $item->id; ?>"
                                        >
                                        <button class="qty-increase" type="button">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="item-subtotal">
                                    <label>Subtotal</label>
                                    <div class="subtotal-amount">
                                        $<?php echo number_format($item->subtotal, 2); ?>
                                    </div>
                                </div>
                                
                                <div class="item-actions">
                                    <button class="remove-cart-item" data-cart-item-id="<?php echo $item->id; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Continue Shopping -->
                    <div class="cart-footer-actions">
                        <a href="<?php echo home_url('/menu'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="cart-summary-section">
                    <div class="cart-summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span class="summary-label">Subtotal:</span>
                            <span class="summary-value cart-subtotal-value">$<?php echo $totals['subtotal']; ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Tax (8%):</span>
                            <span class="summary-value cart-tax-value">$<?php echo $totals['tax']; ?></span>
                        </div>
                        
                        <div class="summary-row summary-total">
                            <span class="summary-label">Total:</span>
                            <span class="summary-value cart-total-value">$<?php echo $totals['total']; ?></span>
                        </div>
                        
                        <div class="checkout-actions">
                            <a href="<?php echo home_url('/checkout'); ?>" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-lock"></i> Proceed to Checkout
                            </a>
                        </div>
                        
                        <!-- Security Badges -->
                        <div class="security-badges">
                            <div class="badge-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Secure Checkout</span>
                            </div>
                            <div class="badge-item">
                                <i class="fas fa-credit-card"></i>
                                <span>Safe Payment</span>
                            </div>
                        </div>
                        
                        <!-- Promo Code -->
                        <div class="promo-code-section">
                            <h4>Have a promo code?</h4>
                            <div class="promo-code-form">
                                <input type="text" placeholder="Enter code" id="promo-code-input">
                                <button class="btn btn-secondary apply-promo-btn">Apply</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Trust Signals -->
                    <div class="trust-signals">
                        <div class="trust-item">
                            <i class="fas fa-truck"></i>
                            <div class="trust-text">
                                <strong>Free Delivery</strong>
                                <span>On orders over $30</span>
                            </div>
                        </div>
                        <div class="trust-item">
                            <i class="fas fa-clock"></i>
                            <div class="trust-text">
                                <strong>Fast Preparation</strong>
                                <span>Ready in 15-20 mins</span>
                            </div>
                        </div>
                        <div class="trust-item">
                            <i class="fas fa-utensils"></i>
                            <div class="trust-text">
                                <strong>Fresh Food</strong>
                                <span>Made to order</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<style>
.cart-page-wrapper {
    padding: 60px 0;
    background: #f8f9fa;
    min-height: 80vh;
}

.page-title {
    font-size: 2.5rem;
    margin-bottom: 40px;
    color: #333;
}

/* Empty Cart */
.empty-cart-container {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-cart-icon {
    font-size: 100px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-cart-container h2 {
    font-size: 2rem;
    margin-bottom: 10px;
}

.empty-cart-container p {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 30px;
}

/* Cart Content */
.cart-content-wrapper {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
}

.cart-items-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cart-items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.clear-cart-btn {
    color: #dc3545;
    background: none;
    border: none;
    cursor: pointer;
    transition: opacity 0.2s;
}

.clear-cart-btn:hover {
    opacity: 0.7;
}

/* Cart Item */
.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr 120px 120px 50px;
    gap: 20px;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #ccc;
}

.item-name {
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.item-options {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin: 8px 0;
}

.option-badge {
    display: inline-block;
    padding: 3px 10px;
    background: #f0f0f0;
    border-radius: 12px;
    font-size: 0.85rem;
    color: #666;
}

.item-price {
    color: #666;
    font-size: 0.9rem;
}

/* Quantity Controls */
.quantity-controls {
    display: flex;
    align-items: center;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
}

.qty-decrease,
.qty-increase {
    width: 35px;
    height: 35px;
    background: #f8f9fa;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

.qty-decrease:hover,
.qty-increase:hover {
    background: #e9ecef;
}

.cart-item-quantity {
    width: 50px;
    height: 35px;
    text-align: center;
    border: none;
    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;
    font-size: 1rem;
}

.item-subtotal {
    text-align: right;
}

.subtotal-amount {
    font-size: 1.2rem;
    font-weight: 600;
    color: #d92027;
}

.remove-cart-item {
    width: 40px;
    height: 40px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    cursor: pointer;
    color: #dc3545;
    transition: all 0.2s;
}

.remove-cart-item:hover {
    background: #dc3545;
    color: white;
}

/* Cart Summary */
.cart-summary-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: sticky;
    top: 20px;
}

.cart-summary-card h3 {
    margin-bottom: 20px;
    font-size: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.summary-total {
    border-top: 2px solid #333;
    border-bottom: none;
    margin-top: 10px;
    padding-top: 20px;
    font-size: 1.3rem;
    font-weight: 600;
}

.checkout-actions {
    margin-top: 25px;
}

.btn-block {
    width: 100%;
    display: block;
    text-align: center;
}

.security-badges {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.badge-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
}

.promo-code-section {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid #f0f0f0;
}

.promo-code-form {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.promo-code-form input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.trust-signals {
    margin-top: 20px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.trust-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.trust-item:last-child {
    border-bottom: none;
}

.trust-item i {
    font-size: 2rem;
    color: #d92027;
}

.trust-text {
    display: flex;
    flex-direction: column;
}

.trust-text strong {
    font-size: 1.1rem;
}

.trust-text span {
    color: #666;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 992px) {
    .cart-content-wrapper {
        grid-template-columns: 1fr;
    }
    
    .cart-summary-card {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-item {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .item-image {
        width: 100%;
        height: 200px;
    }
}
</style>

<?php get_footer(); ?>
