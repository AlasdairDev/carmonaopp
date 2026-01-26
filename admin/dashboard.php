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
include '../includes/header.php';
?>

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
        font-size: 2rem;           /* Changed from 1.75rem */
        font-weight: 800;
        margin-bottom: 0.25rem;
        position: relative;
        z-index: 1;
    }

    .dashboard-header p {
        font-size: 1rem;           /* Changed from 0.95rem */
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

<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header animate__animated animate__fadeInDown">
        <h1>Welcome Back, Admin!</h1>
        <p>Here's what's happening with your permit tracking system today</p>
    </div>

    <!-- Statistics Grid -->
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

    <!-- Management Tools - Full Width Section -->
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
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
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