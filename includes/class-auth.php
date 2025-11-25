<?php
/**
 * API Authentication Class
 * 
 * Handles API key validation and CORS headers
 * 
 * @package Custom_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Custom_API_Auth {
    
    /**
     * Validate API request
     * 
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public static function validate_request($request) {
        // Set CORS headers
        self::set_cors_headers();

        // Get API key from header
        $api_key_from_header = $request->get_header('x-api-key');
        
        // If no API key configured, allow all requests (backward compatibility)
        if (empty(CUSTOM_API_API_KEY) || empty(CUSTOM_API_ENCRYPTION_KEY)) {
            // No auth configured - allow request (development/backward compatibility mode)
            return true;
        }
        
        if (!$api_key_from_header) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('API key is required.', 'custom-api'),
                array('status' => 403)
            );
        }

        // Decrypt API key
        $decrypted_key = self::decrypt_api_key($api_key_from_header);

        // Compare with stored key
        if (CUSTOM_API_API_KEY === $decrypted_key) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__('Invalid API key.', 'custom-api'),
            array('status' => 403)
        );
    }

    /**
     * Set CORS headers for API requests
     * 
     * @return void
     */
    private static function set_cors_headers() {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    }

    /**
     * Decrypt API key
     * 
     * @param string $encrypted_key
     * @return string
     */
    private static function decrypt_api_key($encrypted_key) {
        return openssl_decrypt(
            $encrypted_key,
            'AES-256-CBC',
            CUSTOM_API_ENCRYPTION_KEY,
            0,
            CUSTOM_API_ENCRYPTION_IV
        );
    }

    /**
     * Encrypt API key (utility method)
     * 
     * @param string $api_key
     * @return string
     */
    public static function encrypt_api_key($api_key) {
        return openssl_encrypt(
            $api_key,
            'AES-256-CBC',
            CUSTOM_API_ENCRYPTION_KEY,
            0,
            CUSTOM_API_ENCRYPTION_IV
        );
    }

    /**
     * Add user role to JWT token
     * 
     * @param array $data
     * @param WP_User $user
     * @return array
     */
    public static function add_user_role_to_jwt($data, $user) {
        $data['user_roles'] = $user->roles;
        return $data;
    }
}
