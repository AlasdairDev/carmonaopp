<?php
require_once '../config.php';
require_once '../includes/functions.php';

$message = '';
$messageType = 'error';
$verificationSuccess = false;

// Handle verification
if (isset($_GET['token'])) {
    $token = sanitize($_GET['token']);
    
    if (empty($token)) {
        $message = 'Invalid verification link.';
    } else {
        try {
            // Find user with this token
            $stmt = $pdo->prepare("
                SELECT id, name, email, is_verified, token_expiry 
                FROM users 
                WHERE verification_token = ? 
                LIMIT 1
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $message = 'Invalid or expired verification link. The link may have already been used.';
            } elseif ($user['is_verified'] == 1) {
                $message = 'Your email has already been verified. You can login now.';
                $messageType = 'info';
            } elseif (strtotime($user['token_expiry']) < time()) {
                $message = 'This verification link has expired. Please register again or request a new verification link.';
            } else {
                // Verify the user
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET is_verified = 1, 
                        verification_token = NULL, 
                        token_expiry = NULL,
                        email_verified_at = NOW()
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$user['id']])) {
                    $verificationSuccess = true;
                    $messageType = 'success';
                    $message = 'Your email has been verified successfully! You can now login to your account.';
                    
                    // Log activity
                    logActivity($user['id'], 'Email Verification', 'User verified their email address');
                    
                    // Send welcome email
                    try {
                        $welcomeSubject = "Welcome to " . SITE_NAME;
                        $welcomeBody = getStyledEmailTemplate(
                            'Welcome to Carmona Online Permit Portal!',
                            "
                            <p>Dear {$user['name']},</p>
                            <p>Your email has been successfully verified! Welcome to Carmona Online Permit Portal.</p>
                            
                            <div class='info-box'>
                                <p><strong>What's Next?</strong></p>
                                <p>✓ Login to your account</p>
                                <p>✓ Complete your profile</p>
                                <p>✓ Start applying for permits</p>
                                <p>✓ Track your applications</p>
                            </div>
                            
                            <p style='text-align: center; margin: 2rem 0;'>
                                <a href='" . BASE_URL . "/auth/login.php' class='button'>Login to Your Account</a>
                            </p>
                            
                            <div class='info-box' style='background: #e8f5e9;'>
                                <p><strong>Need Help?</strong></p>
                                <p>If you have any questions or need assistance, feel free to contact our support team.</p>
                            </div>
                            ",
                            '#4CAF50'
                        );
                        
                        sendEmail($user['email'], $welcomeSubject, $welcomeBody);
                    } catch (Exception $e) {
                        error_log("Welcome email failed: " . $e->getMessage());
                    }
                } else {
                    $message = 'An error occurred during verification. Please try again or contact support.';
                }
            }
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            $message = 'An error occurred during verification. Please try again or contact support.';
        }
    }
} else {
    $message = 'No verification token provided.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/favicon.png">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f8fef5 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .verification-container {
            max-width: 600px;
            width: 100%;
        }

        .verification-card {
            background: white;
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 10px 50px rgba(154, 205, 50, 0.2);
            border: 1px solid rgba(154, 205, 50, 0.15);
            text-align: center;
        }

        .verification-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .verification-icon.success {
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
        }

        .verification-icon.error {
            background: linear-gradient(135deg, #f44336, #e57373);
        }

        .verification-icon.info {
            background: linear-gradient(135deg, #2196F3, #64B5F6);
        }

        h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .message {
            font-size: 1.1rem;
            color: #4b5563;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #9ACD32, #7CB342);
            color: white;
            box-shadow: 0 4px 15px rgba(154, 205, 50, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(154, 205, 50, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #9ACD32;
            border: 2px solid #9ACD32;
        }

        .btn-secondary:hover {
            background: #f8fef5;
            transform: translateY(-2px);
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #9ACD32;
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 12px;
            text-align: left;
        }

        .info-box p {
            margin: 0.5rem 0;
            color: #4b5563;
        }

        .info-box strong {
            color: #1f2937;
        }

        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }

            .verification-card {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .verification-icon {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        .countdown {
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 1rem;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(154, 205, 50, 0.3);
            border-radius: 50%;
            border-top-color: #9ACD32;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <div class="verification-icon <?php echo $messageType; ?>">
                <?php if ($verificationSuccess): ?>
                    ✓
                <?php elseif ($messageType === 'info'): ?>
                    ℹ️
                <?php else: ?>
                    ✗
                <?php endif; ?>
            </div>

            <h1>
                <?php if ($verificationSuccess): ?>
                    Email Verified!
                <?php elseif ($messageType === 'info'): ?>
                    Already Verified
                <?php else: ?>
                    Verification Failed
                <?php endif; ?>
            </h1>

            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>

            <?php if ($verificationSuccess): ?>
                <div class="info-box">
                    <p><strong>✓ Your account is now active!</strong></p>
                    <p>You can now login and start using all features of the Carmona Online Permit Portal.</p>
                </div>

                <div class="button-group">
                    <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn btn-primary">
                        Login to Your Account
                    </a>
                </div>

                <p class="countdown">Redirecting to login in <span id="countdown">5</span> seconds...</p>

                <script>
                    let seconds = 5;
                    const countdownElement = document.getElementById('countdown');
                    
                    const interval = setInterval(() => {
                        seconds--;
                        countdownElement.textContent = seconds;
                        
                        if (seconds <= 0) {
                            clearInterval(interval);
                            window.location.href = '<?php echo BASE_URL; ?>/auth/login.php';
                        }
                    }, 1000);
                </script>
            <?php elseif ($messageType === 'info'): ?>
                <div class="button-group">
                    <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn btn-primary">
                        Go to Login
                    </a>
                </div>
            <?php else: ?>
                <div class="info-box">
                    <p><strong>What can you do?</strong></p>
                    <p>• Try registering again with your email</p>
                    <p>• Contact support if you continue experiencing issues</p>
                    <p>• Check if you clicked the correct verification link from your email</p>
                </div>

                <div class="button-group">
                    <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-primary">
                        Register Again
                    </a>
                    <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-secondary">
                        Back to Home
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
