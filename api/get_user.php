<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, name as full_name, email, mobile as phone, address, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching user: ' . $e->getMessage()
    ]);
}
?>