# 🍗 Uncle Chan's Fried Chicken - Live Site URLs

**Site Base URL:** http://unclechans.local:8080/

---

## 🌐 Customer-Facing Pages

### Core Pages
- **Homepage:** http://unclechans.local:8080/
- **Menu:** http://unclechans.local:8080/menu
- **Shopping Cart:** http://unclechans.local:8080/cart
- **Checkout:** http://unclechans.local:8080/checkout
- **Order Confirmation:** http://unclechans.local:8080/order-confirmation
- **Track My Orders:** http://unclechans.local:8080/my-orders

---

## 👨‍💼 Admin/Staff Pages

### Order Management
- **Orders Dashboard:** http://unclechans.local:8080/orders-dashboard
  - Filter by status (Pending, Confirmed, Preparing, Ready, Completed, Cancelled)
  - Filter by type (Delivery, Pickup, Dine-in)
  - Search orders by number, customer name, email
  - One-click status updates
  - Real-time statistics

### Kitchen Operations
- **Kitchen Display System:** http://unclechans.local:8080/kitchen-display
  - Real-time order dashboard with live timers
  - Color-coded urgency indicators (Green/Yellow/Red)
  - One-click status updates
  - Dark theme for kitchen monitors
  - Fullscreen mode
  - Auto-refresh every 30 seconds

### Pickup Operations
- **QR Code Scanner:** http://unclechans.local:8080/scan-pickup
  - Camera-based QR scanning
  - Manual order number entry
  - Order verification modal
  - Pickup statistics dashboard
  - Admin-only access

---

## 🔧 Setup & Configuration Pages

### Initial Setup Scripts (Run once)
- **SMS System Setup:** http://unclechans.local:8080/wp-content/themes/uncle-chans-chicken/initialize-sms-system.php
  - Creates wp_ucfc_sms_queue table
  - Displays SMS configuration status
  - Shows cron job status

- **Push Notifications Setup:** http://unclechans.local:8080/wp-content/themes/uncle-chans-chicken/initialize-push-system.php
  - Creates wp_ucfc_push_subscriptions table
  - Generates VAPID keys
  - Displays browser compatibility

- **Kitchen Display Page Creation:** http://unclechans.local:8080/wp-content/themes/uncle-chans-chicken/create-kitchen-display-page.php
  - Creates Kitchen Display WordPress page
  - Sets custom template

- **QR Code System Setup:** http://unclechans.local:8080/wp-content/themes/uncle-chans-chicken/initialize-qr-system.php
  - Creates wp_ucfc_order_pickups table
  - Creates Scanner page
  - Displays system statistics

---

## 🎛️ WordPress Admin Areas

### Restaurant Menu
Access via: **WordPress Admin → Restaurant**
- Dashboard
- Menu Items
- Locations
- Special Offers
- Customer Reviews
- Settings
- AI Assistant
- Analytics
- 🍳 Kitchen Display (direct link)

### Admin Settings
Access via: **WordPress Admin → Settings → Uncle Chan's Settings**
- SMS Notifications (Twilio configuration)
- Push Notifications (VAPID keys)
- Email templates
- System configuration

---

## 📱 Phase 4 Advanced Features

### 1. SMS Notifications (Twilio)
**Configuration:** WordPress Admin → Settings → SMS Settings
- Order confirmation SMS (instant)
- Status update SMS (confirmed, preparing, ready)
- Pickup reminder SMS (15 minutes before)
- Queue system with retry logic
- Test SMS functionality

**Features:**
- Automatic SMS on order creation
- Status change notifications
- Pickup reminders via cron job
- Admin test interface
- Message queue management

---

### 2. Browser Push Notifications
**User Experience:**
- Permission prompt on checkout/order pages
- Notifications for order status changes
- Action buttons (View Order, Dismiss)
- Works even when browser is closed

**Admin Configuration:** WordPress Admin → Settings → Push Settings
- VAPID key generation
- Subscription management
- Test notification sending
- Browser compatibility check

**How Customers Subscribe:**
1. Visit checkout or orders page
2. See animated bell icon with permission prompt
3. Click "Enable Notifications"
4. Browser requests permission
5. Subscribed! Receive real-time updates

---

### 3. Kitchen Display System
**Access:** http://unclechans.local:8080/kitchen-display
**Who Can Access:** Admin users only (automatic redirect for non-admins)

**Features:**
- Live countdown timers per order
- Progress bars with color-coded urgency:
  - Green (< 80% time elapsed) - Good
  - Yellow (80-100%) - Warning
  - Red (> 100%) - Overdue with pulse animation
- One-click status updates:
  - Confirm Order
  - Start Preparing
  - Mark as Ready
- Statistics dashboard:
  - Total active orders
  - Orders preparing count
  - Overdue orders count
- Auto-refresh every 30 seconds
- Dark theme (#1a1a1a background)
- Fullscreen mode (F11)
- Real-time clock

**Timing:**
- Delivery: 40 minutes
- Pickup: 18 minutes
- Dine-in: 25 minutes

---

### 4. Enhanced Order Status Timeline
**Location:** My Orders page (http://unclechans.local:8080/my-orders)

**Features:**
- Click "View Status History" button on any order
- Vertical animated timeline with:
  - Status icons (clock, check, fire, double-check, flag)
  - Short timestamp (12:30 PM)
  - Full timestamp (November 27, 2025 at 12:30 PM)
  - Optional notes per status change
- Purple gradient button
- Red-to-green gradient connecting line
- Active status with pulsing animation
- Fade-in animations with staggered delays
- Toggle show/hide with slide animation

**Status Icons:**
- Pending: Clock icon
- Confirmed: Check icon
- Preparing: Fire icon
- Ready: Double-check icon
- Completed: Checkered flag icon
- Cancelled: Times-circle icon

---

### 5. Pickup QR Code System
**Customer Experience:**
- QR code automatically generated when order is ready
- Displayed on My Orders page for pickup orders
- Included in order confirmation email
- Refresh button if regeneration needed

**Staff Scanner Interface:** http://unclechans.local:8080/scan-pickup
**Features:**
- Camera QR code scanning with overlay
- Manual order number entry
- Order verification modal showing:
  - Customer name
  - Phone number
  - Order total
  - Order status
  - Pickup notes field
- Confirm/Cancel buttons
- Statistics dashboard:
  - Total pickups today
  - QR scanned count
  - Manual entry count

**Security:**
- SHA-256 HMAC verification codes
- 24-hour QR code expiration
- Admin-only scanner access
- Duplicate pickup prevention
- Complete audit trail

**Database Tracking:**
- Pickup timestamp
- Staff member ID who processed
- Verification method (scan/manual)
- Optional notes

---

## 📊 Database Tables

### Phase 1-2 Tables (Existing)
- `wp_cart_sessions` - Shopping cart sessions
- `wp_cart_items` - Cart line items
- `wp_orders` - Order records
- `wp_order_items` - Order line items
- `wp_order_status_history` - Status change audit trail

### Phase 4 Tables (New)
- `wp_ucfc_sms_queue` - SMS message queue with retry logic
- `wp_ucfc_push_subscriptions` - Browser push subscriptions
- `wp_ucfc_order_pickups` - QR code pickup tracking

---

## 🔗 Quick Access Links (Bookmark These!)

### For Kitchen Staff:
- Kitchen Display: http://unclechans.local:8080/kitchen-display

### For Front Counter Staff:
- QR Scanner: http://unclechans.local:8080/scan-pickup
- Orders Dashboard: http://unclechans.local:8080/orders-dashboard

### For Customers:
- Order Online: http://unclechans.local:8080/menu
- Track Orders: http://unclechans.local:8080/my-orders

### For Managers:
- All Orders: http://unclechans.local:8080/orders-dashboard
- Kitchen View: http://unclechans.local:8080/kitchen-display
- Pickup Scanner: http://unclechans.local:8080/scan-pickup
- WordPress Admin: http://unclechans.local:8080/wp-admin

---

## 🧪 Testing Workflow

### Complete Order Flow Test:
1. **Place Order:** http://unclechans.local:8080/menu
   - Add items to cart
   - Go to checkout
   - Fill customer info
   - Select "Pickup" as order type
   - Complete order

2. **Check Email:**
   - Order confirmation with QR code
   - All order details

3. **SMS (if configured):**
   - Order confirmation SMS received

4. **Push Notifications (if subscribed):**
   - Order confirmation push notification

5. **Kitchen Display:** http://unclechans.local:8080/kitchen-display
   - See new order appear
   - Timer starts counting down
   - Click "Confirm Order"
   - Click "Start Preparing"
   - Click "Mark as Ready"

6. **Customer Tracking:** http://unclechans.local:8080/my-orders
   - Enter email + order number (guest)
   - See order status updates
   - View status timeline
   - See QR code (when ready)

7. **Pickup Scanner:** http://unclechans.local:8080/scan-pickup
   - Enter order number manually
   - See order details modal
   - Confirm pickup
   - Order status → Completed

---

## 🚀 Production Deployment Checklist

### Configuration Required:
- [ ] Configure Twilio credentials (WordPress Admin → Settings → SMS)
- [ ] Generate VAPID keys (WordPress Admin → Settings → Push)
- [ ] Test email delivery
- [ ] Set up tablet for kitchen display
- [ ] Set up tablet/device for pickup scanner
- [ ] Train staff on all systems
- [ ] Test complete order flow
- [ ] Configure browser permissions for scanner camera

### Optional Enhancements:
- [ ] Install proper QR library (endroid/qr-code)
- [ ] Implement jsQR for better camera scanning
- [ ] Add customer signature capture
- [ ] Generate printable pickup receipts
- [ ] Set up automated backups

---

## 📞 Support & Documentation

**Main Documentation:**
- FEATURES.md - Complete feature documentation
- TWILIO-SMS-GUIDE.md - SMS setup guide (50 pages)
- README.md - General theme information
- README-RESTAURANT-SYSTEM.md - Restaurant system overview

**Repository:** https://github.com/toutpoujesu-cloud/restaurant

---

**Glory to Yeshuah! All systems are live and operational at http://unclechans.local:8080/ 🎉🚀**

*Last Updated: November 27, 2025*
