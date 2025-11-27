<?php
/**
 * Template Name: Checkout
 * 
 * Multi-step checkout process:
 * Step 1: Order Type Selection (Pickup, Delivery, Dine-in)
 * Step 2: Customer Information
 * Step 3: Review & Payment
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

get_header();

$cart = ucfc_get_cart();
$items = $cart->get_cart();
$totals = $cart->get_totals();

// Redirect to cart if empty
if (empty($items)) {
    wp_redirect(home_url('/cart'));
    exit;
}
?>

<div class="checkout-page-wrapper">
    <div class="container">
        <h1 class="page-title">Checkout</h1>
        
        <!-- Progress Steps -->
        <div class="checkout-steps">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Order Type</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Your Info</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Review & Pay</div>
            </div>
        </div>
        
        <form id="checkout-form" class="checkout-form">
            
            <!-- Step 1: Order Type -->
            <div class="checkout-step-content active" id="step-1">
                <h2>How would you like to receive your order?</h2>
                
                <div class="order-type-options">
                    <label class="order-type-card">
                        <input type="radio" name="order_type" value="pickup" checked>
                        <div class="card-content">
                            <i class="fas fa-shopping-bag"></i>
                            <h3>Pickup</h3>
                            <p>Pick up your order at the restaurant</p>
                            <span class="time-estimate">Ready in 15-20 mins</span>
                        </div>
                    </label>
                    
                    <label class="order-type-card">
                        <input type="radio" name="order_type" value="delivery">
                        <div class="card-content">
                            <i class="fas fa-truck"></i>
                            <h3>Delivery</h3>
                            <p>We'll deliver to your address</p>
                            <span class="time-estimate">30-45 mins • $5.00 fee</span>
                        </div>
                    </label>
                    
                    <label class="order-type-card">
                        <input type="radio" name="order_type" value="dine-in">
                        <div class="card-content">
                            <i class="fas fa-utensils"></i>
                            <h3>Dine-In</h3>
                            <p>Enjoy your meal at our restaurant</p>
                            <span class="time-estimate">Seat will be ready</span>
                        </div>
                    </label>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn btn-primary btn-lg next-step">
                        Continue <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Customer Information -->
            <div class="checkout-step-content" id="step-2">
                <h2>Your Information</h2>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="customer_name">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" required placeholder="John Doe">
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_email">Email Address *</label>
                        <input type="email" id="customer_email" name="customer_email" required placeholder="john@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_phone">Phone Number *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" required placeholder="(555) 123-4567">
                    </div>
                    
                    <div class="form-group full-width delivery-only" style="display: none;">
                        <label for="customer_address">Delivery Address *</label>
                        <textarea id="customer_address" name="customer_address" rows="3" placeholder="123 Main St, Apt 4B, City, State 12345"></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="special_instructions">Special Instructions (Optional)</label>
                        <textarea id="special_instructions" name="special_instructions" rows="3" placeholder="Extra crispy, no salt, etc."></textarea>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn btn-secondary prev-step">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn btn-primary btn-lg next-step">
                        Continue <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Review & Payment -->
            <div class="checkout-step-content" id="step-3">
                <div class="checkout-grid">
                    <div class="checkout-main">
                        <h2>Review Your Order</h2>
                        
                        <!-- Order Summary -->
                        <div class="order-review-items">
                            <?php foreach ($items as $item): ?>
                                <?php
                                $product = get_post($item->product_id);
                                $thumbnail = get_the_post_thumbnail_url($product, 'thumbnail');
                                ?>
                                <div class="review-item">
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
                        
                        <!-- Payment Method -->
                        <div class="payment-section">
                            <h3>Payment Method</h3>
                            
                            <div class="payment-options">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="card" checked>
                                    <div class="option-content">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Credit/Debit Card</span>
                                    </div>
                                </label>
                                
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="cash">
                                    <div class="option-content">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Cash on Pickup/Delivery</span>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="card-payment-form" id="card-form">
                                <div class="payment-notice">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Payment integration coming soon. For now, your order will be marked as pending payment.</p>
                                </div>
                                
                                <!-- Stripe Payment Form (shown when Stripe is configured) -->
                                <div id="stripe-payment-form" style="display: none;">
                                    <div class="form-group">
                                        <label>Card Information</label>
                                        <div id="card-element" class="stripe-card-element"></div>
                                        <div id="card-errors" role="alert"></div>
                                    </div>
                                    <div class="secure-badge" style="margin-top: 15px;">
                                        <i class="fas fa-lock"></i>
                                        <span>Secured by Stripe • Your card details are encrypted</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="step-actions">
                            <button type="button" class="btn btn-secondary prev-step">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="submit" class="btn btn-success btn-lg" id="place-order-btn">
                                <i class="fas fa-check-circle"></i> Place Order
                            </button>
                        </div>
                    </div>
                    
                    <!-- Order Summary Sidebar -->
                    <div class="checkout-sidebar">
                        <div class="order-summary-card">
                            <h3>Order Summary</h3>
                            
                            <div class="summary-info">
                                <div class="info-row">
                                    <span class="label">Order Type:</span>
                                    <span class="value order-type-display">Pickup</span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Items:</span>
                                    <span class="value"><?php echo $totals['item_count']; ?></span>
                                </div>
                            </div>
                            
                            <div class="summary-totals">
                                <div class="total-row">
                                    <span>Subtotal:</span>
                                    <span>$<?php echo $totals['subtotal']; ?></span>
                                </div>
                                <div class="total-row">
                                    <span>Tax (8%):</span>
                                    <span>$<?php echo $totals['tax']; ?></span>
                                </div>
                                <div class="total-row delivery-fee-row" style="display: none;">
                                    <span>Delivery Fee:</span>
                                    <span class="delivery-fee">$5.00</span>
                                </div>
                                <div class="total-row total-final">
                                    <span>Total:</span>
                                    <span class="final-total">$<?php echo $totals['total']; ?></span>
                                </div>
                            </div>
                            
                            <div class="security-notice">
                                <i class="fas fa-lock"></i>
                                <span>Secure Checkout</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </form>
    </div>
</div>

<style>
.checkout-page-wrapper {
    padding: 60px 0;
    background: #f8f9fa;
    min-height: 80vh;
}

/* Progress Steps */
.checkout-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 40px 0;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    opacity: 0.4;
    transition: opacity 0.3s;
}

.step.active, .step.completed {
    opacity: 1;
}

.step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    transition: all 0.3s;
}

.step.active .step-number {
    background: #d92027;
    color: white;
}

.step.completed .step-number {
    background: #4caf50;
    color: white;
}

.step-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #666;
}

.step-line {
    flex: 1;
    height: 2px;
    background: #e0e0e0;
    margin: 0 10px;
    max-width: 100px;
}

/* Checkout Form */
.checkout-step-content {
    display: none;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.checkout-step-content.active {
    display: block;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.checkout-step-content h2 {
    margin-bottom: 30px;
    color: #333;
}

/* Order Type Cards */
.order-type-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.order-type-card {
    position: relative;
    cursor: pointer;
}

.order-type-card input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.order-type-card .card-content {
    padding: 30px;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s;
}

.order-type-card:hover .card-content {
    border-color: #d92027;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(217, 32, 39, 0.15);
}

.order-type-card input[type="radio"]:checked + .card-content {
    border-color: #d92027;
    background: #fff5f5;
}

.order-type-card i {
    font-size: 3rem;
    color: #d92027;
    margin-bottom: 15px;
}

.order-type-card h3 {
    font-size: 1.3rem;
    margin-bottom: 8px;
}

.order-type-card p {
    color: #666;
    margin-bottom: 12px;
}

.time-estimate {
    display: inline-block;
    padding: 6px 12px;
    background: #f0f0f0;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.form-group input,
.form-group textarea {
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #d92027;
}

/* Checkout Grid */
.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
}

.checkout-main {
    background: white;
}

/* Review Items */
.order-review-items {
    margin-bottom: 30px;
}

.review-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.review-item:last-child {
    border-bottom: none;
}

.review-item .item-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
}

.review-item .item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.review-item .item-details {
    flex: 1;
}

.review-item h4 {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
}

.review-item p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.review-item .item-price {
    font-weight: 600;
    font-size: 1.1rem;
    color: #d92027;
}

/* Payment Section */
.payment-section {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.payment-section h3 {
    margin-bottom: 20px;
}

.payment-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-option .option-content {
    padding: 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-option:hover .option-content {
    border-color: #d92027;
}

.payment-option input[type="radio"]:checked + .option-content {
    border-color: #d92027;
    background: white;
}

.payment-option i {
    font-size: 2rem;
    color: #d92027;
    margin-bottom: 10px;
}

.payment-notice {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 4px;
}

.payment-notice i {
    color: #ffc107;
    font-size: 1.5rem;
}

/* Stripe Elements Styling */
.stripe-card-element {
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    transition: border-color 0.3s;
}

.stripe-card-element:hover {
    border-color: #d92027;
}

#card-errors {
    margin-top: 10px;
    color: #fa755a;
    font-size: 0.9rem;
}

#card-errors .success {
    color: #4caf50;
    font-weight: 600;
}

#card-errors .processing {
    color: #2196f3;
    font-weight: 600;
}

.secure-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #666;
}

.secure-badge i {
    color: #4caf50;
}

/* Order Summary Sidebar */
.order-summary-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    position: sticky;
    top: 20px;
}

.order-summary-card h3 {
    margin-bottom: 20px;
}

.summary-info {
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 15px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.info-row .label {
    color: #666;
}

.info-row .value {
    font-weight: 600;
}

.summary-totals {
    padding: 15px 0;
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
    font-size: 1.2rem;
    font-weight: 700;
}

.final-total {
    color: #d92027;
    font-size: 1.5rem;
}

.security-notice {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
    color: #666;
}

/* Step Actions */
.step-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}

/* Responsive */
@media (max-width: 992px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .order-summary-card {
        position: static;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let currentStep = 1;
    const totalSteps = 3;
    
    // Next step
    $('.next-step').on('click', function() {
        if (validateStep(currentStep)) {
            goToStep(currentStep + 1);
        }
    });
    
    // Previous step
    $('.prev-step').on('click', function() {
        goToStep(currentStep - 1);
    });
    
    // Go to step
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        
        // Update step display
        $('.checkout-step-content').removeClass('active');
        $('#step-' + step).addClass('active');
        
        $('.step').removeClass('active completed');
        $('.step').each(function() {
            const stepNum = parseInt($(this).data('step'));
            if (stepNum < step) {
                $(this).addClass('completed');
            } else if (stepNum === step) {
                $(this).addClass('active');
            }
        });
        
        currentStep = step;
        
        // Scroll to top
        $('html, body').animate({ scrollTop: $('.checkout-steps').offset().top - 100 }, 300);
    }
    
    // Validate step
    function validateStep(step) {
        if (step === 2) {
            const name = $('#customer_name').val().trim();
            const email = $('#customer_email').val().trim();
            const phone = $('#customer_phone').val().trim();
            
            if (!name || !email || !phone) {
                alert('Please fill in all required fields');
                return false;
            }
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return false;
            }
            
            // Check delivery address
            const orderType = $('input[name="order_type"]:checked').val();
            if (orderType === 'delivery' && !$('#customer_address').val().trim()) {
                alert('Please enter your delivery address');
                return false;
            }
        }
        return true;
    }
    
    // Order type change
    $('input[name="order_type"]').on('change', function() {
        const orderType = $(this).val();
        $('.order-type-display').text(orderType.charAt(0).toUpperCase() + orderType.slice(1));
        
        // Show/hide delivery fields
        if (orderType === 'delivery') {
            $('.delivery-only').show();
            $('.delivery-only input, .delivery-only textarea').prop('required', true);
            $('.delivery-fee-row').show();
            updateTotal(5);
        } else {
            $('.delivery-only').hide();
            $('.delivery-only input, .delivery-only textarea').prop('required', false);
            $('.delivery-fee-row').hide();
            updateTotal(0);
        }
    });
    
    // Update total
    function updateTotal(deliveryFee) {
        const subtotal = parseFloat($('.summary-totals .total-row:first-child span:last-child').text().replace('$', ''));
        const tax = parseFloat($('.summary-totals .total-row:nth-child(2) span:last-child').text().replace('$', ''));
        const total = subtotal + tax + deliveryFee;
        $('.final-total').text('$' + total.toFixed(2));
    }
    
    // Submit form
    $('#checkout-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateStep(2)) return;
        
        const btn = $('#place-order-btn');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Collect form data
        const formData = {
            order_type: $('input[name="order_type"]:checked').val(),
            customer_name: $('#customer_name').val(),
            customer_email: $('#customer_email').val(),
            customer_phone: $('#customer_phone').val(),
            customer_address: $('#customer_address').val(),
            special_instructions: $('#special_instructions').val(),
            payment_method: $('input[name="payment_method"]:checked').val()
        };
        
        // Check if Stripe payment is needed
        const paymentMethod = formData.payment_method;
        const needsStripePayment = paymentMethod === 'credit_card' && 
                                   typeof window.StripePaymentHandler !== 'undefined';
        
        if (needsStripePayment) {
            // Process Stripe payment first
            btn.html('<i class="fas fa-spinner fa-spin"></i> Processing payment...');
            
            window.StripePaymentHandler.processPayment(formData, function(error, updatedFormData) {
                if (error) {
                    alert('Payment failed: ' + error);
                    btn.prop('disabled', false).html(originalText);
                } else {
                    // Payment successful, create order
                    const finalData = updatedFormData || formData;
                    btn.html('<i class="fas fa-spinner fa-spin"></i> Creating order...');
                    submitOrder(finalData, btn, originalText);
                }
            });
        } else {
            // No Stripe payment needed (cash or Stripe not configured)
            submitOrder(formData, btn, originalText);
        }
    });
    
    // Submit order to backend
    function submitOrder(formData, btn, originalText) {
        $.ajax({
            url: ucfcCart.ajax_url,
            type: 'POST',
            data: Object.assign({
                action: 'ucfc_process_checkout',
                nonce: ucfcCart.nonce
            }, formData),
            success: function(response) {
                if (response.success) {
                    window.location.href = '<?php echo home_url('/order-confirmation'); ?>?order=' + response.data.order_number;
                } else {
                    alert('Error: ' + response.data.message);
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                btn.prop('disabled', false).html(originalText);
            }
        });
    }
});
</script>

<?php get_footer(); ?>
