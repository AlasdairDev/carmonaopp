<?php
/**
 * LGU Permit Tracking System - Configuration File
 * Auto-detects Local vs Production environment
 */
// 1. Prevent "Session already started" errors
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Set Timezone (Important for Philippines)
date_default_timezone_set('Asia/Manila');

// 3. Detect Environment (Local vs Production)
$isLocal = (
    $_SERVER['HTTP_HOST'] === 'localhost' || 
    $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0
);

// 4. Error Reporting (Different for Local vs Production)
if ($isLocal) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}

// User Roles - UPDATED FOR RBAC
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin'); // Legacy admin role
define('ROLE_DEPARTMENT_ADMIN', 'department_admin'); // NEW
define('ROLE_SUPERADMIN', 'superadmin'); // NEW

// 5. Database Configuration (Auto-switch based on environment)
if ($isLocal) {
    // LOCAL DEVELOPMENT
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'carmonaopp_db');
} else {
    // PRODUCTION (InfinityFree)
    define('DB_HOST', 'sql100.infinityfree.com');
    define('DB_USER', 'if0_40997416');
    define('DB_PASS', 'KeithJusitne57');
    define('DB_NAME', 'if0_40997416_carmonaopp_db');
}

// 6. System Constants
define('SITE_NAME', 'Carmona Online Permit Portal');

// AUTOMATIC BASE URL DETECTION
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptPath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

if ($isLocal) {
    define('BASE_URL', 'http://localhost/carmonaopp');
} else {
    // Auto-detect production URL
    define('BASE_URL', 'https://carmonaopp.great-site.net');
}

define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB


// 8. Initialize PDO (Primary connection)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
    
    // ✅ FIX FOR INFINITYFREE: Set MySQL timezone to Philippine time (+08:00)
    // This ensures all timestamps are stored and retrieved in Philippine timezone
    try {
        $pdo->exec("SET time_zone = '+08:00'");
    } catch (PDOException $e) {
        error_log("Failed to set MySQL timezone: " . $e->getMessage());
    }
    
} catch (PDOException $e) {
    error_log("PDO Connection failed: " . $e->getMessage());
    if ($isLocal) {
        die("PDO Connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please contact administrator.");
    }
}

// 9. EMAIL CONFIGURATION (PHPMailer SMTP)
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'carmonaopp@gmail.com');
define('SMTP_PASSWORD', 'gykxqqyllsxliakf');
define('SMTP_FROM_EMAIL', 'carmonaopp@gmail.com');
define('SMTP_FROM_NAME', 'Carmona Online Permit Portal');
define('SMTP_REPLY_TO', 'carmonaopp@gmail.com');

// 10. SMS CONFIGURATION (Semaphore API)
define('SMS_ENABLED', true);
define('SEMAPHORE_API_KEY', 'e57e3ac833f5121582d1dc49295f8b4c');
define('SEMAPHORE_SENDER_NAME', 'Carmona Online Permit Portal');

// 11. Session Settings
define('SESSION_TIMEOUT', 3600);
define('SESSION_NAME', 'CarmonaOPP_SESSION');

// 12. Security Settings
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

// 13. Pagination
define('RECORDS_PER_PAGE', 10);

// 14. File Upload Settings
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'doc']);
define('UPLOAD_MAX_SIZE', 10485760);

/**
 * Helper Functions
 */

function getDBConnection() {
    global $pdo;
    return $pdo;
}

function isEmailConfigured() {
    if (!defined('SMTP_ENABLED') || !SMTP_ENABLED) {
        return false;
    }
    
    if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD')) {
        return false;
    }
    
    if (strlen(SMTP_PASSWORD) < 10) {
        error_log("WARNING: Gmail App Password not configured properly!");
        return false;
    }
    
    return true;
}

function isSMSConfigured() {
    if (!defined('SMS_ENABLED') || !SMS_ENABLED) {
        return false;
    }
    
    if (!defined('SEMAPHORE_API_KEY') || empty(SEMAPHORE_API_KEY)) {
        return false;
    }
    
    return true;
}

function getNotificationStatus() {
    $status = [];
    
    if (isEmailConfigured()) {
        $status[] = "✅ Email: Enabled";
    } else {
        $status[] = "❌ Email: Not configured";
    }
    
    if (isSMSConfigured()) {
        $status[] = "✅ SMS: Enabled";
    } else {
        $status[] = "❌ SMS: Not configured";
    }
    
    return implode(" | ", $status);
}

// Log user activity
function logActivity($user_id, $action, $description, $details = null, $related_department_id = null) {
    global $pdo;
    
    // Get real IP address (works with proxies/load balancers)
    $ip_address = null;
    
    // Priority 1: Cloudflare
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    // Priority 2: Behind proxy/load balancer
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        $ip_address = trim($ip_address);
    }
    // Priority 3: Nginx real IP
    elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip_address = $_SERVER['HTTP_X_REAL_IP'];
    }
    // Priority 4: Direct connection
    elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    // Fallback: Unknown
    else {
        $ip_address = 'UNKNOWN';
    }
    
    // Validate IP address format
    if ($ip_address !== 'UNKNOWN' && !filter_var($ip_address, FILTER_VALIDATE_IP)) {
        $ip_address = 'INVALID_IP';
    }
    
    // Get user agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Set department_id from session (the actor's department)
    $department_id = $_SESSION['department_id'] ?? null;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, description, details, ip_address, user_agent, department_id, related_department_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $action,
            $description,
            $details ? json_encode($details) : null,
            $ip_address,
            $user_agent,
            $department_id,           // Actor's department (from session)
            $related_department_id    // Affected department (from parameter)
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}

/**
 * ✅ NEW FUNCTION: Convert database timestamp to relative time
 * This handles timezone conversion for display purposes
 */
function getRelativeTime($timestamp) {
    if (empty($timestamp)) {
        return 'Unknown';
    }
    
    // Parse the timestamp (MySQL NOW() already in Asia/Manila due to our SET time_zone)
    $dbTime = new DateTime($timestamp, new DateTimeZone('Asia/Manila'));
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    
    // Calculate difference
    $diff = $now->diff($dbTime);
    
    // If in the future
    if ($diff->invert == 0) {
        return 'just now';
    }
    
    // Format based on time difference
    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
    
    if ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    }
    
    if ($diff->d > 0) {
        if ($diff->d == 1) {
            return 'yesterday';
        }
        if ($diff->d < 7) {
            return $diff->d . ' days ago';
        }
        $weeks = floor($diff->d / 7);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    }
    
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
    
    if ($diff->s > 10) {
        return $diff->s . ' seconds ago';
    }
    
    return 'just now';
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) == 12 && substr($phone, 0, 2) == '63') {
        return '0' . substr($phone, 2);
    }
    
    if (strlen($phone) == 11 && substr($phone, 0, 2) == '09') {
        return $phone;
    }
    
    return $phone;
}

function generateTrackingNumber() {
    return 'CRMN-' . date('Y') . '-' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * UPDATED AUTHENTICATION FUNCTIONS FOR RBAC
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function isAdmin() {
    return isLoggedIn() && in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_DEPARTMENT_ADMIN, ROLE_SUPERADMIN]);
}

function isSuperAdmin() {
    return isLoggedIn() && $_SESSION['role'] === ROLE_SUPERADMIN;
}

function isDepartmentAdmin() {
    return isLoggedIn() && $_SESSION['role'] === ROLE_DEPARTMENT_ADMIN;
}

function isRegularUser() {
    return isLoggedIn() && $_SESSION['role'] === ROLE_USER;
}

function getAdminDepartmentId() {
    if (isDepartmentAdmin() && isset($_SESSION['department_id'])) {
        return $_SESSION['department_id'];
    }
    return null;
}

function requireLogin($redirectTo = '/index.php') {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . $redirectTo);
        exit;
    }
}

function requireAdmin($redirectTo = '/index.php') {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . $redirectTo);
        exit;
    }
}

function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        $_SESSION['error'] = 'Access denied. Superadmin privileges required.';
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
}

// Debug config check (only works locally)
if ($isLocal && isset($_GET['config_check']) && $_GET['config_check'] === 'true') {
    echo "<!DOCTYPE html><html><head><title>Config Check</title>";
    echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;} .error{color:red;}</style>";
    echo "</head><body>";
    echo "<h2>🔧 Configuration Status</h2>";
    echo "<p><strong>Environment:</strong> " . ($isLocal ? "🏠 LOCAL" : "🌐 PRODUCTION") . "</p>";
    echo "<p><strong>Database:</strong> <span class='success'>✅ Connected to " . DB_NAME . "</span></p>";
    echo "<p><strong>Base URL:</strong> " . BASE_URL . "</p>";
    echo "<hr>";
    echo "<h3>📧 Email Settings:</h3>";
    echo "<p>Enabled: " . (SMTP_ENABLED ? "<span class='success'>✅ Yes</span>" : "<span class='error'>❌ No</span>") . "</p>";
    echo "<p>SMTP Host: " . SMTP_HOST . "</p>";
    echo "<p>Username: " . SMTP_USERNAME . "</p>";
    echo "<p>Password: " . (strlen(SMTP_PASSWORD) > 10 ? "<span class='success'>✅ Set</span>" : "<span class='error'>❌ Not set</span>") . "</p>";
    echo "<hr>";
    echo "<h3>📱 SMS Settings:</h3>";
    echo "<p>Enabled: " . (SMS_ENABLED ? "<span class='success'>✅ Yes</span>" : "<span class='error'>❌ No</span>") . "</p>";
    echo "<p>API Key: " . (strlen(SEMAPHORE_API_KEY) > 10 ? "<span class='success'>✅ Set</span>" : "<span class='error'>❌ Not set</span>") . "</p>";
    echo "</body></html>";
    exit;
}
