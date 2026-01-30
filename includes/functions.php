<?php

// Load mailer helper
require_once __DIR__ . '/send_email.php';

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 0;
}

// Get current user name
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

// Get current user role
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? 'user';
}

// Sanitize input 
function sanitize($data) {
    return sanitizeInput($data);
}

// Get status icon
function getStatusIcon($status) {
    $icons = [
        'Pending' => 'fa-clock',
        'Processing' => 'fa-spinner',
        'Approved' => 'fa-check-circle',
        'Rejected' => 'fa-times-circle',
        'Completed' => 'fa-flag-checkered'
    ];
    return $icons[$status] ?? 'fa-clock';
}

// Upload file
function uploadFile($file, $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'], $max_size = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        throw new Exception('File size exceeds maximum allowed size (' . ($max_size / 1048576) . 'MB)');
    }
    
    // Check file type
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowed_types));
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
    $upload_dir = UPLOAD_DIR;
    
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return 'assets/uploads/' . $new_filename;
}

// Delete file
function deleteFile($file_path) {
    $full_path = '../' . $file_path;
    if (file_exists($full_path)) {
        return unlink($full_path);
    }
    return false;
}


// Create notification
function createNotification($pdo, $user_id, $title, $message, $type = 'info', $application_id = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, application_id, title, message, type, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $application_id, $title, $message, $type]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}

// Get unread notification count
function getUnreadNotificationCount($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}
// Mark notification as read
function markNotificationAsRead($pdo, $notification_id, $user_id) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Mark all notifications as read
function markAllNotificationsAsRead($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}


// Get available document types
function getDocumentTypes() {
    return [
        'Valid ID',
        'Birth Certificate',
        'Marriage Certificate',
        'DTI/SEC Registration',
        'Barangay Clearance',
        'Contract of Lease',
        'Land Title',
        'Tax Declaration',
        'Building Plans',
        'Pictures',
        'Other Supporting Documents'
    ];
}

// Format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
}

// Get file icon class
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => 'fa-file-pdf',
        'doc' => 'fa-file-word',
        'docx' => 'fa-file-word',
        'xls' => 'fa-file-excel',
        'xlsx' => 'fa-file-excel',
        'jpg' => 'fa-file-image',
        'jpeg' => 'fa-file-image',
        'png' => 'fa-file-image',
        'gif' => 'fa-file-image',
        'zip' => 'fa-file-archive',
        'rar' => 'fa-file-archive'
    ];
    return $icons[$ext] ?? 'fa-file';
}

// Time ago function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

// Get current user details
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("SELECT id, name, email, mobile, address, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Validate Philippine phone number
function validatePhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (preg_match('/^(09|\+639|639)\d{9}$/', $phone)) {
        return true;
    }
    return false;
}

// Show flash message
function showFlashMessage() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo $_SESSION['success'];
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button></div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo $_SESSION['error'];
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button></div>';
        unset($_SESSION['error']);
    }
}

// Redirect function
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION[$type] = $message;
    }
    header('Location: ' . $url);
    exit();
}

// Get user by ID
function getUserById($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Get status class for badges
function getStatusClass($status) {
    $classes = [
        'pending' => 'warning',
        'Pending' => 'warning',
        'processing' => 'info',
        'Processing' => 'info',
        'approved' => 'success',
        'Approved' => 'success',
        'rejected' => 'danger',
        'Rejected' => 'danger',
        'completed' => 'primary',
        'Completed' => 'primary'
    ];
    return $classes[$status] ?? 'secondary';
}

// Format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

// Copy to clipboard (JavaScript helper)
function copyToClipboard($text) {
    return "navigator.clipboard.writeText('$text')";
}

// Get application status badge HTML
function getStatusBadge($status) {
    $class = getStatusClass($status);
    $icon = getStatusIcon($status);
    return '<span class="badge badge-' . $class . '"><i class="fas ' . $icon . '"></i> ' . $status . '</span>';
}

// Calculate processing days
function calculateProcessingDays($created_at, $completed_at = null) {
    $start = new DateTime($created_at);
    $end = $completed_at ? new DateTime($completed_at) : new DateTime();
    $interval = $start->diff($end);
    return $interval->days;
}

// Check if file exists
function fileExists($file_path) {
    return file_exists(__DIR__ . '/../' . $file_path);
}

// Get file URL
function getFileUrl($file_path) {
    return BASE_URL . '/' . $file_path;
}

// Truncate text
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

// Generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

// Check if date is past
function isPastDate($date) {
    return strtotime($date) < time();
}

// Get days until date
function getDaysUntil($date) {
    $now = new DateTime();
    $target = new DateTime($date);
    $interval = $now->diff($target);
    return $interval->days;
}

