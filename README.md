# Custom API Plugin - Refactored

## Overview
This WordPress plugin provides a comprehensive REST API for managing referrals at Newtech. It has been refactored from a single monolithic file to a well-organized, scalable structure.

## Directory Structure

```
wp-custom-api/
├── index.php                          # Main plugin file (entry point)
├── config/
│   └── config.php                     # Configuration constants and settings
├── includes/
│   ├── class-database.php             # Database operations handler
│   ├── class-auth.php                 # API authentication
│   └── class-email.php                # Email notifications service
├── api/
│   ├── class-referral-endpoints.php   # Referral CRUD endpoints
│   └── class-analytics-endpoints.php  # Analytics and statistics endpoints
└── README.md                          # This file
```

## Architecture

### Main Components

#### 1. **Configuration** (`config/config.php`)
- Defines all plugin constants
- Database table names
- API settings
- Email configuration
- Encryption settings

#### 2. **Database Handler** (`includes/class-database.php`)
- `Custom_API_Database` class
- Handles all database operations
- Methods for CRUD operations on referrals
- Statistics and analytics queries
- Changelog management

**Key Methods:**
- `get_all_referrals()` - Fetch all referrals with full details
- `create_referral($data)` - Create new referral
- `update_referral_status($id, $status_id, $updated_by, $updated_at)` - Update status
- `get_referral_source_stats($from_date, $to_date)` - Analytics data
- `get_referral_status_stats($from_date, $to_date)` - Status statistics

#### 3. **Authentication** (`includes/class-auth.php`)
- `Custom_API_Auth` class
- API key validation
- CORS headers management
- Encryption/decryption utilities
- JWT token customization

**Key Methods:**
- `validate_request($request)` - Validates API requests
- `decrypt_api_key($encrypted_key)` - Decrypts API keys
- `add_user_role_to_jwt($data, $user)` - Adds user roles to JWT

#### 4. **Email Service** (`includes/class-email.php`)
- `Custom_API_Email` class
- Sends referral code emails
- Status update notifications
- HTML email templates

**Key Methods:**
- `send_referral_code_email($recipient_email, $referral_code)` - Welcome email
- `send_status_update_email($recipient_email, $referral_name, $old_status, $new_status)` - Status change notification

#### 5. **Referral Endpoints** (`api/class-referral-endpoints.php`)
- `Custom_API_Referral_Endpoints` class
- All referral-related REST routes
- CRUD operations for referrals

**Available Endpoints:**
- `GET /c-api/v1/test` - Get all referrals
- `GET /c-api/v1/referrals-by-employee-id/{id}` - Get by employee ID
- `GET /c-api/v1/referrals-by-referral-id/{code}` - Get by referral code
- `POST /c-api/v1/add-referral` - Create new referral
- `PUT /c-api/v1/set-status/{id}/{status_id}` - Update status
- `GET /c-api/v1/get-referrer-by-id/{id}` - Get referrer details
- `GET /c-api/v1/get-referrers` - Get all referrers
- `GET /c-api/v1/get-referrers-with-referrals` - Get referrers with their referrals
- `GET /c-api/v1/get-statuses` - Get all available statuses
- `POST /c-api/v1/unleash_forms` - External form submission

#### 6. **Analytics Endpoints** (`api/class-analytics-endpoints.php`)
- `Custom_API_Analytics_Endpoints` class
- Statistics and reporting routes

**Available Endpoints:**
- `GET /c-api/v1/get-total-referral` - Total referrals count
- `GET /c-api/v1/get-total-internos` - Internal referrals count
- `GET /c-api/v1/get-total-externos` - External referrals count
- `GET /c-api/v1/get-total-excluidos` - Rejected referrals count
- `GET /c-api/v1/get-status-monthly` - Monthly review statistics
- `GET /c-api/v1/get-referral-source` - Referral source breakdown
- `POST /c-api/v1/get_referral_source_by_date` - Source stats by date range
- `GET /c-api/v1/get-referral-status-chart` - Status distribution
- `POST /c-api/v1/get_referral_status_chart_by_date` - Status stats by date range

## Database Tables

The plugin uses the following custom tables:

- `wp_referrals` - Main referral records
- `wp_referrer` - Referrer information
- `wp_referrals_status` - Status definitions
- `wp_referral_code` - Unique referral codes
- `wp_changelog_status_referral` - Status change audit log
- `wp_internal` - Employee validation data

## API Authentication

All endpoints require API key authentication via the `x-api-key` header. The API key must be encrypted using AES-256-CBC before transmission.

**Environment Variables Required:**
- `API_KEY` - The master API key
- `ENCRYPTION_KEY` - Encryption key for API key transmission

## Adding New Features

### To Add a New Endpoint:

1. **Determine the category** (Referral or Analytics)
2. **Add method to appropriate class**:
   ```php
   // In api/class-referral-endpoints.php or api/class-analytics-endpoints.php
   public function your_new_endpoint($request) {
       // Your logic here
       return new WP_REST_Response($data, 200);
   }
   ```
3. **Register the route**:
   ```php
   // In the register_routes() method
   register_rest_route(CUSTOM_API_NAMESPACE, '/your-endpoint', array(
       'methods' => 'GET',
       'callback' => array($this, 'your_new_endpoint'),
       'permission_callback' => array('Custom_API_Auth', 'validate_request'),
       'show_in_index' => false,
   ));
   ```

### To Add Database Operations:

Add methods to `Custom_API_Database` class in `includes/class-database.php`:

```php
public function your_database_operation($params) {
    $sql = $this->wpdb->prepare("YOUR SQL HERE", $params);
    return $this->wpdb->get_results($sql);
}
```

### To Add Email Notifications:

Add methods to `Custom_API_Email` class in `includes/class-email.php`:

```php
public static function send_your_notification($recipient, $data) {
    $subject = 'Your Subject';
    $body = self::get_your_template($data);
    return wp_mail($recipient, $subject, $body, $headers);
}
```

## Benefits of This Structure

1. **Separation of Concerns** - Each class has a single responsibility
2. **Maintainability** - Easy to locate and modify specific functionality
3. **Scalability** - Simple to add new features without affecting existing code
4. **Testability** - Classes can be unit tested independently
5. **Readability** - Clean, organized code with proper documentation
6. **DRY Principle** - No code duplication
7. **Version Control** - Changes are isolated to specific files

## Migration Notes

- The old `index.php` has been backed up as `index.php.backup`
- All functionality remains the same
- API endpoints are unchanged
- Database operations are identical
- Authentication works the same way

## Future Enhancements

Consider adding:
- Caching layer using Redis (already installed)
- Rate limiting for API endpoints
- Webhook notifications
- Bulk operations support
- CSV export functionality
- Advanced filtering and search
- API documentation endpoint
- Unit tests
- Database migration system
- Logging and monitoring

## Version History

**v2.0.0** - Refactored architecture
- Organized code into modular structure
- Separated concerns into distinct classes
- Improved documentation
- Enhanced maintainability

**v1.0.0** - Original monolithic version
- Single file with all functionality

## Support

For questions or issues, contact the development team.
