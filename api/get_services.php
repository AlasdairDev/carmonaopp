<?php
// FILE: api/get_services.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$department_id = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;

if (!$department_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Department ID is required'
    ]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            service_name,
            service_code,
            description,
            requirements,
            processing_days,
            base_fee
        FROM services 
        WHERE department_id = ? AND is_active = 1 
        ORDER BY service_name ASC
    ");
    
    $stmt->execute([$department_id]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'services' => $services
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching services: ' . $e->getMessage()
    ]);
}