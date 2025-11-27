# Uncle Chan's Fried Chicken - Features Documentation

**Last Updated:** November 27, 2025  
**Version:** 4.0  
**WordPress Version:** 6.8.3

---

## 📋 Table of Contents

1. [Phase 1: Core E-commerce Foundation](#phase-1-core-e-commerce-foundation)
2. [Phase 2: Advanced Order Management](#phase-2-advanced-order-management)
3. [Phase 3: Enhanced Customer Experience](#phase-3-enhanced-customer-experience)
4. [Phase 4: Advanced Features](#phase-4-advanced-features)
5. [Database Schema](#database-schema)
6. [API Integrations](#api-integrations)

---

## Phase 1: Core E-commerce Foundation

### 🛒 Shopping Cart System
**File:** `inc/cart-functions.php`  
**Status:** ✅ Complete

- Session-based cart management with 7-day expiration
- Guest and logged-in user support
- Add/remove/update cart items with AJAX
- Real-time cart total calculations
- Cart persistence across sessions
- Quantity controls with validation
- Special requests/notes per item

**Database Tables:**
- `wp_cart_sessions` - Cart session management
- `wp_cart_items` - Individual cart items

---

### 🍗 Menu Display System
**File:** `page-menu.php`  
**Status:** ✅ Complete

- Grid-based menu layout with 3 columns
- Category filtering (Chicken, Sides, Drinks, Desserts)
- Product cards with images, prices, descriptions
- "Add to Cart" functionality with instant feedback
- Special requests modal for customization
- Responsive design for mobile/tablet
- Real-time cart count updates

**Features:**
- Product images with fallback
- Price display with formatting
- Category badges
- Quantity selectors
- Special instructions per item

---

### 💳 Checkout System
**File:** `page-checkout.php`, `inc/checkout-process.php`  
**Status:** ✅ Complete

**Customer Information:**
- Name, email, phone validation
- Guest checkout support
- Logged-in user auto-fill

**Order Types:**
- Delivery (with address, city, postal code)
- Pickup (estimated ready time)
- Dine-in (table number)

**Payment Methods:**
- Credit Card
- Debit Card
- Cash
- Mobile Payment

**Order Processing:**
- Server-side validation
- Order creation with unique order numbers
- Email confirmations (customer + admin)
- Order status initialization
- Inventory management hooks
- Action hooks for extensions

**Database Tables:**
- `wp_orders` - Order records
- `wp_order_items` - Order line items

---

### 📧 Email Notifications
**File:** `inc/checkout-process.php`  
**Status:** ✅ Complete

**Customer Emails:**
- Order confirmation with details
- Order number and estimated time
- Itemized list with prices
- Delivery/pickup information
- Payment method confirmation

**Admin Emails:**
- New order notifications
- Customer contact details
- Order type and timing
- Payment status

---

## Phase 2: Advanced Order Management

### 📊 Orders Dashboard (Admin)
**File:** `page-orders-dashboard.php`  
**Status:** ✅ Complete

**Features:**
- Real-time order management interface
- Filter by status (All, Pending, Confirmed, Preparing, Ready, Completed, Cancelled)
- Filter by order type (Delivery, Pickup, Dine-in)
- Search by order number, customer name, email
- Date range filtering
- One-click status updates
- Order details modal with full information
- Statistics dashboard (revenue, orders count)
- Export functionality
- Auto-refresh every 30 seconds

**Order Card Display:**
- Order number and status badge
- Customer information
- Order items with quantities
- Total amount and payment status
- Order type and timing
- Quick action buttons

**Status Colors:**
- Pending: Orange
- Confirmed: Blue
- Preparing: Purple
- Ready: Green
- Completed: Teal
- Cancelled: Red

---

### 📦 Order Tracking (Customer)
**File:** `page-my-orders.php`, `inc/order-tracking-ajax.php`  
**Status:** ✅ Complete

**Features:**
- Guest order lookup (email + order number)
- Logged-in user order history
- Real-time status updates
- Progress bar visualization (4 steps)
- Estimated time remaining
- ETA countdown
- Order details display
- Reorder functionality
- Auto-refresh every 30 seconds

**Order Information:**
- Order status with color coding
- Payment status badge
- Order items with images
- Total amount
- Order type (delivery/pickup/dine-in)
- Created date and time
- Special instructions

---

### 📈 Order Status History
**File:** Database tracking  
**Status:** ✅ Complete

**Features:**
- Automatic status change logging
- Timestamp for each status change
- Optional notes per status change
- Changed by user tracking
- Chronological history

**Database Table:**
- `wp_order_status_history` - Complete audit trail

---

### 🔔 Admin Notifications
**File:** `inc/admin-settings.php`  
**Status:** ✅ Complete

**Features:**
- Sound notifications for new orders
- Desktop notifications (browser)
- Customizable notification sounds
- Enable/disable per admin user
- Visual notification badges

---

## Phase 3: Enhanced Customer Experience

### 🎨 Custom Theme Design
**Theme:** Uncle Chan's Fried Chicken  
**Status:** ✅ Complete

**Design Elements:**
- Brand colors: Red (#d92027), Black (#000000), White (#FFFFFF)
- Custom logo integration
- Responsive navigation menu
- Mobile-first design approach
- Consistent typography (Poppins font)
- Smooth animations and transitions

**Header:**
- Logo and site title
- Main navigation menu
- Shopping cart icon with item count
- Mobile hamburger menu

**Footer:**
- Contact information
- Business hours
- Social media links
- Copyright notice

---

### 📱 Responsive Design
**Files:** All template files  
**Status:** ✅ Complete

**Breakpoints:**
- Desktop: 1200px+
- Tablet: 768px - 1199px
- Mobile: < 768px

**Features:**
- Fluid grid layouts
- Touch-friendly buttons (44px minimum)
- Optimized images
- Mobile menu navigation
- Stacked forms on mobile
- Readable font sizes
- Proper spacing and padding

---

### ⚡ AJAX Enhancements
**File:** `assets/js/cart-handler.js`  
**Status:** ✅ Complete

**Features:**
- Add to cart without page reload
- Update cart quantities instantly
- Remove items with confirmation
- Real-time cart total updates
- Loading states and spinners
- Error handling with user feedback
- Success notifications

---

### 🖼️ Product Images
**Storage:** `wp-content/uploads/products/`  
**Status:** ✅ Complete

**Features:**
- Image upload system
- Thumbnail generation
- Fallback placeholder images
- Lazy loading
- Responsive images
- Alt text for accessibility

---

## Phase 4: Advanced Features

### 📱 SMS Notifications with Twilio
**File:** `inc/sms-notifications.php`  
**Status:** ✅ Complete  
**Lines of Code:** 500+

**Features:**
- Twilio REST API integration
- Queue system with retry logic (max 3 attempts)
- Order confirmation SMS (instant)
- Status update SMS (confirmed, preparing, ready)
- Pickup reminder SMS (15 minutes before)
- Cron job processing every 5 minutes
- Admin settings page with test SMS
- Phone number validation and formatting
- Error logging and tracking
- Delivery status tracking

**Message Templates:**
- Order confirmation with order number and ETA
- Status updates with personalized messages
- Pickup reminders with address
- Custom message support

**Database Table:**
- `wp_ucfc_sms_queue` - SMS queue with status tracking

**Admin Settings:**
- Account SID configuration
- Auth Token setup
- From phone number
- Enable/disable SMS
- Test SMS functionality
- Queue statistics

**Documentation:**
- `TWILIO-SMS-GUIDE.md` (50+ pages)
- Setup instructions
- Cost analysis
- Troubleshooting guide
- Production checklist

---

### 🔔 Browser Push Notifications
**Files:** `service-worker.js`, `inc/push-notifications.php`, `assets/js/push-handler.js`  
**Status:** ✅ Complete  
**Lines of Code:** 1200+

**Features:**
- Web Push API integration
- Service worker registration
- VAPID key authentication
- Push subscription management
- Permission request UI
- Status update notifications
- Order ready alerts
- Action buttons (View Order, Dismiss)
- Background sync support
- Offline capability

**Notification Types:**
- Order confirmed
- Order preparing
- Order ready for pickup
- Order completed
- Custom admin messages

**User Experience:**
- Beautiful permission prompt
- Animated bell icon
- One-click subscribe/unsubscribe
- 7-day dismissal memory
- Non-intrusive design

**Database Table:**
- `wp_ucfc_push_subscriptions` - Subscription management

**Admin Features:**
- VAPID key generation
- Subscription statistics
- Test notification sending
- Enable/disable push
- Browser compatibility check

---

### 🍳 Kitchen Display System (KDS)
**File:** `page-kitchen-display.php`  
**Status:** ✅ Complete  
**Lines of Code:** 800+  
**URL:** `/kitchen-display`

**Features:**
- Real-time order dashboard
- Live countdown timers
- Color-coded urgency indicators
- One-click status updates
- Fullscreen mode for kitchen monitors
- Auto-refresh every 30 seconds
- Dark theme for reduced eye strain
- Audio alerts for new orders

**Display Layout:**
- Grid of order cards (3 columns)
- Large order numbers
- Customer names
- Order items with quantities
- Countdown timer with progress bar
- Status action buttons

**Timing System:**
- Delivery: 40 minutes
- Pickup: 18 minutes
- Dine-in: 25 minutes

**Urgency Colors:**
- Good (< 80%): Green
- Warning (80-100%): Yellow
- Overdue (> 100%): Red with pulse animation

**Status Actions:**
- Confirm Order
- Start Preparing
- Mark as Ready
- Complete Order

**Statistics Dashboard:**
- Total active orders
- Orders preparing count
- Overdue orders count
- Real-time clock

**Access Control:**
- Admin-only access
- Automatic redirect for non-admins
- Session verification

---

### 📅 Enhanced Order Status Timeline
**Files:** `page-my-orders.php`, `inc/order-tracking-ajax.php`  
**Status:** ✅ Complete  
**Lines of Code:** 400+

**Features:**
- Visual timeline on My Orders page
- Vertical timeline with gradient connecting line
- Animated status transitions
- Click to expand/collapse
- AJAX loading with spinner
- Access verification (user/guest)

**Timeline Design:**
- Purple gradient button
- Red-to-green gradient line
- Circular status markers (40px)
- Status icons (Font Awesome)
- Active status with pulse animation
- Fade-in animation with staggered delays

**Status Icons:**
- Pending: Clock icon (fa-clock)
- Confirmed: Check icon (fa-check)
- Preparing: Fire icon (fa-fire)
- Ready: Double check icon (fa-check-double)
- Completed: Checkered flag icon (fa-flag-checkered)
- Cancelled: Times circle icon (fa-times-circle)

**Timeline Data:**
- Status name with friendly labels
- Short timestamp (12:30 PM)
- Full timestamp (November 27, 2025 at 12:30 PM)
- Optional notes per status change
- Changed by user information

**User Experience:**
- Smooth slide animations
- Toggle visibility
- Loading indicator
- Error handling
- Responsive design
- Touch-friendly on mobile

**Backend:**
- AJAX handler: `ucfc_ajax_get_order_history()`
- Fetches from `wp_order_status_history`
- Access control verification
- Formatted JSON response

---

### 📱 Pickup QR Code System
**Files:** `inc/qr-code-system.php`, `page-scan-pickup.php`  
**Status:** 🔄 In Progress  
**Target Lines:** 600+

**Planned Features:**
- QR code generation for each order
- Display on order confirmation page
- Include in email confirmations
- Admin scanner interface
- Pickup verification system
- Timestamp pickup completion
- Staff member tracking
- Statistics and reporting

**QR Code Library:**
- PHP QR Code generator
- SVG format for scalability
- Error correction level M
- 300x300px default size

**Scanner Features:**
- Camera-based scanning
- Manual order number entry
- Order lookup verification
- One-click pickup confirmation
- Invalid code handling
- Already picked up detection

**Database Tracking:**
- Pickup timestamp
- Staff member ID
- Verification method (scan/manual)
- Customer signature (optional)

---

## Database Schema

### wp_cart_sessions
```sql
id (INT, PK, AUTO_INCREMENT)
session_id (VARCHAR 255, UNIQUE)
user_id (BIGINT, NULL)
created_at (DATETIME)
expires_at (DATETIME)
```

### wp_cart_items
```sql
id (INT, PK, AUTO_INCREMENT)
session_id (INT, FK)
product_id (BIGINT)
product_name (VARCHAR 255)
product_price (DECIMAL 10,2)
quantity (INT)
special_request (TEXT, NULL)
added_at (DATETIME)
```

### wp_orders
```sql
id (INT, PK, AUTO_INCREMENT)
order_number (VARCHAR 50, UNIQUE)
user_id (BIGINT, NULL)
customer_name (VARCHAR 255)
customer_email (VARCHAR 255)
customer_phone (VARCHAR 50)
order_type (ENUM: delivery, pickup, dine-in)
delivery_address (TEXT, NULL)
delivery_city (VARCHAR 100, NULL)
delivery_postal (VARCHAR 20, NULL)
table_number (VARCHAR 20, NULL)
payment_method (VARCHAR 50)
payment_status (ENUM: pending, paid)
order_status (ENUM: pending, confirmed, preparing, ready, completed, cancelled)
subtotal (DECIMAL 10,2)
delivery_fee (DECIMAL 10,2)
tax (DECIMAL 10,2)
total (DECIMAL 10,2)
special_instructions (TEXT, NULL)
estimated_time (INT) -- minutes
created_at (DATETIME)
updated_at (DATETIME)
```

### wp_order_items
```sql
id (INT, PK, AUTO_INCREMENT)
order_id (INT, FK)
product_id (BIGINT)
product_name (VARCHAR 255)
product_price (DECIMAL 10,2)
quantity (INT)
special_request (TEXT, NULL)
subtotal (DECIMAL 10,2)
```

### wp_order_status_history
```sql
id (INT, PK, AUTO_INCREMENT)
order_id (INT, FK)
old_status (VARCHAR 50)
new_status (VARCHAR 50)
changed_by (BIGINT) -- user_id
changed_at (DATETIME)
notes (TEXT, NULL)
```

### wp_ucfc_sms_queue
```sql
id (INT, PK, AUTO_INCREMENT)
order_id (INT, FK)
phone_number (VARCHAR 20)
message (TEXT)
message_type (VARCHAR 50)
status (ENUM: pending, sent, failed)
attempts (INT)
twilio_sid (VARCHAR 100, NULL)
error_message (TEXT, NULL)
created_at (DATETIME)
sent_at (DATETIME, NULL)
```

### wp_ucfc_push_subscriptions
```sql
id (INT, PK, AUTO_INCREMENT)
user_id (BIGINT, NULL)
guest_email (VARCHAR 255, NULL)
endpoint (TEXT)
public_key (VARCHAR 255)
auth_token (VARCHAR 255)
user_agent (TEXT)
ip_address (VARCHAR 45)
subscribed_at (DATETIME)
last_notification_at (DATETIME, NULL)
notification_count (INT)
is_active (TINYINT)
```

---

## API Integrations

### Twilio SMS API
**Version:** 2023-10-16  
**Endpoint:** https://api.twilio.com/2010-04-01/Accounts/{AccountSid}/Messages.json  
**Authentication:** HTTP Basic Auth (Account SID + Auth Token)

**Capabilities:**
- Send SMS messages
- Check message status
- Error handling
- Delivery receipts

**Rate Limits:**
- Standard: 60 requests/second
- Queue system handles bursts

---

### Web Push API
**Standard:** W3C Push API  
**Authentication:** VAPID (Voluntary Application Server Identification)

**Browser Support:**
- Chrome 50+
- Firefox 44+
- Edge 17+
- Safari 16+
- Opera 37+

**Features:**
- Push subscriptions
- Background notifications
- Action buttons
- Badge updates
- Silent push

---

## Technology Stack

**Backend:**
- PHP 8.0+
- WordPress 6.8.3
- MySQL 8.0

**Frontend:**
- HTML5
- CSS3 (with animations)
- JavaScript (ES6+)
- jQuery 3.x
- Font Awesome 6.x

**APIs:**
- Twilio REST API
- Web Push API
- Service Workers API

**Development:**
- Docker (WordPress container)
- Git version control
- GitHub repository

---

## File Structure

```
uncle-chans-chicken/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── cart-handler.js
│       └── push-handler.js
├── inc/
│   ├── admin-settings.php
│   ├── cart-functions.php
│   ├── checkout-process.php
│   ├── order-tracking-ajax.php
│   ├── orders-dashboard.php
│   ├── push-notifications.php
│   ├── qr-code-system.php (in progress)
│   └── sms-notifications.php
├── page-checkout.php
├── page-kitchen-display.php
├── page-menu.php
├── page-my-orders.php
├── page-orders-dashboard.php
├── page-scan-pickup.php (in progress)
├── service-worker.js
├── functions.php
├── style.css
├── FEATURES.md
└── TWILIO-SMS-GUIDE.md
```

---

## Setup Scripts

**initialize-sms-system.php**
- Creates SMS queue table
- Checks Twilio configuration
- Verifies cron jobs
- Displays setup status

**initialize-push-system.php**
- Creates push subscriptions table
- Generates VAPID keys
- Checks service worker
- Displays configuration status

**create-kitchen-display-page.php**
- Creates Kitchen Display WordPress page
- Sets custom template
- Configures permalink

---

## Performance Optimizations

1. **Database Indexing**
   - Indexed order_number for fast lookups
   - Indexed session_id for cart queries
   - Indexed order_id for related tables

2. **AJAX Loading**
   - Lazy loading for order history
   - Progressive enhancement
   - Debounced search inputs

3. **Caching**
   - Browser caching for static assets
   - Service worker caching
   - Session-based cart caching

4. **Code Optimization**
   - Minified CSS/JS (production)
   - Lazy loading images
   - Efficient SQL queries

---

## Security Features

1. **AJAX Security**
   - WordPress nonce verification
   - User capability checks
   - Input sanitization
   - Output escaping

2. **Data Validation**
   - Server-side validation
   - Email format checking
   - Phone number validation
   - SQL injection prevention

3. **Access Control**
   - Admin-only pages
   - User/guest order verification
   - Session management
   - CSRF protection

4. **API Security**
   - HTTPS required
   - API key encryption
   - Rate limiting
   - Error message sanitization

---

## Browser Compatibility

**Fully Supported:**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

**Partially Supported:**
- IE 11 (basic functionality, no push notifications)
- Safari < 16 (no push notifications)

---

## Mobile Compatibility

**iOS:**
- Safari 14+
- Chrome 90+
- Responsive design optimized

**Android:**
- Chrome 90+
- Firefox 88+
- Samsung Internet 14+

---

## Future Enhancements

**Planned Features:**
- [ ] Customer loyalty program
- [ ] Online payment processing (Stripe/PayPal)
- [ ] Delivery driver tracking
- [ ] Ingredient inventory management
- [ ] Multi-location support
- [ ] Scheduled orders
- [ ] Promotional codes/coupons
- [ ] Customer reviews and ratings
- [ ] Analytics dashboard
- [ ] Mobile app (iOS/Android)

---

## Change Log

### Version 4.0 (November 27, 2025)
- ✅ Added SMS Notifications with Twilio
- ✅ Added Browser Push Notifications
- ✅ Added Kitchen Display System
- ✅ Added Enhanced Order Status Timeline
- 🔄 Adding Pickup QR Code System

### Version 3.0 (Previous)
- ✅ Custom theme design
- ✅ Responsive design implementation
- ✅ AJAX enhancements
- ✅ Product image system

### Version 2.0 (Previous)
- ✅ Orders Dashboard
- ✅ Order Tracking
- ✅ Order Status History
- ✅ Admin Notifications

### Version 1.0 (Previous)
- ✅ Shopping Cart System
- ✅ Menu Display System
- ✅ Checkout System
- ✅ Email Notifications

---

## Support & Documentation

**Main Documentation:**
- FEATURES.md (this file)
- TWILIO-SMS-GUIDE.md
- WordPress Codex

**Developer Contact:**
- GitHub: toutpoujesu-cloud/restaurant
- Repository: https://github.com/toutpoujesu-cloud/restaurant

---

**Glory to Yeshuah! All features built with excellence and purpose! 🚀**

*This documentation is automatically updated with each new feature deployment.*
