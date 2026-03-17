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
        .signature p { margin:3px 0; font-size:9pt; }
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
    <div class="signature">
        <p><strong>Recruiting Department | Newtech</strong></p>
        <p>T: 1+ (829)-692-8482</p>
        <p>E: <a href="mailto:' . CUSTOM_API_EMAIL_FROM . '" style="color:#0563C1;text-decoration:none;">' . CUSTOM_API_EMAIL_FROM . '</a></p>
        <p>W: <a href="http://www.newtechsa.com/" style="color:#000;text-decoration:none;">www.newtechsa.com</a></p>
        <p style="font-size:8.5pt;color:#767171;margin-top:10px;">The information contained in this message may be proprietary and confidential. If you are not the intended recipient, please notify us immediately.</p>
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
    public static function send_status_update_email($recipient_email, $referral_name, $old_status, $new_status, $feedback_comment = '') {
        $subject = 'Referral Status Update - ' . $referral_name;
        $body = self::get_status_update_template($referral_name, $old_status, $new_status, $feedback_comment);
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
    private static function get_status_update_template($referral_name, $old_status, $new_status, $feedback_comment = '') {
    $current_year = date('Y');
    
    // Only show feedback block if a comment was provided
    $feedback_block = '';
    if (!empty($feedback_comment)) {
        $feedback_block = '
        <div class="feedback-box">
            <p><strong>Feedback:</strong></p>
            <p>' . esc_html($feedback_comment) . '</p>
        </div>';
    }

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
        .feedback-box {
            background-color: #f3fbff;
            border-left: 4px solid #3eade5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .signature p { margin:3px 0; font-size:9pt; }
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
        ' . $feedback_block . '
        <div class="signature">
            <p><strong>Recruiting Department | Newtech</strong></p>
            <p>T: 1+ (829)-692-8482</p>
            <p>E: <a href="mailto:' . CUSTOM_API_EMAIL_FROM . '" style="color:#0563C1;text-decoration:none;">' . CUSTOM_API_EMAIL_FROM . '</a></p>
            <p>W: <a href="http://www.newtechsa.com/" style="color:#000;text-decoration:none;">www.newtechsa.com</a></p>
            <p style="font-size:8.5pt;color:#767171;margin-top:10px;">The information contained in this message may be proprietary and confidential. If you are not the intended recipient, please notify us immediately.</p>
        </div>
    </div>
    <div class="footer">
         <p>Copyright © ' . $current_year . ' Newtech</p>
    </div>
</body>
</html>';
}


/**
 * Send hired notification to all internal departments
 */
public static function send_hired_department_emails($data) {
    // Define distro emails per department
    $departments = [
        'IT'                      => 'it@newtechsa.com',
        'Finanzas'                => 'finanzas@newtechsa.com',
        'Recursos Humanos'        => 'rrhh@newtechsa.com',
        'Legal'                   => 'legal@newtechsa.com',
        'Seguridad'               => 'seguridad@newtechsa.com',
        'Compensación y Beneficios' => 'compensacion@newtechsa.com',
    ];

    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $results = [];

    foreach ($departments as $dept_name => $dept_email) {
        $subject = 'Nuevo Empleado - Alta de Personal: ' . $data['referral_name'];
        $body    = self::get_hired_department_template($dept_name, $data);

        add_action('phpmailer_init', function($phpmailer) {
            $phpmailer->isSMTP();
        });

        $results[$dept_name] = wp_mail($dept_email, $subject, $body, $headers);
    }

    return $results;
}

private static function get_hired_department_template($department, $data) {
    $current_year = date('Y');
    $signing_date = date('d/m/Y h:i A', strtotime($data['signing_date']));

    return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { background: linear-gradient(to bottom right, rgb(0,167,92), rgb(0,170,162)); margin:0; padding:0; }
        .content { margin:30px; background:#fff; padding:25px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,.2); font-family:Segoe UI,Arial,sans-serif; font-size:10pt; color:#333; }
        h2 { color: rgb(0,170,162); margin-top:0; }
        .info-table { width:100%; border-collapse:collapse; margin:20px 0; }
        .info-table td { padding:10px 14px; border-bottom:1px solid #eee; font-size:10pt; }
        .info-table td:first-child { font-weight:600; width:45%; color:#555; }
        .dept-badge { display:inline-block; background:rgb(0,170,162); color:#fff; padding:4px 14px; border-radius:20px; font-size:9pt; margin-bottom:18px; }
        .footer { margin-top:30px; text-align:center; color:#fff; font-size:13px; }
        .signature p { margin:3px 0; font-size:9pt; }
    </style>
</head>
<body>
    <div class="content">
        <img src="' . CUSTOM_API_LOGO_URL . '" alt="Newtech" width="220" style="display:block;border:0;margin-bottom:16px;">
        <span class="dept-badge">Para: ' . esc_html($department) . '</span>
        <h2>Notificación de Alta de Personal</h2>
        <p>Se informa que el siguiente candidato ha sido contratado y requiere gestión de alta por parte de su departamento.</p>
        <table class="info-table">
            <tr><td>Nombre Completo</td><td>' . esc_html($data['referral_name']) . '</td></tr>
            <tr><td>Código de Empleado</td><td>' . esc_html($data['employee_code'] ?? 'Por asignar') . '</td></tr>
            <tr><td>Fecha de Inicio</td><td>' . esc_html($signing_date) . '</td></tr>
            <tr><td>Cliente</td><td>' . esc_html($data['client'] ?? 'N/A') . '</td></tr>
            <tr><td>Posición</td><td>' . esc_html($data['position_name'] ?? 'N/A') . '</td></tr>
            <tr><td>Modalidad de Trabajo</td><td>' . esc_html($data['work_modality'] ?? 'N/A') . '</td></tr>
            <tr><td>Sede de Trabajo</td><td>' . esc_html($data['work_location'] ?? 'N/A') . '</td></tr>
            <tr><td>Aprobado por</td><td>' . esc_html($data['approved_by']) . '</td></tr>
        </table>
        <div class="signature">
            <p><strong>Recruiting Department | Newtech</strong></p>
            <p>T: 1+ (829)-692-8482</p>
            <p>E: <a href="mailto:' . CUSTOM_API_EMAIL_FROM . '" style="color:#0563C1;text-decoration:none;">' . CUSTOM_API_EMAIL_FROM . '</a></p>
            <p>W: <a href="http://www.newtechsa.com/" style="color:#000;text-decoration:none;">www.newtechsa.com</a></p>
            <p style="font-size:8.5pt;color:#767171;margin-top:10px;">The information contained in this message may be proprietary and confidential. If you are not the intended recipient, please notify us immediately.</p>
        </div>
    </div>
    <div class="footer"><p>Copyright © ' . $current_year . ' Newtech</p></div>
</body>
</html>';
}


}


