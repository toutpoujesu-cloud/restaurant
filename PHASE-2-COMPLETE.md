# 🚀 Phase 2 Complete - Advanced AI Features

## 🎉 What's New in Phase 2

### 1. 📊 Conversation Logging System
**File**: `inc/ai-conversation-logger.php` (350+ lines)

**Features**:
- ✅ **Automatic Database Logging**: Every conversation saved to `wp_ucfc_ai_conversations` table
- ✅ **Session Tracking**: Groups conversations by user session
- ✅ **Performance Metrics**: Response time, token usage, API costs
- ✅ **Function Call Tracking**: Logs which AI functions were used
- ✅ **User Satisfaction**: Rate conversations (1-5 stars)
- ✅ **IP & User Agent**: Track user demographics
- ✅ **Data Retention**: Auto-delete old conversations after X days

**Database Schema**:
```sql
CREATE TABLE wp_ucfc_ai_conversations (
    id bigint(20) PRIMARY KEY AUTO_INCREMENT,
    session_id varchar(255) NOT NULL,
    user_id bigint(20) DEFAULT NULL,
    user_message text NOT NULL,
    ai_response text NOT NULL,
    ai_provider varchar(50) NOT NULL,
    ai_model varchar(100) NOT NULL,
    tokens_used int DEFAULT 0,
    response_time float DEFAULT 0,
    function_called varchar(255) DEFAULT NULL,
    function_result text DEFAULT NULL,
    user_satisfaction tinyint DEFAULT NULL,
    user_ip varchar(100) DEFAULT NULL,
    user_agent text DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP
);
```

**Methods**:
- `log_conversation($data)` - Save conversation
- `get_analytics($date_from, $date_to)` - Get comprehensive analytics
- `get_trends($days)` - Get daily conversation counts
- `export_to_csv($date_from, $date_to)` - Export to CSV
- `delete_old_conversations($days)` - Cleanup old data
- `update_satisfaction($id, $rating)` - Rate conversation

---

### 2. 📈 Analytics Dashboard
**File**: `inc/ai-analytics-dashboard.php` (500+ lines)

**Access**: WordPress Admin → **Restaurant → AI Analytics**

**Dashboard Sections**:

#### 📊 Key Metrics Cards
- **Total Conversations**: Count of all AI interactions
- **Unique Sessions**: Number of unique visitors who chatted
- **Avg Response Time**: Average seconds per AI response
- **Total Tokens Used**: Sum of all API token usage
- **Avg Satisfaction**: Average user rating (out of 5 stars)
- **Est. API Costs**: Calculated based on token usage

#### 📈 Charts & Visualizations
- **Conversation Trends**: 30-day line chart (Chart.js)
- **Provider Performance**: Bar chart comparing OpenAI vs Claude vs Gemini
- **Hourly Distribution**: Activity heatmap (which hours are busiest)

#### 🔥 Popular Questions Table
- **Rank**: Most frequently asked questions
- **Question Text**: User messages
- **Count**: Number of times asked
- **Percentage**: % of total conversations

#### ⚙️ Function Usage Stats
- **Function Name**: Which AI functions are being called
- **Call Count**: Number of times each function used
- **Usage Bar**: Visual representation

#### 💬 Recent Conversations
- **Last 20 conversations** displayed
- Shows user message, AI response (truncated)
- Metadata: timestamp, provider, model, response time, tokens, satisfaction

#### 📥 Export Functionality
- **Export to CSV**: Download all conversations
- **Date Range Filter**: Filter by date range

**Charts Powered By**: Chart.js 4.4.0 (loaded from CDN)

---

### 3. ✅ JSON Schema Validator
**File**: `inc/ai-function-validator.php` (200+ lines)

**Purpose**: Validate custom AI function definitions before saving

**Features**:
- ✅ **Syntax Validation**: Checks JSON is valid
- ✅ **Schema Validation**: Ensures function structure is correct
- ✅ **Required Fields**: name, description, parameters
- ✅ **Type Checking**: Validates parameter types (string, number, integer, boolean, array, object)
- ✅ **Error Messages**: Detailed error reporting
- ✅ **Example Library**: Pre-built function examples

**Built-in Examples**:
1. **check_inventory**: Check item availability
2. **calculate_delivery_time**: Estimate delivery to ZIP code
3. **apply_discount**: Apply promo codes
4. **check_allergens**: Search menu for allergens

**AJAX Endpoint**: `ucfc_validate_function_json`

**Usage**: Click "Validate JSON" button in AI Assistant → Functions tab

---

### 4. 🔗 Integration Updates

#### AI Chat Engine (`ai-chat-engine.php`)
- ✅ Added conversation logging to `ucfc_handle_customer_chat()`
- ✅ Tracks response time with microtime()
- ✅ Logs token usage from API responses
- ✅ Logs function calls when used
- ✅ Logs failed conversations too (for debugging)

#### Functions.php
```php
// Phase 2 includes added:
require_once get_template_directory() . '/inc/ai-conversation-logger.php';
require_once get_template_directory() . '/inc/ai-function-validator.php';
require_once get_template_directory() . '/inc/ai-analytics-dashboard.php';
```

#### AI Assistant Settings (`ai-assistant-settings.php`)
- ✅ Added "Validate JSON" button in Functions tab
- ✅ Real-time validation results display
- ✅ Improved code example with multiple functions
- ✅ AJAX validation with detailed error messages

---

## 🎯 How to Use Phase 2 Features

### Setup (One-Time)
1. **Database Tables**: Created automatically on theme activation
2. **Logging**: Starts automatically when AI chat is used
3. **No configuration needed**: Works out of the box!

### Viewing Analytics
1. Go to **WordPress Admin → Restaurant → AI Analytics**
2. See real-time metrics and charts
3. Filter by date range
4. Export data to CSV

### Custom Functions
1. Go to **Restaurant → AI Assistant → Functions Tab**
2. Scroll to "Custom Functions (JSON)"
3. Paste your JSON function definition
4. Click "Validate JSON"
5. Fix any errors shown
6. Click "Save Changes"

### Example Custom Function
```json
[{
  "name": "check_delivery_time",
  "description": "Estimate delivery time to customer address",
  "parameters": {
    "type": "object",
    "properties": {
      "zipcode": {
        "type": "string",
        "description": "Customer's ZIP code"
      },
      "order_size": {
        "type": "string",
        "description": "Order size: small, medium, or large"
      }
    },
    "required": ["zipcode"]
  }
},
{
  "name": "check_allergens",
  "description": "Check menu items for specific allergens",
  "parameters": {
    "type": "object",
    "properties": {
      "allergen": {
        "type": "string",
        "description": "Allergen name (nuts, dairy, gluten, shellfish, etc.)"
      }
    },
    "required": ["allergen"]
  }
}]
```

---

## 📊 Analytics Deep Dive

### Understanding Metrics

**Total Conversations**
- Every user message + AI response = 1 conversation
- Includes both successful and failed interactions

**Unique Sessions**
- PHP session-based tracking
- One session = one visitor (even across page reloads)
- Resets after browser close or 24 hours

**Response Time**
- Measured from when AJAX request starts to when response received
- Includes: Network latency + AI processing time
- Lower is better (aim for < 3 seconds)

**Token Usage**
- Tokens = words/pieces of text processed by AI
- Billing is based on token count
- Input tokens (your message) + Output tokens (AI response)

**Estimated Costs**
- GPT-4 Turbo: ~$0.01 per 1K input tokens, ~$0.03 per 1K output
- GPT-3.5 Turbo: ~$0.0005 per 1K input, ~$0.0015 per 1K output
- Claude: ~$0.008 per 1K input, ~$0.024 per 1K output
- Gemini: Free tier available

### Common Questions Analysis
Use this to:
- ✅ Identify most popular topics
- ✅ Add more menu items in high-demand categories
- ✅ Improve system prompts for common queries
- ✅ Create FAQ section on website

### Function Usage Stats
Tells you:
- Which AI functions customers use most
- If function calling is working properly
- Whether to add more custom functions

### Hourly Distribution
Insights:
- Peak hours for customer inquiries
- Staff scheduling optimization
- When to run promotions
- Server load planning

---

## 🔧 Advanced Configuration

### Data Retention
```php
// Delete conversations older than 90 days
$ucfc_conversation_logger->delete_old_conversations(90);
```

Add to WordPress Cron:
```php
add_action('wp_scheduled_delete', function() {
    global $ucfc_conversation_logger;
    $ucfc_conversation_logger->delete_old_conversations(90);
});

if (!wp_next_scheduled('wp_scheduled_delete')) {
    wp_schedule_event(time(), 'daily', 'wp_scheduled_delete');
}
```

### Custom Export Format
Modify `export_to_csv()` in `ai-conversation-logger.php`:
```php
// Add custom columns
$conversations = $wpdb->get_results("
    SELECT 
        id,
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as timestamp,
        user_message,
        ai_response,
        ai_provider,
        response_time,
        tokens_used
    FROM {$this->table_name} 
    WHERE {$where} 
    ORDER BY created_at DESC
", ARRAY_A);
```

### Satisfaction Rating Widget (Future Enhancement)
```javascript
// Add to ai-chat.js after AI response
function addSatisfactionRating(conversationId) {
    const ratingHTML = `
        <div class="satisfaction-rating">
            How was this response?
            <span class="star" data-rating="1">⭐</span>
            <span class="star" data-rating="2">⭐</span>
            <span class="star" data-rating="3">⭐</span>
            <span class="star" data-rating="4">⭐</span>
            <span class="star" data-rating="5">⭐</span>
        </div>
    `;
    
    $('.chat-messages').append(ratingHTML);
    
    $('.star').on('click', function() {
        const rating = $(this).data('rating');
        saveSatisfactionRating(conversationId, rating);
    });
}
```

---

## 🎨 Customizing Analytics Dashboard

### Change Color Scheme
Edit `ai-analytics-dashboard.php`:
```css
.metric-card {
    /* Change gradient colors */
    background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);
}
```

### Add Custom Metrics
```php
// In get_analytics() method
$custom_metric = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$this->table_name} 
    WHERE {$where} AND user_message LIKE '%order%'
");

return array(
    // ...existing metrics...
    'order_related_queries' => intval($custom_metric)
);
```

### Custom Chart
```javascript
// Add to analytics dashboard
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Menu Questions', 'Orders', 'Other'],
        datasets: [{
            data: [40, 35, 25],
            backgroundColor: ['#667eea', '#764ba2', '#f093fb']
        }]
    }
});
```

---

## 🐛 Troubleshooting

### Issue: Analytics dashboard showing 0 conversations
**Solution**: Chat with AI first to generate data. Database table may not have been created.

**Fix**:
```php
global $ucfc_conversation_logger;
$ucfc_conversation_logger->create_tables();
```

### Issue: CSV export not working
**Solution**: Check WordPress uploads directory permissions

**Fix**:
```bash
chmod 755 wp-content/uploads
```

### Issue: JSON validation not working
**Solution**: Check browser console for JavaScript errors

**Fix**: Ensure jQuery is loaded:
```php
wp_enqueue_script('jquery');
```

### Issue: Charts not displaying
**Solution**: Chart.js CDN may be blocked

**Fix**: Download Chart.js locally and enqueue:
```php
wp_enqueue_script('chartjs', get_template_directory_uri() . '/assets/js/chart.min.js');
```

---

## 📊 Performance Optimization

### Database Indexing
Already optimized with indexes on:
- `session_id` (for fast session lookups)
- `user_id` (for user-specific queries)
- `created_at` (for date range filters)
- `ai_provider` (for provider comparisons)

### Query Caching
Add transient caching for analytics:
```php
function get_cached_analytics() {
    $cache_key = 'ucfc_analytics_30d';
    $analytics = get_transient($cache_key);
    
    if ($analytics === false) {
        global $ucfc_conversation_logger;
        $analytics = $ucfc_conversation_logger->get_analytics();
        set_transient($cache_key, $analytics, HOUR_IN_SECONDS);
    }
    
    return $analytics;
}
```

### Pagination for Large Datasets
Modify `get_recent_conversations()`:
```php
public function get_recent_conversations($limit = 50, $offset = 0) {
    global $wpdb;
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$this->table_name} 
         ORDER BY created_at DESC 
         LIMIT %d OFFSET %d",
        $limit, $offset
    ));
}
```

---

## 🚀 Next Steps: Phase 3 Preview

**Coming Soon** (3 hours development):
- 🤖 **Multi-Agent Orchestration**: Route queries to specialized AI agents
- 🧠 **RAG (Retrieval-Augmented Generation)**: Vector database for menu items
- 🗣️ **Voice Integration**: Speech-to-text and text-to-speech
- 📸 **Image Understanding**: Gemini Pro Vision for food photos
- 🧪 **A/B Testing**: Compare different prompts and models
- 😊 **Sentiment Analysis**: Detect customer satisfaction automatically
- 🔔 **Real-time Alerts**: Notify staff of negative feedback
- 📱 **Mobile App API**: REST endpoints for mobile apps

---

## 📚 API Reference

### Conversation Logger Methods

```php
global $ucfc_conversation_logger;

// Log a conversation
$id = $ucfc_conversation_logger->log_conversation(array(
    'user_message' => 'Hello',
    'ai_response' => 'Hi there!',
    'response_time' => 2.5,
    'tokens_used' => 100
));

// Get analytics
$analytics = $ucfc_conversation_logger->get_analytics('2025-11-01', '2025-11-30');

// Get trends
$trends = $ucfc_conversation_logger->get_trends(30);

// Export CSV
$csv_url = $ucfc_conversation_logger->export_to_csv();

// Update satisfaction
$ucfc_conversation_logger->update_satisfaction(123, 5);

// Delete old data
$deleted_count = $ucfc_conversation_logger->delete_old_conversations(90);
```

### Function Validator Methods

```php
use UCFC_Function_Schema_Validator;

// Validate JSON
$result = UCFC_Function_Schema_Validator::validate($json_string);
if ($result['valid']) {
    echo "Valid!";
} else {
    print_r($result['errors']);
}

// Get examples
$examples = UCFC_Function_Schema_Validator::get_examples();

// Prepare for AI
$tools = UCFC_Function_Schema_Validator::prepare_for_ai($functions);
```

---

## 🎉 Phase 2 Summary

**Total Files Created**: 3
- `inc/ai-conversation-logger.php` (350 lines)
- `inc/ai-analytics-dashboard.php` (500 lines)
- `inc/ai-function-validator.php` (200 lines)

**Total Files Modified**: 3
- `inc/ai-chat-engine.php` (added logging)
- `inc/ai-assistant-settings.php` (added validation UI)
- `functions.php` (added includes)

**Total New Code**: 1,050+ lines

**Database Tables Created**: 1 (`wp_ucfc_ai_conversations`)

**New Admin Pages**: 1 (AI Analytics)

**New AJAX Endpoints**: 1 (`ucfc_validate_function_json`)

**Status**: ✅ **PHASE 2 COMPLETE!**

---

**Ready for Phase 3?** Let's build multi-agent orchestration and RAG! 🚀
