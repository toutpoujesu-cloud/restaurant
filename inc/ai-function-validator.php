<?php
/**
 * JSON Schema Validator for Custom Functions
 * 
 * Allows restaurant owners to add custom AI functions
 * with proper JSON schema validation
 * 
 * @package Uncle_Chans_Chicken
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class UCFC_Function_Schema_Validator {
    
    /**
     * Validate JSON schema
     */
    public static function validate($json_string) {
        // Decode JSON
        $data = json_decode($json_string, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'valid' => false,
                'errors' => array('Invalid JSON: ' . json_last_error_msg())
            );
        }
        
        $errors = array();
        
        // Must be array of functions
        if (!is_array($data)) {
            $errors[] = 'Root element must be an array of functions';
            return array('valid' => false, 'errors' => $errors);
        }
        
        // Validate each function
        foreach ($data as $index => $function) {
            $function_errors = self::validate_function($function, $index);
            $errors = array_merge($errors, $function_errors);
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data
        );
    }
    
    /**
     * Validate single function definition
     */
    private static function validate_function($function, $index) {
        $errors = array();
        
        // Check required fields
        if (!isset($function['name']) || empty($function['name'])) {
            $errors[] = "Function #{$index}: 'name' is required";
        }
        
        if (!isset($function['description']) || empty($function['description'])) {
            $errors[] = "Function #{$index}: 'description' is required";
        }
        
        // Validate name format (lowercase, underscores only)
        if (isset($function['name']) && !preg_match('/^[a-z_][a-z0-9_]*$/', $function['name'])) {
            $errors[] = "Function #{$index}: 'name' must be lowercase with underscores only";
        }
        
        // Validate parameters if present
        if (isset($function['parameters'])) {
            $param_errors = self::validate_parameters($function['parameters'], $index);
            $errors = array_merge($errors, $param_errors);
        }
        
        return $errors;
    }
    
    /**
     * Validate function parameters
     */
    private static function validate_parameters($parameters, $function_index) {
        $errors = array();
        
        if (!is_array($parameters)) {
            $errors[] = "Function #{$function_index}: 'parameters' must be an object";
            return $errors;
        }
        
        // Check type
        if (!isset($parameters['type']) || $parameters['type'] !== 'object') {
            $errors[] = "Function #{$function_index}: parameters.type must be 'object'";
        }
        
        // Check properties
        if (isset($parameters['properties'])) {
            if (!is_array($parameters['properties'])) {
                $errors[] = "Function #{$function_index}: parameters.properties must be an object";
            } else {
                foreach ($parameters['properties'] as $prop_name => $prop_def) {
                    $prop_errors = self::validate_property($prop_def, $function_index, $prop_name);
                    $errors = array_merge($errors, $prop_errors);
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate property definition
     */
    private static function validate_property($property, $function_index, $prop_name) {
        $errors = array();
        
        if (!is_array($property)) {
            $errors[] = "Function #{$function_index}, property '{$prop_name}': must be an object";
            return $errors;
        }
        
        // Check type
        if (!isset($property['type'])) {
            $errors[] = "Function #{$function_index}, property '{$prop_name}': 'type' is required";
        } else {
            $valid_types = array('string', 'number', 'integer', 'boolean', 'array', 'object');
            if (!in_array($property['type'], $valid_types)) {
                $errors[] = "Function #{$function_index}, property '{$prop_name}': invalid type '{$property['type']}'";
            }
        }
        
        // Check description
        if (!isset($property['description']) || empty($property['description'])) {
            $errors[] = "Function #{$function_index}, property '{$prop_name}': 'description' is required";
        }
        
        return $errors;
    }
    
    /**
     * Get example schemas
     */
    public static function get_examples() {
        return array(
            'check_inventory' => array(
                'name' => 'check_inventory',
                'description' => 'Check if menu items are currently available in stock',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'item_name' => array(
                            'type' => 'string',
                            'description' => 'Name of the menu item to check'
                        )
                    ),
                    'required' => array('item_name')
                )
            ),
            'calculate_delivery_time' => array(
                'name' => 'calculate_delivery_time',
                'description' => 'Calculate estimated delivery time to customer address',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'zip_code' => array(
                            'type' => 'string',
                            'description' => 'Customer zip code'
                        ),
                        'order_size' => array(
                            'type' => 'string',
                            'description' => 'Order size: small, medium, or large'
                        )
                    ),
                    'required' => array('zip_code')
                )
            ),
            'apply_discount' => array(
                'name' => 'apply_discount',
                'description' => 'Apply discount code to order',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'discount_code' => array(
                            'type' => 'string',
                            'description' => 'Discount or promo code to apply'
                        ),
                        'order_total' => array(
                            'type' => 'number',
                            'description' => 'Current order total before discount'
                        )
                    ),
                    'required' => array('discount_code', 'order_total')
                )
            ),
            'check_allergens' => array(
                'name' => 'check_allergens',
                'description' => 'Check menu items for specific allergens',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'allergen' => array(
                            'type' => 'string',
                            'description' => 'Allergen to check for (e.g., nuts, dairy, gluten)'
                        ),
                        'category' => array(
                            'type' => 'string',
                            'description' => 'Optional: menu category to filter by'
                        )
                    ),
                    'required' => array('allergen')
                )
            )
        );
    }
    
    /**
     * Sanitize and prepare function for AI
     */
    public static function prepare_for_ai($functions) {
        $prepared = array();
        
        foreach ($functions as $function) {
            $prepared[] = array(
                'type' => 'function',
                'function' => $function
            );
        }
        
        return $prepared;
    }
}

/**
 * AJAX: Validate custom function JSON
 */
add_action('wp_ajax_ucfc_validate_function_json', function() {
    check_ajax_referer('ucfc_ai_test', 'nonce');
    
    $json = stripslashes($_POST['json']);
    $result = UCFC_Function_Schema_Validator::validate($json);
    
    if ($result['valid']) {
        wp_send_json_success(array(
            'message' => '✅ Valid JSON schema! ' . count($result['data']) . ' function(s) defined.',
            'functions' => $result['data']
        ));
    } else {
        wp_send_json_error(array(
            'message' => '❌ Validation failed',
            'errors' => $result['errors']
        ));
    }
});
