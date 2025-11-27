<!-- 🎨 AWARD-WINNING FOOTER - Museum of Modern Art Worthy -->
<footer class="site-footer">
    <!-- Decorative Top Wave -->
    <div class="footer-wave">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path>
            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
        </svg>
    </div>

    <div class="footer-container">
        <!-- Main Footer Content -->
        <div class="footer-main">
            <!-- Brand Column -->
            <div class="footer-column footer-brand">
                <div class="footer-logo">
                    <i class="fas fa-drumstick-bite"></i>
                    <h3><?php echo get_option('uncle_chans_name', 'Uncle Chan\'s Fried Chicken'); ?></h3>
                </div>
                <p class="footer-tagline">Legendary Taste, Military Pride</p>
                <p class="footer-description">Serving the finest fried chicken with love and respect to our military families since day one.</p>
                
                <!-- Military Badge -->
                <div class="military-badge">
                    <div class="badge-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="badge-content">
                        <span class="badge-title">Veteran Owned</span>
                        <span class="badge-subtitle"><?php echo get_option('uncle_chans_military_discount', '15'); ?>% Military Discount</span>
                    </div>
                </div>
            </div>

            <!-- Quick Links Column -->
            <div class="footer-column">
                <h4 class="footer-column-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="#menu"><i class="fas fa-chevron-right"></i>Our Menu</a></li>
                    <li><a href="#about"><i class="fas fa-chevron-right"></i>Our Story</a></li>
                    <li><a href="#locations"><i class="fas fa-chevron-right"></i>Locations</a></li>
                    <li><a href="#catering"><i class="fas fa-chevron-right"></i>Catering</a></li>
                    <li><a href="#careers"><i class="fas fa-chevron-right"></i>Careers</a></li>
                </ul>
            </div>

            <!-- Business Hours Column -->
            <div class="footer-column">
                <h4 class="footer-column-title">Business Hours</h4>
                <div class="hours-list">
                    <?php
                    $hours = get_option('uncle_chans_hours', array());
                    $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
                    $today = date('l');
                    
                    foreach ($days as $day) {
                        $isToday = ($day === $today);
                        $class = $isToday ? 'hour-item today' : 'hour-item';
                        
                        if (isset($hours[$day])) {
                            if (isset($hours[$day]['closed']) && $hours[$day]['closed']) {
                                echo '<div class="' . $class . '"><span class="day">' . $day . '</span><span class="time closed">Closed</span></div>';
                            } else {
                                $open = isset($hours[$day]['open']) ? $hours[$day]['open'] : '';
                                $close = isset($hours[$day]['close']) ? $hours[$day]['close'] : '';
                                if ($open && $close) {
                                    echo '<div class="' . $class . '"><span class="day">' . $day . '</span><span class="time">' . $open . ' - ' . $close . '</span></div>';
                                }
                            }
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Contact Column -->
            <div class="footer-column">
                <h4 class="footer-column-title">Get In Touch</h4>
                <ul class="contact-list">
                    <li class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo get_option('uncle_chans_address', '123 Main Street'); ?></span>
                    </li>
                    <li class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <a href="tel:<?php echo esc_attr(get_option('uncle_chans_phone', '')); ?>"><?php echo get_option('uncle_chans_phone', '(555) 123-4567'); ?></a>
                    </li>
                    <li class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?php echo esc_attr(get_option('uncle_chans_email', '')); ?>"><?php echo get_option('uncle_chans_email', 'info@unclechans.com'); ?></a>
                    </li>
                </ul>

                <!-- Social Media -->
                <div class="footer-social">
                    <p class="social-title">Follow Our Journey</p>
                    <div class="social-icons">
                        <?php if (get_option('uncle_chans_facebook')): ?>
                            <a href="<?php echo esc_url(get_option('uncle_chans_facebook')); ?>" target="_blank" class="social-icon facebook" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (get_option('uncle_chans_instagram')): ?>
                            <a href="<?php echo esc_url(get_option('uncle_chans_instagram')); ?>" target="_blank" class="social-icon instagram" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (get_option('uncle_chans_twitter')): ?>
                            <a href="<?php echo esc_url(get_option('uncle_chans_twitter')); ?>" target="_blank" class="social-icon twitter" aria-label="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (get_option('uncle_chans_youtube')): ?>
                            <a href="<?php echo esc_url(get_option('uncle_chans_youtube')); ?>" target="_blank" class="social-icon youtube" aria-label="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="#" target="_blank" class="social-icon tiktok" aria-label="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter Section -->
        <div class="footer-newsletter">
            <div class="newsletter-content">
                <div class="newsletter-info">
                    <i class="fas fa-envelope-open-text"></i>
                    <div>
                        <h4>Join Our Flavor Club</h4>
                        <p>Get exclusive deals, new menu items & military appreciation events</p>
                    </div>
                </div>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email address" required>
                    <button type="submit">
                        <span>Subscribe</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-legal">
                <p>&copy; <?php echo date('Y'); ?> <?php echo get_option('uncle_chans_name', 'Uncle Chan\'s Fried Chicken'); ?>. All Rights Reserved.</p>
                <div class="legal-links">
                    <a href="#privacy">Privacy Policy</a>
                    <span>•</span>
                    <a href="#terms">Terms of Service</a>
                    <span>•</span>
                    <a href="#accessibility">Accessibility</a>
                </div>
            </div>
            <div class="footer-badges">
                <span class="badge">🇺🇸 Proudly Serving Military Families</span>
                <span class="badge">🍗 Made Fresh Daily</span>
            </div>
        </div>
    </div>

    <!-- Decorative Pattern Background -->
    <div class="footer-pattern"></div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
