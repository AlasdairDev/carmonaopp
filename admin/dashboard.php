<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    log_security_event('UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard');
    header('Location: ../auth/login.php');
    exit();
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    header('Location: ../auth/login.php?timeout=1');
    exit();
}

$_SESSION['last_activity'] = time();

try {
    // Get department filter for department admins
    $dept_filter = getDepartmentFilter('a');
    $dept_where = $dept_filter['where'] ? ' AND ' . $dept_filter['where'] : '';
    $dept_params = $dept_filter['params'];

    // Overall statistics with department filtering
    $overall_stats = [];

    // Total
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a WHERE 1=1" . $dept_where);
    $stmt->execute($dept_params);
    $overall_stats['total'] = $stmt->fetchColumn() ?: 0;

    // Pending
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a WHERE status = 'Pending'" . $dept_where);
    $stmt->execute($dept_params);
    $overall_stats['pending'] = $stmt->fetchColumn() ?: 0;

    // Processing
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a WHERE status = 'Processing'" . $dept_where);
    $stmt->execute($dept_params);
    $overall_stats['processing'] = $stmt->fetchColumn() ?: 0;

    // Approved
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a WHERE status = 'Approved'" . $dept_where);
    $stmt->execute($dept_params);
    $overall_stats['approved'] = $stmt->fetchColumn() ?: 0;

    // Paid
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a WHERE status = 'Paid'" . $dept_where);
    $stmt->execute($dept_params);
    $overall_stats['paid'] = $stmt->fetchColumn() ?: 0;

    // Completed
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a WHERE status = 'Completed'" . $dept_where);
    $stmt->execute($dept_params);
    $overall_stats['completed'] = $stmt->fetchColumn() ?: 0;

    // Rejected
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a WHERE status = 'Rejected'" . $dept_where);
    $stmt->execute($dept_params);
    $overall_stats['rejected'] = $stmt->fetchColumn() ?: 0;

    // Cancelled
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a WHERE status = 'Cancelled'" . $dept_where);
    $stmt->execute($dept_params);
    $overall_stats['cancelled'] = $stmt->fetchColumn() ?: 0;

    // Recent applications with department filtering
    $stmt = $pdo->prepare("
        SELECT a.*, u.name as applicant_name, s.service_name
        FROM applications a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN services s ON a.service_id = s.id
        WHERE 1=1" . $dept_where . "
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $stmt->execute($dept_params);
    $recent_apps = $stmt->fetchAll();

    // Applications by status with department filtering
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count
        FROM applications a
        WHERE 1=1" . $dept_where . "
        GROUP BY status
    ");
    $stmt->execute($dept_params);
    $status_breakdown = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);


} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    log_security_event('DATABASE_ERROR', $e->getMessage());
    die("Database Error: Please contact administrator.");
}

$pageTitle = 'Admin Dashboard';
include '../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/admin/dashboard_styles.css">
<link rel="stylesheet" href="../assets/css/admin-responsive.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header animate__animated animate__fadeInDown">
        <h1>
            <?php if (isSuperAdmin()): ?>
                <i class="fas fa-crown"></i> Super Admin Dashboard
            <?php elseif (isDepartmentAdmin()): ?>
                <i class="fas fa-building"></i> <?php echo htmlspecialchars(getAdminDepartmentName()); ?> Dashboard
            <?php else: ?>
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            <?php endif; ?>
        </h1>
        <p>
            Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!
        </p>
        <p>
            <span class="badge"><?php echo getRoleDisplayName(); ?></span>
        </p>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;"
            onclick="window.location.href='applications.php'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Total</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['total']); ?></div>
            </div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.15s;"
            onclick="window.location.href='applications.php?status=pending'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Pending</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['pending']); ?></div>
            </div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;"
            onclick="window.location.href='applications.php?status=processing'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-info">
                    <i class="fas fa-sync-alt"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Processing</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['processing']); ?></div>
            </div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.25s;"
            onclick="window.location.href='applications.php?status=approved'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Approved</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['approved']); ?></div>
            </div>
        </div>
        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.27s;"
            onclick="window.location.href='applications.php?status=paid'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-paid">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Paid</h3>
                <div class="stat-value">
                    <?php echo number_format($overall_stats['paid']); ?>
                </div>
            </div>
        </div>
        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;"
            onclick="window.location.href='applications.php?status=completed'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-cyan">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Completed</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['completed']); ?></div>
            </div>
        </div>
        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.35s;"
            onclick="window.location.href='applications.php?status=rejected'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>
                    <h3>Rejected</h3>
                    <div class="stat-value"><?php echo number_format($overall_stats['rejected']); ?></div>
            </div>
        </div>
        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s;"
            onclick="window.location.href='applications.php?status=cancelled'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-brown">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Cancelled</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['cancelled']); ?></div>
            </div>
        </div>
    </div>

    <!-- Management Tools -->
    <div class="management-tools-section">
        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas fa-cogs"></i>
                    Management Tools
                </h2>
            </div>
            <div class="quick-actions">
                <a href="manage_departments.php" class="action-btn"
                    style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="icon-logo icon-settings"></div>
                    <span>Departments & Services</span>
                </a>
                <a href="reports.php" class="action-btn"
                    style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                    <div class="icon-logo icon-reports"></div>
                    <span>View Reports</span>
                </a>
                <a href="activity_logs.php" class="action-btn"
                    style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                    <div class="icon-logo icon-activity"></div>
                    <span>Activity Logs</span>
                </a>
                <a href="check_email_and_sms_logs.php" class="action-btn"
                    style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                    <div class="icon-logo icon-email"></div>
                    <span>Email/SMS Logs</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Recent Applications -->
        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas fa-file-alt"></i>
                    Recent Applications
                </h2>
                <a href="applications.php" class="view-all-link">
                    View All
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <?php if (empty($recent_apps)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Applications Yet</h3>
                    <p>New applications will appear here</p>
                </div>
            <?php else: ?>
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Tracking #</th>
                            <th>Applicant</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($recent_apps, 0, 10) as $app): ?>
                            <tr onclick="window.location.href='view_application.php?id=<?php echo $app['id']; ?>'">
                                <td><strong><?php echo htmlspecialchars($app['tracking_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($app['applicant_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['service_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Status Chart -->
        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas fa-chart-pie"></i>
                    Status Overview
                </h2>
            </div>
            <div class="chart-wrapper">
                <canvas id="statusChart"></canvas>
                <div id="customLegend"
                    style="display: flex; flex-direction: column; align-items: center; gap: 8px; margin-top: 20px;">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
<script>
    const ctx = document.getElementById('statusChart');
    const legendContainer = document.getElementById('customLegend');

    if (ctx && legendContainer) {
        const chartData = {
            labels: <?php
            // Create status-to-color mapping
            $statusColors = [
                'pending' => '#ff9800',      // orange
                'processing' => '#fbbf24',   // yellow
                'approved' => '#3b82f6',     // blue
                'paid' => '#10b981',         // green
                'completed' => '#00bcd4',    // cyan
                'rejected' => '#ef4444',     // red
                'cancelled' => '#795548'     // brown
            ];

            $labels = array_keys($status_breakdown);
            $formatted_labels = array_map(function ($label) {
                return ucfirst(strtolower($label));
            }, $labels);

            // Map colors to match the actual statuses present
            $colors = array_map(function ($status) use ($statusColors) {
                return $statusColors[$status] ?? '#cccccc'; // fallback to gray if status not found
            }, $labels);

            echo json_encode($formatted_labels);
            ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($status_breakdown)); ?>,
                backgroundColor: <?php echo json_encode($colors); ?>,
                borderWidth: 0,
                hoverOffset: 10
            }]
        };
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });

        // Generate custom legend
        chartData.labels.forEach((label, i) => {
            const color = chartData.datasets[0].backgroundColor[i];
            const legendItem = document.createElement('div');
            legendItem.style.cssText = 'display: flex; align-items: center; gap: 8px; cursor: pointer;';
            legendItem.innerHTML = `
                <span style="width: 12px; height: 12px; border-radius: 50%; background: ${color};"></span>
                <span style="font-size: 12px; font-weight: 600; color: var(--text-primary);">${label}</span>
            `;
            legendContainer.appendChild(legendItem);
        });
    }
</script>

<?php include '../includes/footer.php'; ?>
