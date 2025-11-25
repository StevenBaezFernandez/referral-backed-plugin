# Custom API Plugin - Quick Reference Guide

## 📁 File Structure Overview

```
wp-custom-api/
│
├── 📄 index.php (85 lines)                    ← Main entry point
│   └── Initializes plugin, loads all classes
│
├── 📁 config/
│   └── 📄 config.php                          ← All constants & settings
│
├── 📁 includes/
│   ├── 📄 class-database.php (600+ lines)    ← All DB operations
│   ├── 📄 class-auth.php (95 lines)          ← API authentication
│   └── 📄 class-email.php (180 lines)        ← Email service
│
├── 📁 api/
│   ├── 📄 class-referral-endpoints.php       ← Referral CRUD routes
│   └── 📄 class-analytics-endpoints.php      ← Stats & analytics routes
│
└── 📄 README.md                               ← Full documentation
```

## 🎯 How to Add New Features

### 1️⃣ Adding a New API Endpoint

**Example: Add "Get Referral by Email" endpoint**

**Step 1:** Add method to `api/class-referral-endpoints.php`
```php
public function get_referral_by_email($request) {
    $email = $request['email'];
    $result = $this->db->get_referral_by_email($email);
    
    return new WP_REST_Response(array(
        'status' => true,
        'data' => $result
    ), 200);
}
```

**Step 2:** Register route in `register_routes()` method
```php
register_rest_route(CUSTOM_API_NAMESPACE, '/referral-by-email/(?P<email>[^/]+)', array(
    'methods' => 'GET',
    'callback' => array($this, 'get_referral_by_email'),
    'permission_callback' => array('Custom_API_Auth', 'validate_request'),
    'show_in_index' => false,
));
```

**Step 3:** Add database method to `includes/class-database.php`
```php
public function get_referral_by_email($email) {
    $sql = $this->wpdb->prepare(
        "SELECT * FROM " . CUSTOM_API_TABLE_REFERRALS . " WHERE email = %s",
        $email
    );
    return $this->wpdb->get_results($sql);
}
```

### 2️⃣ Adding a New Database Table

Add to `config/config.php`:
```php
define('CUSTOM_API_TABLE_NEW_FEATURE', $wpdb->prefix . 'new_feature');
```

Add methods to `includes/class-database.php`:
```php
public function create_new_feature($data) {
    // Insert logic
}

public function get_new_features() {
    // Select logic
}
```

### 3️⃣ Adding Email Templates

Add to `includes/class-email.php`:
```php
public static function send_new_notification($recipient, $data) {
    $subject = 'New Notification';
    $body = self::get_new_template($data);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    return wp_mail($recipient, $subject, $body, $headers);
}

private static function get_new_template($data) {
    return '<html>Your HTML template here</html>';
}
```

## 🔐 API Authentication

All requests require encrypted API key in header:
```
x-api-key: [encrypted_key]
```

Encryption: AES-256-CBC using `ENCRYPTION_KEY` from environment.

## 📊 Current API Endpoints

### Referral Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/c-api/v1/test` | Get all referrals |
| GET | `/c-api/v1/referrals-by-employee-id/{id}` | Get by employee |
| GET | `/c-api/v1/referrals-by-referral-id/{code}` | Get by code |
| POST | `/c-api/v1/add-referral` | Create referral |
| PUT | `/c-api/v1/set-status/{id}/{status}` | Update status |
| GET | `/c-api/v1/get-referrer-by-id/{id}` | Get referrer |
| GET | `/c-api/v1/get-referrers` | List referrers |
| GET | `/c-api/v1/get-statuses` | List statuses |

### Analytics Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/c-api/v1/get-total-referral` | Total count |
| GET | `/c-api/v1/get-total-internos` | Internal count |
| GET | `/c-api/v1/get-total-externos` | External count |
| GET | `/c-api/v1/get-total-excluidos` | Rejected count |
| GET | `/c-api/v1/get-status-monthly` | Monthly stats |
| GET | `/c-api/v1/get-referral-source` | Source breakdown |
| POST | `/c-api/v1/get_referral_source_by_date` | Source by date |
| GET | `/c-api/v1/get-referral-status-chart` | Status chart |
| POST | `/c-api/v1/get_referral_status_chart_by_date` | Status by date |

## 🗄️ Database Tables

- `wp_referrals` - Main records
- `wp_referrer` - Referrer info
- `wp_referrals_status` - Status types
- `wp_referral_code` - Unique codes
- `wp_changelog_status_referral` - Audit log
- `wp_internal` - Employee validation

## ✅ Benefits of Refactored Structure

1. **Easy to Navigate** - Find any feature quickly
2. **Simple to Test** - Each class can be tested independently  
3. **Quick to Modify** - Changes are isolated
4. **Easy to Scale** - Add features without breaking existing code
5. **Team Friendly** - Multiple developers can work simultaneously
6. **Better Git History** - See exactly what changed where
7. **Reduced Bugs** - Less code duplication, clearer logic

## 🚀 Next Steps for Scaling

### Short Term
- [ ] Add input validation layer
- [ ] Implement request logging
- [ ] Add response caching with Redis
- [ ] Create API documentation endpoint
- [ ] Add rate limiting

### Medium Term
- [ ] Webhook system for status updates
- [ ] Bulk operations (import/export)
- [ ] Advanced filtering system
- [ ] Database migration manager
- [ ] Unit test suite

### Long Term
- [ ] GraphQL endpoint option
- [ ] Real-time notifications with WebSockets
- [ ] Machine learning for referral predictions
- [ ] Mobile app SDK
- [ ] Multi-language support

## 📝 Code Quality Tips

### When Adding Features:
1. ✅ Follow existing naming conventions
2. ✅ Add PHPDoc comments to all methods
3. ✅ Use prepared statements for SQL
4. ✅ Return consistent response formats
5. ✅ Handle errors gracefully
6. ✅ Keep methods focused (single responsibility)

### Example Well-Written Method:
```php
/**
 * Get referrals by status category
 * 
 * @param string $category Status category (active, pending, rejected)
 * @return array Array of referral objects
 */
public function get_referrals_by_category($category) {
    $sql = $this->wpdb->prepare(
        "SELECT r.* FROM " . CUSTOM_API_TABLE_REFERRALS . " r
        INNER JOIN " . CUSTOM_API_TABLE_REFERRALS_STATUS . " s 
        ON r.status_id = s.id 
        WHERE s.category = %s 
        ORDER BY r.id DESC",
        $category
    );
    
    return $this->wpdb->get_results($sql);
}
```

## 🔧 Troubleshooting

**Issue:** API returns 403 Forbidden
- **Solution:** Check `x-api-key` header is properly encrypted

**Issue:** Database queries failing
- **Solution:** Verify table names in `config/config.php` match your database

**Issue:** Emails not sending
- **Solution:** Check WP Mail SMTP plugin is configured

**Issue:** Routes not registering
- **Solution:** Clear WordPress permalink cache (Settings > Permalinks > Save)

## 📞 Support

Refer to the full `README.md` for detailed documentation.

---
**Plugin Version:** 2.0.0  
**Last Updated:** November 2025  
**Refactored:** For scalability and maintainability
