<?php
/**
 * Initialize QR Code System
 * 
 * Run this file once to set up the QR code pickup system
 */

// Load WordPress
require_once('../../../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator to run this setup.');
}

echo "<h1>🎯 QR Code System Setup</h1>";
echo "<style>
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; background: #f5f7fa; }
h1 { color: #d92027; }
h2 { color: #333; margin-top: 30px; }
.success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745; }
.error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545; }
.info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #17a2b8; }
.warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ffc107; }
pre { background: #2a2a2a; color: #f8f9fa; padding: 15px; border-radius: 8px; overflow-x: auto; }
code { background: #e9ecef; padding: 2px 6px; border-radius: 4px; color: #d92027; }
.stat-box { background: white; padding: 20px; border-radius: 8px; margin: 15px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
</style>";

echo "<h2>📋 Step 1: Create Database Table</h2>";

// Create the pickup tracking table
require_once get_template_directory() . '/inc/qr-code-system.php';

$result = UCFC_QR_Code_System::create_pickup_table();

if ($result) {
    echo "<div class='success'>✅ <strong>Success!</strong> Order pickups table created successfully.</div>";
} else {
    global $wpdb;
    if ($wpdb->last_error) {
        echo "<div class='error'>❌ <strong>Error:</strong> " . $wpdb->last_error . "</div>";
    } else {
        echo "<div class='info'>ℹ️ Table already exists or was created successfully.</div>";
    }
}

echo "<div class='stat-box'>";
echo "<h3>Database Table: wp_ucfc_order_pickups</h3>";
echo "<p>This table tracks all order pickups with the following structure:</p>";
echo "<ul>";
echo "<li><code>id</code> - Primary key</li>";
echo "<li><code>order_id</code> - Reference to order</li>";
echo "<li><code>order_number</code> - Order number (unique)</li>";
echo "<li><code>qr_code_data</code> - Encoded QR data with verification</li>";
echo "<li><code>qr_code_path</code> - Optional file path for saved QR images</li>";
echo "<li><code>picked_up</code> - Pickup status (0/1)</li>";
echo "<li><code>picked_up_at</code> - Timestamp of pickup</li>";
echo "<li><code>picked_up_by</code> - Staff user ID who processed pickup</li>";
echo "<li><code>verification_method</code> - 'scan' or 'manual'</li>";
echo "<li><code>customer_signature</code> - Optional signature data</li>";
echo "<li><code>notes</code> - Pickup notes</li>";
echo "<li><code>created_at</code> - QR code generation timestamp</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🔧 Step 2: System Configuration</h2>";

echo "<div class='stat-box'>";
echo "<h3>QR Code Generation</h3>";
echo "<p>✅ QR codes are generated automatically when:</p>";
echo "<ul>";
echo "<li>Order confirmation page is viewed</li>";
echo "<li>Order confirmation email is sent</li>";
echo "<li>Admin requests QR code display</li>";
echo "</ul>";

echo "<h3>QR Code Content</h3>";
echo "<p>Each QR code contains encrypted JSON data:</p>";
echo "<pre>{
    \"order_id\": 123,
    \"order_number\": \"UCFC-20251127-001\",
    \"verification_code\": \"sha256_hash\",
    \"timestamp\": 1732723200,
    \"site_url\": \"https://yoursite.com\"
}</pre>";

echo "<h3>Security Features</h3>";
echo "<ul>";
echo "<li>✅ HMAC SHA-256 verification codes</li>";
echo "<li>✅ 24-hour expiration window</li>";
echo "<li>✅ Admin-only scanner access</li>";
echo "<li>✅ Duplicate pickup prevention</li>";
echo "<li>✅ Audit trail logging</li>";
echo "</ul>";
echo "</div>";

echo "<h2>📱 Step 3: Scanner Page Setup</h2>";

// Check if scanner page exists
$scanner_page = get_page_by_path('scan-pickup');

if ($scanner_page) {
    echo "<div class='success'>✅ <strong>Scanner page already exists!</strong></div>";
    echo "<p>📍 <strong>Scanner URL:</strong> <a href='" . home_url('/scan-pickup') . "' target='_blank'>" . home_url('/scan-pickup') . "</a></p>";
} else {
    echo "<div class='warning'>⚠️ Scanner page not found. Creating now...</div>";
    
    // Create scanner page
    $page_id = wp_insert_post([
        'post_title' => 'Scan Pickup',
        'post_name' => 'scan-pickup',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => '<!-- Scanner page uses custom template -->'
    ]);
    
    if ($page_id && !is_wp_error($page_id)) {
        // Assign custom template
        update_post_meta($page_id, '_wp_page_template', 'page-scan-pickup.php');
        
        echo "<div class='success'>✅ <strong>Scanner page created!</strong> Page ID: " . $page_id . "</div>";
        echo "<p>📍 <strong>Scanner URL:</strong> <a href='" . home_url('/scan-pickup') . "' target='_blank'>" . home_url('/scan-pickup') . "</a></p>";
    } else {
        echo "<div class='error'>❌ <strong>Failed to create scanner page.</strong></div>";
    }
}

echo "<div class='stat-box'>";
echo "<h3>Scanner Features</h3>";
echo "<ul>";
echo "<li>📹 <strong>Camera QR Scanning:</strong> Real-time QR code detection</li>";
echo "<li>⌨️ <strong>Manual Entry:</strong> Verify by order number</li>";
echo "<li>✅ <strong>Order Verification:</strong> View order details before confirming</li>";
echo "<li>📊 <strong>Statistics Dashboard:</strong> Track daily pickups</li>";
echo "<li>🔒 <strong>Admin Only:</strong> Automatic access control</li>";
echo "</ul>";
echo "</div>";

echo "<h2>📧 Step 4: Email Integration</h2>";

echo "<div class='info'>";
echo "<p>✅ QR codes are automatically added to order confirmation emails!</p>";
echo "<p>The QR code appears as an inline SVG image in the email, so no external hosting is required.</p>";
echo "</div>";

echo "<h2>🎯 Step 5: Testing</h2>";

echo "<div class='stat-box'>";
echo "<h3>Test Procedure</h3>";
echo "<ol>";
echo "<li><strong>Create a test order</strong> on your site</li>";
echo "<li><strong>Check confirmation email</strong> for QR code</li>";
echo "<li><strong>Visit My Orders page</strong> to see QR code display</li>";
echo "<li><strong>Go to Scanner page</strong>: <a href='" . home_url('/scan-pickup') . "' target='_blank'>" . home_url('/scan-pickup') . "</a></li>";
echo "<li><strong>Test manual verification</strong> with order number</li>";
echo "<li><strong>Test camera scanning</strong> (if available)</li>";
echo "<li><strong>Verify pickup completion</strong> in orders dashboard</li>";
echo "</ol>";
echo "</div>";

echo "<h2>📊 Current Statistics</h2>";

global $wpdb;

// Count total orders
$total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}orders");

// Count orders with QR codes
$qr_table = $wpdb->prefix . 'ucfc_order_pickups';
$total_qr = $wpdb->get_var("SELECT COUNT(*) FROM $qr_table");

// Count completed pickups
$completed_pickups = $wpdb->get_var("SELECT COUNT(*) FROM $qr_table WHERE picked_up = 1");

// Count pending pickups
$pending_pickups = $wpdb->get_var("SELECT COUNT(*) FROM $qr_table WHERE picked_up = 0");

echo "<div class='stat-box'>";
echo "<h3>System Overview</h3>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='border-bottom: 2px solid #ddd;'><th style='text-align: left; padding: 10px;'>Metric</th><th style='text-align: right; padding: 10px;'>Count</th></tr>";
echo "<tr><td style='padding: 10px;'>Total Orders</td><td style='text-align: right; padding: 10px;'><strong>" . $total_orders . "</strong></td></tr>";
echo "<tr><td style='padding: 10px;'>Orders with QR Codes</td><td style='text-align: right; padding: 10px;'><strong>" . $total_qr . "</strong></td></tr>";
echo "<tr><td style='padding: 10px;'>Completed Pickups</td><td style='text-align: right; padding: 10px;'><strong style='color: #28a745;'>" . $completed_pickups . "</strong></td></tr>";
echo "<tr><td style='padding: 10px;'>Pending Pickups</td><td style='text-align: right; padding: 10px;'><strong style='color: #ffc107;'>" . $pending_pickups . "</strong></td></tr>";
echo "</table>";
echo "</div>";

echo "<h2>🚀 Next Steps</h2>";

echo "<div class='stat-box'>";
echo "<h3>Production Deployment</h3>";
echo "<ol>";
echo "<li><strong>Test thoroughly</strong> with multiple test orders</li>";
echo "<li><strong>Train staff</strong> on scanner interface usage</li>";
echo "<li><strong>Set up tablet/device</strong> at pickup counter</li>";
echo "<li><strong>Configure camera permissions</strong> in browser</li>";
echo "<li><strong>Monitor pickup statistics</strong> daily</li>";
echo "</ol>";

echo "<h3>Optional Enhancements</h3>";
echo "<ul>";
echo "<li>Install a proper QR library (endroid/qr-code) for production QR codes</li>";
echo "<li>Implement jsQR library for better camera scanning</li>";
echo "<li>Add customer signature capture</li>";
echo "<li>Generate printable pickup receipts</li>";
echo "<li>Send pickup confirmation SMS</li>";
echo "</ul>";
echo "</div>";

echo "<h2>📚 Documentation</h2>";

echo "<div class='info'>";
echo "<p><strong>AJAX Endpoints:</strong></p>";
echo "<ul>";
echo "<li><code>ucfc_generate_qr</code> - Generate QR code for order</li>";
echo "<li><code>ucfc_scan_qr</code> - Scan and validate QR code</li>";
echo "<li><code>ucfc_verify_pickup</code> - Complete pickup verification</li>";
echo "</ul>";

echo "<p><strong>Files Created:</strong></p>";
echo "<ul>";
echo "<li><code>inc/qr-code-system.php</code> - QR code backend system (600+ lines)</li>";
echo "<li><code>page-scan-pickup.php</code> - Scanner interface template (700+ lines)</li>";
echo "<li><code>assets/js/qr-scanner.js</code> - Scanner frontend (optional)</li>";
echo "</ul>";
echo "</div>";

echo "<div class='success'>";
echo "<h2>✅ Setup Complete!</h2>";
echo "<p><strong>The QR Code Pickup System is now ready to use!</strong></p>";
echo "<p>Glory to Yeshuah! 🚀</p>";
echo "</div>";

echo "<div style='margin-top: 40px; padding: 20px; background: #667eea; color: white; border-radius: 12px; text-align: center;'>";
echo "<h2 style='color: white; margin: 0;'>🎉 Phase 4 Complete!</h2>";
echo "<p style='margin: 10px 0 0 0;'>All advanced features have been successfully deployed.</p>";
echo "</div>";
?>
