/**
 * Push Notification Handler
 * Client-side JavaScript for managing push notifications
 */

(function($) {
    'use strict';
    
    const PushHandler = {
        
        swRegistration: null,
        isSubscribed: false,
        
        /**
         * Initialize push notifications
         */
        init: function() {
            // Check if push notifications are supported
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                console.log('[Push] Push notifications not supported');
                return;
            }
            
            console.log('[Push] Initializing...');
            
            // Register service worker
            this.registerServiceWorker();
            
            // Set up event listeners
            this.setupEventListeners();
        },
        
        /**
         * Register service worker
         */
        registerServiceWorker: function() {
            const self = this;
            
            navigator.serviceWorker.register(ucfcPushConfig.serviceWorkerUrl)
                .then(function(registration) {
                    console.log('[Push] Service Worker registered:', registration);
                    self.swRegistration = registration;
                    
                    // Check current subscription
                    return registration.pushManager.getSubscription();
                })
                .then(function(subscription) {
                    self.isSubscribed = (subscription !== null);
                    console.log('[Push] Current subscription:', subscription);
                    
                    if (subscription) {
                        console.log('[Push] Already subscribed');
                    } else {
                        // Show permission prompt on checkout page
                        if (ucfcPushConfig.isCheckoutPage) {
                            self.showPermissionPrompt();
                        }
                    }
                })
                .catch(function(error) {
                    console.error('[Push] Service Worker registration failed:', error);
                });
        },
        
        /**
         * Setup event listeners
         */
        setupEventListeners: function() {
            const self = this;
            
            // Allow notifications button
            $(document).on('click', '#ucfc-allow-notifications', function(e) {
                e.preventDefault();
                self.subscribeUser();
            });
            
            // Dismiss prompt button
            $(document).on('click', '#ucfc-dismiss-prompt', function(e) {
                e.preventDefault();
                self.hidePermissionPrompt();
                // Remember dismissal for 7 days
                localStorage.setItem('ucfc_push_dismissed', Date.now());
            });
        },
        
        /**
         * Show permission prompt
         */
        showPermissionPrompt: function() {
            // Don't show if dismissed recently (within 7 days)
            const dismissed = localStorage.getItem('ucfc_push_dismissed');
            if (dismissed) {
                const daysSince = (Date.now() - parseInt(dismissed)) / (1000 * 60 * 60 * 24);
                if (daysSince < 7) {
                    console.log('[Push] Prompt dismissed recently, not showing');
                    return;
                }
            }
            
            // Don't show if permission already denied
            if (Notification.permission === 'denied') {
                console.log('[Push] Notification permission denied');
                return;
            }
            
            // Don't show if already subscribed
            if (this.isSubscribed) {
                console.log('[Push] Already subscribed');
                return;
            }
            
            // Show prompt with delay
            setTimeout(function() {
                $('#ucfc-push-prompt').fadeIn(300);
            }, 2000);
        },
        
        /**
         * Hide permission prompt
         */
        hidePermissionPrompt: function() {
            $('#ucfc-push-prompt').fadeOut(300);
        },
        
        /**
         * Subscribe user to push notifications
         */
        subscribeUser: function() {
            const self = this;
            
            if (!this.swRegistration) {
                console.error('[Push] Service Worker not registered');
                return;
            }
            
            console.log('[Push] Subscribing user...');
            
            // Convert VAPID public key
            const applicationServerKey = this.urlB64ToUint8Array(ucfcPushConfig.vapidPublicKey);
            
            this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            })
            .then(function(subscription) {
                console.log('[Push] User subscribed:', subscription);
                
                self.isSubscribed = true;
                self.hidePermissionPrompt();
                
                // Send subscription to server
                return self.sendSubscriptionToServer(subscription);
            })
            .then(function(response) {
                console.log('[Push] Subscription saved:', response);
                
                // Show success message
                self.showNotification('Notifications Enabled!', 'You\'ll receive updates about your orders.');
            })
            .catch(function(error) {
                console.error('[Push] Failed to subscribe:', error);
                
                if (Notification.permission === 'denied') {
                    alert('Push notifications are blocked. Please enable them in your browser settings.');
                } else {
                    alert('Failed to enable notifications. Please try again.');
                }
            });
        },
        
        /**
         * Unsubscribe user from push notifications
         */
        unsubscribeUser: function() {
            const self = this;
            
            this.swRegistration.pushManager.getSubscription()
                .then(function(subscription) {
                    if (subscription) {
                        return subscription.unsubscribe();
                    }
                })
                .then(function() {
                    console.log('[Push] User unsubscribed');
                    self.isSubscribed = false;
                    
                    // Notify server
                    return self.removeSubscriptionFromServer();
                })
                .then(function() {
                    console.log('[Push] Subscription removed from server');
                })
                .catch(function(error) {
                    console.error('[Push] Error unsubscribing:', error);
                });
        },
        
        /**
         * Send subscription to server
         */
        sendSubscriptionToServer: function(subscription) {
            const keys = subscription.getKey('p256dh');
            const auth = subscription.getKey('auth');
            
            return $.ajax({
                url: ucfcPushConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ucfc_subscribe_push',
                    nonce: ucfcPushConfig.nonce,
                    endpoint: subscription.endpoint,
                    public_key: btoa(String.fromCharCode.apply(null, new Uint8Array(keys))),
                    auth_token: btoa(String.fromCharCode.apply(null, new Uint8Array(auth))),
                    guest_email: ucfcPushConfig.userEmail || this.getGuestEmail()
                }
            });
        },
        
        /**
         * Remove subscription from server
         */
        removeSubscriptionFromServer: function() {
            return $.ajax({
                url: ucfcPushConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ucfc_unsubscribe_push',
                    nonce: ucfcPushConfig.nonce,
                    endpoint: this.swRegistration.pushManager.endpoint
                }
            });
        },
        
        /**
         * Get guest email from cookie
         */
        getGuestEmail: function() {
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const parts = cookie.trim().split('=');
                if (parts[0] === 'ucfc_guest_email') {
                    return decodeURIComponent(parts[1]);
                }
            }
            return '';
        },
        
        /**
         * Show browser notification
         */
        showNotification: function(title, body) {
            if (!this.swRegistration) return;
            
            const options = {
                body: body,
                icon: ucfcPushConfig.icon || '/wp-content/themes/uncle-chans-chicken/assets/images/chicken-icon.png',
                badge: ucfcPushConfig.badge || '/wp-content/themes/uncle-chans-chicken/assets/images/badge-icon.png',
                vibrate: [200, 100, 200],
                tag: 'ucfc-notification'
            };
            
            this.swRegistration.showNotification(title, options);
        },
        
        /**
         * Convert base64 string to Uint8Array
         */
        urlB64ToUint8Array: function(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            
            return outputArray;
        }
    };
    
    // Initialize when document ready
    $(document).ready(function() {
        if (typeof ucfcPushConfig !== 'undefined' && ucfcPushConfig.enabled) {
            PushHandler.init();
        }
    });
    
    // Expose to window for debugging
    window.UCFCPushHandler = PushHandler;
    
})(jQuery);
