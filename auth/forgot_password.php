<?php
require_once '../config.php';  // FIXED: Correct path from /auth/
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

// ✨ ADD THIS SECTION - Automatic cleanup of expired/used tokens
try {
    $stmt = $pdo->prepare("CALL cleanup_expired_password_resets()");
    $stmt->execute();
    // Optionally log how many were deleted
    $result = $stmt->fetch();
    $stmt->closeCursor();
    if ($result && $result['deleted_count'] > 0) {
        error_log("Cleaned up {$result['deleted_count']} expired password reset tokens");
    }
} catch (Exception $e) {
    // Don't let cleanup errors stop the reset process
    error_log("Password reset cleanup error: " . $e->getMessage());
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
        log_security_event('CSRF_FAILURE', 'Forgot password with invalid CSRF token');
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format';
        } else {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = generate_secure_token(32);
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Delete old tokens for this user
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                
                // Store token in database
                $stmt = $pdo->prepare("
                    INSERT INTO password_resets (user_id, token, expires_at, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$user['id'], $token, $expires]);
                
                // Send reset email
                try {
                    $reset_link = BASE_URL . '/auth/reset_password.php?token=' . $token;
                    
                    $email_subject = "Password Reset Request - " . SITE_NAME;
                    $email_body = "
                        <h2>Password Reset Request</h2>
                        <p>Hello {$user['name']},</p>
                        <p>We received a request to reset your password. Click the link below:</p>
                        <p><a href='{$reset_link}' style='display: inline-block; padding: 12px 24px; background: #9ACD32; color: white; text-decoration: none; border-radius: 8px;'>Reset Password</a></p>
                        <p>Or copy this link: {$reset_link}</p>
                        <p><strong>This link expires in 1 hour.</strong></p>
                        <p>If you didn't request this, ignore this email.</p>
                    ";
                    
                    sendEmail($user['email'], $email_subject, $email_body);
                    
                    log_security_event('PASSWORD_RESET_REQUEST', "Email: {$email}");
                    
                    $success = 'Password reset instructions have been sent to your email address.';
                } catch (Exception $e) {
                    error_log("Failed to send reset email: " . $e->getMessage());
                    $error = 'Failed to send email. Please try again or contact support.';
                }
            } else {
                // Don't reveal if email exists (security)
                log_security_event('PASSWORD_RESET_ATTEMPT', "Non-existent email: {$email}");
                $success = 'If an account exists with this email, reset instructions have been sent.';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
$pageTitle = 'Forgot Password';
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

        .auth-card {
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

        .auth-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #9ACD32, #7CB342);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #1f2937;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            background: #f9fcf7;
            border: 2px solid #e5f0db;
            border-radius: 12px;
            color: #1f2937;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #9ACD32;
            background: white;
            box-shadow: 0 0 0 4px rgba(154, 205, 50, 0.1);
        }

        .btn-primary {
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
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(154, 205, 50, 0.4);
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
            margin-bottom: 0.75rem;
        }

        .text-link {
            color: #9ACD32;
            text-decoration: none;
            font-weight: 600;
        }

        .text-link:hover {
            color: #7BA428;
        }

        .info-box {
            background: linear-gradient(135deg, rgba(154, 205, 50, 0.08), rgba(181, 229, 80, 0.05));
            border-radius: 12px;
            border-left: 4px solid #9ACD32;
            padding: 1rem;
            margin-top: 1.5rem;
        }

        .info-box p {
            color: #4b5563;
            font-size: 0.85rem;
            margin: 0;
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">🔑</div>
                <h1>Forgot Password?</h1>
                <p>Enter your email to receive reset instructions</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" action="">
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
                            autofocus
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Send Reset Instructions
                    </button>
                </form>

                <div class="info-box">
                    <p>💡 <strong>Tip:</strong> Check your spam folder if you don't see the email within 5 minutes.</p>
                </div>
            <?php endif; ?>

            <div class="auth-footer">
                <p>Remember your password? <a href="login.php" class="text-link">Back to Login</a></p>
                <p><a href="<?php echo BASE_URL; ?>/index.php" class="text-link">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>