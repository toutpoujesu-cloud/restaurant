# Uncle Chan's Fried Chicken - WordPress Theme

A complete restaurant management system with AI-powered customer service, advanced menu management dashboard, and ordering capabilities.

## 🎯 Overview

This WordPress theme is designed for Uncle Chan's Fried Chicken, serving military personnel at NAS Sigonella, Italy. It features:

- **Advanced Menu Management Dashboard** with drag-drop interface, analytics, and bulk operations
- **AI-Powered Customer Service** using OpenAI GPT-4 for intelligent order assistance
- **Restaurant Management System** for locations, special offers, and customer reviews
- **Responsive Landing Page** with interactive menu, ordering system, and military discount support

## 📦 Features

### Menu Management Dashboard
- **Visual Drag-Drop Builder** - Reorder menu items with intuitive interface
- **Performance Analytics** - Track sales, revenue, ratings, and top sellers
- **Quick Edit Modal** - Inline editing without page refresh
- **Bulk Operations** - Multi-select for batch updates
- **CSV Import/Export** - Manage hundreds of items efficiently
- **Category Management** - Organize items by Fried Chicken, Wings, Catfish, etc.
- **Real-time Stats** - Monitor stock levels, profit margins, and sold counts

### AI Customer Service System
- **OpenAI GPT-4 Integration** - Intelligent conversation handling
- **Menu Knowledge Base** - AI understands all menu items, prices, and availability
- **Order Assistance** - Helps customers customize orders and answer questions
- **Conversation Logging** - Track all customer interactions
- **Rate Limiting** - Prevent API abuse with smart throttling
- **Function Validation** - Ensures AI responses are accurate
- **Analytics Dashboard** - Monitor AI performance and usage

### Restaurant Management
- **Custom Post Types** - Menu Items, Locations, Special Offers, Reviews
- **Meta Boxes** - Price, calories, spice level, sides, nutritional info
- **Featured Items** - Highlight bestsellers and specials
- **Category System** - Flexible taxonomy for menu organization
- **Stock Management** - Track inventory levels
- **Profit Tracking** - Monitor cost vs. price margins

### Frontend Features
- **Responsive Design** - Mobile-first approach
- **Interactive Menu Tabs** - Filter by Fried Chicken, Wednesday Wings, Friday Catfish
- **Email Capture Popup** - Build mailing list with 15% discount offer
- **Instagram Gallery** - Showcase food photography
- **Military Discount Badge** - Automatic 15% off for service members
- **Pickup Scheduling** - NAS Sigonella pickup coordination
- **Flash Sales** - Wednesday Wing Special promotion

## 🚀 Installation

### Requirements
- WordPress 6.8.3+
- PHP 8.0+
- MySQL 8.0+
- Docker (for containerized deployment)

### Quick Start

1. **Clone the repository:**
```bash
git clone https://github.com/toutpoujesu-cloud/restaurant.git
cd restaurant
```

2. **Copy to WordPress themes directory:**
```bash
cp -r restaurant /path/to/wordpress/wp-content/themes/uncle-chans-chicken
```

3. **Activate theme in WordPress:**
   - Go to Appearance → Themes
   - Activate "Uncle Chan's Fried Chicken"

4. **Import menu items:**
   - Go to Menu Dashboard → Batch Import
   - Click "Import All Menu Items Now"
   - This creates 8 menu items from the landing page template

5. **Configure AI System (optional):**
   - Go to Uncle Chan's → AI Settings
   - Add OpenAI API key
   - Configure rate limiting and caching

## 📁 File Structure

```
uncle-chans-chicken/
├── assets/
│   ├── css/
│   │   ├── custom.css          # Main styles
│   │   ├── menu-dashboard.css  # Dashboard styling
│   │   └── popup.css           # Email popup styles
│   └── js/
│       ├── main.js             # Frontend interactions
│       ├── menu-dashboard.js   # Dashboard functionality
│       ├── ai-chat.js          # AI chatbot
│       └── email-popup.js      # Email capture
├── inc/
│   ├── menu-dashboard.php      # Advanced menu management
│   ├── custom-post-types.php   # Menu items, locations
│   ├── meta-boxes.php          # Custom fields
│   ├── admin-dashboard.php     # Main admin interface
│   ├── ai-assistant-settings.php # AI configuration
│   ├── ai-chat-engine.php      # AI processing
│   ├── ai-conversation-logger.php # Logging system
│   └── ai-analytics-dashboard.php # AI metrics
├── template-parts/
│   ├── menu-section.php        # Menu display
│   ├── ordering-system.php     # Order interface
│   ├── special-offers.php      # Promotions
│   └── instagram-gallery.php   # Social media
├── functions.php               # Theme setup
├── header.php                  # Site header
├── footer.php                  # Site footer
├── index.php                   # Main template
└── style.css                   # Theme metadata
```

## 🎨 Menu Dashboard Usage

### Adding Categories
1. Navigate to **Menu Dashboard** in WordPress admin
2. Click **"+ Add Category"** button
3. Enter category name (e.g., "Appetizers", "Desserts")
4. Items can be filtered by category

### Adding Menu Items
1. Click **"Add New Item"** button
2. Enter item name and price
3. Click through to edit page for full details:
   - Description
   - Category
   - Cost (for profit calculation)
   - Stock level
   - Calories, spice level
   - Featured image

### Drag-Drop Reordering
- Simply drag menu cards to new positions
- Order saves automatically via AJAX
- Reorder persists across page refreshes

### Quick Edit
- Hover over menu card
- Click **pencil icon**
- Edit inline without leaving dashboard
- Changes save instantly

### Analytics
- Navigate to **Analytics** submenu
- View charts for:
  - Sales trends (last 7 days)
  - Category performance
  - Peak ordering hours
  - Best/worst performing items

### CSV Operations
1. **Export:**
   - Go to Import/Export submenu
   - Click "Export CSV"
   - Download includes all metadata

2. **Import:**
   - Prepare CSV with columns: Name, Description, Price, Cost, Category, Image URL
   - Drag file to upload zone
   - Preview and confirm import

## 🤖 AI System Configuration

### OpenAI Setup
1. Get API key from https://platform.openai.com
2. Go to **Uncle Chan's → AI Settings**
3. Enter API key
4. Configure:
   - Model: gpt-4-turbo-preview (recommended)
   - Max tokens: 500
   - Temperature: 0.7

### Rate Limiting
- Default: 10 requests per minute per user
- Configurable in AI Settings
- Prevents API cost overruns

### Conversation Logging
- All AI chats logged to database
- View in **Uncle Chan's → AI Logs**
- Includes timestamp, user info, conversation history
- Export for analysis

## 🐳 Docker Deployment

### Current Setup
The theme runs in Docker containers:

```yaml
services:
  uncle-chans-wordpress:
    image: wordpress:latest
    ports:
      - "8080:80"
    volumes:
      - ./uncle-chans-chicken:/var/www/html/wp-content/themes/uncle-chans-chicken
    environment:
      WORDPRESS_DB_HOST: uncle-chans-mysql
      WORDPRESS_DB_NAME: uncle_chans_wp
      WORDPRESS_DB_USER: root
      WORDPRESS_DB_PASSWORD: rootpassword

  uncle-chans-mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: uncle_chans_wp
```

**Access URLs:**
- Frontend: http://unclechans.local:8080
- Admin: http://unclechans.local:8080/wp-admin
- phpMyAdmin: http://localhost:8081

### Theme Sync
The theme folder is bind-mounted for real-time sync:
- Local: `d:/XAAMP/htdocs/Chandler/wordpress-6.8.3/wordpress/wp-content/themes/uncle-chans-chicken`
- Container: `/var/www/html/wp-content/themes/uncle-chans-chicken`

Changes to local files instantly reflect in Docker.

## 📊 Database Schema

### Menu Items
- **Post Type:** `menu_item`
- **Taxonomy:** `menu_category`
- **Meta Fields:**
  - `_ucfc_price` - Item price (float)
  - `_ucfc_cost` - Cost to make (float)
  - `_ucfc_stock` - Inventory level (int)
  - `_ucfc_sold_count` - Total sold (int)
  - `_ucfc_rating` - Customer rating (float)
  - `_ucfc_profit_margin` - Calculated % (int)
  - `_menu_item_is_featured` - Featured flag (bool)

### AI Conversations
- **Table:** `wp_ai_conversations`
- **Columns:**
  - `conversation_id` - Unique identifier
  - `user_ip` - Customer IP
  - `messages` - JSON array of chat
  - `created_at` - Timestamp
  - `function_calls` - AI actions taken

## 🔧 Customization

### Changing Colors
Edit `assets/css/custom.css`:
```css
:root {
    --color-primary: #C92A2A;     /* Red */
    --color-secondary: #F0B429;    /* Gold */
    --color-accent: #2E8B57;       /* Green */
}
```

### Adding Custom Menu Fields
1. Edit `inc/meta-boxes.php`
2. Add field to `ucfc_menu_item_fields()`
3. Save in `ucfc_save_menu_item_meta()`
4. Display in `inc/menu-dashboard.php`

### Modifying AI Prompts
Edit `inc/ai-chat-engine.php`:
```php
$system_prompt = "You are Uncle Chan's AI assistant...";
```

## 📝 Menu Import Script

Included scripts for batch operations:

**import-direct.php** - Import hardcoded landing page items
```bash
docker exec uncle-chans-wordpress php /var/www/html/wp-content/themes/uncle-chans-chicken/import-direct.php
```

**update-images.php** - Attach Unsplash images to menu items
```bash
docker exec uncle-chans-wordpress php /var/www/html/wp-content/themes/uncle-chans-chicken/update-images.php
```

## 🎯 Features Roadmap

### Priority 1 (Implemented ✅)
- [x] Visual drag-drop menu builder
- [x] Performance analytics dashboard
- [x] Quick edit modal
- [x] CSV import/export

### Priority 2 (Planned)
- [ ] Smart pricing suggestions (AI-powered)
- [ ] Automated low-stock alerts (email notifications)
- [ ] A/B testing for menu items
- [ ] Customer preference learning

### Priority 3 (Future)
- [ ] Multi-location inventory sync
- [ ] Mobile app integration
- [ ] Voice ordering with AI
- [ ] Predictive demand forecasting

## 🛠️ Troubleshooting

### Dashboard Not Showing Items
```bash
# Verify menu items exist
docker exec -i uncle-chans-mysql mysql -u root -prootpassword uncle_chans_wp -e "SELECT COUNT(*) FROM wp_posts WHERE post_type='menu_item';"

# Re-import if needed
docker exec uncle-chans-wordpress php /var/www/html/wp-content/themes/uncle-chans-chicken/import-direct.php
```

### Images Not Displaying
```bash
# Check featured image attachments
docker exec -i uncle-chans-mysql mysql -u root -prootpassword uncle_chans_wp -e "SELECT p.post_title, pm.meta_value FROM wp_posts p LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id WHERE pm.meta_key='_thumbnail_id';"

# Re-attach images
docker exec uncle-chans-wordpress php /var/www/html/wp-content/themes/uncle-chans-chicken/update-images.php
```

### AI Not Responding
1. Check API key in Uncle Chan's → AI Settings
2. Verify internet connection
3. Check OpenAI API status
4. Review rate limiting settings

## 📄 License

This theme is proprietary software developed for Uncle Chan's Fried Chicken. All rights reserved.

## 👥 Credits

- **Development:** Built with WordPress, OpenAI GPT-4, Chart.js, jQuery UI
- **Images:** Unsplash (chicken, wings, catfish photography)
- **Icons:** Font Awesome 6.4.0
- **Location:** Serving NAS Sigonella, Sicily, Italy

## 📞 Support

For technical support or feature requests, contact the development team or open an issue on GitHub.

---

**Built with ❤️ for those who serve. Thank you for your service!**
