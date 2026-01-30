<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}
if (!isSuperAdmin()) {
    $_SESSION['error'] = 'Access denied. Only superadmins can manage departments.';
    header('Location: dashboard.php');
    exit();
}
// Handle Department Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        try {
            if ($action === 'add_department') {
                $name = sanitizeInput($_POST['name']);
                $code = sanitizeInput($_POST['code']);
                $description = sanitizeInput($_POST['description']);

                $stmt = $pdo->prepare("INSERT INTO departments (name, code, description, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
                $stmt->execute([$name, $code, $description]);

                $_SESSION['success'] = 'Department added successfully!';
                logActivity($_SESSION['user_id'], 'Add Department', "Added department: $name");

            } elseif ($action === 'update_department') {
                $id = (int) $_POST['department_id'];
                $name = sanitizeInput($_POST['name']);
                $code = sanitizeInput($_POST['code']);
                $description = sanitizeInput($_POST['description']);

                $stmt = $pdo->prepare("UPDATE departments SET name = ?, code = ?, description = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $code, $description, $id]);

                $_SESSION['success'] = 'Department updated successfully!';
                logActivity($_SESSION['user_id'], 'Update Department', "Updated department ID: $id");

            } elseif ($action === 'delete_department') {
                $id = (int) $_POST['department_id'];

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE department_id = ?");
                $stmt->execute([$id]);
                $serviceCount = $stmt->fetchColumn();

                if ($serviceCount > 0) {
                    $_SESSION['error'] = 'Cannot delete department with active services. Delete services first.';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
                    $stmt->execute([$id]);

                    $_SESSION['success'] = 'Department deleted successfully!';
                    logActivity($_SESSION['user_id'], 'Delete Department', "Deleted department ID: $id");
                }

            } elseif ($action === 'add_service') {
                $dept_id = (int) $_POST['department_id'];
                $name = sanitizeInput($_POST['service_name']);

                // Check if department is active
                $stmt = $pdo->prepare("SELECT is_active FROM departments WHERE id = ?");
                $stmt->execute([$dept_id]);
                $dept_active = $stmt->fetchColumn();

                if (!$dept_active) {
                    $_SESSION['error'] = 'Cannot add service to an inactive department.';
                    header('Location: manage_departments.php?tab=services');
                    exit();
                }
                // Get department code
                $stmt = $pdo->prepare("SELECT code FROM departments WHERE id = ?");
                $stmt->execute([$dept_id]);
                $dept_code = $stmt->fetchColumn();


                // Get next service number for this department
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE department_id = ?");
                $stmt->execute([$dept_id]);
                $service_count = $stmt->fetchColumn() + 1;

                // Auto-generate service code: DEPTCODE-001, DEPTCODE-002, etc.
                $code = $dept_code . '-' . str_pad($service_count, 3, '0', STR_PAD_LEFT);
                $description = sanitizeInput($_POST['description']);
                $requirements = sanitizeInput($_POST['requirements']);
                $processing_days = (int) $_POST['processing_days'];
                $base_fee = (float) $_POST['base_fee'];

                $stmt = $pdo->prepare("INSERT INTO services (department_id, service_name, service_code, description, requirements, processing_days, base_fee, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");
                $stmt->execute([$dept_id, $name, $code, $description, $requirements, $processing_days, $base_fee]);

                $_SESSION['success'] = 'Service added successfully!';
                logActivity($_SESSION['user_id'], 'Add Service', "Added service: $name");

            } elseif ($action === 'update_service') {
                $id = (int) $_POST['service_id'];
                $name = sanitizeInput($_POST['service_name']);
                // Remove the $code line - keep existing code in database

                $stmt = $pdo->prepare("UPDATE services SET service_name = ?, description = ?, requirements = ?, processing_days = ?, base_fee = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $description, $requirements, $processing_days, $base_fee, $id]);
                $description = sanitizeInput($_POST['description']);
                $requirements = sanitizeInput($_POST['requirements']);
                $processing_days = (int) $_POST['processing_days'];
                $base_fee = (float) $_POST['base_fee'];

                $stmt = $pdo->prepare("UPDATE services SET service_name = ?, service_code = ?, description = ?, requirements = ?, processing_days = ?, base_fee = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $code, $description, $requirements, $processing_days, $base_fee, $id]);

                $_SESSION['success'] = 'Service updated successfully!';
                logActivity($_SESSION['user_id'], 'Update Service', "Updated service ID: $id");

            } elseif ($action === 'delete_service') {
                $id = (int) $_POST['service_id'];

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE service_id = ?");
                $stmt->execute([$id]);
                $appCount = $stmt->fetchColumn();

                if ($appCount > 0) {
                    $_SESSION['error'] = 'Cannot delete service with existing applications.';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
                    $stmt->execute([$id]);

                    $_SESSION['success'] = 'Service deleted successfully!';
                    logActivity($_SESSION['user_id'], 'Delete Service', "Deleted service ID: $id");
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        $tab = isset($_POST['tab']) ? $_POST['tab'] : 'departments';
        header('Location: manage_departments.php?tab=' . $tab);
        exit();
    }
}

// Fetch departments with service count
$departments = $pdo->query("
    SELECT d.*, 
           COUNT(s.id) as service_count
    FROM departments d
    LEFT JOIN services s ON d.id = s.department_id
    GROUP BY d.id
    ORDER BY d.name
")->fetchAll();

// Get all services with department info
$services = $pdo->query("
    SELECT s.*, d.name as department_name
    FROM services s
    JOIN departments d ON s.department_id = d.id
    ORDER BY d.name, s.service_name
")->fetchAll();

// Fetch all departments for filter dropdown
$filterDepartments = $pdo->query("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name")->fetchAll();

$pageTitle = 'Manage Departments & Services';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../assets/css/admin/manage_departments_styles.css">
    <link rel="stylesheet" href="../assets/css/admin-responsive.css">

    

</head>

<body>

    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <h1></i> Manage Departments & Services</h1>
                <p>Add, edit, remove, deactivate, and activate departments and their associated services</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <button class="tab-btn active" onclick="switchTab('departments')">
                <i class="fas fa-building"></i> Departments
            </button>
            <button class="tab-btn" onclick="switchTab('services')">
                <i class="fas fa-cogs"></i> Services
            </button>
        </div>

        <!-- Departments Tab -->
        <div id="departments" class="tab-content active">
            <div class="top-controls">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="deptSearch" class="search-box" placeholder="Search departments..."
                        onkeyup="searchDepartments()">
                </div>
                <select id="deptStatusFilter" class="form-control status-filter" onchange="searchDepartments()">
                    <option value="" selected>All Status</option>
                    <option value="1">Active Only</option>
                    <option value="0">Inactive Only</option>
                </select>
                <button onclick="showAddDeptModal()" class="add-btn">
                    <i class="fas fa-plus"></i> Add New Department
                </button>
            </div>

            <div class="data-table">
                <table id="deptTable">
                    <thead>
                        <tr>
                            <th>Department Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Services</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $dept): ?>
                            <tr
                                style="<?php echo $dept['is_active'] == 0 ? 'background-color: #fff3cd; opacity: 0.8;' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($dept['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($dept['code']); ?></td>
                                <td><?php echo htmlspecialchars(substr($dept['description'], 0, 80)); ?>...</td>
                                <td>
                                    <span class="service-count">
                                        <?php echo $dept['service_count']; ?>
                                        <?php echo $dept['service_count'] == 1 ? 'service' : 'services'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="status-badge <?php echo $dept['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $dept['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button onclick='editDept(<?php echo json_encode($dept); ?>)' class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <button
                                            onclick="deleteDept(<?php echo $dept['id']; ?>, <?php echo $dept['service_count']; ?>)"
                                            class="btn btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                        <?php if ($dept['is_active'] == 1): ?>
                                            <button
                                                onclick="toggleDepartment(<?php echo $dept['id']; ?>, 0, '<?php echo htmlspecialchars($dept['name']); ?>')"
                                                class="btn btn-warning">
                                                <i class="fas fa-ban"></i> Deactivate
                                            </button>
                                        <?php else: ?>
                                            <button
                                                onclick="toggleDepartment(<?php echo $dept['id']; ?>, 1, '<?php echo htmlspecialchars($dept['name']); ?>')"
                                                class="btn btn-success">
                                                <i class="fas fa-check-circle"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Services Tab -->
        <div id="services" class="tab-content">
            <div class="top-controls">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="serviceSearch" class="search-box" placeholder="Search services..."
                        onkeyup="searchServices()">
                </div>

                <select id="departmentFilter" class="status-filter" onchange="filterServices()">
                    <option value="">All Departments</option>
                    <?php foreach ($filterDepartments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept['id']) ?>">
                            <?= htmlspecialchars($dept['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select id="serviceStatusFilter" class="form-control status-filter" onchange="filterServices()">
                    <option value="" selected>All Status</option>
                    <option value="1">Active Only</option>
                    <option value="0">Inactive Only</option>
                </select>
                <button onclick="showAddServiceModal()" class="add-btn">
                    <i class="fas fa-plus"></i> Add New Service
                </button>
            </div>

            <div class="data-table">
                <table id="serviceTable">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Department</th>
                            <th>Base Fee</th>
                            <th>Processing Days</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr data-department-id="<?= $service['department_id'] ?>"
                                style="<?php echo $service['is_active'] == 0 ? 'background-color: #fff3cd; opacity: 0.8;' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($service['service_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($service['department_name']); ?></td>
                                <td>₱<?php echo number_format($service['base_fee'], 2); ?></td>
                                <td><?php echo $service['processing_days']; ?> days</td>
                                <td>
                                    <span
                                        class="status-badge <?php echo $service['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button
                                            onclick="editService(<?php echo htmlspecialchars(json_encode($service), ENT_QUOTES, 'UTF-8'); ?>)"
                                            class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <button onclick="deleteService(<?php echo $service['id']; ?>)"
                                            class="btn btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                        <?php if ($service['is_active'] == 1): ?>
                                            <button
                                                onclick="toggleService(<?php echo $service['id']; ?>, 0, '<?php echo htmlspecialchars($service['service_name']); ?>')"
                                                class="btn btn-warning">
                                                <i class="fas fa-ban"></i> Deactivate
                                            </button>
                                        <?php else: ?>
                                            <button
                                                onclick="toggleService(<?php echo $service['id']; ?>, 1, '<?php echo htmlspecialchars($service['service_name']); ?>')"
                                                class="btn btn-success">
                                                <i class="fas fa-check-circle"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Department Modal -->
    <div id="deptModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="deptModalTitle">Add Department</h3>
                <button class="close-modal" onclick="closeDeptModal()">&times;</button>
            </div>
            <form id="deptForm" onsubmit="submitDeptForm(event)">
                <div class="modal-body">
                    <input type="hidden" name="action" id="deptAction" value="add_department">
                    <input type="hidden" name="department_id" id="deptId">

                    <div class="form-group">
                        <label>Department Name *</label>
                        <input type="text" name="name" id="deptName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Department Code *</label>
                        <input type="text" name="code" id="deptCode" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="deptDescription" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeDeptModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
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

    <!-- Feedback Modal (for messages after action) -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <div style="padding: 2rem;">
                <div id="feedbackIcon" style="font-size: 3rem; margin-bottom: 1rem;"></div>
                <h2 id="feedbackTitle" style="margin-bottom: 0.5rem;"></h2>
                <p id="feedbackMessage" style="color: var(--text-secondary); margin-bottom: 1.5rem;"></p>
                <button onclick="closeFeedbackModal()" class="btn btn-primary"
                    style="background: var(--primary) !important; color: white !important;">OK</button>
            </div>
        </div>
    </div>
    <!-- Service Modal -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="serviceModalTitle">Add Service</h3>
                <button class="close-modal" onclick="closeServiceModal()">&times;</button>
            </div>
            <form id="serviceForm" onsubmit="submitServiceForm(event)">
                <div class="modal-body">
                    <input type="hidden" name="action" id="serviceAction" value="add_service">
                    <input type="hidden" name="service_id" id="serviceId">

                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department_id" id="serviceDeptId" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <?php if ($dept['is_active'] == 1): ?>
                                    <option value="<?php echo $dept['id']; ?>">
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Service Name *</label>
                        <input type="text" name="service_name" id="serviceName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="serviceDescription" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Requirements</label>
                        <textarea name="requirements" id="serviceRequirements" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Processing Days *</label>
                        <input type="number" name="processing_days" id="serviceProcessingDays" class="form-control"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Base Fee (₱) *</label>
                        <input type="number" step="0.01" name="base_fee" id="serviceBaseFee" class="form-control"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeServiceModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Service</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            const tabBtn = document.querySelector(`.tab-btn[onclick*="${tab}"]`);
            if (tabBtn) {
                tabBtn.classList.add('active');
            }

            const tabContent = document.getElementById(tab);
            if (tabContent) {
                tabContent.classList.add('active');
            }
        }

        function showAddDeptModal() {
            document.getElementById('deptModalTitle').textContent = 'Add Department';
            document.getElementById('deptAction').value = 'add_department';
            document.getElementById('deptId').value = '';
            document.getElementById('deptName').value = '';
            document.getElementById('deptCode').value = '';
            document.getElementById('deptDescription').value = '';
            document.getElementById('deptModal').classList.add('active');
        }

        function editDept(dept) {
            document.getElementById('deptModalTitle').textContent = 'Edit Department';
            document.getElementById('deptAction').value = 'update_department';
            document.getElementById('deptId').value = dept.id;
            document.getElementById('deptName').value = dept.name;
            document.getElementById('deptCode').value = dept.code;
            document.getElementById('deptDescription').value = dept.description || '';
            document.getElementById('deptModal').classList.add('active');
        }

        function closeDeptModal() {
            document.getElementById('deptModal').classList.remove('active');
        }

        function deleteDept(id, serviceCount) {
            if (serviceCount > 0) {
                showFeedbackModal(
                    'Cannot Delete Department',
                    'This department has active services. Please delete or reassign the services first.',
                    'error'
                );
                return;
            }

            showConfirmModal(
                'Delete Department',
                'Are you sure you want to permanently delete this department? This action cannot be undone.',
                '<i class="fas fa-trash-alt"></i>',
                '#ef4444',
                function () {
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'delete_department',
                            department_id: id
                        })
                    })
                        .then(response => response.text())
                        .then(() => {
                            showToast('Department deleted successfully!', 'success');
                            setTimeout(() => {
                                reloadTableData('departments');
                            }, 500);
                        })
                        .catch(error => {
                            showToast('An error occurred. Please try again.', 'error');
                            console.error('Error:', error);
                        });
                },
                'Delete',
                'btn-delete'
            );
        }

        function showAddServiceModal() {
            document.getElementById('serviceModalTitle').textContent = 'Add Service';
            document.getElementById('serviceAction').value = 'add_service';
            document.getElementById('serviceId').value = '';
            document.getElementById('serviceDeptId').value = '';
            document.getElementById('serviceName').value = '';
            document.getElementById('serviceDescription').value = '';
            document.getElementById('serviceRequirements').value = '';
            document.getElementById('serviceProcessingDays').value = '';
            document.getElementById('serviceBaseFee').value = '';
            document.getElementById('serviceModal').classList.add('active');
        }

        function editService(service) {
            document.getElementById('serviceModalTitle').textContent = 'Edit Service';
            document.getElementById('serviceAction').value = 'update_service';
            document.getElementById('serviceId').value = service.id;
            document.getElementById('serviceDeptId').value = service.department_id;
            document.getElementById('serviceName').value = service.service_name;
            document.getElementById('serviceDescription').value = service.description || '';
            document.getElementById('serviceRequirements').value = service.requirements || '';
            document.getElementById('serviceProcessingDays').value = service.processing_days;
            document.getElementById('serviceBaseFee').value = service.base_fee;
            document.getElementById('serviceModal').classList.add('active');
        }

        function closeServiceModal() {
            document.getElementById('serviceModal').classList.remove('active');
        }

        function deleteService(id) {
            showConfirmModal(
                'Delete Service',
                'Are you sure you want to permanently delete this service? This action cannot be undone.',
                '<i class="fas fa-trash-alt"></i>',
                '#ef4444',
                function () {
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'delete_service',
                            service_id: id
                        })
                    })
                        .then(response => response.text())
                        .then(() => {
                            showToast('Service deleted successfully!', 'success');
                            setTimeout(() => {
                                reloadTableData('services');
                                reloadTableData('departments'); // ADD THIS LINE
                            }, 500);
                        })
                        .catch(error => {
                            showToast('An error occurred. Please try again.', 'error');
                            console.error('Error:', error);
                        });
                },
                'Delete',
                'btn-delete'
            );
        }

        window.onclick = function (event) {
            const confirmModal = document.getElementById('confirmModal');
            const feedbackModal = document.getElementById('feedbackModal');

            if (event.target === confirmModal) {
                closeConfirmModal();
            }
            if (event.target === feedbackModal) {
                closeFeedbackModal();
            }

            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }

        function searchDepartments() {
            const input = document.getElementById('deptSearch');
            const filter = input.value.toUpperCase();
            const statusFilter = document.getElementById('deptStatusFilter').value;
            const table = document.getElementById('deptTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let textFound = false;

                for (let j = 0; j < td.length - 1; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            textFound = true;
                            break;
                        }
                    }
                }

                let statusMatches = true;
                if (statusFilter !== '') {
                    const badge = tr[i].querySelector('.status-badge');
                    if (badge) {
                        const isActive = badge.classList.contains('status-active') ? '1' : '0';
                        statusMatches = (isActive === statusFilter);
                    }
                }

                tr[i].style.display = (textFound && statusMatches) ? '' : 'none';
            }
        }

        function searchServices() {
            const input = document.getElementById('serviceSearch');
            const filter = input.value.toUpperCase();
            const statusFilter = document.getElementById('serviceStatusFilter').value;
            const table = document.getElementById('serviceTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let textFound = false;

                for (let j = 0; j < td.length - 1; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            textFound = true;
                            break;
                        }
                    }
                }

                let statusMatches = true;
                if (statusFilter !== '') {
                    const badge = tr[i].querySelector('.status-badge');
                    if (badge) {
                        const isActive = badge.classList.contains('status-active') ? '1' : '0';
                        statusMatches = (isActive === statusFilter);
                    }
                }

                tr[i].style.display = (textFound && statusMatches) ? '' : 'none';
            }
        }

        document.querySelectorAll('#deptModal form, #serviceModal form').forEach(form => {
            form.addEventListener('submit', function () {
                const activeTab = document.querySelector('.tab-content.active');
                if (activeTab) {
                    const tabInput = document.createElement('input');
                    tabInput.type = 'hidden';
                    tabInput.name = 'tab';
                    tabInput.value = activeTab.id;
                    this.appendChild(tabInput);
                }
            });
        });

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;

            const icon = type === 'success' ? '✓' : '✕';
            const iconColor = type === 'success' ? '#22c55e' : '#ef4444';

            toast.innerHTML = `
        <div class="toast-icon" style="color: ${iconColor};">${icon}</div>
        <div class="toast-message">${message}</div>
    `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab');

            if (activeTab) {
                switchTab(activeTab);
            } else {
                switchTab('departments');
            }

            <?php if (isset($_SESSION['success'])): ?>
                showToast('<?php echo addslashes($_SESSION['success']); ?>', 'success');
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                showToast('<?php echo addslashes($_SESSION['error']); ?>', 'error');
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });

        function toggleDepartment(id, status, name) {
            const action = status == 1 ? 'activate' : 'deactivate';
            const actionCapital = status == 1 ? 'Activate' : 'Deactivate';
            const icon = status == 1 ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-ban"></i>';
            const iconColor = status == 1 ? '#4caf50' : '#ff9800';

            showConfirmModal(
                `${actionCapital} Department`,
                `Are you sure you want to ${action} "${name}"?`,
                icon,
                iconColor,
                function () {
                    fetch('../api/toggle_department.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ department_id: id, status: status })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message, 'success');

                                const rows = document.querySelectorAll('#deptTable tbody tr');
                                rows.forEach(row => {
                                    const editBtn = row.querySelector('.btn-edit');
                                    if (editBtn && editBtn.onclick.toString().includes(`"id":${id}`)) {
                                        const badge = row.querySelector('.status-badge');
                                        const actionBtn = row.querySelector('.btn-warning, .btn-success');

                                        if (status == 1) {
                                            badge.className = 'status-badge status-active';
                                            badge.textContent = 'Active';
                                            actionBtn.className = 'btn btn-warning';
                                            actionBtn.innerHTML = '<i class="fas fa-ban"></i> Deactivate';
                                            actionBtn.onclick = function () { toggleDepartment(id, 0, name); };
                                            row.style.backgroundColor = '';
                                            row.style.opacity = '';
                                        } else {
                                            badge.className = 'status-badge status-inactive';
                                            badge.textContent = 'Inactive';
                                            actionBtn.className = 'btn btn-success';
                                            actionBtn.innerHTML = '<i class="fas fa-check-circle"></i> Activate';
                                            actionBtn.onclick = function () { toggleDepartment(id, 1, name); };
                                            row.style.backgroundColor = '#fff3cd';
                                            row.style.opacity = '0.8';
                                        }
                                    }
                                });

                                // Update the service modal dropdown immediately
                                const deptDropdown = document.getElementById('serviceDeptId');
                                const option = deptDropdown.querySelector(`option[value="${id}"]`);

                                if (status == 0) {
                                    // Deactivated - remove from dropdown
                                    if (option) {
                                        option.remove();
                                    }
                                } else {
                                    // Activated - add back to dropdown if not present
                                    if (!option) {
                                        // Fetch updated dropdown from server
                                        fetch('manage_departments.php?tab=departments')
                                            .then(response => response.text())
                                            .then(html => {
                                                const parser = new DOMParser();
                                                const doc = parser.parseFromString(html, 'text/html');
                                                const newDeptSelect = doc.querySelector('#serviceDeptId');
                                                document.querySelector('#serviceDeptId').innerHTML = newDeptSelect.innerHTML;
                                            });
                                    }
                                }
                            } else {
                                showFeedbackModal('Error', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            showFeedbackModal('Error', 'An error occurred. Please try again.', 'error');
                            console.error('Error:', error);
                        });
                },
                actionCapital,
                status == 1 ? 'btn-success' : 'btn-warning'
            );
        }

        function toggleService(id, status, name) {
            const action = status == 1 ? 'activate' : 'deactivate';
            const actionCapital = status == 1 ? 'Activate' : 'Deactivate';
            const icon = status == 1 ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-ban"></i>';
            const iconColor = status == 1 ? '#4caf50' : '#ff9800';

            showConfirmModal(
                `${actionCapital} Service`,
                `Are you sure you want to ${action} "${name}"?`,
                icon,
                iconColor,
                function () {
                    fetch('../api/toggle_service.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ service_id: id, status: status })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message, 'success');

                                const rows = document.querySelectorAll('#serviceTable tbody tr');
                                rows.forEach(row => {
                                    const editBtn = row.querySelector('.btn-edit');
                                    if (editBtn && editBtn.onclick.toString().includes(`"id":${id}`)) {
                                        const badge = row.querySelector('.status-badge');
                                        const actionBtn = row.querySelector('.btn-warning, .btn-success');

                                        if (status == 1) {
                                            badge.className = 'status-badge status-active';
                                            badge.textContent = 'Active';
                                            actionBtn.className = 'btn btn-warning';
                                            actionBtn.innerHTML = '<i class="fas fa-ban"></i> Deactivate';
                                            actionBtn.onclick = function () { toggleService(id, 0, name); };
                                            row.style.backgroundColor = '';
                                            row.style.opacity = '';
                                        } else {
                                            badge.className = 'status-badge status-inactive';
                                            badge.textContent = 'Inactive';
                                            actionBtn.className = 'btn btn-success';
                                            actionBtn.innerHTML = '<i class="fas fa-check-circle"></i> Activate';
                                            actionBtn.onclick = function () { toggleService(id, 1, name); };
                                            row.style.backgroundColor = '#fff3cd';
                                            row.style.opacity = '1';
                                        }
                                    }
                                });
                            } else {
                                showFeedbackModal('Error', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            showFeedbackModal('Error', 'An error occurred. Please try again.', 'error');
                            console.error('Error:', error);
                        });
                },
                actionCapital,
                status == 1 ? 'btn-success' : 'btn-warning'
            );
        }

        function showConfirmModal(title, message, icon, iconColor, onConfirm, confirmBtnText = 'Confirm', confirmBtnClass = 'btn-delete') {
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

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').classList.remove('active');
        }

        function submitDeptForm(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            })
                .then(response => response.text())
                .then(() => {
                    const action = data.action;
                    const isAdd = action === 'add_department';

                    showToast(
                        isAdd ? 'Department added successfully!' : 'Department updated successfully!',
                        'success'
                    );

                    closeDeptModal();

                    setTimeout(() => {
                        reloadTableData('departments');
                    }, 500);
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                });
        }

        function submitServiceForm(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            })
                .then(response => response.text())
                .then(() => {
                    const action = data.action;
                    const isAdd = action === 'add_service';

                    showToast(
                        isAdd ? 'Service added successfully!' : 'Service updated successfully!',
                        'success'
                    );

                    closeServiceModal();

                    setTimeout(() => {
                        reloadTableData('services');
                        reloadTableData('departments'); // ADD THIS LINE
                    }, 500);
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                });
        }
        function reloadTableData(tab) {
            fetch(`manage_departments.php?tab=${tab}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    if (tab === 'departments') {
                        const newTable = doc.querySelector('#deptTable tbody');
                        document.querySelector('#deptTable tbody').innerHTML = newTable.innerHTML;
                    } else {
                        const newTable = doc.querySelector('#serviceTable tbody');
                        document.querySelector('#serviceTable tbody').innerHTML = newTable.innerHTML;
                    }
                });
        }
        function filterServices() {
            const departmentFilter = document.getElementById('departmentFilter').value;
            const statusFilter = document.getElementById('serviceStatusFilter').value;
            const searchInput = document.getElementById('serviceSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#serviceTable tbody tr');

            rows.forEach(row => {
                const departmentId = row.getAttribute('data-department-id');
                const badge = row.querySelector('.status-badge');
                const isActive = badge.classList.contains('status-active') ? '1' : '0';
                const serviceName = row.cells[0].textContent.toLowerCase();

                const departmentMatch = !departmentFilter || departmentId === departmentFilter;
                const statusMatch = !statusFilter || isActive === statusFilter;
                const searchMatch = !searchInput || serviceName.includes(searchInput);

                if (departmentMatch && statusMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>

</body>

</html>

<?php include '../includes/footer.php'; ?>