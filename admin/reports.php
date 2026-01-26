<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Date range for reports
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');

// Applications by status
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as count
    FROM applications
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY status
");
$stmt->execute([$date_from, $date_to]);
$status_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Applications by service
$stmt = $pdo->prepare("
    SELECT s.service_name, COUNT(*) as count
    FROM applications a
    JOIN services s ON a.service_id = s.id
    WHERE DATE(a.created_at) BETWEEN ? AND ?
    GROUP BY s.id, s.service_name
    ORDER BY count DESC
    LIMIT 10
");
$stmt->execute([$date_from, $date_to]);
$service_data = $stmt->fetchAll();

// Applications by department
$stmt = $pdo->prepare("
    SELECT d.name as department_name, COUNT(*) as count
    FROM applications a
    JOIN departments d ON a.department_id = d.id
    WHERE DATE(a.created_at) BETWEEN ? AND ?
    GROUP BY d.id, d.name
    ORDER BY count DESC
");
$stmt->execute([$date_from, $date_to]);
$department_data = $stmt->fetchAll();

// Daily applications
$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM applications
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute([$date_from, $date_to]);
$daily_data = $stmt->fetchAll();

// Revenue by service
$stmt = $pdo->prepare("
    SELECT s.service_name, SUM(s.base_fee) as total_fee, COUNT(*) as count
    FROM applications a
    JOIN services s ON a.service_id = s.id
    WHERE DATE(a.created_at) BETWEEN ? AND ?
    GROUP BY s.id, s.service_name
    ORDER BY total_fee DESC
");
$stmt->execute([$date_from, $date_to]);
$revenue_data = $stmt->fetchAll();

// Processing time analysis
$stmt = $pdo->prepare("
    SELECT 
        AVG(DATEDIFF(updated_at, created_at)) as avg_days,
        MIN(DATEDIFF(updated_at, created_at)) as min_days,
        MAX(DATEDIFF(updated_at, created_at)) as max_days
    FROM applications
    WHERE status IN ('Approved', 'Completed', 'Rejected')
    AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$date_from, $date_to]);
$processing_stats = $stmt->fetch();

// Top applicants
$stmt = $pdo->prepare("
    SELECT u.name, u.email, COUNT(a.id) as total_apps
    FROM applications a
    JOIN users u ON a.user_id = u.id
    WHERE DATE(a.created_at) BETWEEN ? AND ?
    GROUP BY a.user_id
    ORDER BY total_apps DESC
    LIMIT 10
");
$stmt->execute([$date_from, $date_to]);
$top_applicants = $stmt->fetchAll();

// Overall statistics for the period
$total_apps = array_sum($status_data);
$total_revenue_stmt = $pdo->prepare("
    SELECT SUM(s.base_fee) 
    FROM applications a 
    JOIN services s ON a.service_id = s.id 
    WHERE DATE(a.created_at) BETWEEN ? AND ?
");
$total_revenue_stmt->execute([$date_from, $date_to]);
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
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 1rem;
            align-items: end;
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
            border: 1.5px solid var(--border);
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

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 8px !important;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        .btn-export {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-radius: 8px;
        }

        .btn-export:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Ensure consistent button heights */
        .filter-group button {
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .filter-group button,
        .filter-group .btn {
            border-radius: 8px !important;
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
            border-radius: var(--radius);
            padding: 1.75rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-label {
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

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .chart-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .chart-card.full-width {
            grid-column: 1 / -1;
        }

        .chart-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-card h3 i {
            color: var(--primary);
        }

        /* Tables */
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

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 1024px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem 1rem 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
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
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-chart-line"></i> Reports & Analytics</h1>
            <p>Comprehensive insights and statistics for <?php echo date('F d, Y', strtotime($date_from)); ?> - <?php echo date('F d, Y', strtotime($date_to)); ?></p>
        </div>

        <!-- Date Filter -->
        <div class="filters-section">
            <div class="filters-header">
                <h3>
                    <i class="fas fa-calendar-alt"></i>
                    Report Period
                </h3>
            </div>

            <form method="GET" action="" id="filterForm">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>" required>
                    </div>

                    <div class="filter-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>" required>
                    </div>

                    <div class="filter-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-sync-alt"></i> Generate Report
                        </button>
                    </div>

                    <div class="filter-group" style="display: flex; align-items: flex-end;">
                        <button type="button" onclick="exportReport()" class="btn btn-export" style="width: 100%;">
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
                <div class="stat-value" style="font-size: 1.75rem;">₱<?php echo number_format($total_revenue ?? 0, 2); ?></div>
            </div>

            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="stat-label">Avg. Processing Time</div>
                <div class="stat-value"><?php echo round($processing_stats['avg_days'] ?? 0, 1); ?> <span style="font-size: 1rem;">days</span></div>
            </div>

            <div class="stat-card" style="border-left-color: #3b82f6;">
                <div class="stat-label">Approval Rate</div>
                <div class="stat-value">
                    <?php 
                    $approval_rate = $total_apps > 0 
                        ? round((($status_data['Approved'] ?? 0) + ($status_data['Completed'] ?? 0)) / $total_apps * 100, 1)
                        : 0;
                    echo $approval_rate; 
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

            <!-- Applications by Department -->
            <div class="chart-card">
                <h3>
                    <i class="fas fa-building"></i>
                    Applications by Department
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
                                    <td><strong style="color: var(--primary);">₱<?php echo number_format($row['total_fee'], 2); ?></strong></td>
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
                            <?php $rank = 1; foreach ($top_applicants as $applicant): ?>
                                <tr>
                                    <td><strong><?php echo $rank++; ?></strong></td>
                                    <td><?php echo htmlspecialchars($applicant['name']); ?></td>
                                    <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                                    <td><strong style="color: var(--primary);"><?php echo $applicant['total_apps']; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
                    labels: <?php echo json_encode(array_keys($status_data)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($status_data)); ?>,
                        backgroundColor: ['#fbbf24', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6'],
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

        // Department Chart
        const deptCtx = document.getElementById('departmentChart');
        if (deptCtx) {
            new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($department_data, 'department_name')); ?>,
                    datasets: [{
                        label: 'Applications',
                        data: <?php echo json_encode(array_column($department_data, 'count')); ?>,
                        backgroundColor: '#8bc34a',
                        borderRadius: 8,
                        barThickness: 40
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
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>