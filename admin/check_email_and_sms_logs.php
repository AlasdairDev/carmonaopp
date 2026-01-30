<?php
/**
 * EMAIL & SMS LOGS MONITOR
 * Place in /admin/ as check_email_and_sms_logs.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Access denied');
}
// Pagination
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;
// Filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'email';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$user_filter = isset($_GET['user']) ? $_GET['user'] : '';
$pageTitle = 'Email & SMS Logs';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../assets/css/admin-responsive.css">
    <link rel="stylesheet" href="../assets/css/admin/check_email_and_sms_logs_styles.css">

</head>

<body>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <h1></i> Email & SMS Logs</h1>
                <p>Track and analyze all email and SMS notifications</p>
            </div>
            <div class="header-actions">
                <button onclick="exportLogs()" class="btn btn-white">
                    <i class="fas fa-download"></i> Export CSV
                </button>
                <button onclick="clearLogs()" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Clear Old Logs
                </button>
            </div>
        </div>

        <?php
        try {
            // Check if tables exist
            $email_table_exists = $pdo->query("SHOW TABLES LIKE 'email_logs'")->rowCount() > 0;
            $sms_table_exists = $pdo->query("SHOW TABLES LIKE 'sms_logs'")->rowCount() > 0;

            if (!$email_table_exists && !$sms_table_exists) {
                echo '<div class="alert alert-error">';
                echo '<i class="fas fa-exclamation-circle"></i>';
                echo '<div><strong>Tables Not Found!</strong><br>Neither email_logs nor sms_logs tables exist in your database.</div>';
                echo '</div>';
            } else {
                // Get statistics
                $email_stats = ['total' => 0, 'sent' => 0, 'failed' => 0, 'pending' => 0];
                $sms_stats = ['total' => 0, 'sent' => 0, 'failed' => 0, 'pending' => 0];

                if ($email_table_exists) {
                    $dept_where_stats = '';
                    $dept_params_stats = [];

                    if (isDepartmentAdmin()) {
                        $dept_id = $_SESSION['department_id'];
                        $dept_where_stats = " WHERE department_id = ?";
                        $dept_params_stats = [$dept_id];
                    }

                    $query = "SELECT 
                        COUNT(*) as total,
                        COALESCE(SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END), 0) as sent,
                        COALESCE(SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END), 0) as failed,
                        COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending
                    FROM email_logs" . $dept_where_stats;

                    if ($date_from && $date_to) {
                        $query .= ($dept_where_stats ? " AND" : " WHERE") . " DATE(created_at) BETWEEN ? AND ?";
                        $stmt = $pdo->prepare($query);
                        $all_params = array_merge($dept_params_stats, [$date_from, $date_to]);
                        $stmt->execute($all_params);
                    } else {
                        $stmt = $pdo->prepare($query);
                        $stmt->execute($dept_params_stats);
                    }

                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $email_stats = [
                            'total' => $result['total'] ?? 0,
                            'sent' => $result['sent'] ?? 0,
                            'failed' => $result['failed'] ?? 0,
                            'pending' => $result['pending'] ?? 0
                        ];
                    }
                }


                if ($sms_table_exists) {
                    $dept_where_stats = '';
                    $dept_params_stats = [];

                    if (isDepartmentAdmin()) {
                        $dept_id = $_SESSION['department_id'];
                        $dept_where_stats = " WHERE department_id = ?";
                        $dept_params_stats = [$dept_id];
                    }

                    $query = "SELECT 
                        COUNT(*) as total,
                        COALESCE(SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END), 0) as sent,
                        COALESCE(SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END), 0) as failed,
                        COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending
                    FROM sms_logs" . $dept_where_stats;

                    if ($date_from && $date_to) {
                        $query .= ($dept_where_stats ? " AND" : " WHERE") . " DATE(created_at) BETWEEN ? AND ?";
                        $stmt = $pdo->prepare($query);
                        $all_params = array_merge($dept_params_stats, [$date_from, $date_to]);
                        $stmt->execute($all_params);
                    } else {
                        $stmt = $pdo->prepare($query);
                        $stmt->execute($dept_params_stats);
                    }

                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $sms_stats = [
                            'total' => $result['total'] ?? 0,
                            'sent' => $result['sent'] ?? 0,
                            'failed' => $result['failed'] ?? 0,
                            'pending' => $result['pending'] ?? 0
                        ];
                    }
                }

                $display_stats = $type_filter === 'sms' ? $sms_stats : $email_stats;
                $users = $pdo->query("SELECT id, name FROM users WHERE role != 'admin' ORDER BY name")->fetchAll();
                ?>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <div class="stat-icon stat-icon-primary">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        <div class="stat-content">
                            <h3>Total Messages</h3>
                            <div class="stat-value"><?php echo number_format($display_stats['total']); ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <div class="stat-icon stat-icon-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-content">
                            <h3>Sent</h3>
                            <div class="stat-value"><?php echo number_format($display_stats['sent']); ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <div class="stat-icon stat-icon-danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="stat-content">
                            <h3>Failed</h3>
                            <div class="stat-value"><?php echo number_format($display_stats['failed']); ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <div class="stat-icon stat-icon-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-content">
                            <h3>Pending</h3>
                            <div class="stat-value"><?php echo number_format($display_stats['pending']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-card">
                    <div class="filters-header">
                        <i class="fas fa-filter"></i>
                        <h3>Filter Logs</h3>
                    </div>

                    <form method="GET" action="" id="filterForm" onsubmit="applyFilter(event)">
                        <div class="filters-grid">
                            <div class="filter-group">
                                <label>Type</label>
                                <select name="type">
                                    <option value="email" <?php echo $type_filter === 'email' ? 'selected' : ''; ?>>Email</option>
                                    <option value="sms" <?php echo $type_filter === 'sms' ? 'selected' : ''; ?>>SMS</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="">All Statuses</option>
                                    <option value="sent" <?php echo $status_filter === 'sent' ? 'selected' : ''; ?>>Sent</option>
                                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed
                                    </option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending
                                    </option>
                                </select>
                            </div>

                            <!-- ADD THIS NEW USER FILTER -->
                            <div class="filter-group">
                                <label>User</label>
                                <select name="user">
                                    <option value="" <?php echo ($user_filter === '') ? 'selected' : ''; ?>>All Users</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo ($user_filter == $user['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" value="<?php echo $date_from; ?>">
                            </div>

                            <div class="filter-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" value="<?php echo $date_to; ?>">
                            </div>

                            <div class="filter-group">
                                <button type="button" onclick="resetFilters()" class="btn btn-secondary">
                                    <i class="fas fa-sync-alt"></i> Reset
                                </button>
                            </div>

                            <div class="filter-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <?php
                // Initialize variables for count query
                $total = 0;
                $total_pages = 0;

                // Fetch logs based on type
                $table = $type_filter === 'sms' ? 'sms_logs' : 'email_logs';

                if (($type_filter === 'email' && $email_table_exists) || ($type_filter === 'sms' && $sms_table_exists)) {
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

                    // Get total count
                    $count_sql = "SELECT COUNT(*) FROM $table WHERE $where_clause";
                    $count_stmt = $pdo->prepare($count_sql);
                    $count_stmt->execute($params);
                    $total = $count_stmt->fetchColumn();
                    $total_pages = ceil($total / $per_page);
                }
                ?>

                <!-- Results Info -->
                <?php if ($total > 0): ?>
                    <div class="results-info">
                        Showing <strong><?php
                        $start = $offset + 1;
                        $end = min($offset + $per_page, $total);
                        echo number_format($start) . '-' . number_format($end);
                        ?></strong> of <strong><?php echo number_format($total); ?></strong> logs
                    </div>
                <?php endif; ?>

                <!-- Logs Table -->
                <div class="content-card">
                    <div class="content-header">
                        <h2>
                            <i class="fas fa-<?php echo $type_filter === 'sms' ? 'sms' : 'envelope'; ?>"></i>
                            <?php echo ucfirst($type_filter); ?> Logs
                        </h2>
                        <span class="last-updated">
                            Last updated: <?php echo date('M d, Y h:i A'); ?>
                        </span>
                    </div>

                    <div class="table-container">
                        <?php
                        if (($type_filter === 'email' && $email_table_exists) || ($type_filter === 'sms' && $sms_table_exists)) {
                            // Rebuild params for the data query (must match the count query)
                            $params = [];

                            // DEPARTMENT FILTERING
                            if (isDepartmentAdmin()) {
                                $dept_id = $_SESSION['department_id'];
                                $params[] = $dept_id;
                            }

                            if ($date_from && $date_to) {
                                $params[] = $date_from;
                                $params[] = $date_to;
                            }
                            if (!empty($status_filter)) {
                                $params[] = $status_filter;
                            }
                            if (!empty($user_filter)) {
                                $params[] = $user_filter;
                            }

                            // Get logs with pagination
                            $query = "SELECT * FROM $table WHERE $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
                            $stmt = $pdo->prepare($query);
                            $params[] = $per_page;
                            $params[] = $offset;
                            $stmt->execute($params);
                            $logs = $stmt->fetchAll();

                            if (empty($logs)) {
                                echo '<div class="empty-state">';
                                echo '<i class="fas fa-inbox"></i>';
                                echo '<h3>No logs found</h3>';
                                echo '<p>No ' . $type_filter . ' logs match your current filters.</p>';
                                echo '</div>';
                            } else {
                                echo '<table>';
                                echo '<thead><tr>';
                                echo '<th>Date & Time</th>';

                                if ($type_filter === 'sms') {
                                    echo '<th>Phone Number</th>';
                                    echo '<th>Message</th>';
                                } else {
                                    echo '<th>Recipient</th>';
                                    echo '<th>Subject</th>';
                                }

                                echo '<th>Status</th>';

                                if ($type_filter === 'email') {
                                    echo '<th>Error</th>';
                                }

                                echo '<th style="text-align: center;">Details</th>'; 
                
                                echo '</tr></thead>';
                                echo '<tbody>';

                                foreach ($logs as $log) {
                                    echo '<tr>';
                                    echo '<td style="white-space: nowrap;">' . date('M d, Y h:i A', strtotime($log['created_at'])) . '</td>';

                                    if ($type_filter === 'sms') {
                                        echo '<td>' . htmlspecialchars($log['phone_number']) . '</td>';
                                        echo '<td>' . htmlspecialchars(substr($log['message'], 0, 80)) . '...</td>';
                                    } else {
                                        echo '<td>' . htmlspecialchars($log['recipient']) . '</td>';
                                        echo '<td>' . htmlspecialchars(substr($log['subject'], 0, 60)) . '...</td>';
                                    }

                                    echo '<td><span class="status-badge status-' . $log['status'] . '">' . strtoupper($log['status']) . '</span></td>';

                                    if ($type_filter === 'email') {
                                        echo '<td style="font-size: 0.8125rem;">' . ($log['error_message'] ? htmlspecialchars(substr($log['error_message'], 0, 60)) . '...' : '-') . '</td>';
                                    }
                                    echo '<td style="text-align: center;">';
                                    if ($type_filter === 'sms') {
                                        echo '<button onclick="showDetails(this)" ';
                                        echo 'data-type="sms" ';
                                        echo 'data-phone="' . htmlspecialchars($log['phone_number']) . '" ';
                                        echo 'data-message="' . htmlspecialchars($log['message']) . '" ';
                                        echo 'data-status="' . htmlspecialchars($log['status']) . '" ';
                                        echo 'data-date="' . date('M d, Y h:i A', strtotime($log['created_at'])) . '" ';
                                        echo 'class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">';
                                        echo '<i class="fas fa-info-circle"></i> View';
                                        echo '</button>';
                                    } else {
                                        echo '<button onclick="showDetails(this)" ';
                                        echo 'data-type="email" ';
                                        echo 'data-recipient="' . htmlspecialchars($log['recipient']) . '" ';
                                        echo 'data-subject="' . htmlspecialchars($log['subject']) . '" ';
                                        echo 'data-status="' . htmlspecialchars($log['status']) . '" ';
                                        echo 'data-error="' . htmlspecialchars($log['error_message'] ?? '') . '" ';
                                        echo 'data-date="' . date('M d, Y h:i A', strtotime($log['created_at'])) . '" ';
                                        echo 'class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">';
                                        echo '<i class="fas fa-info-circle"></i> View';
                                        echo '</button>';
                                    }
                                    echo '</td>';

                                    echo '</tr>';
                                }

                                echo '</tbody>';
                                echo '</table>';
                            }
                        }
                        ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                    class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-link">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
            }
        } catch (Exception $e) {
            echo '<div class="alert alert-error">';
            echo '<i class="fas fa-exclamation-triangle"></i>';
            echo '<div><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '</div>';
        }
        ?>
    </div>
    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <div class="modal-body">
                <div id="feedbackIcon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h3 id="feedbackTitle">Notification</h3>
                <p id="feedbackMessage"></p>
            </div>
            <div class="modal-footer">
                <button onclick="closeFeedbackModal()" class="btn btn-primary"
                    style="background: var(--primary) !important; color: white !important;">OK</button>
            </div>
        </div>
    </div>
    <!-- Clear Logs Modal -->
    <div id="clearLogsModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 2px solid var(--border);">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700;">Clear Logs</h3>
                <button class="close-modal" onclick="closeClearLogsModal()">&times;</button>
            </div>

            <div style="padding: 2rem; text-align: center;">
                <div
                    style="width: 80px; height: 80px; margin: 0 auto 1.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <i class="fas fa-trash-alt" style="color: white;"></i>
                </div>

                <p style="margin-bottom: 2rem; color: var(--text-secondary);">Choose a date range to clear specific
                    logs, or leave blank to clear all logs.</p>

                <div style="display: grid; gap: 1.5rem; text-align: left;">
                    <div class="filter-group">
                        <label>LOG TYPE TO CLEAR</label>
                        <select id="clearLogType" class="form-control">
                            <option value="both">Both (Email & SMS)</option>
                            <option value="email">Email Only</option>
                            <option value="sms">SMS Only</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>CLEAR FROM DATE (OPTIONAL)</label>
                        <input type="date" id="clearDateFrom" class="form-control">
                    </div>

                    <div class="filter-group">
                        <label>CLEAR TO DATE (OPTIONAL)</label>
                        <input type="date" id="clearDateTo" class="form-control">
                    </div>
                </div>
            </div>

            <div style="padding: 0 2rem 2rem 2rem; display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
                <button onclick="closeClearLogsModal()" class="btn btn-secondary"
                    style="width: 100%; margin: 0;">Cancel</button>
                <button onclick="confirmClearLogs()" class="btn btn-danger" style="width: 100%; margin: 0;"><i
                        class="fas fa-trash-alt"></i> Clear Logs</button>
            </div>
        </div>
    </div>
    <div id="detailsModal" class="modal">
        <div class="modal-content" style="max-width: 600px; text-align: left;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 2px solid var(--border);">
                <h3 id="detailsModalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 700;">Log Details</h3>
                <button class="close-modal" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div style="padding: 1.5rem;">
                <div id="detailsContent" style="line-height: 1.8; text-align: left;">
                    <!-- Content will be inserted here -->
                </div>
            </div>
        </div>
    </div>
    <script>
        function exportLogs() {
            // Get current URL parameters (these are the actually applied filters)
            const urlParams = new URLSearchParams(window.location.search);
            const params = new URLSearchParams();

            // Only include parameters that are in the current URL
            if (urlParams.get('type')) params.append('type', urlParams.get('type'));
            if (urlParams.get('status')) params.append('status', urlParams.get('status'));
            if (urlParams.get('user')) params.append('user', urlParams.get('user'));
            if (urlParams.get('date_from')) params.append('date_from', urlParams.get('date_from'));
            if (urlParams.get('date_to')) params.append('date_to', urlParams.get('date_to'));

            window.location.href = '../api/export_email_sms_logs.php?' + params.toString();
        }
        function clearLogs() {
            // Set max date to today for the modal date inputs
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('clearDateFrom').setAttribute('max', today);
            document.getElementById('clearDateTo').setAttribute('max', today);

            // Clear any previous values
            document.getElementById('clearLogType').value = 'both';
            document.getElementById('clearDateFrom').value = '';
            document.getElementById('clearDateTo').value = '';

            // Show modal
            document.getElementById('clearLogsModal').classList.add('active');
        }
        function closeClearLogsModal() {
            document.getElementById('clearLogsModal').classList.remove('active');
        }

        function confirmClearLogs() {
            const logType = document.getElementById('clearLogType').value;
            const dateFrom = document.getElementById('clearDateFrom').value;
            const dateTo = document.getElementById('clearDateTo').value;

            // Validate date range if both dates are provided
            if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
                closeClearLogsModal();
                showFeedbackModal(
                    'Invalid Date Range',
                    '"Clear From Date" cannot be later than "Clear To Date"',
                    'error'
                );
                return;
            }

            // Build the clear URL
            let clearUrl = '?clear=1';
            clearUrl += `&log_type=${logType}`;
            if (dateFrom) clearUrl += `&date_from=${dateFrom}`;
            if (dateTo) clearUrl += `&date_to=${dateTo}`;

            closeClearLogsModal();
            window.location.href = clearUrl;
        }
        <?php
        if (isset($_GET['clear']) && $_GET['clear'] == '1') {
            try {
                $clear_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
                $clear_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
                $log_type = isset($_GET['log_type']) ? $_GET['log_type'] : 'both';

                // Clear Email Logs
                if (($log_type === 'both' || $log_type === 'email') && $email_table_exists) {
                    if (isDepartmentAdmin()) {
                        $dept_id = $_SESSION['department_id'];
                        if ($clear_date_from && $clear_date_to) {
                            $stmt = $pdo->prepare("DELETE FROM email_logs WHERE DATE(created_at) BETWEEN ? AND ? AND department_id = ?");
                            $stmt->execute([$clear_date_from, $clear_date_to, $dept_id]);
                        } else {
                            $stmt = $pdo->prepare("DELETE FROM email_logs WHERE department_id = ?");
                            $stmt->execute([$dept_id]);
                        }
                    } else {
                        if ($clear_date_from && $clear_date_to) {
                            $stmt = $pdo->prepare("DELETE FROM email_logs WHERE DATE(created_at) BETWEEN ? AND ?");
                            $stmt->execute([$clear_date_from, $clear_date_to]);
                        } else {
                            $pdo->exec("TRUNCATE TABLE email_logs");
                        }
                    }
                }

                // Clear SMS Logs
                if (($log_type === 'both' || $log_type === 'sms') && $sms_table_exists) {
                    if (isDepartmentAdmin()) {
                        $dept_id = $_SESSION['department_id'];
                        if ($clear_date_from && $clear_date_to) {
                            $stmt = $pdo->prepare("DELETE FROM sms_logs WHERE DATE(created_at) BETWEEN ? AND ? AND department_id = ?");
                            $stmt->execute([$clear_date_from, $clear_date_to, $dept_id]);
                        } else {
                            $stmt = $pdo->prepare("DELETE FROM sms_logs WHERE department_id = ?");
                            $stmt->execute([$dept_id]);
                        }
                    } else {
                        if ($clear_date_from && $clear_date_to) {
                            $stmt = $pdo->prepare("DELETE FROM sms_logs WHERE DATE(created_at) BETWEEN ? AND ?");
                            $stmt->execute([$clear_date_from, $clear_date_to]);
                        } else {
                            $pdo->exec("TRUNCATE TABLE sms_logs");
                        }
                    }
                }

                // Build success message
                $type_text = $log_type === 'both' ? 'Email & SMS' : ucfirst($log_type);
                $message = ($clear_date_from && $clear_date_to)
                    ? "{$type_text} logs from {$clear_date_from} to {$clear_date_to} cleared successfully!"
                    : "All {$type_text} logs cleared successfully!";

                echo 'showFeedbackModal("Success", "' . $message . '", "success"); setTimeout(() => { window.location.href="check_email_and_sms_logs.php"; }, 2000);';
            } catch (Exception $e) {
                echo 'showFeedbackModal("Error", "Error clearing logs: ' . addslashes($e->getMessage()) . '", "error");';
            }
        }
        ?>
        // Set max date to today to disable future dates
        document.addEventListener('DOMContentLoaded', function () {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.setAttribute('max', today);
            });
        });
        // Feedback Modal Functions
        function showFeedbackModal(title, message, type = 'info') {
            const iconMap = {
                'success': { icon: 'fa-check', bgColor: '#10b981' },
                'error': { icon: 'fa-times', bgColor: '#ef4444' },
                'warning': { icon: 'fa-exclamation', bgColor: '#fbbf24' },
                'info': { icon: 'fa-info', bgColor: '#3b82f6' }
            };

            const config = iconMap[type] || iconMap['info'];

            document.getElementById('feedbackTitle').textContent = title;
            document.getElementById('feedbackMessage').textContent = message;
            document.getElementById('feedbackIcon').innerHTML = `<i class="fas ${config.icon}"></i>`;
            document.getElementById('feedbackIcon').style.backgroundColor = config.bgColor;

            document.getElementById('feedbackModal').classList.add('active');
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const feedbackModal = document.getElementById('feedbackModal');
            const clearLogsModal = document.getElementById('clearLogsModal');
            const detailsModal = document.getElementById('detailsModal'); // ADD THIS

            if (event.target === feedbackModal) {
                closeFeedbackModal();
            }
            if (event.target === clearLogsModal) {
                closeClearLogsModal();
            }
            if (event.target === detailsModal) { // ADD THIS
                closeDetailsModal();
            }
        }
        // Apply filters without page reload
        function applyFilter(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const dateFrom = formData.get('date_from');
            const dateTo = formData.get('date_to');
            const type = formData.get('type');
            const status = formData.get('status');
            const user = formData.get('user'); 
            // Validate dates
            if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
                showFeedbackModal(
                    'Invalid Date Range',
                    '"Date From" cannot be later than "Date To"',
                    'error'
                );
                return;
            }

            // Build query string
            const params = new URLSearchParams();
            if (type) params.append('type', type);
            if (status) params.append('status', status);
            if (user) params.append('user', user); // ADD THIS
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            // Fetch filtered data
            fetch(`check_email_and_sms_logs.php?${params.toString()}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Update statistics cards
                    const newStats = doc.querySelectorAll('.stat-value');
                    const currentStats = document.querySelectorAll('.stat-value');
                    newStats.forEach((stat, index) => {
                        if (currentStats[index]) {
                            currentStats[index].textContent = stat.textContent;
                        }
                    });

                    // Update results info
                    const newResultsInfo = doc.querySelector('.results-info');
                    const currentResultsInfo = document.querySelector('.results-info');
                    if (newResultsInfo && currentResultsInfo) {
                        currentResultsInfo.innerHTML = newResultsInfo.innerHTML;
                    } else if (newResultsInfo && !currentResultsInfo) {
                        // Insert results info if it doesn't exist
                        const contentCard = document.querySelector('.content-card');
                        contentCard.insertAdjacentHTML('beforebegin', newResultsInfo.outerHTML);
                    } else if (!newResultsInfo && currentResultsInfo) {
                        currentResultsInfo.remove();
                    }

                    // Update the table container
                    const newTableContainer = doc.querySelector('.table-container');
                    const currentTableContainer = document.querySelector('.table-container');
                    if (newTableContainer && currentTableContainer) {
                        currentTableContainer.innerHTML = newTableContainer.innerHTML;
                    }

                    // Update pagination
                    const newPagination = doc.querySelector('.pagination');
                    const currentPagination = document.querySelector('.pagination');
                    if (newPagination && currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else if (newPagination && !currentPagination) {
                        document.querySelector('.content-card').insertAdjacentHTML('beforeend', `<div class="pagination">${newPagination.innerHTML}</div>`);
                    } else if (!newPagination && currentPagination) {
                        currentPagination.remove();
                    }
                })
                .catch(error => {
                    showFeedbackModal(
                        'Error',
                        'An error occurred while filtering. Please try again.',
                        'error'
                    );
                    console.error('Error:', error);
                });
        }

        // Reset filters without page reload
        function resetFilters() {
            // Clear all form inputs
            document.querySelector('select[name="type"]').value = 'email';
            document.querySelector('select[name="status"]').value = '';
            document.querySelector('select[name="user"]').value = ''; // ADD THIS
            document.querySelector('input[name="date_from"]').value = '';
            document.querySelector('input[name="date_to"]').value = '';

            // Fetch unfiltered data
            fetch('check_email_and_sms_logs.php')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Update statistics cards
                    const newStats = doc.querySelectorAll('.stat-value');
                    const currentStats = document.querySelectorAll('.stat-value');
                    newStats.forEach((stat, index) => {
                        if (currentStats[index]) {
                            currentStats[index].textContent = stat.textContent;
                        }
                    });

                    // Update results info
                    const newResultsInfo = doc.querySelector('.results-info');
                    const currentResultsInfo = document.querySelector('.results-info');
                    if (newResultsInfo && currentResultsInfo) {
                        currentResultsInfo.innerHTML = newResultsInfo.innerHTML;
                    } else if (!newResultsInfo && currentResultsInfo) {
                        currentResultsInfo.remove();
                    }

                    // Update the table container
                    const newTableContainer = doc.querySelector('.table-container');
                    const currentTableContainer = document.querySelector('.table-container');
                    if (newTableContainer && currentTableContainer) {
                        currentTableContainer.innerHTML = newTableContainer.innerHTML;
                    }

                    // Update pagination
                    const newPagination = doc.querySelector('.pagination');
                    const currentPagination = document.querySelector('.pagination');
                    if (newPagination && currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    } else if (!newPagination && currentPagination) {
                        currentPagination.remove();
                    }
                })
                .catch(error => {
                    showFeedbackModal(
                        'Error',
                        'An error occurred while resetting filters. Please try again.',
                        'error'
                    );
                    console.error('Error:', error);
                });
        }
        function showDetails(button) {
            const type = button.getAttribute('data-type');
            const date = button.getAttribute('data-date');
            const status = button.getAttribute('data-status');

            let content = '';

            if (type === 'sms') {
                const phone = button.getAttribute('data-phone');
                const message = button.getAttribute('data-message');

                document.getElementById('detailsModalTitle').textContent = 'SMS Log Details';
                content = `
            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Date & Time:</strong>
                <div>${date}</div>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Phone Number:</strong>
                <div>${phone}</div>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Status:</strong>
                <div><span class="status-badge status-${status}">${status.toUpperCase()}</span></div>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Full Message:</strong>
                <div style="background: var(--background); padding: 1rem; border-radius: 8px; white-space: pre-wrap; word-break: break-word;">${message}</div>
            </div>
        `;
            } else {
                const recipient = button.getAttribute('data-recipient');
                const subject = button.getAttribute('data-subject');
                const error = button.getAttribute('data-error');

                document.getElementById('detailsModalTitle').textContent = 'Email Log Details';
                content = `
            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Date & Time:</strong>
                <div>${date}</div>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Recipient:</strong>
                <div>${recipient}</div>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Status:</strong>
                <div><span class="status-badge status-${status}">${status.toUpperCase()}</span></div>
            </div>
            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Full Subject:</strong>
                <div style="background: var(--background); padding: 1rem; border-radius: 8px; word-break: break-word;">${subject}</div>
            </div>
            ${error ? `
            <div style="margin-bottom: 1rem;">
                <strong style="color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Error Message:</strong>
                <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; word-break: break-word;">${error}</div>
            </div>
            ` : ''}
        `;
            }

            document.getElementById('detailsContent').innerHTML = content;
            document.getElementById('detailsModal').classList.add('show');
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('show');
        }
    </script>

</body>

</html>

<?php include '../includes/footer.php'; ?>
