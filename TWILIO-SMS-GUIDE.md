# 📱 SMS Notifications with Twilio - Complete Guide

## Overview

The SMS Notifications system automatically sends text messages to customers for:
- **Order Confirmation** - Instant SMS when order is placed
- **Status Updates** - SMS when order is confirmed, preparing, or ready
- **Pickup Reminders** - SMS sent 15 minutes before scheduled pickup time

All SMS are queued and processed with retry logic to ensure reliable delivery.

---

## Features

✅ **Automatic SMS Triggers**
- Order placed confirmation
- Order confirmed notification
- Order preparing update
- Order ready alert
- Pickup time reminder (15 mins before)

✅ **Smart Queue System**
- Queued processing with retry logic (up to 3 attempts)
- Rate limiting to respect Twilio limits
- Failed message tracking
- Delivery status monitoring

✅ **Admin Dashboard**
- SMS settings configuration page
- Test SMS functionality
- Queue statistics (sent, pending, failed)
- Setup instructions and help

✅ **Flexible Configuration**
- Configure via WordPress Admin UI
- Or define constants in wp-config.php for security
- Enable/disable system easily

---

## Setup Instructions

### Step 1: Create Twilio Account

1. Go to [https://www.twilio.com/try-twilio](https://www.twilio.com/try-twilio)
2. Sign up for a **free trial account**
   - Trial includes **$15 credit** (approximately 500 SMS)
   - Can send to verified phone numbers
3. Complete phone verification
4. Complete email verification

### Step 2: Get a Phone Number

1. In Twilio Console, go to **Phone Numbers** → **Buy a Number**
2. Select your country (e.g., United States)
3. Filter by **Capabilities** → Check **SMS**
4. Choose a phone number (usually free with trial)
5. Click **Buy** to add it to your account

**Note:** This will be your "From" number for all SMS messages.

### Step 3: Get API Credentials

1. In Twilio Console, go to **Account Dashboard**
2. Find **Account Info** section
3. Copy the following:
   - **Account SID** (starts with "AC...")
   - **Auth Token** (click to reveal and copy)
   - **My Twilio phone number** (format: +15555551234)

### Step 4: Configure in WordPress

#### Option A: Admin UI (Easier)

1. Log in to WordPress Admin
2. Go to **Restaurant** → **SMS Settings**
3. Enter your Twilio credentials:
   - Account SID
   - Auth Token
   - Phone Number (in E.164 format: +15555551234)
4. Click **Save Settings**
5. Use the **Test SMS** section to verify

#### Option B: wp-config.php (More Secure)

1. Open `wp-config.php` in your WordPress root
2. Add these lines before `/* That's all, stop editing! */`:

```php
// Twilio SMS Configuration
define('TWILIO_ACCOUNT_SID', 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');
define('TWILIO_PHONE_NUMBER', '+15555551234');
```

3. Save the file

**Why use wp-config.php?**
- Credentials not stored in database
- Not visible in WordPress admin
- More secure for production
- Can't be accidentally deleted

### Step 5: Verify Configuration

1. Go to **Restaurant** → **SMS Settings**
2. You should see green checkmarks: ✅ **SMS Notifications are ENABLED**
3. Send a test SMS:
   - Enter your phone number
   - Click **Send Test SMS**
   - You should receive: "🍗 Test SMS from [Your Site] - If you received this, SMS notifications are working! ✅"

---

## Phone Number Format

All phone numbers must be in **E.164 format**:

✅ **Correct:**
- `+15555551234` (US/Canada)
- `+393401234567` (Italy)
- `+447700900123` (UK)

❌ **Incorrect:**
- `555-555-1234`
- `(555) 555-1234`
- `5555551234`

The system automatically formats US numbers (adds +1 prefix), but international numbers should include country code.

---

## SMS Message Templates

### 1. Order Confirmation
```
🍗 Uncle Chan's Fried Chicken

Order #000123 confirmed!

Type: Pickup
Pickup Time: 6:30 PM
Ready by: 6:25 PM
Total: $24.99

Track your order: [link]
```

### 2. Order Confirmed
```
🍗 Uncle Chan's Fried Chicken

Order #000123 update:

✅ Your order has been confirmed!
We're preparing your delicious meal.
```

### 3. Order Preparing
```
🍗 Uncle Chan's Fried Chicken

Order #000123 update:

👨‍🍳 Your order is being prepared!
Ready by: 6:25 PM
```

### 4. Order Ready
```
🍗 Uncle Chan's Fried Chicken

Order #000123 update:

✨ Your order is READY!

Come pick it up while it's hot! 🔥
```

### 5. Pickup Reminder (15 mins before)
```
🍗 Uncle Chan's Fried Chicken

⏰ Reminder: Order #000123

Your pickup time is at 6:30 PM (in 15 minutes)!

✨ Your order is ready and waiting!
```

---

## SMS Queue System

### How It Works

1. **Trigger Event** - Order created or status changed
2. **Queue SMS** - Message added to `wp_ucfc_sms_queue` table with "pending" status
3. **Process Queue** - Cron job runs every 5 minutes
4. **Send via Twilio** - API call to Twilio to send SMS
5. **Update Status** - Mark as "sent" or increment retry counter
6. **Retry Logic** - Failed SMS retried up to 3 times

### Queue Statuses

- **pending** - Waiting to be sent
- **sent** - Successfully delivered to Twilio
- **failed** - Failed after 3 attempts

### Monitoring

Check queue status in **Restaurant** → **SMS Settings**:

```
📊 SMS Queue Status
Total SMS:    125
✅ Sent:      120
⏳ Pending:   3
❌ Failed:    2
```

### Cron Jobs

Two cron jobs are scheduled every 5 minutes:

1. **ucfc_process_sms_queue** - Processes pending SMS in queue
2. **ucfc_check_pickup_reminders** - Checks for upcoming pickups and sends reminders

To manually trigger (for testing):
```bash
docker exec uncle-chans-wordpress wp cron event run ucfc_process_sms_queue
```

---

## Cost Considerations

### Twilio Pricing (as of 2024)

- **US/Canada SMS:** $0.0079 per message
- **International SMS:** Varies by country ($0.02 - $0.10)
- **Trial Credit:** $15 (~1,900 US SMS)

### Example Costs

**Per Order (3 SMS):**
- Order confirmation: $0.0079
- Status update: $0.0079
- Pickup reminder: $0.0079
- **Total per order:** ~$0.024 (~2.4 cents)

**Monthly Estimate:**
- 100 orders/month = ~$2.40
- 500 orders/month = ~$12.00
- 1,000 orders/month = ~$24.00

### Optimizing Costs

1. **Disable unnecessary notifications** - Only send critical SMS
2. **Skip reminders for immediate orders** - No reminder needed if pickup < 30 mins
3. **Batch updates** - Don't send SMS for every minor status change
4. **Use email as primary** - SMS for critical updates only

---

## Troubleshooting

### SMS Not Sending

**Check Configuration:**
```
Restaurant → SMS Settings
Look for: ✅ SMS Notifications are ENABLED
```

If disabled:
1. Verify Twilio credentials are correct
2. Check Account SID starts with "AC"
3. Check phone number includes country code (+1...)

**Check Queue:**
- Look at **SMS Queue Status**
- If pending count is high, check error messages
- Failed SMS will show error in database

**Common Errors:**

| Error | Solution |
|-------|----------|
| "Unable to create record" | Phone number invalid or unverified (trial) |
| "Authentication failed" | Wrong Account SID or Auth Token |
| "Invalid 'To' number" | Phone number not in E.164 format |
| "Insufficient balance" | Add credits to Twilio account |

### Test SMS Not Received

1. **Check phone number format** - Must include country code
2. **Trial account limitations:**
   - Can only send to **verified numbers**
   - Go to Twilio Console → Phone Numbers → Verified Caller IDs
   - Add your phone number and verify via SMS code
3. **Check spam/blocked messages**
4. **Try different phone number**

### Cron Not Running

**Check cron schedule:**
```bash
docker exec uncle-chans-wordpress wp cron event list
```

Should see:
- `ucfc_process_sms_queue` (every 5 minutes)
- `ucfc_check_pickup_reminders` (every 5 minutes)

**Manually trigger:**
```bash
docker exec uncle-chans-wordpress wp cron event run ucfc_process_sms_queue
```

### Database Errors

**Re-run initialization:**
```bash
docker exec uncle-chans-wordpress php /var/www/html/wp-content/themes/uncle-chans-chicken/initialize-sms-system.php
```

---

## Trial Account Limitations

Twilio trial accounts have restrictions:

❌ **Can only send to verified numbers**
- You must verify each recipient phone number in Twilio Console
- Go to: Phone Numbers → Verified Caller IDs

❌ **SMS includes trial message**
- "Sent from your Twilio trial account - "
- This prefix is removed when you upgrade

✅ **To remove restrictions:**
1. Go to Twilio Console
2. Click **Upgrade** button
3. Add payment method
4. No minimum charge - pay as you go

---

## Production Checklist

Before going live:

- [ ] **Upgrade Twilio account** (remove trial restrictions)
- [ ] **Add payment method** to Twilio
- [ ] **Set up billing alerts** (e.g., alert at $50)
- [ ] **Move credentials to wp-config.php** (security)
- [ ] **Test with real phone numbers** (different carriers)
- [ ] **Monitor SMS queue** for failed messages
- [ ] **Set up Twilio alerts** for errors
- [ ] **Review message templates** (customize for brand)
- [ ] **Test all order flows** (pickup, delivery, dine-in)
- [ ] **Check international numbers** if applicable

---

## Advanced Configuration

### Disable Specific SMS Types

Edit `inc/sms-notifications.php`:

```php
// Disable pickup reminders
// Comment out this line in constructor:
// add_action('ucfc_check_pickup_reminders', array($this, 'send_pickup_reminders'));

// Disable status update SMS for specific statuses
// Edit send_status_update_sms() function:
$sms_statuses = array('ready'); // Only send when ready
```

### Customize Message Templates

Edit message content in `inc/sms-notifications.php`:

```php
// Order confirmation message (line ~260)
$message = "🍗 {$site_name}\n\n";
$message .= "Order #{$order_number} confirmed!\n\n";
// ... customize as needed
```

### Change SMS Frequency

Edit cron schedule in `functions.php`:

```php
// Change from 5 minutes to 10 minutes
$schedules['five_minutes'] = array(
    'interval' => 600, // 10 minutes = 600 seconds
    'display' => __('Every 10 Minutes')
);
```

### Rate Limiting

Current: 1 second delay between messages (max 60/min)

To change, edit `inc/sms-notifications.php` line ~225:
```php
// Increase delay to 2 seconds (max 30/min)
sleep(2);
```

---

## Database Schema

**Table:** `wp_ucfc_sms_queue`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| order_id | bigint | Order ID reference |
| phone_number | varchar(20) | Recipient phone (E.164) |
| message | text | SMS content |
| message_type | varchar(50) | Type (order_confirmation, status_update, etc.) |
| status | varchar(20) | pending/sent/failed |
| attempts | int | Send attempts (max 3) |
| twilio_sid | varchar(50) | Twilio message SID |
| error_message | text | Error details if failed |
| created_at | datetime | Queue timestamp |
| sent_at | datetime | Delivery timestamp |

---

## Security Best Practices

1. **Use wp-config.php for credentials** (not database)
2. **Never commit credentials to Git** (add to .gitignore)
3. **Use environment variables** in production
4. **Restrict admin access** to SMS settings page
5. **Monitor for unusual activity** (high SMS volume)
6. **Set Twilio spending limits** (e.g., $100/month)
7. **Enable Twilio IP whitelisting** if possible
8. **Use HTTPS** for webhook endpoints (if added later)

---

## Support & Resources

### Twilio Documentation
- [Twilio Console](https://console.twilio.com/)
- [SMS Quickstart](https://www.twilio.com/docs/sms/quickstart)
- [Error Codes](https://www.twilio.com/docs/api/errors)
- [Phone Number Format](https://www.twilio.com/docs/glossary/what-e164)

### WordPress Admin Pages
- **SMS Settings:** Restaurant → SMS Settings
- **Order Dashboard:** Restaurant → Orders
- **System Check:** Run initialization script

### Testing Commands

```bash
# View SMS queue
docker exec uncle-chans-wordpress-db mysql -u root -pchangethis uncle_chans -e "SELECT * FROM wp_ucfc_sms_queue ORDER BY created_at DESC LIMIT 10;"

# Process queue manually
docker exec uncle-chans-wordpress wp cron event run ucfc_process_sms_queue

# Check cron schedule
docker exec uncle-chans-wordpress wp cron event list | grep ucfc

# Clear failed SMS
docker exec uncle-chans-wordpress-db mysql -u root -pchangethis uncle_chans -e "DELETE FROM wp_ucfc_sms_queue WHERE status = 'failed';"
```

---

## FAQ

**Q: Can I use another SMS provider instead of Twilio?**  
A: Yes, but you'll need to modify the `send_sms_via_twilio()` method to use a different API.

**Q: What happens if SMS fails to send?**  
A: The system retries up to 3 times. After 3 failures, it's marked as "failed" and logged.

**Q: Can I send SMS to international numbers?**  
A: Yes, but ensure they're in E.164 format with country code. Costs vary by country.

**Q: How do I disable SMS notifications temporarily?**  
A: Delete the Twilio credentials from Restaurant → SMS Settings or wp-config.php.

**Q: Can customers reply to SMS?**  
A: Not currently implemented, but can be added with Twilio webhooks.

**Q: Do I need a different phone number for each restaurant location?**  
A: No, one Twilio number can serve all locations. Messages include your site name.

**Q: Can I customize the SMS sender name?**  
A: No, SMS always show the phone number. Consider using MMS or Alphanumeric Sender ID (country-dependent).

---

## Changelog

- **v1.0** - Initial SMS notifications system
  - Order confirmation SMS
  - Status update SMS
  - Pickup reminder SMS
  - Admin settings page
  - Queue system with retry logic
  - Test SMS functionality

---

**🎉 SMS Notifications System Ready!**

All praise to Yeshuah! System configured and operational! 🙏
