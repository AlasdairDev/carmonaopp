<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/security.php'; // NEW: Add security functions

// Redirect if already logged in 
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(BASE_URL . '/admin/dashboard.php');
    } else {
        redirect(BASE_URL . '/user/dashboard.php');
    }
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // NEW: Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
        log_security_event('CSRF_FAILURE', 'Login attempt with invalid CSRF token');
    } else {
        $email = sanitize_input($_POST['email'] ?? ''); // NEW: Use sanitize_input
        $password = $_POST['password'] ?? ''; // Don't sanitize password
        
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            // NEW: Check rate limiting
            $rate_check = check_rate_limit($email);

            if (!$rate_check['allowed']) {
                $error = "Too many login attempts. Please try again in " . $rate_check['time_remaining'] . " minutes.";
                log_security_event('RATE_LIMIT_EXCEEDED', "Email: $email");
            } else {
                $stmt = $pdo->prepare("SELECT id, name, email, password, role, is_active FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() === 1) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$user['is_active']) {
                        $error = 'Your account has been deactivated. Please contact the administrator.';
                        log_security_event('LOGIN_FAILURE', "Inactive account: $email");
                    } elseif (verify_password($password, $user['password'])) {
                        // Login successful
                        regenerate_session();
                        
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['last_activity'] = time();
                        
                        // Clear login attempts on success
                        if (isset($_SESSION['login_attempts'][$email])) {
                            unset($_SESSION['login_attempts'][$email]);
                        }
                        
                        logActivity($user['id'], 'Login', 'User logged in');
                        log_security_event('LOGIN_SUCCESS', "User ID: {$user['id']}, Email: $email");
                        
                        // Redirect based on role
                        if ($user['role'] === 'admin') {
                            header('Location: ' . BASE_URL . '/admin/dashboard.php');
                            exit();
                        } else {
                            header('Location: ' . BASE_URL . '/user/dashboard.php');
                            exit();
                        }
                    } else {
                        $error = 'Invalid email or password.';
                        log_security_event('LOGIN_FAILURE', "Invalid password for: $email");
                    }
                } else {
                    $error = 'Invalid email or password.';
                    log_security_event('LOGIN_FAILURE', "User not found: $email");
                }
            }
        }
    }
}

// NEW: Generate CSRF token
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/favicon.png">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body.auth-page {
            background: linear-gradient(135deg, #f8fef5 0%, #ffffff 100%);
            margin: 0;
            padding: 1rem 0;
        }

        body.auth-page::before,
        body.auth-page::after {
            display: none !important;
        }

        .auth-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 500px;
            padding: 1rem;
            margin: 0 auto;
        }

        .auth-card {
            background: white;
            border-radius: 24px;
            padding: 2rem 1.75rem 1.75rem 1.75rem;
            box-shadow: 0 10px 50px rgba(154, 205, 50, 0.2);
            border: 1px solid rgba(154, 205, 50, 0.15);
            margin: 0;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-logo {
    width: 130px;
    height: 130px;
    margin: 0 auto 1.25rem;
    display: block;
    object-fit: contain;
}

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 8px 32px rgba(154, 205, 50, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 12px 40px rgba(154, 205, 50, 0.6); }
        }

        .auth-header h1 {
            font-size: 1.65rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
            line-height: 1.3;
        }

        .auth-header p {
            color: #6b7280;
            font-size: 1rem;
            font-weight: 400;
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            animation: slideDown 0.3s ease;
            font-size: 0.9rem;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .auth-form .form-group {
            margin-bottom: 1.25rem;
        }

        .auth-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #1f2937;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .auth-form .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            background: #f9fcf7;
            border: 2px solid #e5f0db;
            border-radius: 12px;
            color: #1f2937;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .auth-form .form-control:focus {
            outline: none;
            border-color: #9ACD32;
            background: white;
            box-shadow: 0 0 0 4px rgba(154, 205, 50, 0.1);
            transform: translateY(-2px);
        }

        .auth-form .form-control::placeholder {
            color: #9ca3af;
        }

        .form-group-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #4b5563;
            cursor: pointer;
            font-weight: 500;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #9ACD32;
        }

        .text-link {
            color: #9ACD32;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .text-link:hover {
            color: #7BA428;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #9ACD32, #7CB342) !important;
            color: white !important;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(154, 205, 50, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(154, 205, 50, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.75rem;
            border-top: 1px solid #e5e7eb;
        }

        .auth-footer p {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .text-link-bold {
            color: #9ACD32;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .text-link-bold:hover {
            color: #7BA428;
        }

        .demo-credentials {
            margin-top: 1.25rem;
            margin-bottom: 0;
            padding: 0.875rem;
            background: linear-gradient(135deg, rgba(154, 205, 50, 0.08), rgba(181, 229, 80, 0.05));
            border-radius: 12px;
            border-left: 4px solid #9ACD32;
        }

        .demo-title {
            color: #1f2937;
            font-weight: 700;
            margin-bottom: 0.4rem;
            font-size: 0.875rem;
        }

        .demo-account {
            color: #4b5563;
            font-size: 0.85rem;
            padding: 0;
            margin: 0;
            line-height: 1.4;
        }

        .demo-account strong {
            color: #9ACD32;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            body.auth-page {
                padding: 0.5rem 0;
            }

            .auth-container {
                padding: 0.5rem;
                margin: 0 auto;
            }

            .auth-card {
                padding: 1.75rem 1.5rem 1.5rem 1.5rem;
                border-radius: 20px;
            }

            .auth-header h1 {
                font-size: 1.4rem;
            }

            .auth-logo {
                width: 75px;
                height: 75px;
            }
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="<?php echo BASE_URL; ?>/assets/carmona-logo.png" alt="LGU Logo" class="auth-logo" onerror="this.style.display='none'">
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Sign in to your account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <!-- NEW: CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control"
                        placeholder="your.email@example.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="form-group-flex">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="forgot_password.php" class="text-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php" class="text-link-bold">Register here</a></p>
                <p><a href="<?php echo BASE_URL; ?>/index.php" class="text-link">← Back to Home</a></p>
            </div>

            <div class="demo-credentials">
                <p class="demo-title">🔐 After running password reset, use:</p>
                <div class="demo-account">
                    <strong>Password for all accounts:</strong> TempPass123!
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>