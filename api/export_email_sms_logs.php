<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Access denied');
}

// Get filter parameters
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'email';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$user_filter = isset($_GET['user']) ? $_GET['user'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$table = $type_filter === 'sms' ? 'sms_logs' : 'email_logs';
$where = ["1=1"];
$params = [];

// DEPARTMENT FILTERING
if (isDepartmentAdmin()) {
    $dept_id = $_SESSION['department_id'];
    $where[] = "department_id = ?";
    $params[] = $dept_id;
}

if ($date_from && $date_to) {
    $where[] = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
}

if (!empty($status_filter)) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($user_filter)) {
    $where[] = "user_id = ?";
    $params[] = $user_filter;
}

$where_clause = implode(' AND ', $where);

// Get logs
$sql = "SELECT * FROM $table WHERE $where_clause ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
$filename = $type_filter . '_logs_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 support
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// headers based on type
if ($type_filter === 'sms') {
    fputcsv($output, ['Date & Time', 'Phone Number', 'Message', 'Status', 'Created At']);

    foreach ($logs as $log) {
        fputcsv($output, [
            date('M d, Y h:i A', strtotime($log['created_at'])),
            $log['phone_number'],
            $log['message'],
            strtoupper($log['status']),
            $log['created_at']
        ]);
    }
} else {
    fputcsv($output, ['Date & Time', 'Recipient', 'Subject', 'Status', 'Error Message', 'Created At']);

    foreach ($logs as $log) {
        fputcsv($output, [
            date('M d, Y h:i A', strtotime($log['created_at'])),
            $log['recipient'],
            $log['subject'],
            strtoupper($log['status']),
            $log['error_message'] ?? '',
            $log['created_at']
        ]);
    }
}

fclose($output);
exit();
