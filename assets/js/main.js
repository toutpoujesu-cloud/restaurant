// Uncle Chan's Fried Chicken - Main JavaScript

// Menu Data
const menuData = {
    sides: [
        { id: 1, name: "Mashed Potatoes", description: "Creamy and buttery" },
        { id: 2, name: "Macaroni and Cheese", description: "Classic comfort food" },
        { id: 3, name: "Coleslaw", description: "Fresh and tangy" },
        { id: 4, name: "Mixed Collard Greens", description: "Southern-style with smoked turkey" }
    ],
    sauces: [
        { id: 1, name: "Ghost Pepper Butter", description: "Dangerously Spicy [EXTREME]" },
        { id: 2, name: "Garlic Parmesan", description: "Creamy & Bold" },
        { id: 3, name: "Hot Hot Honey", description: "Sweet & Spicy" },
        { id: 4, name: "Lemon Pepper", description: "Tangy & Zesty" }
    ]
};

// Cart State
let cart = [];
let currentMeal = null;
let selectedSides = [];
let selectedSauces = [];

// Initialize when document is ready
jQuery(document).ready(function($) {
    initializeMenuTabs();
    initializeCustomizeButtons();
    initializeCart();
    initializeCheckout();
    updateCartDisplay();
    initializeScrollEffects();
    initializeMobileMenu();
    initializeMenuBarEffects();
    initializeOurStorySection();
    
    // Timer functionality
    startTimer(2 * 3600 + 44 * 60 + 50, $('#timer'));
});

// Menu Tab Functionality
function initializeMenuTabs() {
    jQuery('.menu-tab').on('click', function() {
        const category = jQuery(this).data('category');
        
        // Update active tab
        jQuery('.menu-tab').removeClass('active');
        jQuery(this).addClass('active');
        
        // Show corresponding category
        jQuery('.menu-category').removeClass('active');
        jQuery('#' + category + '-menu').addClass('active');
    });
}

// Customize Button Functionality
function initializeCustomizeButtons() {
    jQuery('.customize-btn').on('click', function() {
        const mealName = jQuery(this).data('meal');
        const mealPrice = parseFloat(jQuery(this).data('price'));
        
        currentMeal = {
            name: mealName,
            price: mealPrice
        };
        
        // Reset selections
        selectedSides = [];
        selectedSauces = [];
        
        // Populate sides and sauces
        populateSides();
        populateSauces();
        
        // Update final price
        jQuery('#finalPrice').text(mealPrice.toFixed(2));
        
        // Show modal
        jQuery('#modalMealName').text(mealName);
        jQuery('#customizeModal').addClass('active');
        
        // Reset to first step
        jQuery('#stepSides').addClass('active');
        jQuery('#stepSauces').removeClass('active');
        
        // Update counters
        updateSideCounter();
        updateSauceCounter();
    });
}

// Populate Sides Options
function populateSides() {
    const sidesContainer = jQuery('#sidesOptions');
    sidesContainer.empty();
    
    menuData.sides.forEach(function(side) {
        const sideElement = jQuery('<div>')
            .addClass('option-card')
            .attr('data-id', side.id)
            .html(
                '<div class="option-name">' + side.name + '</div>' +
                '<div class="option-description">' + side.description + '</div>'
            )
            .on('click', function() {
                toggleSideSelection(side.id);
            });
        
        sidesContainer.append(sideElement);
    });
}

// Populate Sauces Options
function populateSauces() {
    const saucesContainer = jQuery('#saucesOptions');
    saucesContainer.empty();
    
    menuData.sauces.forEach(function(sauce) {
        const sauceElement = jQuery('<div>')
            .addClass('option-card')
            .attr('data-id', sauce.id)
            .html(
                '<div class="option-name">' + sauce.name + '</div>' +
                '<div class="option-description">' + sauce.description + '</div>'
            )
            .on('click', function() {
                toggleSauceSelection(sauce.id);
            });
        
        saucesContainer.append(sauceElement);
    });
}

// Toggle Side Selection
function toggleSideSelection(sideId) {
    const index = selectedSides.indexOf(sideId);
    const sideElement = jQuery('.option-card[data-id="' + sideId + '"]');
    
    if (index === -1) {
        if (selectedSides.length < 2) {
            selectedSides.push(sideId);
            sideElement.addClass('selected');
        }
    } else {
        selectedSides.splice(index, 1);
        sideElement.removeClass('selected');
    }
    
    updateSideCounter();
}

// Toggle Sauce Selection
function toggleSauceSelection(sauceId) {
    const index = selectedSauces.indexOf(sauceId);
    const sauceElement = jQuery('.option-card[data-id="' + sauceId + '"]');
    
    if (index === -1) {
        if (selectedSauces.length < 2) {
            selectedSauces.push(sauceId);
            sauceElement.addClass('selected');
        }
    } else {
        selectedSauces.splice(index, 1);
        sauceElement.removeClass('selected');
    }
    
    updateSauceCounter();
}

// Update Side Counter
function updateSideCounter() {
    jQuery('#sideCount').text(selectedSides.length);
}

// Update Sauce Counter
function updateSauceCounter() {
    jQuery('#sauceCount').text(selectedSauces.length);
}

// Cart Functionality
function initializeCart() {
    // Open cart sidebar
    jQuery('#cartIcon').on('click', function() {
        jQuery('#cartSidebar').addClass('active');
    });
    
    // Close cart sidebar
    jQuery('#closeCart').on('click', function() {
        jQuery('#cartSidebar').removeClass('active');
    });
    
    // Close modal
    jQuery('#closeModal').on('click', function() {
        jQuery('#customizeModal').removeClass('active');
    });
    
    // Navigation between customization steps
    jQuery('#nextToSauces').on('click', function() {
        if (selectedSides.length === 2) {
            jQuery('#stepSides').removeClass('active');
            jQuery('#stepSauces').addClass('active');
        } else {
            alert('Please select 2 sides to continue');
        }
    });
    
    jQuery('#backToSides').on('click', function() {
        jQuery('#stepSauces').removeClass('active');
        jQuery('#stepSides').addClass('active');
    });
    
    // Add to cart
    jQuery('#addToCart').on('click', function() {
        if (currentMeal && selectedSides.length === 2) {
            // Get selected sides and sauces names
            const sidesNames = selectedSides.map(function(id) {
                const side = menuData.sides.find(s => s.id === id);
                return side ? side.name : '';
            });
            
            const saucesNames = selectedSauces.map(function(id) {
                const sauce = menuData.sauces.find(s => s.id === id);
                return sauce ? sauce.name : '';
            });
            
            // Add to cart
            cart.push({
                id: Date.now(),
                name: currentMeal.name,
                price: currentMeal.price,
                sides: sidesNames,
                sauces: saucesNames,
                quantity: 1
            });
            
            // Update cart display
            updateCartDisplay();
            
            // Close modal
            jQuery('#customizeModal').removeClass('active');
            
            // Show cart
            jQuery('#cartSidebar').addClass('active');
        } else {
            alert('Please select 2 sides before adding to cart');
        }
    });
}

// Update Cart Display
function updateCartDisplay() {
    // Update cart count
    const totalItems = cart.reduce(function(total, item) {
        return total + item.quantity;
    }, 0);
    jQuery('.cart-count').text(totalItems);
    
    // Update cart items
    if (cart.length === 0) {
        jQuery('#cartItems').html(
            '<div class="empty-cart">' +
            '<i class="fas fa-shopping-cart"></i>' +
            '<p>Your cart is empty</p>' +
            '<p>Add some delicious items to get started!</p>' +
            '</div>'
        );
    } else {
        let itemsHTML = '';
        let total = 0;
        
        cart.forEach(function(item) {
            total += item.price * item.quantity;
            
            itemsHTML += 
                '<div class="cart-item">' +
                '<div class="cart-item-details">' +
                '<h4>' + item.name + '</h4>' +
                (item.sides.length > 0 ? '<p>Sides: ' + item.sides.join(', ') + '</p>' : '') +
                (item.sauces.length > 0 ? '<p>Sauces: ' + item.sauces.join(', ') + '</p>' : '') +
                '<div class="cart-item-actions">' +
                '<button class="quantity-btn decrease-quantity" data-id="' + item.id + '">-</button>' +
                '<span class="cart-item-quantity">' + item.quantity + '</span>' +
                '<button class="quantity-btn increase-quantity" data-id="' + item.id + '">+</button>' +
                '<button class="remove-item" data-id="' + item.id + '">Remove</button>' +
                '</div>' +
                '</div>' +
                '<div class="cart-item-price">€' + (item.price * item.quantity).toFixed(2) + '</div>' +
                '</div>';
        });
        
        jQuery('#cartItems').html(itemsHTML);
        jQuery('#cartTotal').text(total.toFixed(2));
        
        // Add event listeners to quantity buttons
        jQuery('.decrease-quantity').on('click', function() {
            const itemId = parseInt(jQuery(this).data('id'));
            updateQuantity(itemId, -1);
        });
        
        jQuery('.increase-quantity').on('click', function() {
            const itemId = parseInt(jQuery(this).data('id'));
            updateQuantity(itemId, 1);
        });
        
        jQuery('.remove-item').on('click', function() {
            const itemId = parseInt(jQuery(this).data('id'));
            removeFromCart(itemId);
        });
    }
}

// Update Item Quantity
function updateQuantity(itemId, change) {
    const itemIndex = cart.findIndex(item => item.id === itemId);
    
    if (itemIndex !== -1) {
        cart[itemIndex].quantity += change;
        
        if (cart[itemIndex].quantity <= 0) {
            cart.splice(itemIndex, 1);
        }
        
        updateCartDisplay();
    }
}

// Remove Item from Cart
function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    updateCartDisplay();
}

// Checkout Functionality
function initializeCheckout() {
    // Open checkout
    jQuery('#checkoutBtn').on('click', function() {
        if (cart.length > 0) {
            jQuery('#checkoutModal').addClass('active');
            jQuery('#cartSidebar').removeClass('active');
        }
    });
    
    // Close checkout
    jQuery('#closeCheckout').on('click', function() {
        jQuery('#checkoutModal').removeClass('active');
    });
    
    // Cancel checkout
    jQuery('#cancelCheckout').on('click', function() {
        jQuery('#checkoutModal').removeClass('active');
    });
    
    // Navigation between checkout steps
    jQuery('#nextToPayment').on('click', function() {
        // Validate form
        const name = jQuery('#customerName').val();
        const phone = jQuery('#customerPhone').val();
        const location = jQuery('#pickupLocation').val();
        const time = jQuery('#pickupTime').val();
        
        if (!name || !phone || !location || !time) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Move to next step
        jQuery('#checkoutStep1').removeClass('active');
        jQuery('#checkoutStep2').addClass('active');
        
        // Update checkout steps
        jQuery('.checkout-step').eq(0).removeClass('active');
        jQuery('.checkout-step').eq(1).addClass('active');
    });
    
    jQuery('#backToDetails').on('click', function() {
        jQuery('#checkoutStep2').removeClass('active');
        jQuery('#checkoutStep1').addClass('active');
        
        // Update checkout steps
        jQuery('.checkout-step').eq(1).removeClass('active');
        jQuery('.checkout-step').eq(0).addClass('active');
    });
    
    // Payment method selection
    jQuery(document).on('click', '.payment-option', function() {
        jQuery('.payment-option').removeClass('selected');
        jQuery(this).addClass('selected');
    });
    
    // Complete order
    jQuery('#completeOrder').on('click', function() {
        const selectedPayment = jQuery('.payment-option.selected');
        
        if (selectedPayment.length === 0) {
            alert('Please select a payment method');
            return;
        }
        
        // Move to confirmation step
        jQuery('#checkoutStep2').removeClass('active');
        jQuery('#checkoutStep3').addClass('active');
        
        // Update checkout steps
        jQuery('.checkout-step').eq(1).removeClass('active');
        jQuery('.checkout-step').eq(2).addClass('active');
        
        // Update confirmation details
        jQuery('#confirmationLocation').text(
            jQuery('#pickupLocation option:selected').text()
        );
        jQuery('#confirmationTime').text(
            jQuery('#pickupTime option:selected').text()
        );
        
        // Generate random order number
        jQuery('#orderNumber').text('UC-' + Math.floor(10000 + Math.random() * 90000));
    });
    
    // Start new order
    jQuery('#newOrder').on('click', function() {
        // Clear cart
        cart = [];
        updateCartDisplay();
        
        // Close modals
        jQuery('#checkoutModal').removeClass('active');
        jQuery('#customizeModal').removeClass('active');
        
        // Reset checkout form
        jQuery('#checkoutStep3').removeClass('active');
        jQuery('#checkoutStep1').addClass('active');
        jQuery('.checkout-step').removeClass('active').eq(0).addClass('active');
    });
}

// Timer Function
function startTimer(duration, display) {
    let timer = duration;
    setInterval(function () {
        let hours = parseInt(timer / 3600, 10);
        let minutes = parseInt((timer % 3600) / 60, 10);
        let seconds = parseInt(timer % 60, 10);

        hours = hours < 10 ? "0" + hours : hours;
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.text(hours + ":" + minutes + ":" + seconds);

        if (--timer < 0) {
            timer = duration;
        }
    }, 1000);
}

// Menu Bar Scroll Effect
function initializeScrollEffects() {
    jQuery(window).on('scroll', function() {
        if (jQuery(window).scrollTop() > 50) {
            jQuery('#menuBar').addClass('scrolled');
        } else {
            jQuery('#menuBar').removeClass('scrolled');
        }
    });
}

// ========================================
// 🎨 STUNNING MOBILE MENU - SMOOTH ANIMATIONS
// ========================================

function initializeMobileMenu() {
    const $ = jQuery;
    const mobileToggle = $('.mobile-menu-toggle');
    const mobileMenu = $('.mobile-menu');
    const mobileClose = $('.mobile-menu-close');
    const mobileNavItems = $('.mobile-nav-item');
    
    // Open mobile menu
    mobileToggle.on('click', function() {
        const isExpanded = $(this).attr('aria-expanded') === 'true';
        
        if (!isExpanded) {
            // Open menu
            $(this).attr('aria-expanded', 'true');
            mobileMenu.addClass('active');
            $('body').css('overflow', 'hidden'); // Prevent scroll
            
            // Animate items in sequence
            mobileNavItems.each(function(index) {
                $(this).css({
                    opacity: 0,
                    transform: 'translateX(50px)'
                });
                
                setTimeout(() => {
                    $(this).css({
                        opacity: 1,
                        transform: 'translateX(0)',
                        transition: 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)'
                    });
                }, index * 50);
            });
        } else {
            // Close menu
            closeMobileMenu();
        }
    });
    
    // Close button
    mobileClose.on('click', function() {
        closeMobileMenu();
    });
    
    // Close on nav item click
    mobileNavItems.on('click', function() {
        setTimeout(() => {
            closeMobileMenu();
        }, 300);
    });
    
    // Close on outside click
    mobileMenu.on('click', function(e) {
        if ($(e.target).hasClass('mobile-menu')) {
            closeMobileMenu();
        }
    });
    
    // Close menu function
    function closeMobileMenu() {
        mobileToggle.attr('aria-expanded', 'false');
        mobileMenu.removeClass('active');
        $('body').css('overflow', ''); // Restore scroll
    }
    
    // Close on escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu.hasClass('active')) {
            closeMobileMenu();
        }
    });
}

// ========================================
// 🎨 MENU BAR ADVANCED EFFECTS
// ========================================

function initializeMenuBarEffects() {
    const $ = jQuery;
    const menuBar = $('#menuBar');
    const logoCircle = $('.logo-circle');
    const navItems = $('.nav-item');
    
    // Parallax effect on scroll
    let ticking = false;
    
    $(window).on('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                const scrolled = $(window).scrollTop();
                
                // Subtle logo rotation on scroll
                if (logoCircle.length) {
                    const rotation = scrolled * 0.1;
                    logoCircle.css('transform', `rotate(${rotation}deg)`);
                }
                
                ticking = false;
            });
            
            ticking = true;
        }
    });
    
    // Add ripple effect to order button
    $('.order-button').on('click', function(e) {
        const button = $(this);
        const circle = $('<span class="ripple"></span>');
        const diameter = Math.max(button.outerWidth(), button.outerHeight());
        const radius = diameter / 2;
        
        circle.css({
            width: diameter,
            height: diameter,
            left: e.pageX - button.offset().left - radius,
            top: e.pageY - button.offset().top - radius
        }).appendTo(button);
        
        setTimeout(() => circle.remove(), 600);
    });
    
    // Smooth scroll for nav links
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 800, 'swing');
        }
    });
    
    // Add hover sound effect (optional - uncomment if you have sound files)
    // navItems.on('mouseenter', function() {
    //     const hoverSound = new Audio('/wp-content/themes/uncle-chans-chicken/assets/sounds/hover.mp3');
    //     hoverSound.volume = 0.2;
    //     hoverSound.play().catch(() => {});
    // });
}

// Add ripple animation CSS dynamically
jQuery(document).ready(function($) {
    const rippleStyle = $('<style>').text(`
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: ripple-animation 0.6s ease-out;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `);
    
    $('head').append(rippleStyle);
});

// ========================================
// 🎨 OUR STORY SECTION - INTERACTIVE MAGIC
// ========================================

function initializeOurStorySection() {
    const $ = jQuery;
    
    // Initialize scroll animations
    initializeScrollAnimations();
    
    // Initialize testimonial carousel
    initializeTestimonialCarousel();
    
    // Initialize counter animations
    initializeCounterAnimations();
}

// Scroll-triggered animations (AOS - Animate On Scroll)
function initializeScrollAnimations() {
    const $ = jQuery;
    
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                $(entry.target).addClass('aos-animate');
            }
        });
    }, observerOptions);
    
    // Observe all elements with data-aos attribute
    $('[data-aos]').each(function() {
        observer.observe(this);
    });
}

// Testimonial Carousel - Auto-rotating with manual controls
function initializeTestimonialCarousel() {
    const $ = jQuery;
    let currentSlide = 0;
    const slides = $('.testimonial-slide');
    const dots = $('.carousel-dots .dot');
    const slideCount = slides.length;
    let autoplayInterval;
    
    if (slideCount === 0) return;
    
    // Function to show specific slide
    function showSlide(index) {
        // Hide all slides
        slides.removeClass('active');
        dots.removeClass('active');
        
        // Show selected slide
        $(slides[index]).addClass('active');
        $(dots[index]).addClass('active');
        
        currentSlide = index;
    }
    
    // Next slide
    function nextSlide() {
        const next = (currentSlide + 1) % slideCount;
        showSlide(next);
    }
    
    // Start autoplay
    function startAutoplay() {
        autoplayInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }
    
    // Stop autoplay
    function stopAutoplay() {
        clearInterval(autoplayInterval);
    }
    
    // Dot click handler
    dots.on('click', function() {
        const slideIndex = $(this).data('slide');
        showSlide(slideIndex);
        stopAutoplay();
        startAutoplay(); // Restart autoplay after manual selection
    });
    
    // Swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    $('.testimonial-slider').on('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    $('.testimonial-slider').on('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        if (touchEndX < touchStartX - 50) {
            // Swipe left - next slide
            nextSlide();
            stopAutoplay();
            startAutoplay();
        }
        if (touchEndX > touchStartX + 50) {
            // Swipe right - previous slide
            const prev = (currentSlide - 1 + slideCount) % slideCount;
            showSlide(prev);
            stopAutoplay();
            startAutoplay();
        }
    }
    
    // Start autoplay
    startAutoplay();
    
    // Pause on hover
    $('.testimonial-mini-carousel').hover(
        () => stopAutoplay(),
        () => startAutoplay()
    );
}

// Animated Counter - Counts up when visible
function initializeCounterAnimations() {
    const $ = jQuery;
    let countersAnimated = false;
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !countersAnimated) {
                countersAnimated = true;
                animateCounters();
            }
        });
    }, { threshold: 0.5 });
    
    const counterSection = $('.social-proof-counter')[0];
    if (counterSection) {
        counterObserver.observe(counterSection);
    }
    
    function animateCounters() {
        $('.counter-number').each(function() {
            const $this = $(this);
            const target = parseFloat($this.data('target'));
            const duration = 2000; // 2 seconds
            const steps = 60;
            const increment = target / steps;
            const stepDuration = duration / steps;
            let current = 0;
            
            const isDecimal = target % 1 !== 0;
            
            const timer = setInterval(() => {
                current += increment;
                
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                if (isDecimal) {
                    $this.text(current.toFixed(1));
                } else {
                    $this.text(Math.floor(current).toLocaleString());
                }
            }, stepDuration);
        });
    }
}

// Smooth scroll enhancement for story CTAs
jQuery(document).ready(function($) {
    $('.cta-primary-btn, .cta-secondary-btn').on('click', function(e) {
        const href = $(this).attr('href');
        
        if (href && href.startsWith('#')) {
            e.preventDefault();
            const target = $(href);
            
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800, 'swing');
            }
        }
    });
    
    // Add hover sound effect to story cards (optional)
    $('.story-card').on('mouseenter', function() {
        $(this).css('transition', 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)');
    });
    
    // Parallax effect on story timeline
    $(window).on('scroll', function() {
        const scrolled = $(window).scrollTop();
        const storySection = $('.our-story');
        
        if (storySection.length) {
            const sectionTop = storySection.offset().top;
            const sectionHeight = storySection.outerHeight();
            const windowHeight = $(window).height();
            
            // Check if section is in viewport
            if (scrolled + windowHeight > sectionTop && scrolled < sectionTop + sectionHeight) {
                const relativeScroll = (scrolled + windowHeight - sectionTop) / (sectionHeight + windowHeight);
                const parallaxAmount = relativeScroll * 50;
                
                // Apply subtle parallax to background pattern
                $('.story-pattern').css('transform', `translateY(${parallaxAmount}px)`);
            }
        }
    });
});
