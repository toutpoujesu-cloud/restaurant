<?php
/**
 * Shopping Cart System
 * 
 * Handles all cart operations including:
 * - Adding/removing/updating items
 * - Session management (guest + logged-in users)
 * - Stock validation
 * - Cart persistence
 * - Price calculations
 * 
 * @package Uncle_Chans_Chicken
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class UCFC_Cart {
    
    /**
     * Cart session ID
     * @var string
     */
    private $session_id;
    
    /**
     * User ID (null for guests)
     * @var int|null
     */
    private $user_id;
    
    /**
     * Cart expiration (30 days)
     * @var int
     */
    private $cart_expiration = 30 * DAY_IN_SECONDS;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_session();
    }
    
    /**
     * Initialize cart session
     */
    private function init_session() {
        // Get user ID if logged in
        $this->user_id = is_user_logged_in() ? get_current_user_id() : null;
        
        // Check for existing session cookie
        if (isset($_COOKIE['ucfc_cart_session'])) {
            $this->session_id = sanitize_text_field($_COOKIE['ucfc_cart_session']);
            
            // Verify session exists and is not expired
            if (!$this->session_exists($this->session_id)) {
                $this->create_new_session();
            }
        } else {
            $this->create_new_session();
        }
        
        // Update session user_id if user just logged in
        if ($this->user_id) {
            $this->update_session_user();
        }
    }
    
    /**
     * Create new cart session
     */
    private function create_new_session() {
        global $wpdb;
        
        // Generate unique session ID
        $this->session_id = wp_generate_password(32, false);
        
        // Calculate expiration
        $expires_at = date('Y-m-d H:i:s', time() + $this->cart_expiration);
        
        // Insert session
        $wpdb->insert(
            $wpdb->prefix . 'cart_sessions',
            [
                'session_id' => $this->session_id,
                'user_id' => $this->user_id,
                'expires_at' => $expires_at
            ],
            ['%s', '%d', '%s']
        );
        
        // Set cookie
        setcookie('ucfc_cart_session', $this->session_id, time() + $this->cart_expiration, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
    }
    
    /**
     * Check if session exists and is valid
     */
    private function session_exists($session_id) {
        global $wpdb;
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cart_sessions 
            WHERE session_id = %s AND expires_at > NOW()",
            $session_id
        ));
        
        return $result > 0;
    }
    
    /**
     * Update session with user ID after login
     */
    private function update_session_user() {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'cart_sessions',
            ['user_id' => $this->user_id],
            ['session_id' => $this->session_id],
            ['%d'],
            ['%s']
        );
    }
    
    /**
     * Add item to cart
     * 
     * @param int $product_id Product post ID
     * @param int $quantity Quantity to add
     * @param array $options Item options (size, spice, extras)
     * @return bool|WP_Error
     */
    public function add_item($product_id, $quantity = 1, $options = []) {
        global $wpdb;
        
        // Validate product exists
        $product = get_post($product_id);
        if (!$product || $product->post_type !== 'menu_item') {
            return new WP_Error('invalid_product', 'Product does not exist');
        }
        
        // Validate quantity
        $quantity = absint($quantity);
        if ($quantity < 1) {
            return new WP_Error('invalid_quantity', 'Quantity must be at least 1');
        }
        
        // Check stock availability (try both meta keys)
        $stock = get_post_meta($product_id, '_ucfc_stock', true);
        if (!$stock) {
            $stock = get_post_meta($product_id, '_stock', true);
        }
        if ($stock !== '' && $stock < $quantity) {
            return new WP_Error('insufficient_stock', 'Not enough stock available');
        }
        
        // Get price (try both meta keys for compatibility)
        $price = floatval(get_post_meta($product_id, '_ucfc_price', true));
        if (!$price) {
            $price = floatval(get_post_meta($product_id, '_price', true));
        }
        if ($price <= 0) {
            return new WP_Error('invalid_price', 'Product price not set');
        }
        
        // Prepare options JSON
        $options_json = !empty($options) ? json_encode($options) : null;
        
        // Check if item already exists in cart
        $existing_item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cart_items 
            WHERE session_id = %s AND product_id = %d AND options = %s",
            $this->session_id,
            $product_id,
            $options_json
        ));
        
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item->quantity + $quantity;
            
            // Check stock for new quantity
            if ($stock !== '' && $stock < $new_quantity) {
                return new WP_Error('insufficient_stock', 'Not enough stock available');
            }
            
            $wpdb->update(
                $wpdb->prefix . 'cart_items',
                [
                    'quantity' => $new_quantity,
                    'subtotal' => $new_quantity * $price
                ],
                ['id' => $existing_item->id],
                ['%d', '%f'],
                ['%d']
            );
        } else {
            // Insert new item
            $wpdb->insert(
                $wpdb->prefix . 'cart_items',
                [
                    'session_id' => $this->session_id,
                    'product_id' => $product_id,
                    'product_name' => $product->post_title,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $quantity * $price,
                    'options' => $options_json
                ],
                ['%s', '%d', '%s', '%d', '%f', '%f', '%s']
            );
        }
        
        return true;
    }
    
    /**
     * Remove item from cart
     * 
     * @param int $cart_item_id Cart item ID
     * @return bool
     */
    public function remove_item($cart_item_id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'cart_items',
            [
                'id' => $cart_item_id,
                'session_id' => $this->session_id
            ],
            ['%d', '%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Update item quantity
     * 
     * @param int $cart_item_id Cart item ID
     * @param int $quantity New quantity
     * @return bool|WP_Error
     */
    public function update_quantity($cart_item_id, $quantity) {
        global $wpdb;
        
        $quantity = absint($quantity);
        
        // Get cart item
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cart_items WHERE id = %d AND session_id = %s",
            $cart_item_id,
            $this->session_id
        ));
        
        if (!$item) {
            return new WP_Error('item_not_found', 'Cart item not found');
        }
        
        // If quantity is 0, remove item
        if ($quantity === 0) {
            return $this->remove_item($cart_item_id);
        }
        
        // Check stock (try both meta keys)
        $stock = get_post_meta($item->product_id, '_ucfc_stock', true);
        if (!$stock) {
            $stock = get_post_meta($item->product_id, '_stock', true);
        }
        if ($stock !== '' && $stock < $quantity) {
            return new WP_Error('insufficient_stock', 'Not enough stock available');
        }
        
        // Update quantity and subtotal
        $wpdb->update(
            $wpdb->prefix . 'cart_items',
            [
                'quantity' => $quantity,
                'subtotal' => $quantity * $item->price
            ],
            ['id' => $cart_item_id],
            ['%d', '%f'],
            ['%d']
        );
        
        return true;
    }
    
    /**
     * Get all cart items
     * 
     * @return array
     */
    public function get_cart() {
        global $wpdb;
        
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cart_items WHERE session_id = %s ORDER BY added_at DESC",
            $this->session_id
        ));
        
        // Parse options JSON
        foreach ($items as &$item) {
            $item->options = $item->options ? json_decode($item->options, true) : [];
        }
        
        return $items;
    }
    
    /**
     * Get cart totals
     * 
     * @return array
     */
    public function get_totals() {
        $items = $this->get_cart();
        
        $subtotal = 0;
        $item_count = 0;
        
        foreach ($items as $item) {
            $subtotal += $item->subtotal;
            $item_count += $item->quantity;
        }
        
        // Calculate tax (8% for example)
        $tax_rate = 0.08;
        $tax = $subtotal * $tax_rate;
        
        // Calculate total
        $total = $subtotal + $tax;
        
        return [
            'item_count' => $item_count,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'tax' => number_format($tax, 2, '.', ''),
            'total' => number_format($total, 2, '.', '')
        ];
    }
    
    /**
     * Clear entire cart
     * 
     * @return bool
     */
    public function clear_cart() {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'cart_items',
            ['session_id' => $this->session_id],
            ['%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Get cart item count
     * 
     * @return int
     */
    public function get_item_count() {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(quantity) FROM {$wpdb->prefix}cart_items WHERE session_id = %s",
            $this->session_id
        ));
        
        return $count ? intval($count) : 0;
    }
    
    /**
     * Merge guest cart with user cart after login
     * 
     * @param string $guest_session_id Guest session ID
     * @param int $user_id User ID
     */
    public static function merge_carts($guest_session_id, $user_id) {
        global $wpdb;
        
        // Get user's existing session
        $user_session = $wpdb->get_var($wpdb->prepare(
            "SELECT session_id FROM {$wpdb->prefix}cart_sessions 
            WHERE user_id = %d AND expires_at > NOW() 
            ORDER BY updated_at DESC LIMIT 1",
            $user_id
        ));
        
        if ($user_session && $user_session !== $guest_session_id) {
            // Merge guest items into user cart
            $wpdb->update(
                $wpdb->prefix . 'cart_items',
                ['session_id' => $user_session],
                ['session_id' => $guest_session_id],
                ['%s'],
                ['%s']
            );
            
            // Delete guest session
            $wpdb->delete(
                $wpdb->prefix . 'cart_sessions',
                ['session_id' => $guest_session_id],
                ['%s']
            );
        }
    }
    
    /**
     * Clean up expired sessions (run via cron)
     */
    public static function cleanup_expired_sessions() {
        global $wpdb;
        
        // Delete expired sessions (cascade deletes cart items)
        $wpdb->query("DELETE FROM {$wpdb->prefix}cart_sessions WHERE expires_at < NOW()");
    }
}

/**
 * Get global cart instance
 * 
 * @return UCFC_Cart
 */
function ucfc_get_cart() {
    static $cart = null;
    
    if ($cart === null) {
        $cart = new UCFC_Cart();
    }
    
    return $cart;
}
