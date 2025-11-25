<?php
/**
 * Email Service Class
 * 
 * Handles all email notifications for referrals
 * 
 * @package Custom_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Custom_API_Email {
    
    /**
     * Send referral code email
     * 
     * @param string $recipient_email
     * @param string $referral_code
     * @return bool
     */
    public static function send_referral_code_email($recipient_email, $referral_code) {
        $subject = 'Newtech Referral Program';
        $body = self::get_referral_email_template($referral_code);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Configure WP Mail SMTP
        add_action('phpmailer_init', function($phpmailer) {
            $phpmailer->isSMTP();
        });

        return wp_mail($recipient_email, $subject, $body, $headers);
    }

    /**
     * Get referral email HTML template
     * 
     * @param string $referral_code
     * @return string
     */
    private static function get_referral_email_template($referral_code) {
        $current_year = date('Y');
        
        return '<!DOCTYPE html>
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
        <img src="' . CUSTOM_API_LOGO_URL . '" alt="Referral App Logo" width="300">
        <p>Congratulations! You are now part of Newtech\'s Referral Program!</p>
        <p>We greatly appreciate your contributions to help our family grow every day!</p>
        <p>Your <strong>referral code</strong> is:</p>
        <a href="' . CUSTOM_API_REFERRAL_URL . '?utm_source=email&raf_code=' . $referral_code . '" class="referral-code">' . $referral_code . '</a>
        <p>For more information on your referral status, feel free to contact our Recruiting Department:</p>
        <p class="contact"><strong>Email:</strong> ' . CUSTOM_API_EMAIL_FROM . '</p>
        <p class="contact"><strong>Phone Number:</strong> ' . CUSTOM_API_EMAIL_PHONE . '</p>
        <p><strong>Kindly note:</strong> Referral Payout is only valid for active Newtech employees.</p>
    </div>

    <div class="footer">
         <p>Copyright © ' . $current_year . ' Newtech</p>
    </div>
</body>

</html>';
    }

    /**
     * Send status update notification
     * 
     * @param string $recipient_email
     * @param string $referral_name
     * @param string $old_status
     * @param string $new_status
     * @return bool
     */
    public static function send_status_update_email($recipient_email, $referral_name, $old_status, $new_status) {
        $subject = 'Referral Status Update - ' . $referral_name;
        $body = self::get_status_update_template($referral_name, $old_status, $new_status);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        add_action('phpmailer_init', function($phpmailer) {
            $phpmailer->isSMTP();
        });

        return wp_mail($recipient_email, $subject, $body, $headers);
    }

    /**
     * Get status update email template
     * 
     * @param string $referral_name
     * @param string $old_status
     * @param string $new_status
     * @return string
     */
    private static function get_status_update_template($referral_name, $old_status, $new_status) {
        $current_year = date('Y');
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Referral Status Update</title>
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

        .status-box {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
        <img src="' . CUSTOM_API_LOGO_URL . '" alt="Referral App Logo" width="300">
        <p>The status of your referral <strong>' . esc_html($referral_name) . '</strong> has been updated.</p>
        <div class="status-box">
            <p><strong>Previous Status:</strong> ' . esc_html($old_status) . '</p>
            <p><strong>New Status:</strong> ' . esc_html($new_status) . '</p>
        </div>
        <p>For more information, contact our Recruiting Department:</p>
        <p><strong>Email:</strong> ' . CUSTOM_API_EMAIL_FROM . '</p>
        <p><strong>Phone:</strong> ' . CUSTOM_API_EMAIL_PHONE . '</p>
    </div>

    <div class="footer">
         <p>Copyright © ' . $current_year . ' Newtech</p>
    </div>
</body>

</html>';
    }
}
