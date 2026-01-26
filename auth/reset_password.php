<?php
require_once '../config.php';  // FIXED: Correct path from /auth/
require_once '../includes/functions.php';  // FIXED: Correct path
require_once '../includes/security.php';  // FIXED: Correct path

$error = '';
$success = '';
$token = isset($_GET['token']) ? sanitize_input(trim($_GET['token'])) : '';

if (empty($token)) {
    $error = 'Invalid or missing reset token';
} else {
    // Verify token
    $stmt = $pdo->prepare("
        SELECT pr.*, u.id as user_id, u.email, u.name
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        $error = 'Invalid or expired reset token. Please request a new password reset.';
        log_security_event('PASSWORD_RESET_INVALID', "Invalid/expired token used");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
        log_security_event('CSRF_FAILURE', 'Password reset with invalid CSRF token');
    } else {
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        if (empty($password)) {
            $error = 'Password is required';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long';
        } else {
            $strength_check = validate_password_strength($password);
            if (!$strength_check['valid']) {
                $error = implode('<br>', $strength_check['errors']);
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match';
            } else {
                try {
                    $pdo->beginTransaction();
                    
                    // Use secure password hashing
                    $hashed_password = hash_password($password);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $reset['user_id']]);
                    
                    // Mark token as used
                    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                    $stmt->execute([$token]);
                    
                    // Log activity
                    $stmt = $pdo->prepare("
                        INSERT INTO activity_logs (user_id, action, description, ip_address, created_at)
                        VALUES (?, 'password_reset', 'Password was reset', ?, NOW())
                    ");
                    $stmt->execute([$reset['user_id'], $_SERVER['REMOTE_ADDR']]);
                    
                    $pdo->commit();
                    
                    log_security_event('PASSWORD_RESET_SUCCESS', "User ID: {$reset['user_id']}, Email: {$reset['email']}");
                    
                    $success = 'Your password has been reset successfully. You can now login with your new password.';
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'An error occurred. Please try again.';
                    log_security_event('PASSWORD_RESET_ERROR', "User ID: {$reset['user_id']}, Error: " . $e->getMessage());
                }
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generate_csrf_token();

$pageTitle = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.auth-page {
            background: linear-gradient(135deg, #f8fef5 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .auth-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 1rem;
        }

        .auth-box {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 10px 50px rgba(154, 205, 50, 0.2);
            border: 1px solid rgba(154, 205, 50, 0.15);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header i {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #9ACD32, #7CB342);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 8px 24px rgba(154, 205, 50, 0.35);
        }

        .auth-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert i {
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .auth-form .form-group {
            margin-bottom: 1.5rem;
        }

        .auth-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #1f2937;
            font-weight: 600;
        }

        .auth-form label i {
            margin-right: 0.5rem;
            color: #9ACD32;
        }

        .password-input {
            position: relative;
        }

        .auth-form input {
            width: 100%;
            padding: 0.875rem 1rem;
            padding-right: 3rem;
            font-size: 0.95rem;
            background: #f9fcf7;
            border: 2px solid #e5f0db;
            border-radius: 12px;
            color: #1f2937;
            transition: all 0.3s ease;
        }

        .auth-form input:focus {
            outline: none;
            border-color: #9ACD32;
            background: white;
            box-shadow: 0 0 0 4px rgba(154, 205, 50, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: #9ACD32;
        }

        .form-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #6b7280;
        }

        .password-strength {
            margin: 15px 0;
        }

        .strength-bar {
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background 0.3s;
            border-radius: 3px;
        }

        .strength-text {
            font-size: 13px;
            font-weight: 500;
            margin: 0;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #9ACD32, #7CB342);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(154, 205, 50, 0.3);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(154, 205, 50, 0.4);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .auth-footer p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .auth-footer a {
            color: #9ACD32;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            color: #7BA428;
        }

        .auth-footer i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <i class="fas fa-key"></i>
                <h1>Reset Your Password</h1>
                <p>Enter your new password below.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo $error; ?></div>
                </div>
                <?php if (strpos($error, 'expired') !== false || strpos($error, 'Invalid') !== false): ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="forgot_password.php" class="btn">
                            <i class="fas fa-redo"></i> Request New Reset Link
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo htmlspecialchars($success); ?></div>
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="login.php" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Login Now
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!$error && !$success && isset($reset) && $reset): ?>
                <form method="POST" action="" class="auth-form" id="resetForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> New Password
                        </label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Enter new password"
                                minlength="8"
                                required
                                autofocus
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-text">Must be 8+ characters with uppercase, lowercase, number, and special character</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirm New Password
                        </label>
                        <div class="password-input">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="Confirm new password"
                                minlength="8"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <p class="strength-text" id="strengthText"></p>
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-check"></i> Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-footer">
                <p>
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const button = input.parentElement.querySelector('.toggle-password');
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    const passwordInput = document.getElementById('password');
    const strengthIndicator = document.getElementById('passwordStrength');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                strengthIndicator.style.display = 'none';
                return;
            }
            
            strengthIndicator.style.display = 'block';
            
            let strength = 0;
            let messages = [];
            
            if (password.length >= 8) strength += 20;
            else messages.push('8+ chars');
            
            if (password.length >= 12) strength += 10;
            
            if (/[A-Z]/.test(password)) strength += 20;
            else messages.push('uppercase');
            
            if (/[a-z]/.test(password)) strength += 20;
            else messages.push('lowercase');
            
            if (/\d/.test(password)) strength += 15;
            else messages.push('number');
            
            if (/[^a-zA-Z0-9]/.test(password)) strength += 15;
            else messages.push('special char');
            
            strengthFill.style.width = strength + '%';
            
            if (strength < 50) {
                strengthFill.style.background = '#e74c3c';
                strengthText.textContent = 'Weak - needs: ' + messages.join(', ');
                strengthText.style.color = '#e74c3c';
            } else if (strength < 80) {
                strengthFill.style.background = '#f39c12';
                strengthText.textContent = 'Medium - add: ' + messages.join(', ');
                strengthText.style.color = '#f39c12';
            } else {
                strengthFill.style.background = '#27ae60';
                strengthText.textContent = 'Strong password! ✓';
                strengthText.style.color = '#27ae60';
            }
        });
    }

    const form = document.getElementById('resetForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
            
            if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || 
                !/\d/.test(password) || !/[^a-zA-Z0-9]/.test(password)) {
                e.preventDefault();
                alert('Password must contain uppercase, lowercase, number, and special character!');
                return false;
            }
        });
    }
    </script>
</body>
</html>