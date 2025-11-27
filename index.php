<?php
/**
 * The main template file
 */

get_header(); ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content container">
        <h1>TASTE OF HOME.<br>DEPLOYED.</h1>
        <p>Uncle Chan's Legendary Fried Chicken. Proudly Serving Motta, Catania & NAS Sigonella.</p>
        <div class="hero-badges">
            <div class="hero-badge">
                <i class="fas fa-shield-alt"></i>
                <span><?php echo get_option('uncle_chans_military_discount', '15'); ?>% Military Discount</span>
            </div>
            <div class="hero-badge">
                <i class="fas fa-truck"></i>
                <span>Pickup at NAS Sigonella</span>
            </div>
            <div class="hero-badge">
                <i class="fas fa-clock"></i>
                <span>Open Until 10 PM</span>
            </div>
        </div>
        <a href="#menu-section" class="btn btn-large">🔥 ORDER MISSION READY MEALS 🔥</a>
    </div>
</section>

<!-- Flash Sale -->
<div class="flash-sale">
    <div class="container">
        <span>🚨 WEDNESDAY WING SPECIAL: 10 wings + 2 sides for €12!</span>
        <span class="timer" id="timer">02:44:50</span>
    </div>
</div>

<!-- Trust Barrage -->
<section class="trust-barrage">
    <div class="container">
        <div class="trust-grid">
            <div class="trust-item">
                <i class="fas fa-utensils"></i>
                <h3>Freshly Prepared</h3>
                <p>All meals made to order with the finest ingredients</p>
            </div>
            <div class="trust-item">
                <i class="fas fa-dollar-sign"></i>
                <h3><?php echo get_option('uncle_chans_military_discount', '15'); ?>% Military Discount</h3>
                <p>Always. Verified with ID. Thank you for your service!</p>
            </div>
            <div class="trust-item">
                <i class="fas fa-star"></i>
                <h3>Rated 4.8/5 Stars</h3>
                <p>Rated #1 Fried Chicken by Sigonella Service Members.</p>
            </div>
            <div class="trust-item">
                <i class="fas fa-calendar-check"></i>
                <h3>Twice Weekly Service</h3>
                <p>Order twice a week for pickup at NAS Sigonella 1 & 2</p>
            </div>
        </div>
    </div>
</section>

<!-- 🎨 OUR STORY - EMOTIONALLY ENGAGING & INTERACTIVE -->
<section class="our-story" id="about">
    <div class="story-background">
        <div class="story-pattern"></div>
    </div>
    
    <div class="container">
        <!-- Story Header -->
        <div class="story-header" data-aos="fade-up">
            <span class="story-overline">More Than Just Chicken</span>
            <h2 class="story-title">Welcome to the Family</h2>
            <p class="story-subtitle">Every piece of chicken tells a story. This is ours.</p>
        </div>

        <!-- Interactive Story Timeline -->
        <div class="story-timeline">
            <!-- Story Card 1: The Beginning -->
            <div class="story-card card-left" data-aos="fade-right">
                <div class="story-card-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="story-card-content">
                    <span class="story-year">The Beginning</span>
                    <h3>It Started With a Promise</h3>
                    <p>When Uncle Chan first tasted American Southern fried chicken during his military service, he made a promise: <strong>"One day, I'll bring this taste of home to those who serve."</strong></p>
                    <p>That day came when he opened his first kitchen near NAS Sigonella, dedicated to serving the men and women who protect our freedom.</p>
                </div>
            </div>

            <!-- Story Card 2: The Recipe -->
            <div class="story-card card-right" data-aos="fade-left">
                <div class="story-card-icon">
                    <i class="fas fa-drumstick-bite"></i>
                </div>
                <div class="story-card-content">
                    <span class="story-year">The Secret</span>
                    <h3>A Recipe Born From Love</h3>
                    <p>Our signature blend isn't just spices and herbs. It's <strong>24 ingredients</strong> carefully selected over 15 years of perfection.</p>
                    <p>Each piece is hand-seasoned, pressure-cooked to lock in juices, then finished to golden perfection. The result? <strong>Crispy on the outside, tender and juicy inside.</strong></p>
                    <div class="story-stats">
                        <div class="stat-item">
                            <span class="stat-number">24</span>
                            <span class="stat-label">Secret Spices</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">15+</span>
                            <span class="stat-label">Years Perfecting</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">100%</span>
                            <span class="stat-label">Made Fresh</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Story Card 3: The Mission -->
            <div class="story-card card-left" data-aos="fade-right">
                <div class="story-card-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="story-card-content">
                    <span class="story-year">Our Mission</span>
                    <h3>More Than Food. It's Family.</h3>
                    <p>Being away from home is tough. We know. That's why every meal we serve isn't just food—<strong>it's a warm hug from home.</strong></p>
                    <p>Our <?php echo get_option('uncle_chans_military_discount', '15'); ?>% military discount isn't a promotion. It's our way of saying <strong>thank you</strong> for your service, your sacrifice, and your family's strength.</p>
                    <blockquote class="story-quote">
                        <i class="fas fa-quote-left"></i>
                        <p>"When you're thousands of miles from home, a taste of familiar comfort means everything."</p>
                        <cite>- Uncle Chan</cite>
                    </blockquote>
                </div>
            </div>

            <!-- Story Card 4: The Community -->
            <div class="story-card card-right" data-aos="fade-left">
                <div class="story-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="story-card-content">
                    <span class="story-year">Today</span>
                    <h3>You're Part of Our Story Now</h3>
                    <p>Over <strong>50,000 meals served</strong> to service members and their families. Every order, every smile, every "This reminds me of home" keeps us going.</p>
                    <p>When you order from Uncle Chan's, you're not just a customer. <strong>You're family.</strong></p>
                    <div class="story-highlights">
                        <div class="highlight-badge">
                            <i class="fas fa-medal"></i>
                            <span>Veteran Owned</span>
                        </div>
                        <div class="highlight-badge">
                            <i class="fas fa-star"></i>
                            <span>5-Star Rated</span>
                        </div>
                        <div class="highlight-badge">
                            <i class="fas fa-award"></i>
                            <span>Community Choice</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Call to Action Section -->
        <div class="story-cta-section" data-aos="zoom-in">
            <div class="cta-box">
                <div class="cta-content">
                    <div class="cta-icon-wrapper">
                        <div class="cta-icon-circle">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="cta-pulse"></div>
                    </div>
                    <h3 class="cta-title">Ready to Join the Family?</h3>
                    <p class="cta-description">Experience the taste that's brought comfort to thousands. Your first order is waiting.</p>
                    
                    <!-- CTA Stats -->
                    <div class="cta-stats">
                        <div class="cta-stat">
                            <i class="fas fa-clock"></i>
                            <span>15 min prep time</span>
                        </div>
                        <div class="cta-stat">
                            <i class="fas fa-truck"></i>
                            <span>Easy pickup</span>
                        </div>
                        <div class="cta-stat">
                            <i class="fas fa-percent"></i>
                            <span><?php echo get_option('uncle_chans_military_discount', '15'); ?>% discount ready</span>
                        </div>
                    </div>

                    <!-- Primary CTA Buttons -->
                    <div class="cta-buttons">
                        <a href="#menu-section" class="cta-primary-btn">
                            <span class="btn-text">Order Your First Meal</span>
                            <i class="fas fa-arrow-right"></i>
                            <div class="btn-shine"></div>
                        </a>
                        <a href="#menu-section" class="cta-secondary-btn">
                            <i class="fas fa-list-ul"></i>
                            <span>View Full Menu</span>
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="cta-trust">
                        <div class="trust-badge">
                            <i class="fas fa-check-circle"></i>
                            <span>Fresh Daily</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-check-circle"></i>
                            <span>No MSG</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-check-circle"></i>
                            <span>Halal Options</span>
                        </div>
                    </div>
                </div>

                <!-- Interactive Testimonial Carousel -->
                <div class="testimonial-mini-carousel">
                    <div class="carousel-header">
                        <i class="fas fa-star"></i>
                        <span>What Our Family Says</span>
                    </div>
                    <div class="testimonial-slider" id="testimonialSlider">
                        <div class="testimonial-slide active">
                            <div class="testimonial-rating">⭐⭐⭐⭐⭐</div>
                            <p>"Best fried chicken I've had since leaving home. Uncle Chan's is a lifesaver!"</p>
                            <cite>- SSgt. Martinez, USAF</cite>
                        </div>
                        <div class="testimonial-slide">
                            <div class="testimonial-rating">⭐⭐⭐⭐⭐</div>
                            <p>"The military discount shows they truly care. Food's amazing too!"</p>
                            <cite>- PO2 Johnson, USN</cite>
                        </div>
                        <div class="testimonial-slide">
                            <div class="testimonial-rating">⭐⭐⭐⭐⭐</div>
                            <p>"My kids ask for Uncle Chan's every week. It's our family tradition now."</p>
                            <cite>- Mrs. Chen, Navy Family</cite>
                        </div>
                    </div>
                    <div class="carousel-dots">
                        <span class="dot active" data-slide="0"></span>
                        <span class="dot" data-slide="1"></span>
                        <span class="dot" data-slide="2"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Proof Counter -->
        <div class="social-proof-counter" data-aos="fade-up">
            <div class="counter-grid">
                <div class="counter-item">
                    <div class="counter-number" data-target="50000">0</div>
                    <div class="counter-label">Meals Served</div>
                </div>
                <div class="counter-item">
                    <div class="counter-number" data-target="4.8">0</div>
                    <div class="counter-label">Average Rating</div>
                </div>
                <div class="counter-item">
                    <div class="counter-number" data-target="15">0</div>
                    <div class="counter-label">Years of Service</div>
                </div>
                <div class="counter-item">
                    <div class="counter-number" data-target="98">0</div>
                    <div class="counter-label">% Would Recommend</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Special Offers Section -->
<?php get_template_part('template-parts/special-offers'); ?>

<!-- Menu Section -->
<?php get_template_part('template-parts/menu-section'); ?>

<!-- Instagram Gallery Section -->
<?php get_template_part('template-parts/instagram-gallery'); ?>

<!-- Ordering System (Modals and Cart) -->
<?php get_template_part('template-parts/ordering-system'); ?>

<?php get_footer(); ?>
