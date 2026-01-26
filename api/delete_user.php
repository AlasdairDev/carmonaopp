<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

// Prevent deleting self
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Check if user exists and get their role
    $stmt = $pdo->prepare("SELECT id, role, name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // BALANCED MODE: Allow admin deletion but track it
    $isAdminDeletion = ($user['role'] === 'admin');
    
    // Delete user's notifications
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Get all documents for user's applications - FIXED COLLATION
    $stmt = $pdo->prepare("
        SELECT d.file_path 
        FROM documents d
        INNER JOIN applications a ON d.application_id = a.id
        WHERE a.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $documents = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Delete physical files
    foreach ($documents as $file_path) {
        if (file_exists("../$file_path")) {
            unlink("../$file_path");
        }
    }
    
    // Delete documents from database - FIXED COLLATION
    $stmt = $pdo->prepare("
        DELETE d FROM documents d
        INNER JOIN applications a ON d.application_id = a.id
        WHERE a.user_id = ?
    ");
    $stmt->execute([$user_id]);
    
    // Delete application status history - FIXED COLLATION
    $stmt = $pdo->prepare("
        DELETE ash FROM application_status_history ash
        INNER JOIN applications a ON ash.application_id = a.id
        WHERE a.user_id = ?
    ");
    $stmt->execute([$user_id]);
    
    
    // Delete applications
    $stmt = $pdo->prepare("DELETE FROM applications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Delete password reset tokens
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Finally, delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    // Log the activity with enhanced details for admin deletions
    $action = $isAdminDeletion ? 'delete_admin_user' : 'delete_user';
    $description = $isAdminDeletion 
        ? "🚨 ADMIN DELETED: {$user['name']} ({$user['email']}) - ID: {$user_id}"
        : "Deleted user account: {$user['name']} ({$user['email']}) - ID: {$user_id}";
    
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, description, ip_address, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $action,
        $description,
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ]);
    
    $pdo->commit();
    
    // Enhanced success message for admin deletions
    $successMessage = $isAdminDeletion 
        ? 'Administrator account deleted successfully. This action has been logged.'
        : 'User deleted successfully';
    
    echo json_encode([
        'success' => true,
        'message' => $successMessage,
        'was_admin' => $isAdminDeletion
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>