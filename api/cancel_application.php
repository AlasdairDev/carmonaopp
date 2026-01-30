<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$application_id = isset($data['application_id']) ? (int) $data['application_id'] : 0;
$cancellation_reason = isset($data['reason']) ? trim($data['reason']) : '';

if (!$application_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit();
}

if (empty($cancellation_reason)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a reason for cancellation']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Verify the application belongs to the user and can be cancelled
    $check_query = "SELECT id, status, tracking_number, department_id FROM applications WHERE id = ? AND user_id = ?";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$application_id, $user_id]);
    $application = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit();
    }

    // Only allow cancellation of pending or processing applications
    if (!in_array($application['status'], ['pending', 'processing'])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'This application cannot be cancelled']);
        exit();
    }

    // Update application status to cancelled AND clear payment requirements
    $update_query = "UPDATE applications 
                     SET status = 'cancelled', 
                         payment_required = 0,
                         payment_status = NULL,
                         payment_amount = NULL,
                         payment_deadline = NULL,
                         updated_at = NOW() 
                     WHERE id = ?";
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->execute([$application_id]);

    // Insert into status history with user's reason
    $history_query = "INSERT INTO application_status_history 
                      (application_id, status, remarks, updated_by, created_at) 
                      VALUES (?, 'cancelled', ?, ?, NOW())";
    $history_stmt = $pdo->prepare($history_query);
    $history_stmt->execute([$application_id, "Cancelled by user. Reason: " . $cancellation_reason, $user_id]);

    // Create notification for admin with reason
    $notification_query = "INSERT INTO notifications 
                  (user_id, application_id, title, message, type, is_read, created_at)
                  SELECT DISTINCT u.id, ?, 'Application Cancelled', 
                         CONCAT('Application ', ?, ' has been cancelled by the user. Reason: ', ?), 
                         'cancelled_application', 0, NOW()
                  FROM users u WHERE u.role IN ('superadmin', 'department_admin')";
    $notification_stmt = $pdo->prepare($notification_query);
    $notification_stmt->execute([$application_id, $application['tracking_number'], $cancellation_reason]);

    // Commit transaction
    $pdo->commit();

    // Log activity
    logActivity(
        $user_id,
        'Cancel Application',
        "Cancelled application {$application['tracking_number']}. Reason: {$cancellation_reason}",
        ['application_id' => $application_id, 'reason' => $cancellation_reason],
        $application['department_id'] 
    );

    echo json_encode(['success' => true, 'message' => 'Application cancelled successfully']);

} catch (PDOException $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error']);
    error_log('Cancel application error: ' . $e->getMessage());
}
?>
