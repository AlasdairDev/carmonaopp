<?php
/**
 * Create Notification API
 * Enhanced with Email & SMS support
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/send_email.php';
require_once __DIR__ . '/../includes/send_sms.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

/**
 * Create in-app notification
 */
function createNotification($userId, $applicationId, $title, $message, $type = 'info') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, application_id, title, message, type, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$userId, $applicationId, $title, $message, $type]);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Send all notifications when application status changes
 */
function notifyApplicationStatusChange($applicationId, $newStatus, $sendEmail = true, $sendSms = true) {
    global $pdo;
    
    try {
        // Get application details with user info
        $stmt = $pdo->prepare("
            SELECT a.*, 
                   u.email, 
                   u.mobile, 
                   u.name as applicant_name,
                   COALESCE(a.service_name, s.service_name, 'Service') as service_name
            FROM applications a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN services s ON a.service_id = s.id
            WHERE a.id = ?
        ");
        $stmt->execute([$applicationId]);
        $application = $stmt->fetch();
        
        if (!$application) {
            return [
                'success' => false,
                'message' => 'Application not found'
            ];
        }
        
        // Notification messages based on status
        $notificationMessages = [
            'Processing' => 'Your application is now being processed by our team.',
            'Approved' => 'Congratulations! Your application has been approved.',
            'Rejected' => 'Your application requires revision. Please check the remarks.',
            'Completed' => 'Your application is completed and ready for pickup/release!'
        ];
        
        $title = "Application " . $newStatus;
        $message = $notificationMessages[$newStatus] ?? "Your application status has been updated to: $newStatus";
        
        // Determine notification type
        $type = $newStatus === 'Approved' || $newStatus === 'Completed' ? 'success' : 
               ($newStatus === 'Rejected' ? 'danger' : 'info');
        
        // Create in-app notification
        createNotification($application['user_id'], $applicationId, $title, $message, $type);
        
        $results = [
            'in_app' => true,
            'email' => false,
            'sms' => false
        ];
        
        // Send Email
        if ($sendEmail && isEmailConfigured()) {
            try {
                $emailTemplate = getApplicationStatusEmailTemplate($application, $newStatus);
                if ($emailTemplate) {
                    $results['email'] = sendEmail(
                        $application['email'], 
                        $emailTemplate['subject'], 
                        $emailTemplate['body']
                    );
                }
            } catch (Exception $e) {
                error_log("Email notification error: " . $e->getMessage());
            }
        }
        
        // Send SMS
        if ($sendSms && isSMSConfigured()) {
            try {
                $smsMessage = getSMSTemplate($application, $newStatus);
                $results['sms'] = sendSMS($application['mobile'], $smsMessage);
            } catch (Exception $e) {
                error_log("SMS notification error: " . $e->getMessage());
            }
        }
        
        return [
            'success' => true,
            'message' => 'Notifications sent',
            'results' => $results
        ];
        
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Send custom notification to user
 */
function sendCustomNotification($userId, $title, $message, $sendEmail = false, $sendSms = false) {
    global $pdo;
    
    try {
        // Get user details
        $stmt = $pdo->prepare("SELECT name, email, mobile FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Create in-app notification
        createNotification($userId, null, $title, $message, 'info');
        
        $results = [
            'in_app' => true,
            'email' => false,
            'sms' => false
        ];
        
        // Send Email
        if ($sendEmail && isEmailConfigured()) {
            $emailBody = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h2>$title</h2></div>
                    <div class='content'>
                        <p>Dear {$user['name']},</p>
                        <p>$message</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $results['email'] = sendEmail($user['email'], $title, $emailBody);
        }
        
        // Send SMS
        if ($sendSms && isSMSConfigured()) {
            $smsText = "LGU: $title - " . substr(strip_tags($message), 0, 120);
            $results['sms'] = sendSMS($user['mobile'], $smsText);
        }
        
        return [
            'success' => true,
            'message' => 'Notification sent',
            'results' => $results
        ];
        
    } catch (Exception $e) {
        error_log("Custom notification error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'status_change':
            $applicationId = $input['application_id'] ?? 0;
            $newStatus = $input['status'] ?? '';
            $sendEmail = $input['send_email'] ?? true;
            $sendSms = $input['send_sms'] ?? true;
            
            $result = notifyApplicationStatusChange($applicationId, $newStatus, $sendEmail, $sendSms);
            echo json_encode($result);
            break;
            
        case 'custom':
            $userId = $input['user_id'] ?? 0;
            $title = $input['title'] ?? '';
            $message = $input['message'] ?? '';
            $sendEmail = $input['send_email'] ?? false;
            $sendSms = $input['send_sms'] ?? false;
            
            $result = sendCustomNotification($userId, $title, $message, $sendEmail, $sendSms);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}