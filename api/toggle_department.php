<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['department_id']) || !isset($input['status'])) {
        echo json_encode(['success' => false, 'message' => 'Department ID and status are required']);
        exit();
    }

    $department_id = (int) $input['department_id'];
    $status = (int) $input['status'];

    // Check if department exists
    $stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt->execute([$department_id]);
    $dept = $stmt->fetch();
    
    if (!$dept) {
        echo json_encode(['success' => false, 'message' => 'Department not found']);
        exit();
    }

    // Update department status
    $stmt = $pdo->prepare("UPDATE departments SET is_active = ? WHERE id = ?");
    $stmt->execute([$status, $department_id]);

    $action = $status == 1 ? 'activated' : 'deactivated';
    
    echo json_encode([
        'success' => true,
        'message' => "Department {$action} successfully"
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}