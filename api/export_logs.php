<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    exit('Unauthorized');
}

// Get filters from query string
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$user_filter = isset($_GET['user']) ? $_GET['user'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where = [];
$params = [];
// DEPARTMENT FILTERING
if (isDepartmentAdmin()) {
    $dept_id = $_SESSION['department_id'];
    $where[] = "(al.department_id = ? OR al.related_department_id = ?)";
    $params[] = $dept_id;
    $params[] = $dept_id;
}

if ($action_filter) {
    $where[] = "al.action = ?";
    $params[] = $action_filter;
}

if ($user_filter) {
    if ($user_filter === 'system') {
        $where[] = "al.user_id IS NULL";
    } else {
        $where[] = "al.user_id = ?";
        $params[] = $user_filter;
    }
}

if ($date_from) {
    $where[] = "DATE(al.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where[] = "DATE(al.created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get ALL logs
$sql = "
    SELECT al.*, u.name, u.email
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    $where_clause
    ORDER BY al.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=activity_logs_' . date('Y-m-d_His') . '.csv');

// Output BOM for Excel UTF-8 support
echo "\xEF\xBB\xBF";

// Open output stream
$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, ['Timestamp', 'User', 'Email', 'Action', 'Description', 'IP Address']);

// Write data
foreach ($logs as $log) {
    $user = $log['user_id'] ? $log['name'] : 'System';
    $email = $log['user_id'] ? $log['email'] : 'N/A';

    fputcsv($output, [
        date('Y-m-d H:i:s', strtotime($log['created_at'])),
        $user,
        $email,
        $log['action'],
        $log['description'],
        $log['ip_address'] ?: 'N/A'
    ]);
}

fclose($output);
exit();
?>
