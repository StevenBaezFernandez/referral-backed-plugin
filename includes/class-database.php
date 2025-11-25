<?php
/**
 * Database Handler Class
 * 
 * Handles all database operations for referrals
 * 
 * @package Custom_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Custom_API_Database {
    
    /**
     * WordPress database object
     * 
     * @var wpdb
     */
    private $wpdb;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Get all referrals with complete information
     * 
     * @return array
     */
    public function get_all_referrals() {
        $sql = "SELECT 
            o.id            AS referral_id,
            o.NAME          AS referral_name,
            o.last_name     AS referral_last_name,
            o.phone_number  AS referral_phone_number,
            o.email         AS referral_email,
            o.experiencia   AS referral_experience,
            o.english_level AS referral_english_level,
            o.job_preference AS job_preference,
            referrer_source AS referral_referrer_source,
            referred_date   AS referral_referred_date,
            ref.id          AS referrer_id,
            o.internal_id   AS internal_id,
            o.referrer_name AS referrer_name,
            o.incoming_source AS incoming_source,
            o.referrer_last_name   AS referrer_last_name,
            o.referrer_email AS referrer_email,
            o.referrer_phone_number AS referrer_phone_number,
            ref.email       AS referrer_email_2,
            ref.newtech_id  AS referrer_newtech_id,
            st.id           AS status_id,
            st.NAME         AS status_name,
            st.parent       AS status_parent,
            st.category     AS status_category,
            o.month         AS current_month,
            o.status_month  AS status_month,
            latest_status_review_by,
            latest_status_review_date,
            cd.alphanumeric_code AS referral_code
        FROM   " . CUSTOM_API_TABLE_REFERRALS . " o
        LEFT JOIN " . CUSTOM_API_TABLE_REFERRER . " ref
                ON ref.id = o.referrer_id
        LEFT JOIN " . CUSTOM_API_TABLE_REFERRALS_STATUS . " st
                ON st.id = o.status_id
        LEFT JOIN " . CUSTOM_API_TABLE_REFERRAL_CODE . " cd
                ON cd.id = o.referral_code 
        ORDER BY o.id DESC";

        $result = $this->wpdb->get_results($sql);

        // Add change log for each referral
        foreach ($result as $key => $value) {
            $result[$key]->change_log = $this->get_referral_changelog($value->referral_id);
        }

        return $result;
    }

    /**
     * Get referral changelog
     * 
     * @param int $referral_id
     * @return array
     */
    public function get_referral_changelog($referral_id) {
        $sql = $this->wpdb->prepare(
            "SELECT c.id, c.record_id, s_old.name AS old_status_name, s_new.name AS new_status_name, 
            c.old_status, c.new_status, c.performer, c.date
            FROM " . CUSTOM_API_TABLE_CHANGELOG . " c
            JOIN " . CUSTOM_API_TABLE_REFERRALS_STATUS . " s_old ON c.old_status = s_old.id
            JOIN " . CUSTOM_API_TABLE_REFERRALS_STATUS . " s_new ON c.new_status = s_new.id
            WHERE c.record_id = %d
            ORDER BY c.date DESC
            LIMIT 4",
            $referral_id
        );

        return $this->wpdb->get_results($sql);
    }

    /**
     * Get referrals by employee ID
     * 
     * @param string $internal_id
     * @return array
     */
    public function get_referrals_by_employee_id($internal_id) {
        $isNTG = strpos(strtolower($internal_id), 'ntg') !== false;
        $isNT = strpos(strtolower($internal_id), 'nt') !== false;

        if ($isNTG) {
            $isNT = false;
        }

        if (stripos($internal_id, "ntg") === 0) {
            $internal_id = "NG" . substr($internal_id, 3);
        }

        $sql = $this->wpdb->prepare(
            "SELECT 
                o.id                    AS referral_id,
                o.NAME                  AS referral_name,
                o.last_name             AS referral_last_name,
                referred_date           AS referral_referred_date,
                st.referrer_label       AS status_name,
                st.category             AS status_category
            FROM   " . CUSTOM_API_TABLE_REFERRALS . " o
            INNER JOIN " . CUSTOM_API_TABLE_REFERRER . " ref
                    ON ref.id = o.referrer_id
            INNER JOIN " . CUSTOM_API_TABLE_REFERRALS_STATUS . " st
                    ON st.id = o.status_id
            WHERE o.internal_id = %s",
            $internal_id
        );

        if ($isNTG) {
            $sql .= " AND referrer_source = 'NTG'";
        }

        if ($isNT) {
            $sql .= " AND referrer_source = 'NT'";
        }

        return $this->wpdb->get_results($sql);
    }

    /**
     * Get referrals by referral code
     * 
     * @param string $referral_code
     * @return array
     */
    public function get_referrals_by_code($referral_code) {
        $sql = $this->wpdb->prepare(
            "SELECT 
                o.id                    AS referral_id,
                o.NAME                  AS referral_name,
                o.last_name             AS referral_last_name,
                referred_date           AS referral_referred_date,
                st.referrer_label       AS status_name,
                st.category             AS status_category
            FROM   " . CUSTOM_API_TABLE_REFERRALS . " o
            INNER JOIN " . CUSTOM_API_TABLE_REFERRER . " ref
                    ON ref.id = o.referrer_id
            INNER JOIN " . CUSTOM_API_TABLE_REFERRALS_STATUS . " st
                    ON st.id = o.status_id
            INNER JOIN " . CUSTOM_API_TABLE_REFERRAL_CODE . " cd
                    ON cd.id = o.referral_code
            WHERE cd.alphanumeric_code = %s",
            $referral_code
        );

        return $this->wpdb->get_results($sql);
    }

    /**
     * Create a new referral
     * 
     * @param array $data
     * @return array Response with status and data
     */
    public function create_referral($data) {
        // Validate employee exists
        $internal_id = $data['referrer_source'] . $data['newtech_id'];
        
        if (stripos($internal_id, "ntg") === 0) {
            $internal_id = "NG" . substr($internal_id, 3);
        }

        if ($data['referrer_source'] !== 'non-employee') {
            $employee_query = $this->wpdb->prepare(
                "SELECT * FROM " . CUSTOM_API_TABLE_INTERNAL . " WHERE id = %s",
                $internal_id
            );
            $employee_result = $this->wpdb->get_results($employee_query);

            if (count($employee_result) === 0) {
                return [
                    'success' => false,
                    'message' => 'no_employee_matches',
                    'error' => '404'
                ];
            }
        }

        // Generate referral code
        $r_code_data = $this->generate_referral_code();
        
        if (!$r_code_data['success']) {
            return $r_code_data;
        }

        // Check if referrer exists, create if not
        $referrer_result = $this->get_or_create_referrer($data);
        
        if (!$referrer_result['success']) {
            return $referrer_result;
        }

        // Insert referral
        $insert_result = $this->wpdb->query(
            $this->wpdb->prepare(
                "INSERT INTO " . CUSTOM_API_TABLE_REFERRALS . "
                (`name`, `last_name`, `phone_number`, `email`, `experiencia`, 
                 `english_level`, `job_preference`, `referrer_source`, `referrer_id`, 
                 `internal_id`, `referrer_email`, `referrer_phone_number`, 
                 `referrer_name`, `referrer_last_name`, `status_id`, `referral_code`, `month`)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, MONTH(NOW()))",
                addslashes($data['referral_name']),
                addslashes($data['referral_last_name']),
                addslashes($data['referral_phone_number']),
                addslashes($data['referral_email']),
                addslashes($data['experiencia']),
                addslashes($data['english_level']),
                addslashes($data['job_preference']),
                addslashes($data['referrer_source']),
                addslashes($internal_id),
                addslashes($internal_id),
                addslashes($data['referrer_email']),
                addslashes($data['referrer_phone_number']),
                addslashes($data['referrer_name']),
                addslashes($data['referrer_last_name']),
                0,
                $r_code_data['code_id']
            )
        );

        if (!$insert_result) {
            return [
                'success' => false,
                'message' => 'Database insert failed',
                'error' => $this->wpdb->last_error
            ];
        }

        return [
            'success' => true,
            'referral_code' => $r_code_data['code'],
            'referral_id' => $this->wpdb->insert_id
        ];
    }

    /**
     * Generate a unique referral code
     * 
     * @return array
     */
    private function generate_referral_code() {
        $sql = "INSERT INTO " . CUSTOM_API_TABLE_REFERRAL_CODE . " (alphanumeric_code)
                VALUES (CONCAT('RAF', RAND()))";
        
        $this->wpdb->query($sql);
        $code_id = $this->wpdb->insert_id;

        $code_sql = $this->wpdb->prepare(
            "SELECT alphanumeric_code FROM " . CUSTOM_API_TABLE_REFERRAL_CODE . " WHERE id = %d",
            $code_id
        );
        
        $code_result = $this->wpdb->get_results($code_sql);

        if (!$code_result) {
            return [
                'success' => false,
                'message' => 'Failed to generate referral code'
            ];
        }

        return [
            'success' => true,
            'code' => $code_result[0]->alphanumeric_code,
            'code_id' => $code_id
        ];
    }

    /**
     * Get or create referrer
     * 
     * @param array $data
     * @return array
     */
    private function get_or_create_referrer($data) {
        $referrer_sql = $this->wpdb->prepare(
            "SELECT * FROM " . CUSTOM_API_TABLE_REFERRER . " WHERE id = %d",
            $data['newtech_id']
        );
        
        $referrer_result = $this->wpdb->get_results($referrer_sql);

        if ($referrer_result == null) {
            $insert_referrer = $this->wpdb->query(
                $this->wpdb->prepare(
                    "INSERT INTO " . CUSTOM_API_TABLE_REFERRER . " 
                    (id, name, last_name, email, newtech_id) 
                    VALUES (%s, %s, %s, %s, %s)",
                    $data['newtech_id'],
                    $data['referral_name'],
                    $data['referral_last_name'],
                    $data['referral_email'],
                    $data['newtech_id']
                )
            );

            if (!$insert_referrer) {
                return [
                    'success' => false,
                    'message' => 'Failed to create referrer'
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Update referral status
     * 
     * @param int $referral_id
     * @param int $status_id
     * @param string $updated_by
     * @param string $updated_at
     * @return array
     */
    public function update_referral_status($referral_id, $status_id, $updated_by, $updated_at) {
        // Get current status
        $current_status = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT status_id FROM " . CUSTOM_API_TABLE_REFERRALS . " WHERE id = %d",
                $referral_id
            )
        );

        if (!$current_status) {
            return [
                'success' => false,
                'message' => 'Referral not found'
            ];
        }

        $old_status = $current_status[0]->status_id;

        // Update status
        $result = $this->wpdb->update(
            CUSTOM_API_TABLE_REFERRALS,
            ['status_id' => $status_id],
            ['id' => $referral_id]
        );

        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $this->wpdb->last_error
            ];
        }

        // Update review metadata
        $this->wpdb->update(
            CUSTOM_API_TABLE_REFERRALS,
            [
                'latest_status_review_date' => $updated_at,
                'latest_status_review_by' => $updated_by,
                'status_month' => 0
            ],
            ['id' => $referral_id]
        );

        // Add to changelog
        $history = $this->wpdb->query(
            $this->wpdb->prepare(
                "INSERT INTO " . CUSTOM_API_TABLE_CHANGELOG . " 
                (record_id, old_status, new_status, performer) 
                VALUES (%d, %d, %d, %s)",
                $referral_id,
                $old_status,
                $status_id,
                $updated_by
            )
        );

        return [
            'success' => true,
            'old_status' => $old_status,
            'new_status' => $status_id,
            'changelog_created' => (bool) $history
        ];
    }

    /**
     * Get all statuses
     * 
     * @return array
     */
    public function get_all_statuses() {
        $sql = "SELECT * FROM " . CUSTOM_API_TABLE_REFERRALS_STATUS;
        return $this->wpdb->get_results($sql);
    }

    /**
     * Get referrer by ID
     * 
     * @param int $referrer_id
     * @return object|null
     */
    public function get_referrer_by_id($referrer_id) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM " . CUSTOM_API_TABLE_REFERRER . " WHERE id = %d",
            $referrer_id
        );
        
        $result = $this->wpdb->get_results($sql);
        return $result ? $result[0] : null;
    }

    /**
     * Get all referrers
     * 
     * @return array
     */
    public function get_all_referrers() {
        $sql = "SELECT * FROM " . CUSTOM_API_TABLE_REFERRALS . " 
                INNER JOIN " . CUSTOM_API_TABLE_REFERRER . " 
                ON " . CUSTOM_API_TABLE_REFERRER . ".id = " . CUSTOM_API_TABLE_REFERRALS . ".referrer_id 
                GROUP BY " . CUSTOM_API_TABLE_REFERRALS . ".referrer_id";
        
        return $this->wpdb->get_results($sql);
    }

    /**
     * Get referrers with their referrals
     * 
     * @return array
     */
    public function get_referrers_with_referrals() {
        $referrers = $this->get_all_referrers();

        foreach ($referrers as $key => $referrer) {
            $sql = $this->wpdb->prepare(
                "SELECT * FROM " . CUSTOM_API_TABLE_REFERRALS . " WHERE referrer_id = %s",
                $referrer->referrer_id
            );
            
            $referrers[$key]->referrals = $this->wpdb->get_results($sql);
        }

        return $referrers;
    }

    /**
     * Get total count of referrals
     * 
     * @return int
     */
    public function get_total_referrals() {
        $sql = "SELECT COUNT(*) AS total FROM " . CUSTOM_API_TABLE_REFERRALS;
        $result = $this->wpdb->get_results($sql);
        return $result[0]->total;
    }

    /**
     * Get total internal referrals (from employees)
     * 
     * @return int
     */
    public function get_total_internal_referrals() {
        $sql = "SELECT COUNT(*) AS total FROM " . CUSTOM_API_TABLE_REFERRALS . " 
                WHERE referrer_source != 'non-employee'";
        $result = $this->wpdb->get_results($sql);
        return $result[0]->total;
    }

    /**
     * Get total external referrals (non-employees)
     * 
     * @return int
     */
    public function get_total_external_referrals() {
        $sql = "SELECT COUNT(*) AS total FROM " . CUSTOM_API_TABLE_REFERRALS . " 
                WHERE referrer_source = 'non-employee'";
        $result = $this->wpdb->get_results($sql);
        return $result[0]->total;
    }

    /**
     * Get total rejected referrals
     * 
     * @return int
     */
    public function get_total_rejected_referrals() {
        $sql = "SELECT COUNT(*) AS total FROM " . CUSTOM_API_TABLE_REFERRALS . " r 
                INNER JOIN " . CUSTOM_API_TABLE_REFERRALS_STATUS . " s ON r.status_id = s.id 
                WHERE s.category = 'rejected'";
        $result = $this->wpdb->get_results($sql);
        return $result[0]->total;
    }

    /**
     * Get monthly status review counts
     * 
     * @return array
     */
    public function get_status_monthly() {
        $sql_reviewed = "SELECT COUNT(*) as total FROM " . CUSTOM_API_TABLE_REFERRALS . " 
                        WHERE status_month = -1";
        $result_reviewed = $this->wpdb->get_results($sql_reviewed);

        $sql_not_reviewed = "SELECT COUNT(*) as total FROM " . CUSTOM_API_TABLE_REFERRALS . " 
                            WHERE status_month = 0";
        $result_not_reviewed = $this->wpdb->get_results($sql_not_reviewed);

        return [
            'review' => $result_reviewed[0]->total,
            'no_review' => $result_not_reviewed[0]->total
        ];
    }

    /**
     * Get referral source statistics
     * 
     * @param string|null $from_date
     * @param string|null $to_date
     * @return array
     */
    public function get_referral_source_stats($from_date = null, $to_date = null) {
        $sql = "SELECT COUNT(*) as total, referrer_source 
                FROM " . CUSTOM_API_TABLE_REFERRALS;

        if ($from_date && $to_date) {
            $sql .= $this->wpdb->prepare(" WHERE referred_date BETWEEN %s AND %s", $from_date, $to_date);
        }

        $sql .= " GROUP BY referrer_source";

        return $this->wpdb->get_results($sql);
    }

    /**
     * Get referral status statistics
     * 
     * @param string|null $from_date
     * @param string|null $to_date
     * @return array
     */
    public function get_referral_status_stats($from_date = null, $to_date = null) {
        $sql = "SELECT COUNT(*) as total, re.status_id, st.name, st.category 
                FROM " . CUSTOM_API_TABLE_REFERRALS . " re 
                INNER JOIN " . CUSTOM_API_TABLE_REFERRALS_STATUS . " st ON re.status_id = st.id";

        if ($from_date && $to_date) {
            $sql .= $this->wpdb->prepare(" WHERE re.referred_date BETWEEN %s AND %s", $from_date, $to_date);
        }

        $sql .= " GROUP BY re.status_id";

        return $this->wpdb->get_results($sql);
    }

    /**
     * Create referral from external form (Unleash Forms)
     * 
     * @param array $data
     * @return array
     */
    public function create_referral_from_form($data) {
        // Check if email already exists
        $existing = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM " . CUSTOM_API_TABLE_REFERRALS . " WHERE email = %s",
                $data['email']
            )
        );

        if (count($existing) > 0) {
            return [
                'success' => false,
                'message' => 'Ya existe un registro con este correo electrónico',
                'data' => $existing
            ];
        }

        // Insert new referral
        $result = $this->wpdb->query(
            $this->wpdb->prepare(
                "INSERT INTO " . CUSTOM_API_TABLE_REFERRALS . " 
                (name, last_name, phone_number, email, job_preference, 
                 english_level, status_id, incoming_source, month) 
                VALUES (%s, %s, %s, %s, %s, %s, %d, %s, MONTH(NOW()))",
                $data['first_name'],
                $data['last_name'],
                $data['phone'],
                $data['email'],
                'IB/OB Support',
                'intermediate',
                0,
                $data['source']
            )
        );

        if ($result) {
            return [
                'success' => true,
                'message' => 'Registro exitoso',
                'referral_id' => $this->wpdb->insert_id
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al registrar',
            'error' => $this->wpdb->last_error
        ];
    }
}
