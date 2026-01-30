<?php
require_once __DIR__ . '/../config.php';

// Load PHPMailer from vendor/phpmailer/phpmailer folder
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using PHPMailer
 */
function sendEmail($to, $subject, $body, $altBody = '', $user_id = null, $application_id = null, $department_id = null)
{
    // Validate inputs
    if (empty($to) || empty($subject) || empty($body)) {
        error_log("Email validation failed: Missing required fields");
        return false;
    }

    // Check if email is configured
    if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD')) {
        error_log("Email not configured: Missing SMTP settings in config.php");
        logEmailStatus($to, $subject, 'failed', 'SMTP not configured', $user_id, $application_id, $department_id);
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;

        // Timeout settings
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = false;

        // Recipients
        $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : SMTP_USERNAME;
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'LGU Permit System';
        $replyTo = defined('SMTP_REPLY_TO') ? SMTP_REPLY_TO : $fromEmail;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($replyTo);

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();

        // Log success
        logEmailStatus($to, $subject, 'sent', null, $user_id, $application_id, $department_id);

        return true;
    } catch (Exception $e) {
        // Log failure
        $errorMsg = $mail->ErrorInfo;
        error_log("Email send failed to $to: $errorMsg");
        logEmailStatus($to, $subject, 'failed', $errorMsg, $user_id, $application_id, $department_id);

        return false;
    }
}

/**
 * Log email status to database
 */
function logEmailStatus($recipient, $subject, $status, $error = null, $user_id = null, $application_id = null, $department_id = null)
{
    global $pdo;

    // Check if PDO connection exists
    if (!isset($pdo)) {
        error_log("Cannot log email: PDO connection not available");
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO email_logs (user_id, application_id, department_id, recipient, subject, status, error_message, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $application_id, $department_id, $recipient, $subject, $status, $error]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to log email status: " . $e->getMessage());
        return false;
    }
}

/**
 * Get email template for application status
 */
function getApplicationStatusEmailTemplate($application, $status)
{
    $tracking = htmlspecialchars($application['tracking_number']);
    $serviceName = htmlspecialchars($application['service_name']);
    $applicantName = htmlspecialchars($application['name']);
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';

    $templates = [
        'Pending' => [
            'subject' => 'Application Status Update - ' . $tracking,
            'body' => getStyledEmailTemplate(
                'Application Status Update',
                "
            <p>Dear {$applicantName},</p>
            <p>Your application status has been updated to <strong>Pending</strong>.</p>
            
            <div class='info-box'>
                <p><strong>Tracking Number:</strong> {$tracking}</p>
                <p><strong>Service:</strong> {$serviceName}</p>
                <p><strong>Status:</strong> <span style='color: #FF9800; font-weight: bold;'>Pending</span></p>
            </div>
            
            " . (!empty($application['admin_remarks']) ? "
            <div class='remarks-box'>
                <p><strong>Admin Remarks:</strong></p>
                <p>" . nl2br(htmlspecialchars($application['admin_remarks'])) . "</p>
            </div>
            " : "") . "
            
            <p>Our team will review your application shortly. You will receive updates as your application progresses.</p>
            
            <p style='text-align: center; margin-top: 2rem;'>
                <a href='{$baseUrl}/user/view_application.php?id={$application['id']}' class='button'>View Application</a>
            </p>
            ",
                '#FF9800'
            )
        ],
        'Processing' => [
            'subject' => 'Application Now Being Processed - ' . $tracking,
            'body' => getStyledEmailTemplate(
                'Application Being Processed',
                "
        <p>Dear {$applicantName},</p>
        <p>Good news! Your application is now being reviewed by our team.</p>
        
        <div class='info-box'>
            <p><strong>Tracking Number:</strong> {$tracking}</p>
            <p><strong>Service:</strong> {$serviceName}</p>
            <p><strong>Status:</strong> <span style='color: #FF9800; font-weight: bold;'>Processing</span></p>
        </div>
        
        <p>We will notify you once there is an update on your application.</p>
        
        <p style='text-align: center; margin-top: 2rem;'>
            <a href='{$baseUrl}/user/track.php?tracking={$tracking}' class='button'>Track Your Application</a>
        </p>
        ",
                '#FF9800'
            )
        ],

        'Approved' => [
            'subject' => 'Application Approved - ' . $tracking,
            'body' => getStyledEmailTemplate(
                'Application Approved!',
                "
        <p>Dear {$applicantName},</p>
        <p><strong>Congratulations!</strong> Your application has been approved and is ready for the next step.</p>
        
        <div class='info-box'>
            <p><strong>Tracking Number:</strong> {$tracking}</p>
            <p><strong>Service:</strong> {$serviceName}</p>
            <p><strong>Status:</strong> <span style='color: #4CAF50; font-weight: bold;'>✓ Approved</span></p>
        </div>
        
        " . (!empty($application['admin_remarks']) ? "
        <div class='remarks-box'>
            <p><strong>Admin Remarks:</strong></p>
            <p>" . nl2br(htmlspecialchars($application['admin_remarks'])) . "</p>
        </div>
        " : "") . "
        
        <p style='text-align: center; margin-top: 2rem;'>
            <a href='{$baseUrl}/user/view_application.php?id={$application['id']}' class='button'>View Details</a>
        </p>
        ",
                '#4CAF50'
            )
        ],

        'Rejected' => [
            'subject' => 'Application Requires Revision - ' . $tracking,
            'body' => getStyledEmailTemplate(
                'Application Status Update',
                "
        <p>Dear {$applicantName},</p>
        <p>We regret to inform you that your application requires revision.</p>
        
        <div class='info-box' style='border-left-color: #f44336;'>
            <p><strong>Tracking Number:</strong> {$tracking}</p>
            <p><strong>Service:</strong> {$serviceName}</p>
            <p><strong>Status:</strong> <span style='color: #f44336; font-weight: bold;'>⚠ Requires Revision</span></p>
        </div>
        
        " . (!empty($application['admin_remarks']) ? "
        <div class='remarks-box' style='background: #ffebee; border-left-color: #f44336;'>
            <p><strong>Remarks:</strong></p>
            <p>" . nl2br(htmlspecialchars($application['admin_remarks'])) . "</p>
        </div>
        " : "") . "
        
        <p>Please review the remarks and resubmit your application with the necessary corrections.</p>
        
        <p style='text-align: center; margin-top: 2rem;'>
            <a href='{$baseUrl}/user/view_application.php?id={$application['id']}' class='button' style='background: #2196F3;'>View Details</a>
        </p>
        ",
                '#f44336'
            )
        ],

        'Completed' => [
            'subject' => 'Application Completed - ' . $tracking,
            'body' => getStyledEmailTemplate(
                'Application Completed!',
                "
        <p>Dear {$applicantName},</p>
        <p><strong>Your service request has been completed and is ready for pickup/release.</strong></p>
        
        <div class='info-box'>
            <p><strong>Tracking Number:</strong> {$tracking}</p>
            <p><strong>Service:</strong> {$serviceName}</p>
            <p><strong>Status:</strong> <span style='color: #4CAF50; font-weight: bold;'>✓ Completed</span></p>
        </div>
        
        <div class='info-box' style='background: #e8f5e9;'>
            <p><strong>Next Steps:</strong></p>
            <p>Please proceed to the office to claim your documents.</p>
            <p><strong>Office Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM</p>
            <p><strong>Bring:</strong> Valid ID and your tracking number</p>
        </div>
        
        <p style='text-align: center; margin-top: 2rem;'>
            <a href='{$baseUrl}/user/view_application.php?id={$application['id']}' class='button'>View Details</a>
        </p>
        ",
                '#4CAF50'
            )
        ]
    ];

    return $templates[$status] ?? null;
}

/**
 * Get payment proof received email template
 */
function getPaymentProofReceivedEmailTemplate($application)
{
    $tracking = htmlspecialchars($application['tracking_number']);
    $serviceName = htmlspecialchars($application['service_name']);
    $applicantName = htmlspecialchars($application['name']);
    $amount = number_format($application['payment_amount'], 2);
    $reference = htmlspecialchars($application['payment_reference']);
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';

    return [
        'subject' => 'Payment Proof Received - ' . $tracking,
        'body' => getStyledEmailTemplate(
            'Payment Proof Received',
            "
            <p>Dear {$applicantName},</p>
            <p>We have received your payment proof for the following application:</p>
            
            <div class='info-box'>
                <p><strong>Tracking Number:</strong> {$tracking}</p>
                <p><strong>Service:</strong> {$serviceName}</p>
                <p><strong>Amount:</strong> ₱{$amount}</p>
                <p><strong>Reference:</strong> {$reference}</p>
            </div>
            
            <div class='info-box' style='background: #fff3e0; border-left-color: #FF9800;'>
                <p><strong>What's Next?</strong></p>
                <p>Your payment is now under verification. You will receive a notification once it has been verified.</p>
                <p><strong>Processing Time:</strong> Usually 24-48 hours</p>
            </div>
            
            <p>Thank you for your patience!</p>
            
            <p style='text-align: center; margin-top: 2rem;'>
                <a href='{$baseUrl}/user/view_application.php?id={$application['id']}' class='button'>View Application</a>
            </p>
            ",
            '#FF9800'
        )
    ];
}
/**
 * Base styled email template wrapper
 */
function getStyledEmailTemplate($title, $content, $accentColor = '#4CAF50')
{
    $year = date('Y');

    return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background-color: #f4f4f4; 
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white; 
        }
        .header { 
            background: linear-gradient(135deg, {$accentColor}, " . adjustColor($accentColor, 20) . ");
            color: white; 
            padding: 2.5rem 2rem; 
            text-align: center; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 1.8rem; 
            font-weight: 700;
        }
        .content { 
            padding: 2.5rem 2rem; 
        }
        .content p {
            margin-bottom: 1rem;
            line-height: 1.7;
        }
        .button { 
            display: inline-block; 
            padding: 1rem 2rem; 
            background: {$accentColor}; 
            color: white !important; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: 700;
            transition: all 0.3s ease;
        }
        .info-box { 
            background: #f8f9fa; 
            border-left: 4px solid {$accentColor}; 
            padding: 1.5rem; 
            margin: 1.5rem 0; 
            border-radius: 8px;
        }
        .info-box p {
            margin: 0.5rem 0;
        }
        .remarks-box {
            background: #fffbf0;
            border-left: 4px solid #FF9800;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 8px;
        }
        .footer { 
            background: #f8f9fa; 
            padding: 2rem; 
            text-align: center; 
            font-size: 0.9rem; 
            color: #666; 
            border-top: 1px solid #e0e0e0; 
        }
        .footer p {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>{$title}</h1>
        </div>
        <div class='content'>
            {$content}
        </div>
        <div class='footer'>
            <p><strong>Carmona Online Permit Portal</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p style='margin-top: 1rem; color: #999;'>Â© {$year} Carmona Online Permit Portal. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
    ";
}

function adjustColor($hex, $percent)
{
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + ($r * $percent / 100)));
    $g = max(0, min(255, $g + ($g * $percent / 100)));
    $b = max(0, min(255, $b + ($b * $percent / 100)));

    return sprintf("#%02x%02x%02x", $r, $g, $b);
}
