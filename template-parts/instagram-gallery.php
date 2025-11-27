<?php
/**
 * Template Part: Instagram Gallery
 * 
 * Displays Instagram feed from admin settings
 * 
 * @package Uncle_Chans_Chicken
 */

$instagram_token = get_option('ucfc_instagram_token');
$feed_limit = get_option('ucfc_instagram_feed_limit', 6);
$hashtag = get_option('ucfc_instagram_hashtag', '#UncleChansFriedChicken');
?>

<section class="instagram-gallery" id="instagram-gallery">
    <div class="instagram-header">
        <div class="section-badge">
            <i class="fab fa-instagram"></i>
            <span>Follow Us</span>
        </div>
        <h2 class="section-title">
            <span class="gradient-text">Our Food Gallery</span>
        </h2>
        <p class="section-subtitle">
            Share your moments with <?php echo esc_html($hashtag); ?> for a chance to be featured!
        </p>
    </div>
    
    <div class="instagram-feed" id="instagram-feed">
        <?php if ($instagram_token): ?>
            <!-- Instagram posts will be loaded via JavaScript -->
            <div class="instagram-loading">
                <div class="loading-spinner"></div>
                <p>Loading delicious photos...</p>
            </div>
        <?php else: ?>
            <!-- Demo placeholders when no token is set -->
            <?php for ($i = 1; $i <= $feed_limit; $i++): ?>
                <div class="instagram-item demo-item" data-aos="zoom-in" data-aos-delay="<?php echo $i * 100; ?>">
                    <div class="instagram-placeholder">
                        <i class="fab fa-instagram"></i>
                        <p>Connect Instagram in Settings</p>
                    </div>
                    <div class="instagram-overlay">
                        <div class="instagram-stats">
                            <span><i class="fas fa-heart"></i> 1.2k</span>
                            <span><i class="fas fa-comment"></i> 45</span>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
    
    <div class="instagram-cta">
        <a href="https://instagram.com" target="_blank" class="instagram-follow-btn">
            <i class="fab fa-instagram"></i>
            <span>Follow Us on Instagram</span>
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

<style>
/* Instagram Gallery Styles */
.instagram-gallery {
    padding: 80px 20px;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    position: relative;
    overflow: hidden;
}

.instagram-gallery::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, rgba(201, 42, 42, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(240, 180, 41, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.instagram-header {
    text-align: center;
    margin-bottom: 60px;
    position: relative;
    z-index: 1;
}

.section-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: linear-gradient(135deg, #833AB4, #FD1D1D, #F77737);
    color: white;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 20px;
}

.instagram-feed {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    max-width: 1400px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.instagram-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 15px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.instagram-item:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 50px rgba(201, 42, 42, 0.4);
}

.instagram-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.instagram-item:hover img {
    transform: scale(1.1);
}

.instagram-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #2d2d2d, #1a1a1a);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
    font-size: 48px;
}

.instagram-placeholder p {
    margin-top: 15px;
    font-size: 14px;
    color: #888;
}

.instagram-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(201, 42, 42, 0.9), rgba(240, 180, 41, 0.9));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.instagram-item:hover .instagram-overlay {
    opacity: 1;
}

.instagram-stats {
    display: flex;
    gap: 30px;
    color: white;
    font-size: 18px;
    font-weight: 600;
}

.instagram-stats span {
    display: flex;
    align-items: center;
    gap: 8px;
}

.instagram-loading {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #888;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #333;
    border-top-color: #C92A2A;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.instagram-cta {
    text-align: center;
    margin-top: 50px;
    position: relative;
    z-index: 1;
}

.instagram-follow-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 18px 40px;
    background: linear-gradient(135deg, #833AB4, #FD1D1D);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(131, 58, 180, 0.3);
}

.instagram-follow-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(131, 58, 180, 0.5);
}

.instagram-follow-btn i.fa-arrow-right {
    transition: transform 0.3s ease;
}

.instagram-follow-btn:hover i.fa-arrow-right {
    transform: translateX(5px);
}

@media (max-width: 768px) {
    .instagram-gallery {
        padding: 60px 15px;
    }
    
    .instagram-feed {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .instagram-stats {
        font-size: 14px;
        gap: 20px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    const instagramToken = '<?php echo esc_js($instagram_token); ?>';
    const feedLimit = <?php echo intval($feed_limit); ?>;
    
    if (instagramToken) {
        loadInstagramFeed();
    }
    
    function loadInstagramFeed() {
        // Instagram Basic Display API call
        $.ajax({
            url: `https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url,timestamp&access_token=${instagramToken}&limit=${feedLimit}`,
            method: 'GET',
            success: function(response) {
                displayInstagramPosts(response.data);
            },
            error: function() {
                $('#instagram-feed').html('<p class="error-message">Unable to load Instagram feed. Please check your access token.</p>');
            }
        });
    }
    
    function displayInstagramPosts(posts) {
        const feed = $('#instagram-feed');
        feed.empty();
        
        posts.forEach((post, index) => {
            const mediaUrl = post.media_type === 'VIDEO' ? post.thumbnail_url : post.media_url;
            const delay = index * 100;
            
            const item = $(`
                <a href="${post.permalink}" target="_blank" class="instagram-item" data-aos="zoom-in" data-aos-delay="${delay}">
                    <img src="${mediaUrl}" alt="${post.caption || 'Instagram post'}" loading="lazy" />
                    <div class="instagram-overlay">
                        <div class="instagram-stats">
                            <span><i class="fas fa-heart"></i></span>
                            ${post.media_type === 'VIDEO' ? '<span><i class="fas fa-play"></i></span>' : ''}
                        </div>
                    </div>
                </a>
            `);
            
            feed.append(item);
        });
    }
});
</script>
