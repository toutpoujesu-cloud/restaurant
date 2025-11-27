<?php
/**
 * Enhanced Email System
 * Beautiful HTML email templates for order notifications
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get email header HTML
 */
function ucfc_get_email_header($title = 'Order Notification') {
    $logo_url = get_template_directory_uri() . '/assets/images/logo.png'; // Add your logo
    $site_name = get_bloginfo('name');
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo esc_html($title); ?></title>
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                background-color: #f5f5f5;
                color: #333333;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
            }
            .email-header {
                background: linear-gradient(135deg, #d92027 0%, #ff6b6b 100%);
                padding: 40px 20px;
                text-align: center;
            }
            .email-header img {
                max-width: 150px;
                height: auto;
                margin-bottom: 15px;
            }
            .email-header h1 {
                color: #ffffff;
                margin: 0;
                font-size: 28px;
                font-weight: 700;
            }
            .email-body {
                padding: 40px 30px;
            }
            .success-icon {
                text-align: center;
                margin-bottom: 30px;
            }
            .success-icon svg {
                width: 80px;
                height: 80px;
            }
            .order-header {
                text-align: center;
                margin-bottom: 40px;
            }
            .order-header h2 {
                color: #333;
                font-size: 24px;
                margin: 0 0 10px 0;
            }
            .order-header p {
                color: #666;
                font-size: 14px;
                margin: 0;
            }
            .info-grid {
                display: table;
                width: 100%;
                margin-bottom: 30px;
                border: 2px solid #f0f0f0;
                border-radius: 8px;
                overflow: hidden;
            }
            .info-row {
                display: table-row;
            }
            .info-cell {
                display: table-cell;
                padding: 15px 20px;
                border-bottom: 1px solid #f0f0f0;
            }
            .info-row:last-child .info-cell {
                border-bottom: none;
            }
            .info-label {
                font-weight: 600;
                color: #666;
                width: 40%;
            }
            .info-value {
                color: #333;
            }
            .order-items {
                margin: 30px 0;
            }
            .order-items h3 {
                color: #333;
                font-size: 20px;
                margin-bottom: 20px;
            }
            .item-row {
                display: flex;
                justify-content: space-between;
                padding: 15px 0;
                border-bottom: 1px solid #f0f0f0;
            }
            .item-row:last-child {
                border-bottom: 2px solid #e0e0e0;
            }
            .item-details {
                flex: 1;
            }
            .item-name {
                font-weight: 600;
                color: #333;
                margin-bottom: 5px;
            }
            .item-quantity {
                color: #666;
                font-size: 14px;
            }
            .item-price {
                font-weight: 600;
                color: #d92027;
                white-space: nowrap;
            }
            .totals {
                margin-top: 20px;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 8px;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                font-size: 16px;
            }
            .total-row.final {
                border-top: 2px solid #333;
                margin-top: 10px;
                padding-top: 15px;
                font-size: 20px;
                font-weight: 700;
                color: #d92027;
            }
            .cta-button {
                display: inline-block;
                background: #d92027;
                color: #ffffff !important;
                padding: 15px 40px;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
                margin: 30px 0;
            }
            .footer-info {
                background: #f8f9fa;
                padding: 30px;
                text-align: center;
                border-top: 3px solid #d92027;
            }
            .footer-info p {
                color: #666;
                font-size: 14px;
                line-height: 1.6;
                margin: 10px 0;
            }
            .social-links {
                margin: 20px 0;
            }
            .social-links a {
                display: inline-block;
                margin: 0 10px;
                color: #d92027;
                text-decoration: none;
                font-size: 24px;
            }
            @media only screen and (max-width: 600px) {
                .email-body {
                    padding: 30px 20px;
                }
                .info-grid {
                    display: block;
                }
                .info-row, .info-cell {
                    display: block;
                }
                .info-label {
                    width: 100%;
                    padding-bottom: 5px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1><?php echo esc_html($site_name); ?></h1>
                <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px;">Finger-Lickin' Good!</p>
            </div>
    <?php
    return ob_get_clean();
}

/**
 * Get email footer HTML
 */
function ucfc_get_email_footer() {
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    $phone = get_option('uncle_chans_phone', '(555) 123-4567');
    $address = get_option('uncle_chans_address', '123 Main St, City, State 12345');
    
    ob_start();
    ?>
            <div class="footer-info">
                <p><strong><?php echo esc_html($site_name); ?></strong></p>
                <p><?php echo esc_html($address); ?></p>
                <p>Phone: <?php echo esc_html($phone); ?></p>
                
                <div class="social-links">
                    <a href="#" title="Facebook">📘</a>
                    <a href="#" title="Instagram">📷</a>
                    <a href="#" title="Twitter">🐦</a>
                </div>
                
                <p style="font-size: 12px; color: #999;">
                    You're receiving this email because you placed an order at <?php echo esc_html($site_name); ?>.<br>
                    If you have questions, please contact us at <?php echo esc_html($phone); ?>.
                </p>
                
                <p style="font-size: 12px; color: #999; margin-top: 20px;">
                    © <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?>. All rights reserved.
                </p>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Enhanced Order Confirmation Email (Customer)
 */
function ucfc_send_enhanced_order_confirmation($order_id, $order) {
    global $wpdb;
    
    // Get order items
    $items = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}order_items WHERE order_id = %d",
        $order_id
    ));
    
    // Start email content
    $email = ucfc_get_email_header('Order Confirmation');
    
    // Success icon
    $email .= '<div class="email-body">';
    $email .= '<div class="success-icon">';
    $email .= '<svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">';
    $email .= '<circle cx="50" cy="50" r="45" fill="#4caf50"/>';
    $email .= '<path d="M30 50 L45 65 L70 35" stroke="white" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>';
    $email .= '</svg>';
    $email .= '</div>';
    
    // Order header
    $email .= '<div class="order-header">';
    $email .= '<h2>Thank You for Your Order!</h2>';
    $email .= '<p>Your order has been received and is being prepared.</p>';
    $email .= '</div>';
    
    // Order info
    $email .= '<div class="info-grid">';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Order Number</div>';
    $email .= '<div class="info-cell info-value"><strong>' . esc_html($order->order_number) . '</strong></div>';
    $email .= '</div>';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Order Date</div>';
    $email .= '<div class="info-cell info-value">' . date('F j, Y \a\t g:i A', strtotime($order->created_at)) . '</div>';
    $email .= '</div>';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Order Type</div>';
    $email .= '<div class="info-cell info-value">' . ucfirst($order->order_type) . '</div>';
    $email .= '</div>';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Estimated Time</div>';
    $email .= '<div class="info-cell info-value">' . $order->estimated_time . ' minutes</div>';
    $email .= '</div>';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Payment Status</div>';
    $email .= '<div class="info-cell info-value">' . ucfirst($order->payment_status) . '</div>';
    $email .= '</div>';
    $email .= '</div>';
    
    // Delivery address if applicable
    if ($order->order_type === 'delivery' && !empty($order->customer_address)) {
        $email .= '<div style="margin: 20px 0; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">';
        $email .= '<strong style="color: #856404;">Delivery Address:</strong><br>';
        $email .= '<p style="margin: 10px 0 0 0; color: #856404;">' . nl2br(esc_html($order->customer_address)) . '</p>';
        $email .= '</div>';
    }
    
    // Order items
    $email .= '<div class="order-items">';
    $email .= '<h3>Order Details</h3>';
    
    foreach ($items as $item) {
        $email .= '<div class="item-row">';
        $email .= '<div class="item-details">';
        $email .= '<div class="item-name">' . esc_html($item->product_name) . '</div>';
        $email .= '<div class="item-quantity">Qty: ' . $item->quantity . ' × $' . number_format($item->price, 2) . '</div>';
        $email .= '</div>';
        $email .= '<div class="item-price">$' . number_format($item->subtotal, 2) . '</div>';
        $email .= '</div>';
    }
    
    // Totals
    $email .= '<div class="totals">';
    $email .= '<div class="total-row">';
    $email .= '<span>Subtotal</span>';
    $email .= '<span>$' . number_format($order->subtotal, 2) . '</span>';
    $email .= '</div>';
    $email .= '<div class="total-row">';
    $email .= '<span>Tax (8%)</span>';
    $email .= '<span>$' . number_format($order->tax, 2) . '</span>';
    $email .= '</div>';
    if ($order->delivery_fee > 0) {
        $email .= '<div class="total-row">';
        $email .= '<span>Delivery Fee</span>';
        $email .= '<span>$' . number_format($order->delivery_fee, 2) . '</span>';
        $email .= '</div>';
    }
    $email .= '<div class="total-row final">';
    $email .= '<span>Total</span>';
    $email .= '<span>$' . number_format($order->total, 2) . '</span>';
    $email .= '</div>';
    $email .= '</div>';
    $email .= '</div>';
    
    // Special instructions
    if (!empty($order->special_instructions)) {
        $cleaned_instructions = preg_replace('/\[Payment Intent:.*?\]/', '', $order->special_instructions);
        $cleaned_instructions = trim($cleaned_instructions);
        if (!empty($cleaned_instructions)) {
            $email .= '<div style="margin: 30px 0; padding: 20px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 8px;">';
            $email .= '<strong style="color: #1565c0;">Special Instructions:</strong><br>';
            $email .= '<p style="margin: 10px 0 0 0; color: #1565c0;">' . nl2br(esc_html($cleaned_instructions)) . '</p>';
            $email .= '</div>';
        }
    }
    
    // CTA button
    $track_url = home_url('/order-confirmation?order=' . $order->order_number);
    $email .= '<div style="text-align: center;">';
    $email .= '<a href="' . esc_url($track_url) . '" class="cta-button">View Order Details</a>';
    $email .= '</div>';
    
    $email .= '</div>'; // Close email-body
    
    // Footer
    $email .= ucfc_get_email_footer();
    
    // Send email
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail(
        $order->customer_email,
        'Order Confirmation - ' . $order->order_number,
        $email,
        $headers
    );
}

/**
 * Enhanced Staff Notification Email
 */
function ucfc_send_enhanced_staff_notification($order_id, $order) {
    global $wpdb;
    
    // Get order items
    $items = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}order_items WHERE order_id = %d",
        $order_id
    ));
    
    // Start email content
    $email = ucfc_get_email_header('New Order Received');
    
    // Alert icon
    $email .= '<div class="email-body">';
    $email .= '<div class="success-icon">';
    $email .= '<svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">';
    $email .= '<circle cx="50" cy="50" r="45" fill="#ff9800"/>';
    $email .= '<text x="50" y="70" font-size="60" text-anchor="middle" fill="white">🔔</text>';
    $email .= '</svg>';
    $email .= '</div>';
    
    // Order header
    $email .= '<div class="order-header">';
    $email .= '<h2>New Order Received!</h2>';
    $email .= '<p>Action required: Prepare this order ASAP</p>';
    $email .= '</div>';
    
    // Order info
    $email .= '<div class="info-grid">';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Order Number</div>';
    $email .= '<div class="info-cell info-value"><strong style="font-size: 18px; color: #d92027;">' . esc_html($order->order_number) . '</strong></div>';
    $email .= '</div>';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Order Type</div>';
    $email .= '<div class="info-cell info-value"><strong>' . ucfirst($order->order_type) . '</strong></div>';
    $email .= '</div>';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Estimated Time</div>';
    $email .= '<div class="info-cell info-value"><strong>' . $order->estimated_time . ' minutes</strong></div>';
    $email .= '</div>';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Customer</div>';
    $email .= '<div class="info-cell info-value">' . esc_html($order->customer_name) . '</div>';
    $email .= '</div>';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Phone</div>';
    $email .= '<div class="info-cell info-value"><a href="tel:' . esc_attr($order->customer_phone) . '">' . esc_html($order->customer_phone) . '</a></div>';
    $email .= '</div>';
    $email .= '<div class="info-row">';
    $email .= '<div class="info-cell info-label">Payment</div>';
    $email .= '<div class="info-cell info-value">' . ucfirst($order->payment_method) . ' - ' . ucfirst($order->payment_status) . '</div>';
    $email .= '</div>';
    $email .= '</div>';
    
    // Delivery address if applicable
    if ($order->order_type === 'delivery' && !empty($order->customer_address)) {
        $email .= '<div style="margin: 20px 0; padding: 20px; background: #ffebee; border-left: 4px solid #f44336; border-radius: 8px;">';
        $email .= '<strong style="color: #c62828;">🚚 DELIVERY ADDRESS:</strong><br>';
        $email .= '<p style="margin: 10px 0 0 0; color: #c62828; font-size: 16px;">' . nl2br(esc_html($order->customer_address)) . '</p>';
        $email .= '</div>';
    }
    
    // Order items
    $email .= '<div class="order-items">';
    $email .= '<h3>Items to Prepare</h3>';
    
    foreach ($items as $item) {
        $email .= '<div class="item-row">';
        $email .= '<div class="item-details">';
        $email .= '<div class="item-name" style="font-size: 18px;">' . esc_html($item->product_name) . '</div>';
        $email .= '<div class="item-quantity" style="font-size: 16px; font-weight: 600; color: #d92027;">Quantity: ' . $item->quantity . '</div>';
        $email .= '</div>';
        $email .= '<div class="item-price" style="font-size: 18px;">$' . number_format($item->subtotal, 2) . '</div>';
        $email .= '</div>';
    }
    
    // Total
    $email .= '<div class="totals">';
    $email .= '<div class="total-row final">';
    $email .= '<span>Order Total</span>';
    $email .= '<span>$' . number_format($order->total, 2) . '</span>';
    $email .= '</div>';
    $email .= '</div>';
    $email .= '</div>';
    
    // Special instructions
    if (!empty($order->special_instructions)) {
        $cleaned_instructions = preg_replace('/\[Payment Intent:.*?\]/', '', $order->special_instructions);
        $cleaned_instructions = trim($cleaned_instructions);
        if (!empty($cleaned_instructions)) {
            $email .= '<div style="margin: 30px 0; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">';
            $email .= '<strong style="color: #856404; font-size: 16px;">⚠️ SPECIAL INSTRUCTIONS:</strong><br>';
            $email .= '<p style="margin: 10px 0 0 0; color: #856404; font-size: 16px;">' . nl2br(esc_html($cleaned_instructions)) . '</p>';
            $email .= '</div>';
        }
    }
    
    // CTA button
    $admin_url = admin_url('admin.php?page=ucfc-orders');
    $email .= '<div style="text-align: center;">';
    $email .= '<a href="' . esc_url($admin_url) . '" class="cta-button">View in Admin Dashboard</a>';
    $email .= '</div>';
    
    $email .= '</div>'; // Close email-body
    
    // Footer
    $email .= ucfc_get_email_footer();
    
    // Send to admin email
    $admin_email = get_option('admin_email');
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail(
        $admin_email,
        '🔔 New Order #' . $order->order_number . ' - ' . ucfirst($order->order_type),
        $email,
        $headers
    );
}
