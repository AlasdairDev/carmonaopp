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
    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
    strpos($_SERVER['HTTP_HOST'], 'ngrok-free.dev') !== false // ADD THIS LINE
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

// User Roles
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');

// 5. Database Configuration (Auto-switch based on environment)
if ($isLocal) {
    // LOCAL DEVELOPMENT
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'carmonaopp_db');
} else {
    // PRODUCTION (InfinityFree)
    // ⚠️ UPDATE THESE AFTER CREATING DATABASE ON INFINITYFREE
    define('DB_HOST', 'sql210.infinityfree.com'); // Your InfinityFree DB host
    define('DB_USER', 'if0_40982177'); // Your InfinityFree DB username
    define('DB_PASS', 'KeiChoo57'); // Your InfinityFree DB password
    define('DB_NAME', 'if0_40982177_lgu_permit_tracking'); // Your InfinityFree DB name
}

// 6. System Constants
define('SITE_NAME', 'Carmona Online Permit Portal');

// 7. AUTOMATIC BASE URL DETECTION (FIXED)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Check if localhost
if (strpos($host, 'localhost') !== false || $host === '127.0.0.1') {
    define('BASE_URL', 'http://localhost/carmonaopp');
}
// Check if ngrok
elseif (strpos($host, 'ngrok') !== false) {
    define('BASE_URL', $protocol . "://" . $host . "/carmonaopp");
}
// Production
else {
    define('BASE_URL', 'https://carmonaops.infinityfreeapp.com');
}

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
define('SMTP_USERNAME', 'keithjustine57@gmail.com');
define('SMTP_PASSWORD', 'kmssvxavsskufcjj'); // Your Gmail App Password
define('SMTP_FROM_EMAIL', 'keithjustine57@gmail.com');
define('SMTP_FROM_NAME', 'LGU Permit System');
define('SMTP_REPLY_TO', 'keithjustine57@gmail.com');

// 10. SMS CONFIGURATION (Semaphore API)
define('SMS_ENABLED', true);
define('SEMAPHORE_API_KEY', 'e57e3ac833f5121582d1dc49295f8b4c');
define('SEMAPHORE_SENDER_NAME', 'LGU-PERMIT');

// 11. Session Settings
define('SESSION_TIMEOUT', 3600);
define('SESSION_NAME', 'LGU_PERMIT_SESSION');

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

function logActivity($userId, $action, $description, $details = null, $ipAddress = null) {
    global $pdo;
    
    try {
        $ipAddress = $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, description, details, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $detailsJson = $details ? json_encode($details) : null;
        $stmt->execute([$userId, $action, $description, $detailsJson, $ipAddress, $userAgent]);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
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

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === ROLE_ADMIN;
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
?>