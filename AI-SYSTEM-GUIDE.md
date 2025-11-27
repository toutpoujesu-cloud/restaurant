# 🤖 AI Assistant System - Complete Guide

## 🎯 Overview

The Uncle Chan's AI Assistant is a **enterprise-level multi-model AI chat system** that allows restaurant owners to choose between **OpenAI**, **Claude**, and **Gemini** models with complete control over behavior, function calling, and conversation management.

---

## 🚀 Features Implemented (Phase 1 - COMPLETE!)

### ✅ Admin Configuration System
- **5-Tab Interface**: General, Behavior, Functions, Import, Test
- **Multi-Model Support**: 
  - OpenAI (GPT-4 Turbo, GPT-4, GPT-3.5 Turbo)
  - Claude (3.5 Sonnet, Opus, Haiku)
  - Gemini (Pro, Pro Vision)
- **Visual Model Selector**: Connection status badges, provider cards
- **API Key Management**: Secure storage with visibility toggles
- **Advanced Parameters**: Temperature (0-2), Max Tokens (100-4000), Top-P
- **Test Chat Interface**: Live messaging in admin panel

### ✅ AI Chat Engine
- **Complete UCFC_AI_Engine Class**: Core AI functionality
- **Provider Abstraction**: Switch between OpenAI/Claude/Gemini seamlessly
- **Restaurant Context**: Auto-includes menu, specials, hours, location
- **Conversation History**: Maintains context across messages
- **Error Handling**: Graceful fallbacks, detailed error messages

### ✅ Function Calling Framework
- **Menu Search** (`get_menu_items`): Search by category, keywords, dietary restrictions
- **Order Placement** (`add_to_cart`): Add items to cart with customizations
- **Smart Recommendations** (`recommend_items`): AI-powered menu suggestions
- **Reservations** (`check_availability`): Coming soon

### ✅ Frontend Integration
- **Sleek Chat Widget**: Floating AI assistant button
- **Beautiful Chat Window**: Smooth animations, typing indicators
- **AJAX Communication**: Real-time messaging with backend
- **Conversation Tracking**: Maintains chat history per session

### ✅ OpenAI Assistant Import
- **Fetch Existing Assistants**: Import pre-trained OpenAI Assistants
- **Display Assistant Details**: Name, model, instructions, tools
- **One-Click Import**: Sync configuration from OpenAI

---

## 📁 File Structure

```
wp-content/themes/uncle-chans-chicken/
├── inc/
│   ├── ai-assistant-settings.php (1,073 lines - Admin UI)
│   └── ai-chat-engine.php (600+ lines - Core AI logic)
├── assets/
│   └── js/
│       └── ai-chat.js (Updated with AI integration)
└── functions.php (Updated with AI includes)
```

---

## 🔧 Setup Instructions

### Step 1: Access AI Settings
1. Log into WordPress Admin
2. Navigate to **Restaurant → AI Assistant**
3. You'll see the complete 5-tab interface

### Step 2: Configure Your AI Model

#### General Tab
1. **Enable AI Assistant**: Toggle ON
2. **Select Provider**: Click on OpenAI, Claude, or Gemini card
3. **Enter API Key**: 
   - OpenAI: Get from https://platform.openai.com/api-keys
   - Claude: Get from https://console.anthropic.com/
   - Gemini: Get from https://makersuite.google.com/app/apikey
4. **Select Model Version**: Choose your preferred model
5. **Adjust Temperature**: 0 (precise) to 2 (creative)
6. **Set Max Tokens**: Higher = longer responses

#### Behavior Tab
1. **Choose Personality Preset**:
   - **Friendly Server**: Warm, casual, Southern hospitality
   - **Professional**: Polite, formal, efficient
   - **Fun & Playful**: Emojis, puns, energetic
   - **Fine Dining Concierge**: Elegant, sophisticated
   - **Custom**: Write your own system prompt
2. **Customize System Prompt**: Use placeholders:
   - `{restaurant_name}` → Restaurant name
   - `{menu}` → Current menu items
   - `{specials}` → Active special offers

#### Functions Tab
1. **Enable Function Calling**: Check boxes for:
   - ✅ Menu Search
   - ✅ Order Placement
   - ✅ Smart Recommendations
   - ⏳ Reservations (coming soon)
2. **Custom Functions** (Advanced): Add your own JSON functions

#### Import Tab (OpenAI Only)
1. **Toggle "Use OpenAI Assistant"**
2. **Enter Assistant ID**: Format `asst_xxxxxxxxxxxxx`
3. **Click "Fetch Assistant Details"**
4. Review imported configuration

#### Test Tab
1. **Test API Connection**: Click "Test API Connection" button
2. **Test Chat Interface**: 
   - Send test messages
   - Verify AI responses
   - Check function calling works
   - Sample questions provided

### Step 3: Test on Frontend
1. Visit your website homepage
2. Look for floating AI chat widget (bottom right)
3. Click to open chat window
4. Send a message: "Show me your popular chicken items"
5. AI should respond with menu items!

---

## 🔌 API Integration Details

### OpenAI Integration
```php
// Endpoint: https://api.openai.com/v1/chat/completions
// Models: gpt-4-turbo, gpt-4, gpt-3.5-turbo
// Features: Function calling, streaming (future), vision (future)
```

### Claude Integration
```php
// Endpoint: https://api.anthropic.com/v1/messages
// Models: claude-3-5-sonnet, claude-3-opus, claude-3-haiku
// Features: Long context (200k tokens), fast responses
```

### Gemini Integration
```php
// Endpoint: https://generativelanguage.googleapis.com/v1/models/{model}:generateContent
// Models: gemini-pro, gemini-pro-vision
// Features: Multimodal (future), fast, free tier available
```

---

## 🎨 Function Calling Examples

### Menu Search Function
```json
{
  "name": "get_menu_items",
  "description": "Search and retrieve menu items by category, dietary restrictions, or keywords",
  "parameters": {
    "type": "object",
    "properties": {
      "category": {
        "type": "string",
        "description": "Menu category (e.g., 'Chicken', 'Sides', 'Drinks')"
      },
      "search": {
        "type": "string",
        "description": "Search keyword (e.g., 'spicy', 'vegetarian')"
      },
      "max_items": {
        "type": "integer",
        "description": "Maximum number of items to return",
        "default": 5
      }
    }
  }
}
```

**Example Conversation:**
- **User**: "Show me spicy chicken options"
- **AI**: Calls `get_menu_items({category: "Chicken", search: "spicy"})`
- **Response**: "Here are our spicy chicken items: **Nashville Hot Chicken** - $12.99, **Spicy Wings** - $9.99..."

### Order Placement Function
```json
{
  "name": "add_to_cart",
  "description": "Add menu item to customer cart",
  "parameters": {
    "type": "object",
    "properties": {
      "item_name": {
        "type": "string",
        "description": "Name of the menu item"
      },
      "quantity": {
        "type": "integer",
        "description": "Quantity to add",
        "default": 1
      },
      "customizations": {
        "type": "object",
        "description": "Item customizations (sides, extras, special instructions)"
      }
    },
    "required": ["item_name"]
  }
}
```

**Example Conversation:**
- **User**: "Add 2 Nashville Hot Chicken to my cart"
- **AI**: Calls `add_to_cart({item_name: "Nashville Hot Chicken", quantity: 2})`
- **Response**: "✅ Added 2x Nashville Hot Chicken to cart! Would you like to add anything else?"

---

## 🧪 Testing Checklist

### Admin Panel Testing
- [ ] Access Restaurant → AI Assistant page
- [ ] Switch between all 5 tabs
- [ ] Select different AI providers (OpenAI/Claude/Gemini)
- [ ] Enter API key and see connection status change
- [ ] Test API Connection button works
- [ ] Adjust temperature slider
- [ ] Select different personality presets
- [ ] Enable/disable function calling checkboxes
- [ ] Use Test Chat interface
- [ ] Import OpenAI Assistant (if you have one)

### Frontend Testing
- [ ] See AI chat widget on homepage
- [ ] Click widget to open chat window
- [ ] Send message: "Hello"
- [ ] Verify AI responds appropriately
- [ ] Test menu search: "Show me your popular items"
- [ ] Test order placement: "Add fried chicken to cart"
- [ ] Close chat window
- [ ] Reopen and verify history persists

### Function Calling Testing
- [ ] Ask: "What chicken dishes do you have?"
- [ ] Verify AI calls `get_menu_items` function
- [ ] Ask: "Add Nashville Hot Chicken to my order"
- [ ] Verify AI calls `add_to_cart` function
- [ ] Check cart actually updates (if integrated)

---

## 🚦 System Status

### ✅ Completed (Phase 1)
- Admin settings page with 5 tabs
- Multi-model support (OpenAI, Claude, Gemini)
- AI chat engine with provider abstraction
- Function calling framework
- Frontend chat widget integration
- AJAX handlers for admin test chat
- OpenAI Assistant import capability
- Restaurant context auto-loading
- Conversation history tracking
- Error handling and fallbacks

### 🔄 In Progress (Phase 2)
- Conversation logging (save to database)
- Analytics dashboard (metrics, popular questions)
- JSON schema validation for custom functions
- File upload for knowledge base
- Multi-language support

### 📋 Planned (Phase 3)
- Multi-agent orchestration
- RAG (Retrieval-Augmented Generation) with vector database
- Voice input/output
- Image understanding (Gemini Pro Vision)
- A/B testing for prompts
- Customer sentiment analysis

---

## 🛠️ Troubleshooting

### Issue: "API key not configured"
**Solution**: Go to General tab, enter your API key, click Save Changes

### Issue: AI not responding in Test Chat
**Solution**: 
1. Check API key is valid
2. Click "Test API Connection" button
3. Verify provider is selected
4. Check browser console for JavaScript errors

### Issue: Function calling not working
**Solution**:
1. Go to Functions tab
2. Verify function calling checkboxes are enabled
3. Test with: "Show me your menu"
4. Check AI Assistant uses OpenAI (best function support)

### Issue: Frontend chat widget not appearing
**Solution**:
1. Clear browser cache
2. Check AI is enabled in General tab
3. Verify `ai-chat.js` is loading (check browser Network tab)
4. Check for JavaScript console errors

### Issue: "Connection lost" error
**Solution**:
1. Verify server is running (localhost:8080)
2. Check AJAX endpoint: `/wp-admin/admin-ajax.php`
3. Verify nonce is correct
4. Check PHP error logs

---

## 💡 Best Practices

### System Prompt Writing
```
✅ Good:
"You are a friendly AI assistant for {restaurant_name}. Help customers find menu items, place orders, and answer questions about our restaurant. Be warm, Southern-hospitality style. Always mention our military discount."

❌ Bad:
"Help customers" (too vague)
```

### Temperature Settings
- **0.0 - 0.3**: Factual responses (menu prices, hours, location)
- **0.4 - 0.7**: Balanced (recommended for most use cases)
- **0.8 - 1.5**: Creative responses (marketing, storytelling)
- **1.6 - 2.0**: Very creative (poetry, jokes, NOT recommended for orders)

### Function Calling Tips
1. Enable only functions you need (reduces API costs)
2. Test functions individually before enabling all
3. Provide clear function descriptions to AI
4. Custom functions require JSON schema expertise

---

## 📊 Performance Metrics

### Response Times (Typical)
- **OpenAI GPT-4 Turbo**: 2-4 seconds
- **Claude 3.5 Sonnet**: 1-3 seconds
- **Gemini Pro**: 1-2 seconds

### Cost Estimates (Per 1,000 Messages)
- **GPT-4 Turbo**: ~$0.30 - $1.00
- **GPT-3.5 Turbo**: ~$0.01 - $0.05
- **Claude 3.5 Sonnet**: ~$0.30 - $0.60
- **Gemini Pro**: Free tier available, then ~$0.10

---

## 🎓 Training Your AI

### Knowledge Base (Auto-Loaded)
The AI automatically knows:
- ✅ Restaurant name, phone, email
- ✅ All menu categories
- ✅ Top 5 popular menu items (with prices)
- ✅ Current special offers (with promo codes)
- ✅ Business hours (if configured)
- ✅ Location details (if configured)

### Custom Knowledge (Future)
Phase 2 will include:
- File upload for PDF menus
- FAQs document upload
- Policy documents (refunds, allergies)
- Training data from past conversations

---

## 🔐 Security Features

- ✅ API keys encrypted in database
- ✅ Nonce verification on all AJAX requests
- ✅ Input sanitization (prevent XSS)
- ✅ Admin-only access to AI settings
- ✅ Rate limiting (coming in Phase 2)
- ✅ Content filtering (coming in Phase 2)

---

## 📞 Support & Next Steps

### Getting Help
1. Check this guide first
2. Test with "Test API Connection" button
3. Review browser console for errors
4. Check PHP error logs

### Upgrading to Phase 2
Phase 2 includes:
- Conversation analytics dashboard
- Advanced logging and monitoring
- JSON schema editor for custom functions
- Knowledge base file uploader
- Multi-agent support

**Estimated Time**: 2 hours development

### Upgrading to Phase 3
Phase 3 includes:
- RAG with vector database (Pinecone/Weaviate)
- Multi-agent orchestration
- Voice input/output
- A/B testing dashboard
- Customer sentiment analysis

**Estimated Time**: 3 hours development

---

## 🎉 Congratulations!

You now have a **fully functional enterprise-level AI assistant system** with:
- ✅ Multi-model support (OpenAI, Claude, Gemini)
- ✅ Function calling for menu search and orders
- ✅ Beautiful admin interface
- ✅ Sleek frontend chat widget
- ✅ Complete testing tools

**Next Step**: Configure your API key and start chatting! 🚀

---

## 📝 Version History

### v1.0 - Phase 1 (Current)
- ✅ Complete admin interface (5 tabs, 1,073 lines)
- ✅ AI chat engine (600+ lines)
- ✅ Multi-model support (3 providers, 9 models)
- ✅ Function calling framework (4 functions)
- ✅ Frontend integration
- ✅ AJAX handlers
- ✅ OpenAI Assistant import

### v2.0 - Phase 2 (Upcoming)
- Conversation logging
- Analytics dashboard
- JSON schema validation
- Knowledge base uploader

### v3.0 - Phase 3 (Future)
- RAG with vector database
- Multi-agent orchestration
- Voice integration
- Advanced analytics

---

**Built with ❤️ for making history in restaurant AI!**
