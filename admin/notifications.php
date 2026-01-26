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
        .burger-menu {
    display: none;
    flex-direction: column;
    gap: 0.4rem;
    cursor: pointer;
    background: var(--primary);
    border: none;
    padding: 0.75rem;
    border-radius: 8px;
    z-index: 1001;
    position: relative;
    transition: all 0.3s ease;
}

.burger-menu:hover {
    background: var(--primary-dark);
    transform: scale(1.05);
}

.burger-menu span {
    width: 25px;
    height: 3px;
    background: white;
    border-radius: 3px;
    transition: all 0.3s ease;
    display: block;
}

/* Animated burger menu when active */
.burger-menu.active span:nth-child(1) {
    transform: rotate(45deg) translate(8px, 8px);
}

.burger-menu.active span:nth-child(2) {
    opacity: 0;
}

.burger-menu.active span:nth-child(3) {
    transform: rotate(-45deg) translate(8px, -8px);
}

/* Mobile Navigation Overlay */
.mobile-nav-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.mobile-nav-overlay.show {
    display: block;
    opacity: 1;
}

/* Mobile Navigation Menu */
.mobile-nav-menu {
    display: none;
    position: fixed;
    top: 0;
    right: -100%;
    width: 280px;
    height: 100%;
    background: white;
    z-index: 1000;
    padding: 1.5rem;
    overflow-y: auto;
    box-shadow: -4px 0 20px rgba(0, 0, 0, 0.2);
    transition: right 0.3s ease;
}

.mobile-nav-menu.show {
    display: block;
    right: 0;
}

/* Mobile Nav Header */
.mobile-nav-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
    margin-bottom: 1.5rem;
}

.mobile-nav-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
}

.mobile-nav-close {
    background: none;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    color: var(--text-secondary);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.mobile-nav-close:hover {
    background: var(--background);
    color: var(--text-primary);
}

/* Mobile Nav Links */
.mobile-nav-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    color: var(--text-primary);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.mobile-nav-link:hover {
    background: linear-gradient(135deg, var(--primary-light) 0%, rgba(139, 195, 74, 0.2) 100%);
    color: var(--primary-dark);
}

.mobile-nav-link.active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
}

.mobile-nav-link i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
}

/* Mobile Nav Section */
.mobile-nav-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--border);
}

.mobile-nav-section-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
    padding-left: 1rem;
}

/* RESPONSIVE BREAKPOINTS */
@media (max-width: 1024px) {
    /* Show burger menu on tablets and below */
    .burger-menu {
        display: flex !important;
    }
}

@media (max-width: 768px) {
    /* Adjust container padding */
    .container {
        padding: 0 1rem 1rem 1rem !important;
    }

    /* Adjust page header */
    .page-header {
        padding: 1.5rem !important;
    }

    .page-header h1 {
        font-size: 1.5rem !important;
    }

    .page-header p {
        font-size: 0.875rem !important;
    }

    /* Stack header content */
    .header-content {
        flex-direction: column !important;
        gap: 1rem !important;
        align-items: flex-start !important;
    }

    .header-actions {
        width: 100%;
        flex-direction: column !important;
    }

    .header-actions .btn {
        width: 100%;
        justify-content: center;
    }

    /* Adjust stats grid */
    .stats-grid {
        grid-template-columns: 1fr !important;
        gap: 1rem !important;
    }

    /* Adjust filters */
    .filters-grid {
        grid-template-columns: 1fr !important;
        gap: 1rem !important;
    }

    /* Adjust cards */
    .card {
        padding: 1rem !important;
    }

    .card h3 {
        font-size: 1rem !important;
    }

    /* Adjust tables */
    .table-container {
        overflow-x: auto;
    }

    .modern-table {
        min-width: 800px;
    }

    /* Adjust modals */
    .modal-content {
        width: 95%;
        margin: 1rem;
        max-height: 95vh;
    }

    .modal-header h3 {
        font-size: 1.125rem !important;
    }

    /* Adjust buttons */
    .btn {
        padding: 0.625rem 1rem !important;
        font-size: 0.875rem !important;
    }

    /* Fix mobile navigation menu width */
    .mobile-nav-menu {
        width: 85%;
        max-width: 320px;
    }
}

@media (max-width: 480px) {
    /* Extra small screens */
    .page-header h1 {
        font-size: 1.25rem !important;
    }

    .stat-value {
        font-size: 2rem !important;
    }

    .mobile-nav-menu {
        width: 90%;
    }
}

/* Animation Keyframes */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Apply animations when menu opens */
.mobile-nav-menu.show {
    animation: slideInRight 0.3s ease;
}

.mobile-nav-overlay.show {
    animation: fadeIn 0.3s ease;
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
<!-- Burger Menu Button -->
<button class="burger-menu" id="burgerMenu" onclick="toggleMobileNav()">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Mobile Navigation Overlay -->
<div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="closeMobileNav()"></div>

<!-- Mobile Navigation Menu -->
<div class="mobile-nav-menu" id="mobileNavMenu">
    <div class="mobile-nav-header">
        <h3>Admin Menu</h3>
        <button class="mobile-nav-close" onclick="closeMobileNav()">&times;</button>
    </div>

    <!-- Main Navigation -->
    <div class="mobile-nav-links">
        <a href="dashboard.php" class="mobile-nav-link">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="applications.php" class="mobile-nav-link">
            <i class="fas fa-clipboard-list"></i>
            <span>Applications</span>
        </a>
        <a href="verify_payments.php" class="mobile-nav-link">
            <i class="fas fa-money-check-alt"></i>
            <span>Verify Payments</span>
        </a>
        <a href="users.php" class="mobile-nav-link">
            <i class="fas fa-users"></i>
            <span>User Management</span>
        </a>
        <a href="notifications.php" class="mobile-nav-link">
            <i class="fas fa-bell"></i>
            <span>Notifications</span>
        </a>
    </div>

    <!-- Management Section -->
    <div class="mobile-nav-section">
        <div class="mobile-nav-section-title">Management</div>
        <div class="mobile-nav-links">
            <a href="manage_departments.php" class="mobile-nav-link">
                <i class="fas fa-building"></i>
                <span>Departments & Services</span>
            </a>
            <a href="reports.php" class="mobile-nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </a>
            <a href="activity_logs.php" class="mobile-nav-link">
                <i class="fas fa-history"></i>
                <span>Activity Logs</span>
            </a>
            <a href="check_email_and_sms_logs.php" class="mobile-nav-link">
                <i class="fas fa-envelope"></i>
                <span>Email/SMS Logs</span>
            </a>
        </div>
    </div>

    <!-- Account Section -->
    <div class="mobile-nav-section">
        <div class="mobile-nav-section-title">Account</div>
        <div class="mobile-nav-links">
            <a href="profile.php" class="mobile-nav-link">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
            <a href="../auth/logout.php" class="mobile-nav-link" style="color: #ef4444;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
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