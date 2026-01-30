<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle AJAX requests
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    header('Content-Type: application/json');

    // Handle mark all as read
    if (isset($_POST['mark_all_read'])) {
        try {
            if (isDepartmentAdmin()) {
                // mark notifications for applications in their department
                $stmt = $pdo->prepare("
                UPDATE notifications n
                INNER JOIN applications a ON n.application_id = a.id
                SET n.is_read = 1 
                WHERE n.user_id = ? AND a.department_id = ?
            ");
                $stmt->execute([$_SESSION['user_id'], $_SESSION['department_id']]);
            } else {
                // Superadmin marks all
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
            }
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to mark all as read']);
        }
        exit();
    }
    // Handle delete all notifications
    if (isset($_POST['delete_all'])) {
        try {
            if (isDepartmentAdmin()) {
                // delete notifications for applications in their department
                $stmt = $pdo->prepare("
                DELETE n FROM notifications n
                INNER JOIN applications a ON n.application_id = a.id
                WHERE n.user_id = ? AND a.department_id = ?
            ");
                $stmt->execute([$_SESSION['user_id'], $_SESSION['department_id']]);
            } else {
                // Superadmin deletes all
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
            }
            echo json_encode(['success' => true, 'message' => 'All notifications deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete all notifications']);
        }
        exit();
    }

    // Handle delete notification
    if (isset($_POST['delete_notification']) && isset($_POST['notification_id'])) {
        $notification_id = (int) $_POST['notification_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $_SESSION['user_id']]);
            echo json_encode(['success' => true, 'message' => 'Notification deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
        }
        exit();
    }

    // Handle mark as read
    if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
        $notification_id = (int) $_POST['notification_id'];
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $_SESSION['user_id']]);
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to mark as read']);
        }
        exit();
    }

    // Handle fetch notifications (for filters and pagination)
    if (isset($_GET['fetch_notifications'])) {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $user_filter = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
        $type_filter = isset($_GET['type']) ? $_GET['type'] : '';
        $read_filter = isset($_GET['read']) ? $_GET['read'] : '';

        $where = [];
        $params = [];

        $where[] = "n.user_id = ?";
        $params[] = $_SESSION['user_id'];

        if ($read_filter !== '') {
            $where[] = "n.is_read = ?";
            $params[] = $read_filter === 'read' ? 1 : 0;
        }
        // DEPARTMENT FILTERING
        if (isDepartmentAdmin()) {
            $where[] = "a.department_id = ?";
            $params[] = $_SESSION['department_id'];
        }
        if ($user_filter) {
            $where[] = "a.user_id = ?"; 
            $params[] = $user_filter;
        }

        if ($type_filter) {
            $where[] = "n.type = ?";
            $params[] = $type_filter;
        }

        $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $count_sql = "SELECT COUNT(*) FROM notifications n 
                      LEFT JOIN applications a ON n.application_id = a.id 
                      $where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetchColumn();
        $total_pages = ceil($total / $per_page);

        // Get notifications
        $sql = "SELECT n.*, 
                u.name as user_name, 
                u.email as user_email,
                a.tracking_number,
                applicant.name as applicant_name
                FROM notifications n
                LEFT JOIN users u ON n.user_id = u.id
                LEFT JOIN applications a ON n.application_id = a.id
                LEFT JOIN users applicant ON a.user_id = applicant.id
                $where_clause
                ORDER BY n.created_at DESC
                LIMIT $per_page OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get statistics
        $current_user = $_SESSION['user_id'];

        // Build department filter for stats
        $dept_stats_where = '';
        $dept_stats_params = [];
        if (isDepartmentAdmin()) {
            $dept_stats_where = ' AND EXISTS (
            SELECT 1 FROM applications a 
            WHERE a.id = notifications.application_id 
            AND a.department_id = ?
        )';
            $dept_stats_params = [$_SESSION['department_id']];
        }
        $stats_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?" . $dept_stats_where);
        $stats_stmt->execute(array_merge([$current_user], $dept_stats_params));
        $total_notifs = $stats_stmt->fetchColumn();

        $stats_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND user_id = ?" . $dept_stats_where);
        $stats_stmt->execute(array_merge([$current_user], $dept_stats_params));
        $unread_notifs = $stats_stmt->fetchColumn();

        $stats_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE DATE(created_at) = CURDATE() AND user_id = ?" . $dept_stats_where);
        $stats_stmt->execute(array_merge([$current_user], $dept_stats_params));
        $today_notifs = $stats_stmt->fetchColumn();

        $stats = [
            'total' => $total_notifs,
            'unread' => $unread_notifs,
            'today' => $today_notifs
        ];

        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total' => $total
            ]
        ]);
        exit();
    }
}

// Get department filter for users list
if (isDepartmentAdmin()) {
    $dept_id = $_SESSION['department_id'];
    $users = $pdo->prepare("
        SELECT DISTINCT u.id, u.name 
        FROM users u
        INNER JOIN applications a ON u.id = a.user_id
        WHERE u.role = 'user' AND a.department_id = ?
        ORDER BY u.name
    ");
    $users->execute([$dept_id]);
    $users = $users->fetchAll(PDO::FETCH_ASSOC);
} else {
    $users = $pdo->query("
        SELECT DISTINCT u.id, u.name 
        FROM users u
        INNER JOIN applications a ON u.id = a.user_id
        WHERE u.role = 'user'
        ORDER BY u.name
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$types = [
    'new_application',
    'cancelled_application',
    'payment_submitted'
];

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
    <link rel="stylesheet" href="../assets/css/admin-responsive.css">
    <link rel="stylesheet" href="../assets/css/admin/notifications_styles.css">

</head>

<body>

    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <h1></i> Notifications</h1>
                <p>Manage and track all system notifications</p>
            </div>
            <div class="header-actions">
                <button type="button" class="header-btn" onclick="markAllAsRead()" id="markAllBtn"
                    style="display: none;">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
                <button type="button" class="header-btn btn-danger" onclick="showDeleteAllModal()" id="clearAllBtn"
                    style="display: none;">
                    <i class="fas fa-trash-alt"></i> Clear All Notifications
                </button>
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
                    <div class="stat-value" id="stat-total">0</div>
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
                    <div class="stat-value" id="stat-unread">0</div>
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
                    <div class="stat-value" id="stat-today">0</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <div class="filters-header">
                <i class="fas fa-filter"></i>
                <h3>Filter Notifications</h3>
            </div>

            <form id="filterForm">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Applicant</label>
                        <select name="user_id" id="filter-user">
                            <option value="">All Applicants</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Type</label>
                        <select name="type" id="filter-type">
                            <option value="">All Types</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $type)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status</label>
                        <select name="read" id="filter-read">
                            <option value="">All Status</option>
                            <option value="unread">Unread</option>
                            <option value="read">Read</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                    </div>

                </div>
            </form>
        </div>

        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loadingSpinner">
            <div class="spinner"></div>
        </div>

        <!-- Notifications List -->
        <div class="notifications-list" id="notificationsList">
            <!-- Notifications will be loaded here via AJAX -->
        </div>

        <!-- Pagination -->
        <div class="pagination" id="paginationContainer">
        </div>
    </div>

    <!-- Delete All Notifications Modal -->
    <div id="deleteAllModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-icon">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h2>Clear All Notifications</h2>
            <p>Are you sure you want to delete ALL notifications? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeDeleteAllModal()">Cancel</button>
                <button class="btn-delete" onclick="confirmDeleteAll()">Delete All</button>
            </div>
        </div>
    </div>

    <!-- Delete Single Notification Modal -->
    <div id="deleteSingleModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-icon">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h2>Delete Notification</h2>
            <p>Are you sure you want to delete this notification? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeDeleteSingleModal()">Cancel</button>
                <button class="btn-delete" onclick="confirmDeleteSingle()">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentDeleteId = null;
        let autoRefreshInterval;
        let lastNotificationId = 0;

        // Load notifications via AJAX
        function loadNotificationsPage(page = 1, silent = false) {
            const filters = new URLSearchParams({
                ajax: 'true',
                fetch_notifications: 'true',
                page: page,
                user_id: document.getElementById('filter-user').value,
                type: document.getElementById('filter-type').value,
                read: document.getElementById('filter-read').value
            });

            fetch('<?php echo BASE_URL; ?>/admin/notifications.php?' + filters.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update stats
                        document.getElementById('stat-total').textContent = data.stats.total.toLocaleString();
                        document.getElementById('stat-unread').textContent = data.stats.unread.toLocaleString();
                        document.getElementById('stat-today').textContent = data.stats.today.toLocaleString();

                        const markAllBtn = document.getElementById('markAllBtn');
                        const clearAllBtn = document.getElementById('clearAllBtn');

                        if (data.stats.unread > 0) {
                            markAllBtn.style.display = 'inline-flex';
                        } else {
                            markAllBtn.style.display = 'none';
                        }

                        // Hide Clear All button if no notifications
                        if (data.stats.total > 0) {
                            clearAllBtn.style.display = 'inline-flex';
                        } else {
                            clearAllBtn.style.display = 'none';
                        }

                        // Check if there's a new notification
                        if (data.notifications.length > 0) {
                            const newestId = data.notifications[0].id;
                            if (lastNotificationId > 0 && newestId > lastNotificationId && !silent) {
                                showToast('New notification received!', 'success');
                            }
                            lastNotificationId = Math.max(lastNotificationId, newestId);
                        }

                        renderNotifications(data.notifications);
                        renderPagination(data.pagination);
                        currentPage = data.pagination.current_page;
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }

        // Render notifications
        function renderNotifications(notifications) {
            const container = document.getElementById('notificationsList');

            if (notifications.length === 0) {
                container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No notifications found</h3>
                    <p>There are no notifications matching your filters.</p>
                </div>
            `;
                return;
            }

            let html = '';
            notifications.forEach(notif => {
                const unreadClass = notif.is_read == 0 ? 'unread' : '';
                const applicantInfo = notif.applicant_name ? `<span><i class="fas fa-user"></i> ${escapeHtml(notif.applicant_name)}</span>` : '';
                const trackingInfo = notif.tracking_number ? `<span><i class="fas fa-hashtag"></i> ${escapeHtml(notif.tracking_number)}</span>` : '';
                const markReadBtn = notif.is_read == 0 ? `
                <button type="button" class="btn-icon btn-icon-read" onclick="markAsRead(${notif.id})" title="Mark as Read">
                    <i class="fas fa-eye"></i>
                </button>
            ` : '';

                html += `
                <div class="notification-item ${unreadClass}" id="notif-${notif.id}">
                    <div class="notification-icon ${notif.type}">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${notif.title}</div>
                        <div class="notification-meta">
                            ${applicantInfo}
                            ${trackingInfo}
                            <span><i class="fas fa-clock"></i> ${formatDate(notif.created_at)}</span>
                        </div>
                        <div class="notification-message">${notif.message}</div>
                    </div>
                    <div class="notification-actions">
                        ${markReadBtn}
                        <button type="button" class="btn-icon btn-icon-delete" onclick="showDeleteSingleModal(${notif.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            });

            container.innerHTML = html;
        }

        // Render pagination
        function renderPagination(pagination) {
            const container = document.getElementById('paginationContainer');

            if (pagination.total_pages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            for (let i = 1; i <= pagination.total_pages; i++) {
                const activeClass = i === pagination.current_page ? 'active' : '';
                if (i === pagination.current_page) {
                    html += `<span class="${activeClass}">${i}</span>`;
                } else {
                    html += `<a href="javascript:void(0)" onclick="loadNotificationsPage(${i})">${i}</a>`;
                }
            }

            container.innerHTML = html;
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('filter-user').value = '';
            document.getElementById('filter-type').value = '';
            document.getElementById('filter-read').value = '';
            loadNotificationsPage(1);
        }

        // Mark all as read
        function markAllAsRead() {
            fetch('notifications.php?ajax=true', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'mark_all_read=1'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        loadNotificationsPage(currentPage);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to mark all as read', 'error');
                });
        }

        // Mark single notification as read
        function markAsRead(notificationId) {
            fetch('notifications.php?ajax=true', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `mark_read=1&notification_id=${notificationId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        loadNotificationsPage(currentPage);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to mark as read', 'error');
                });
        }

        // Delete All Modal
        function showDeleteAllModal() {
            document.getElementById('deleteAllModal').classList.add('active');
        }

        function closeDeleteAllModal() {
            document.getElementById('deleteAllModal').classList.remove('active');
        }

        function confirmDeleteAll() {
            fetch('notifications.php?ajax=true', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'delete_all=1'
            })
                .then(response => response.json())
                .then(data => {
                    closeDeleteAllModal();
                    if (data.success) {
                        showToast(data.message, 'success');
                        loadNotificationsPage(1);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    closeDeleteAllModal();
                    showToast('Failed to delete all notifications', 'error');
                });
        }

        // Delete Single Modal
        function showDeleteSingleModal(notificationId) {
            currentDeleteId = notificationId;
            document.getElementById('deleteSingleModal').classList.add('active');
        }

        function closeDeleteSingleModal() {
            document.getElementById('deleteSingleModal').classList.remove('active');
            currentDeleteId = null;
        }

        function confirmDeleteSingle() {
            if (currentDeleteId) {
                fetch('notifications.php?ajax=true', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `delete_notification=1&notification_id=${currentDeleteId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        closeDeleteSingleModal();
                        if (data.success) {
                            showToast(data.message, 'success');
                            loadNotificationsPage(currentPage);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        closeDeleteSingleModal();
                        showToast('Failed to delete notification', 'error');
                    });
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeDeleteAllModal();
                closeDeleteSingleModal();
            }
        });

        // Toast notification
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

        // Helper functions
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('en-US', options);
        }

        // fast auto-refresh
        function startAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

            // Check for new notifications 
            autoRefreshInterval = setInterval(function () {
                loadNotificationsPage(currentPage, true);
            }, 500); 
        }

        // Start when page loads
        document.addEventListener('DOMContentLoaded', function () {
            loadNotificationsPage(1, true);
            startAutoRefresh();
            console.log('✅ Auto-refresh enabled (checking every 0.5 seconds)');

            // event listeners to filter dropdowns
            document.getElementById('filter-user').addEventListener('change', function () {
                loadNotificationsPage(1);
            });

            document.getElementById('filter-type').addEventListener('change', function () {
                loadNotificationsPage(1);
            });

            document.getElementById('filter-read').addEventListener('change', function () {
                loadNotificationsPage(1);
            });
        });

        // Stop when leaving page
        window.addEventListener('beforeunload', function () {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        });

    </script>
</body>

</html>

<?php include '../includes/footer.php'; ?>
