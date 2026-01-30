<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}
$dept_filter_data = getDepartmentFilter('a');
$dept_filter_where = $dept_filter_data['where'] ? ' AND ' . $dept_filter_data['where'] : '';
$dept_filter_params = $dept_filter_data['params'];
// Date range for reports
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$department_filter = isset($_GET['department_id']) ? $_GET['department_id'] : '';

if (isDepartmentAdmin()) {
    $admin_dept_id = getAdminDepartmentId();
    $department_filter = $admin_dept_id; 
}
if (isDepartmentAdmin()) {
    $admin_dept_id = getAdminDepartmentId();
    $departments_stmt = $pdo->prepare("SELECT id, name FROM departments WHERE is_active = 1 AND id = ? ORDER BY name ASC");
    $departments_stmt->execute([$admin_dept_id]);
} else {
    $departments_stmt = $pdo->query("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name ASC");
}
$departments = $departments_stmt->fetchAll();
// Build WHERE clause based on filters
$where_conditions = ["DATE(a.created_at) BETWEEN ? AND ?"];
$params = [$date_from, $date_to];

if (!empty($department_filter)) {
    $where_conditions[] = "a.department_id = ?";
    $params[] = $department_filter;
}

elseif (!empty($dept_filter_where)) {
    $where_conditions[] = trim($dept_filter_where, ' AND'); 
    $params = array_merge($params, $dept_filter_params);
}

$where_clause = implode(" AND ", $where_conditions);

// Applications by status
$stmt = $pdo->prepare("
    SELECT a.status, COUNT(*) as count
    FROM applications a
    WHERE $where_clause
    GROUP BY a.status
");
$stmt->execute($params);
$status_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Applications by service
$stmt = $pdo->prepare("
    SELECT s.service_name, s.service_code, COUNT(*) as count
    FROM applications a
    JOIN services s ON a.service_id = s.id
    WHERE $where_clause
    GROUP BY s.id, s.service_name, s.service_code
    ORDER BY count DESC
");
$stmt->execute($params);
$service_data = $stmt->fetchAll();

// Applications by department
$stmt = $pdo->prepare("
    SELECT d.name as department_name, d.code, COUNT(*) as count
    FROM applications a
    JOIN departments d ON a.department_id = d.id
    WHERE $where_clause
    GROUP BY d.id, d.name, d.code
    ORDER BY count DESC
");
$stmt->execute($params);
$department_data = $stmt->fetchAll();

// Daily applications
// get the actual application dates
$stmt = $pdo->prepare("
    SELECT DATE(a.created_at) as date, COUNT(*) as count
    FROM applications a
    WHERE $where_clause
    GROUP BY DATE(a.created_at)
    ORDER BY date ASC
");
$stmt->execute($params);
$daily_data_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Generate all dates in range with 0 counts
$daily_data = [];
$current_date = new DateTime($date_from);
$end_date = new DateTime($date_to);

while ($current_date <= $end_date) {
    $date_str = $current_date->format('Y-m-d');
    $daily_data[] = [
        'date' => $date_str,
        'count' => isset($daily_data_raw[$date_str]) ? $daily_data_raw[$date_str] : 0
    ];
    $current_date->modify('+1 day');
}

// Revenue by service
$stmt = $pdo->prepare("
    SELECT s.service_name, SUM(s.base_fee) as total_fee, COUNT(*) as count
    FROM applications a
    JOIN services s ON a.service_id = s.id
    WHERE $where_clause
    GROUP BY s.id, s.service_name
    ORDER BY total_fee DESC
");
$stmt->execute($params);
$revenue_data = $stmt->fetchAll();

// Processing time analysis
$stmt = $pdo->prepare("
    SELECT 
        AVG(DATEDIFF(a.updated_at, a.created_at)) as avg_days,
        MIN(DATEDIFF(a.updated_at, a.created_at)) as min_days,
        MAX(DATEDIFF(a.updated_at, a.created_at)) as max_days
    FROM applications a
    WHERE a.status IN ('approved', 'completed', 'rejected')
    AND $where_clause
");
$stmt->execute($params);
$processing_stats = $stmt->fetch();

// Top applicants
$stmt = $pdo->prepare("
    SELECT u.name, u.email, COUNT(a.id) as total_apps
    FROM applications a
    JOIN users u ON a.user_id = u.id
    WHERE $where_clause
    GROUP BY a.user_id
    ORDER BY total_apps DESC
    LIMIT 10
");
$stmt->execute($params);
$top_applicants = $stmt->fetchAll();

// Overall statistics for the period
$total_apps = array_sum($status_data);
$total_revenue_stmt = $pdo->prepare("
    SELECT SUM(s.base_fee) 
    FROM applications a 
    JOIN services s ON a.service_id = s.id 
    WHERE $where_clause
");
$total_revenue_stmt->execute($params);
$total_revenue = $total_revenue_stmt->fetchColumn() ?: 0; // Default to 0 if null

$pageTitle = 'Reports & Analytics';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/css/admin-responsive.css">
    <link rel="stylesheet" href="../assets/css/admin/reports_styles.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Custom Date Picker Init -->
    <script src="../assets/js/datepicker-init.js"></script>

</head>

<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Reports & Analytics</h1>
            <p>Comprehensive insights and statistics for <?php echo date('F d, Y', strtotime($date_from)); ?> -
                <?php echo date('F d, Y', strtotime($date_to)); ?>
            </p>
        </div>

        <!-- Date Filter -->
        <div class="filters-section">
            <div class="filters-header">
                <h3>
                    <i class="fas fa-filter"></i>
                    Filter
                </h3>
            </div>

            <form method="GET" action="" id="filterForm">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>"
                            required>
                    </div>

                    <div class="filter-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>" required>
                    </div>

                    <div class="filter-group">
                        <label>Department</label>
                        <select name="department_id" class="form-control" <?php echo isDepartmentAdmin() ? 'disabled' : ''; ?>>
                            <?php if (!isDepartmentAdmin()): ?>
                                <option value="">All Departments</option>
                            <?php endif; ?>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo ($department_filter == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1rem;">
                            <i class="fas fa-sync-alt"></i> Generate Report
                        </button>
                    </div>

                    <div class="filter-group" style="display: flex; align-items: flex-end;">
                        <button type="button" onclick="exportReport()" class="btn btn-export"
                            style="width: 100%; font-size: 1rem;">
                            <i class="fas fa-file-download"></i> Export PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Applications</div>
                <div class="stat-value"><?php echo number_format($total_apps); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value" style="font-size: 1.75rem;">
                    ₱<?php echo number_format($total_revenue ?? 0, 2); ?></div>
            </div>

            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="stat-label">Avg. Processing Time</div>
                <div class="stat-value">
                    <?php
                    $avg_days = $processing_stats['avg_days'] ?? 0;
                    $days = floor($avg_days);
                    $hours = round(($avg_days - $days) * 24);

                    if ($days > 0) {
                        echo $days . '<span style="font-size: 1rem;">d</span> ' . $hours . '<span style="font-size: 1rem;">h</span>';
                    } else {
                        echo $hours . '<span style="font-size: 1rem;">hours</span>';
                    }
                    ?>
                </div>
            </div>

            <div class="stat-card" style="border-left-color: #3b82f6;">
                <div class="stat-label">Completion Rate</div>
                <div class="stat-value">
                    <?php
                    $completion_rate = $total_apps > 0
                        ? round(($status_data['completed'] ?? 0) / $total_apps * 100, 1)
                        : 0;
                    echo $completion_rate;
                    ?>%
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <!-- Applications by Status -->
            <div class="chart-card">
                <h3>
                    <i class="fas fa-chart-pie"></i>
                    Applications by Status
                </h3>
                <canvas id="statusChart" style="max-height: 300px;"></canvas>
            </div>

            <!-- Applications by Service -->
            <div class="chart-card">
                <h3>
                    <i class="fas fa-building"></i>
                    Applications by Service
                </h3>
                <canvas id="departmentChart" style="max-height: 300px;"></canvas>
            </div>

            <!-- Daily Trend -->
            <div class="chart-card full-width">
                <h3>
                    <i class="fas fa-chart-area"></i>
                    Daily Applications Trend
                </h3>
                <canvas id="trendChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Revenue Table -->
        <div class="chart-card">
            <h3>
                <i class="fas fa-money-bill-wave"></i>
                Revenue by Service
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Applications</th>
                            <th>Total Revenue</th>
                            <th>Avg. Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($revenue_data)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                    No revenue data available for this period
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($revenue_data as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['service_name']); ?></strong></td>
                                    <td><?php echo number_format($row['count']); ?></td>
                                    <td><strong
                                            style="color: var(--primary);">₱<?php echo number_format($row['total_fee'], 2); ?></strong>
                                    </td>
                                    <td>₱<?php echo number_format($row['total_fee'] / $row['count'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Applicants -->
        <div class="chart-card">
            <h3>
                <i class="fas fa-users"></i>
                Top Applicants
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Total Applications</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_applicants)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                    No applicant data available for this period
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $rank = 1;
                            foreach ($top_applicants as $applicant): ?>
                                <tr>
                                    <td><strong><?php echo $rank++; ?></strong></td>
                                    <td><?php echo htmlspecialchars($applicant['name']); ?></td>
                                    <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                                    <td><strong style="color: var(--primary);"><?php echo $applicant['total_apps']; ?></strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <div id="modalIcon" style="font-size: 3rem; margin-bottom: 1rem;"></div>
            <h2 id="modalTitle" style="margin-bottom: 0.5rem;"></h2>
            <p id="modalMessage" style="color: var(--text-secondary); margin-bottom: 1.5rem;"></p>
            <button onclick="closeFeedbackModal()" class="btn btn-success">OK</button>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
    <script>
        // Chart.js Default Configuration
        Chart.defaults.font.family = 'Inter';
        Chart.defaults.color = '#64748b';

        // Status Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
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

                    $status_labels = array_keys($status_data);
                    $formatted_status_labels = array_map('ucfirst', $status_labels);

                    // Map colors to match the actual statuses present
                    $status_colors = array_map(function ($status) use ($statusColors) {
                        return $statusColors[$status] ?? '#cccccc'; // fallback to gray if status not found
                    }, $status_labels);

                    echo json_encode($formatted_status_labels);
                    ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($status_data)); ?>,
                        backgroundColor: <?php echo json_encode($status_colors); ?>,
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: { size: 12, weight: '600' },
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }

        // Service Chart
        const deptCtx = document.getElementById('departmentChart');
        if (deptCtx) {
            new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($service_data, 'service_code')); ?>,
                    datasets: [{
                        label: 'Applications',
                        data: <?php echo json_encode(array_column($service_data, 'count')); ?>,
                        backgroundColor: '#8bc34a',
                        borderRadius: 8,
                        barThickness: 40
                    }]
                },
                options: {
                    indexAxis: 'x',  
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function (context) {
                                    // Show full service name in tooltip
                                    const fullNames = <?php echo json_encode(array_column($service_data, 'service_name')); ?>;
                                    return fullNames[context[0].dataIndex];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: '#f0f0f0' }
                        },
                        y: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Trend Chart
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($daily_data, 'date')); ?>,
                    datasets: [{
                        label: 'Daily Applications',
                        data: <?php echo json_encode(array_column($daily_data, 'count')); ?>,
                        borderColor: '#8bc34a',
                        backgroundColor: 'rgba(139, 195, 74, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#8bc34a',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: '#f0f0f0' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        async function exportReport() {
            const urlParams = new URLSearchParams(window.location.search);
            const dateFrom = urlParams.get('date_from') || '<?php echo date('Y-m-01'); ?>';
            const dateTo = urlParams.get('date_to') || '<?php echo date('Y-m-t'); ?>';
            const departmentId = urlParams.get('department_id') || '';

            // Show loading indicator
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';

            try {
                // Capture charts as images
                const charts = {
                    status: await captureChart('statusChart'),
                    department: await captureChart('departmentChart'),
                    trend: await captureChart('trendChart')
                };

                // Send charts data to PDF generator
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../api/export_report_pdf.php';
                form.target = '_blank';

                // Add hidden fields
                const fields = {
                    date_from: dateFrom,
                    date_to: dateTo,
                    department_id: departmentId,
                    chart_status: charts.status,
                    chart_department: charts.department,
                    chart_trend: charts.trend
                };

                for (const [key, value] of Object.entries(fields)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);

            } catch (error) {
                console.error('Export error:', error);
                alert('Error generating PDF. Please try again.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        async function captureChart(chartId) {
            const canvas = document.getElementById(chartId);
            if (!canvas) return '';
            return canvas.toDataURL('image/png');
        }

        console.log('✅ Reports page loaded successfully');

        // Validate date range
        document.getElementById('filterForm').addEventListener('submit', function (e) {
            const dateFrom = document.querySelector('input[name="date_from"]').value;
            const dateTo = document.querySelector('input[name="date_to"]').value;

            if (dateFrom && dateTo) {
                const fromDate = new Date(dateFrom);
                const toDate = new Date(dateTo);

                if (fromDate > toDate) {
                    e.preventDefault();
                    showFeedback('error', 'Invalid Date Range', '"Date From" cannot be later than "Date To"');
                    return false;
                }
            }
        });

        function showFeedback(type, title, message) {
            const modal = document.getElementById('feedbackModal');
            const icon = document.getElementById('modalIcon');
            const titleEl = document.getElementById('modalTitle');
            const messageEl = document.getElementById('modalMessage');

            if (type === 'error') {
                icon.innerHTML = '❌';
                icon.style.color = '#f44336';
            }

            titleEl.textContent = title;
            messageEl.textContent = message;
            modal.classList.add('show');
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').classList.remove('show');
        }
        // Before form submission, save scroll position
        document.getElementById('filterForm').addEventListener('submit', function () {
            sessionStorage.setItem('scrollPosition', window.scrollY);
        });

        // After page loads, restore scroll position
        window.addEventListener('load', function () {
            const scrollPosition = sessionStorage.getItem('scrollPosition');
            if (scrollPosition) {
                window.scrollTo(0, parseInt(scrollPosition));
                sessionStorage.removeItem('scrollPosition');
            }
        });

        // Disable future dates in date pickers
        document.addEventListener('DOMContentLoaded', function () {
            // Get today's date in YYYY-MM-DD format
            const today = new Date().toISOString().split('T')[0];

            // Set max attribute for both date inputs
            const dateFromInput = document.querySelector('input[name="date_from"]');
            const dateToInput = document.querySelector('input[name="date_to"]');

            if (dateFromInput && dateFromInput.type === 'date') {
                dateFromInput.setAttribute('max', today);
            }

            if (dateToInput && dateToInput.type === 'date') {
                dateToInput.setAttribute('max', today);
            }
        });
    </script>
</body>

</html>

<?php include '../includes/footer.php'; ?>
