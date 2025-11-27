# Stripe Payment Integration Guide

## Overview

Uncle Chan's Fried Chicken theme now supports **Stripe payment processing** for credit/debit card payments. This guide explains how to set up and configure Stripe for your restaurant.

## Features

✅ **Secure Payment Processing** - PCI-compliant card payments via Stripe  
✅ **Stripe Elements** - Beautiful, customizable payment form  
✅ **Payment Intent API** - Modern payment flow with Strong Customer Authentication (SCA)  
✅ **Webhook Support** - Real-time payment status updates  
✅ **Automatic Order Updates** - Payment status synced with order status  
✅ **Cash Payment Option** - Customers can still pay on delivery/pickup  

---

## Step 1: Create Stripe Account

1. Go to [https://stripe.com](https://stripe.com)
2. Click "Sign up" and create an account
3. Complete business verification (required for live payments)
4. Activate your account

---

## Step 2: Get API Keys

### Test Mode Keys (For Development)

1. Log in to [Stripe Dashboard](https://dashboard.stripe.com)
2. Make sure you're in **Test Mode** (toggle in top-right corner)
3. Navigate to **Developers → API keys**
4. Copy the following keys:
   - **Publishable key** (starts with `pk_test_`)
   - **Secret key** (starts with `sk_test_`)

### Live Mode Keys (For Production)

1. Switch to **Live Mode** in Stripe Dashboard
2. Navigate to **Developers → API keys**
3. Copy the following keys:
   - **Publishable key** (starts with `pk_live_`)
   - **Secret key** (starts with `sk_live_`)

---

## Step 3: Configure WordPress

### Option A: Using wp-config.php (Recommended)

Add these constants to your `wp-config.php` file (above the "That's all" line):

```php
// Stripe API Keys (Test Mode)
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY_HERE');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_PUBLISHABLE_KEY_HERE');
define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET_HERE');
```

**For production**, use live keys:

```php
// Stripe API Keys (Live Mode)
define('STRIPE_SECRET_KEY', 'sk_live_YOUR_SECRET_KEY_HERE');
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_YOUR_PUBLISHABLE_KEY_HERE');
define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET_HERE');
```

### Option B: Using Environment Variables

If your hosting supports environment variables (e.g., Docker, Heroku):

```bash
STRIPE_SECRET_KEY=sk_test_YOUR_SECRET_KEY_HERE
STRIPE_PUBLISHABLE_KEY=pk_test_YOUR_PUBLISHABLE_KEY_HERE
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET_HERE
```

---

## Step 4: Set Up Webhooks

Webhooks allow Stripe to notify your site when payment events occur (successful payments, refunds, etc.).

### Create Webhook Endpoint

1. Go to **Stripe Dashboard → Developers → Webhooks**
2. Click **Add endpoint**
3. Enter your webhook URL:
   ```
   https://yourdomain.com/wp-admin/admin-ajax.php?action=ucfc_stripe_webhook
   ```
4. Select events to listen for:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
5. Click **Add endpoint**
6. Copy the **Signing secret** (starts with `whsec_`)
7. Add it to your `wp-config.php`:
   ```php
   define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET_HERE');
   ```

---

## Step 5: Test the Integration

### Using Test Cards

Stripe provides test card numbers for development:

| Card Number         | Description                    |
|---------------------|--------------------------------|
| 4242 4242 4242 4242 | Successful payment             |
| 4000 0000 0000 0002 | Declined (generic error)       |
| 4000 0000 0000 9995 | Declined (insufficient funds)  |
| 4000 0025 0000 3155 | Requires authentication (3DS)  |

**Use any:**
- Expiration date: Any future date (e.g., 12/34)
- CVC: Any 3 digits (e.g., 123)
- ZIP: Any 5 digits (e.g., 12345)

### Test Workflow

1. Add items to cart
2. Go to **Checkout** (`/checkout`)
3. Select **Pickup** or **Delivery**
4. Fill in customer information
5. Select **Credit/Debit Card** payment method
6. Enter test card number: **4242 4242 4242 4242**
7. Click **Place Order**
8. Verify order is created with **Paid** status
9. Check **Orders Dashboard** in WordPress admin

---

## Step 6: Go Live

### Before Going Live

- [ ] Switch from Test Mode to Live Mode in Stripe Dashboard
- [ ] Update API keys in `wp-config.php` to live keys
- [ ] Update webhook endpoint URL (if using different domain)
- [ ] Test with small real transactions
- [ ] Verify email notifications are working
- [ ] Check order confirmation page displays correctly

### Important Security Notes

⚠️ **Never commit API keys to Git**  
⚠️ **Never expose secret keys in frontend code**  
⚠️ **Always use HTTPS in production**  
⚠️ **Keep WordPress and theme updated**  

---

## Troubleshooting

### "Payment system not configured" Error

**Solution:** Make sure Stripe API keys are defined in `wp-config.php`

### Webhook Not Receiving Events

**Solution:** 
1. Check webhook URL is correct
2. Verify webhook secret is set
3. Check Stripe Dashboard → Webhooks for failed events
4. Test webhook endpoint manually

### Payment Successful But Order Shows "Pending"

**Solution:**
1. Check webhook is set up correctly
2. Verify webhook secret matches
3. Check WordPress error logs for webhook failures

### Card Declined Errors

**Solution:**
- In test mode: Use test card numbers from Stripe docs
- In live mode: Customer's actual card may be declined by their bank

---

## Payment Flow

```
1. Customer adds items to cart
2. Goes to checkout page
3. Fills in information (Step 1-2)
4. Selects Credit Card payment
5. Theme creates Payment Intent on Stripe ($XX.XX)
6. Customer enters card details (Stripe Elements)
7. Stripe validates card and processes payment
8. On success: Theme creates order in database
9. Order marked as "Paid" with Payment Intent ID
10. Confirmation email sent to customer
11. Notification email sent to restaurant
12. Webhook confirms payment (backup verification)
```

---

## Features in Detail

### Payment Intent API

- Modern payment flow recommended by Stripe
- Supports Strong Customer Authentication (SCA/3D Secure)
- Better fraud protection
- Automatic payment method selection

### Stripe Elements

- Pre-built, customizable UI components
- Automatic validation
- Mobile-optimized
- PCI-compliant (no sensitive data touches your server)

### Webhook Integration

- Real-time payment status updates
- Handles edge cases (network failures, etc.)
- Automatic order status synchronization
- Refund handling

---

## Supported Payment Methods

✅ **Visa**  
✅ **Mastercard**  
✅ **American Express**  
✅ **Discover**  
✅ **Diners Club**  
✅ **JCB**  
✅ **Cash on Delivery/Pickup** (no Stripe processing)

---

## Fees

Stripe charges:
- **2.9% + $0.30** per successful card charge (US)
- No setup fees, monthly fees, or hidden fees
- Fees vary by country - check [Stripe Pricing](https://stripe.com/pricing)

---

## Support

### Stripe Support
- Documentation: [https://stripe.com/docs](https://stripe.com/docs)
- Support: [https://support.stripe.com](https://support.stripe.com)

### Theme Support
- Check theme documentation
- Review WordPress error logs
- Test in Test Mode before going live

---

## Next Steps

After setting up Stripe:

1. **Test thoroughly** with test cards
2. **Configure email templates** (see `inc/checkout-process.php`)
3. **Set up order notifications** for kitchen staff
4. **Train staff** on orders dashboard
5. **Go live** and start accepting payments! 🎉

---

**Last Updated:** November 27, 2025  
**Stripe API Version:** 2023-10-16
