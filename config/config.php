<?php
/**
 * Plugin Configuration
 * 
 * @package Custom_API
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin version
define('CUSTOM_API_VERSION', '1.0.0');

// Plugin paths
define('CUSTOM_API_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));
define('CUSTOM_API_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));

// API namespace and version
define('CUSTOM_API_NAMESPACE', 'c-api/v1');

// Database table names
global $wpdb;
define('CUSTOM_API_TABLE_REFERRALS', $wpdb->prefix . 'referrals');
define('CUSTOM_API_TABLE_REFERRER', $wpdb->prefix . 'referrer');
define('CUSTOM_API_TABLE_REFERRALS_STATUS', $wpdb->prefix . 'referrals_status');
define('CUSTOM_API_TABLE_REFERRAL_CODE', $wpdb->prefix . 'referral_code');
define('CUSTOM_API_TABLE_CHANGELOG', $wpdb->prefix . 'changelog_status_referral');
define('CUSTOM_API_TABLE_INTERNAL', $wpdb->prefix . 'internal');

// Email configuration
define('CUSTOM_API_EMAIL_FROM', 'hr@newtechsa.com');
define('CUSTOM_API_EMAIL_PHONE', '1+ (829)-692-8482');
define('CUSTOM_API_REFERRAL_URL', 'https://nt-referral.com/status');
define('CUSTOM_API_LOGO_URL', 'http://nt-referral.com/wp-content/uploads/2023/05/reaferral-app-logo.png');

// Encryption settings (from wp-config.php or environment variables)
if (!defined('CUSTOM_API_ENCRYPTION_KEY')) {
    // Try multiple sources in order of preference
    if (defined('WP_ENCRYPTION_KEY')) {
        // From wp-config.php constant
        //define('CUSTOM_API_ENCRYPTION_KEY', WP_ENCRYPTION_KEY);
    } elseif (getenv('ENCRYPTION_KEY')) {
        // From server environment variable
        define('CUSTOM_API_ENCRYPTION_KEY', getenv('ENCRYPTION_KEY'));
    } elseif (isset($_ENV['ENCRYPTION_KEY']) && !empty($_ENV['ENCRYPTION_KEY'])) {
        // From PHP $_ENV superglobal
        define('CUSTOM_API_ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY']);
    } else {
        // No key configured - will run in development mode (no auth)
        define('CUSTOM_API_ENCRYPTION_KEY', '');
    }
}

if (!defined('CUSTOM_API_API_KEY')) {
    // Try multiple sources in order of preference
    if (defined('WP_API_KEY')) {
        // From wp-config.php constant
        //define('CUSTOM_API_API_KEY', WP_API_KEY);
    } elseif (getenv('API_KEY')) {
        // From server environment variable
        define('CUSTOM_API_API_KEY', getenv('API_KEY'));
    } elseif (isset($_ENV['API_KEY']) && !empty($_ENV['API_KEY'])) {
        // From PHP $_ENV superglobal
        define('CUSTOM_API_API_KEY', $_ENV['API_KEY']);
    } else {
        // No key configured - will run in development mode (no auth)
        define('CUSTOM_API_API_KEY', '');
    }
}

define('CUSTOM_API_ENCRYPTION_IV', '1234567890123456'); // 16-byte IV for AES-256-CBC

return [
    'version' => CUSTOM_API_VERSION,
    'namespace' => CUSTOM_API_NAMESPACE,
    'tables' => [
        'referrals' => CUSTOM_API_TABLE_REFERRALS,
        'referrer' => CUSTOM_API_TABLE_REFERRER,
        'status' => CUSTOM_API_TABLE_REFERRALS_STATUS,
        'code' => CUSTOM_API_TABLE_REFERRAL_CODE,
        'changelog' => CUSTOM_API_TABLE_CHANGELOG,
        'internal' => CUSTOM_API_TABLE_INTERNAL,
    ],
];
