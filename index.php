<?php
/**
 * Plugin Name: Custom API
 * Plugin URI: http://chrushingit.com
 * Description: Crushing it!
 * Version: 1.0
 * Author: Art Vandelay
 * Author URI: http://watch-learn.com
 */

//  require './vendor/autoload.php';

//  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
//  $dotenv->load();



//date_default_timezone_set('America/La_Paz');
$currentdate = date("Y-m-d H:i:s");

// $api_key_from_header = $_SERVER['HTTP_X_API_KEY'];

// $encrypted_api_key = openssl_encrypt($api_key, 'AES-256-CBC', $encryption_key, 0, $iv);

// global custom route permission callback fucn.------------------
function my_custom_route_permission_callback( $request ) {
    
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    $api_key_from_header = $request->get_header('x-api-key');
    $api_key = $_ENV['API_KEY'];
    // echo $request->get_header('x-api-key');
    $encryption_key = $_ENV['ENCRYPTION_KEY'];
    $iv = '1234567890123456'; // 16-byte IV for AES-256-CBC
    $decrypted_api_key = openssl_decrypt($api_key_from_header, 'AES-256-CBC', $encryption_key, 0, $iv);
    if ( $api_key ==  $decrypted_api_key) {
      return true;
    } else {
      return new WP_Error( 'rest_forbidden', esc_html__( 'Invalid API key.', 'myplugin' ), array( 'status' => 403 ) );
    }
}
// ---------------------------------------------------------------

// SELECT 
//     o.id            AS referral_id,
//     o.NAME          AS referral_name,
//     o.last_name     AS referral_last_name,
//     o.phone_number  AS referral_phone_number,
//     o.email         AS referral_email,
//     o.experiencia   AS referral_experience,
//     o.english_level AS referral_english_level,
//     referrer_source AS referral_referrer_source,
//     referred_date   AS referral_referred_date,
//     ref.id          AS referrer_id,
//     ref.NAME        AS referrer_name,
//     ref.last_name   AS referrer_last_name,
//     ref.email       AS referrer_email,
//     ref.newtech_id  AS referrer_newtech_id,
//     st.id           AS status_id,
//     st.NAME         AS status_name,
//     st.parent       AS status_parent,
//     cd.alphanumeric_code AS referral_code
//     FROM   wp_referrals o
//     INNER JOIN wp_referrer ref
//             ON ref.id = o.referrer_id
//     INNER JOIN wp_referrals_status st
//             ON st.id = o.status_id
//     INNER JOIN wp_referral_code cd
//             ON cd.id = o.referral_code
//     WHERE cd.alphanumeric_code = "RAF0.08285";

function wp_test() {
    global $wpdb;
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
    FROM   wp_referrals o
    LEFT JOIN wp_referrer ref
            ON ref.id = o.referrer_id
    LEFT JOIN wp_referrals_status st
            ON st.id = o.status_id
    LEFT JOIN wp_referral_code cd
            ON cd.id = o.referral_code ORDER BY o.id DESC";

    // retrieve change log info base on the referral id
    // sql to retrieve change log info:      

	$result = $wpdb -> get_results($sql);
     // add sql_change_lo result to the result array
    foreach ($result as $key => $value) {
        $sql_change_log = "SELECT c.id, c.record_id, s_old.name AS old_status_name, s_new.name AS new_status_name, c.old_status, c.new_status, c.performer, c.date
        FROM wp_changelog_status_referral c
        JOIN wp_referrals_status s_old ON c.old_status = s_old.id
        JOIN wp_referrals_status s_new ON c.new_status = s_new.id
        WHERE c.record_id = $value->referral_id
        ORDER BY c.date DESC
        LIMIT 4";
        $result[$key]->change_log = $wpdb -> get_results($sql_change_log);
    }
    

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result,
            'sql' => $sql_change_log
        );
    }

	return $response;
}
function wp_referrals_by_employee_id($params) {
    global $wpdb;

    $internal_id = $params['employee_id'];
    
    $isNTG = strpos(strtolower($internal_id), 'ntg') !== false;
    $isNT = strpos(strtolower($internal_id), 'nt') !== false;

    if($isNTG){
        $isNT = false;
    }

    if (stripos($internal_id, "ntg") === 0) {
        $internal_id = "NG" . substr($internal_id, 3);
    }
    
    $sql = $wpdb -> prepare( "SELECT 
    o.id                    AS referral_id,
    o.NAME                  AS referral_name,
    o.last_name             AS referral_last_name,
    referred_date           AS referral_referred_date,
    st.referrer_label       AS status_name,
    st.category             AS status_category
    FROM   wp_referrals o
    INNER JOIN wp_referrer ref
            ON ref.id = o.referrer_id
    INNER JOIN wp_referrals_status st
            ON st.id = o.status_id
    WHERE o.internal_id = %s",  [$internal_id]);

    // If $isNTG is true, add a condition to filter by referrer_source
    if ($isNTG) {
        $sql .= " AND referrer_source = 'NTG'";
    }
    // If $isNT is true, add a condition to filter by referrer_source
    if ($isNT) {
        $sql .= " AND referrer_source = 'NT'";
    }

	$result = $wpdb -> get_results($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result,
            'sql' => $sql,
            'employee_id' => $params['employee_id'],
        );
    }

	return $response;
}
function wp_referrals_by_referral_id($params) {
    global $wpdb;
    $sql = $wpdb -> prepare( "SELECT 
    o.id                    AS referral_id,
    o.NAME                  AS referral_name,
    o.last_name             AS referral_last_name,
    referred_date           AS referral_referred_date,
    st.referrer_label       AS status_name,
    st.category             AS status_category
    FROM   wp_referrals o
    INNER JOIN wp_referrer ref
            ON ref.id = o.referrer_id
    INNER JOIN wp_referrals_status st
            ON st.id = o.status_id
    INNER JOIN wp_referral_code cd
            ON cd.id = o.referral_code
    WHERE cd.alphanumeric_code = %s",  [$params['referral_id']]);

	$result = $wpdb -> get_results($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        );
    }

	return $response;
}
function wp_add_referral($request) {

    $resp = json_decode($request -> get_body(), true);
    global $wpdb;
    $english_level = $resp['english_level']['code'];
    $referrer_source = $resp['referrer_source']['code'];
    $experiencia = $resp['experiencia']['code'];

    // $internal_id = `$referrer_source-$resp[newtech_id]`;
    $internal_id = "$referrer_source$resp[newtech_id]";

    if (stripos($internal_id, "ntg") === 0) {
        $internal_id = "NG" . substr($internal_id, 3);
    }

    $query = $wpdb->prepare("SELECT * FROM `wp_internal` WHERE id = %s", $internal_id );
    $results_employee = $wpdb->get_results( $query );

    if(count( $results_employee ) === 0 && $referrer_source !== 'non-employee'){
        $response = array(
            'status' => false,
            'message' => 'no_employee_matches',
            'error' => '404',
            'id' => $id
        );
        return $response;
    }

    // code to generate a random alphanumeric code
    $r_code_sql = "INSERT INTO wp_referral_code (alphanumeric_code)
    VALUES (CONCAT('RAF', RAND()))";    
    $wpdb->query($r_code_sql);
    $r_code_id = $wpdb -> insert_id;
    $r_code_sql = $wpdb -> prepare( "SELECT alphanumeric_code FROM wp_referral_code WHERE id = %d",  [$r_code_id]);
    $r_code_result = $wpdb-> get_results($r_code_sql);
    $r_code = $r_code_result[0] -> alphanumeric_code;

    // code to verify if there is a referrer with the same id
    $referrer_sql = $wpdb -> prepare( "SELECT * FROM wp_referrer WHERE id = %d",  [$resp['newtech_id']]);
    $referrer_result = $wpdb-> get_results($referrer_sql);

    if($referrer_result == null){
        $sql_insert_none_existing_referrer = $wpdb->prepare(
            "INSERT INTO wp_referrer (id, name, last_name, email, newtech_id) VALUES (%s, %s, %s, %s, %s)",
            $resp['newtech_id'],
            $resp['referral_name'],
            $resp['referral_last_name'],
            $resp['referral_email'],
            $resp['newtech_id']
        );        
        $wpdb->query($sql_insert_none_existing_referrer);
        $inserted_referrer_id = $wpdb -> insert_id;
        $resp['referrer_id'] = $inserted_referrer_id;
    }

    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Referral Code Email</title>
    <style>
        body {
            background: linear-gradient(to bottom right, rgb(0, 167, 92), rgb(0, 170, 162));
            margin: 0;
            padding: 0;
        }

        .content {
            margin: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        p {
            color: #333;
            font-size: 18px;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        p.contact {
            font-size: 16px;
            margin-bottom: 0;
        }

        .referral-code {
            background-color: rgb(0, 170, 162);
            color: white;
            font-size: 20px;
            padding: 10px 20px;
            border-radius: 20px;
            display: inline-block;
            width: fit-content;
            text-decoration: none;
            margin-top: 10px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: white;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="content">
        <img src="http://nt-referral.com/wp-content/uploads/2023/05/reaferral-app-logo.png" alt="Referral App Logo" width="300">
        <p>Congratulations! You are now part of Newtech’s Referral Program!</p>
        <p>We greatly appreciate your contributions to help our family grow every day!</p>
        <p>Your <strong>referral code</strong> is:</p>
        <a href="https://nt-referral.com/status?utm_source=email&raf_code='.$r_code.'" class="referral-code">' . $r_code . '</a>
        <p>For more information on your referral status, feel free to contact our Recruiting Department:</p>
        <p class="contact"><strong>Email:</strong> hr@newtechsa.com</p>
        <p class="contact"><strong>Phone Number:</strong> 1+ (829)-692-8482</p>
        <p><strong>Kindly note:</strong> Referral Payout is only valid for active Newtech employees.</p>
    </div>

    <div class="footer">
         <p>Copyright © '.$currentYear = date('Y').' Newtech</p>
    </div>
</body>

</html>
';

    // Set the email recipient, subject, body, and headers
    $to = $resp['referrer_email'];
    $subject = 'Newtech Referral Program';
    $body = $html;
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Set the WP Mail SMTP mailer to use
    add_action( 'phpmailer_init', function( $phpmailer ) {
        $phpmailer->isSMTP();
    });

    // Send the email using wp_mail()
    $sent = wp_mail($to, $subject, $body, $headers);

    
    

    // code to insert the referral
    $sql = $wpdb->prepare(
        "INSERT INTO `wp_referrals`
        (`name`,
         `last_name`,
         `phone_number`,
         `email`,
         `experiencia`,
         `english_level`,
         `job_preference`,
         `referrer_source`,
         `referrer_id`,
         `internal_id`,
         `referrer_email`,
         `referrer_phone_number`,
         `referrer_name`,
         `referrer_last_name`, -- This is the 14th column
         `status_id`,
         `referral_code`,
         `month`)
        VALUES      
        (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, MONTH(NOW()))",
        addslashes($resp['referral_name']),
        addslashes($resp['referral_last_name']),
        addslashes($resp['referral_phone_number']),
        addslashes($resp['referral_email']),
        addslashes($experiencia),
        addslashes($english_level),
        addslashes($resp['job_preference']),
        addslashes($referrer_source),
        addslashes($internal_id),
        addslashes($internal_id),
        addslashes($resp['referrer_email']),
        addslashes($resp['referrer_phone_number']),
        addslashes($resp['referrer_name']),
        addslashes($resp['referrer_last_name']), // This should be in the 14th position
        0,
        $r_code_id
    );
    

    $result = $wpdb->query($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql,
            'error' => $wpdb->last_error,
            'referrral-code' => $r_code_id,
            'the_prop' => null,
            'confirmation_referrer' => $referrer_result,
        );
    }else {
        // Send the email using wp_mail()
        $send = wp_mail($to, $subject, $body, $headers);
        $response = array(
            'status' => true,
            'message' => 'Success',
            'send' => $send,
            'referral_code' => $r_code
    
        );
    }    

    return $response;
}
function wp_get_total_referral($request) {
    global $wpdb;
    $sql = "SELECT COUNT(*) AS total FROM wp_referrals";
    $result = $wpdb -> get_results($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result[0]
        );
    }

	return $response;

}
function wp_get_total_internos($request) {
    global $wpdb;
    $sql = "SELECT COUNT(*) AS total FROM `wp_referrals` WHERE `referrer_source`!='non-employee'";
    $result = $wpdb -> get_results($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result[0]
        );
    }

	return $response;

}
function wp_get_total_externos($request) {
    global $wpdb;
    $sql = "SELECT COUNT(*) AS total FROM `wp_referrals` WHERE `referrer_source`='non-employee'";
    $result = $wpdb -> get_results($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result[0]
        );
    }

	return $response;

}
function wp_get_total_excluidos($request) {
    global $wpdb;
    $sql = "SELECT COUNT(*) AS total FROM `wp_referrals` r INNER JOIN `wp_referrals_status` s ON r.status_id = s.id  WHERE s.category= 'rejected'";
    $result = $wpdb -> get_results($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result[0]
        );
    }

	return $response;

}
function wp_get_referrer_by_id($request) {
    global $wpdb;
    $id = $request['id'];
    $sql = "SELECT * FROM wp_referrer WHERE id = $id";
    $result = $wpdb -> get_results($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result[0]
        );
    }

    return $response;

}
function wp_get_referrers($request) {
    global $wpdb;
    $sql = "SELECT * FROM `wp_referrals` INNER JOIN wp_referrer ON wp_referrer.id = wp_referrals.referrer_id group by wp_referrals.referrer_id;";
    $result = $wpdb -> get_results($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        );
    }

    return $response;

}
function wp_get_referrers_with_referrals($request) {
    global $wpdb;
    $sql = "SELECT * FROM `wp_referrals` INNER JOIN wp_referrer ON wp_referrer.id = wp_referrals.referrer_id group by wp_referrals.referrer_id;";
    $result = $wpdb -> get_results($sql);

    foreach ($result as $key => $value) {
        $referrer_id = $value -> referrer_id;
        $sql = "SELECT * FROM `wp_referrals` WHERE `referrer_id` = $referrer_id";
        $referrals = $wpdb -> get_results($sql);
        $result[$key] -> referrals = $referrals;
    }

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        );
    }

    return $response;

}
function wp_get_status_monthly($request) {
    global $wpdb;
    $sql1 = "SELECT COUNT(*) as total FROM `wp_referrals` WHERE status_month = -1;";
    $result1 = $wpdb -> get_results($sql1);

    $sql2 = "SELECT COUNT(*) as total FROM `wp_referrals` WHERE status_month = 0;";
    $result2 = $wpdb -> get_results($sql2);

    $result = array(
        'status_month' => array(
            'review' => $result1[0] -> total,
            'no_review' => $result2[0] -> total
        )
    );



    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        );
    }

    return $response;

}
//function to set the status of a referral
function wp_set_status_referral($request) {
    $resp = json_decode($request -> get_body(), true);
    global $wpdb;
    $id = $request['id'];
    // retriving the current status of the referral
    $current_status = $wpdb->get_results("SELECT status_id FROM `wp_referrals` WHERE `id` = $id");
    $status_id = $request['status_id'];


    
    $sql = "UPDATE `wp_referrals` SET `status_id` = $status_id WHERE `id` = $id";
    $result = $wpdb->update('wp_referrals', array('status_id' => $status_id), array('id' => $id));   
    //add latest_status_review_date if status is updated successfully
    //add latest_status_review_by if status is updated successfully
    if ($result) {
        //retrive the user_name id of the current user
        $user_name = $resp['updated_by'];
        $currentdate = $resp['updated_at'];
        $result_review_date = $wpdb->update('wp_referrals', array('latest_status_review_date' => $currentdate), array('id' => $id));
        $result_review_by = $wpdb->update('wp_referrals', array('latest_status_review_by' => $user_name ), array('id' => $id));

        // if $result_review_date and $result_review_by are true, then add the status history to the wp_changelog_status_referral table
        
        $old_status = $current_status[0] -> status_id;
        $new_status = $status_id;
        $change_timestamp = current_time('mysql');
        $history =  $wpdb -> query("INSERT INTO wp_changelog_status_referral (record_id, old_status, new_status, performer) VALUES ($id, $old_status, $new_status, '$user_name')");

    }
    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql ,
            'error' => $wpdb -> last_error

        );
    }else {
        $wpdb->update('wp_referrals', array('status_month' => 0), array('id' => $id));
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result,
            'current_status' => $current_status[0] -> status_id,
            'new_status' => $status_id,
            'history' => $history,
            'last_error' => $wpdb -> last_error,
            'user' => $user_name
        );
    }

    return $response;

}
// function to get the number of three referral source categories
function wp_get_referral_source($request) {
    global $wpdb;
    $sql = "SELECT COUNT(*) as total, `referrer_source` FROM `wp_referrals` GROUP BY `referrer_source`";
    $result = $wpdb -> get_results($sql);

    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        );
    }

    return $response;

} 
// function to get the number of referral status
function wp_get_referral_status_chart($request) {
    global $wpdb;
    $sql = "SELECT COUNT(*) as total, `status_id`, st.name, category FROM `wp_referrals` re INNER JOIN `wp_referrals_status` st ON re.status_id = st.id GROUP BY `status_id`";
    $result = $wpdb -> get_results($sql);
    // $result -> status = $result;
    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        );
    }

    return $response;

}
// function to get the number of referral status sorted by date, takes from and to as parameters
function wp_get_referral_status_chart_by_date($request) {
    global $wpdb;
    $resp = json_decode($request -> get_body(), true);
    $from = $resp['from'];
    $to = $resp['to'];
    $sql = "SELECT COUNT(*) as total, `status_id`, st.name, category FROM `wp_referrals` re INNER JOIN `wp_referrals_status` st ON re.status_id = st.id WHERE re.referred_date BETWEEN '$from' AND '$to' GROUP BY `status_id`";
    $result = $wpdb -> get_results($sql);
    // $result -> status = $result;
    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        );
    }

    return $response;

}
// function to get the number of referral source sorted by date, takes from and to as parameters
function wp_get_referral_source_by_date($request) {
    global $wpdb;
    $resp = json_decode($request -> get_body(), true);
    $from = $resp['from'];
    $to = $resp['to'];
    $sql = "SELECT COUNT(*) as total, `referrer_source` FROM `wp_referrals` WHERE `referred_date` BETWEEN '$from' AND '$to' GROUP BY `referrer_source`";
    $result = $wpdb -> get_results($sql);
    // $result -> status = $result;
    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        );
    }

    return $response;

}

// function to get the overall statuses from the wp_referrals_status table
function wp_get_statuses($request) {
    global $wpdb;
    $sql = "SELECT * FROM `wp_referrals_status`";
    $result = $wpdb -> get_results($sql);
    // $result -> status = $result;
    if (!$result) {
        $response = array(
            'status' => false,
            'message' => 'An error occured...',
            'sql' => $sql
        );
    }else {
        $response = array(
            'status' => true,
            'message' => 'Success',
            'data' => $result
        );
    }

    return $response;

}


function wp_unleash_form($request) {

    global $wpdb;

    
    
    if(isset($_POST) && !empty($_POST)){

        $data = $_POST['forms'];

        error_log(print_r($data, true));

        $sql = ("SELECT * FROM wp_referrals WHERE email = '$data[email]'");

        $result = $wpdb -> get_results($sql);

        if(count($result) > 0){
            $response = array(
                'status' => false,
                'message' => 'Ya existe un registro con este correo electrónico',
                'data' => $result
            );
        }else{

            error_log(print_r($data, true));

            $sql2 = "INSERT INTO wp_referrals (
                name, 
                last_name, 
                phone_number, 
                email, 
                job_preference, 
                english_level,
                status_id,
                incoming_source,
                month
            ) VALUES (
                '$data[luyp_first_name]', 
                '$data[luyp_last_name]', 
                '$data[luyp_phone]', 
                '$data[luyp_email]',
                'IB/OB Support',  
                'intermediate', 
                '0',
                '$data[luyp_source]',
                MONTH(NOW())
            )";

            $result2 = $wpdb -> query($sql2);

            if($result2){
                $response = array(
                    'status' => true,
                    'message' => 'Registro exitoso',
                    'data' => $result2
                );
            }else{
                $response = array(
                    'status' => false,
                    'message' => 'Error al registrar',
                    'data' => $result2
                );
            }



            return $response;

        }



                

    }






}

// setting custom routes
add_action('rest_api_init', function() {    
	register_rest_route('c-api/v1', 'test', array(
		'methods' => 'GET',
		'callback' => 'wp_test',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
	register_rest_route('c-api/v1', 'referrals-by-employee-id/(?P<employee_id>[a-zA-Z0-9-]+)', array(
		'methods' => 'GET',
		'callback' => 'wp_referrals_by_employee_id',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
	register_rest_route('c-api/v1', 'referrals-by-referral-id/(?P<referral_id>[a-zA-Z0-9-.]+)', array(
		'methods' => 'GET',
		'callback' => 'wp_referrals_by_referral_id',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
	register_rest_route('c-api/v1', 'get-total-referral', array(
		'methods' => 'GET',
		'callback' => 'wp_get_total_referral',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
	register_rest_route('c-api/v1', 'get-total-internos', array(
		'methods' => 'GET',
		'callback' => 'wp_get_total_internos',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
	register_rest_route('c-api/v1', 'get-total-externos', array(
		'methods' => 'GET',
		'callback' => 'wp_get_total_externos',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
	register_rest_route('c-api/v1', 'get-total-excluidos', array(
		'methods' => 'GET',
		'callback' => 'wp_get_total_excluidos',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
    register_rest_route('c-api/v1', 'get-referrers', array(
		'methods' => 'GET',
		'callback' => 'wp_get_referrers',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
    register_rest_route('c-api/v1', 'get-referrers-with-referrals', array(
		'methods' => 'GET',
		'callback' => 'wp_get_referrers_with_referrals',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
    register_rest_route('c-api/v1', 'get-status-monthly', array(
		'methods' => 'GET',
		'callback' => 'wp_get_status_monthly',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
    register_rest_route('c-api/v1', 'get-referral-source', array(
		'methods' => 'GET',
		'callback' => 'wp_get_referral_source',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
    register_rest_route('c-api/v1', 'get-referral-status-chart', array(
		'methods' => 'GET',
		'callback' => 'wp_get_referral_status_chart',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
    register_rest_route('c-api/v1', 'get-referrer-by-id/(?P<id>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'wp_get_referrer_by_id',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
    ));
    register_rest_route('c-api/v1', 'get-statuses', array(
        'methods' => 'GET',
        'callback' => 'wp_get_statuses',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
    ));
    register_rest_route('c-api/v1', 'set-status/(?P<id>[a-zA-Z0-9-]+)/(?P<status_id>[a-zA-Z0-9-]+)', array(
        'methods' => 'PUT',
        'callback' => 'wp_set_status_referral',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
    ));
	register_rest_route('c-api/v1', 'add-referral', array(
		'methods' => 'POST',
		'callback' => 'wp_add_referral',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
	));
	 register_rest_route('c-api/v1', 'get_referral_status_chart_by_date', array(
        'methods' => 'POST',
        'callback' => 'wp_get_referral_status_chart_by_date',
        'permission_callback' => 'my_custom_route_permission_callback',
		 'show_in_index' => false,
    ));
    register_rest_route('c-api/v1', 'get_referral_source_by_date', array(
        'methods' => 'POST',
        'callback' => 'wp_get_referral_source_by_date',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
    ));
    register_rest_route('c-api/v1', 'unleash_forms', array(
        'methods' => 'POST',
        'callback' => 'wp_unleash_form',
        'permission_callback' => 'my_custom_route_permission_callback',
		'show_in_index' => false,
    ));
});




// function to add the user role to the JWT token


function add_user_role_to_jwt_token($data, $user) {
    // Get the user's roles
    $user_roles = $user->roles;

    // Add the user's roles to the JWT token data
    $data['user_roles'] = $user_roles;

    // You can add more data to $data if needed

    return $data;
}
add_filter('jwt_auth_token_before_dispatch', 'add_user_role_to_jwt_token', 10, 2);
