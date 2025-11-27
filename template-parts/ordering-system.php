<!-- Customization Modal -->
<div class="customize-modal" id="customizeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalMealName">Customize Your Meal</h3>
            <button class="close-modal" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <!-- Step 1: Choose Sides -->
            <div class="customize-step active" id="stepSides">
                <h4 class="step-title">Choose Your Sides</h4>
                <p class="step-description">Select 2 sides to complete your meal</p>
                <div class="step-counter">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Your Sides ( <span id="sideCount">0</span> / 2 )</span>
                </div>
                <div class="options-grid" id="sidesOptions">
                    <!-- Sides will be populated by JavaScript -->
                </div>
                <div class="step-actions">
                    <div></div>
                    <button class="btn" id="nextToSauces">Next: Choose Sauces</button>
                </div>
            </div>
            
            <!-- Step 2: Choose Sauces -->
            <div class="customize-step" id="stepSauces">
                <h4 class="step-title">Choose Your Sauces</h4>
                <p class="step-description">Select up to 2 sauces (optional)</p>
                <div class="step-counter">
                    <i class="fas fa-wine-bottle"></i>
                    <span>Your Sauces ( <span id="sauceCount">0</span> / 2 )</span>
                </div>
                <div class="options-grid" id="saucesOptions">
                    <!-- Sauces will be populated by JavaScript -->
                </div>
                <div class="step-actions">
                    <button class="btn btn-secondary" id="backToSides">Back to Sides</button>
                    <button class="btn" id="addToCart">ADD TO CART - €<span id="finalPrice">0</span></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Sidebar -->
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
        <h3 class="cart-title">Your Order</h3>
        <button class="close-cart" id="closeCart">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="cart-items" id="cartItems">
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty</p>
            <p>Add some delicious items to get started!</p>
        </div>
    </div>
    <div class="cart-footer">
        <div class="cart-total">
            <span>Total:</span>
            <span>€<span id="cartTotal">0.00</span></span>
        </div>
        <button class="btn checkout-btn" id="checkoutBtn">PROCEED TO CHECKOUT</button>
    </div>
</div>

<!-- Checkout Modal -->
<div class="checkout-modal" id="checkoutModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Checkout</h3>
            <button class="close-modal" id="closeCheckout">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="checkout-steps">
                <div class="checkout-step active">
                    <div class="step-number">1</div>
                    <div class="step-label">Details</div>
                </div>
                <div class="checkout-step">
                    <div class="step-number">2</div>
                    <div class="step-label">Payment</div>
                </div>
                <div class="checkout-step">
                    <div class="step-number">3</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>
            
            <!-- Step 1: Customer Details -->
            <div class="checkout-step-content active" id="checkoutStep1">
                <h4 class="step-title">Pickup Information</h4>
                <div class="form-group">
                    <label for="customerName">Full Name</label>
                    <input type="text" id="customerName" placeholder="Enter your full name">
                </div>
                <div class="form-group">
                    <label for="customerPhone">Phone Number</label>
                    <input type="tel" id="customerPhone" placeholder="Enter your phone number">
                </div>
                <div class="form-group">
                    <label for="pickupLocation">Pickup Location</label>
                    <select id="pickupLocation">
                        <option value="">Select pickup location</option>
                        <?php
                        $locations = get_option('uncle_chans_pickup_locations', array());
                        foreach ($locations as $location) {
                            echo '<option value="' . esc_attr($location['name']) . '">' . esc_html($location['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pickupTime">Preferred Pickup Time</label>
                    <select id="pickupTime">
                        <option value="">Select pickup time</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="11:30">11:30 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="12:30">12:30 PM</option>
                        <option value="13:00">1:00 PM</option>
                        <option value="13:30">1:30 PM</option>
                        <option value="17:00">5:00 PM</option>
                        <option value="17:30">5:30 PM</option>
                        <option value="18:00">6:00 PM</option>
                        <option value="18:30">6:30 PM</option>
                        <option value="19:00">7:00 PM</option>
                        <option value="19:30">7:30 PM</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="militaryDiscount">
                        I qualify for the <?php echo get_option('uncle_chans_military_discount', '15'); ?>% military discount (ID required at pickup)
                    </label>
                </div>
                <div class="form-actions">
                    <button class="btn btn-secondary" id="cancelCheckout">Cancel</button>
                    <button class="btn" id="nextToPayment">Continue to Payment</button>
                </div>
            </div>
            
            <!-- Step 2: Payment -->
            <div class="checkout-step-content" id="checkoutStep2">
                <h4 class="step-title">Payment Method</h4>
                <p class="step-description">Select your preferred payment method</p>
                
                <div class="payment-options">
                    <div class="payment-option" data-method="zelle">
                        <div class="payment-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="payment-name">Zelle</div>
                    </div>
                    <div class="payment-option" data-method="stripe">
                        <div class="payment-icon">
                            <i class="fab fa-cc-stripe"></i>
                        </div>
                        <div class="payment-name">Credit/Debit Card</div>
                    </div>
                    <div class="payment-option" data-method="paypal">
                        <div class="payment-icon">
                            <i class="fab fa-paypal"></i>
                        </div>
                        <div class="payment-name">PayPal</div>
                    </div>
                    <div class="payment-option" data-method="navy">
                        <div class="payment-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <div class="payment-name">Navy Federal</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button class="btn btn-secondary" id="backToDetails">Back</button>
                    <button class="btn" id="completeOrder">Complete Order</button>
                </div>
            </div>
            
            <!-- Step 3: Confirmation -->
            <div class="checkout-step-content" id="checkoutStep3">
                <div class="order-confirmation">
                    <div class="confirmation-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="confirmation-title">Order Confirmed!</h3>
                    <p>Thank you for your order. We're preparing your food now.</p>
                    
                    <div class="confirmation-details">
                        <p><strong>Order Number:</strong> <span class="order-number" id="orderNumber">UC-38492</span></p>
                        <p><strong>Pickup Location:</strong> <span id="confirmationLocation">NAS Sigonella - Location 1</span></p>
                        <p><strong>Pickup Time:</strong> <span id="confirmationTime">12:00 PM</span></p>
                        <p><strong>Estimated Ready:</strong> <span id="confirmationReady">25-35 minutes</span></p>
                    </div>
                    
                    <p>You'll receive a text message when your order is ready for pickup.</p>
                    <button class="btn" id="newOrder">Place Another Order</button>
                </div>
            </div>
        </div>
    </div>
</div>
