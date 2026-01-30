<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication and authorization
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required'
        ]);
        exit();
    }

    $user_id = (int) $input['user_id'];

    // Get user info before reactivation
    $stmt = $pdo->prepare("SELECT name, email, role, is_active FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit();
    }

    // Check if user is already active
    if ($user['is_active'] == 1) {
        echo json_encode([
            'success' => false,
            'message' => 'User is already active'
        ]);
        exit();
    }

    $was_admin = ($user['role'] === 'admin');

    // Reactivate: Set is_active to 1
    $stmt = $pdo->prepare("UPDATE users SET is_active = 1, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$user_id]);

    // Optional: Log the reactivation for audit purposes
    try {
        $log_stmt = $pdo->prepare("
            INSERT INTO user_reactivation_log 
            (reactivated_user_id, reactivated_by_user_id, reactivated_at, user_name, user_email, user_role) 
            VALUES (?, ?, NOW(), ?, ?, ?)
        ");
        $log_stmt->execute([
            $user_id,
            $_SESSION['user_id'],
            $user['name'],
            $user['email'],
            $user['role']
        ]);
    } catch (PDOException $e) {
        // If logging table doesn't exist, continue without logging
        
    }

    echo json_encode([
        'success' => true,
        'message' => 'User reactivated successfully',
        'was_admin' => $was_admin
    ]);

} catch (PDOException $e) {
    error_log("Reactivate user error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Reactivate user error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
