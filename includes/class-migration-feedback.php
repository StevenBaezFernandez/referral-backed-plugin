<?php
/**
 * Database Migration - Add Feedback Comment Field
 * 
 * Run this script once to add the feedback_comment column to referral tables
 * 
 * @package Custom_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add feedback comment column to database
 */
function custom_api_add_feedback_comment_column() {
    global $wpdb;
    
    $table_referrals = $wpdb->prefix . 'referrals';
    $table_changelog = $wpdb->prefix . 'changelog_status_referral';
    
    // Check if column already exists in referrals table
    $column_exists_referrals = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = %s 
            AND TABLE_NAME = %s 
            AND COLUMN_NAME = 'feedback_comment'",
            DB_NAME,
            $table_referrals
        )
    );
    
    // Add column to referrals table if it doesn't exist
    if (empty($column_exists_referrals)) {
        $sql_referrals = "ALTER TABLE `{$table_referrals}` 
                         ADD COLUMN `feedback_comment` TEXT NULL 
                         COMMENT 'Feedback or comments about the referral status'";
        $wpdb->query($sql_referrals);
        
        if ($wpdb->last_error) {
            return [
                'success' => false,
                'message' => 'Error adding feedback_comment to referrals table',
                'error' => $wpdb->last_error
            ];
        }
    }
    
    // Check if column already exists in changelog table
    $column_exists_changelog = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = %s 
            AND TABLE_NAME = %s 
            AND COLUMN_NAME = 'feedback_comment'",
            DB_NAME,
            $table_changelog
        )
    );
    
    // Add column to changelog table if it doesn't exist
    if (empty($column_exists_changelog)) {
        $sql_changelog = "ALTER TABLE `{$table_changelog}` 
                         ADD COLUMN `feedback_comment` TEXT NULL 
                         COMMENT 'Feedback provided during status change'";
        $wpdb->query($sql_changelog);
        
        if ($wpdb->last_error) {
            return [
                'success' => false,
                'message' => 'Error adding feedback_comment to changelog table',
                'error' => $wpdb->last_error
            ];
        }
    }
    
    return [
        'success' => true,
        'message' => 'Feedback comment columns added successfully',
        'referrals_added' => empty($column_exists_referrals),
        'changelog_added' => empty($column_exists_changelog)
    ];
}

/**
 * Migration activation hook
 */
function custom_api_run_feedback_migration() {
    $result = custom_api_add_feedback_comment_column();
    
    if ($result['success']) {
        update_option('custom_api_feedback_migration_completed', true);
        update_option('custom_api_feedback_migration_date', current_time('mysql'));
    }
    
    return $result;
}

// Auto-run migration on plugin load (runs once)
add_action('plugins_loaded', function() {
    if (!get_option('custom_api_feedback_migration_completed')) {
        $result = custom_api_run_feedback_migration();
        
        // Log the migration result
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('Custom API Feedback Migration: ' . json_encode($result));
        }
    }
});
