<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$date_from = $data['date_from'] ?? null;
$date_to = $data['date_to'] ?? null;
$department_id = $data['department_id'] ?? null;
$is_department_admin = $data['is_department_admin'] ?? false;

try {
    // If no dates provided, delete ALL logs
    if (!$date_from && !$date_to) {
        if ($is_department_admin && $department_id) {
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE (department_id = ? OR related_department_id = ?)");
            $stmt->execute([$department_id, $department_id]);
            $deleted = $stmt->rowCount();
            logActivity(null, 'clear_logs', "All activity logs cleared for department $department_id");
        } else {
            $stmt = $pdo->prepare("DELETE FROM activity_logs");
            $stmt->execute();
            $deleted = $stmt->rowCount();
            logActivity(null, 'clear_logs', 'All activity logs cleared');
        }
    }
    // Build the DELETE query based on provided dates
    elseif ($date_from && $date_to) {
        if ($is_department_admin && $department_id) {
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE DATE(created_at) BETWEEN ? AND ? AND (department_id = ? OR related_department_id = ?)");
            $stmt->execute([$date_from, $date_to, $department_id, $department_id]);
            $deleted = $stmt->rowCount();
            logActivity(null, 'clear_logs', "Activity logs cleared from $date_from to $date_to for department $department_id");
        } else {
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE DATE(created_at) BETWEEN ? AND ?");
            $stmt->execute([$date_from, $date_to]);
            $deleted = $stmt->rowCount();
            logActivity(null, 'clear_logs', "Activity logs cleared from $date_from to $date_to");
        }
    } elseif ($date_from) {
        if ($is_department_admin && $department_id) {
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE DATE(created_at) >= ? AND (department_id = ? OR related_department_id = ?)");
            $stmt->execute([$date_from, $department_id, $department_id]);
            $deleted = $stmt->rowCount();
            logActivity(null, 'clear_logs', "Activity logs cleared from $date_from onwards for department $department_id");
        } else {
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE DATE(created_at) >= ?");
            $stmt->execute([$date_from]);
            $deleted = $stmt->rowCount();
            logActivity(null, 'clear_logs', "Activity logs cleared from $date_from onwards");
        }
    } elseif ($date_to) {
        if ($is_department_admin && $department_id) {
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE DATE(created_at) <= ? AND (department_id = ? OR related_department_id = ?)");
            $stmt->execute([$date_to, $department_id, $department_id]);
            $deleted = $stmt->rowCount();
            logActivity(null, 'clear_logs', "Activity logs cleared up to $date_to for department $department_id");
        } else {
            $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE DATE(created_at) <= ?");
            $stmt->execute([$date_to]);
            $deleted = $stmt->rowCount();
            logActivity(null, 'clear_logs', "Activity logs cleared up to $date_to");
        }
    }

    echo json_encode([
        'success' => true,
        'deleted' => $deleted,
        'message' => 'Logs cleared successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error clearing logs: ' . $e->getMessage()
    ]);
}