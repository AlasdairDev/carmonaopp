<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';


// Check if user is logged in
if(!isLoggedIn() || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit();
}


$user_id = $_SESSION['user_id'];


// Mark notification as read if requested
if(isset($_POST['mark_read']) && isset($_POST['notif_id'])) {
    $notif_id = (int)$_POST['notif_id'];
   
    try {
        // First check if the notification exists
        $check = $pdo->prepare("SELECT id, is_read, user_id FROM notifications WHERE id = ?");
        $check->execute([$notif_id]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);
       
        if(!$existing) {
            $_SESSION['error'] = 'Notification not found';
        } elseif($existing['user_id'] != $user_id) {
            $_SESSION['error'] = 'Permission denied';
        } else {
            if($existing['is_read'] == 1) {
                $_SESSION['success'] = 'Notification already marked as read';
            } else {
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$notif_id, $user_id]);
                $rowsAffected = $stmt->rowCount();
               
                if($rowsAffected > 0) {
                    $_SESSION['success'] = 'Notification marked as read';
                } else {
                    $_SESSION['error'] = 'Failed to mark notification as read';
                }
            }
        }
    } catch(Exception $e) {
        $_SESSION['error'] = 'An error occurred. Please try again.';
        error_log("Error marking notification as read: " . $e->getMessage());
    }
   
    header('Location: notifications.php?filter=' . ($_GET['filter'] ?? 'all'));
    exit();
}


// Mark all as read
if(isset($_POST['mark_all_read'])) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $result = $stmt->execute([$user_id]);
       
        if($result) {
            $_SESSION['success'] = 'All notifications marked as read';
        } else {
            $_SESSION['error'] = 'Failed to mark all notifications as read';
        }
    } catch(Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        error_log("Error marking all notifications as read: " . $e->getMessage());
    }
   
    header('Location: notifications.php?filter=' . ($_GET['filter'] ?? 'all'));
    exit();
}


// Delete notification
if(isset($_POST['delete_notif']) && isset($_POST['notif_id'])) {
    $notif_id = (int)$_POST['notif_id'];
   
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$notif_id, $user_id]);
       
        if($result) {
            $_SESSION['success'] = 'Notification deleted';
        } else {
            $_SESSION['error'] = 'Failed to delete notification';
        }
    } catch(Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        error_log("Error deleting notification: " . $e->getMessage());
    }
   
    header('Location: notifications.php?filter=' . ($_GET['filter'] ?? 'all'));
    exit();
}


// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';


// Build query
$where = "WHERE user_id = ?";
$params = [$user_id];


if($filter === 'unread') {
    $where .= " AND is_read = 0";
} elseif($filter === 'read') {
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


<style>
:root {
    --primary: #7cb342;
    --primary-dark: #689f38;
    --text-dark: #2d3748;
    --text-light: #718096;
    --bg-light: #f8faf8;
}


body {
    /* Keep the dark green gradient on the body */
    background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
    min-height: 100vh;
    box-sizing: border-box;
}


.wrapper {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
    min-height: calc(100vh - 40px);
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
    padding: 4rem 2rem;
}


.page-wrapper {
    position: relative;
    z-index: 1;
    padding: 2rem 0 4rem;
}


.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}


.dashboard-banner {
    background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
    border-radius: 30px;
    padding: 3rem 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}


.dashboard-banner h1 {
    color: white;
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
}


.dashboard-banner p {
    color: rgba(255,255,255,0.95);
    font-size: 1.1rem;
    margin: 0.5rem 0 0 0;
}


.stats-bar {
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    flex-wrap: wrap;
    gap: 1rem;
}


.stats-info {
    display: flex;
    gap: 2rem;
    align-items: center;
}


.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}


.stat-label {
    color: var(--text-light);
    font-size: 0.9rem;
}


.stat-value {
    background: var(--primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-weight: 700;
}


.filter-tabs {
    display: flex;
    gap: 1rem;
}


.filter-tab {
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    border: 2px solid #e0e0e0;
    background: white;
    color: var(--text-dark);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 0.9rem;
}


.filter-tab:hover, .filter-tab.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}


.actions-bar {
    display: flex;
    gap: 1rem;
}


.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
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
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}


.notification-item {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
    display: flex;
    gap: 1.5rem;
    align-items: start;
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
}


.notif-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.8rem 1rem;
    border: none;
    background: #7cb342;
    color: white;
    cursor: pointer;
    border-radius: 12px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    font-weight: 700;
    white-space: nowrap;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}


.notif-btn:hover {
    background: #689f38;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}




.notif-btn.delete-btn {
    background: #ef5350; /
    color: white;
}


.notif-btn.delete-btn:hover {
    background: #c62828;
    color: white;
}


.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}


.empty-icon {
    font-size: 5rem;
    margin-bottom: 1.5rem;
}


.empty-state h3 {
    color: var(--text-dark);
    font-size: 1.5rem;
    margin-bottom: 1rem;
}


.empty-state p {
    color: var(--text-light);
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


@media (max-width: 768px) {
    .container {
        padding: 0 1rem;
    }
   
    .stats-bar {
        flex-direction: column;
        align-items: stretch;
    }
   
    .stats-info {
        flex-direction: column;
        gap: 1rem;
    }
   
    .filter-tabs {
        width: 100%;
        flex-direction: column;
    }
   
    .filter-tab {
        text-align: center;
    }
   
    .notification-item {
        flex-direction: column;
    }
   
    .notif-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>


<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">
            <div class="dashboard-banner">
                <h1>🔔 Notifications</h1>
                <p>Stay updated with your application status</p>
            </div>


            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>


            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>


            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stats-info">
                    <div class="stat-item">
                        <span class="stat-label">Total:</span>
                        <span class="stat-value"><?php echo $counts['total']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Unread:</span>
                        <span class="stat-value"><?php echo $counts['unread']; ?></span>
                    </div>
                </div>


                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        All
                    </a>
                    <a href="?filter=unread" class="filter-tab <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                        Unread
                    </a>
                    <a href="?filter=read" class="filter-tab <?php echo $filter === 'read' ? 'active' : ''; ?>">
                        Read
                    </a>
                </div>


                <?php if($counts['unread'] > 0): ?>
                <div class="actions-bar">
                    <form method="POST" action="notifications.php" style="display: inline-block;">
                        <input type="hidden" name="mark_all_read" value="1">
                        <button type="submit" class="btn btn-primary">
                            ✓ Mark All Read
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>


            <!-- Notifications List -->
            <div class="notifications-container">
                <?php if(count($notifications) > 0): ?>
                    <?php foreach($notifications as $notif): ?>
                        <div class="notification-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>">
                            <div class="notif-icon <?php echo htmlspecialchars($notif['type']); ?>">
                                <?php
                                $icons = [
                                    'success' => '✓',
                                    'info' => 'ℹ',
                                    'warning' => '⚠',
                                    'danger' => '✕'
                                ];
                                echo $icons[$notif['type']] ?? 'ℹ';
                                ?>
                            </div>
                           
                            <div class="notif-content">
                                <h3 class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></h3>
                                <p class="notif-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <div class="notif-meta">
                                    <span>📅 <?php echo date('M d, Y', strtotime($notif['created_at'])); ?></span>
                                    <span>🕐 <?php echo date('h:i A', strtotime($notif['created_at'])); ?></span>
                                    <?php if($notif['application_id']): ?>
                                        <a href="view_application.php?id=<?php echo $notif['application_id']; ?>"
                                        style="color: var(--primary); text-decoration: none; font-weight: 600;">
                                            View Application →
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>


                            <div class="notif-actions">
                                <?php if(!$notif['is_read']): ?>
                                    <form method="POST" action="notifications.php" style="display: inline-block;">
                                        <input type="hidden" name="notif_id" value="<?php echo (int)$notif['id']; ?>">
                                        <input type="hidden" name="mark_read" value="1">
                                        <button type="submit" class="notif-btn">
                                            ✓ Mark Read
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="notifications.php" style="display: inline-block;"
                                    onsubmit="return confirm('Delete this notification?');">
                                    <input type="hidden" name="notif_id" value="<?php echo (int)$notif['id']; ?>">
                                    <input type="hidden" name="delete_notif" value="1">
                                    <button type="submit" class="notif-btn delete-btn">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🔔</div>
                        <h3>No Notifications</h3>
                        <p>You're all caught up! Check back later for updates.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>

