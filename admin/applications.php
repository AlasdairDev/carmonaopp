<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
   header('Location: ../login.php');
   exit();
}

// --- LOGIC SECTION ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$department_filter = isset($_GET['department']) ? (int)$_GET['department'] : 0;
$service_filter = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$where = [];
$params = [];

if ($department_filter) {
   $where[] = "a.department_id = ?";
   $params[] = $department_filter;
}
if ($service_filter) {
   $where[] = "a.service_id = ?";
   $params[] = $service_filter;
}
if ($status_filter && $status_filter !== 'all') {
   $where[] = "a.status = ?";
   $params[] = ucfirst($status_filter);
}
if ($search) {
   $where[] = "(a.tracking_number COLLATE utf8mb4_unicode_ci LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
   $search_param = "%$search%";
   $params[] = $search_param; $params[] = $search_param; $params[] = $search_param;
}
if ($date_from) {
   $where[] = "DATE(a.created_at) >= ?";
   $params[] = $date_from;
}
if ($date_to) {
   $where[] = "DATE(a.created_at) <= ?";
   $params[] = $date_to;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_sql = "SELECT COUNT(*) FROM applications a JOIN users u ON a.user_id = u.id $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$sql = "SELECT a.*, u.name as applicant_name, u.email, u.mobile as phone, 
        d.name as department_name, d.code COLLATE utf8mb4_unicode_ci as department_code, 
        s.service_name, s.base_fee
   FROM applications a 
   JOIN users u ON a.user_id = u.id
   LEFT JOIN departments d ON a.department_id = d.id
   LEFT JOIN services s ON a.service_id = s.id
   $where_clause ORDER BY a.created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

$departments = $pdo->query("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name")->fetchAll();
$services = [];
if ($department_filter) {
   $stmt = $pdo->prepare("SELECT id, service_name FROM services WHERE department_id = ? AND is_active = 1 ORDER BY service_name");
   $stmt->execute([$department_filter]);
   $services = $stmt->fetchAll();
}

$pageTitle = 'Manage Applications';
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
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
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
            gap: 0.5rem;
            font-size: 0.875rem;
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

        /* Improved Filters Section */
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

        .filters-toggle {
            background: none;
            border: none;
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
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

.filter-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    grid-column: 1 / -1;
    align-items: end;
}

.filter-actions > * {
    width: 100%;
    box-sizing: border-box;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px !important;
}

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text-primary);
            border-radius: 8px;
        }

        .btn-secondary, .btn-primary {
            flex: 1;
            max-width: 100%;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            color: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(226, 232, 240, 0.5);
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

        .tracking-number {
            font-weight: 700;
            color: var(--primary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-paid { background: #c8e6c9; color: #1b5e20; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-completed { background: #dbeafe; color: #1e40af; }

        /* Updated View Button Style - Matches User Dashboard */
        .btn-view {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
            border: none;
            cursor: pointer;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(139, 195, 74, 0.4);
        }

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

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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

        /* Responsive */
        @media (max-width: 1024px) {
            .filters-grid {
                grid-template-columns: repeat(2, 1fr);
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

            .filter-actions {
                flex-direction: column;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        /* Justify button content */
        .btn {
            justify-content: center;
        }

        .filter-actions .btn-secondary,
.filter-actions .btn-primary {
    border-radius: 8px !important;
}

.filter-group input[type="text"] {
    border-radius: 8px !important;
}
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Manage Applications</h1>
                <p>Review and process all submitted applications</p>
            </div>
            <div class="header-actions">
                <button onclick="exportToCSV()" class="btn btn-white">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
                <a href="reports.php" class="btn btn-white">
                    <i class="fas fa-chart-line"></i> Reports
                </a>
            </div>
        </div>

        <!-- Improved Filters Card -->
        <div class="filters-section">
            <div class="filters-header">
                <h3>
                    <i class="fas fa-filter"></i>
                    Filter Applications
                </h3>
            </div>
            
            <form method="GET" action="" id="filterForm">
                <div class="filters-grid">
                    <!-- Row 1: Department, Service, Status -->
                    <div class="filter-group">
                        <label>Department</label>
                        <select name="department" id="departmentFilter" class="form-control" onchange="updateServiceFilter()">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $department_filter == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Service</label>
                        <select name="service" id="serviceFilter" class="form-control">
                            <option value="">All Services</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>" <?php echo $service_filter == $service['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($service['service_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>

                    <!-- Row 2: Date From, Date To, Search -->
                    <div class="filter-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>

                    <div class="filter-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>

                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Tracking #, Name, Email..." value="<?php echo htmlspecialchars($search); ?>" style="height: 42px;">
                    </div>

                    <!-- Row 3: Action Buttons -->
                    <div class="filter-actions">
                        <a href="applications.php" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
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
                Showing <strong><?php echo count($applications); ?></strong> of <strong><?php echo number_format($total); ?></strong> applications
            </div>
        </div>

        <!-- Applications Table -->
        <?php if (empty($applications)): ?>
            <div class="table-card">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Applications Found</h3>
                    <p>Try adjusting your filters or search criteria</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-card">
                <div class="table-container">
                    <table class="modern-table" id="applicationsTable">
                        <thead>
                            <tr>
                                <th>Tracking ID</th>
                                <th>Applicant</th>
                                <th>Service</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><span class="tracking-number"><?php echo htmlspecialchars($app['tracking_number']); ?></span></td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);"><?php echo htmlspecialchars($app['email']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($app['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['department_name']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?php echo date('M d, Y', strtotime($app['created_at'])); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);"><?php echo date('h:i A', strtotime($app['created_at'])); ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn-view" onclick="event.stopPropagation()">
                                        View
                                    </a>
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

    <script>
    // Update service filter based on department
    async function updateServiceFilter() {
        const departmentId = document.getElementById('departmentFilter').value;
        const serviceFilter = document.getElementById('serviceFilter');
        
        if (departmentId) {
            serviceFilter.innerHTML = '<option value="">Loading...</option>';
            try {
                const response = await fetch(`../api/get_services.php?department_id=${departmentId}`);
                const data = await response.json();
                serviceFilter.innerHTML = '<option value="">All Services</option>';
                if (data.success) {
                    data.services.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.service_name;
                        serviceFilter.appendChild(opt);
                    });
                }
            } catch (e) {
                console.error(e);
                serviceFilter.innerHTML = '<option value="">All Services</option>';
            }
        } else {
            serviceFilter.innerHTML = '<option value="">All Services</option>';
        }
    }

    // Export to CSV
    function exportToCSV() {
        const table = document.getElementById('applicationsTable');
        if(!table) return;
        
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (const row of rows) {
            const cols = row.querySelectorAll('td, th');
            const rowData = [];
            for (let i = 0; i < cols.length - 1; i++) {
                let text = cols[i].innerText.replace(/\n/g, ' ').replace(/"/g, '""');
                rowData.push('"' + text + '"');
            }
            csv.push(rowData.join(','));
        }
        
        const csvContent = '\uFEFF' + csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'applications_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    // Validate date range
    document.getElementById('filterForm').addEventListener('submit', function(e) {
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

    console.log('✅ Applications page loaded successfully');
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>