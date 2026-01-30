<?php
/**
 * Security Functions 
 * Includes CSRF protection, rate limiting, security logging, and RBAC
 * WITH ROLE-BASED ACCESS CONTROL
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ============================================
 * RBAC FUNCTIONS
 * ============================================
 */

/**
 * Get department name of the logged-in admin
 */
function getAdminDepartmentName() {
    global $pdo;
    
    $dept_id = getAdminDepartmentId();
    if (!$dept_id) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->execute([$dept_id]);
        $result = $stmt->fetch();
        return $result ? $result['name'] : null;
    } catch (PDOException $e) {
        error_log("Error getting department name: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if admin has access to a specific department
 * Superadmins have access to all departments
 * Department admins only have access to their own department
 */
function hasAccessToDepartment($department_id) {
    if (isSuperAdmin()) {
        return true; // Superadmin has access to all departments
    }
    
    if (isDepartmentAdmin()) {
        return getAdminDepartmentId() == $department_id;
    }
    
    return false;
}

/**
 * Check if admin has access to a specific application
 * Based on the application's department
 */
function hasAccessToApplication($application_id) {
    global $pdo;
    
    if (isSuperAdmin()) {
        return true; // Superadmin has access to all applications
    }
    
    if (!isDepartmentAdmin()) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT department_id FROM applications WHERE id = ?");
        $stmt->execute([$application_id]);
        $result = $stmt->fetch();
        
        if ($result) {
            return hasAccessToDepartment($result['department_id']);
        }
    } catch (PDOException $e) {
        error_log("Error checking application access: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Get department filter for SQL queries
 * Returns WHERE clause to filter by department if needed
 */
/**
 * Get department filter for queries (for department admins)
 * Returns array with WHERE clause and parameters
 */
function getDepartmentFilter($table_alias = '') {
    // If superadmin, no filtering needed
    if (isSuperAdmin()) {
        return [
            'where' => '',
            'params' => []
        ];
    }
    
    // If department admin, filter by their department
    if (isDepartmentAdmin()) {
        $dept_id = getAdminDepartmentId();
        if ($dept_id) {
            $alias = $table_alias ? $table_alias . '.' : '';
            return [
                'where' => "{$alias}department_id = ?",
                'params' => [$dept_id]
            ];
        }
    }
    
    // Legacy admin or no department restriction
    return [
        'where' => '',
        'params' => []
    ];
}

/**
 * Get user's role display name
 */
function getRoleDisplayName($role = null) {
    if ($role === null) {
        $role = $_SESSION['role'] ?? '';
    }
    
    $roles = [
        'user' => 'User',
        'admin' => 'Admin',
        'department_admin' => 'Department Admin',
        'superadmin' => 'Super Admin'
    ];
    
    return $roles[$role] ?? 'Unknown';
}

/**
 * Prevent admin from accessing user pages
 */
function preventAdminAccessToUserPages() {
    if (isAdmin()) {
        log_security_event('UNAUTHORIZED_ACCESS', 'Attempt to access user dashboard', [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        header('Location: ../admin/dashboard.php');
        exit();
    }
}

/**
 * Prevent user from accessing admin pages
 */
function preventUserAccessToAdminPages() {
    if (!isAdmin()) {
        log_security_event('UNAUTHORIZED_ACCESS', 'Attempt to access admin area', [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        header('Location: ../user/dashboard.php');
        exit();
    }
}

/**
 * ============================================
 * EXISTING SECURITY FUNCTIONS
 * ============================================
 */

/**
 * Generate CSRF Token
 * Creates a new token if one doesn't exist
 * 
 * @return string The CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 * Checks if the provided token matches the session token
 * 
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF Token Field (HTML)
 * Returns HTML input field with CSRF token
 * 
 * @return string HTML input field
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Regenerate Session ID
 * Prevents session fixation attacks
 */
function regenerate_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 
 * @param string $data The data to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Verify Password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Hash Password

 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Check Rate Limit
 * Prevents brute force attacks by limiting login attempts
 * 

 */
function check_rate_limit($identifier, $max_attempts = 5, $lockout_time = 900) {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    $identifier = strtolower(trim($identifier));
    
    // Initialize if not exists
    if (!isset($_SESSION['login_attempts'][$identifier])) {
        $_SESSION['login_attempts'][$identifier] = [
            'count' => 0,
            'first_attempt' => time(),
            'locked_until' => 0
        ];
    }
    
    $attempt_data = &$_SESSION['login_attempts'][$identifier];
    $current_time = time();
    
    // Check if currently locked out
    if ($attempt_data['locked_until'] > $current_time) {
        $time_remaining = ceil(($attempt_data['locked_until'] - $current_time) / 60);
        return [
            'allowed' => false,
            'attempts' => $attempt_data['count'],
            'time_remaining' => $time_remaining
        ];
    }
    
    // Reset if lockout period has passed
    if ($attempt_data['locked_until'] > 0 && $attempt_data['locked_until'] <= $current_time) {
        $attempt_data['count'] = 0;
        $attempt_data['first_attempt'] = $current_time;
        $attempt_data['locked_until'] = 0;
    }
    
    // Increment attempt counter
    $attempt_data['count']++;
    
    // Lock out if max attempts reached
    if ($attempt_data['count'] >= $max_attempts) {
        $attempt_data['locked_until'] = $current_time + $lockout_time;
        $time_remaining = ceil($lockout_time / 60);
        
        return [
            'allowed' => false,
            'attempts' => $attempt_data['count'],
            'time_remaining' => $time_remaining
        ];
    }
    
    return [
        'allowed' => true,
        'attempts' => $attempt_data['count'],
        'time_remaining' => 0
    ];
}

/**
 * Clear Rate Limit
 * Removes rate limit for a specific identifier (call on successful login)

 */
function clear_rate_limit($identifier) {
    if (isset($_SESSION['login_attempts'])) {
        $identifier = strtolower(trim($identifier));
        unset($_SESSION['login_attempts'][$identifier]);
    }
}

/**
 * Log Security Event
 * Logs security-related events to database
 */
function log_security_event($event_type, $description, $additional_data = []) {
    global $pdo;
    
    if (!isset($pdo)) {
        error_log("Security Event [{$event_type}]: {$description}");
        return;
    }
    
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $user_id = $_SESSION['user_id'] ?? null;
        
        $data = array_merge([
            'ip' => $ip_address,
            'user_agent' => $user_agent,
            'timestamp' => date('Y-m-d H:i:s')
        ], $additional_data);
        
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, description, details, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $event_type,
            $description,
            json_encode($data),
            $ip_address,
            $user_agent
        ]);
    } catch (Exception $e) {
        error_log("Failed to log security event: " . $e->getMessage());
    }
}

/**
 * Validate Password Strength
 * Checks if password meets minimum security requirements

 */
function validate_password_strength($password, $min_length = 8) {
    $errors = [];
    
    if (strlen($password) < $min_length) {
        $errors[] = "Password must be at least {$min_length} characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Simple password strength check (for backward compatibility)
 */
function isStrongPassword($password) {
    $result = validate_password_strength($password);
    return $result['valid'];
}

/**
 * Check Session Timeout
 * Validates if user session has expired
 */
function check_session_timeout($timeout = 3600) {
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        
        if ($elapsed > $timeout) {
            session_destroy();
            return false;
        }
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Validate Email Format
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Prevent SQL Injection
 * Wrapper for prepared statement parameter
 */
function escape_sql($input) {
    global $pdo;
    if ($pdo) {
        return $pdo->quote($input);
    }
    return addslashes($input);
}

/**
 * Generate Secure Random Token
 * For password resets, API keys, etc.
 */
function generate_secure_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Check if IP is Whitelisted
 */
function check_ip_whitelist($whitelist = []) {
    if (empty($whitelist)) {
        return true; 
    }
    
    $current_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($current_ip, $whitelist);
}

/**
 * Remove dangerous characters from filenames
 */
function sanitize_filename($filename) {
    // Remove path components
    $filename = basename($filename);
    
    // Remove special characters except dots, dashes, underscores
    $filename = preg_replace('/[^A-Za-z0-9\._-]/', '_', $filename);
    
    // Prevent double extensions
    $filename = preg_replace('/\.+/', '.', $filename);
    
    return $filename;
}

/**
 * Detect XSS Attempts
 * Basic XSS detection (use in addition to sanitization)
 */
function detect_xss($input) {
    $dangerous_patterns = [
        '/<script\b[^>]*>(.*?)<\/script>/is',
        '/javascript:/i',
        '/on\w+\s*=/i', 
        '/<iframe/i',
        '/<object/i',
        '/<embed/i',
    ];
    
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Log Failed Login Attempt
 * Convenience function for logging failed logins
 */
function log_failed_login($email, $reason = 'Invalid credentials') {
    log_security_event('LOGIN_FAILURE', "Invalid password for: {$email}", [
        'reason' => $reason,
        'email' => $email
    ]);
}

/**
 * Log Successful Login
 * Convenience function for logging successful logins
 */
function log_successful_login($user_id, $email) {
    log_security_event('LOGIN_SUCCESS', "User ID: {$user_id}, Email: {$email}", [
        'user_id' => $user_id,
        'email' => $email
    ]);
}

/**
 * Check if User is Locked Out
 * Quick check without incrementing counter
 */
function is_locked_out($identifier) {
    if (!isset($_SESSION['login_attempts'][$identifier])) {
        return false;
    }
    
    $attempt_data = $_SESSION['login_attempts'][$identifier];
    return $attempt_data['locked_until'] > time();
}
