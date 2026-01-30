<?php
require_once '../config.php';
require_once '../includes/functions.php';


// Check if user is logged in
if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit();
}


$user_id = $_SESSION['user_id'];

// Handle AJAX requests
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    header('Content-Type: application/json');

    // Mark notification as read
    if (isset($_POST['mark_read']) && isset($_POST['notif_id'])) {
        $notif_id = (int) $_POST['notif_id'];

        try {
            $check = $pdo->prepare("SELECT id, is_read, user_id FROM notifications WHERE id = ?");
            $check->execute([$notif_id]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);

            if (!$existing) {
                echo json_encode(['success' => false, 'message' => 'Notification not found']);
            } elseif ($existing['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
            } else {
                if ($existing['is_read'] == 1) {
                    echo json_encode(['success' => true, 'message' => 'Notification already marked as read']);
                } else {
                    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                    $result = $stmt->execute([$notif_id, $user_id]);

                    if ($result) {
                        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
                    }
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
        exit();
    }

    // Mark all as read
    if (isset($_POST['mark_all_read'])) {
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
            $result = $stmt->execute([$user_id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to mark all notifications as read']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // Delete all notifications
    if (isset($_POST['delete_all'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
            $result = $stmt->execute([$user_id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'All notifications deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete all notifications']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // Delete notification
    if (isset($_POST['delete_notif']) && isset($_POST['notif_id'])) {
        $notif_id = (int) $_POST['notif_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$notif_id, $user_id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Notification deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // Fetch notifications (for AJAX updates)
    if (isset($_GET['fetch_notifications'])) {
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

        $where = "WHERE user_id = ?";
        $params = [$user_id];

        if ($filter === 'unread') {
            $where .= " AND is_read = 0";
        } elseif ($filter === 'read') {
            $where .= " AND is_read = 1";
        }

        $query = "SELECT * FROM notifications $where ORDER BY created_at DESC LIMIT 50";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count_query = "SELECT
            COUNT(*) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
            FROM notifications WHERE user_id = ?";
        $stmt = $pdo->prepare($count_query);
        $stmt->execute([$user_id]);
        $counts = $stmt->fetch(PDO::FETCH_ASSOC);
        $counts['unread'] = $counts['unread'] ?? 0;

        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'counts' => $counts
        ]);
        exit();
    }
}


// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';


// Build query
$where = "WHERE user_id = ?";
$params = [$user_id];


if ($filter === 'unread') {
    $where .= " AND is_read = 0";
} elseif ($filter === 'read') {
    $where .= " AND is_read = 1";
}


// Get notifications
$query = "SELECT * FROM notifications $where ORDER BY created_at DESC LIMIT 50";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get counts
$count_query = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
    FROM notifications WHERE user_id = ?";
$stmt = $pdo->prepare($count_query);
$stmt->execute([$user_id]);
$counts = $stmt->fetch(PDO::FETCH_ASSOC);


// Handle null unread count
$counts['unread'] = $counts['unread'] ?? 0;


$pageTitle = 'Notifications';
include '../includes/header.php';
?>



<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-responsive.css">

<style>
    :root {
        --primary: #7cb342;
        --primary-dark: #689f38;
        --text-dark: #2d3748;
        --text-light: #718096;
        --bg-light: #f8faf8;
    }


    body {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        min-height: 100vh;
        box-sizing: border-box;
    }


    .wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
        min-height: calc(100vh - 40px);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        padding: 4rem 2rem;
    }


    .page-wrapper {
        position: relative;
        z-index: 2;
        padding: 0;
    }


    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }


    .dashboard-banner {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        border-radius: 30px;
        padding: 3rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
        margin: 0 0 2rem 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
        height: 195px;
    }

    .dashboard-banner h1 {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
    }

    .dashboard-banner p {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1.1rem;
        margin: 0.5rem 0 0 0;
    }

    .banner-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        border: none;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        width: 200px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        background: white;
        color: var(--primary);
    }

    .banner-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .banner-btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
    }

    .banner-btn-danger:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4) !important;
    }


    .stats-bar {
        background: white;
        padding: 2rem 2.5rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 2px solid #f0f4f8;
    }

    .stats-info {
        display: flex;
        gap: 2rem;
        align-items: center;
    }


    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }


    .stat-label {
        font-size: 1rem;
        color: var(--text-light);
        font-weight: 600;
    }


    .stat-value {
        background: linear-gradient(135deg, var(--primary) 0%, #9ccc65 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.5rem;
        min-width: 60px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(124, 179, 66, 0.3);
    }


    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        background: #f8faf8;
        padding: 0.5rem;
        border-radius: 15px;
    }


    .filter-tab {
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        text-decoration: none;
        color: var(--text-light);
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }


    .filter-tab:hover {
        background: rgba(124, 179, 66, 0.1);
        color: var(--primary);
    }


    .filter-tab.active {
        background: var(--primary);
        color: white;
        box-shadow: 0 2px 8px rgba(124, 179, 66, 0.3);
    }


    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }


    .btn-primary {
        background: var(--primary);
        color: white;
    }


    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }


    .notifications-container {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }


    .notification-item {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        display: flex;
        gap: 1.5rem;
        align-items: start;
        align-items: center;
    }


    .notification-item:last-child {
        border-bottom: none;
    }


    .notification-item.unread {
        background: #f1f8e9;
        border-left: 4px solid var(--primary);
    }


    .notification-item:hover {
        background: #fafafa;
    }


    .notif-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }


    .notif-icon.success {
        background: #e8f5e9;
        color: #2e7d32;
    }


    .notif-icon.info {
        background: #e3f2fd;
        color: #1976d2;
    }


    .notif-icon.warning {
        background: #fff3e0;
        color: #ef6c00;
    }


    .notif-icon.danger {
        background: #ffebee;
        color: #c62828;
    }


    .notif-content {
        flex: 1;
    }


    .notif-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 0.5rem 0;
    }


    .notif-message {
        color: var(--text-light);
        line-height: 1.6;
        margin: 0 0 0.75rem 0;
    }


    .notif-meta {
        display: flex;
        gap: 1rem;
        align-items: center;
        font-size: 0.85rem;
        color: var(--text-light);
    }


    .notif-actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
        align-self: center;
    }

    .notif-btn {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: #3b82f6;
        color: white;
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .notif-btn:hover {
        background: #2563eb;
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .notif-btn.delete-btn {
        background: #ef4444;
        color: white;
    }

    .notif-btn.delete-btn:hover {
        background: #dc2626;
    }


    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-light);
    }


    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }


    .empty-state h3 {
        font-size: 1.5rem;
        color: var(--text-dark);
        margin: 0 0 0.5rem 0;
    }


    .empty-state p {
        font-size: 1rem;
        margin: 0;
    }


    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }


    .alert-success {
        background: #e8f5e9;
        color: #2e7d32;
        border-left: 4px solid #2e7d32;
    }


    .alert-error {
        background: #ffebee;
        color: #c62828;
        border-left: 4px solid #c62828;
    }

    /* Toast Notification */
    .toast-notification {
        position: fixed;
        top: 100px;
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

    .toast-success {
        border-left: 4px solid #22c55e;
    }

    .toast-error {
        border-left: 4px solid #ef4444;
    }

    .toast-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toast-message {
        flex: 1;
        font-weight: 500;
        font-size: 0.9375rem;
        color: var(--text-dark);
    }


    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(4px);
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-container {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        max-width: 450px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            transform: scale(0.9);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .modal-icon {
        width: 80px;
        height: 80px;
        background: #fee2e2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
    }

    .modal-icon i {
        font-size: 2.5rem;
        color: #ef4444;
    }

    .modal-container h2 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: var(--text-dark);
    }

    .modal-container p {
        font-size: 1rem;
        color: var(--text-light);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }

    .btn-cancel,
    .btn-delete {
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-cancel {
        background: #e2e8f0;
        color: var(--text-dark);
    }

    .btn-cancel:hover {
        background: #cbd5e1;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
</style>
<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">
            <div class="dashboard-banner">
                <div style="flex: 1;">
                    <h1>Notifications</h1>
                    <p>Stay updated with your application status</p>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem; align-self: center;">
                    <button onclick="markAllAsRead()" class="banner-btn" id="mark-all-btn"
                        style="display: <?php echo $counts['unread'] > 0 ? 'inline-flex' : 'none'; ?>;">
                        <i class="fas fa-check-double"></i> Mark All Read
                    </button>

                    <button onclick="deleteAllNotifications()" class="banner-btn banner-btn-danger" id="delete-all-btn"
                        style="display: <?php echo $counts['total'] > 0 ? 'inline-flex' : 'none'; ?>;">
                        <i class="fas fa-trash-alt"></i> Delete All
                    </button>
                </div>
            </div>


            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stats-info">
                    <div class="stat-item">
                        <span class="stat-label">Total:</span>
                        <span class="stat-value" id="total-count"><?php echo $counts['total']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Unread:</span>
                        <span class="stat-value" id="unread-count"><?php echo $counts['unread']; ?></span>
                    </div>
                </div>

                <div class="filter-tabs">
                    <a href="#" onclick="filterNotifications('all'); return false;"
                        class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>" id="filter-all">
                        All
                    </a>
                    <a href="#" onclick="filterNotifications('unread'); return false;"
                        class="filter-tab <?php echo $filter === 'unread' ? 'active' : ''; ?>" id="filter-unread">
                        Unread
                    </a>
                    <a href="#" onclick="filterNotifications('read'); return false;"
                        class="filter-tab <?php echo $filter === 'read' ? 'active' : ''; ?>" id="filter-read">
                        Read
                    </a>
                </div>
            </div>


            <!-- Notifications List -->
            <div class="notifications-container" id="notifications-list">
                <?php if (count($notifications) > 0): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>"
                            onclick="handleNotificationClick(<?php echo $notif['id']; ?>, <?php echo $notif['application_id'] ? $notif['application_id'] : 'null'; ?>, <?php echo $notif['is_read']; ?>)"
                            style="cursor: pointer;">
                            <div class="notif-icon <?php echo htmlspecialchars($notif['type']); ?>">
                                <?php
                                $icons = [
                                    'success' => '<i class="fas fa-check"></i>',
                                    'info' => '<i class="fas fa-info"></i>',
                                    'warning' => '<i class="fas fa-exclamation-triangle"></i>',
                                    'danger' => '<i class="fas fa-times"></i>'
                                ];
                                echo $icons[$notif['type']] ?? '<i class="fas fa-bell"></i>';
                                ?>
                            </div>

                            <div class="notif-content">
                                <h3 class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></h3>
                                <p class="notif-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <div class="notif-meta">
                                    <span><i class="fas fa-calendar"></i>
                                        <?php echo date('M d, Y', strtotime($notif['created_at'])); ?></span>
                                    <span><i class="fas fa-clock"></i>
                                        <?php echo date('h:i A', strtotime($notif['created_at'])); ?></span>
                                </div>
                            </div>


                            <div class="notif-actions">
                                <?php if (!$notif['is_read']): ?>
                                    <button onclick="markAsRead(<?php echo $notif['id']; ?>); event.stopPropagation();"
                                        class="notif-btn" title="Mark as Read">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>
                                <button onclick="deleteNotification(<?php echo $notif['id']; ?>); event.stopPropagation();"
                                    class="notif-btn delete-btn" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
                        <h3>No Notifications</h3>
                        <p>You're all caught up! Check back later for updates.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Delete Single Modal -->
<div id="deleteSingleModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2>Delete Notification?</h2>
        <p>Are you sure you want to delete this notification? This action cannot be undone.</p>
        <div class="modal-actions">
            <button onclick="closeDeleteModal()" class="btn-cancel">Cancel</button>
            <button onclick="confirmDelete()" class="btn-delete">Delete</button>
        </div>
    </div>
</div>
<script>
    let currentFilter = '<?php echo $filter; ?>';

    // Show toast notification
    function showToast(message, type = 'success') {
        const existingToast = document.querySelector('.toast-notification');
        if (existingToast) {
            existingToast.remove();
        }

        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;

        const iconHtml = type === 'success'
            ? '<i class="fas fa-check-circle" style="color: #22c55e; font-size: 1.25rem;"></i>'
            : '<i class="fas fa-times-circle" style="color: #ef4444; font-size: 1.25rem;"></i>';

        toast.innerHTML = `
        <div class="toast-icon">${iconHtml}</div>
        <div class="toast-message">${message}</div>
    `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Reload notifications
    function reloadNotifications() {
        fetch(`notifications.php?ajax=true&fetch_notifications=1&filter=${currentFilter}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationsList(data.notifications);
                    updateCounts(data.counts);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Update notifications list
    function updateNotificationsList(notifications) {
        const container = document.getElementById('notifications-list');

        if (notifications.length === 0) {
            container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
                <h3>No Notifications</h3>
                <p>You're all caught up! Check back later for updates.</p>
            </div>
        `;
            return;
        }

        let html = '';
        notifications.forEach(notif => {
            const icons = {
                'success': '✓',
                'info': 'ℹ',
                'warning': '⚠',
                'danger': '✕'
            };

            const unreadClass = notif.is_read == 0 ? 'unread' : '';
            const icon = icons[notif.type] || 'ℹ';
            const appId = notif.application_id || 'null';

            html += `
            <div class="notification-item ${unreadClass}" 
            onclick="handleNotificationClick(${notif.id}, ${appId}, ${notif.is_read})" 
            style="cursor: pointer;">
                <div class="notif-icon ${notif.type}">
                    ${icon}
                </div>
               
                <div class="notif-content">
                    <h3 class="notif-title">${escapeHtml(notif.title)}</h3>
                    <p class="notif-message">${escapeHtml(notif.message)}</p>
                    <div class="notif-meta">
                        <span><i class="fas fa-calendar"></i> ${formatDate(notif.created_at)}</span>
                        <span><i class="fas fa-clock"></i> ${formatTime(notif.created_at)}</span>
                    </div>
                </div>

                <div class="notif-actions">
                    ${notif.is_read == 0 ? `
                        <button onclick="markAsRead(${notif.id}); event.stopPropagation();" class="notif-btn" title="Mark as Read">
                            <i class="fas fa-eye"></i>
                        </button>
                    ` : ''}
                    <button onclick="deleteNotification(${notif.id}); event.stopPropagation();" class="notif-btn delete-btn" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        });

        container.innerHTML = html;
    }

    // Update counts
    function updateCounts(counts) {
        document.getElementById('total-count').textContent = counts.total;
        document.getElementById('unread-count').textContent = counts.unread;

        // Show/hide buttons
        document.getElementById('mark-all-btn').style.display = counts.unread > 0 ? 'inline-flex' : 'none';
        document.getElementById('delete-all-btn').style.display = counts.total > 0 ? 'inline-flex' : 'none';
    }

    // Mark as read
    function markAsRead(notifId) {
        const formData = new FormData();
        formData.append('notif_id', notifId);
        formData.append('mark_read', '1');

        fetch('notifications.php?ajax=true', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    reloadNotifications();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to mark as read', 'error');
            });
    }

    // Mark all as read
    function markAllAsRead() {
        const formData = new FormData();
        formData.append('mark_all_read', '1');

        fetch('notifications.php?ajax=true', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    reloadNotifications();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to mark all as read', 'error');
            });
    }
    let deleteNotifId = null;
    // Delete notification
    function deleteNotification(notifId) {
        deleteNotifId = notifId;
        document.getElementById('deleteSingleModal').classList.add('active');
    }
    function closeDeleteModal() {
        document.getElementById('deleteSingleModal').classList.remove('active');
        deleteNotifId = null;
    }
    function confirmDelete() {
        if (!deleteNotifId) return;

        const formData = new FormData();
        formData.append('notif_id', deleteNotifId);
        formData.append('delete_notif', '1');

        fetch('notifications.php?ajax=true', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                closeDeleteModal();
                if (data.success) {
                    showToast(data.message, 'success');
                    reloadNotifications();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeDeleteModal();
                showToast('Failed to delete notification', 'error');
            });
    }
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeDeleteModal();
        }
    });
    // Delete all notifications
    function deleteAllNotifications() {
        if (!confirm('Are you sure you want to delete all notifications?')) return;

        const formData = new FormData();
        formData.append('delete_all', '1');

        fetch('notifications.php?ajax=true', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    reloadNotifications();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to delete all notifications', 'error');
            });
    }

    // Filter notifications
    function filterNotifications(filter) {
        currentFilter = filter;

        // Update active tab
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.getElementById('filter-' + filter).classList.add('active');

        reloadNotifications();
    }

    // Handle notification click
    function handleNotificationClick(notifId, applicationId, isRead) {
        if (isRead === 1) {
            if (applicationId) {
                window.location.href = 'view_application.php?id=' + applicationId;
            }
            return;
        }

        // If unread, mark as read first, then redirect
        const formData = new FormData();
        formData.append('notif_id', notifId);
        formData.append('mark_read', '1');

        fetch('notifications.php?ajax=true', {
            method: 'POST',
            body: formData
        }).then(() => {
            if (applicationId) {
                window.location.href = 'view_application.php?id=' + applicationId;
            } else {
                reloadNotifications();
            }
        }).catch(error => {
            console.error('Error:', error);
            if (applicationId) {
                window.location.href = 'view_application.php?id=' + applicationId;
            }
        });
    }

    // Helper functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php include '../includes/footer.php'; ?>
