<?php
/**
 * Database Optimization Migration
 * 
 * Adds indexes and optimizations for Software referral workflow
 * 
 * @package Custom_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add performance indexes for referral queries
 */
function custom_api_add_performance_indexes() {
    global $wpdb;
    
    // Check if migration already ran
    if (get_option('custom_api_optimization_migration_completed')) {
        return;
    }
    
    $referrals_table = CUSTOM_API_TABLE_REFERRALS;
    $changelog_table = CUSTOM_API_TABLE_CHANGELOG;
    
    // Get existing indexes
    $existing_indexes = $wpdb->get_results("SHOW INDEX FROM {$referrals_table}");
    $index_names = array_column($existing_indexes, 'Key_name');
    
    // Add index on job_preference for filtering Software roles
    if (!in_array('idx_job_preference', $index_names)) {
        $wpdb->query("ALTER TABLE {$referrals_table} ADD INDEX idx_job_preference (job_preference(50))");
        error_log('Added index idx_job_preference to ' . $referrals_table);
    }
    
    // Add index on status_id for JOIN optimization
    if (!in_array('idx_status_id', $index_names)) {
        $wpdb->query("ALTER TABLE {$referrals_table} ADD INDEX idx_status_id (status_id)");
        error_log('Added index idx_status_id to ' . $referrals_table);
    }
    
    // Add index on latest_status_review_date for re-evaluation queries
    if (!in_array('idx_latest_status_review_date', $index_names)) {
        $wpdb->query("ALTER TABLE {$referrals_table} ADD INDEX idx_latest_status_review_date (latest_status_review_date)");
        error_log('Added index idx_latest_status_review_date to ' . $referrals_table);
    }
    
    // Add composite index for filtering rejected referrals with feedback
    if (!in_array('idx_status_feedback', $index_names)) {
        $wpdb->query("ALTER TABLE {$referrals_table} ADD INDEX idx_status_feedback (status_id, feedback_comment(100))");
        error_log('Added index idx_status_feedback to ' . $referrals_table);
    }
    
    // Optimize changelog table
    $changelog_indexes = $wpdb->get_results("SHOW INDEX FROM {$changelog_table}");
    $changelog_index_names = array_column($changelog_indexes, 'Key_name');
    
    // Add index on record_id for changelog lookups
    if (!in_array('idx_record_id', $changelog_index_names)) {
        $wpdb->query("ALTER TABLE {$changelog_table} ADD INDEX idx_record_id (record_id)");
        error_log('Added index idx_record_id to ' . $changelog_table);
    }
    
    // Add index on new_status for filtering by status transitions
    if (!in_array('idx_new_status', $changelog_index_names)) {
        $wpdb->query("ALTER TABLE {$changelog_table} ADD INDEX idx_new_status (new_status)");
        error_log('Added index idx_new_status to ' . $changelog_table);
    }
    
    // Add composite index for date-based queries
    if (!in_array('idx_record_date', $changelog_index_names)) {
        $wpdb->query("ALTER TABLE {$changelog_table} ADD INDEX idx_record_date (record_id, date)");
        error_log('Added index idx_record_date to ' . $changelog_table);
    }
    
    // Mark migration as complete
    update_option('custom_api_optimization_migration_completed', true);
    error_log('Custom API optimization migration completed successfully');
}

// Run migration on plugin load
add_action('plugins_loaded', 'custom_api_add_performance_indexes');

/**
 * Add soft delete column (optional - uncomment if needed)
 */
function custom_api_add_soft_delete_column() {
    global $wpdb;
    
    if (get_option('custom_api_soft_delete_migration_completed')) {
        return;
    }
    
    $referrals_table = CUSTOM_API_TABLE_REFERRALS;
    
    // Check if column exists
    $column_exists = $wpdb->get_results(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = '{$referrals_table}' 
        AND COLUMN_NAME = 'deleted_at'"
    );
    
    if (empty($column_exists)) {
        $wpdb->query("ALTER TABLE {$referrals_table} ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL");
        $wpdb->query("ALTER TABLE {$referrals_table} ADD INDEX idx_deleted_at (deleted_at)");
        error_log('Added soft delete column to ' . $referrals_table);
    }
    
    update_option('custom_api_soft_delete_migration_completed', true);
}

// Uncomment to enable soft delete:
// add_action('plugins_loaded', 'custom_api_add_soft_delete_column', 11);
