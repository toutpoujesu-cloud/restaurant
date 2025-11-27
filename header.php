<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- 🎨 STUNNING MENU BAR - Nobel Prize Worthy Design -->
<header class="menu-bar" id="menuBar">
    <!-- Premium Background Overlay -->
    <div class="menu-bar-overlay"></div>
    
    <div class="menu-container">
        <!-- Uncle Chan's Logo - Stunning Integration -->
        <a href="<?php echo home_url(); ?>" class="brand-logo">
            <div class="logo-circle">
                <div class="logo-image-wrapper">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/uncle-chans-logo.png" 
                         alt="Uncle Chan's Fried Chicken" 
                         class="logo-image"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="logo-fallback" style="display: none;">
                        <i class="fas fa-drumstick-bite"></i>
                    </div>
                </div>
                <div class="logo-glow"></div>
            </div>
            <div class="brand-text">
                <span class="brand-name"><?php echo get_option('uncle_chans_name', 'UNCLE CHAN\'S'); ?></span>
                <span class="brand-tagline">Legendary Fried Chicken</span>
            </div>
        </a>
        
        <!-- Navigation Links - Sleek & Modern -->
        <nav class="nav-links" role="navigation" aria-label="Main navigation">
            <a href="#menu-section" class="nav-item">
                <i class="fas fa-utensils nav-icon"></i>
                <span>MENU</span>
                <div class="nav-underline"></div>
            </a>
            <a href="#about" class="nav-item">
                <i class="fas fa-book-open nav-icon"></i>
                <span>OUR STORY</span>
                <div class="nav-underline"></div>
            </a>
            <a href="#locations" class="nav-item">
                <i class="fas fa-map-marker-alt nav-icon"></i>
                <span>LOCATIONS</span>
                <div class="nav-underline"></div>
            </a>
            <a href="#testimonials" class="nav-item">
                <i class="fas fa-star nav-icon"></i>
                <span>REVIEWS</span>
                <div class="nav-underline"></div>
            </a>
            <a href="#contact" class="nav-item">
                <i class="fas fa-phone nav-icon"></i>
                <span>CONTACT</span>
                <div class="nav-underline"></div>
            </a>
        </nav>
        
        <!-- CTA Section - Premium Actions -->
        <div class="nav-actions">
            <!-- Military Badge -->
            <div class="military-indicator">
                <i class="fas fa-shield-alt"></i>
                <span><?php echo get_option('uncle_chans_military_discount', '15'); ?>% OFF</span>
            </div>
            
            <!-- Shopping Cart -->
            <div class="cart-button" id="cartIcon" role="button" aria-label="Shopping Cart">
                <div class="cart-icon-wrapper">
                    <a href="<?php echo home_url('/cart'); ?>" class="cart-link">
                        <i class="fas fa-shopping-bag"></i>
                        <?php
                        $cart = ucfc_get_cart();
                        $cart_count = $cart->get_item_count();
                        ?>
                        <span class="cart-count-badge" style="<?php echo $cart_count > 0 ? '' : 'display:none;'; ?>">
                            <?php echo $cart_count; ?>
                        </span>
                        <div class="cart-pulse"></div>
                    </a>
                    
                    <!-- Mini Cart Dropdown -->
                    <div class="mini-cart-dropdown">
                        <div class="mini-cart-header">
                            <h4>Shopping Cart</h4>
                            <span class="mini-cart-count"><?php echo $cart_count; ?> <?php echo $cart_count == 1 ? 'item' : 'items'; ?></span>
                        </div>
                        
                        <?php 
                        $cart_items = $cart->get_cart();
                        $cart_totals = $cart->get_totals();
                        ?>
                        
                        <?php if (!empty($cart_items)): ?>
                            <div class="mini-cart-items">
                                <?php foreach (array_slice($cart_items, 0, 3) as $item): ?>
                                    <?php $thumbnail = get_the_post_thumbnail_url($item->product_id, 'thumbnail'); ?>
                                    <div class="mini-cart-item">
                                        <div class="mini-item-image">
                                            <?php if ($thumbnail): ?>
                                                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($item->product_name); ?>">
                                            <?php else: ?>
                                                <i class="fas fa-utensils"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mini-item-details">
                                            <h5><?php echo esc_html($item->product_name); ?></h5>
                                            <span class="mini-item-qty"><?php echo $item->quantity; ?>x $<?php echo number_format($item->price, 2); ?></span>
                                        </div>
                                        <div class="mini-item-subtotal">
                                            $<?php echo number_format($item->subtotal, 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($cart_items) > 3): ?>
                                    <div class="mini-cart-more">
                                        + <?php echo count($cart_items) - 3; ?> more item<?php echo (count($cart_items) - 3) > 1 ? 's' : ''; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mini-cart-footer">
                                <div class="mini-cart-total">
                                    <span>Total:</span>
                                    <strong>$<?php echo $cart_totals['total']; ?></strong>
                                </div>
                                <div class="mini-cart-actions">
                                    <a href="<?php echo home_url('/cart'); ?>" class="btn btn-secondary btn-sm">View Cart</a>
                                    <a href="<?php echo home_url('/checkout'); ?>" class="btn btn-primary btn-sm">Checkout</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mini-cart-empty">
                                <i class="fas fa-shopping-cart"></i>
                                <p>Your cart is empty</p>
                                <a href="<?php echo home_url('/menu'); ?>" class="btn btn-primary btn-sm">Browse Menu</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Primary CTA -->
            <a href="#menu-section" class="order-button">
                <span class="button-text">ORDER NOW</span>
                <i class="fas fa-arrow-right button-icon"></i>
                <div class="button-shine"></div>
            </a>
        </div>
        
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span class="menu-line"></span>
            <span class="menu-line"></span>
            <span class="menu-line"></span>
        </button>
    </div>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/uncle-chans-logo.png" 
                 alt="Uncle Chan's" 
                 class="mobile-logo">
            <button class="mobile-menu-close" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="mobile-nav-links">
            <a href="#menu-section" class="mobile-nav-item">
                <i class="fas fa-utensils"></i>
                <span>MENU</span>
            </a>
            <a href="#about" class="mobile-nav-item">
                <i class="fas fa-book-open"></i>
                <span>OUR STORY</span>
            </a>
            <a href="#locations" class="mobile-nav-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>LOCATIONS</span>
            </a>
            <a href="#testimonials" class="mobile-nav-item">
                <i class="fas fa-star"></i>
                <span>REVIEWS</span>
            </a>
            <a href="#contact" class="mobile-nav-item">
                <i class="fas fa-phone"></i>
                <span>CONTACT</span>
            </a>
        </nav>
        <div class="mobile-menu-footer">
            <a href="#menu-section" class="mobile-order-button">
                <i class="fas fa-shopping-bag"></i>
                <span>ORDER NOW</span>
            </a>
            <div class="mobile-military-badge">
                <i class="fas fa-shield-alt"></i>
                <span><?php echo get_option('uncle_chans_military_discount', '15'); ?>% Military Discount</span>
            </div>
        </div>
    </div>
</header>
