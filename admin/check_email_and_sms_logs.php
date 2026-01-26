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

// Filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'email';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$pageTitle = 'Email & SMS Logs';
include '../includes/header.php';

// Initialize variables
$email_table_exists = false;
$sms_table_exists = false;
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
            --secondary: #558b2f;
            --background: #f5f7fa;
            --surface: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #64748b;
            --border: #e2e8f0;
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

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header-content h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .header-content p {
            font-size: 1rem;
            opacity: 0.95;
        }

        .header-actions {
            position: relative;
            z-index: 1;
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .btn-white {
            background: rgba(255, 255, 255, 0.95);
            color: var(--primary);
        }

        .btn-white:hover {
            background: white;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            color: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(226, 232, 240, 0.5);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
            border-color: var(--primary);
        }

        .stat-icon-wrapper {
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon-primary {
            background: linear-gradient(135deg, #8bc34a 0%, #689f38 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        .stat-icon-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .stat-icon-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .stat-icon-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }

        .stat-content h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        /* Filters Card */
        .filters-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .filters-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }

        .filters-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 0.625rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            background: white;
            transition: all 0.2s ease;
            height: 42px;
            box-sizing: border-box;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 195, 74, 0.1);
        }

        .filter-group .btn {
            width: 100%;
            margin-top: auto;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
        }

        /* Content Card */
        .content-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .content-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-header h2 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .last-updated {
            color: var(--text-secondary);
            font-size: 0.8125rem;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 1rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.75rem;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            border-radius: 50px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-sent {
            background: #dcfce7;
            color: #166534;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.125rem;
            margin: 0 0 0.5rem 0;
            color: var(--text-primary);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-envelope-open-text"></i> Email & SMS Logs</h1>
            <p>Track and analyze all email and SMS notifications</p>
        </div>
        <div class="header-actions">
            <button onclick="location.reload()" class="btn btn-white">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button onclick="clearLogs()" class="btn btn-danger">
                <i class="fas fa-trash-alt"></i> Clear Logs
            </button>
            <a href="dashboard.php" class="btn btn-white">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
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
                $query = "SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END), 0) as sent,
                    COALESCE(SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END), 0) as failed,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending
                FROM email_logs";
                
                if ($date_from && $date_to) {
                    $query .= " WHERE DATE(created_at) BETWEEN ? AND ?";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$date_from, $date_to]);
                } else {
                    $stmt = $pdo->prepare($query);
                    $stmt->execute();
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
                $query = "SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END), 0) as sent,
                    COALESCE(SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END), 0) as failed,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending
                FROM sms_logs";
                
                if ($date_from && $date_to) {
                    $query .= " WHERE DATE(created_at) BETWEEN ? AND ?";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$date_from, $date_to]);
                } else {
                    $stmt = $pdo->prepare($query);
                    $stmt->execute();
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
                    <i class="fas fa-filter" style="color: var(--primary);"></i>
                    <h3>Filter Logs</h3>
                </div>

                <form method="GET" action="" id="filter-form">
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
                                <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
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
                            <a href="check_email_and_sms_logs.php" class="btn btn-secondary">
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

            <!-- Logs Table -->
            <div class="content-card">
                <div class="content-header">
                    <h2>
                        <i class="fas fa-<?php echo $type_filter === 'sms' ? 'comments' : 'envelope'; ?>"></i>
                        <?php echo ucfirst($type_filter); ?> Logs
                    </h2>
                    <span class="last-updated">
                        Last updated: <?php echo date('M d, Y h:i A'); ?>
                    </span>
                </div>

                <div class="table-container">
                    <?php
                    // Fetch logs based on type
                    $table = $type_filter === 'sms' ? 'sms_logs' : 'email_logs';
                    
                    if (($type_filter === 'email' && $email_table_exists) || ($type_filter === 'sms' && $sms_table_exists)) {
                        $query = "SELECT * FROM $table WHERE 1=1";
                        $params = [];
                        
                        if ($date_from && $date_to) {
                            $query .= " AND DATE(created_at) BETWEEN ? AND ?";
                            $params[] = $date_from;
                            $params[] = $date_to;
                        }
                        
                        if (!empty($status_filter)) {
                            $query .= " AND status = ?";
                            $params[] = $status_filter;
                        }
                        
                        $query .= " ORDER BY created_at DESC LIMIT 100";
                        
                        $stmt = $pdo->prepare($query);
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
                                
                                echo '</tr>';
                            }

                            echo '</tbody>';
                            echo '</table>';
                        }
                    }
                    ?>
                </div>
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

<script>
function clearLogs() {
    if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
        if (confirm('This will permanently delete all email and SMS logs. Continue?')) {
            window.location.href = '?clear=1';
        }
    }
}

// Validate date range
const filterForm = document.getElementById('filter-form');
if (filterForm) {
    filterForm.addEventListener('submit', function(e) {
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
}

<?php
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    try {
        if ($email_table_exists) {
            $pdo->exec("TRUNCATE TABLE email_logs");
        }
        if ($sms_table_exists) {
            $pdo->exec("TRUNCATE TABLE sms_logs");
        }
        echo 'alert("Logs cleared successfully!"); window.location.href="check_email_and_sms_logs.php";';
    } catch (Exception $e) {
        echo 'alert("Error clearing logs: ' . addslashes($e->getMessage()) . '");';
    }
}
?>
</script>

</body>
</html>

<?php include '../includes/footer.php'; ?>