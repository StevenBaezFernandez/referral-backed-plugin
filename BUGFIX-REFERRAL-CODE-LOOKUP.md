# Referral Code Lookup - Bug Fix

## Issue Summary
Referral code lookup was returning "not found" error even when the code existed in the database.

## Root Cause
1. **INNER JOINs failing silently**: The query used `INNER JOIN` for all related tables, which meant if any related record was missing (referrer, status), the entire result was excluded.
2. **Query started from wrong table**: Started from `wp_referrals` and joined to `wp_referral_code`, when it should start from the code table.
3. **Poor error handling**: Endpoint didn't distinguish between "database error" and "no results found".

## Solution Applied

### 1. Fixed Database Query (`class-database.php`)

**Before:**
```sql
FROM wp_referrals o
INNER JOIN wp_referrer ref ON ref.id = o.referrer_id        -- Fails if no referrer
INNER JOIN wp_referrals_status st ON st.id = o.status_id    -- Fails if no status
INNER JOIN wp_referral_code cd ON cd.id = o.referral_code   -- Fails if no code
WHERE cd.alphanumeric_code = %s
```

**After:**
```sql
FROM wp_referral_code cd                                     -- Start from code table
INNER JOIN wp_referrals o ON o.referral_code = cd.id        -- Only this is required
LEFT JOIN wp_referrer ref ON ref.id = o.referrer_id         -- Optional referrer data
LEFT JOIN wp_referrals_status st ON st.id = o.status_id     -- Optional status data
WHERE cd.alphanumeric_code = %s
```

**Changes:**
- Start query from `wp_referral_code` table (the search key)
- Only `INNER JOIN` to `wp_referrals` (required relationship)
- Use `LEFT JOIN` for referrer and status (optional data)
- Added more fields to response (phone, email, job preference, feedback, etc.)
- Automatically includes changelog for each referral

### 2. Improved Endpoint Error Handling (`class-referral-endpoints.php`)

**Before:**
```php
if (!$result) {
    return 500 error "An error occurred...";
}
```

**After:**
```php
if ($result === false) {
    return 500 error "Database error occurred";
}
if (empty($result)) {
    return 404 error "No referrals found with code: RAF123";
}
return 200 with data and count;
```

**Changes:**
- Distinguish between database error (`false`) and no results (empty array)
- Return proper 404 status when code not found
- Include referral code in error message for debugging
- Add `count` field to successful response

## Testing

### Test Case 1: Valid Referral Code
```bash
curl http://localhost:10006/wp-json/c-api/v1/referrals-by-referral-id/RAF123
```

**Expected Response (200):**
```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "referral_id": 1,
      "referral_name": "John",
      "referral_last_name": "Doe",
      "referral_phone_number": "555-1234",
      "referral_email": "john@example.com",
      "job_preference": "Software Developer",
      "status_name": "Under Review",
      "status_category": "pending",
      "referral_code": "RAF123",
      "change_log": [...]
    }
  ],
  "count": 1
}
```

### Test Case 2: Invalid Referral Code
```bash
curl http://localhost:10006/wp-json/c-api/v1/referrals-by-referral-id/INVALID999
```

**Expected Response (404):**
```json
{
  "status": false,
  "message": "No referrals found with code: INVALID999"
}
```

### Test Case 3: Referral with Missing Referrer Data
Previously would fail with INNER JOIN, now returns referral with NULL referrer fields.

## Verification Steps

1. **Check if code exists in database:**
   ```sql
   SELECT alphanumeric_code FROM wp_referral_code WHERE alphanumeric_code = 'RAF123';
   ```

2. **Check if referral is linked to code:**
   ```sql
   SELECT r.id, r.name, r.referral_code, c.alphanumeric_code
   FROM wp_referrals r
   LEFT JOIN wp_referral_code c ON c.id = r.referral_code
   WHERE c.alphanumeric_code = 'RAF123';
   ```

3. **Test the endpoint:**
   - Use the actual referral code from your database
   - Check browser DevTools Network tab
   - Verify 200 response with data

## Files Modified

1. **`includes/class-database.php`**
   - Method: `get_referrals_by_code()`
   - Lines: ~152-180
   - Changes: Rewrote SQL query, added changelog, expanded fields

2. **`api/class-referral-endpoints.php`**
   - Method: `get_referrals_by_code()`
   - Lines: ~205-225
   - Changes: Added proper error handling and count field

## Additional Benefits

The fix also:
- Returns complete referral data (not just basic fields)
- Includes changelog history automatically
- Works even if referrer or status data is missing
- Better debugging with specific error messages
- Consistent response format with other endpoints

## Migration Notes

No database changes required - this is a code-only fix.

## Related Issues

This same pattern should be reviewed in:
- `get_referrals_by_employee_id()` - Also uses INNER JOINs
- Any other methods that might fail silently with INNER JOINs

Consider auditing all database queries for similar issues.
