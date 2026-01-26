<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$notificationId = isset($input['notification_id']) ? (int)$input['notification_id'] : 0;

if (!$notificationId) {
    echo json_encode(['success' => false, 'message' => 'Notification ID required']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$notificationId, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}