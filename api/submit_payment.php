<?php
/**
 * API: Submit Payment Proof
 * Handles payment proof upload from users
 * ✅ AUTO STATUS CHANGE: Approved → Paid when user submits payment
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/send_email.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $app_id = (int)($_POST['app_id'] ?? 0);
    $payment_reference = trim($_POST['payment_reference'] ?? '');
    $payment_notes = trim($_POST['payment_notes'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (!$app_id || empty($payment_reference)) {
        throw new Exception('Missing required fields');
    }
    
    // Verify application belongs to user and is in Approved status
    $stmt = $pdo->prepare("
        SELECT * FROM applications 
WHERE id = ? AND user_id = ? AND payment_required = 1 AND payment_status IN ('pending', 'rejected') AND status = 'Approved'
    ");
    $stmt->execute([$app_id, $user_id]);
    $app = $stmt->fetch();
    
    if (!$app) {
        throw new Exception('Application not found, payment already submitted, or not yet approved');
    }
    
    // Check if deadline has passed
    if (strtotime($app['payment_deadline']) < time()) {
        throw new Exception('Payment deadline has expired');
    }
    
    // Handle file upload
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Please upload payment proof');
    }
    
    $file = $_FILES['payment_proof'];
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type. Only JPG and PNG are allowed');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('File size exceeds 5MB limit');
    }
    
    // Create upload directory
    $upload_dir = __DIR__ . '/../assets/uploads/payments/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'payment_' . $app_id . '_' . time() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_filename;
    $relative_path = 'assets/uploads/payments/' . $new_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to upload file');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // ✅ UPDATE APPLICATION: Change status from Approved to Paid automatically
    $stmt = $pdo->prepare("
        UPDATE applications SET
            payment_status = 'submitted',
            payment_reference = ?,
            payment_proof = ?,
            payment_proof_size = ?,
            payment_notes = ?,
            payment_submitted_at = NOW(),
            status = 'Paid',
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$payment_reference, $relative_path, $file['size'], $payment_notes, $app_id]);
    
    // Add to status history - record the automatic status change
    $stmt = $pdo->prepare("
        INSERT INTO application_status_history (application_id, status, remarks, updated_by, created_at)
        VALUES (?, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', ?, NOW())
    ");
    $stmt->execute([$app_id, $user_id]);
    
    // Add to payment history
    $stmt = $pdo->prepare("
        INSERT INTO payment_history (application_id, action, amount, payment_method, reference_number, notes, performed_by, performed_at)
        VALUES (?, 'payment_submitted', ?, 'GCash', ?, ?, ?, NOW())
    ");
    $stmt->execute([$app_id, $app['payment_amount'], $payment_reference, $payment_notes, $user_id]);
    
    // Create notification for user
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, application_id, title, message, type, created_at)
        VALUES (?, ?, ?, ?, 'success', NOW())
    ");
    $notif_msg = "Your payment proof for application {$app['tracking_number']} has been submitted successfully! Your application status is now PAID and is under verification.";
    $stmt->execute([$user_id, $app_id, 'Payment Submitted - Status Changed to PAID', $notif_msg]);
    
    // Create notification for admins
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    
    $admin_stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, application_id, title, message, type, created_at)
        VALUES (?, ?, ?, ?, 'info', NOW())
    ");
    
    foreach ($admins as $admin) {
        $admin_msg = "✅ Payment submitted for application {$app['tracking_number']}. Status changed to PAID. Please verify payment proof.";
        $admin_stmt->execute([$admin['id'], $app_id, 'New Payment - Status: PAID', $admin_msg]);
    }
    
    // Log activity
    logActivity($user_id, 'Submit Payment', "Submitted payment for application {$app['tracking_number']} - Status changed to Paid");
    
    $pdo->commit();
    
    // Send email to user
    try {
        $stmt = $pdo->prepare("SELECT u.email, u.name, s.service_name FROM applications a JOIN users u ON a.user_id = u.id LEFT JOIN services s ON a.service_id = s.id WHERE a.id = ?");
        $stmt->execute([$app_id]);
        $details = $stmt->fetch();
        
        if ($details && !empty($details['email'])) {
            $email_subject = "✅ Payment Submitted - Status: PAID [{$app['tracking_number']}]";
            $email_body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #4CAF50, #66BB6A); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { padding: 30px; background: #f9f9f9; }
                        .status-badge { background: #4CAF50; color: white; padding: 10px 20px; border-radius: 20px; display: inline-block; font-weight: bold; font-size: 16px; }
                        .info-box { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #4CAF50; border-radius: 5px; }
                        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                        table { width: 100%; border-collapse: collapse; }
                        table td { padding: 10px; border-bottom: 1px solid #e0e0e0; }
                        table td:first-child { font-weight: bold; width: 40%; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>🎉 Payment Submitted Successfully!</h1>
                        </div>
                        <div class='content'>
                            <p>Dear {$details['name']},</p>
                            <p>Your payment proof has been submitted successfully!</p>
                            
                            <div style='text-align: center; margin: 30px 0;'>
                                <span class='status-badge'>STATUS: PAID ✓</span>
                            </div>
                            
                            <div class='info-box'>
                                <h3>Application Details:</h3>
                                <table>
                                    <tr>
                                        <td>Tracking Number:</td>
                                        <td><strong>{$app['tracking_number']}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Service:</td>
                                        <td>{$details['service_name']}</td>
                                    </tr>
                                    <tr>
                                        <td>Payment Amount:</td>
                                        <td><strong style='font-size: 18px; color: #4CAF50;'>₱" . number_format($app['payment_amount'], 2) . "</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Reference Number:</td>
                                        <td><strong>{$payment_reference}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Status:</td>
                                        <td><strong style='color: #4CAF50;'>PAID (Under Verification)</strong></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class='info-box' style='background: #e8f5e9; border-left-color: #4CAF50;'>
                                <h3>✅ What Happens Next?</h3>
                                <ol>
                                    <li>Our admin team will verify your payment proof</li>
                                    <li>Verification usually takes 24-48 hours</li>
                                    <li>You'll receive a notification once verified</li>
                                    <li>After verification, you can claim your permit/document</li>
                                </ol>
                            </div>
                            
                            <p><strong>Note:</strong> Your application status has been automatically updated to <strong>PAID</strong>.</p>
                            <p>You can track your application status anytime in your dashboard.</p>
                            
                            <p>Thank you for your patience!</p>
                        </div>
                        <div class='footer'>
                            <p>This is an automated message from LGU Permit Tracking System</p>
                            <p>Please do not reply to this email</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            sendEmail($details['email'], $email_subject, $email_body);
        }
    } catch (Exception $e) {
        error_log("Failed to send payment submission email: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => '✅ Payment proof submitted successfully! Your application status has been changed to PAID and is now under verification.',
        'new_status' => 'Paid'
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Delete uploaded file if exists
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }
    
    error_log("Payment submission error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}