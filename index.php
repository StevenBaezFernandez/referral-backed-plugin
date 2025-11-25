<?php
/**
 * Plugin Name: Custom API
 * Plugin URI: http://chrushingit.com
 * Description: Referral Management System with REST API
 * Version: 2.0.0
 * Author: Art Vandelay
 * Author URI: http://watch-learn.com
 * Text Domain: custom-api
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Load configuration
require_once plugin_dir_path(__FILE__) . 'config/config.php';

// Load core classes
require_once plugin_dir_path(__FILE__) . 'includes/class-database.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-auth.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-email.php';

// Load API endpoint classes
require_once plugin_dir_path(__FILE__) . 'api/class-referral-endpoints.php';
require_once plugin_dir_path(__FILE__) . 'api/class-analytics-endpoints.php';

/**
 * Main Plugin Class
 */
class Custom_API_Plugin {
    
    /**
     * Plugin instance
     * 
     * @var Custom_API_Plugin
     */
    private static $instance = null;

    /**
     * Get plugin instance
     * 
     * @return Custom_API_Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     * 
     * @return void
     */
    private function init_hooks() {
        add_action('rest_api_init', array($this, 'register_routes'));
        add_filter('jwt_auth_token_before_dispatch', array('Custom_API_Auth', 'add_user_role_to_jwt'), 10, 2);
    }

    /**
     * Register all REST API routes
     * 
     * @return void
     */
    public function register_routes() {
        $referral_endpoints = new Custom_API_Referral_Endpoints();
        $referral_endpoints->register_routes();

        $analytics_endpoints = new Custom_API_Analytics_Endpoints();
        $analytics_endpoints->register_routes();
    }
}

// Initialize the plugin
Custom_API_Plugin::get_instance();
