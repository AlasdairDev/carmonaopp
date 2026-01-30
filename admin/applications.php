<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// --- LOGIC SECTION ---
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

$department_filter = isset($_GET['department']) ? (int) $_GET['department'] : 0;
$service_filter = isset($_GET['service']) ? (int) $_GET['service'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$where = [];
$params = [];
// Department-based access control
if (isDepartmentAdmin()) {
    $where[] = "a.department_id = ?";
    $params[] = getAdminDepartmentId();
}
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
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
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

$departments = []; // Will be loaded via AJAX
$services = [];

$pageTitle = 'Manage Applications';
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

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <link rel="stylesheet" href="../assets/css/admin-responsive.css">
    <link rel="stylesheet" href="../assets/css/admin/applications_styles.css">
    
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
                    <i class="fas fa-download"></i></i> Export CSV
                </button>
                <a href="reports.php" class="btn btn-white">
                    <i class="fas fa-chart-line"></i> Reports
                </a>
            </div>
        </div>

        <!-- Filters Card -->
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
                        <select name="department" id="departmentFilter" class="form-control">
                            <option value="">All Departments</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Service</label>
                        <select name="service" id="serviceFilter" class="form-control" disabled>
                            <option value="">Select Department First</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>
                                Processing</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>
                                Approved</option>
                            <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>
                                Completed</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>
                                Rejected</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>
                                Cancelled</option>
                        </select>
                    </div>

                    <!-- Row 2: Date From, Date To, Search -->
                    <div class="filter-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control"
                            value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>

                    <div class="filter-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control"
                            value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>

                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Tracking #, Name, Email..."
                            value="<?php echo htmlspecialchars($search); ?>" style="height: 42px;">
                    </div>

                    <!-- Row 3: Action Buttons -->
                    <!-- Row 3: Action Buttons -->
                    <div class="filter-actions">
                        <button type="button" onclick="resetFilters()" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
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
                Showing <strong><?php echo count($applications); ?></strong> of
                <strong><?php echo number_format($total); ?></strong> applications
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
                                    <td><span
                                            class="tracking-number"><?php echo htmlspecialchars($app['tracking_number']); ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($app['applicant_name']); ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($app['email']); ?>
                                        </div>
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
                                        <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                            <?php echo date('h:i A', strtotime($app['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn-view"
                                            onclick="event.stopPropagation()">
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

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content-small">
            <div class="modal-icon" id="feedbackIcon"></div>
            <h2 id="feedbackTitle">Success</h2>
            <p id="feedbackMessage">Operation completed successfully</p>
            <button onclick="closeFeedbackModal()" class="btn"
                style="background: var(--primary) !important; color: white !important; border: none !important;">OK</button>
        </div>
    </div>
    <script>
        // Global variables to track current state
        let currentDepartments = [];
        let currentServices = [];
        let currentDepartmentValue = '';
        let currentServiceValue = '';
        let departmentRefreshInterval;
        let serviceRefreshInterval;
        let isFirstLoad = true;

        // Load active departments
        async function loadDepartments(preserveSelection = false) {
            const departmentSelect = document.getElementById('departmentFilter');
            const phpSelectedDept = '<?php echo $department_filter; ?>';

            if (preserveSelection && !isFirstLoad) {
                currentDepartmentValue = departmentSelect.value;
            } else if (isFirstLoad && phpSelectedDept) {
                currentDepartmentValue = phpSelectedDept;
            }

            try {
                const response = await fetch('../api/get_departments.php?t=' + Date.now());
                const data = await response.json();

                console.log('📥 [DEPT] Fetched departments:', data);

                if (data.success) {
                    const newDeptSignature = data.departments
                        .map(d => `${d.id}:${d.name}`)
                        .sort()
                        .join('|');

                    const oldDeptSignature = currentDepartments
                        .map(d => `${d.id}:${d.name}`)
                        .sort()
                        .join('|');

                    console.log('🔍 [DEPT] Comparing signatures:', {
                        old: oldDeptSignature,
                        new: newDeptSignature,
                        changed: newDeptSignature !== oldDeptSignature
                    });

                    if (newDeptSignature !== oldDeptSignature || isFirstLoad) {
                        console.log('🔄 [DEPT] Departments changed, updating dropdown...');

                        currentDepartments = data.departments;
                        departmentSelect.innerHTML = '<option value="">All Departments</option>';

                        data.departments.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept.id;
                            option.textContent = dept.name;

                            if (dept.id == currentDepartmentValue) {
                                option.selected = true;
                            }

                            departmentSelect.appendChild(option);
                        });

                        if (currentDepartmentValue && currentDepartmentValue !== '') {
                            const stillExists = data.departments.some(d => d.id == currentDepartmentValue);

                            if (!stillExists && !isFirstLoad) {
                                console.log('⚠️ [DEPT] Selected department was deactivated/removed');
                                departmentSelect.value = '';
                                currentDepartmentValue = '';

                                const serviceSelect = document.getElementById('serviceFilter');
                                serviceSelect.innerHTML = '<option value="">Select Department First</option>';
                                serviceSelect.disabled = true;
                                currentServices = [];
                            }
                        }

                        departmentSelect.disabled = false;

                        if (departmentSelect.value) {
                            await loadServices(departmentSelect.value, true);
                        }

                        if (!isFirstLoad) {
                            console.log('✅ [DEPT] Departments updated successfully');
                        }
                    } else {
                        console.log('⏭️ [DEPT] No changes detected, skipping update');
                    }

                    isFirstLoad = false;

                } else {
                    console.error('❌ [DEPT] Error from API:', data.message);
                    departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
                }
            } catch (error) {
                console.error('❌ [DEPT] Fetch error:', error);
                departmentSelect.innerHTML = '<option value="">Error loading departments</option>';
            }
        }

        // Load active services for selected department
        async function loadServices(departmentId, preserveSelection = false) {
            const serviceSelect = document.getElementById('serviceFilter');
            const phpSelectedService = '<?php echo $service_filter; ?>';

            if (preserveSelection && currentServiceValue === '') {
                currentServiceValue = phpSelectedService;
            } else if (preserveSelection) {
                currentServiceValue = serviceSelect.value;
            }

            if (!departmentId) {
                serviceSelect.innerHTML = '<option value="">Select Department First</option>';
                serviceSelect.disabled = true;
                currentServices = [];
                return;
            }

            try {
                const response = await fetch(`../api/get_services.php?department_id=${departmentId}&t=${Date.now()}`);
                const data = await response.json();

                console.log('📥 [SERVICE] Fetched services for dept', departmentId, ':', data);

                if (data.success) {
                    const newServiceSignature = data.services
                        .map(s => `${s.id}:${s.service_name}`)
                        .sort()
                        .join('|');

                    const oldServiceSignature = currentServices
                        .map(s => `${s.id}:${s.service_name}`)
                        .sort()
                        .join('|');

                    console.log('🔍 [SERVICE] Comparing signatures:', {
                        old: oldServiceSignature,
                        new: newServiceSignature,
                        changed: newServiceSignature !== oldServiceSignature
                    });

                    if (newServiceSignature !== oldServiceSignature || oldServiceSignature === '') {
                        console.log('🔄 [SERVICE] Services changed, updating dropdown...');

                        currentServices = data.services;
                        serviceSelect.innerHTML = '<option value="">All Services</option>';

                        data.services.forEach(service => {
                            const option = document.createElement('option');
                            option.value = service.id;
                            option.textContent = service.service_name;

                            if (service.id == currentServiceValue) {
                                option.selected = true;
                            }

                            serviceSelect.appendChild(option);
                        });

                        if (currentServiceValue && currentServiceValue !== '') {
                            const stillExists = data.services.some(s => s.id == currentServiceValue);

                            if (!stillExists && preserveSelection) {
                                console.log('⚠️ [SERVICE] Selected service was deactivated/removed');
                                serviceSelect.value = '';
                                currentServiceValue = '';
                            }
                        }

                        serviceSelect.disabled = false;
                        console.log('✅ [SERVICE] Services updated successfully');
                    } else {
                        console.log('⏭️ [SERVICE] No changes detected, skipping update');
                    }
                } else {
                    serviceSelect.innerHTML = '<option value="">No services available</option>';
                    serviceSelect.disabled = true;
                    currentServices = [];
                }
            } catch (error) {
                console.error('❌ [SERVICE] Fetch error:', error);
                serviceSelect.innerHTML = '<option value="">Error loading services</option>';
                serviceSelect.disabled = true;
                currentServices = [];
            }
        }

        // Start periodic refresh of departments list
        function startDepartmentRefresh() {
            departmentRefreshInterval = setInterval(() => {
                console.log('⏰ [DEPT] Auto-refresh triggered');
                loadDepartments(true);
            }, 5000);

            console.log('🔄 [DEPT] Auto-refresh enabled: Checking every 5 seconds');
        }

        // Start periodic refresh of services list
        function startServiceRefresh() {
            serviceRefreshInterval = setInterval(() => {
                const departmentSelect = document.getElementById('departmentFilter');
                if (departmentSelect.value) {
                    console.log('⏰ [SERVICE] Auto-refresh triggered');
                    loadServices(departmentSelect.value, true);
                }
            }, 5000);

            console.log('🔄 [SERVICE] Auto-refresh enabled: Checking every 5 seconds');
        }

        // Stop periodic refresh
        function stopAllRefresh() {
            if (departmentRefreshInterval) {
                clearInterval(departmentRefreshInterval);
                console.log('⏹️ [DEPT] Auto-refresh stopped');
            }
            if (serviceRefreshInterval) {
                clearInterval(serviceRefreshInterval);
                console.log('⏹️ [SERVICE] Auto-refresh stopped');
            }
        }

        // AJAX Filter Function
        function applyFilter(event) {
            event.preventDefault();

            const department = document.getElementById('departmentFilter').value;
            const service = document.getElementById('serviceFilter').value;
            const status = document.querySelector('select[name="status"]').value;
            const search = document.querySelector('input[name="search"]').value;
            const dateFrom = document.querySelector('input[name="date_from"]').value;
            const dateTo = document.querySelector('input[name="date_to"]').value;

            // Validate date range
            if (dateFrom && dateTo) {
                const fromDate = new Date(dateFrom);
                const toDate = new Date(dateTo);

                if (fromDate > toDate) {
                    showFeedbackModal(
                        'Invalid Date Range',
                        '"Date From" cannot be later than "Date To"',
                        'error'
                    );
                    return;
                }
            }

            // Build query string
            const params = new URLSearchParams();
            if (department) params.append('department', department);
            if (service) params.append('service', service);
            if (status) params.append('status', status);
            if (search) params.append('search', search);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            console.log('🔍 Applying filters:', params.toString());

            // Fetch filtered data
            fetch(`applications.php?${params.toString()}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

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

                    console.log('✅ Filters applied successfully');
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

        // Reset Filters Function
        function resetFilters() {
            // Clear all form inputs
            document.getElementById('departmentFilter').value = '';
            document.getElementById('serviceFilter').value = '';
            document.getElementById('serviceFilter').disabled = true;
            document.getElementById('serviceFilter').innerHTML = '<option value="">Select Department First</option>';
            document.querySelector('select[name="status"]').value = '';
            document.querySelector('input[name="search"]').value = '';
            document.querySelector('input[name="date_from"]').value = '';
            document.querySelector('input[name="date_to"]').value = '';

            // Reset tracking variables
            currentDepartmentValue = '';
            currentServiceValue = '';

            console.log('🔄 Resetting filters...');

            // Fetch unfiltered data
            fetch('applications.php')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

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

                    console.log('✅ Filters reset successfully');
                })
                .catch(error => {
                    alert('An error occurred while resetting filters. Please try again.');
                    console.error('Error:', error);
                });
        }

        // Export to CSV
        function exportToCSV() {
            const table = document.getElementById('applicationsTable');
            if (!table) return;

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

        // Disable future dates
        function setMaxDateToToday() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="date_from"]').setAttribute('max', today);
            document.querySelector('input[name="date_to"]').setAttribute('max', today);
            console.log('📅 Future dates disabled - max date set to:', today);
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function () {
            const departmentSelect = document.getElementById('departmentFilter');
            const serviceSelect = document.getElementById('serviceFilter');

            console.log('🚀 Applications page initializing...');

            // Disable future dates
            setMaxDateToToday();

            // Load departments on page load
            loadDepartments(false);

            // Start automatic refresh after initial load
            setTimeout(() => {
                startDepartmentRefresh();
                startServiceRefresh();
            }, 2000);

            // Update services when department changes manually
            departmentSelect.addEventListener('change', function () {
                currentDepartmentValue = this.value;
                currentServiceValue = '';
                loadServices(this.value, false);
            });

            // Track service selection changes
            serviceSelect.addEventListener('change', function () {
                currentServiceValue = this.value;
            });

            // Attach form submit handler for AJAX filtering
            document.getElementById('filterForm').addEventListener('submit', applyFilter);

            console.log('✅ Applications page loaded with real-time AJAX filters');
        });

        // Stop refresh when leaving page
        window.addEventListener('beforeunload', function () {
            stopAllRefresh();
        });

        // Pause/resume refresh based on tab visibility
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                console.log('👁️ Tab hidden - pausing auto-refresh');
                stopAllRefresh();
            } else {
                console.log('👁️ Tab visible - resuming auto-refresh');
                loadDepartments(true);
                const departmentSelect = document.getElementById('departmentFilter');
                if (departmentSelect.value) {
                    loadServices(departmentSelect.value, true);
                }
                startDepartmentRefresh();
                startServiceRefresh();
            }
        });
        // Show feedback modal
        function showFeedbackModal(title, message, type = 'success') {
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

            document.getElementById('feedbackModal').classList.add('active');
        }

        // Close feedback modal
        function closeFeedbackModal() {
            document.getElementById('feedbackModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const feedbackModal = document.getElementById('feedbackModal');
            if (event.target === feedbackModal) {
                closeFeedbackModal();
            }
        }
    </script>
</body>

</html>

<?php include '../includes/footer.php'; ?>
