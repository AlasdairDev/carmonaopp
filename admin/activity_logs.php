<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}


// Pagination
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Filters
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$user_filter = isset($_GET['user']) ? $_GET['user'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where = [];
$params = [];
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

// Get total count
$count_sql = "SELECT COUNT(*) FROM activity_logs al $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Get logs
$sql = "
    SELECT al.*, u.name, u.email
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    $where_clause
    ORDER BY al.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $pdo->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get unique actions for filter
$actions = $pdo->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
// Get users for filter
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();

$dept_where_stats = '';
$dept_params_stats = [];
if (isDepartmentAdmin()) {
    $dept_id = $_SESSION['department_id'];
    $dept_where_stats = " WHERE (department_id = ? OR related_department_id = ?)";
    $dept_params_stats = [$dept_id, $dept_id];
}

$stmt_total = $pdo->prepare("SELECT COUNT(*) FROM activity_logs" . $dept_where_stats);
$stmt_total->execute($dept_params_stats);

$stmt_today = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()" . ($dept_where_stats ? str_replace('WHERE', 'AND', $dept_where_stats) : ''));
$stmt_today->execute($dept_params_stats);

$stmt_week = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE YEARWEEK(created_at) = YEARWEEK(NOW())" . ($dept_where_stats ? str_replace('WHERE', 'AND', $dept_where_stats) : ''));
$stmt_week->execute($dept_params_stats);

$stats = [
    'total' => $stmt_total->fetchColumn(),
    'today' => $stmt_today->fetchColumn(),
    'this_week' => $stmt_week->fetchColumn(),
];

$pageTitle = 'Activity Logs';
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
    <link rel="stylesheet" href="../assets/css/admin/activity_logs_styles.css">

</head>

<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Activity Logs</h1>
                <p>Monitor all system activities and user actions</p>
            </div>
            <div class="header-actions">
                <button onclick="exportLogs()" class="btn btn-white">
                    <i class="fas fa-download"></i> Export CSV
                </button>
                <button onclick="clearOldLogs()" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Clear Old Logs
                </button>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Logs</h3>
                <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Today</h3>
                <div class="stat-value"><?php echo $stats['today']; ?></div>
            </div>
            <div class="stat-card">
                <h3>This Week</h3>
                <div class="stat-value"><?php echo $stats['this_week']; ?></div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-header">
                <h3>
                    <i class="fas fa-filter"></i>
                    Filter Activity Logs
                </h3>
            </div>

            <form id="filterForm" onsubmit="applyFilter(event)">
                <div class="filters-grid">
                    <!-- Action Type -->
                    <div class="filter-group">
                        <label>Action Type</label>
                        <select name="action" class="form-control">
                            <option value="" <?php echo ($action_filter === '') ? 'selected' : ''; ?>>All Actions</option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?php echo htmlspecialchars($action); ?>" <?php echo ($action_filter === $action) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($action); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- User -->
                    <div class="filter-group">
                        <label>User</label>
                        <select name="user" class="form-control">
                            <option value="" <?php echo ($user_filter === '') ? 'selected' : ''; ?>>All Users</option>
                            <option value="system" <?php echo ($user_filter === 'system') ? 'selected' : ''; ?>>System
                            </option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo ($user_filter == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>"
                            max="<?php echo date('m-d-Y'); ?>">
                    </div>

                    <div class="filter-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>"
                            max="<?php echo date('m-d-Y'); ?>">
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

        <!-- Results Info -->
        <div class="results-info">
            <div class="results-count">
                Showing <strong><?php echo count($logs); ?></strong> of
                <strong><?php echo number_format($total); ?></strong> logs
            </div>
        </div>

        <!-- Activity Logs Table -->
        <?php if (empty($logs)): ?>
            <div class="table-card">
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h3>No Activity Logs Found</h3>
                    <p>Try adjusting your filters</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-card">
                <div class="table-container">
                    <table class="modern-table" id="logsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th style="text-align: center;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <div><?php echo date('M d, Y', strtotime($log['created_at'])); ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                            <?php echo date('h:i:s A', strtotime($log['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($log['user_id']): ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($log['name']); ?></strong>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($log['email']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--text-secondary);">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo getActionBadgeClass($log['action']); ?>">
                                            <?php echo htmlspecialchars($log['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['description']); ?></td>
                                    <td>
                                        <code><?php echo $log['ip_address'] ? htmlspecialchars($log['ip_address']) : 'N/A'; ?></code>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($log['details']): ?>
                                            <button onclick="showDetails(this)"
                                                data-details="<?php echo $log['details'] ? htmlspecialchars($log['details']) : ''; ?>"
                                                class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">
                                                <i class="fas fa-info-circle"></i> View
                                            </button>
                                        <?php else: ?>
                                            <span style="color: var(--text-secondary);">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <?php
                        $prev_params = $_GET;
                        $prev_params['page'] = $page - 1;
                        ?>
                        <a href="?<?php echo http_build_query($prev_params); ?>" class="page-btn">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);

                    for ($i = $start; $i <= $end; $i++):
                        $page_params = $_GET;
                        $page_params['page'] = $i;
                        ?>
                        <a href="?<?php echo http_build_query($page_params); ?>"
                            class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <?php
                        $next_params = $_GET;
                        $next_params['page'] = $page + 1;
                        ?>
                        <a href="?<?php echo http_build_query($next_params); ?>" class="page-btn">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header"
                style="padding: 1.5rem; background: linear-gradient(135deg, rgba(139, 195, 74, 0.05) 0%, rgba(102, 187, 106, 0.05) 100%);">
                <h3
                    style="margin: 0; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                    Log Details
                </h3>
                <button class="close-modal" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div style="padding: 1.5rem;" id="detailsContainer">
                <!-- Details will be inserted here -->
            </div>
        </div>
    </div>
    <div id="feedbackModal" class="modal">
        <div class="modal-content" style="max-width: 500px; text-align: center;">
            <div style="padding: 2rem;">
                <div id="feedbackIcon" style="font-size: 3rem; margin-bottom: 1rem;"></div>
                <h2 id="feedbackTitle" style="margin-bottom: 0.5rem;"></h2>
                <p id="feedbackMessage" style="color: var(--text-secondary); margin-bottom: 1.5rem;"></p>
                <button onclick="closeFeedbackModal()" class="btn"
                    style="background: var(--primary) !important; color: white !important; border: none !important;">OK</button>
            </div>
        </div>
    </div>
    <!-- Confirm Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div style="text-align: center; padding: 2rem;">
                <div id="confirmIcon" style="font-size: 4rem; margin-bottom: 1rem;"></div>
                <h2 id="confirmTitle" style="margin-bottom: 1rem; font-size: 1.5rem;"></h2>
                <p id="confirmMessage" style="color: var(--text-secondary); margin-bottom: 2rem; font-size: 1rem;"></p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button onclick="closeConfirmModal()" class="btn btn-secondary" style="min-width: 120px;">
                        Cancel
                    </button>
                    <button id="confirmActionBtn" class="btn" style="min-width: 120px;">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Clear Logs Modal -->
    <div id="clearLogsModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Clear Activity Logs</h3>
                <button class="close-modal" onclick="closeClearLogsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div
                        style="width: 80px; height: 80px; margin: 0 auto 1rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; background: #ef4444;">
                        <i class="fas fa-trash-alt" style="color: white;"></i>
                    </div>
                    <p style="color: var(--text-secondary);">
                        Choose a date range to clear specific logs, or leave blank to clear all logs.
                    </p>
                </div>

                <div style="display: grid; gap: 1rem;">
                    <div class="filter-group">
                        <label>Clear From Date</label>
                        <input type="date" id="clearDateFrom" class="form-control">
                    </div>
                    <div class="filter-group">
                        <label>Clear To Date </label>
                        <input type="date" id="clearDateTo" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button onclick="closeClearLogsModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button onclick="confirmClearLogs()" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Clear Logs
                </button>
            </div>
        </div>
    </div>
    <script>
        function showDetails(button) {
            const detailsJson = button.getAttribute('data-details');
            const container = document.getElementById('detailsContainer');

            try {
                const details = JSON.parse(detailsJson);
                let html = '';

                // Check if details is empty
                if (Object.keys(details).length === 0) {
                    html = '<div class="empty-details"><i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3; margin-bottom: 0.5rem;"></i><p>No additional details available</p></div>';
                } else {
                    // Loop through each property and create organized cards
                    for (const [key, value] of Object.entries(details)) {
                        const formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        const formattedValue = typeof value === 'object' ? JSON.stringify(value, null, 2) : value;

                        html += `
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-tag" style="margin-right: 0.25rem;"></i>
                            ${formattedKey}
                        </div>
                        <div class="detail-value">${formattedValue}</div>
                    </div>
                `;
                    }
                }

                container.innerHTML = html;
            } catch (e) {
                container.innerHTML = `
            <div class="detail-item">
                <div class="detail-label">
                    <i class="fas fa-file-alt" style="margin-right: 0.25rem;"></i>
                    Raw Data
                </div>
                <div class="detail-value">${detailsJson}</div>
            </div>
        `;
            }

            document.getElementById('detailsModal').classList.add('show');
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('show');
        }

        function exportLogs() {
            const formData = new FormData(document.getElementById('filterForm'));
            const params = new URLSearchParams();

            if (formData.get('action')) params.append('action', formData.get('action'));
            if (formData.get('user')) params.append('user', formData.get('user'));
            if (formData.get('date_from')) params.append('date_from', formData.get('date_from'));
            if (formData.get('date_to')) params.append('date_to', formData.get('date_to'));

            window.location.href = '../api/export_logs.php?' + params.toString();
        }

        function clearOldLogs() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('clearDateFrom').setAttribute('max', today);
            document.getElementById('clearDateTo').setAttribute('max', today);

            document.getElementById('clearDateFrom').value = '';
            document.getElementById('clearDateTo').value = '';

            document.getElementById('clearLogsModal').classList.add('show');
        }

        function closeClearLogsModal() {
            document.getElementById('clearLogsModal').classList.remove('show');
        }

        async function confirmClearLogs() {
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

            closeClearLogsModal();

            const button = document.querySelector('button[onclick*="clearOldLogs"]');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';

            try {
                const payload = {};

                // Only include dates if they are provided
                if (dateFrom) payload.date_from = dateFrom;
                if (dateTo) payload.date_to = dateTo;

                // Add department_id from PHP session
                payload.department_id = <?php echo json_encode($_SESSION['department_id'] ?? null); ?>;
                payload.is_department_admin = <?php echo json_encode(isDepartmentAdmin()); ?>;

                const response = await fetch('../api/clear_logs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    const message = (dateFrom && dateTo)
                        ? `Logs from ${dateFrom} to ${dateTo} cleared successfully! Deleted: ${result.deleted} logs`
                        : dateFrom || dateTo
                            ? `Logs cleared successfully! Deleted: ${result.deleted} logs`
                            : `All logs cleared successfully! Deleted: ${result.deleted} logs`;

                    showFeedbackModal('Success', message, 'success', true);
                } else {
                    showFeedbackModal('Error', result.message, 'error');
                }
            } catch (error) {
                console.error('Clear logs error:', error);
                showFeedbackModal('Error', 'An error occurred. Please try again.', 'error');
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        function showFeedbackModal(title, message, type = 'error', reloadOnClose = false) {
            const icons = {
                'success': '<i class="fas fa-check-circle"></i>',
                'error': '<i class="fas fa-times-circle"></i>',
                'warning': '<i class="fas fa-exclamation-triangle"></i>',
                'info': '<i class="fas fa-info-circle"></i>'
            };

            const colors = {
                'success': '#22c55e',
                'error': '#ef4444',
                'warning': '#ff9800',
                'info': '#3b82f6'
            };

            document.getElementById('feedbackTitle').textContent = title;
            document.getElementById('feedbackMessage').textContent = message;
            document.getElementById('feedbackIcon').innerHTML = icons[type] || icons['info'];
            document.getElementById('feedbackIcon').style.color = colors[type] || colors['info'];

            // Store reload flag in modal dataset
            document.getElementById('feedbackModal').dataset.reloadOnClose = reloadOnClose;

            document.getElementById('feedbackModal').classList.add('active');
        }

        function closeFeedbackModal() {
            const modal = document.getElementById('feedbackModal');
            const shouldReload = modal.dataset.reloadOnClose === 'true';

            modal.classList.remove('active');

            if (shouldReload) {
                location.reload();
            }
        }

        function showConfirmModal(title, message, icon, iconColor, onConfirm, confirmBtnText = 'Confirm', confirmBtnClass = 'btn-primary') {
            document.getElementById('confirmTitle').textContent = title;
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmIcon').innerHTML = icon;
            document.getElementById('confirmIcon').style.color = iconColor;

            const confirmBtn = document.getElementById('confirmActionBtn');
            confirmBtn.textContent = confirmBtnText;
            confirmBtn.className = 'btn ' + confirmBtnClass;
            confirmBtn.onclick = function () {
                closeConfirmModal();
                onConfirm();
            };

            document.getElementById('confirmModal').classList.add('active');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('active');
        }

        function applyFilter(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const dateFrom = formData.get('date_from');
            const dateTo = formData.get('date_to');
            const user = formData.get('user');
            const action = formData.get('action');

            // Validate dates
            if (!validateDateRange(dateFrom, dateTo)) {
                showFeedbackModal(
                    'Invalid Date Range',
                    '"Date From" cannot be later than "Date To"',
                    'error'
                );
                return;
            }

            // Build query string with converted dates
            const params = new URLSearchParams();
            if (dateFrom) params.append('date_from', convertToBackendDate(dateFrom));
            if (dateTo) params.append('date_to', convertToBackendDate(dateTo));
            if (user) params.append('user', user);
            if (action) params.append('action', action);

            // Fetch filtered data
            fetch(`activity_logs.php?${params.toString()}`)
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

                    // Update results count
                    const newResultsInfo = doc.querySelector('.results-info');
                    const currentResultsInfo = document.querySelector('.results-info');
                    if (newResultsInfo && currentResultsInfo) {
                        currentResultsInfo.innerHTML = newResultsInfo.innerHTML;
                    }

                    // Update the entire table card
                    const newTableCard = doc.querySelector('.table-card');
                    const currentTableCard = document.querySelector('.table-card');
                    if (newTableCard && currentTableCard) {
                        currentTableCard.innerHTML = newTableCard.innerHTML;
                    }

                    // Update pagination if it exists
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
                        'An error occurred while filtering. Please try again.',
                        'error'
                    );
                    console.error('Error:', error);
                });
        }

        window.onclick = function (event) {
            const feedbackModal = document.getElementById('feedbackModal');
            const confirmModal = document.getElementById('confirmModal');
            const clearLogsModal = document.getElementById('clearLogsModal');
            const detailsModal = document.getElementById('detailsModal');

            if (event.target === feedbackModal) {
                closeFeedbackModal();
            }
            if (event.target === confirmModal) {
                closeConfirmModal();
            }
            if (event.target === clearLogsModal) {
                closeClearLogsModal();
            }
            if (event.target === detailsModal) {
                closeDetailsModal();
            }
        }

        // Single form submit handler
        document.querySelector('#filterForm').addEventListener('submit', applyFilter);

        function resetFilters() {
            // Clear all form inputs
            document.querySelector('select[name="action"]').value = '';
            document.querySelector('select[name="user"]').value = '';
            document.querySelector('input[name="date_from"]').value = '';
            document.querySelector('input[name="date_to"]').value = '';

            // Fetch unfiltered data
            fetch('activity_logs.php')
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

                    // Update results count
                    const newResultsInfo = doc.querySelector('.results-info');
                    const currentResultsInfo = document.querySelector('.results-info');
                    if (newResultsInfo && currentResultsInfo) {
                        currentResultsInfo.innerHTML = newResultsInfo.innerHTML;
                    }

                    // Update the entire table card
                    const newTableCard = doc.querySelector('.table-card');
                    const currentTableCard = document.querySelector('.table-card');
                    if (newTableCard && currentTableCard) {
                        currentTableCard.innerHTML = newTableCard.innerHTML;
                    }

                    // Update pagination if it exists
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

        // Get today's date in YYYY-MM-DD format
        function getTodayFormatted() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // HTML5 date inputs already use YYYY-MM-DD format, so no conversion needed
        function convertToBackendDate(dateString) {
            return dateString; // Already in YYYY-MM-DD format
        }

        // Validate date range
        function validateDateRange(dateFrom, dateTo) {
            if (!dateFrom || !dateTo) return true;
            return new Date(dateFrom) <= new Date(dateTo);
        }

        // Set max date on page load to disable future dates
        document.addEventListener('DOMContentLoaded', function () {
            const today = getTodayFormatted();
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.setAttribute('max', today);
            });
        });
    </script>

</body>

</html>

<?php include '../includes/footer.php'; ?>

<?php
function getActionBadgeClass($action)
{
    $classes = [
        'login' => 'success',
        'logout' => 'secondary',
        'register' => 'info',
        'create' => 'primary',
        'update' => 'warning',
        'delete' => 'danger',
        'approve' => 'success',
        'reject' => 'danger',
        'submit' => 'primary'
    ];

    foreach ($classes as $key => $class) {
        if (stripos($action, $key) !== false) {
            return $class;
        }
    }

    return 'secondary';
}
?>
