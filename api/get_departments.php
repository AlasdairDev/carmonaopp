<?php
// FILE: api/get_departments.php
// FIXED: Changed from MySQLi ($conn) to PDO ($pdo) for consistency
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    // FIXED: Using $pdo instead of $conn for consistency with rest of application
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