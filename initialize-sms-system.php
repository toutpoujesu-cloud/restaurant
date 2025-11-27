<?php
/**
 * Initialize SMS Notifications System
 * Run this once to create the SMS queue table and configure cron jobs
 */

// Load WordPress
require_once(__DIR__ . '/../../../wp-load.php');

global $wpdb;

echo "🚀 Initializing SMS Notifications System...\n\n";

// 1. Create SMS queue table
$table_name = $wpdb->prefix . 'ucfc_sms_queue';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    order_id bigint(20) NOT NULL,
    phone_number varchar(20) NOT NULL,
    message text NOT NULL,
    message_type varchar(50) NOT NULL,
    status varchar(20) NOT NULL DEFAULT 'pending',
    attempts int(11) NOT NULL DEFAULT 0,
    twilio_sid varchar(50),
    error_message text,
    created_at datetime NOT NULL,
    sent_at datetime,
    PRIMARY KEY  (id),
    KEY order_id (order_id),
    KEY status (status),
    KEY created_at (created_at)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

echo "✅ SMS queue table created: {$table_name}\n";

// 2. Check table structure
$columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");
echo "📋 Table columns:\n";
foreach ($columns as $column) {
    echo "   - {$column->Field} ({$column->Type})\n";
}

// 3. Schedule cron jobs
if (!wp_next_scheduled('ucfc_process_sms_queue')) {
    wp_schedule_event(time(), 'five_minutes', 'ucfc_process_sms_queue');
    echo "\n✅ Scheduled SMS queue processor (every 5 minutes)\n";
} else {
    echo "\n✓ SMS queue processor already scheduled\n";
}

if (!wp_next_scheduled('ucfc_check_pickup_reminders')) {
    wp_schedule_event(time(), 'five_minutes', 'ucfc_check_pickup_reminders');
    echo "✅ Scheduled pickup reminders (every 5 minutes)\n";
} else {
    echo "✓ Pickup reminders already scheduled\n";
}

// 4. Check Twilio configuration
$twilio_sid = defined('TWILIO_ACCOUNT_SID') ? TWILIO_ACCOUNT_SID : get_option('ucfc_twilio_sid');
$twilio_token = defined('TWILIO_AUTH_TOKEN') ? TWILIO_AUTH_TOKEN : get_option('ucfc_twilio_token');
$twilio_phone = defined('TWILIO_PHONE_NUMBER') ? TWILIO_PHONE_NUMBER : get_option('ucfc_twilio_phone');

echo "\n📱 Twilio Configuration Status:\n";
echo "   - Account SID: " . (!empty($twilio_sid) ? "✅ Configured" : "❌ Not configured") . "\n";
echo "   - Auth Token: " . (!empty($twilio_token) ? "✅ Configured" : "❌ Not configured") . "\n";
echo "   - Phone Number: " . (!empty($twilio_phone) ? "✅ Configured ({$twilio_phone})" : "❌ Not configured") . "\n";

if (empty($twilio_sid) || empty($twilio_token) || empty($twilio_phone)) {
    echo "\n⚠️  SMS notifications are DISABLED - Configure Twilio credentials:\n";
    echo "   1. Go to: Restaurant → SMS Settings in WordPress Admin\n";
    echo "   2. Or add to wp-config.php:\n";
    echo "      define('TWILIO_ACCOUNT_SID', 'ACxxxx...');\n";
    echo "      define('TWILIO_AUTH_TOKEN', 'your_token');\n";
    echo "      define('TWILIO_PHONE_NUMBER', '+15555551234');\n";
} else {
    echo "\n✅ SMS notifications are ENABLED and ready!\n";
}

// 5. Display cron schedule
echo "\n📅 Scheduled Cron Jobs:\n";
$cron = _get_cron_array();
foreach ($cron as $timestamp => $events) {
    foreach ($events as $hook => $details) {
        if (strpos($hook, 'ucfc_') === 0) {
            $time = date('Y-m-d H:i:s', $timestamp);
            echo "   - {$hook}: Next run at {$time}\n";
        }
    }
}

echo "\n🎉 SMS Notifications System initialized successfully!\n";
echo "\n📚 Next Steps:\n";
echo "1. Configure Twilio credentials in Restaurant → SMS Settings\n";
echo "2. Send a test SMS to verify configuration\n";
echo "3. Place a test order to see SMS notifications in action\n";
echo "4. Monitor SMS queue in Restaurant → SMS Settings\n";
