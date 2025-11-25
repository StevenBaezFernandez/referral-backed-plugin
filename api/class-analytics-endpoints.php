<?php
/**
 * Analytics API Endpoints
 * 
 * Handles all analytics and statistics REST API endpoints
 * 
 * @package Custom_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Custom_API_Analytics_Endpoints {
    
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
     * Register all analytics routes
     * 
     * @return void
     */
    public function register_routes() {
        // Get total referrals count
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-total-referral', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_total_referrals'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get total internal referrals
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-total-internos', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_total_internal'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get total external referrals
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-total-externos', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_total_external'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get total rejected referrals
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-total-excluidos', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_total_rejected'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get monthly status review stats
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-status-monthly', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_status_monthly'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referral source statistics
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-referral-source', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_referral_source'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referral source statistics by date range
        register_rest_route(CUSTOM_API_NAMESPACE, '/get_referral_source_by_date', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_referral_source_by_date'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referral status chart
        register_rest_route(CUSTOM_API_NAMESPACE, '/get-referral-status-chart', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_referral_status_chart'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));

        // Get referral status chart by date range
        register_rest_route(CUSTOM_API_NAMESPACE, '/get_referral_status_chart_by_date', array(
            'methods' => 'POST',
            'callback' => array($this, 'get_referral_status_chart_by_date'),
            'permission_callback' => array('Custom_API_Auth', 'validate_request'),
            'show_in_index' => false,
        ));
    }

    /**
     * Get total referrals count
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_total_referrals($request) {
        $total = $this->db->get_total_referrals();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => array('total' => $total)
        ), 200);
    }

    /**
     * Get total internal referrals (from employees)
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_total_internal($request) {
        $total = $this->db->get_total_internal_referrals();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => array('total' => $total)
        ), 200);
    }

    /**
     * Get total external referrals (non-employees)
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_total_external($request) {
        $total = $this->db->get_total_external_referrals();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => array('total' => $total)
        ), 200);
    }

    /**
     * Get total rejected referrals
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_total_rejected($request) {
        $total = $this->db->get_total_rejected_referrals();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => array('total' => $total)
        ), 200);
    }

    /**
     * Get monthly status review statistics
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_status_monthly($request) {
        $result = $this->db->get_status_monthly();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => array('status_month' => $result)
        ), 200);
    }

    /**
     * Get referral source statistics
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_referral_source($request) {
        $result = $this->db->get_referral_source_stats();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ), 200);
    }

    /**
     * Get referral source statistics by date range
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_referral_source_by_date($request) {
        $body = json_decode($request->get_body(), true);
        $from = $body['from'] ?? null;
        $to = $body['to'] ?? null;

        if (!$from || !$to) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'From and To dates are required'
            ), 400);
        }

        $result = $this->db->get_referral_source_stats($from, $to);

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ), 200);
    }

    /**
     * Get referral status chart data
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_referral_status_chart($request) {
        $result = $this->db->get_referral_status_stats();

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ), 200);
    }

    /**
     * Get referral status chart data by date range
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_referral_status_chart_by_date($request) {
        $body = json_decode($request->get_body(), true);
        $from = $body['from'] ?? null;
        $to = $body['to'] ?? null;

        if (!$from || !$to) {
            return new WP_REST_Response(array(
                'status' => false,
                'message' => 'From and To dates are required'
            ), 400);
        }

        $result = $this->db->get_referral_status_stats($from, $to);

        return new WP_REST_Response(array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        ), 200);
    }
}
