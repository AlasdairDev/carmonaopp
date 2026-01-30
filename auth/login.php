<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Redirect if already logged in 
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(BASE_URL . '/admin/dashboard.php');
    } else {
        redirect(BASE_URL . '/user/dashboard.php');
    }
}

$error = '';
$success = '';

// Check for logout success message
if (isset($_SESSION['logout_success'])) {
    $success = 'You have been logged out successfully.';
    unset($_SESSION['logout_success']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
        log_security_event('CSRF_FAILURE', 'Login attempt with invalid CSRF token');
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? ''; // Don't sanitize password

        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            // Check rate limiting
            $rate_check = check_rate_limit($email);

            if (!$rate_check['allowed']) {
                $error = "Too many login attempts. Please try again in " . $rate_check['time_remaining'] . " minutes.";
                log_security_event('RATE_LIMIT_EXCEEDED', "Email: $email");
            } else {
                // Query user with RBAC fields - UPDATED
                $stmt = $pdo->prepare("
                    SELECT id, name, email, password, role, department_id, is_active, is_verified 
                    FROM users WHERE email = ?
                ");
                $stmt->execute([$email]);

                if ($stmt->rowCount() === 1) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Check if account is active
                    if (!$user['is_active']) {
                        $error = 'Your account has been deactivated. Please contact the administrator.';
                        log_security_event('LOGIN_FAILURE', "Inactive account: $email");
                    }
                    // NEW: Check if email is verified
                    elseif (isset($user['is_verified']) && $user['is_verified'] == 0) {
                        $error = 'Please verify your email address before logging in. Check your inbox for the verification link.';
                        log_security_event('LOGIN_FAILURE', "Unverified email: $email");
                    }
                    // Verify password
                    elseif (verify_password($password, $user['password'])) {
                        // Login successful
                        regenerate_session();

                        // UPDATED: Store RBAC session data
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['department_id'] = $user['department_id']; // NEW: Store department_id
                        $_SESSION['last_activity'] = time();

                        // Clear login attempts on success
                        if (isset($_SESSION['login_attempts'][$email])) {
                            unset($_SESSION['login_attempts'][$email]);
                        }

                        logActivity($user['id'], 'Login', 'User logged in');
                        log_successful_login($user['id'], $email);

                        // UPDATED: Redirect based on role (all admin types go to admin dashboard)
                        if (isAdmin()) {
                            header('Location: ' . BASE_URL . '/admin/dashboard.php');
                            exit();
                        } else {
                            header('Location: ' . BASE_URL . '/user/dashboard.php');
                            exit();
                        }
                    } else {
                        $error = 'Invalid email or password.';
                        log_failed_login($email, 'Invalid password');
                    }
                } else {
                    $error = 'Invalid email or password.';
                    log_security_event('LOGIN_FAILURE', "User not found: $email");
                }
            }
        }
    }
}

// Generate CSRF token
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

        html,
        body {
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

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 8px 32px rgba(154, 205, 50, 0.4);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 12px 40px rgba(154, 205, 50, 0.6);
            }
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
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
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
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #9ACD32;
            margin: 0;
            padding: 0;
            vertical-align: middle;
            position: relative;
            top: 0;
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
            display: block !important;
            width: 280px !important;
            margin: 0 auto !important;
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

        /* Mobile Optimizations */
        @media (max-height: 700px) {
            .auth-logo {
                width: 60px;
                height: 60px;
                margin-bottom: 0.75rem;
            }

            .auth-header h1 {
                font-size: 1.3rem;
            }

            .auth-header {
                margin-bottom: 1rem;
            }

            .form-group {
                margin-bottom: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
            }

            .auth-card {
                padding: 1.5rem 1.25rem;
            }

            .auth-logo {
                width: 70px;
                height: 70px;
            }

            .auth-header h1 {
                font-size: 1.35rem;
            }
        }

        /* Landscape phone */
        @media (max-height: 500px) {
            .auth-container {
                max-height: calc(100vh - 1rem);
            }

            .auth-card {
                padding: 1rem;
            }

            .auth-logo {
                width: 50px;
                height: 50px;
                margin-bottom: 0.5rem;
            }

            .auth-header {
                margin-bottom: 0.75rem;
            }

            .auth-header h1 {
                font-size: 1.2rem;
                margin-bottom: 0.15rem;
            }

            .auth-header p {
                font-size: 0.8rem;
            }

            .form-group {
                margin-bottom: 0.7rem;
            }

            .form-control {
                padding: 0.6rem 0.8rem;
            }

            .auth-footer {
                margin-top: 0.75rem;
                padding-top: 0.75rem;
            }
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            transition: color 0.3s ease;
            z-index: 10;
        }

        .toggle-password:hover {
            color: #9ACD32;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
        }
    </style>
</head>

<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="<?php echo BASE_URL; ?>/assets/carmona-logo.png" alt="LGU Logo" class="auth-logo"
                    onerror="this.style.display='none'">
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Sign in to your account</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                        placeholder="your.email@example.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                </path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group-flex">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="forgot_password.php" class="text-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary">
                    Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php" class="text-link-bold">Register here</a></p>
                <p><a href="<?php echo BASE_URL; ?>/index.php" class="text-link">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle eye icon - FIXED LOGIC
            if (type === 'password') {
                // Password is hidden, show eye with slash (currently hidden)
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                // Password is visible, show regular eye (currently visible)
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</body>

</html>