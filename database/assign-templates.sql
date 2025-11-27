-- Assign page templates to Checkout and Order Confirmation pages
-- Run: docker exec -i uncle-chans-mysql mysql -u root -p uncle_chans_wp < assign-templates.sql

-- Assign checkout template to page ID 46 (Checkout)
INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
VALUES (46, '_wp_page_template', 'page-checkout.php')
ON DUPLICATE KEY UPDATE meta_value = 'page-checkout.php';

-- Create Order Confirmation page if it doesn't exist
INSERT INTO wp_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
SELECT 1, NOW(), UTC_TIMESTAMP(), '', 'Order Confirmation', '', 'publish', 'closed', 'closed', '', 'order-confirmation', '', '', NOW(), UTC_TIMESTAMP(), '', 0, 'http://localhost:8080/?page_id=47', 0, 'page', '', 0
WHERE NOT EXISTS (SELECT 1 FROM wp_posts WHERE post_name = 'order-confirmation' AND post_type = 'page');

-- Get the page ID (will be 47 if it's new, or existing ID)
SET @confirmation_page_id = (SELECT ID FROM wp_posts WHERE post_name = 'order-confirmation' AND post_type = 'page' LIMIT 1);

-- Assign order confirmation template
INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
VALUES (@confirmation_page_id, '_wp_page_template', 'page-order-confirmation.php')
ON DUPLICATE KEY UPDATE meta_value = 'page-order-confirmation.php';

-- Show results
SELECT 'Templates assigned successfully!' AS result;
SELECT ID, post_title, post_name FROM wp_posts WHERE ID IN (46, @confirmation_page_id);
SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key = '_wp_page_template' AND post_id IN (46, @confirmation_page_id);
