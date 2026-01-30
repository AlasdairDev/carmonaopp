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
    
    if (!isset($input['service_id']) || !isset($input['status'])) {
        echo json_encode(['success' => false, 'message' => 'Service ID and status are required']);
        exit();
    }

    $service_id = (int) $input['service_id'];
    $status = (int) $input['status'];

    // Check if service exists
    $stmt = $pdo->prepare("SELECT service_name FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();
    
    if (!$service) {
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        exit();
    }

    // Update service status
    $stmt = $pdo->prepare("UPDATE services SET is_active = ? WHERE id = ?");
    $stmt->execute([$status, $service_id]);

    $action = $status == 1 ? 'activated' : 'deactivated';
    
    echo json_encode([
        'success' => true,
        'message' => "Service {$action} successfully"
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}