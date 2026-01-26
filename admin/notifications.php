<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle mark all as read
if (isset($_POST['mark_all_read'])) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1");
        $stmt->execute();
        $_SESSION['success'] = 'All notifications marked as read';
    } catch(Exception $e) {
        $_SESSION['error'] = 'Failed to mark all as read';
    }
    header('Location: notifications.php');
    exit();
}

// Handle delete notification
if (isset($_POST['delete_notification']) && isset($_POST['notification_id'])) {
    $notification_id = (int)$_POST['notification_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->execute([$notification_id]);
        $_SESSION['success'] = 'Notification deleted successfully';
    } catch(Exception $e) {
        $_SESSION['error'] = 'Failed to delete notification';
    }
    header('Location: notifications.php');
    exit();
}

// Handle mark as read
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notification_id = (int)$_POST['notification_id'];
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$notification_id]);
        $_SESSION['success'] = 'Notification marked as read';
    } catch(Exception $e) {
        $_SESSION['error'] = 'Failed to mark as read';
    }
    header('Location: notifications.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filters
$user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$read_filter = isset($_GET['read']) ? $_GET['read'] : '';

// Build query
$where = [];
$params = [];

if ($user_filter) {
    $where[] = "n.user_id = ?";
    $params[] = $user_filter;
}

if ($type_filter) {
    $where[] = "n.type = ?";
    $params[] = $type_filter;
}

if ($read_filter !== '') {
    $where[] = "n.is_read = ?";
    $params[] = $read_filter === 'read' ? 1 : 0;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM notifications n $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Get notifications
$sql = "SELECT n.*, u.name as user_name, u.email as user_email,
        a.tracking_number
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        LEFT JOIN applications a ON n.application_id = a.id
        $where_clause
        ORDER BY n.created_at DESC
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn() ?: 0,
    'unread' => $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn() ?: 0,
    'today' => $pdo->query("SELECT COUNT(*) FROM notifications WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0
];

// Get all users for filter
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Notifications';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
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

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
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

        .stat-icon-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }

        .stat-icon-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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

        /* Filters */
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
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
            align-items: stretch;
        }

        .filter-group label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .filter-group select {
            width: 100%;
            padding: 0.625rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            background: white;
            transition: all 0.2s ease;
            height: 42px;
        }

        .filter-group select:focus {
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

        .btn-primary {
            border-radius: 8px !important;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        .btn-secondary {
            border-radius: 8px !important;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            color: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(226, 232, 240, 0.5);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        /* Notifications List */
        .notifications-list {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .notification-item {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 1rem;
            align-items: center;
            transition: background 0.2s ease;
        }

        .notification-item:hover {
            background: #f8fafc;
        }

        .notification-item.unread {
            background: #f0f9ff;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-icon.info {
            background: #dbeafe;
            color: #1e40af;
        }

        .notification-icon.success {
            background: #dcfce7;
            color: #166534;
        }

        .notification-icon.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .notification-icon.danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .notification-meta {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            display: flex;
            gap: 1rem;
        }

        .notification-message {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .notification-actions {
            display: flex;
            gap: 0.5rem;
            margin-left: auto;
            flex-shrink: 0;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-icon-read {
            background: #3b82f6;
            color: white;
        }

        .btn-icon-read:hover {
            background: #2563eb;
            transform: scale(1.05);
        }

        .btn-icon-delete {
            background: #ef4444;
            color: white;
        }

        .btn-icon-delete:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pagination a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
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

        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: 1fr; }
            .filters-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .filters-grid { grid-template-columns: 1fr; }
        }
        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 80px;
            left: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 9999;
            animation: slideInLeft 0.4s ease, fadeOut 0.4s ease 2.6s;
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
                transform: translateX(-400px);
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
            <h1><i class="fas fa-bell"></i> Notifications</h1>
            <p>Manage and track all system notifications</p>
        </div>
        <div class="header-actions">
            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="mark_all_read" value="1">
                <button type="submit" class="btn btn-white">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
            </form>
        </div>
    </div>


    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-primary">
                    <i class="fas fa-bell"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Total Notifications</h3>
                <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-warning">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Unread</h3>
                <div class="stat-value"><?php echo number_format($stats['unread']); ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-info">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Today</h3>
                <div class="stat-value"><?php echo number_format($stats['today']); ?></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <div class="filters-header">
            <i class="fas fa-filter" style="color: var(--primary);"></i>
            <h3>Filter Notifications</h3>
        </div>

        <form method="GET" action="">
            <div class="filters-grid">
                <div class="filter-group">
                    <label>User</label>
                    <select name="user_id">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="status_update" <?php echo $type_filter === 'status_update' ? 'selected' : ''; ?>>Status Update</option>
                        <option value="payment" <?php echo $type_filter === 'payment' ? 'selected' : ''; ?>>Payment</option>
                        <option value="approval" <?php echo $type_filter === 'approval' ? 'selected' : ''; ?>>Approval</option>
                        <option value="rejection" <?php echo $type_filter === 'rejection' ? 'selected' : ''; ?>>Rejection</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select name="read">
                        <option value="">All Status</option>
                        <option value="unread" <?php echo $read_filter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                        <option value="read" <?php echo $read_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                    </select>
                </div>

                <div class="filter-group">
                    <a href="notifications.php" class="btn btn-secondary">
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

    <!-- Notifications List -->
    <div class="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>No notifications found</h3>
                <p>There are no notifications matching your filters.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                    <div class="notification-icon <?php echo $notification['type']; ?>">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">
                            <?php echo htmlspecialchars($notification['title']); ?>
                        </div>
                        <div class="notification-meta">
                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($notification['user_name']); ?></span>
                            <?php if ($notification['tracking_number']): ?>
                                <span><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($notification['tracking_number']); ?></span>
                            <?php endif; ?>
                            <span><i class="fas fa-clock"></i> <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></span>
                        </div>
                        <div class="notification-message">
                            <?php echo htmlspecialchars($notification['message']); ?>
                        </div>
                    </div>
                    <div class="notification-actions">
                        <?php if (!$notification['is_read']): ?>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                <input type="hidden" name="mark_read" value="1">
                                <button type="submit" class="btn-icon btn-icon-read" title="Mark as Read">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                            <input type="hidden" name="delete_notification" value="1">
                            <button type="submit" class="btn-icon btn-icon-delete" title="Delete" onclick="return confirm('Delete this notification?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php
                $query_params = $_GET;
                $query_params['page'] = $i;
                $query_string = http_build_query($query_params);
                ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?<?php echo $query_string; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<script>
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

// Show toast on page load
document.addEventListener('DOMContentLoaded', function() {
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