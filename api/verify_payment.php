<?php
/**
 * API: Verify or Reject Payment
 * Admin endpoint to verify or reject submitted payments
 * Status remains "Paid" after verification (already changed when user submitted)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/send_email.php';
require_once __DIR__ . '/../includes/send_sms.php';
require_once __DIR__ . '/../includes/security.php';


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
    $app_id = (int) ($_POST['app_id'] ?? 0);
    $action = $_POST['action'] ?? ''; // 'verify' or 'reject'
    $admin_id = $_SESSION['user_id'];

    if (!$app_id || !in_array($action, ['verify', 'reject'])) {
        throw new Exception('Invalid request parameters');
    }

    // Get application details - now checking for "Paid" status
    $stmt = $pdo->prepare("
        SELECT a.*, u.name, u.email, u.mobile, s.service_name
        FROM applications a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN services s ON a.service_id = s.id
        WHERE a.id = ? AND a.payment_status = 'submitted'
    ");
    $stmt->execute([$app_id]);
    $app = $stmt->fetch();


    if (!$app) {
        throw new Exception('Application not found or payment already processed');
    }

    if (!hasAccessToApplication($app_id)) {
        echo json_encode(['success' => false, 'message' => 'Access denied. You can only verify payments for your department.']);
        exit();
    }
    // Start transaction
    $pdo->beginTransaction();

    if ($action === 'verify') {
        // Verify payment - keep status as "Paid", just mark payment as verified
        $stmt = $pdo->prepare("
            UPDATE applications SET
                payment_status = 'verified',
                payment_verified_by = ?,
                payment_verified_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$admin_id, $app_id]);

        // Add to status history
        $stmt = $pdo->prepare("
            INSERT INTO application_status_history (application_id, status, remarks, updated_by, created_at)
            VALUES (?, 'Paid', 'Payment verified by admin - Ready for document claiming', ?, NOW())
        ");
        $stmt->execute([$app_id, $admin_id]);

        // Add to payment history
        $stmt = $pdo->prepare("
            INSERT INTO payment_history (application_id, action, amount, notes, performed_by, performed_at)
            VALUES (?, 'payment_verified', ?, 'Payment verified by admin', ?, NOW())
        ");
        $stmt->execute([$app_id, $app['payment_amount'], $admin_id]);

        // Create notification for user
        $notif_title = '✅ Payment Verified - Ready for Claiming';
        $notif_message = "Great news! Your payment for application {$app['tracking_number']} has been verified! You may now claim your permit/document at our office.";

        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, application_id, title, message, type, created_at)
            VALUES (?, ?, ?, ?, 'payment_verified', NOW())
        ");
        $stmt->execute([$app['user_id'], $app_id, $notif_title, $notif_message]);

        // Log activity
        logActivity(
            $admin_id,
            'Verify Payment',
            "Verified payment for application {$app['tracking_number']}",
            null,  // details
            $app['department_id']  // ADD THIS
        );
        $pdo->commit();

        // Send email
        try {
            if (!empty($app['email'])) {
                $email_subject = "✅ Payment Verified - Ready for Claiming [{$app['tracking_number']}]";
                $email_body = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: linear-gradient(135deg, #4CAF50, #66BB6A); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                            .content { padding: 30px; background: #f9f9f9; }
                            .status-badge { background: #4CAF50; color: white; padding: 12px 24px; border-radius: 20px; display: inline-block; font-weight: bold; font-size: 18px; }
                            .info-box { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #4CAF50; border-radius: 5px; }
                            .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                            .checklist { background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0; }
                            .checklist ul { margin: 10px 0; padding-left: 20px; }
                            .checklist li { margin: 8px 0; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>🎉 Payment Verified!</h1>
                                <p style='font-size: 18px; margin: 10px 0;'>Your application is ready for claiming</p>
                            </div>
                            <div class='content'>
                                <p>Dear {$app['name']},</p>
                                <p>Congratulations! Your payment has been <strong>verified</strong> by our admin team.</p>
                                
                                <div style='text-align: center; margin: 30px 0;'>
                                    <span class='status-badge'>✓ VERIFIED</span>
                                </div>
                                
                                <div class='info-box'>
                                    <h3>📋 Application Details:</h3>
                                    <table style='width: 100%; border-collapse: collapse;'>
                                        <tr>
                                            <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'><strong>Tracking Number:</strong></td>
                                            <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'>{$app['tracking_number']}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'><strong>Service:</strong></td>
                                            <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'>{$app['service_name']}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'><strong>Amount Paid:</strong></td>
                                            <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'><strong>₱" . number_format($app['payment_amount'], 2) . "</strong></td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'><strong>Reference:</strong></td>
                                            <td style='padding: 8px; border-bottom: 1px solid #e0e0e0;'>{$app['payment_reference']}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 8px;'><strong>Status:</strong></td>
                                            <td style='padding: 8px;'><strong style='color: #4CAF50;'>PAID ✓</strong></td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class='checklist'>
                                    <h3>📍 Next Steps - Claim Your Document:</h3>
                                    <p><strong>Please bring the following when claiming:</strong></p>
                                    <ul>
                                        <li>✓ Valid Government-Issued ID</li>
                                        <li>✓ This email or your tracking number: <strong>{$app['tracking_number']}</strong></li>
                                        <li>✓ Payment receipt/screenshot (if available)</li>
                                    </ul>
                                    
                                    <p style='margin-top: 20px;'><strong>🏢 Office Location:</strong><br>
                                    Local Government Unit Office<br>
                                    Municipal Hall</p>
                                    
                                    <p><strong>🕐 Office Hours:</strong><br>
                                    Monday to Friday: 8:00 AM - 5:00 PM</p>
                                </div>
                                
                                <p style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>
                                    <strong>⏰ Important:</strong> Please claim your document within <strong>30 days</strong> from today to avoid re-processing.
                                </p>
                                
                                <p>Thank you for using our service!</p>
                            </div>
                            <div class='footer'>
                                <p>This is an automated message from LGU Permit Tracking System</p>
                                <p>For inquiries, please contact our office during business hours</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";

                sendEmail(
                    $app['email'],
                    $email_subject,
                    $email_body,
                    '',  // altBody
                    $app['user_id'],
                    $app_id,
                    $app['department_id']
                );
            }
        } catch (Exception $e) {
            error_log("Failed to send verification email: " . $e->getMessage());
        }

        // Send SMS
        try {
            if (!empty($app['mobile'])) {
                $sms_message = "✅ PAYMENT VERIFIED! Your application {$app['tracking_number']} is ready for claiming. Visit our office with valid ID and tracking number. Thank you!";
                sendSMS(
                    $app['mobile'],
                    $sms_message,
                    $app['user_id'],
                    $app_id,
                    $app['department_id']
                );
            }
        } catch (Exception $e) {
            error_log("Failed to send verification SMS: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Payment verified successfully! Applicant can now claim their document.',
            'status' => 'Paid - Verified'
        ]);

    } else if ($action === 'reject') {
        $rejection_reason = trim($_POST['rejection_reason'] ?? '');

        if (empty($rejection_reason)) {
            throw new Exception('Rejection reason is required');
        }

        // Reject payment - revert status back to Approved, reset payment
        $stmt = $pdo->prepare("
            UPDATE applications SET
                status = 'Approved',
                payment_status = 'rejected',
                payment_rejection_reason = ?,
                payment_proof = NULL,
                payment_proof_size = NULL,
                payment_reference = NULL,
                payment_submitted_at = NULL,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$rejection_reason, $app_id]);

        // Add to status history - record status change back to Approved
        $stmt = $pdo->prepare("
            INSERT INTO application_status_history (application_id, status, remarks, updated_by, created_at)
            VALUES (?, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: {$rejection_reason}', ?, NOW())
        ");
        $stmt->execute([$app_id, $admin_id]);

        // Add to payment history
        $stmt = $pdo->prepare("
            INSERT INTO payment_history (application_id, action, notes, performed_by, performed_at)
            VALUES (?, 'payment_rejected', ?, ?, NOW())
        ");
        $stmt->execute([$app_id, $rejection_reason, $admin_id]);

        // Create notification for user
        $notif_title = 'Payment Rejected - Status Reverted to APPROVED';
        $notif_message = "Your payment proof for application {$app['tracking_number']} has been rejected. Status changed back to APPROVED. Reason: {$rejection_reason}. Please submit a new payment proof.";

        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, application_id, title, message, type, created_at)
            VALUES (?, ?, ?, ?, 'payment_rejected', NOW())
        ");
        $stmt->execute([$app['user_id'], $app_id, $notif_title, $notif_message]);

        // Log activity
        logActivity(
            $admin_id,
            'Reject Payment',
            "Rejected payment for application {$app['tracking_number']} - Status reverted to Approved",
            null,  // details
            $app['department_id']  // ADD THIS
        );
        // Delete old payment proof file
        if (!empty($app['payment_proof'])) {
            $file_path = __DIR__ . '/../' . $app['payment_proof'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $pdo->commit();

        // Send email
        try {
            if (!empty($app['email'])) {
                $email_subject = "❌ Payment Rejected - Resubmission Required [{$app['tracking_number']}]";
                $email_body = "
                    <!DOCTYPE html>
                    <html>
                    <body style='font-family: Arial, sans-serif; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                            <div style='background: #f44336; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                                <h1>❌ Payment Rejected</h1>
                            </div>
                            <div style='padding: 30px; background: #f9f9f9;'>
                                <p>Dear {$app['name']},</p>
                                <p>Unfortunately, your payment proof has been <strong>rejected</strong>.</p>
                                
                                <div style='background: #ffebee; padding: 20px; border-left: 4px solid #f44336; margin: 20px 0; border-radius: 5px;'>
                                    <h3 style='margin-top: 0; color: #c62828;'>Rejection Reason:</h3>
                                    <p style='font-size: 16px;'><strong>{$rejection_reason}</strong></p>
                                </div>
                                
                                <div style='background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #ff9800; border-radius: 5px;'>
                                    <h3>📋 Application Details:</h3>
                                    <ul style='list-style: none; padding: 0;'>
                                        <li><strong>Tracking Number:</strong> {$app['tracking_number']}</li>
                                        <li><strong>Service:</strong> {$app['service_name']}</li>
                                        <li><strong>Payment Amount:</strong> ₱" . number_format($app['payment_amount'], 2) . "</li>
                                        <li><strong>Deadline:</strong> " . date('M d, Y h:i A', strtotime($app['payment_deadline'])) . "</li>
                                        <li><strong>Current Status:</strong> <strong style='color: #ff9800;'>APPROVED (Payment Pending)</strong></li>
                                    </ul>
                                </div>
                                
                                <div style='background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                                    <h3>🔄 Next Steps:</h3>
                                    <ol>
                                        <li>Review the rejection reason above</li>
                                        <li>Log in to your account</li>
                                        <li>Submit a <strong>new payment proof</strong> with correct information</li>
                                        <li>Ensure the screenshot is clear and shows:
                                            <ul>
                                                <li>✓ Amount paid</li>
                                                <li>✓ Reference number</li>
                                                <li>✓ Date and time</li>
                                                <li>✓ Recipient details</li>
                                            </ul>
                                        </li>
                                    </ol>
                                </div>
                                
                                <p style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>
                                    <strong>⚠️ Important:</strong> Please resubmit your payment proof before the deadline to avoid cancellation.
                                </p>
                                
                                <p>If you have questions, please contact our office.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";

                sendEmail(
                    $app['email'],
                    $email_subject,
                    $email_body,
                    '',  // altBody
                    $app['user_id'],
                    $app_id,
                    $app['department_id']
                );
            }
        } catch (Exception $e) {
            error_log("Failed to send rejection email: " . $e->getMessage());
        }

        // Send SMS
        try {
            if (!empty($app['mobile'])) {
                $sms_message = "Payment REJECTED for {$app['tracking_number']}. Status: APPROVED (Payment Pending). Reason: {$rejection_reason}. Please resubmit payment proof. Check email for details.";
                sendSMS(
                    $app['mobile'],
                    $sms_message,
                    $app['user_id'],
                    $app_id,
                    $app['department_id']
                );
            }
        } catch (Exception $e) {
            error_log("Failed to send rejection SMS: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Payment rejected. Status reverted to Approved. User notified to resubmit payment proof.',
            'new_status' => 'Approved'
        ]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Payment verification error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}