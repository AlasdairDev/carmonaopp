<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$onlyCount = isset($_GET['only_count']) && $_GET['only_count'] === 'true';

// If only count is requested, return early
if ($onlyCount) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $unreadCount = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'unread_count' => $unreadCount
    ]);
    exit();
}
try {
    // Get unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $unreadCount = $stmt->fetchColumn();
    
    // Get notifications - COLLATION SAFE
    $stmt = $pdo->prepare("
        SELECT n.*, a.tracking_number
        FROM notifications n
        LEFT JOIN applications a ON n.application_id = a.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    $notifications = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'unread_count' => $unreadCount,
        'notifications' => $notifications
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
