<?php
// FILE: api/update_application.php
// ENHANCED VERSION WITH EMAIL & SMS NOTIFICATIONS

// Start output buffering to catch any stray output
ob_start();

// Disable display errors (send to log instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load dependencies
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/send_email.php';
require_once __DIR__ . '/../includes/send_sms.php';

// Clear output buffer and set JSON header
ob_end_clean();
header('Content-Type: application/json');

// Log function for debugging
function debugLog($message)
{
    $logFile = __DIR__ . '/../debug.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

try {
    debugLog("=== UPDATE APPLICATION REQUEST ===");
    debugLog("POST: " . json_encode($_POST));
    debugLog("SESSION: user_id=" . ($_SESSION['user_id'] ?? 'none') . ", role=" . ($_SESSION['role'] ?? 'none'));

    // Check authentication
    if (!isLoggedIn() || !isAdmin()) {
        throw new Exception('Unauthorized access. Must be logged in as admin.');
    }

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. POST required.');
    }

    // Get and validate input
    $app_id = isset($_POST['app_id']) ? (int) $_POST['app_id'] : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
    $send_email = isset($_POST['send_email']) && $_POST['send_email'] === '1';
    $send_sms = isset($_POST['send_sms']) && $_POST['send_sms'] === '1';

    debugLog("app_id: $app_id, new_status: $new_status, send_email: " . ($send_email ? 'yes' : 'no') . ", send_sms: " . ($send_sms ? 'yes' : 'no'));

    // Validation
    if (!$app_id) {
        throw new Exception('Application ID is required');
    }

    if (empty($new_status)) {
        throw new Exception('New status is required');
    }

    $valid_statuses = ['Pending', 'Processing', 'Approved', 'Rejected', 'Completed'];
    if (!in_array($new_status, $valid_statuses)) {
        throw new Exception('Invalid status: ' . $new_status);
    }

    // ✅ PREVENT COMPLETED WITHOUT PAYMENT VERIFICATION
    if ($new_status === 'Completed') {
        // Check if this application requires payment
        $check_payment = $pdo->prepare("
            SELECT payment_required, payment_status 
            FROM applications 
            WHERE id = ?
        ");
        $check_payment->execute([$app_id]);
        $payment_info = $check_payment->fetch();

        if ($payment_info && $payment_info['payment_required'] == 1) {
            // Payment is required, check if verified
            if ($payment_info['payment_status'] !== 'verified') {
                throw new Exception('<i class="fas fa-times-circle">Cannot mark as Completed: Payment must be verified first! Please go to "Verify Payments" page to confirm the payment before completing this application.');
            }
        }
    }


    // Start transaction
    $pdo->beginTransaction();

    try {
        // Get application details with user info
        $stmt = $pdo->prepare("
            SELECT a.*, 
                   u.name, 
                   u.email, 
                   u.mobile,
                   COALESCE(a.service_name, s.service_name, 'Service') as service_name
            FROM applications a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN services s ON a.service_id = s.id
            WHERE a.id = ?
        ");
        $stmt->execute([$app_id]);
        $application = $stmt->fetch();

        if (!$application) {
            throw new Exception('Application not found with ID: ' . $app_id);
        }

        $old_status = $application['status'];
        debugLog("Old status: $old_status, New status: $new_status");

        // Check if status is actually changing
        if ($old_status === $new_status && empty($remarks)) {
            throw new Exception('No changes to update. Status is already ' . $new_status);
        }

        // Update application
        if ($new_status === 'Rejected') {
            $stmt = $pdo->prepare("
                UPDATE applications 
                SET status = ?, 
                    admin_remarks = ?,
                    payment_required = 0,
                    payment_status = NULL,
                    payment_amount = NULL,
                    payment_deadline = NULL,
                    updated_at = NOW()
                WHERE id = ?
            ");
        } else {
            $stmt = $pdo->prepare("
                UPDATE applications 
                SET status = ?, 
                    admin_remarks = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
        }

        if (!$stmt->execute([$new_status, $remarks, $app_id])) {
            throw new Exception('Failed to update application: ' . implode(', ', $stmt->errorInfo()));
        }

        debugLog("Application updated successfully");

        // Add to application_status_history
        $stmt = $pdo->prepare("
            INSERT INTO application_status_history 
            (application_id, status, remarks, updated_by, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$app_id, $new_status, $remarks, $_SESSION['user_id']]);
        debugLog("Status history added");

        // Create in-app notification with specific types
        $notification_type = 'status_update';  // default
        if ($new_status === 'Approved') {
            $notification_type = 'approved';
        } elseif ($new_status === 'Rejected') {
            $notification_type = 'rejected';
        } elseif ($new_status === 'Completed') {
            $notification_type = 'completed';
        } elseif ($new_status === 'Processing') {
            $notification_type = 'processing';
        }

        $notification_message = "Your application ({$application['tracking_number']}) status has been updated to: {$new_status}";
        if ($remarks) {
            $notification_message .= ". Remarks: " . $remarks;
        }

        $stmt = $pdo->prepare("
            INSERT INTO notifications 
            (user_id, application_id, title, message, type, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([
            $application['user_id'],
            $app_id,
            'Application Status Updated',
            $notification_message,
            $notification_type
        ]);

        debugLog("In-app notification created");

        // Commit transaction before sending notifications
        // (This ensures database changes are saved even if email/SMS fails)
        $pdo->commit();
        debugLog("Transaction committed");

        // Update application array with new data for notifications
        $application['status'] = $new_status;
        $application['admin_remarks'] = $remarks;

        // Track notification results
        $notifications_sent = [];
        $email_sent = false;
        $sms_sent = false;

        // Send Email Notification (outside transaction)
        if ($send_email && isEmailConfigured()) {
            debugLog("Attempting to send email to: " . $application['email']);

            try {
                $emailTemplate = getApplicationStatusEmailTemplate($application, $new_status);
                if ($emailTemplate) {
                    $email_sent = sendEmail(
                        $application['email'],
                        $emailTemplate['subject'],
                        $emailTemplate['body'],
                        '',  // altBody
                        $application['user_id'],
                        $app_id,
                        $application['department_id']
                    );

                    if ($email_sent) {
                        $notifications_sent[] = '<i class="fas fa-check-circle"></i> Email sent to ' . $application['email'];
                        debugLog("Email sent successfully");
                    } else {
                        $notifications_sent[] = '<i class="fas fa-times-circle"></i> Email failed to send';
                        debugLog("Email sending failed");
                    }
                } else {
                    $notifications_sent[] = '<i class="fas fa-exclamation-triangle"></i> No email template for status: ' . $new_status;
                    debugLog("No email template found for status: $new_status");
                }
            } catch (Exception $e) {
                $notifications_sent[] = '<i class="fas fa-times-circle"></i> Email error: ' . $e->getMessage();
                debugLog("Email error (non-critical): " . $e->getMessage());
            }
        } elseif (!isEmailConfigured()) {
            $notifications_sent[] = '<i class="fas fa-exclamation-triangle"></i> Email not configured';
        }

        // Send SMS Notification (outside transaction)
        if ($send_sms && isSMSConfigured()) {
            debugLog("Attempting to send SMS to: " . $application['mobile']);

            try {
                $smsMessage = getSMSTemplate($application, $new_status);
                $sms_sent = sendSMS(
                    $application['mobile'],
                    $smsMessage,
                    $application['user_id'],
                    $app_id,
                    $application['department_id']
                );

                if ($sms_sent) {
                    $notifications_sent[] = '<i class="fas fa-check-circle"></i> SMS sent to ' . $application['mobile'];
                    debugLog("SMS sent successfully");
                } else {
                    $notifications_sent[] = '<i class="fas fa-times-circle"></i> SMS failed to send';
                    debugLog("SMS sending failed");
                }
            } catch (Exception $e) {
                $notifications_sent[] = '<i class="fas fa-times-circle"></i> SMS error: ' . $e->getMessage();
                debugLog("SMS error (non-critical): " . $e->getMessage());
            }
        } elseif (!isSMSConfigured()) {
            $notifications_sent[] = '<i class="fas fa-exclamation-triangle"></i> SMS not configured';
        }

        // Log activity
        logActivity(
            $_SESSION['user_id'],
            'Update Application Status',
            "Updated application {$application['tracking_number']} from $old_status to $new_status",
            [
                'application_id' => $app_id,
                'tracking_number' => $application['tracking_number'],
                'old_status' => $old_status,
                'new_status' => $new_status,
                'email_sent' => $email_sent,
                'sms_sent' => $sms_sent
            ],
            $application['department_id']  // ADD THIS - 5th parameter
        );

        debugLog("=== UPDATE COMPLETED SUCCESSFULLY ===");

        // Build detailed success message
        $success_message = "Status updated to '{$new_status}' successfully!\n\n";

        // Email status
        if ($send_email) {
            if ($email_sent) {
                $success_message .= "<i class='fas fa-envelope'></i> Email: <i class='fas fa-check-circle'></i> Sent successfully\n";
            } else {
                $success_message .= "<i class='fas fa-envelope'></i> Email: <i class='fas fa-times-circle'></i> Failed to send\n";
            }
        } else {
            $success_message .= "<i class='fas fa-envelope'></i> Email: Skipped (not requested)\n";
        }

        // SMS status
        if ($send_sms) {
            if ($sms_sent) {
                $success_message .= "<i class='fas fa-sms'></i> SMS: <i class='fas fa-check-circle'></i> Sent successfully";
            } else {
                $success_message .= "<i class='fas fa-sms'></i> SMS: <i class='fas fa-times-circle'></i> Failed to send";
            }
        } else {
            $success_message .= "<i class='fas fa-sms'></i> SMS: Skipped (not requested)";
        }

        // Success response
        echo json_encode([
            'success' => true,
            'message' => $success_message,
            'data' => [
                'application_id' => $app_id,
                'old_status' => $old_status,
                'new_status' => $new_status,
                'tracking_number' => $application['tracking_number'],
                'email_sent' => $email_sent,
                'sms_sent' => $sms_sent
            ],
            'notifications' => $notifications_sent
        ]);

    } catch (Exception $e) {
        // Rollback transaction if still active
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            debugLog("Transaction rolled back");
        }
        throw $e; // Re-throw to outer catch
    }

} catch (Exception $e) {
    debugLog("ERROR: " . $e->getMessage());
    debugLog("Stack trace: " . $e->getTraceAsString());

    // Error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_detail' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
}