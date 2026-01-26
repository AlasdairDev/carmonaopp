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
$days = isset($input['days']) ? (int)$input['days'] : 90;

if ($days < 1 || $days > 365) {
    echo json_encode(['success' => false, 'message' => 'Days must be between 1 and 365']);
    exit();
}

try {
    // Delete old logs
    $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
    $stmt->execute([$days]);
    $deleted = $stmt->rowCount();
    
    // Log this action
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, description, ip_address, created_at)
        VALUES (?, 'clear_logs', ?, ?, NOW())
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        "Cleared activity logs older than {$days} days ({$deleted} records deleted)",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Old logs cleared successfully',
        'deleted' => $deleted
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error clearing logs: ' . $e->getMessage()
    ]);
}
?>