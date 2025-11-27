// Email Popup System - Controlled from Admin Settings
// Settings are passed via wp_localize_script in functions.php
jQuery(document).ready(function($) {
    if (typeof ucfc_popup_settings === 'undefined') return;
    
    const popupEnabled = ucfc_popup_settings.enabled;
    const popupDelay = ucfc_popup_settings.delay * 1000;
    const exitIntentEnabled = ucfc_popup_settings.exit_intent;
    
    if (!popupEnabled) return;
    
    // Check if user has already seen popup (cookie)
    if (getCookie('ucfc_popup_shown')) return;
    
    // Show popup after delay
    setTimeout(showPopup, popupDelay);
    
    // Exit intent detection
    if (exitIntentEnabled) {
        $(document).on('mouseleave', function(e) {
            if (e.clientY < 50 && !$('#email-popup').hasClass('active')) {
                showPopup();
            }
        });
    }
    
    function showPopup() {
        if ($('#email-popup').length === 0) {
            createPopup();
        }
        
        $('#email-popup').addClass('active');
        $('body').css('overflow', 'hidden');
        
        // Set cookie for 24 hours
        setCookie('ucfc_popup_shown', 'true', 1);
    }
    
    function createPopup() {
        const popupHTML = `
            <div id="email-popup" class="email-popup">
                <div class="popup-overlay"></div>
                <div class="popup-container">
                    <button class="popup-close">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="popup-content">
                        <div class="popup-image">
                            <div class="discount-badge pulse">
                                <span class="badge-text">${ucfc_popup_settings.discount_text}</span>
                            </div>
                            <img src="${ucfc_popup_settings.image}" alt="Special Offer" />
                        </div>
                        
                        <div class="popup-form-section">
                            <div class="popup-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                            
                            <h3 class="popup-title">${ucfc_popup_settings.title}</h3>
                            
                            <p class="popup-description">
                                ${ucfc_popup_settings.description}
                            </p>
                            
                            <form id="popup-email-form" class="popup-form">
                                <div class="form-group">
                                    <input type="text" id="popup-name" name="name" placeholder="Your Name" required />
                                    <i class="fas fa-user"></i>
                                </div>
                                
                                <div class="form-group">
                                    <input type="email" id="popup-email" name="email" placeholder="Your Email" required />
                                    <i class="fas fa-envelope"></i>
                                </div>
                                
                                <button type="submit" class="popup-submit-btn">
                                    <span>${ucfc_popup_settings.button_text}</span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                                
                                <p class="popup-privacy">
                                    🔒 We respect your privacy. Unsubscribe anytime.
                                </p>
                            </form>
                            
                            <div id="popup-success-message" class="popup-success" style="display: none;">
                                <i class="fas fa-check-circle"></i>
                                <h4>You're In!</h4>
                                <p>Check your email for your discount code.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(popupHTML);
        initializePopupEvents();
    }
    
    function initializePopupEvents() {
        // Close popup
        $('.popup-close, .popup-overlay').on('click', function() {
            closePopup();
        });
        
        // Form submission
        $('#popup-email-form').on('submit', function(e) {
            e.preventDefault();
            
            const name = $('#popup-name').val();
            const email = $('#popup-email').val();
            const $form = $(this);
            const $btn = $form.find('.popup-submit-btn');
            
            // Disable button
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
            
            // AJAX submission
            $.ajax({
                url: uncle_chans_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'subscribe_popup_email',
                    nonce: uncle_chans_ajax.nonce,
                    name: name,
                    email: email
                },
                success: function(response) {
                    if (response.success) {
                        $form.fadeOut(300, function() {
                            $('#popup-success-message').fadeIn(300);
                        });
                        
                        setTimeout(closePopup, 3000);
                    } else {
                        alert('Something went wrong. Please try again.');
                        $btn.prop('disabled', false).html(`<span>${ucfc_popup_settings.button_text}</span><i class="fas fa-arrow-right"></i>`);
                    }
                },
                error: function() {
                    alert('Connection error. Please try again.');
                    $btn.prop('disabled', false).html(`<span>${ucfc_popup_settings.button_text}</span><i class="fas fa-arrow-right"></i>`);
                }
            });
        });
    }
    
    function closePopup() {
        $('#email-popup').removeClass('active');
        $('body').css('overflow', '');
        
        setTimeout(() => {
            $('#email-popup').remove();
        }, 300);
    }
    
    // Cookie helpers
    function setCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/';
    }
    
    function getCookie(name) {
        return document.cookie.split('; ').reduce((r, v) => {
            const parts = v.split('=');
            return parts[0] === name ? decodeURIComponent(parts[1]) : r;
        }, '');
    }
});
