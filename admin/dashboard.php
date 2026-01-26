<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
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
    // Overall statistics
    $overall_stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn() ?: 0,
        'pending' => $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Pending'")->fetchColumn() ?: 0,
        'processing' => $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Processing'")->fetchColumn() ?: 0,
        'approved' => $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Approved'")->fetchColumn() ?: 0,
        'rejected' => $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Rejected'")->fetchColumn() ?: 0,
        'completed' => $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Completed'")->fetchColumn() ?: 0,
    ];

    // Recent applications
    $recent_apps = $pdo->query("
        SELECT a.*, u.name as applicant_name, s.service_name
        FROM applications a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN services s ON a.service_id = s.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ")->fetchAll();

    // Applications by status
    $status_breakdown = $pdo->query("
        SELECT status, COUNT(*) as count
        FROM applications
        GROUP BY status
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Recent activity
    $recent_activity = $pdo->query("
        SELECT al.*, u.name
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT 8
    ")->fetchAll();

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    log_security_event('DATABASE_ERROR', $e->getMessage());
    die("Database Error: Please contact administrator.");
}

$pageTitle = 'Admin Dashboard';
// Note: If header.php already has a navbar, you might want to comment it out or remove it to use this new one.
include '../includes/header.php';
?>

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
        /* UPDATED: Add padding top so content isn't hidden behind the new fixed navbar */
        padding-top: 80px; 
    }

    /* =========================================
       NEW: TOP NAVIGATION BAR & BURGER MENU
       ========================================= */
    .top-navbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 70px;
        background: var(--surface);
        box-shadow: var(--shadow);
        z-index: 1000;
        padding: 0 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .nav-brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        color: var(--text-primary);
        font-weight: 700;
        font-size: 1.25rem;
    }

    .nav-brand img {
        height: 40px;
        width: auto;
    }

    .burger-menu {
        display: flex;
        flex-direction: column;
        gap: 5px;
        cursor: pointer;
        background: transparent;
        border: none;
        padding: 0.5rem;
        z-index: 1002;
    }

    .burger-menu span {
        width: 25px;
        height: 3px;
        background: var(--primary-dark);
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    .burger-menu.active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 6px);
    }

    .burger-menu.active span:nth-child(2) {
        opacity: 0;
    }

    .burger-menu.active span:nth-child(3) {
        transform: rotate(-45deg) translate(5px, -6px);
    }

    /* =========================================
       NEW: SIDE DRAWER (MOBILE MENU)
       ========================================= */
    .mobile-nav-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .mobile-nav-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    .mobile-nav-menu {
        position: fixed;
        top: 0;
        right: -300px;
        width: 280px;
        height: 100%;
        background: var(--surface);
        z-index: 1001;
        padding: 1.5rem;
        box-shadow: -5px 0 25px rgba(0,0,0,0.1);
        transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }

    .mobile-nav-menu.show {
        right: 0;
    }

    .mobile-nav-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }

    .mobile-nav-header h3 {
        color: var(--primary);
        font-weight: 800;
    }

    .nav-links {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        overflow-y: auto;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 1rem;
        text-decoration: none;
        color: var(--text-primary);
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .nav-link:hover {
        background: var(--background);
        color: var(--primary);
        transform: translateX(5px);
    }

    .nav-link.active {
        background: linear-gradient(135deg, var(--primary-light) 0%, rgba(139, 195, 74, 0.2) 100%);
        color: var(--primary-dark);
        font-weight: 600;
    }

    .nav-link i {
        width: 20px;
        text-align: center;
    }

    .nav-section-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-secondary);
        font-weight: 700;
        margin: 1.5rem 0 0.5rem 1rem;
        letter-spacing: 0.5px;
    }

    /* =========================================
       EXISTING DASHBOARD STYLES
       ========================================= */

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem 1.5rem 1.5rem;
    }

    /* Header */
    .dashboard-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border-radius: var(--radius);
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
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

    .dashboard-header h1 {
        font-size: 2rem;           
        font-weight: 800;
        margin-bottom: 0.25rem;
        position: relative;
        z-index: 1;
    }

    .dashboard-header p {
        font-size: 1rem;           
        opacity: 0.95;
        position: relative;
        z-index: 1;
    }

    /* Stats Grid - Compact Version */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--surface);
        border-radius: 12px;
        padding: 1.25rem 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100%;
        background: linear-gradient(135deg, rgba(139, 195, 74, 0.05) 0%, transparent 100%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .stat-card:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        border-color: var(--primary);
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-icon-wrapper {
        margin-bottom: 1rem;
        display: flex;
        justify-content: center;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        position: relative;
        transition: all 0.4s ease;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .stat-icon-primary {
        background: linear-gradient(135deg, #8bc34a 0%, #689f38 100%);
        color: white;
        box-shadow: 0 6px 12px rgba(139, 195, 74, 0.3);
    }

    .stat-icon-warning {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: white;
        box-shadow: 0 6px 12px rgba(251, 191, 36, 0.3);
    }

    .stat-icon-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        box-shadow: 0 6px 12px rgba(59, 130, 246, 0.3);
    }

    .stat-icon-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 6px 12px rgba(16, 185, 129, 0.3);
    }

    .stat-icon-cyan {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
        box-shadow: 0 6px 12px rgba(6, 182, 212, 0.3);
    }

    .stat-icon-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 6px 12px rgba(239, 68, 68, 0.3);
    }

    .stat-content {
        text-align: center;
    }

    .stat-content h3 {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        letter-spacing: -0.02em;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, var(--text-primary) 0%, var(--primary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.7rem;
        font-weight: 500;
        color: var(--text-secondary);
        padding: 0.35rem 0.6rem;
        background: var(--background);
        border-radius: 6px;
    }

    .stat-trend i {
        font-size: 0.65rem;
    }

    .stat-trend-warning {
        background: rgba(251, 191, 36, 0.1);
        color: #f59e0b;
    }

    .stat-trend-info {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    .stat-trend-success {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    .stat-trend-cyan {
        background: rgba(6, 182, 212, 0.1);
        color: #0891b2;
    }

    .stat-trend-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    /* Management Tools Section */
    .management-tools-section {
        margin-bottom: 2.5rem;
    }

    .management-tools-section .card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #e2e8f0;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .management-tools-section .card-header {
        background: linear-gradient(135deg, #8bc34a 0%, #689f38 100%);
        margin: -1.5rem -1.5rem 2rem -1.5rem;
        padding: 1.5rem 2rem;
        border-radius: 12px 12px 0 0;
        border-bottom: none;
    }

    .management-tools-section .card-header h2 {
        color: white;
        font-size: 1.5rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }

    .management-tools-section .card-header i {
        color: white;
        font-size: 1.75rem;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        padding: 1rem;
    }

    /* Modern Professional Button Design */
    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 2rem 1.5rem;
        color: white;
        text-decoration: none;
        border-radius: 16px;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem;
        position: relative;
        overflow: hidden;
        text-align: center;
        cursor: pointer;
        
        box-shadow: 
            0 2px 0 rgba(0, 0, 0, 0.15),
            0 6px 20px rgba(0, 0, 0, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.25);
        
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(180deg, 
                                    rgba(255, 255, 255, 0.15) 0%, 
                                    transparent 50%, 
                                    rgba(0, 0, 0, 0.1) 100%);
        pointer-events: none;
        border-radius: 16px;
    }

    .action-btn:hover {
        transform: translateY(-4px);
        box-shadow: 
            0 4px 0 rgba(0, 0, 0, 0.15),
            0 10px 30px rgba(0, 0, 0, 0.25),
            inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }

    .action-btn:active {
        transform: translateY(-1px);
        box-shadow: 
            0 1px 0 rgba(0, 0, 0, 0.15),
            0 4px 12px rgba(0, 0, 0, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }

    .action-btn .icon-logo {
        font-size: 3rem;
        filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.25));
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.15);
        padding: 1rem;
        border-radius: 50%;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        
        box-shadow: 
            0 2px 8px rgba(0, 0, 0, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.3);
        
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .action-btn:hover .icon-logo {
        transform: scale(1.08);
        filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.3));
        background: rgba(255, 255, 255, 0.2);
    }

    /* Custom icons */
    .icon-settings {
        position: relative;
    }

    .icon-settings::before {
        content: '';
        position: absolute;
        width: 60%;
        height: 60%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: currentColor;
        clip-path: polygon(
            50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%
        );
    }

    .icon-reports {
        position: relative;
    }

    .icon-reports::before {
        content: '';
        position: absolute;
        width: 60%;
        height: 60%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: 
            linear-gradient(to top, currentColor 30%, transparent 30%) 10% 100%/15% 100% no-repeat,
            linear-gradient(to top, currentColor 60%, transparent 60%) 35% 100%/15% 100% no-repeat,
            linear-gradient(to top, currentColor 45%, transparent 45%) 60% 100%/15% 100% no-repeat,
            linear-gradient(to top, currentColor 75%, transparent 75%) 85% 100%/15% 100% no-repeat;
    }

    .icon-activity {
        position: relative;
    }

    .icon-activity::before {
        content: '';
        position: absolute;
        width: 50%;
        height: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 4px solid currentColor;
        border-radius: 50%;
    }

    .icon-activity::after {
        content: '';
        position: absolute;
        width: 3px;
        height: 20%;
        background: currentColor;
        top: 35%;
        left: 50%;
        transform-origin: bottom center;
        transform: translateX(-50%) rotate(45deg);
    }

    .icon-email {
        position: relative;
    }

    .icon-email::before {
        content: '';
        position: absolute;
        width: 55%;
        height: 40%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 4px solid currentColor;
        border-radius: 4px;
    }

    .icon-email::after {
        content: '';
        position: absolute;
        width: 55%;
        height: 0;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border-left: calc(55% * 0.5) solid transparent;
        border-right: calc(55% * 0.5) solid transparent;
        border-top: 20px solid currentColor;
    }

    .action-btn span {
        font-size: 1rem;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        text-transform: uppercase;
        font-weight: 700;
        line-height: 1.3;
    }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .card {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border);
    }

    .card-header h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-header i {
        color: var(--primary);
    }

    .view-all-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: gap 0.3s ease;
    }

    .view-all-link:hover {
        gap: 0.75rem;
    }

    /* Table */
    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table thead th {
        background: var(--background);
        padding: 0.75rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modern-table tbody tr {
        transition: background 0.2s ease;
        cursor: pointer;
        border-bottom: 1px solid var(--border);
    }

    .modern-table tbody tr:hover {
        background: var(--background);
    }

    .modern-table tbody td {
        padding: 0.75rem;
        font-size: 0.825rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-processing { background: #dbeafe; color: #1e40af; }
    .status-approved { background: #d1fae5; color: #065f46; }
    .status-paid { background: #c8e6c9; color: #1b5e20; }

    /* Activity */
    .activity-list {
        list-style: none;
    }

    .activity-item {
        position: relative;
        padding-left: 2.5rem;
        padding-bottom: 1.5rem;
    }

    .activity-item::before {
        content: '';
        position: absolute;
        left: 0.625rem;
        top: 2rem;
        width: 2px;
        height: calc(100% - 2rem);
        background: var(--border);
    }

    .activity-item:last-child::before {
        display: none;
    }

    .activity-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-dark);
        font-size: 0.875rem;
    }

    .activity-content {
        background: var(--background);
        padding: 1rem;
        border-radius: var(--radius);
    }

    .activity-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
    }

    .activity-time {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    /* Chart */
    .chart-wrapper {
        position: relative;
        height: 300px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1200px) {
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .action-btn {
            padding: 2rem 1.5rem;
        }
        
        .action-btn .icon-logo {
            font-size: 2.5rem;
            width: 70px;
            height: 70px;
            padding: 0.875rem;
        }
        
        .action-btn span {
            font-size: 0.9rem;
        }
    }
</style>

<nav class="top-navbar">
    <a href="dashboard.php" class="nav-brand">
        <img src="../assets/carmona-logo.png" alt="Logo" onerror="this.style.display='none'"> 
        <span>ADMIN PORTAL</span>
    </a>

    <button class="burger-menu" id="burgerMenu" onclick="toggleMobileNav()">
        <span></span>
        <span></span>
        <span></span>
    </button>
</nav>

<div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="closeMobileNav()"></div>
<div class="mobile-nav-menu" id="mobileNavMenu">
    <div class="mobile-nav-header">
        <h3>Admin Menu</h3>
        <button onclick="closeMobileNav()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--text-secondary)">&times;</button>
    </div>

    <div class="nav-links">
        <div class="nav-section-title">Main</div>
        <a href="dashboard.php" class="nav-link active">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="applications.php" class="nav-link">
            <i class="fas fa-clipboard-list"></i> Applications
        </a>
        <a href="users.php" class="nav-link">
            <i class="fas fa-users"></i> Users
        </a>

        <div class="nav-section-title">Management</div>
        <a href="manage_departments.php" class="nav-link">
            <i class="fas fa-building"></i> Depts & Services
        </a>
        <a href="reports.php" class="nav-link">
            <i class="fas fa-chart-line"></i> Reports
        </a>
        <a href="activity_logs.php" class="nav-link">
            <i class="fas fa-history"></i> Activity Logs
        </a>
        <a href="check_email_and_sms_logs.php" class="nav-link">
            <i class="fas fa-envelope"></i> Email/SMS Logs
        </a>

        <div class="nav-section-title">Account</div>
        <a href="../auth/logout.php" class="nav-link" style="color: #ef4444;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="dashboard-container">
    <div class="dashboard-header animate__animated animate__fadeInDown">
        <h1>Welcome Back, Admin!</h1>
        <p>Here's what's happening with your permit tracking system today</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;" onclick="window.location.href='applications.php'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Total</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['total']); ?></div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                    <span>All time</span>
                </div>
            </div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.15s;" onclick="window.location.href='applications.php?status=pending'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Pending</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['pending']); ?></div>
                <div class="stat-trend stat-trend-warning">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Review</span>
                </div>
            </div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;" onclick="window.location.href='applications.php?status=processing'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-info">
                    <i class="fas fa-sync-alt"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Processing</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['processing']); ?></div>
                <div class="stat-trend stat-trend-info">
                    <i class="fas fa-spinner"></i>
                    <span>Active</span>
                </div>
            </div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.25s;" onclick="window.location.href='applications.php?status=approved'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Approved</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['approved']); ?></div>
                <div class="stat-trend stat-trend-success">
                    <i class="fas fa-check"></i>
                    <span>Ready</span>
                </div>
            </div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;" onclick="window.location.href='applications.php?status=completed'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-cyan">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Completed</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['completed']); ?></div>
                <div class="stat-trend stat-trend-cyan">
                    <i class="fas fa-flag-checkered"></i>
                    <span>Done</span>
                </div>
            </div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.35s;" onclick="window.location.href='applications.php?status=rejected'">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Rejected</h3>
                <div class="stat-value"><?php echo number_format($overall_stats['rejected']); ?></div>
                <div class="stat-trend stat-trend-danger">
                    <i class="fas fa-ban"></i>
                    <span>Denied</span>
                </div>
            </div>
        </div>
    </div>

    <div class="management-tools-section">
        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas fa-cogs"></i>
                    Management Tools
                </h2>
            </div>
            <div class="quick-actions">
                <a href="manage_departments.php" class="action-btn" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="icon-logo icon-settings"></div>
                    <span>Departments & Services</span>
                </a>
                <a href="reports.php" class="action-btn" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                    <div class="icon-logo icon-reports"></div>
                    <span>View Reports</span>
                </a>
                <a href="activity_logs.php" class="action-btn" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                    <div class="icon-logo icon-activity"></div>
                    <span>Activity Logs</span>
                </a>
                <a href="check_email_and_sms_logs.php" class="action-btn" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                    <div class="icon-logo icon-email"></div>
                    <span>Email/SMS Logs</span>
                </a>
            </div>
        </div>
    </div>

    <div class="content-grid">
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

        <div class="card">
            <div class="card-header">
                <h2>
                    <i class="fas fa-chart-pie"></i>
                    Status Overview
                </h2>
            </div>
            <div class="chart-wrapper">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>
                <i class="fas fa-clock"></i>
                Recent Activity
            </h2>
            <a href="activity_logs.php" class="view-all-link">
                View All
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <?php if (empty($recent_activity)): ?>
            <div class="empty-state">
                <i class="fas fa-clock"></i>
                <h3>No Recent Activity</h3>
                <p>Activity logs will appear here</p>
            </div>
        <?php else: ?>
            <ul class="activity-list">
                <?php foreach (array_slice($recent_activity, 0, 5) as $activity): ?>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">
                            <?php echo htmlspecialchars($activity['description']); ?>
                        </div>
                        <div class="activity-time">
                            <?php 
                            $time = strtotime($activity['created_at']);
                            $diff = time() - $time;
                            if ($diff < 60) {
                                echo 'Just now';
                            } elseif ($diff < 3600) {
                                echo floor($diff / 60) . ' minutes ago';
                            } elseif ($diff < 86400) {
                                echo floor($diff / 3600) . ' hours ago';
                            } else {
                                echo date('M d, Y \a\t h:i A', $time);
                            }
                            ?>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
<script>
    // MOBILE NAVIGATION LOGIC (Added)
    function toggleMobileNav() {
        const menu = document.getElementById('mobileNavMenu');
        const overlay = document.getElementById('mobileNavOverlay');
        const burger = document.getElementById('burgerMenu');
        
        menu.classList.toggle('show');
        overlay.classList.toggle('show');
        burger.classList.toggle('active');
        
        // Prevent body scroll when menu is open
        document.body.style.overflow = menu.classList.contains('show') ? 'hidden' : '';
    }

    function closeMobileNav() {
        const menu = document.getElementById('mobileNavMenu');
        const overlay = document.getElementById('mobileNavOverlay');
        const burger = document.getElementById('burgerMenu');
        
        menu.classList.remove('show');
        overlay.classList.remove('show');
        burger.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Status Chart
    const ctx = document.getElementById('statusChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($status_breakdown)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($status_breakdown)); ?>,
                    backgroundColor: [
                        '#fbbf24',
                        '#3b82f6',
                        '#10b981',
                        '#ef4444',
                        '#6366f1'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '600',
                                family: 'Inter'
                            },
                            usePointStyle: true
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
</script>

<?php include '../includes/footer.php'; ?>