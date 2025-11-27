<?php
/**
 * Template Part: Special Offers Showcase
 * 
 * Displays active special offers from admin
 * 
 * @package Uncle_Chans_Chicken
 */

$args = array(
    'post_type' => 'special_offer',
    'posts_per_page' => 6,
    'orderby' => 'date',
    'order' => 'DESC'
);

$offers = new WP_Query($args);
?>

<?php if ($offers->have_posts()): ?>
<section class="special-offers" id="special-offers">
    <div class="offers-header">
        <div class="section-badge">
            <i class="fas fa-tag"></i>
            <span>Limited Time</span>
        </div>
        <h2 class="section-title">
            <span class="gradient-text">Special Offers</span>
        </h2>
        <p class="section-subtitle">
            Exclusive deals that'll make your taste buds and wallet happy!
        </p>
    </div>
    
    <div class="offers-grid">
        <?php 
        $delay = 0;
        while ($offers->have_posts()): 
            $offers->the_post();
            
            // Get custom fields
            $discount_amount = get_post_meta(get_the_ID(), '_offer_discount_amount', true);
            $discount_type = get_post_meta(get_the_ID(), '_offer_discount_type', true); // percentage or fixed
            $expiry_date = get_post_meta(get_the_ID(), '_offer_expiry_date', true);
            $offer_code = get_post_meta(get_the_ID(), '_offer_code', true);
            $is_featured = get_post_meta(get_the_ID(), '_offer_is_featured', true);
            
            $delay += 100;
        ?>
            <div class="offer-card <?php echo $is_featured ? 'featured' : ''; ?>" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <?php if ($is_featured): ?>
                    <div class="featured-badge">
                        <i class="fas fa-star"></i>
                        <span>Best Deal</span>
                    </div>
                <?php endif; ?>
                
                <?php if (has_post_thumbnail()): ?>
                    <div class="offer-image">
                        <?php the_post_thumbnail('medium_large'); ?>
                        <div class="offer-overlay"></div>
                    </div>
                <?php endif; ?>
                
                <div class="offer-content">
                    <?php if ($discount_amount): ?>
                        <div class="offer-discount">
                            <?php if ($discount_type === 'percentage'): ?>
                                <span class="discount-value"><?php echo esc_html($discount_amount); ?>%</span>
                                <span class="discount-label">OFF</span>
                            <?php else: ?>
                                <span class="discount-value">$<?php echo esc_html($discount_amount); ?></span>
                                <span class="discount-label">OFF</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="offer-title"><?php the_title(); ?></h3>
                    <div class="offer-description">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <?php if ($offer_code): ?>
                        <div class="offer-code">
                            <span class="code-label">Use Code:</span>
                            <span class="code-value"><?php echo esc_html($offer_code); ?></span>
                            <button class="copy-code-btn" data-code="<?php echo esc_attr($offer_code); ?>">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($expiry_date): ?>
                        <div class="offer-expiry">
                            <i class="fas fa-clock"></i>
                            <span>Valid until <?php echo date('M d, Y', strtotime($expiry_date)); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <a href="#menu" class="offer-cta-btn">
                        <span>Order Now</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    </div>
</section>

<style>
/* Special Offers Styles */
.special-offers {
    padding: 80px 20px;
    background: linear-gradient(to bottom, #0f0f0f, #1a1a1a);
    position: relative;
}

.special-offers::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 300px;
    background: radial-gradient(ellipse at top, rgba(240, 180, 41, 0.15), transparent);
    pointer-events: none;
}

.offers-header {
    text-align: center;
    margin-bottom: 60px;
    position: relative;
    z-index: 1;
}

.offers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    max-width: 1400px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.offer-card {
    background: linear-gradient(135deg, #2d2d2d, #1a1a1a);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid transparent;
}

.offer-card:hover {
    transform: translateY(-10px);
    border-color: #C92A2A;
    box-shadow: 0 20px 50px rgba(201, 42, 42, 0.3);
}

.offer-card.featured {
    border-color: #F0B429;
    background: linear-gradient(135deg, #3d2a1a, #2d1a0a);
}

.featured-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #F0B429, #D4A027);
    color: #1a1a1a;
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 5px 20px rgba(240, 180, 41, 0.4);
}

.offer-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.offer-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.offer-card:hover .offer-image img {
    transform: scale(1.1);
}

.offer-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent, rgba(26, 26, 26, 0.9));
}

.offer-content {
    padding: 30px;
    position: relative;
}

.offer-discount {
    position: absolute;
    top: -40px;
    left: 30px;
    background: linear-gradient(135deg, #C92A2A, #A02020);
    padding: 15px 25px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(201, 42, 42, 0.5);
    z-index: 5;
}

.discount-value {
    display: block;
    font-size: 36px;
    font-weight: 700;
    font-family: 'Bebas Neue', sans-serif;
    color: white;
    line-height: 1;
}

.discount-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #F0B429;
    letter-spacing: 2px;
}

.offer-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 28px;
    color: white;
    margin: 25px 0 15px;
    letter-spacing: 1px;
}

.offer-description {
    color: #aaa;
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 20px;
}

.offer-code {
    background: rgba(240, 180, 41, 0.1);
    border: 2px dashed #F0B429;
    border-radius: 10px;
    padding: 15px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.code-label {
    color: #888;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.code-value {
    flex: 1;
    font-family: 'Courier New', monospace;
    font-size: 18px;
    font-weight: 700;
    color: #F0B429;
    letter-spacing: 2px;
}

.copy-code-btn {
    background: #F0B429;
    color: #1a1a1a;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.copy-code-btn:hover {
    background: #C92A2A;
    color: white;
    transform: scale(1.1);
}

.offer-expiry {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #888;
    font-size: 13px;
    margin-bottom: 20px;
}

.offer-expiry i {
    color: #C92A2A;
}

.offer-cta-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #C92A2A, #A02020);
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.offer-cta-btn:hover {
    background: linear-gradient(135deg, #A02020, #C92A2A);
    transform: translateX(5px);
    box-shadow: 0 5px 20px rgba(201, 42, 42, 0.4);
}

.offer-cta-btn i {
    transition: transform 0.3s ease;
}

.offer-cta-btn:hover i {
    transform: translateX(5px);
}

@media (max-width: 768px) {
    .special-offers {
        padding: 60px 15px;
    }
    
    .offers-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Copy promo code functionality
    $('.copy-code-btn').on('click', function() {
        const code = $(this).data('code');
        const $btn = $(this);
        
        // Copy to clipboard
        navigator.clipboard.writeText(code).then(() => {
            // Visual feedback
            $btn.html('<i class="fas fa-check"></i>');
            $btn.css('background', '#4CAF50');
            
            setTimeout(() => {
                $btn.html('<i class="fas fa-copy"></i>');
                $btn.css('background', '');
            }, 2000);
        });
    });
});
</script>
<?php endif; ?>
