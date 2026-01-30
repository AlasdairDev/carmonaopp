<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

error_log("=== DEACTIVATE USER DEBUG ===");
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));

// Check authentication and authorization
if (!isLoggedIn() || !isAdmin()) {
    error_log("Auth failed - Not logged in or not admin");
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

try {
    // Get raw input
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);
    
    // Get JSON input
    $input = json_decode($rawInput, true);
    error_log("Decoded input: " . print_r($input, true));
    
    if (!isset($input['user_id'])) {
        error_log("ERROR: user_id not set in input");
        error_log("Available keys: " . print_r(array_keys($input ?: []), true));
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required',
            'debug' => [
                'raw_input' => $rawInput,
                'decoded' => $input,
                'keys' => array_keys($input ?: [])
            ]
        ]);
        exit();
    }

    $user_id = (int) $input['user_id'];
    error_log("Processing user_id: " . $user_id);
    
    // Prevent self-deactivation
    if ($user_id === $_SESSION['user_id']) {
        error_log("Self-deactivation attempt blocked");
        echo json_encode([
            'success' => false,
            'message' => 'You cannot deactivate your own account'
        ]);
        exit();
    }

    // Get user info before deactivation (for logging purposes)
    $stmt = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        error_log("User not found: " . $user_id);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit();
    }

    error_log("Found user: " . $user['name'] . " (" . $user['email'] . ")");
    $was_admin = ($user['role'] === 'admin');

    $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
    $stmt->execute([$user_id]);
    
    error_log("User deactivated successfully - affected rows: " . $stmt->rowCount());

    // Optional: Log the deactivation for audit purposes
    try {
        $log_stmt = $pdo->prepare("
            INSERT INTO user_deactivation_log 
            (deactivated_user_id, deactivated_by_user_id, deactivated_at, user_name, user_email, user_role) 
            VALUES (?, ?, NOW(), ?, ?, ?)
        ");
        $log_stmt->execute([
            $user_id,
            $_SESSION['user_id'],
            $user['name'],
            $user['email'],
            $user['role']
        ]);
        error_log("Deactivation logged successfully");
    } catch (PDOException $e) {
        error_log("Log table doesn't exist (this is OK): " . $e->getMessage());
    }

    error_log("=== DEACTIVATION SUCCESS ===");
    echo json_encode([
        'success' => true,
        'message' => 'User deactivated successfully',
        'was_admin' => $was_admin
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
