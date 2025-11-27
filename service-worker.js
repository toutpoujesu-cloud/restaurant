/**
 * Service Worker for Push Notifications
 * Uncle Chan's Fried Chicken
 * 
 * Handles:
 * - Push notification events
 * - Notification click events
 * - Background sync for offline support
 */

const CACHE_NAME = 'ucfc-v1';
const urlsToCache = [];

// Install event - cache resources
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching app shell');
                return cache.addAll(urlsToCache);
            })
            .then(() => {
                console.log('[Service Worker] Installed successfully');
                return self.skipWaiting(); // Activate immediately
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            console.log('[Service Worker] Activated successfully');
            return self.clients.claim(); // Take control immediately
        })
    );
});

// Push notification received
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push notification received');
    
    let notificationData = {
        title: 'Uncle Chan\'s Fried Chicken',
        body: 'You have a new notification',
        icon: '/wp-content/themes/uncle-chans-chicken/assets/images/chicken-icon.png',
        badge: '/wp-content/themes/uncle-chans-chicken/assets/images/badge-icon.png',
        vibrate: [200, 100, 200],
        tag: 'order-notification',
        requireInteraction: false,
        data: {
            url: '/',
            timestamp: Date.now()
        }
    };
    
    // Parse push data if available
    if (event.data) {
        try {
            const data = event.data.json();
            
            // Update notification data from payload
            if (data.title) notificationData.title = data.title;
            if (data.body) notificationData.body = data.body;
            if (data.icon) notificationData.icon = data.icon;
            if (data.url) notificationData.data.url = data.url;
            if (data.order_id) notificationData.data.order_id = data.order_id;
            if (data.tag) notificationData.tag = data.tag;
            
            // Add action buttons based on notification type
            if (data.type === 'order_ready') {
                notificationData.actions = [
                    {
                        action: 'view',
                        title: '👁️ View Order',
                        icon: '/wp-content/themes/uncle-chans-chicken/assets/images/view-icon.png'
                    },
                    {
                        action: 'dismiss',
                        title: '✖️ Dismiss'
                    }
                ];
                notificationData.requireInteraction = true; // Stay visible
            }
            
        } catch (e) {
            console.error('[Service Worker] Error parsing push data:', e);
            notificationData.body = event.data.text();
        }
    }
    
    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
            .then(() => {
                console.log('[Service Worker] Notification displayed');
            })
            .catch((error) => {
                console.error('[Service Worker] Error displaying notification:', error);
            })
    );
});

// Notification clicked
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');
    
    event.notification.close();
    
    // Handle action buttons
    if (event.action === 'dismiss') {
        console.log('[Service Worker] Notification dismissed');
        return;
    }
    
    // Get URL from notification data
    let urlToOpen = event.notification.data.url || '/';
    
    // Special handling for order notifications
    if (event.notification.data.order_id) {
        urlToOpen = '/my-orders';
    }
    
    // Open URL in existing tab or new tab
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Check if there's already a window open
                for (let client of clientList) {
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }
                
                // Open new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
            .catch((error) => {
                console.error('[Service Worker] Error opening URL:', error);
            })
    );
});

// Background sync for offline order tracking
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync:', event.tag);
    
    if (event.tag === 'sync-order-status') {
        event.waitUntil(
            fetch('/wp-admin/admin-ajax.php?action=ucfc_sync_order_status')
                .then((response) => response.json())
                .then((data) => {
                    console.log('[Service Worker] Order status synced:', data);
                })
                .catch((error) => {
                    console.error('[Service Worker] Sync failed:', error);
                })
        );
    }
});

// Message from client
self.addEventListener('message', (event) => {
    console.log('[Service Worker] Message received:', event.data);
    
    if (event.data.action === 'skipWaiting') {
        self.skipWaiting();
    }
});

console.log('[Service Worker] Loaded successfully');
