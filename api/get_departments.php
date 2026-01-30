<?php
require_once __DIR__ . '/../config.php';


header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            code,
            description
        FROM departments 
        WHERE is_active = 1 
        ORDER BY name ASC
    ");
    
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'departments' => $departments,
        'count' => count($departments)
    ]);
    
} catch (Exception $e) {
    error_log("Get departments error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching departments: ' . $e->getMessage()
    ]);
}
?>
