/**
 * Stripe Payment Handler
 * Handles Stripe.js integration on checkout page
 */

(function($) {
    'use strict';
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if (typeof ucfcStripe === 'undefined' || !ucfcStripe.publishableKey) {
            console.warn('Stripe not configured - payment will be marked as pending');
            return;
        }
        
        StripePaymentHandler.init();
    });
    
    const StripePaymentHandler = {
        stripe: null,
        elements: null,
        cardElement: null,
        paymentIntentClientSecret: null,
        
        /**
         * Initialize Stripe
         */
        init: function() {
            // Initialize Stripe with publishable key
            this.stripe = Stripe(ucfcStripe.publishableKey);
            
            // Create Elements instance
            this.elements = this.stripe.elements();
            
            // Create card element
            this.cardElement = this.elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#32325d',
                        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        '::placeholder': {
                            color: '#aab7c4',
                        },
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a',
                    },
                },
            });
            
            // Mount card element to DOM
            const cardContainer = document.getElementById('card-element');
            if (cardContainer) {
                this.cardElement.mount('#card-element');
                
                // Display card errors
                this.cardElement.on('change', function(event) {
                    const displayError = document.getElementById('card-errors');
                    if (event.error) {
                        displayError.textContent = event.error.message;
                    } else {
                        displayError.textContent = '';
                    }
                });
            }
            
            // Hide cash payment option when Stripe is available
            this.updatePaymentOptions();
            
            // Listen for order type changes to update amount
            $('input[name="order_type"]').on('change', function() {
                StripePaymentHandler.createPaymentIntent();
            });
            
            // Create initial payment intent when step 3 is reached
            this.observeStepChanges();
        },
        
        /**
         * Update payment options UI
         */
        updatePaymentOptions: function() {
            // Show Stripe payment form
            $('#stripe-payment-form').show();
            
            // Update payment method label
            $('label[for="payment_credit_card"]').text('Credit/Debit Card (Powered by Stripe)');
            
            // Hide "integration coming soon" notice
            $('.payment-notice').hide();
        },
        
        /**
         * Observe step changes to trigger payment intent creation
         */
        observeStepChanges: function() {
            const self = this;
            
            // Watch for step 3 to become active
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.target.classList.contains('active') && 
                        mutation.target.id === 'step-3') {
                        self.createPaymentIntent();
                    }
                });
            });
            
            const step3 = document.getElementById('step-3');
            if (step3) {
                observer.observe(step3, { attributes: true, attributeFilter: ['class'] });
            }
        },
        
        /**
         * Create Payment Intent on Stripe
         */
        createPaymentIntent: function() {
            if (this.paymentIntentClientSecret) {
                return; // Already created
            }
            
            const orderType = $('input[name="order_type"]:checked').val() || 'pickup';
            
            $.ajax({
                url: ucfcStripe.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_create_payment_intent',
                    nonce: ucfcStripe.nonce,
                    order_type: orderType,
                },
                success: (response) => {
                    if (response.success) {
                        this.paymentIntentClientSecret = response.data.clientSecret;
                        console.log('Payment Intent created for $' + response.data.amount);
                    } else {
                        console.error('Failed to create Payment Intent:', response.data.message);
                        alert('Payment system error: ' + response.data.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error:', error);
                    alert('Payment system unavailable. Please try again.');
                }
            });
        },
        
        /**
         * Process payment and submit order
         * Called by checkout form submission
         */
        processPayment: function(formData, callback) {
            const paymentMethod = $('input[name="payment_method"]:checked').val();
            
            // If cash payment, proceed directly to order creation
            if (paymentMethod === 'cash') {
                callback(null);
                return;
            }
            
            // Process Stripe payment
            if (!this.paymentIntentClientSecret) {
                callback('Payment not initialized. Please refresh and try again.');
                return;
            }
            
            // Show processing message
            $('#card-errors').html('<div class="processing">Processing payment...</div>');
            
            // Confirm card payment with Stripe
            this.stripe.confirmCardPayment(this.paymentIntentClientSecret, {
                payment_method: {
                    card: this.cardElement,
                    billing_details: {
                        name: formData.customer_name,
                        email: formData.customer_email,
                        phone: formData.customer_phone,
                        address: formData.customer_address ? {
                            line1: formData.customer_address,
                        } : null,
                    },
                },
            }).then((result) => {
                if (result.error) {
                    // Show error to customer
                    $('#card-errors').html('<div class="error">' + result.error.message + '</div>');
                    callback(result.error.message);
                } else {
                    // Payment successful
                    if (result.paymentIntent.status === 'succeeded') {
                        $('#card-errors').html('<div class="success">✓ Payment successful!</div>');
                        
                        // Verify payment on backend before creating order
                        this.verifyPayment(result.paymentIntent.id, function(error) {
                            if (error) {
                                callback(error);
                            } else {
                                // Add payment intent ID to form data for order record
                                formData.payment_intent_id = result.paymentIntent.id;
                                callback(null, formData);
                            }
                        });
                    } else {
                        callback('Payment not completed. Status: ' + result.paymentIntent.status);
                    }
                }
            }).catch((error) => {
                console.error('Stripe error:', error);
                callback('Payment processing error. Please try again.');
            });
        },
        
        /**
         * Verify payment on backend
         */
        verifyPayment: function(paymentIntentId, callback) {
            $.ajax({
                url: ucfcStripe.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_confirm_payment',
                    nonce: ucfcStripe.nonce,
                    payment_intent_id: paymentIntentId,
                },
                success: (response) => {
                    if (response.success) {
                        console.log('Payment verified: $' + response.data.amount);
                        callback(null);
                    } else {
                        callback('Payment verification failed: ' + response.data.message);
                    }
                },
                error: (xhr, status, error) => {
                    callback('Payment verification error. Please contact support.');
                }
            });
        },
    };
    
    // Expose to global scope for checkout form integration
    window.StripePaymentHandler = StripePaymentHandler;
    
})(jQuery);
