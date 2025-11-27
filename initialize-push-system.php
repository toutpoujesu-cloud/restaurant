<?php
/**
 * Initialize Push Notifications System
 * Run this once to create the subscriptions table
 */

// Load WordPress
require_once(__DIR__ . '/../../../wp-load.php');

global $wpdb;

echo "🔔 Initializing Push Notifications System...\n\n";

// 1. Create push subscriptions table
$table_name = $wpdb->prefix . 'ucfc_push_subscriptions';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20),
    guest_email varchar(255),
    endpoint text NOT NULL,
    public_key varchar(255) NOT NULL,
    auth_token varchar(255) NOT NULL,
    user_agent text,
    ip_address varchar(45),
    subscribed_at datetime NOT NULL,
    last_notification_at datetime,
    notification_count int(11) DEFAULT 0,
    is_active tinyint(1) DEFAULT 1,
    PRIMARY KEY  (id),
    UNIQUE KEY endpoint (endpoint(191)),
    KEY user_id (user_id),
    KEY guest_email (guest_email),
    KEY is_active (is_active)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

echo "✅ Push subscriptions table created: {$table_name}\n";

// 2. Check table structure
$columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
echo "📋 Table columns:\n";
foreach ($columns as $column) {
    echo "   - {$column->Field} ({$column->Type})\n";
}

// 3. Check VAPID keys
$vapid_public = get_option('ucfc_vapid_public_key');
$vapid_private = get_option('ucfc_vapid_private_key');

echo "\n🔑 VAPID Keys Status:\n";
echo "   - Public Key: " . (!empty($vapid_public) ? "✅ Configured (" . substr($vapid_public, 0, 20) . "...)" : "❌ Not configured") . "\n";
echo "   - Private Key: " . (!empty($vapid_private) ? "✅ Configured" : "❌ Not configured") . "\n";

if (empty($vapid_public) || empty($vapid_private)) {
    echo "\n⚠️  Push notifications are DISABLED - Generate VAPID keys:\n";
    echo "   1. Go to: Restaurant → Push Notifications in WordPress Admin\n";
    echo "   2. Click 'Generate VAPID Keys' button\n";
    echo "   3. Save settings\n";
} else {
    echo "\n✅ Push notifications are ENABLED and ready!\n";
}

// 4. Display subscription stats
$stats = $wpdb->get_row("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
        SUM(notification_count) as total_sent
    FROM {$table_name}
");

echo "\n📊 Current Statistics:\n";
echo "   - Total Subscriptions: " . number_format($stats->total) . "\n";
echo "   - Active: " . number_format($stats->active) . "\n";
echo "   - Inactive: " . number_format($stats->total - $stats->active) . "\n";
echo "   - Total Notifications Sent: " . number_format($stats->total_sent) . "\n";

// 5. Check service worker
$sw_path = get_template_directory() . '/service-worker.js';
if (file_exists($sw_path)) {
    echo "\n✅ Service worker file exists: service-worker.js\n";
} else {
    echo "\n❌ Service worker file NOT found!\n";
}

// 6. Check push handler script
$handler_path = get_template_directory() . '/assets/js/push-handler.js';
if (file_exists($handler_path)) {
    echo "✅ Push handler script exists: push-handler.js\n";
} else {
    echo "❌ Push handler script NOT found!\n";
}

echo "\n🎉 Push Notifications System initialized successfully!\n";
echo "\n📚 Next Steps:\n";
echo "1. Generate VAPID keys in Restaurant → Push Notifications\n";
echo "2. Visit checkout page as a customer\n";
echo "3. Allow notifications when prompted\n";
echo "4. Place a test order\n";
echo "5. Change order status to see push notifications\n";
echo "\n💡 Browser Compatibility:\n";
echo "✅ Chrome, Firefox, Edge, Opera\n";
echo "❌ Safari (limited), iOS Safari (not supported)\n";
