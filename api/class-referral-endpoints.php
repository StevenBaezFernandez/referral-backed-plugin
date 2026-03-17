<?php
/**
 * Referral API Endpoints
 * 
 * Handles all referral-related REST API endpoints
 * 
 * @package Custom_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Custom_API_Referral_Endpoints {
    
    /**
     * Database handler instance
     * 
     * @var Custom_API_Database
     */
    private $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new Custom_API_Database();
    }

    /**
     * Register all referral routes
     * 
     * @return void
     */
    public function register_routes() {
        // Get all referrals
        register_rest_route(CUSTOM_API_NAMESPACE, '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_all_referrals'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referrals by employee ID
        register_rest_route(CUSTOM_API_NAMESPACE, '/referrals-by-employee-id/(?P<employee_id>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_referrals_by_employee_id'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referrals by referral code
        register_rest_route(CUSTOM_API_NAMESPACE, '/referrals-by-referral-id/(?P<referral_id>[a-zA-Z0-9-.]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_referrals_by_code'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Add new referral
        register_rest_route(CUSTOM_API_NAMESPACE, '/add-referral', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_referral'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Update referral status
        register_rest_route(CUSTOM_API_NAMESPACE, '/set-status/(?P<id>[a-zA-Z0-9-]+)/(?P<status_id>[a-zA-Z0-9-]+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'set_status'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referrer by ID
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-referrer-by-id/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_referrer_by_id'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get all referrers
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-referrers', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_referrers'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referrers with their referrals
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-referrers-with-referrals', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_referrers_with_referrals'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get all statuses
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-statuses', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_statuses'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // External form submission (Unleash Forms)
        register_rest_route(CUSTOM_API_NAMESPACE, '/unleash_forms', array(
            'methods' => 'POST',
            'callback' => array($this, 'unleash_form'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get full changelog with feedback
        register_rest_route(CUSTOM_API_NAMESPACE, '/referral-changelog/(?P<id>[0-9]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_full_changelog'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Add feedback comment
        register_rest_route(CUSTOM_API_NAMESPACE, '/add-feedback/(?P<id>[0-9]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_feedback'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referrals by job preference
        register_rest_route(CUSTOM_API_NAMESPACE, '/referrals-by-job/(?P<job_preference>[a-zA-Z0-9\s\-\/]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_by_job_preference'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referrals for re-evaluation
        register_rest_route(CUSTOM_API_NAMESPACE, '/referrals-reevaluation', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_reevaluation_referrals'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Update job preference
        register_rest_route(CUSTOM_API_NAMESPACE, '/update-job-preference/(?P<id>[0-9]+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_job_preference'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        register_rest_route(CUSTOM_API_NAMESPACE, '/set-signing-date/(?P<id>\d+)', array(
            'methods'             => 'PUT',
            'callback'            => array($this, 'set_signing_date'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
        ));

        register_rest_route(CUSTOM_API_NAMESPACE, '/create-hiring-log', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'create_hiring_log'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
        ));

        register_rest_route(CUSTOM_API_NAMESPACE, '/get-hiring-logs', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_hiring_logs'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
        ));

        
    }

    public function set_signing_date($request) {
        $body          = json_decode($request->get_body(), true);
        $referral_id   = $request['id'];
        $signing_date  = $body['signing_date']  ?? null;
        $work_location = $body['work_location'] ?? ''; // 👈 added

        if (!$signing_date) {
            return new WP_REST_Response(['status' => false, 'message' => 'signing_date is required'], 400);
        }

        $result = $this->db->set_signing_date($referral_id, $signing_date);

        if (!$result['success']) {
            return new WP_REST_Response(['status' => false, 'message' => $result['error']], 500);
        }

        // Send signing date notification to candidate 👈
        $referral = $this->db->get_referral_by_id($referral_id);
        if ($referral) {
            Custom_API_Email::send_signing_date_email(
                $referral->email,
                $referral->name . ' ' . $referral->last_name,
                $signing_date,
                $work_location
            );
        }

        return new WP_REST_Response([
            'status'  => true,
            'message' => 'Signing date saved'
        ], 200);
    }

    public function create_hiring_log($request) {
        $body = json_decode($request->get_body(), true);

        $required = ['referral_id', 'referral_name', 'signing_date', 'approved_by'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                return new WP_REST_Response(['status' => false, 'message' => "$field is required"], 400);
            }
        }

        // Validate signing_date was set before allowing Hired
        $signing_date = $this->db->get_signing_date($body['referral_id']);
        if (!$signing_date) {
            return new WP_REST_Response([
                'status'  => false,
                'message' => 'Cannot mark as Hired without a signing date set first'
            ], 422);
        }

        $result = $this->db->create_hiring_log($body);

        if (!$result['success']) {
            return new WP_REST_Response(['status' => false, 'message' => $result['error']], 500);
        }

        // Send internal department emails 
        Custom_API_Email::send_hired_department_emails($body);

        // Send congratulations email to candidate 
        $referral = $this->db->get_referral_by_id($body['referral_id']);
        if ($referral) {
            Custom_API_Email::send_hired_congratulations_email(
                $referral->email,
                $body['referral_name'],
                $body['signing_date'],
                $body['position_name']  ?? '',
                $body['work_location']  ?? '',
                $body['work_modality']  ?? ''
            );
        }

        return new WP_REST_Response([
            'status'  => true,
            'message' => 'Hiring log created',
            'log_id'  => $result['log_id'] ?? null
        ], 201);
    }

    public function get_hiring_logs($request) {
        $logs = $this->db->get_hiring_logs();
        return new WP_REST_Response(['status' => true, 'data' => $logs], 200);
    }

    /**
     * Get all referrals
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_all_referrals($request) {
        $result = $this->db->get_all_referrals();

        if (!$result) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'An error occurred...'
            ), 500);
        }

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ), 200);
    }

    /**
     * Get referrals by employee ID
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_referrals_by_employee_id($request) {
        $employee_id = $request['employee_id'];
        $result = $this->db->get_referrals_by_employee_id($employee_id);

        if (!$result) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'An error occurred...'
            ), 500);
        }

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result,
            'employee_id' => $employee_id
        ), 200);
    }

    /**
     * Get referrals by referral code
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_referrals_by_code($request) {
        $referral_code = $request['referral_id'];
        $result = $this->db->get_referrals_by_code($referral_code);

        // Check for database error (false) vs empty results (empty array)
        if ($result === false) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'Database error occurred'
            ), 500);
        }

        // Check if no referrals found
        if (empty($result)) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'No referrals found with code: ' . $referral_code
            ), 404);
        }

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result,
            'count' => count($result)
        ), 200);
    }

    /**
     * Add new referral
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function add_referral($request) {
        $body = json_decode($request->get_body(), true);

        $data = array(
            'referral_name' => $body['referral_name'],
            'referral_last_name' => $body['referral_last_name'],
            'referral_phone_number' => $body['referral_phone_number'],
            'referral_email' => $body['referral_email'],
            'experiencia' => $body['experiencia']['code'],
            'english_level' => $body['english_level']['code'],
            'job_preference' => $body['job_preference'],
            'referrer_source' => $body['referrer_source']['code'],
            'newtech_id' => $body['newtech_id'],
            'referrer_email' => $body['referrer_email'],
            'referrer_phone_number' => $body['referrer_phone_number'],
            'referrer_name' => $body['referrer_name'],
            'referrer_last_name' => $body['referrer_last_name']
        );

        $result = $this->db->create_referral($data);

        if (!$result['success']) {
            $status_code = $result['message'] === 'no_employee_matches' ? 404 : 500;
            
            return new WP_REST_Response(array(
                'status' => false,
                'message' => $result['message'],
                'error' => $result['error'] ?? null
            ), $status_code);
        }

        // Send email notification
        $email_sent = Custom_API_Email::send_referral_code_email(
            $data['referrer_email'],
            $result['referral_code']
        );

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'referral_code' => $result['referral_code'],
            'email_sent' => $email_sent
        ), 201);
    }

    /**
     * Update referral status
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function set_status($request) {
        $body = json_decode($request->get_body(), true);
        $referral_id          = $request['id'];
        $status_id            = $request['status_id'];
        $updated_by           = $body['updated_by']           ?? 'system';
        $updated_at           = $body['updated_at']           ?? current_time('mysql');
        $feedback_comment     = $body['feedback_comment']     ?? '';
        $reevaluation_scheduled = (bool)($body['reevaluation_scheduled'] ?? false);

        $result = $this->db->update_referral_status(
            $referral_id,
            $status_id,
            $updated_by,
            $updated_at,
            $feedback_comment,
            $reevaluation_scheduled 
        );

        if (!$result['success']) {
            return new WP_REST_Response(array(
                'status'  => false,
                'message' => $result['message'],
                'error'   => $result['error'] ?? null
            ), 500);
        }

        return new WP_REST_Response(array(
            'status'  => true,
            'message' => 'Success',
            'data'    => $result
        ), 200);
    }

    /**
     * Get referrer by ID
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_referrer_by_id($request) {
        $referrer_id = $request['id'];
        $result = $this->db->get_referrer_by_id($referrer_id);

        if (!$result) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'Referrer not found'
            ), 404);
        }

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ), 200);
    }

    /**
     * Get all referrers
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_referrers($request) {
        $result = $this->db->get_all_referrers();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ), 200);
    }

    /**
     * Get referrers with their referrals
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_referrers_with_referrals($request) {
        $result = $this->db->get_referrers_with_referrals();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ), 200);
    }

    /**
     * Get all statuses
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_statuses($request) {
        $result = $this->db->get_all_statuses();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ), 200);
    }

    /**
     * Handle external form submission (Unleash Forms)
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function unleash_form($request) {
        if (!isset($_POST) || empty($_POST)) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'No data provided'
            ), 400);
        }

        $forms_data = $_POST['forms'];

        $data = array(
            'first_name' => $forms_data['luyp_first_name'],
            'last_name' => $forms_data['luyp_last_name'],
            'phone' => $forms_data['luyp_phone'],
            'email' => $forms_data['luyp_email'],
            'source' => $forms_data['luyp_source']
        );

        $result = $this->db->create_referral_from_form($data);

        $status_code = $result['success'] ? 201 : 400;

        return new WP_REST_Response($result, $status_code);
    }

    /**
     * Get full changelog with feedback for a referral
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_full_changelog($request) {
        $referral_id = $request['id'];
        $limit = $request->get_param('limit') ?? 10;

        $result = $this->db->get_full_changelog($referral_id, $limit);

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result,
            'count' => count($result)
        ), 200);
    }

    /**
     * Add feedback comment to a referral
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function add_feedback($request) {
        $referral_id = $request['id'];
        $body = json_decode($request->get_body(), true);
        $feedback_comment = $body['feedback_comment'] ?? '';

        if (empty($feedback_comment)) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'Feedback comment is required'
            ), 400);
        }

        $result = $this->db->add_feedback_comment($referral_id, $feedback_comment);

        $status_code = $result['success'] ? 200 : 500;

        return new WP_REST_Response($result, $status_code);
    }

    /**
     * Get referrals by job preference
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_by_job_preference($request) {
        $job_preference = urldecode($request['job_preference']);
        
        $result = $this->db->get_referrals_by_job_preference($job_preference);

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result,
            'job_preference' => $job_preference,
            'count' => count($result)
        ), 200);
    }

    /**
     * Get referrals for re-evaluation
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_reevaluation_referrals($request) {
        $result = $this->db->get_referrals_for_reevaluation();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result,
            'count' => count($result)
        ), 200);
    }

    /**
     * Update job preference for a referral
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_job_preference($request) {
        $referral_id = $request['id'];
        $body = json_decode($request->get_body(), true);
        $job_preference = $body['job_preference'] ?? '';

        if (empty($job_preference)) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'Job preference is required'
            ), 400);
        }

        $result = $this->db->update_job_preference($referral_id, $job_preference);

        $status_code = $result['success'] ? 200 : 500;

        return new WP_REST_Response($result, $status_code);
    }
}
