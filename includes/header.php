<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser();
$unreadCount = 0;

// Get unread notification count if user is logged in
if ($currentUser) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$currentUser['id']]);
        $unreadCount = $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error fetching notification count: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/favicon.png">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <?php if (isset($additionalCSS)): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL . '/assets/css/' . $additionalCSS; ?>">
    <?php endif; ?>
    
    <!-- Notification Bell & Responsive Styles -->
    <style>
        .notification-bell {
            position: relative;
            display: inline-block;
            margin: 0 1rem;
            cursor: pointer;
        }

        .notification-bell-icon {
            width: 28px;
            height: 28px;
            stroke: #6b7280;
            transition: stroke 0.3s;
        }

        .notification-bell:hover .notification-bell-icon {
            stroke: #7cb342;
        }

        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 350px;
            max-height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
            margin-top: 10px;
            overflow: hidden;
        }

        .notification-dropdown.show {
            display: block;
        }

        .notification-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 700;
            color: #333;
        }

        .notification-list {
            max-height: 300px;
            overflow-y: auto;
            flex: 1;
        }

        .notification-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f8f8f8;
        }

        .notification-item.unread {
            background: #e8f5e9;
        }

        .notification-empty {
            padding: 2rem;
            text-align: center;
            color: #999;
        }

        /* Mobile Responsive Styles */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            z-index: 1002;
        }

        .mobile-menu-toggle svg {
            width: 28px;
            height: 28px;
            stroke: #6b7280;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .mobile-overlay.active {
            opacity: 1;
        }

        @media (max-width: 1024px) {
            .navbar {
                padding: 1rem 1.5rem !important;
            }

            .navbar-brand .brand-text {
                font-size: 1.1rem !important;
            }

            .navbar-brand img {
                width: 40px !important;
                height: 40px !important;
            }

            .nav-links-center {
                gap: 2rem !important;
            }

            .nav-link-icon {
                font-size: 0.85rem !important;
            }

            .nav-link-icon svg {
                width: 24px !important;
                height: 24px !important;
            }
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .mobile-overlay.active {
                display: block;
            }

            .navbar {
                padding: 16px 0;
            }

            .navbar-brand {
                gap: 0.5rem !important;
            }

            .navbar-brand .brand-text {
                font-size: 0.95rem !important;
            }

            .navbar-brand img {
                width: 35px !important;
                height: 35px !important;
            }

            .nav-links-center {
                position: fixed;
                top: 0;
                right: -100%;
                width: 280px;
                height: 100vh;
                background: white;
                flex-direction: column;
                align-items: flex-start !important;
                padding: 5rem 1.5rem 2rem;
                gap: 0 !important;
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
                transition: right 0.3s ease;
                z-index: 1001;
                overflow-y: auto;
            }

            .nav-links-center.active {
                right: 0;
            }

            .nav-links-center a {
                width: 100%;
                padding: 1rem !important;
                flex-direction: row !important;
                justify-content: flex-start !important;
                gap: 1rem !important;
                border-bottom: 1px solid #f0f0f0;
            }

            .nav-links-center a svg {
                width: 24px !important;
                height: 24px !important;
            }

            .nav-links-center a span {
                font-size: 1rem !important;
            }

            .notification-bell {
                margin: 0 0.5rem;
            }

            .notification-bell-icon {
                width: 24px;
                height: 24px;
            }

            .notification-dropdown {
                width: 300px;
                right: -20px;
            }

            .nav-dropdown-toggle {
                padding: 0.5rem 1rem !important;
                font-size: 0.9rem !important;
            }

            .nav-dropdown-toggle svg {
                width: 20px !important;
                height: 20px !important;
            }

            .nav-dropdown-toggle span {
                max-width: 100px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .nav-right-section {
                gap: 0.5rem !important;
            }
        }

        @media (max-width: 480px) {
            .navbar-brand .brand-text {
                font-size: 0.85rem !important;
            }

            .navbar-brand img {
                width: 30px !important;
                height: 30px !important;
            }

            .notification-dropdown {
                width: calc(100vw - 40px);
                max-width: 300px;
            }

            .nav-dropdown-toggle span {
                max-width: 80px;
            }

            .notification-bell {
                margin: 0 0.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>

    <!-- Navigation Bar -->
    <nav class="navbar" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; background: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 1000;">
        <!-- Left: Logo -->
        <div class="navbar-brand" style="display: flex; align-items: center; gap: 0.75rem;">
            <?php if (isLoggedIn() && isAdmin()): ?>
                <!-- Admin: Non-clickable logo -->
                <img src="<?php echo BASE_URL; ?>/assets/carmona-logo.png" alt="Logo" style="width: 45px; height: 45px; object-fit: contain; flex-shrink: 0;" onerror="this.style.display='none'">
                <span class="brand-text" style="font-size: 1.25rem; font-weight: 700; color: #7fb539;">Carmona Online Permit Portal</span>
            <?php else: ?>
                <!-- User/Guest: Clickable logo -->
                <?php $logoLink = isLoggedIn() ? BASE_URL . '/user/dashboard.php' : BASE_URL . '/index.php'; ?>
                <a href="<?php echo $logoLink; ?>" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; pointer-events: none;">
                    <img src="<?php echo BASE_URL; ?>/assets/carmona-logo.png" alt="Logo" style="width: 45px; height: 45px; object-fit: contain; flex-shrink: 0; pointer-events: auto; cursor: pointer;" onerror="this.style.display='none'">
                    <span class="brand-text" style="font-size: 1.25rem; font-weight: 700; color: #7fb539; pointer-events: none; cursor: default;">Carmona Online Permit Portal</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Center: Navigation Links with Icons -->
        <div class="nav-links-center" id="navLinksCenter" style="display: flex; align-items: center; gap: 3.5rem;">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <!-- Admin Navigation -->
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; transition: all 0.3s ease;" class="nav-link-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px; stroke: #6b7280;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/applications.php" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; transition: all 0.3s ease;" class="nav-link-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px; stroke: #6b7280;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Applications</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/verify_payments.php" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; transition: all 0.3s ease;" class="nav-link-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px; stroke: #6b7280;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span>Verify Payments</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/users.php" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; transition: all 0.3s ease;" class="nav-link-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px; stroke: #6b7280;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Users</span>
                    </a>
                <?php else: ?>
                    <!-- User Navigation -->
                    <a href="<?php echo BASE_URL; ?>/user/dashboard.php" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; transition: all 0.3s ease;" class="nav-link-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px; stroke: #6b7280;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/apply.php" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; transition: all 0.3s ease;" class="nav-link-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px; stroke: #6b7280;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>New Application</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/applications.php" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; transition: all 0.3s ease;" class="nav-link-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px; stroke: #6b7280;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Applications</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/track.php" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; transition: all 0.3s ease;" class="nav-link-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px; stroke: #6b7280;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Track</span>
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <!-- Guest Navigation -->
                <a href="<?php echo BASE_URL; ?>/index.php" style="text-decoration: none; color: #333; font-weight: 500; font-size: 1rem; transition: color 0.3s ease;" class="nav-link-simple">Home</a>
                <a href="#" style="text-decoration: none; color: #333; font-weight: 500; font-size: 1rem; transition: color 0.3s ease;" class="nav-link-simple">How It Works</a>
                <a href="#" style="text-decoration: none; color: #333; font-weight: 500; font-size: 1rem; transition: color 0.3s ease;" class="nav-link-simple">About Us</a>
                <a href="#" style="text-decoration: none; color: #333; font-weight: 500; font-size: 1rem; transition: color 0.3s ease;" class="nav-link-simple">Contact</a>
            <?php endif; ?>
        </div>

        <!-- Right: Mobile Toggle + Notification Bell + Login or User Dropdown -->
        <div class="nav-right-section" style="display: flex; align-items: center; gap: 1rem;">
            <?php if (isLoggedIn()): ?>
                <!-- Notification Bell -->
                <div class="notification-bell" onclick="toggleNotifications()">
                    <svg class="notification-bell-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notification-badge" id="notificationBadge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                    
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">Notifications</div>
                        <div class="notification-list" id="notificationList">
                            <div class="notification-empty">Loading...</div>
                        </div>
                        <div style="padding: 1rem; border-top: 1px solid #e0e0e0; text-align: center;">
                            <a href="<?php echo BASE_URL; ?><?php echo isAdmin() ? '/admin/notifications.php' : '/user/notifications.php'; ?>"
                               style="display: block; padding: 0.75rem; background: #7cb342; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                                View All Notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <div class="nav-dropdown" style="position: relative;">
                    <button class="nav-dropdown-toggle" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; border-radius: 50px; background: linear-gradient(135deg, #9ACD32, #8BC34A); color: white; border: none; cursor: pointer; box-shadow: 0 8px 32px rgba(154, 205, 50, 0.3); font-weight: 700; font-size: 1rem; transition: all 0.3s ease;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px; stroke: white;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span><?php echo htmlspecialchars($currentUser['name']); ?></span>
                    </button>
                    <div class="nav-dropdown-menu" style="position: absolute; top: calc(100% + 0.75rem); right: 0; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); padding: 0.5rem; min-width: 200px; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.3s ease; border: 2px solid rgba(154, 205, 50, 0.2); z-index: 1001;">
                        <a href="<?php echo BASE_URL; ?><?php echo isAdmin() ? '/admin/profile.php' : '/user/profile.php'; ?>" class="dropdown-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: #546E7A; text-decoration: none; border-radius: 8px; transition: all 0.3s ease; font-size: 0.9rem; font-weight: 600;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="dropdown-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: #546E7A; text-decoration: none; border-radius: 8px; transition: all 0.3s ease; font-size: 0.9rem; font-weight: 600;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>

                <!-- Mobile Menu Toggle (for logged in users) -->
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn" style="padding: 0.65rem 2rem; border-radius: 50px; text-decoration: none; color: #7fb539; font-weight: 600; border: 2px solid #7fb539; transition: all 0.3s ease; background: transparent;">Login</a>
                
                <!-- Mobile Menu Toggle (for guests) -->
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    </nav>

    <script>
    let notificationDropdownOpen = false;

    // Mobile menu functions
    function toggleMobileMenu() {
        const navLinks = document.getElementById('navLinksCenter');
        const overlay = document.getElementById('mobileOverlay');
        navLinks.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    function closeMobileMenu() {
        const navLinks = document.getElementById('navLinksCenter');
        const overlay = document.getElementById('mobileOverlay');
        navLinks.classList.remove('active');
        overlay.classList.remove('active');
    }

    // Close mobile menu when clicking a link
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nav-links-center a').forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });
    });

    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        notificationDropdownOpen = !notificationDropdownOpen;
        
        if (notificationDropdownOpen) {
            dropdown.classList.add('show');
            loadNotifications();
        } else {
            dropdown.classList.remove('show');
        }
    }

    async function loadNotifications() {
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/api/get_notifications.php');
            const data = await response.json();
            
            if (data.success) {
                const listEl = document.getElementById('notificationList');
                
                if (data.notifications.length === 0) {
                    listEl.innerHTML = '<div class="notification-empty">No notifications</div>';
                    return;
                }
                
                listEl.innerHTML = data.notifications.map(n => {
                    const icons = { 'success': '✓', 'info': 'ℹ', 'warning': '⚠', 'danger': '✕' };
                    return `
                        <div class="notification-item ${n.is_read == 0 ? 'unread' : ''}" 
                            onclick="markAsRead(${n.id}, ${n.application_id || 'null'})"
                            style="display: flex; gap: 1rem; align-items: start;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.2rem; background: ${n.type === 'success' ? '#e8f5e9' : n.type === 'warning' ? '#fff3e0' : n.type === 'danger' ? '#ffebee' : '#e3f2fd'}; color: ${n.type === 'success' ? '#2e7d32' : n.type === 'warning' ? '#ef6c00' : n.type === 'danger' ? '#c62828' : '#1976d2'};">
                                ${icons[n.type] || 'ℹ'}
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">${escapeHtml(n.title)}</div>
                                <div style="font-size: 0.85rem; color: #666; margin-bottom: 0.25rem;">${escapeHtml(n.message)}</div>
                                <div style="font-size: 0.75rem; color: #999;">${timeAgo(n.created_at)}</div>
                            </div>
                        </div>
                    `;
                }).join('');
                
                updateNotificationBadge(data.unread_count);
            }
        } catch (error) {
            console.error('Failed to load notifications:', error);
            document.getElementById('notificationList').innerHTML = '<div class="notification-empty">Failed to load notifications</div>';
        }
    }

    async function markAsRead(notificationId, applicationId) {
        try {
            await fetch('<?php echo BASE_URL; ?>/api/mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: notificationId })
            });
            
            if (applicationId) {
                <?php if (isAdmin()): ?>
                    window.location.href = '<?php echo BASE_URL; ?>/admin/view_application.php?id=' + applicationId;
                <?php else: ?>
                    window.location.href = '<?php echo BASE_URL; ?>/user/view_application.php?id=' + applicationId;
                <?php endif; ?>
            } else {
                loadNotifications();
            }
        } catch (error) {
            console.error('Failed to mark as read:', error);
        }
    }

    function updateNotificationBadge(count) {
        const badge = document.getElementById('notificationBadge');
        const bellIcon = document.querySelector('.notification-bell-icon');
        
        if (count > 0) {
            if (badge) {
                badge.textContent = count;
            } else {
                bellIcon.insertAdjacentHTML('afterend', `<span class="notification-badge" id="notificationBadge">${count}</span>`);
            }
        } else {
            if (badge) badge.remove();
        }
    }

    function timeAgo(dateString) {
        const seconds = Math.floor((new Date() - new Date(dateString)) / 1000);
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
        return Math.floor(seconds / 86400) + ' days ago';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close notification dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notification-bell')) {
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
                notificationDropdownOpen = false;
            }
        }
    });

    // Poll for new notifications every 30 seconds
    setInterval(() => {
        if (!notificationDropdownOpen) {
            fetch('<?php echo BASE_URL; ?>/api/get_notifications.php?limit=1')
                .then(r => r.json())
                .then(data => { if (data.success) updateNotificationBadge(data.unread_count); })
                .catch(err => console.error('Failed to poll notifications:', err));
        }
    }, 30000);

    // Dropdown hover effects
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
            dropdown.addEventListener('mouseenter', function() {
                const menu = this.querySelector('.nav-dropdown-menu');
                if (menu) {
                    menu.style.opacity = '1';
                    menu.style.visibility = 'visible';
                    menu.style.transform = 'translateY(0)';
                }
            });
            dropdown.addEventListener('mouseleave', function() {
                const menu = this.querySelector('.nav-dropdown-menu');
                if (menu) {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(-10px)';
                }
            });
        });

        // Nav link hover effects
        document.querySelectorAll('.nav-link-simple').forEach(link => {
            link.addEventListener('mouseenter', function() { this.style.color = '#7fb539'; });
            link.addEventListener('mouseleave', function() { this.style.color = '#333'; });
        });

        document.querySelectorAll('.nav-link-icon').forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.color = '#7fb539';
                const svg = this.querySelector('svg');
                if (svg) svg.style.stroke = '#7fb539';
            });
            link.addEventListener('mouseleave', function() {
                this.style.color = '#6b7280';
                const svg = this.querySelector('svg');
                if (svg) svg.style.stroke = '#6b7280';
            });
        });

        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.background = 'rgba(154, 205, 50, 0.15)';
                this.style.color = '#7CB342';
                this.style.transform = 'translateX(3px)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.background = 'transparent';
                this.style.color = '#546E7A';
                this.style.transform = 'translateX(0)';
            });
        });

        document.querySelectorAll('.nav-dropdown-toggle').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 12px 40px rgba(154, 205, 50, 0.5)';
            });
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 8px 32px rgba(154, 205, 50, 0.3)';
            });
        });
    });
    </script>

<!-- Main Content -->
    <main class="main-content">
        <?php 
        if (isset($_SESSION['logout_success'])) {
            $current_page = basename($_SERVER['PHP_SELF']);
            if ($current_page === 'login.php') {
                echo '<div class="alert alert-success">You have been logged out successfully.</div>';
            }
            unset($_SESSION['logout_success']);
        }
        ?>