<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
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

if ($action_filter) {
    $where[] = "al.action = ?";
    $params[] = $action_filter;
}

if ($user_filter) {
    $where[] = "al.user_id = ?";
    $params[] = $user_filter;
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

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn(),
    'today' => $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
    'this_week' => $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE YEARWEEK(created_at) = YEARWEEK(NOW())")->fetchColumn(),
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #8bc34a;
            --primary-dark: #689f38;
            --primary-light: #dcedc8;
            --secondary: #558b2f;
            --background: #f5f7fa;
            --surface: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius: 12px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem 1.5rem 1.5rem;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--radius);
            padding: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: 1rem;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.75rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card:hover::before {
            width: 8px;
        }

        .stat-card h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .btn-white {
            background: white;
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(244, 67, 54, 0.3);
        }

        /* Filters Section */
        .filters-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border);
        }

        .filters-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: stretch;
}

        .filter-group label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: var(--background);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 195, 74, 0.1);
            background: white;
        }

        .filter-group .btn {
            width: 100%;
            margin-top: auto;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
        }

        /* Results Info */
        .results-info {
            background: var(--surface);
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .results-count {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .results-count strong {
            color: var(--text-primary);
            font-weight: 700;
        }

        /* Table */
        .table-card {
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }

        .modern-table thead {
            background: linear-gradient(135deg, rgba(139, 195, 74, 0.1) 0%, rgba(102, 187, 106, 0.1) 100%);
        }

        .modern-table th {
            padding: 1rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
        }

        .modern-table tbody tr {
            transition: background 0.2s ease;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
        }

        .modern-table tbody tr:hover {
            background: var(--background);
        }

        .modern-table td {
            padding: 1rem;
            font-size: 0.875rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-primary { background: #cce5ff; color: #004085; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .page-btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: white;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .page-btn:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }

        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: #f0f0f0;
            color: #333;
        }

        .modal-body {
            padding: 1rem;
        }

        .modal-body pre {
            background: var(--background);
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 0.875rem;
        }

        .modal-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .modal-actions .btn {
            width: 100%;
            margin: 0;
            padding: 0.75rem 1.5rem;
            text-align: center;
            box-sizing: border-box;
        }

        code {
            background: var(--background);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            color: #e83e8c;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .filters-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-grid .filter-group:nth-child(5),
            .filters-grid .filter-group:nth-child(6) {
                grid-column: span 1;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem 1rem 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .results-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .table-container {
                overflow-x: scroll;
            }
        }
        .btn-primary {
    border-radius: 8px;
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary) 100%);
}

.btn-secondary {
    border-radius: 8px;
}

.btn-secondary:hover {
    background: #cbd5e1;
    color: var(--text-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(226, 232, 240, 0.5);
}
.filters-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
    align-items: end;
}
    </style>
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

            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Action Type</label>
                        <select name="action" class="form-control">
                            <option value="">All Actions</option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?php echo htmlspecialchars($action); ?>" <?php echo $action_filter === $action ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($action); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>User</label>
                        <select name="user" class="form-control">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>

                    <div class="filter-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>

                    <div class="filter-group">
                        <a href="activity_logs.php" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
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
                Showing <strong><?php echo count($logs); ?></strong> of <strong><?php echo number_format($total); ?></strong> logs
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
                                        <code><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></code>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($log['details']): ?>
                                            <button onclick="showDetails(this)" 
                                                    data-details="<?php echo htmlspecialchars($log['details']); ?>" 
                                                    class="btn btn-secondary"
                                                    style="padding: 0.5rem 1rem; font-size: 0.75rem;">
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
                        <a href="?<?php echo http_build_query($page_params); ?>" class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
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
        <div class="modal-content">
            <div class="modal-header">
                <h3>Log Details</h3>
                <button class="close-modal" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <pre id="detailsContent"></pre>
            </div>
            <div class="modal-actions">
                <button onclick="closeDetailsModal()" class="btn btn-secondary">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
    function showDetails(button) {
        const detailsJson = button.getAttribute('data-details');
        try {
            const details = JSON.parse(detailsJson);
            document.getElementById('detailsContent').textContent = JSON.stringify(details, null, 2);
        } catch (e) {
            document.getElementById('detailsContent').textContent = detailsJson;
        }
        document.getElementById('detailsModal').classList.add('show');
    }

    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.remove('show');
    }

    function exportLogs() {
        const table = document.getElementById('logsTable');
        let csv = [];
        
        // Headers
        const headers = [];
        table.querySelectorAll('thead th').forEach(th => {
            if (th.textContent.trim() !== 'Details') {
                headers.push(th.textContent.trim());
            }
        });
        csv.push(headers.join(','));
        
        // Rows
        table.querySelectorAll('tbody tr').forEach(row => {
            const rowData = [];
            row.querySelectorAll('td').forEach((td, index) => {
                if (index < row.cells.length - 1) {
                    let text = td.textContent.trim()
                        .replace(/\n/g, ' ')
                        .replace(/"/g, '""');
                    rowData.push('"' + text + '"');
                }
            });
            csv.push(rowData.join(','));
        });
        
        const BOM = '\uFEFF';
        const csvContent = BOM + csv.join('\n');
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'activity_logs_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    async function clearOldLogs() {
        if (!confirm('⚠️ This will delete logs older than 90 days. Continue?')) {
            return;
        }
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
        
        try {
            const response = await fetch('../api/clear_logs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ days: 90 })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ Old logs cleared successfully! Deleted: ' + result.deleted + ' logs');
                location.reload();
            } else {
                alert('❌ Error: ' + result.message);
            }
        } catch (error) {
            console.error('Clear logs error:', error);
            alert('❌ An error occurred. Please try again.');
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    window.onclick = function(event) {
        const modal = document.getElementById('detailsModal');
        if (event.target === modal) {
            closeDetailsModal();
        }
    }
    // Validate date range
    document.querySelector('form').addEventListener('submit', function(e) {
        const dateFrom = document.querySelector('input[name="date_from"]').value;
        const dateTo = document.querySelector('input[name="date_to"]').value;
        
        if (dateFrom && dateTo) {
            const fromDate = new Date(dateFrom);
            const toDate = new Date(dateTo);
            
            if (fromDate > toDate) {
                e.preventDefault();
                alert('❌ Error: "Date From" cannot be later than "Date To"');
                return false;
            }
        }
    });

    console.log('✅ Activity logs page loaded successfully');
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>

<?php
function getActionBadgeClass($action) {
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