<?php
/**
 * API: Approve Application with Payment Request
 * Handles approval and sets payment requirements
 * UPDATED: Beautiful HTML email design without QR code
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $app_id = (int)($_POST['app_id'] ?? 0);
    $payment_amount = floatval($_POST['payment_amount'] ?? 0);
    $status = 'Approved';
    $remarks = trim($_POST['remarks'] ?? '');
    $send_email = isset($_POST['send_email']) && $_POST['send_email'] == '1';
    $send_sms = isset($_POST['send_sms']) && $_POST['send_sms'] == '1';
    
    if (!$app_id || $payment_amount <= 0) {
        throw new Exception('Invalid application ID or payment amount');
    }
    
    // Get payment config
    $stmt = $pdo->prepare("SELECT config_key, config_value FROM payment_config");
    $stmt->execute();
    $config = [];
    while ($row = $stmt->fetch()) {
        $config[$row['config_key']] = $row['config_value'];
    }
    
    $deadline_days = (int)($config['payment_deadline_days'] ?? 3);
    $payment_deadline = date('Y-m-d H:i:s', strtotime("+{$deadline_days} days"));
    
    // Get application details
    $stmt = $pdo->prepare("
        SELECT a.*, u.name, u.email, u.mobile, s.service_name
        FROM applications a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN services s ON a.service_id = s.id
        WHERE a.id = ?
    ");
    $stmt->execute([$app_id]);
    $app = $stmt->fetch();
    
    if (!$app) {
        throw new Exception('Application not found');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update application with approval and payment details
    $stmt = $pdo->prepare("
        UPDATE applications SET
            status = ?,
            admin_remarks = ?,
            payment_required = 1,
            payment_amount = ?,
            payment_status = 'pending',
            payment_deadline = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$status, $remarks, $payment_amount, $payment_deadline, $app_id]);
    
    // Add to status history
    $admin_id = $_SESSION['user_id'];
    $history_remarks = $remarks . "\n\nPayment Required: PHP " . number_format($payment_amount, 2) . "\nDeadline: " . date('M d, Y h:i A', strtotime($payment_deadline));
    
    $stmt = $pdo->prepare("
        INSERT INTO application_status_history (application_id, status, remarks, updated_by, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$app_id, $status, $history_remarks, $admin_id]);
    
    // Add to payment history
    $stmt = $pdo->prepare("
        INSERT INTO payment_history (application_id, action, amount, notes, performed_by, performed_at)
        VALUES (?, 'payment_required', ?, ?, ?, NOW())
    ");
    $stmt->execute([$app_id, $payment_amount, "Payment deadline set to " . date('M d, Y', strtotime($payment_deadline)), $admin_id]);
    
    // Create notification
    $notif_title = 'Application Approved - Payment Required';
    $notif_message = "Your application {$app['tracking_number']} has been approved! Please submit payment of PHP " . number_format($payment_amount, 2) . " within {$deadline_days} days (by " . date('M d, Y', strtotime($payment_deadline)) . ").";
    
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, application_id, title, message, type, created_at)
        VALUES (?, ?, ?, ?, 'success', NOW())
    ");
    $stmt->execute([$app['user_id'], $app_id, $notif_title, $notif_message]);
    
    $pdo->commit();
    
    // Send email notification with BEAUTIFUL HTML (no QR code)
    if ($send_email && !empty($app['email'])) {
        try {
            $email_subject = "Application Approved - Payment Required [{$app['tracking_number']}]";
            
        $email_body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            line-height: 1.6; 
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
        }
        .header { 
            background: linear-gradient(135deg, #7cb342, #9ccc65);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.95;
            font-size: 18px;
        }
        .content { 
            padding: 40px 30px;
            background: #ffffff;
        }
        .content > p:first-of-type {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .info-box { 
            background: #f8f9fa;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #7cb342;
            border-radius: 8px;
        }
        .info-box h3 {
            margin: 0 0 15px 0;
            color: #558b2f;
            font-size: 18px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-table td:first-child {
            font-weight: 600;
            color: #666;
            width: 40%;
        }
        .info-table td:last-child {
            color: #333;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .status-approved {
            color: #4CAF50;
            font-weight: 700;
        }
        .amount-highlight {
            font-size: 32px;
            color: #FF9800;
            font-weight: 800;
        }
        .payment-section { 
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 30px;
            margin: 25px 0;
            border-radius: 10px;
            border: 3px solid #2196F3;
        }
        .payment-section h3 {
            color: #1565c0;
            margin: 0 0 20px 0;
            font-size: 20px;
            text-align: center;
        }
        .payment-info {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 15px;
        }
        .payment-info p {
            margin: 12px 0;
            color: #1976d2;
            font-size: 16px;
        }
        .payment-info strong {
            color: #0d47a1;
        }
        .footer { 
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin: 5px 0;
            color: #666;
            font-size: 13px;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #7cb342, #9ccc65);
            color: white !important;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 700;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(124, 179, 66, 0.3);
        }
        .warning-box {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            color: #856404;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class='email-wrapper'>
        <div class='header'>
            <h1>Application Approved!</h1>
            <p>Payment Required</p>
        </div>
        
        <div class='content'>
            <p>Dear {$app['name']},</p>
            <p><strong>Great news!</strong> Your application has been approved.</p>
            
            <div class='info-box'>
                <h3>Application Details:</h3>
                <table class='info-table'>
                    <tr>
                        <td>Tracking Number:</td>
                        <td><strong>{$app['tracking_number']}</strong></td>
                    </tr>
                    <tr>
                        <td>Service:</td>
                        <td>{$app['service_name']}</td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><span class='status-approved'>Approved</span></td>
                    </tr>
                </table>
            </div>
            
            <div class='info-box' style='border-left-color: #FFA726; background: linear-gradient(135deg, #fff8e1, #ffecb3);'>
                <h3>Payment Required:</h3>
                <table class='info-table'>
                    <tr>
                        <td>Amount:</td>
                        <td><span class='amount-highlight'>PHP " . number_format($payment_amount, 2) . "</span></td>
                    </tr>
                    <tr>
                        <td>Deadline:</td>
                        <td><strong style='color: #d84315;'>" . date('F d, Y h:i A', strtotime($payment_deadline)) . "</strong></td>
                    </tr>
                    <tr>
                        <td>Days to Pay:</td>
                        <td><strong style='color: #d84315;'>{$deadline_days} days</strong></td>
                    </tr>
                </table>
            </div>
            
            <div class='payment-section'>
                <h3>Payment Instructions</h3>
                <div class='payment-info'>
                    <p><strong>GCash Number:</strong> {$config['gcash_number']}</p>
                    <p><strong>Account Name:</strong> {$config['gcash_name']}</p>
                    <p><strong>Amount to Send:</strong> PHP " . number_format($payment_amount, 2) . "</p>
                    <p><strong>Reference:</strong> {$app['tracking_number']}</p>
                </div>
            </div>
            
            <div class='info-box'>
                <h3>After Payment:</h3>
                <ol style='color: #333; line-height: 1.8; padding-left: 20px;'>
                    <li>Take a <strong>screenshot</strong> of your payment confirmation</li>
                    <li>Log in to your account</li>
                    <li>Go to <strong>'My Applications'</strong></li>
                    <li>Click on your application</li>
                    <li>Click <strong>'Pay Now'</strong> button</li>
                    <li>Upload the screenshot as payment proof</li>
                    <li>Wait for verification (usually within <strong>24 hours</strong>)</li>
                </ol>
            </div>
            
            " . ($remarks ? "<div class='info-box' style='border-left-color: #2196F3;'><h3>Admin Remarks:</h3><p style='color: #333; line-height: 1.7;'>" . nl2br(htmlspecialchars($remarks)) . "</p></div>" : "") . "
            
            <div style='text-align: center;'>
                <a href='" . BASE_URL . "/user/submit_payment.php?id={$app_id}' class='btn'>
                    Submit Payment Now
                </a>
            </div>
            
            <div class='warning-box'>
                <strong>Important:</strong> Your payment must be received within <strong>{$deadline_days} days</strong>. Late payments may result in application cancellation.
            </div>
            
            <p style='margin-top: 25px;'><strong>Thank you for using our service!</strong></p>
        </div>
        
        <div class='footer'>
            <p>This is an automated email from LGU Permit System.</p>
            <p>For questions, contact us at " . SMTP_FROM_EMAIL . "</p>
            <p style='margin-top: 15px; color: #999;'>&copy; " . date('Y') . " LGU Permit System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";
            
            sendEmail($app['email'], $email_subject, $email_body);
        } catch (Exception $e) {
            error_log("Failed to send approval email: " . $e->getMessage());
        }
    }
    
    // Send SMS notification
    if ($send_sms && !empty($app['mobile'])) {
        try {
            $sms_message = "Your application {$app['tracking_number']} is APPROVED! Payment required: PHP " . number_format($payment_amount, 2) . " within {$deadline_days} days. Pay via GCash to {$config['gcash_number']}. Ref: {$app['tracking_number']}";
        } catch (Exception $e) {
            error_log("Failed to send approval SMS: " . $e->getMessage());
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Application approved with payment request',
        'payment_amount' => number_format($payment_amount, 2),
        'payment_deadline' => date('M d, Y h:i A', strtotime($payment_deadline))
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Approval with payment error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}