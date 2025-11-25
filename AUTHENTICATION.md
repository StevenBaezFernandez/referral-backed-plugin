# Authentication Setup Guide

## Current Status

The plugin is currently running in **development mode** with authentication **disabled** because the API keys are not configured.

## To Enable Authentication

Add these lines to your `wp-config.php` file (before the "stop editing" comment):

```php
/* Custom API Authentication Keys */
define('WP_API_KEY', 'your-secret-api-key-here');
define('WP_ENCRYPTION_KEY', 'your-32-character-encryption-key');
```

### Generating Keys

**API Key:**
```php
// Generate a random API key
echo bin2hex(random_bytes(32));
```

**Encryption Key:**
```php
// Must be exactly 32 characters for AES-256-CBC
echo bin2hex(random_bytes(16)); // This generates 32 hex characters
```

## How Authentication Works

1. **Frontend** encrypts the API key using AES-256-CBC
2. **Sends** encrypted key in `x-api-key` header
3. **Backend** decrypts and validates the key
4. **Allows/Denies** access based on validation

## Current Behavior (No Keys Configured)

✅ All API requests are **allowed** (backward compatibility mode)  
⚠️  No authentication is performed  
📝 Suitable for **development** and **local testing**

## Production Deployment

For production, **you must**:

1. Set `WP_API_KEY` and `WP_ENCRYPTION_KEY` in `wp-config.php`
2. Update your frontend to use the same keys
3. Test that authentication works
4. Remove development/debug flags

## Checking Current Status

Add this to any PHP file to see the current configuration:

```php
echo "API Key Configured: " . (defined('CUSTOM_API_API_KEY') && !empty(CUSTOM_API_API_KEY) ? 'YES' : 'NO') . "\n";
echo "Encryption Key Configured: " . (defined('CUSTOM_API_ENCRYPTION_KEY') && !empty(CUSTOM_API_ENCRYPTION_KEY) ? 'YES' : 'NO') . "\n";
echo "Authentication Mode: " . (empty(CUSTOM_API_API_KEY) ? 'DISABLED (Dev Mode)' : 'ENABLED') . "\n";
```

## Security Recommendations

- ✅ Use strong, random keys (32+ characters)
- ✅ Never commit keys to version control
- ✅ Use different keys for dev/staging/production
- ✅ Rotate keys periodically
- ✅ Use HTTPS in production
- ✅ Enable authentication before going live

## Troubleshooting

**Issue:** API returns "Invalid API key"  
**Solution:** Check that frontend and backend use same encryption key

**Issue:** API returns "API key is required"  
**Solution:** Ensure `x-api-key` header is sent with all requests

**Issue:** All requests return 403  
**Solution:** Keys might not be configured - check `wp-config.php`

**Issue:** Works locally but not in production  
**Solution:** Verify keys are set in production `wp-config.php`
