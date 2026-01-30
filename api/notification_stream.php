<?php
// CRITICAL: Add these at the very top
set_time_limit(30);
ini_set('max_execution_time', 30);
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering

// Flush output immediately
ob_end_flush();
flush();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => 'Unauthorized']) . "\n\n";
    flush();
    exit();
}

$user_id = $_SESSION['user_id'];
$last_check = time();

// Run for maximum 25 seconds, then client will reconnect
while (time() - $last_check < 25) {
    try {
        // Use non-blocking read with timeout
        $pdo->setAttribute(PDO::ATTR_TIMEOUT, 2);
        
        // Get statistics with NOLOCK equivalent
        $stats_stmt = $pdo->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today
            FROM notifications 
            WHERE user_id = ?");
        $stats_stmt->execute([$user_id]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get recent notifications (limited, non-blocking)
        $notif_stmt = $pdo->prepare("SELECT n.*, 
            a.tracking_number,
            applicant.name as applicant_name
            FROM notifications n
            LEFT JOIN applications a ON n.application_id = a.id
            LEFT JOIN users applicant ON a.user_id = applicant.id
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT 20");
        $notif_stmt->execute([$user_id]);
        $notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Send data
        echo "data: " . json_encode([
            'stats' => $stats,
            'notifications' => $notifications
        ]) . "\n\n";
        
        flush();
        
        // Wait 3 seconds before next check
        sleep(3);
        
    } catch (Exception $e) {
        echo "event: error\n";
        echo "data: " . json_encode(['error' => 'Database error']) . "\n\n";
        flush();
        break;
    }
}

// Connection timeout - client will reconnect
exit();