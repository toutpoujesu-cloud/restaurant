/**
 * Shopping Cart Frontend JavaScript
 * 
 * Handles:
 * - Add to cart buttons
 * - Cart badge updates
 * - Mini cart dropdown
 * - Cart page interactions
 * - Quantity updates
 * - Item removal
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Cart Manager
     */
    const CartManager = {
        
        /**
         * Initialize
         */
        init() {
            this.bindEvents();
            this.updateCartBadge();
        },
        
        /**
         * Bind event listeners
         */
        bindEvents() {
            // Add to cart buttons
            $(document).on('click', '.add-to-cart-btn', this.handleAddToCart.bind(this));
            
            // Remove from cart buttons
            $(document).on('click', '.remove-cart-item', this.handleRemoveItem.bind(this));
            
            // Update quantity buttons
            $(document).on('click', '.qty-increase', this.handleQuantityIncrease.bind(this));
            $(document).on('click', '.qty-decrease', this.handleQuantityDecrease.bind(this));
            
            // Manual quantity input
            $(document).on('change', '.cart-item-quantity', this.handleQuantityChange.bind(this));
            
            // Clear cart button
            $(document).on('click', '.clear-cart-btn', this.handleClearCart.bind(this));
            
            // Cart icon hover (show mini cart)
            $(document).on('mouseenter', '.cart-icon-wrapper', this.showMiniCart.bind(this));
            $(document).on('mouseleave', '.cart-icon-wrapper', this.hideMiniCart.bind(this));
        },
        
        /**
         * Handle add to cart
         */
        handleAddToCart(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const productId = $btn.data('product-id');
            const quantity = parseInt($btn.data('quantity') || 1);
            
            // Get options if available
            const options = {};
            const $parent = $btn.closest('.menu-item, .product-card');
            
            if ($parent.find('.option-size:checked').length) {
                options.size = $parent.find('.option-size:checked').val();
            }
            if ($parent.find('.option-spice:checked').length) {
                options.spice = $parent.find('.option-spice:checked').val();
            }
            if ($parent.find('.option-extras:checked').length) {
                options.extras = [];
                $parent.find('.option-extras:checked').each(function() {
                    options.extras.push($(this).val());
                });
            }
            
            // Show loading state
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
            
            // AJAX request
            $.ajax({
                url: ucfcCart.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_add_to_cart',
                    nonce: ucfcCart.nonce,
                    product_id: productId,
                    quantity: quantity,
                    options: options
                },
                success: (response) => {
                    if (response.success) {
                        // Update cart badge
                        this.updateCartBadge(response.data.cart_count);
                        
                        // Show success message
                        this.showNotification(ucfcCart.messages.added, 'success');
                        
                        // Trigger custom event
                        $(document).trigger('ucfc:cart:item-added', [response.data]);
                        
                        // Reset button
                        $btn.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                        
                        // Show mini cart briefly
                        this.showMiniCart();
                        setTimeout(() => this.hideMiniCart(), 3000);
                    } else {
                        this.showNotification(response.data.message, 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                    }
                },
                error: () => {
                    this.showNotification(ucfcCart.messages.error, 'error');
                    $btn.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                }
            });
        },
        
        /**
         * Handle remove item
         */
        handleRemoveItem(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const cartItemId = $btn.data('cart-item-id');
            
            if (!confirm('Remove this item from cart?')) {
                return;
            }
            
            // Show loading
            $btn.html('<i class="fas fa-spinner fa-spin"></i>');
            
            // AJAX request
            $.ajax({
                url: ucfcCart.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_remove_from_cart',
                    nonce: ucfcCart.nonce,
                    cart_item_id: cartItemId
                },
                success: (response) => {
                    if (response.success) {
                        // Remove item from DOM
                        $btn.closest('.cart-item').fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if cart is empty
                            if ($('.cart-item').length === 0) {
                                $('.cart-items-container').html('<p class="empty-cart-message">Your cart is empty</p>');
                                $('.cart-totals, .cart-actions').hide();
                            }
                        });
                        
                        // Update cart badge and totals
                        this.updateCartBadge(response.data.cart_count);
                        this.updateCartTotals(response.data);
                        
                        // Show notification
                        this.showNotification(ucfcCart.messages.removed, 'success');
                        
                        // Trigger custom event
                        $(document).trigger('ucfc:cart:item-removed', [response.data]);
                    } else {
                        this.showNotification(response.data.message, 'error');
                        $btn.html('<i class="fas fa-trash"></i>');
                    }
                },
                error: () => {
                    this.showNotification(ucfcCart.messages.error, 'error');
                    $btn.html('<i class="fas fa-trash"></i>');
                }
            });
        },
        
        /**
         * Handle quantity increase
         */
        handleQuantityIncrease(e) {
            e.preventDefault();
            const $input = $(e.currentTarget).siblings('.cart-item-quantity');
            const newQty = parseInt($input.val()) + 1;
            $input.val(newQty).trigger('change');
        },
        
        /**
         * Handle quantity decrease
         */
        handleQuantityDecrease(e) {
            e.preventDefault();
            const $input = $(e.currentTarget).siblings('.cart-item-quantity');
            const newQty = Math.max(1, parseInt($input.val()) - 1);
            $input.val(newQty).trigger('change');
        },
        
        /**
         * Handle quantity change
         */
        handleQuantityChange(e) {
            const $input = $(e.currentTarget);
            const cartItemId = $input.data('cart-item-id');
            const quantity = parseInt($input.val()) || 1;
            
            // Prevent negative or zero values
            if (quantity < 1) {
                $input.val(1);
                return;
            }
            
            // Debounce AJAX call
            clearTimeout(this.quantityUpdateTimeout);
            this.quantityUpdateTimeout = setTimeout(() => {
                this.updateQuantity(cartItemId, quantity);
            }, 500);
        },
        
        /**
         * Update quantity via AJAX
         */
        updateQuantity(cartItemId, quantity) {
            $.ajax({
                url: ucfcCart.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_update_cart_quantity',
                    nonce: ucfcCart.nonce,
                    cart_item_id: cartItemId,
                    quantity: quantity
                },
                success: (response) => {
                    if (response.success) {
                        // Update cart badge and totals
                        this.updateCartBadge(response.data.cart_count);
                        this.updateCartTotals(response.data);
                        
                        // Update item subtotal
                        const item = response.data.cart_items.find(i => i.id == cartItemId);
                        if (item) {
                            $(`.cart-item[data-cart-item-id="${cartItemId}"] .item-subtotal`).text('$' + item.subtotal);
                        }
                        
                        // Show notification
                        this.showNotification(ucfcCart.messages.updated, 'success');
                        
                        // Trigger custom event
                        $(document).trigger('ucfc:cart:quantity-updated', [response.data]);
                    } else {
                        this.showNotification(response.data.message, 'error');
                    }
                },
                error: () => {
                    this.showNotification(ucfcCart.messages.error, 'error');
                }
            });
        },
        
        /**
         * Handle clear cart
         */
        handleClearCart(e) {
            e.preventDefault();
            
            if (!confirm('Clear all items from cart?')) {
                return;
            }
            
            $.ajax({
                url: ucfcCart.ajax_url,
                type: 'POST',
                data: {
                    action: 'ucfc_clear_cart',
                    nonce: ucfcCart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        // Empty cart UI
                        $('.cart-items-container').html('<p class="empty-cart-message">Your cart is empty</p>');
                        $('.cart-totals, .cart-actions').hide();
                        
                        // Update badge
                        this.updateCartBadge(0);
                        
                        // Show notification
                        this.showNotification(ucfcCart.messages.cleared, 'success');
                        
                        // Trigger custom event
                        $(document).trigger('ucfc:cart:cleared');
                    } else {
                        this.showNotification(response.data.message, 'error');
                    }
                },
                error: () => {
                    this.showNotification(ucfcCart.messages.error, 'error');
                }
            });
        },
        
        /**
         * Update cart badge
         */
        updateCartBadge(count) {
            if (count === undefined) {
                // Fetch from server
                $.ajax({
                    url: ucfcCart.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ucfc_get_cart',
                        nonce: ucfcCart.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            this.updateCartBadge(response.data.cart_count);
                        }
                    }
                });
            } else {
                // Update badge
                const $badge = $('.cart-count-badge');
                $badge.text(count);
                
                if (count > 0) {
                    $badge.show().addClass('bounce');
                    setTimeout(() => $badge.removeClass('bounce'), 300);
                } else {
                    $badge.hide();
                }
            }
        },
        
        /**
         * Update cart totals
         */
        updateCartTotals(data) {
            $('.cart-subtotal-value').text(data.cart_subtotal);
            $('.cart-tax-value').text(data.cart_tax || '$0.00');
            $('.cart-total-value').text(data.cart_total);
        },
        
        /**
         * Show mini cart
         */
        showMiniCart() {
            const $miniCart = $('.mini-cart-dropdown');
            if ($miniCart.length) {
                $miniCart.addClass('show');
            }
        },
        
        /**
         * Hide mini cart
         */
        hideMiniCart() {
            const $miniCart = $('.mini-cart-dropdown');
            if ($miniCart.length) {
                setTimeout(() => {
                    if (!$miniCart.is(':hover')) {
                        $miniCart.removeClass('show');
                    }
                }, 200);
            }
        },
        
        /**
         * Show notification
         */
        showNotification(message, type = 'success') {
            // Remove existing notifications
            $('.cart-notification').remove();
            
            // Create notification
            const $notification = $('<div>')
                .addClass('cart-notification')
                .addClass('notification-' + type)
                .html(`
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                `)
                .appendTo('body')
                .fadeIn(200);
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                $notification.fadeOut(200, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };
    
    // Initialize on document ready
    $(document).ready(() => {
        CartManager.init();
    });
    
})(jQuery);
