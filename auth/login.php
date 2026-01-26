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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
        log_security_event('CSRF_FAILURE', 'Login attempt with invalid CSRF token');
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
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
                        regenerate_session();
                        
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['last_activity'] = time();
                        
                        if (isset($_SESSION['login_attempts'][$email])) {
                            unset($_SESSION['login_attempts'][$email]);
                        }
                        
                        logActivity($user['id'], 'Login', 'User logged in');
                        log_security_event('LOGIN_SUCCESS', "User ID: {$user['id']}, Email: $email");
                        
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

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/favicon.png">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100vh;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8fef5 0%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            padding: 0.5rem;
        }

        .auth-container::-webkit-scrollbar {
            width: 6px;
        }

        .auth-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .auth-container::-webkit-scrollbar-thumb {
            background: #9ACD32;
            border-radius: 3px;
        }

        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: 0 10px 50px rgba(154, 205, 50, 0.15);
            border: 1px solid rgba(154, 205, 50, 0.1);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            display: block;
            object-fit: contain;
        }

        .auth-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .auth-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .alert {
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.85rem;
            animation: slideDown 0.3s ease;
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
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            color: #1f2937;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem 0.9rem;
            font-size: 0.9rem;
            background: #f9fcf7;
            border: 2px solid #e5f0db;
            border-radius: 10px;
            color: #1f2937;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #9ACD32;
            background: white;
            box-shadow: 0 0 0 3px rgba(154, 205, 50, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-group-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            font-size: 0.8rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: #4b5563;
            cursor: pointer;
            font-weight: 500;
        }

        .checkbox-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #9ACD32;
        }

        .text-link {
            color: #9ACD32;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .text-link:hover {
            color: #7BA428;
        }

        .btn-primary {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #9ACD32, #7CB342);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(154, 205, 50, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(154, 205, 50, 0.35);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid #e5e7eb;
        }

        .auth-footer p {
            color: #6b7280;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
        }

        .text-link-bold {
            color: #9ACD32;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease;
        }

        .text-link-bold:hover {
            color: #7BA428;
        }

        .demo-credentials {
            margin-top: 1rem;
            padding: 0.75rem;
            background: linear-gradient(135deg, rgba(154, 205, 50, 0.08), rgba(181, 229, 80, 0.05));
            border-radius: 10px;
            border-left: 3px solid #9ACD32;
        }

        .demo-title {
            color: #1f2937;
            font-weight: 700;
            margin-bottom: 0.3rem;
            font-size: 0.8rem;
        }

        .demo-account {
            color: #4b5563;
            font-size: 0.8rem;
            line-height: 1.3;
        }

        .demo-account strong {
            color: #9ACD32;
            font-weight: 700;
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

            .demo-credentials {
                margin-top: 0.75rem;
                padding: 0.6rem;
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

            .demo-credentials {
                margin-top: 0.5rem;
                padding: 0.5rem;
            }

            .auth-footer {
                margin-top: 0.75rem;
                padding-top: 0.75rem;
            }
        }
    </style>
</head>
<body>
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
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
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

                <button type="submit" class="btn-primary">
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
</body>
</html>