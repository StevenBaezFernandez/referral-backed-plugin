# Software Referral Workflow - Feature Documentation

## Overview

Extended backend functionality to support the hiring and onboarding workflow for Software referrals, including feedback management, job preference handling, and re-evaluation workflows.

## Database Changes

### New Column: `feedback_comment`

Added to two tables:
- `wp_referrals` - Stores current feedback for the referral
- `wp_changelog_status_referral` - Stores feedback for each status change

**Type:** `TEXT`  
**Nullable:** Yes  
**Purpose:** Store interviewer/evaluator feedback during status transitions

### Migration

The migration runs automatically on plugin load. It:
1. Checks if columns exist
2. Adds columns if missing
3. Logs completion to WordPress options

**Manual Migration:**
If needed, you can manually run the migration by calling:
```php
do_action('plugins_loaded');
```

## New API Endpoints

### 1. Get Full Changelog with Feedback
```
GET /wp-json/c-api/v1/referral-changelog/{id}
```

**Parameters:**
- `id` (required): Referral ID
- `limit` (optional): Number of records (default: 10)

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "record_id": 123,
      "old_status_name": "Pending Review",
      "new_status_name": "Rejected",
      "new_status_category": "rejected",
      "performer": "john.doe",
      "date": "2025-11-26 10:30:00",
      "feedback_comment": "Candidate lacks required experience in React"
    }
  ],
  "count": 1
}
```

### 2. Add Feedback Comment
```
POST /wp-json/c-api/v1/add-feedback/{id}
```

**Body:**
```json
{
  "feedback_comment": "Strong technical skills but needs improvement in communication"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Feedback comment added successfully"
}
```

### 3. Get Referrals by Job Preference
```
GET /wp-json/c-api/v1/referrals-by-job/{job_preference}
```

**Example:**
```
GET /wp-json/c-api/v1/referrals-by-job/Software%20Developer
```

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "job_preference": "Software Developer",
  "data": [...],
  "count": 5
}
```

### 4. Get Referrals for Re-evaluation
```
GET /wp-json/c-api/v1/referrals-reevaluation
```

Returns all rejected referrals that have feedback and can be reconsidered.

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 123,
      "name": "John",
      "last_name": "Doe",
      "job_preference": "Software Developer",
      "status_id": 3,
      "feedback_comment": "Technical skills insufficient at time of review",
      "last_review_date": "2025-10-15 14:30:00"
    }
  ],
  "count": 3
}
```

### 5. Update Job Preference
```
PUT /wp-json/c-api/v1/update-job-preference/{id}
```

**Body:**
```json
{
  "job_preference": "Software"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Job preference updated successfully"
}
```

### 6. Update Status with Feedback (Enhanced)
```
PUT /wp-json/c-api/v1/set-status/{id}/{status_id}
```

**Body (Enhanced):**
```json
{
  "updated_by": "interviewer@company.com",
  "updated_at": "2025-11-26 15:30:00",
  "feedback_comment": "Excellent problem-solving skills. Strong cultural fit. Recommended for hire."
}
```

**Response:**
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "success": true,
    "old_status": 2,
    "new_status": 3,
    "feedback_comment": "Excellent problem-solving skills...",
    "changelog_created": true
  }
}
```

## Updated Database Methods

### `Custom_API_Database` Class

#### New Methods:

1. **`get_full_changelog($referral_id, $limit = 10)`**
   - Returns complete changelog with feedback
   - Includes status categories

2. **`add_feedback_comment($referral_id, $feedback_comment)`**
   - Adds/updates feedback for a referral
   - Sanitizes input

3. **`get_referrals_by_job_preference($job_preference)`**
   - Filters referrals by job preference
   - Includes feedback and status info

4. **`get_referrals_for_reevaluation()`**
   - Returns rejected referrals with feedback
   - Sorted by last review date

5. **`update_job_preference($referral_id, $job_preference)`**
   - Updates job preference for a referral

#### Modified Methods:

1. **`update_referral_status()`** - Now accepts `$feedback_comment` parameter
2. **`get_referral_changelog()`** - Now includes `feedback_comment` in results

## Workflow Examples

### Rejection with Feedback
```javascript
// Reject a referral with detailed feedback
fetch('/wp-json/c-api/v1/set-status/123/5', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
    'x-api-key': encryptedKey
  },
  body: JSON.stringify({
    updated_by: 'hr.manager@company.com',
    feedback_comment: 'Technical assessment score: 65/100. Areas for improvement: Algorithm optimization, System design patterns.'
  })
});
```

### Re-evaluation Process
```javascript
// 1. Get candidates for re-evaluation
const candidates = await fetch('/wp-json/c-api/v1/referrals-reevaluation')
  .then(r => r.json());

// 2. Update job preference if needed
await fetch('/wp-json/c-api/v1/update-job-preference/123', {
  method: 'PUT',
  body: JSON.stringify({
    job_preference: 'Junior Software Developer'
  })
});

// 3. Change status back to under review
await fetch('/wp-json/c-api/v1/set-status/123/2', {
  method: 'PUT',
  body: JSON.stringify({
    updated_by: 'senior.tech@company.com',
    feedback_comment: 'Re-evaluating after 6 months. Candidate has completed online courses and built portfolio projects.'
  })
});
```

### View Complete History
```javascript
// Get full changelog with all feedback
fetch('/wp-json/c-api/v1/referral-changelog/123?limit=20')
  .then(r => r.json())
  .then(data => {
    data.data.forEach(entry => {
      console.log(`${entry.date}: ${entry.old_status_name} → ${entry.new_status_name}`);
      if (entry.feedback_comment) {
        console.log(`Feedback: ${entry.feedback_comment}`);
      }
    });
  });
```

## Best Practices

### When to Add Feedback

**Always provide feedback when:**
- ✅ Rejecting a candidate
- ✅ Requiring re-evaluation
- ✅ Moving to "On Hold" status
- ✅ Escalating to higher management
- ✅ Requesting additional interviews

**Optional but recommended:**
- Moving forward in pipeline
- Accepting/hiring
- Marking as "In Progress"

### Feedback Guidelines

**Good Feedback:**
```
"Strong JavaScript fundamentals. Excellent work on take-home assignment. 
Recommended for final round with senior architect."
```

**Bad Feedback:**
```
"Not a good fit."
```

### Security Notes

- All feedback is sanitized using `sanitize_textarea_field()`
- Feedback is stored in plaintext (not encrypted)
- Access controlled by API authentication
- Consider GDPR/privacy implications when storing feedback

## Testing

### Test the Migration
```sql
-- Check if columns were added
DESCRIBE wp_referrals;
DESCRIBE wp_changelog_status_referral;

-- Should see feedback_comment column in both tables
```

### Test Feedback Storage
```bash
# Add feedback via API
curl -X POST "http://your-site.com/wp-json/c-api/v1/add-feedback/123" \
  -H "Content-Type: application/json" \
  -H "x-api-key: YOUR_KEY" \
  -d '{"feedback_comment":"Test feedback"}'

# Verify in database
SELECT id, name, feedback_comment FROM wp_referrals WHERE id = 123;
```

## Future Enhancements

Potential additions for Software referral workflow:

1. **Technical Assessment Scores**
   - Add columns for coding test scores
   - Track assessment completion dates

2. **Interview Scheduling**
   - Integration with calendar systems
   - Automated reminders

3. **Skills Matrix**
   - Tag referrals with technical skills
   - Match against job requirements

4. **Automated Re-evaluation**
   - Periodic checks on rejected candidates
   - Skill development tracking

5. **Feedback Templates**
   - Pre-defined feedback categories
   - Quick selection for common scenarios

6. **Approval Workflows**
   - Multi-stage approval process
   - Role-based access to feedback

## Troubleshooting

**Issue:** Feedback not saving  
**Solution:** Check that migration ran successfully, verify column exists in database

**Issue:** Cannot see feedback in frontend  
**Solution:** Ensure you're using the updated API endpoints that return feedback_comment

**Issue:** Migration didn't run  
**Solution:** Manually trigger by going to WordPress admin and refreshing any page

**Issue:** Feedback appears in wrong encoding  
**Solution:** Ensure database charset is UTF-8, check `sanitize_textarea_field()` is being used

## Version History

**v2.1.0** - Software Referral Workflow Extension
- Added feedback_comment field to database
- Created 5 new API endpoints
- Enhanced status update with feedback
- Added re-evaluation workflow support
- Migration system for database changes
