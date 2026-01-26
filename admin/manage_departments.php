<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
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
                $code = sanitizeInput($_POST['service_code']);
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
                $code = sanitizeInput($_POST['service_code']);
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
            font-family: 'Inter', -apple-system, sans-serif;
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
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
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

        /* Tabs */
        .tabs-container {
            background: white;
            border-radius: var(--radius);
            padding: 0.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 0.5rem;
        }

        .tab-btn {
            flex: 1;
            padding: 0.875rem 1.5rem;
            background: transparent;
            border: none;
            border-radius: 8px;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .tab-btn:hover {
            background: #f8fafc;
            color: var(--primary);
        }

        .tab-btn.active {
            background: var(--primary);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Add Button */
        .add-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            transition: all 0.2s ease;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        /* Table */
        .data-table {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 50px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .service-count {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.625rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Action Buttons */
        .btn {
            padding: 0.5rem 0.875rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.8125rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
        }

        .btn-edit:hover {
            background: #2563eb;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: visible;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 0.75rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.625rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 195, 74, 0.1);
        }

        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 2px solid var(--border);
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            align-items: end;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 0.625rem 1.25rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text-primary);
            padding: 0.625rem 1.25rem;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        /* Search Box */
        .search-container {
            margin-bottom: 1rem;
            position: relative;
        }

        .search-box {
            width: 100%;
            max-width: 1100px;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 195, 74, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.625rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
            font-size: 0.75rem;
        }

        .top-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            gap: 1rem;
        }

        /* Fix Modal Buttons */
        .modal-footer .btn {
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
            padding: 0;
            min-width: 120px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary));
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .tabs-container {
                flex-direction: column;
            }

            .action-btns {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            .tabs-container {
                flex-direction: column;
            }

            .action-btns {
                flex-direction: column;
            }

            .top-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }
        }

        .search-container {
            margin-bottom: 1rem;
            position: relative;
            flex: 1;
            max-width: 1100px;
        }

        .search-box {
            width: 600px;
            max-width: 600px;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .modal-body .form-control {
            font-size: 0.875rem !important;
        }

        .modal-body input.form-control,
        .modal-body textarea.form-control,
        .modal-body select.form-control {
            font-size: 0.875rem !important;
            font-family: 'Inter', -apple-system, sans-serif !important;
        }

        .modal-body input.form-control,
        .modal-body textarea.form-control,
        .modal-body select.form-control {
            font-size: 1rem !important;
            font-family: 'Inter', -apple-system, sans-serif !important;
            padding: 0.625rem !important;
            text-indent: 0 !important;
            padding-left: 0.625rem !important;
        }

        .modal-body {
            padding: 1.5rem;
        }

        #serviceModal .form-group {
            margin-bottom: 0.75rem;
        }

        #serviceModal .form-group label {
            margin-bottom: 0.375rem;
            font-size: 0.875rem;
        }

        #serviceModal .form-control {
            padding: 0.5rem 0.625rem !important;
        }

        #serviceModal textarea.form-control {
            min-height: 60px;
        }
        /* Toast Notification */
.toast-notification {
    position: fixed;
    top: 110px;
    left: 20px;  /* Change from 'right: 20px' to 'left: 20px' */
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 1rem;
    z-index: 9999;
    animation: slideInLeft 0.4s ease, fadeOut 0.4s ease 2.6s;  /* Change animation name */
    min-width: 300px;
    max-width: 500px;
}

.toast-success {
    border-left: 4px solid #22c55e;
}

.toast-error {
    border-left: 4px solid #ef4444;
}

.toast-icon {
    font-size: 1.5rem;
}

.toast-message {
    flex: 1;
    font-weight: 600;
    color: var(--text-primary);
}

@keyframes slideInLeft {
    from {
        transform: translateX(-400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateX(-400px);  /* Change to slide out left */
    }
}
/* Tablet Landscape & Smaller Desktops (1024px - 1200px) */
@media (max-width: 1200px) {
    /* Stats Grid - Works on dashboard, reports, users, etc. */
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 1rem;
    }
    
    /* Quick Actions - Works on dashboard */
    .quick-actions {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    /* Content Grid - Works on dashboard, profile */
    .content-grid {
        grid-template-columns: 1fr !important;
    }
    
    /* Grid-2 - Works on view_application */
    .grid-2 {
        grid-template-columns: 1fr !important;
    }
    
    /* Charts Grid - Works on reports */
    .charts-grid {
        grid-template-columns: 1fr !important;
    }
    
    /* Management Tools - Works on dashboard */
    .management-tools-section .quick-actions {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    /* Container - Works on ALL pages */
    .container,
    .dashboard-container {
        padding: 0 1rem 1rem 1rem;
    }
}

/* Tablet Portrait (768px - 1024px) */
@media (max-width: 1024px) {
    /* Header - Works on ALL pages */
    .page-header,
    .dashboard-header {
        padding: 1.5rem;
    }
    
    .page-header h1,
    .dashboard-header h1 {
        font-size: 1.75rem;
    }
    
    /* Stats Grid - ALL pages with stats */
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    /* Filters Grid - applications, users, activity_logs, notifications */
    .filters-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    /* Info Grid - view_application, profile */
    .info-grid {
        grid-template-columns: 1fr !important;
    }
    
    /* Payment Info Grid - verify_payments */
    .payment-info-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    /* Payment Row - verify_payments */
    .payment-row {
        grid-template-columns: 1fr !important;
        gap: 1.25rem;
    }
    
    /* Proof Actions - verify_payments */
    .proof-actions-section {
        justify-content: space-between;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }
    
    .action-btns-compact {
        flex-direction: row !important;
    }
    
    /* Table - ALL pages with tables */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .modern-table th,
    .modern-table td {
        padding: 0.75rem;
        font-size: 0.8125rem;
    }
    
    /* Action Buttons - applications, users */
    .action-buttons,
    .action-btns {
        flex-direction: column;
        gap: 0.375rem;
    }
}

/* Mobile & Small Tablets (480px - 768px) */
@media (max-width: 768px) {
    /* ========================================
       GLOBAL ELEMENTS (All Pages)
       ======================================== */
    
    .container,
    .dashboard-container {
        padding: 0 0.75rem 0.75rem 0.75rem;
    }
    
    /* Page Header - ALL PAGES */
    .page-header,
    .dashboard-header {
        padding: 1.25rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .page-header h1,
    .dashboard-header h1 {
        font-size: 1.5rem;
    }
    
    .page-header p {
        font-size: 0.875rem;
    }
    
    /* Header Content - ALL PAGES */
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        width: 100%;
    }
    
    .header-left,
    .header-right {
        width: 100%;
    }
    
    .header-right {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    /* Header Actions - ALL PAGES */
    .header-actions {
        width: 100%;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .header-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    /* ========================================
       STATS COMPONENTS
       (dashboard, users, reports, notifications, etc.)
       ======================================== */
    
    .stats-grid {
        grid-template-columns: 1fr !important;
        gap: 0.75rem;
    }
    
    .stat-card {
        padding: 1.25rem;
    }
    
    .stat-value {
        font-size: 2rem;
    }
    
    /* ========================================
       MANAGEMENT TOOLS & QUICK ACTIONS
       (dashboard)
       ======================================== */
    
    .quick-actions,
    .management-tools-section .quick-actions {
        grid-template-columns: 1fr !important;
        gap: 1rem;
    }
    
    .action-btn {
        padding: 1.5rem 1rem;
    }
    
    .action-btn .icon-logo {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }
    
    .action-btn span {
        font-size: 0.875rem;
    }
    
    /* ========================================
       FILTERS SECTION
       (applications, users, activity_logs, notifications, reports)
       ======================================== */
    
    .filters-section {
        padding: 1rem;
    }
    
    .filters-grid {
        grid-template-columns: 1fr !important;
        gap: 0.75rem;
    }
    
    .filter-group .btn {
        height: 44px;
    }
    
    .filter-actions {
        grid-template-columns: 1fr !important;
    }
    
    /* Search Container - applications, users, manage_departments */
    .search-container {
        width: 100%;
        max-width: 100%;
    }
    
    .search-box {
        width: 100%;
        max-width: 100%;
    }
    
    /* Top Controls - applications, users, manage_departments */
    .top-controls {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    
    /* ========================================
       FORM CONTROLS
       (ALL pages with forms)
       ======================================== */
    
    .form-control {
        height: 44px;
        padding: 0.625rem;
        font-size: 0.875rem;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group label {
        font-size: 0.8125rem;
    }
    
    /* ========================================
       BUTTONS
       (ALL pages)
       ======================================== */
    
    .btn {
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
    }
    
    .add-btn {
        width: 100%;
        justify-content: center;
    }
    
    /* ========================================
       RESULTS INFO
       (applications, users, activity_logs)
       ======================================== */
    
    .results-info {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
        padding: 0.75rem 1rem;
    }
    
    /* ========================================
       TABLES
       (ALL pages with tables)
       ======================================== */
    
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .modern-table {
        min-width: 800px;
    }
    
    .modern-table th,
    .modern-table td {
        padding: 0.625rem;
        font-size: 0.75rem;
    }
    
    .data-table {
        overflow-x: auto;
    }
    
    /* ========================================
       TABS
       (manage_departments)
       ======================================== */
    
    .tabs-container {
        flex-direction: column;
        gap: 0.375rem;
    }
    
    .tab-btn {
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
    }
    
    /* ========================================
       INFO DISPLAYS
       (view_application, profile)
       ======================================== */
    
    .info-display,
    .info-grid {
        grid-template-columns: 1fr !important;
    }
    
    .info-item {
        padding: 0.75rem;
    }
    
    .info-label {
        font-size: 0.625rem;
    }
    
    .info-value {
        font-size: 0.875rem;
    }
    
    /* ========================================
       PAYMENT COMPONENTS
       (verify_payments, view_application)
       ======================================== */
    
    .payment-info-grid {
        grid-template-columns: 1fr !important;
    }
    
    .payment-proof {
        grid-template-columns: 1fr !important;
    }
    
    .proof-preview {
        width: 100%;
        height: 250px;
    }
    
    .proof-buttons {
        flex-direction: column;
        width: 100%;
    }
    
    .proof-buttons .btn {
        width: 100%;
    }
    
    .proof-thumb {
        width: 100%;
        height: 200px;
    }
    
    .info-item-compact {
        padding: 0.75rem;
    }
    
    .info-label-compact {
        font-size: 0.625rem;
    }
    
    .info-value-compact {
        font-size: 0.875rem;
    }
    
    /* ========================================
       ACTION BUTTONS
       (applications, users, verify_payments)
       ======================================== */
    
    .action-buttons {
        grid-template-columns: 1fr !important;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
    
    /* ========================================
       STATUS & BADGES
       (ALL pages)
       ======================================== */
    
    .status-banner {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .status-badge,
    .role-badge {
        padding: 0.375rem 0.75rem;
        font-size: 0.625rem;
    }
    
    /* ========================================
       USER COMPONENTS
       (users, applications, verify_payments)
       ======================================== */
    
    .profile-avatar {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .user-info {
        gap: 0.5rem;
    }
    
    .user-avatar {
        width: 36px;
        height: 36px;
        font-size: 0.875rem;
    }
    
    .applicant-section {
        gap: 0.625rem;
    }
    
    .applicant-avatar-sm {
        width: 44px;
        height: 44px;
        font-size: 1.125rem;
    }
    
    .applicant-details-sm h4 {
        font-size: 0.9375rem;
    }
    
    .meta-row {
        flex-direction: column;
        gap: 0.375rem;
        align-items: flex-start;
        font-size: 0.75rem;
    }
    
    /* ========================================
       DOCUMENTS
       (view_application)
       ======================================== */
    
    .document-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .doc-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .doc-actions .btn {
        width: 100%;
    }
    
    /* ========================================
       ACTIVITY & TIMELINE
       (dashboard, profile, view_application)
       ======================================== */
    
    .activity-item {
        padding-left: 2rem;
    }
    
    .activity-icon {
        width: 2rem;
        height: 2rem;
        font-size: 0.75rem;
    }
    
    .activity-content {
        padding: 0.75rem;
    }
    
    .timeline-item {
        padding-left: 1.5rem;
    }
    
    .timeline-content {
        padding: 0.75rem;
    }
    
    .timeline-date,
    .timeline-user {
        font-size: 0.75rem;
    }
    
    /* ========================================
       PAGINATION
       (ALL pages with pagination)
       ======================================== */
    
    .pagination {
        flex-wrap: wrap;
        gap: 0.375rem;
    }
    
    .page-btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        min-width: 36px;
    }
    
    /* ========================================
       MODALS
       (ALL pages with modals)
       ======================================== */
    
    .modal {
        padding: 0.5rem;
    }
    
    .modal-content {
        width: 100%;
        max-width: 100%;
        max-height: 95vh;
        padding: 1rem;
    }
    
    .modal-header {
        padding: 1rem;
    }
    
    .modal-header h3 {
        font-size: 1.125rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .modal-footer {
        padding: 0.75rem 1rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .modal-footer .btn {
        width: 100%;
    }
    
    .modal-actions {
        grid-template-columns: 1fr !important;
        gap: 0.5rem;
    }
    
    .modal-actions .btn {
        width: 100%;
        height: 44px !important;
    }
    
    .close-modal {
        width: 36px;
        height: 36px;
    }
    
    /* ========================================
       NOTIFICATIONS & ALERTS
       (ALL pages)
       ======================================== */
    
    .toast-notification {
        top: 70px;
        left: 10px;
        right: 10px;
        min-width: auto;
        max-width: none;
    }
    
    .alert {
        padding: 0.75rem;
        font-size: 0.875rem;
    }
    
    /* Notification Item - notifications page */
    .notification-item {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .notification-meta {
        flex-direction: column;
        gap: 0.25rem;
        align-items: flex-start;
    }
    
    .notification-actions {
        width: 100%;
        justify-content: flex-start;
        margin-top: 0.75rem;
    }
    
    /* ========================================
       CHARTS
       (reports, dashboard)
       ======================================== */
    
    .chart-wrapper {
        height: 250px;
    }
    
    .chart-card {
        padding: 1rem;
    }
    
    /* ========================================
       SPECIAL COMPONENTS
       ======================================== */
    
    /* Notes Row - verify_payments */
    .notes-row {
        padding: 0.75rem 1rem;
        font-size: 0.8125rem;
    }
    
    /* Empty State - ALL pages */
    .empty-state {
        padding: 2rem 1rem;
    }
    
    .empty-state i {
        font-size: 3rem;
    }
    
    .empty-state h3 {
        font-size: 1.25rem;
    }
    
    /* Payment Card - verify_payments */
    .payment-card-compact {
        margin-bottom: 0.75rem;
    }
}

/* Small Mobile (320px - 480px) */
@media (max-width: 480px) {
    .container,
    .dashboard-container {
        padding: 0 0.5rem 0.5rem 0.5rem;
    }
    
    .page-header,
    .dashboard-header {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .page-header h1,
    .dashboard-header h1 {
        font-size: 1.25rem;
    }
    
    .page-header p {
        font-size: 0.8125rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-value {
        font-size: 1.75rem;
    }
    
    .stat-card h3 {
        font-size: 0.75rem;
    }
    
    .btn {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
    }
    
    .btn-icon {
        font-size: 0.75rem;
        padding: 0.375rem;
    }
    
    .action-btn {
        padding: 1.25rem 0.875rem;
    }
    
    .action-btn .icon-logo {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .action-btn span {
        font-size: 0.8125rem;
    }
    
    .filters-section {
        padding: 0.75rem;
    }
    
    .filters-header h3 {
        font-size: 0.875rem;
    }
    
    .form-control {
        padding: 0.5rem;
        font-size: 0.8125rem;
        height: 40px;
    }
    
    .modern-table th,
    .modern-table td {
        padding: 0.5rem;
        font-size: 0.6875rem;
    }
    
    .modal-content {
        padding: 0.75rem;
    }
    
    .modal-header {
        padding: 0.75rem;
    }
    
    .modal-header h3 {
        font-size: 1rem;
    }
    
    .modal-body {
        padding: 0.75rem;
    }
    
    .modal-footer {
        padding: 0.5rem 0.75rem;
    }
    
    .modal-footer .btn,
    .modal-actions .btn {
        height: 40px !important;
        font-size: 0.8125rem;
    }
    
    .close-modal {
        width: 32px;
        height: 32px;
        font-size: 1.5rem;
    }
    
    .toast-notification {
        top: 60px;
        left: 5px;
        right: 5px;
        padding: 0.75rem 1rem;
        font-size: 0.8125rem;
    }
    
    .applicant-details-sm h4 {
        font-size: 0.875rem;
    }
    
    .timeline-item {
        padding-left: 1.25rem;
        padding-bottom: 1rem;
    }
    
    .timeline-icon {
        width: 1.75rem;
        height: 1.75rem;
        font-size: 0.625rem;
    }
    
    .timeline-content {
        padding: 0.625rem;
    }
    
    .chart-wrapper {
        height: 200px;
    }
}

/* Landscape Orientation */
@media (max-width: 768px) and (orientation: landscape) {
    .page-header {
        padding: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    .modal-content {
        max-height: 85vh;
    }
}

/* Print Styles - ALL PAGES */
@media print {
    .header-actions,
    .status-action,
    .btn,
    .btn-icon,
    .action-buttons,
    .action-btns,
    .action-btns-compact,
    .filters-section,
    .search-container,
    .pagination,
    .modal {
        display: none !important;
    }
    
    .container,
    .dashboard-container {
        max-width: 100%;
        padding: 0;
    }
    
    .page-header {
        background: white !important;
        color: black !important;
        border: 2px solid #000;
    }
    
    .card,
    .table-card,
    .stat-card {
        page-break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ddd;
    }
}

/* Touch Devices - ALL PAGES */
@media (hover: none) and (pointer: coarse) {
    .btn,
    .page-btn,
    .btn-icon {
        min-height: 44px;
        min-width: 44px;
    }
    
    .action-buttons,
    .action-btns,
    .header-actions {
        gap: 0.75rem;
    }
}

/* Accessibility - Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Fix long content overflow - ALL PAGES */
@media (max-width: 768px) {
    .tracking-number {
        word-break: break-all;
        font-size: 0.8125rem;
    }
    
    .card {
        overflow: hidden;
    }
    
    .card * {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .user-details,
    .applicant-details-sm,
    .notification-meta {
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .table-container::-webkit-scrollbar {
        height: 6px;
    }
    
    .table-container::-webkit-scrollbar-track {
        background: var(--background);
    }
    
    .table-container::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 3px;
    }
    
    iframe {
        max-width: 100%;
    }
}
    </style>

</head>

<body>

    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <h1><i class="fas fa-building"></i> Manage Departments & Services</h1>
                <p>Add, edit, or remove departments and their associated services</p>
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
                            <tr>
                                <td><strong><?php echo htmlspecialchars($dept['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($dept['code']); ?></td>
                                <td><?php echo htmlspecialchars(substr($dept['description'], 0, 80)); ?>...</td>
                                <td>
                                    <span class="service-count">
                                        <?php echo $dept['service_count']; ?> services
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
                <button onclick="showAddServiceModal()" class="add-btn">
                    <i class="fas fa-plus"></i> Add New Service
                </button>
            </div>

            <div class="data-table">
                <table id="serviceTable">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th>Base Fee</th>
                            <th>Processing Days</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($service['service_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($service['service_code']); ?></td>
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
            <form method="POST" action="">
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

    <!-- Service Modal -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="serviceModalTitle">Add Service</h3>
                <button class="close-modal" onclick="closeServiceModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" id="serviceAction" value="add_service">
                    <input type="hidden" name="service_id" id="serviceId">

                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department_id" id="serviceDeptId" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Service Name *</label>
                        <input type="text" name="service_name" id="serviceName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Service Code *</label>
                        <input type="text" name="service_code" id="serviceCode" class="form-control" required>
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
            
            // Activate the tab button
            const tabBtn = document.querySelector(`.tab-btn[onclick*="${tab}"]`);
            if (tabBtn) {
                tabBtn.classList.add('active');
            }
            
            // Activate the tab content
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
                alert('Cannot delete department with active services. Delete services first.');
                return;
            }

            if (confirm('Are you sure you want to delete this department?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
            <input type="hidden" name="action" value="delete_department">
            <input type="hidden" name="department_id" value="${id}">
        `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function showAddServiceModal() {
            document.getElementById('serviceModalTitle').textContent = 'Add Service';
            document.getElementById('serviceAction').value = 'add_service';
            document.getElementById('serviceId').value = '';
            document.getElementById('serviceDeptId').value = '';
            document.getElementById('serviceName').value = '';
            document.getElementById('serviceCode').value = '';
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
            document.getElementById('serviceCode').value = service.service_code;
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
            if (confirm('Are you sure you want to delete this service?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
            <input type="hidden" name="action" value="delete_service">
            <input type="hidden" name="service_id" value="${id}">
            <input type="hidden" name="tab" value="services">
        `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        window.onclick = function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }

        function searchDepartments() {
            const input = document.getElementById('deptSearch');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('deptTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < td.length - 1; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = found ? '' : 'none';
            }
        }

        function searchServices() {
            const input = document.getElementById('serviceSearch');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('serviceTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < td.length - 1; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = found ? '' : 'none';
            }
        }


// Save tab state before form submission
document.querySelectorAll('#deptModal form, #serviceModal form').forEach(form => {
    form.addEventListener('submit', function() {
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
// Combined DOMContentLoaded - Handle both tab switching and toast notifications
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab switching from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    if (activeTab) {
        switchTab(activeTab);
    } else {
        switchTab('departments');
    }
    
    // Show toast notifications
    <?php if (isset($_SESSION['success'])): ?>
        showToast('<?php echo addslashes($_SESSION['success']); ?>', 'success');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        showToast('<?php echo addslashes($_SESSION['error']); ?>', 'error');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
});
    </script>

</body>

</html>

<?php include '../includes/footer.php'; ?>