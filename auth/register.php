<?php
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/user/dashboard.php');
}

$errors = [];
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
        log_security_event('CSRF_FAILURE', 'Registration with invalid CSRF token');
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $mobile = sanitize($_POST['mobile'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($name)) $errors[] = 'Name is required.';
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (strpos($email, '@') === false) {
            $errors[] = 'Email must contain @ symbol.';
        } else {
            // Additional email validation
            $emailParts = explode('@', $email);
            if (count($emailParts) !== 2) {
                $errors[] = 'Invalid email format.';
            } else {
                $domain = $emailParts[1];
                // Check if domain has a dot
                if (strpos($domain, '.') === false) {
                    $errors[] = 'Email must have a valid domain (e.g., @gmail.com).';
                } else {
                    // Check domain extension length
                    $domainParts = explode('.', $domain);
                    $extension = end($domainParts);
                    if (strlen($extension) < 2) {
                        $errors[] = 'Email must have a valid domain extension.';
                    }
                }
            }
        }
        
        if (empty($mobile)) {
            $errors[] = 'Mobile number is required.';
        } elseif (!preg_match('/^09[0-9]{9}$/', $mobile)) {
            $errors[] = 'Please enter a valid Philippine mobile number (09xxxxxxxxx).';
        }
        if (empty($address)) $errors[] = 'Address is required.';
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        // Check if email already exists
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                if ($existingUser['is_verified'] == 1) {
                    $errors[] = 'Email address is already registered and verified.';
                } else {
                    $errors[] = 'Email address is already registered but not verified. Please check your email for verification link.';
                }
            }
        }
        
        // Check if mobile already exists
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ? AND is_verified = 1");
            $stmt->execute([$mobile]);
            if ($stmt->rowCount() > 0) $errors[] = 'Mobile number is already registered.';
        }
        
        // Register user if no errors
        if (empty($errors)) {
            try {
                $hashedPassword = hashPassword($password);
                
                // Generate verification token
                $verificationToken = bin2hex(random_bytes(32));
                $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Insert user with is_verified = 0
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, mobile, address, role, is_verified, verification_token, token_expiry, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'user', 0, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$name, $email, $hashedPassword, $mobile, $address, $verificationToken, $tokenExpiry])) {
                    $userId = $pdo->lastInsertId();
                    
                    // Send verification email
                    try {
                        $verificationLink = BASE_URL . '/auth/verify_email.php?token=' . $verificationToken;
                        
                        $emailSubject = "Verify Your Email - " . SITE_NAME;
                        $emailBody = getStyledEmailTemplate(
                            'Email Verification Required',
                            "
                            <p>Dear {$name},</p>
                            <p>Thank you for registering with Carmona Online Permit Portal!</p>
                            <p>To complete your registration and activate your account, please verify your email address by clicking the button below:</p>
                            
                            <p style='text-align: center; margin: 2rem 0;'>
                                <a href='{$verificationLink}' class='button'>Verify Email Address</a>
                            </p>
                            
                            <div class='info-box'>
                                <p><strong>Or copy this link:</strong></p>
                                <p style='word-break: break-all; font-size: 0.9rem;'>{$verificationLink}</p>
                            </div>
                            
                            <div class='info-box' style='background: #fff3e0; border-left-color: #FF9800;'>
                                <p><strong>Important:</strong></p>
                                <p>This verification link will expire in 24 hours.</p>
                                <p>If you did not create an account, please ignore this email.</p>
                            </div>
                            ",
                            '#9ACD32'
                        );
                        
                        if (sendEmail($email, $emailSubject, $emailBody)) {
                            logActivity($userId, 'Registration', 'New user registered - verification email sent');
                            $success = "Registration successful! A verification email has been sent to <strong>{$email}</strong>. Please check your inbox (and spam folder) and click the verification link to activate your account. The link expires in 24 hours.";
                            $_POST = []; // Clear form
                        } else {
                            // Email failed, but user is created
                            logActivity($userId, 'Registration', 'New user registered - verification email FAILED');
                            $errors[] = 'Registration completed, but we could not send the verification email. Please contact support at carmonaopp@gmail.com to verify your account manually.';
                        }
                    } catch (Exception $e) {
                        error_log("Verification email failed: " . $e->getMessage());
                        $errors[] = 'Registration successful, but verification email failed to send. Please contact support.';
                    }
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            } catch (Exception $e) {
                error_log("Registration error: " . $e->getMessage());
                $errors[] = 'An error occurred during registration. Please try again.';
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
    <title>Register - <?php echo SITE_NAME; ?></title>
    
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/favicon.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
            max-width: 900px;
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
            padding: 1.75rem 2rem;
            box-shadow: 0 10px 50px rgba(154, 205, 50, 0.15);
            border: 1px solid rgba(154, 205, 50, 0.1);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 0.75rem;
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
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .error-list {
            margin: 0;
            padding-left: 1.25rem;
            list-style: disc;
        }

        .error-list li {
            margin-bottom: 0.25rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            color: #1f2937;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .required {
            color: #f44336;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem 0.9rem;
            font-size: 0.9rem;
            background: #f9fcf7;
            border: 2px solid #e5f0db;
            border-radius: 10px;
            color: #1f2937;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #9ACD32;
            background: white;
            box-shadow: 0 0 0 4px rgba(154, 205, 50, 0.1);
        }

        .form-help {
            display: block;
            margin-top: 0.3rem;
            color: #6b7280;
            font-size: 0.75rem;
        }

        /* Password toggle styles */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 45px;
            width: 100%;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 11px;
            background: none;
            border: none;
            cursor: pointer;
            color: #64748b;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #9ACD32;
        }

        .btn-primary {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #9ACD32, #7CB342);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(154, 205, 50, 0.3);
            margin-top: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(154, 205, 50, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .auth-footer p {
            color: #6b7280;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .text-link {
            color: #9ACD32;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .text-link:hover {
            color: #7CB342;
        }

        .text-link-bold {
            color: #9ACD32;
            text-decoration: none;
            font-weight: 700;
        }

        .text-link-bold:hover {
            color: #7CB342;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-height: 700px) {
            .auth-logo {
                width: 55px;
                height: 55px;
                margin-bottom: 0.5rem;
            }

            .auth-header h1 {
                font-size: 1.3rem;
            }

            .auth-header {
                margin-bottom: 1rem;
            }

            .form-group, .form-row {
                margin-bottom: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
            }

            .auth-card {
                padding: 1.25rem;
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
                width: 45px;
                height: 45px;
                margin-bottom: 0.4rem;
            }

            .auth-header {
                margin-bottom: 0.75rem;
            }

            .auth-header h1 {
                font-size: 1.15rem;
                margin-bottom: 0.15rem;
            }

            .auth-header p {
                font-size: 0.8rem;
            }

            .form-group, .form-row {
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
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="<?php echo BASE_URL; ?>/assets/carmona-logo.png" alt="LGU Logo" class="auth-logo" onerror="this.style.display='none'">
                <h1>Create Account</h1>
                <p>Register to apply for permits and track your applications</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div><?php echo $success; ?></div>
                </div>
                
                <div style="background: linear-gradient(135deg, #e8f5e9, #f1f8f4); border-left: 4px solid #4CAF50; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="color: #2e7d32; font-size: 1.1rem; margin-bottom: 1rem; font-weight: 700;">Next Steps:</h3>
                    <ol style="color: #1b5e20; margin: 0; padding-left: 1.5rem; line-height: 1.8;">
                        <li>Check your email inbox for the verification message</li>
                        <li>If you don't see it, check your <strong>Spam/Junk folder</strong></li>
                        <li>Click the verification link in the email</li>
                        <li>Once verified, you can login to your account</li>
                    </ol>
                    <div style="margin-top: 1rem; padding: 1rem; background: white; border-radius: 8px; border: 1px solid #c8e6c9;">
                        <p style="margin: 0 0 0.5rem 0; color: #2e7d32; font-size: 0.9rem;">
                            <strong>Important:</strong> The verification link expires in 24 hours.
                        </p>
                        <p style="margin: 0; color: #666; font-size: 0.85rem;">
                            <strong>Didn't receive the email?</strong> Contact us at 
                            <a href="mailto:carmonaopp@gmail.com" style="color: #4CAF50; font-weight: 600;">carmonaopp@gmail.com</a>
                        </p>
                    </div>
                </div>
                
                <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 12px; margin-bottom: 1rem;">
                    <p style="margin: 0 0 0.75rem 0; color: #666; font-size: 0.9rem;">Already verified your email?</p>
                    <a href="login.php" style="display: inline-block; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #9ACD32, #7CB342); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: transform 0.2s;">
                        Go to Login →
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="" class="auth-form" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-control"
                            placeholder="Juan Dela Cruz"
                            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control"
                            placeholder="juan@gmail.com"
                            pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                            title="Please enter a valid email address (e.g., name@gmail.com)"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="mobile">Mobile Number <span class="required">*</span></label>
                        <input 
                            type="tel" 
                            id="mobile" 
                            name="mobile" 
                            class="form-control"
                            placeholder="09123456789"
                            pattern="09[0-9]{9}"
                            value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>"
                            required
                        >
                        <small class="form-help">Format: 09xxxxxxxxx</small>
                    </div>

                    <div class="form-group">
                        <label for="address">Address <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="address" 
                            name="address" 
                            class="form-control"
                            placeholder="Complete Address"
                            value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control"
                                placeholder="Minimum 8 characters"
                                minlength="8"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <small class="form-help">At least 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-control"
                                placeholder="Re-enter password"
                                minlength="8"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    Create Account
                </button>
            </form>
            <?php endif; ?>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php" class="text-link-bold">Login here</a></p>
                <p><a href="<?php echo BASE_URL; ?>/index.php" class="text-link">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // PASSWORD VISIBILITY TOGGLE
        // ============================================
        
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            const icon = button.querySelector('i');

            if (field.type === 'password') {
                // Show password - use open eye
                field.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                // Hide password - use closed eye
                field.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // ============================================
            // REAL-TIME VALIDATION FUNCTIONS
            // ============================================
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            if (!field) return;
            
            // Add error class to field
            field.classList.add('error');
            field.style.borderColor = '#ef4444';
            field.style.background = '#fef2f2';
            
            // Remove existing error message if any
            let errorDiv = field.parentElement.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.remove();
            }
            
            // Create and insert error message
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.cssText = 'color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.25rem;';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            field.parentElement.appendChild(errorDiv);
        }
        
        function clearError(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return;
            
            // Remove error class and styling
            field.classList.remove('error');
            field.style.borderColor = '';
            field.style.background = '';
            
            // Remove error message
            const errorDiv = field.parentElement.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        // ============================================
        // REAL-TIME VALIDATION FOR REGISTRATION FORM
        // ============================================

        // Full Name real-time validation
        const nameField = document.getElementById('name');
        if (nameField) {
            nameField.addEventListener('input', function () {
                const value = this.value.trim();

                if (value.length > 0) {
                    if (value.length < 3) {
                        showError('name', 'Must be at least 3 characters long');
                    } else if (value.length > 100) {
                        showError('name', 'Cannot exceed 100 characters');
                    } else if (!/^[a-zA-ZÀ-ÿ\s.\-']+$/.test(value)) {
                        showError('name', 'Only letters, spaces, hyphens, periods allowed');
                    } else {
                        clearError('name');
                    }
                } else {
                    clearError('name');
                }
            });
        }

        // Email real-time validation
        const emailField = document.getElementById('email');
        if (emailField) {
            emailField.addEventListener('input', function () {
                const value = this.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (value.length > 0) {
                    if (!emailRegex.test(value)) {
                        showError('email', 'Please enter a valid email address');
                    } else if (value.length > 255) {
                        showError('email', 'Email address is too long');
                    } else {
                        clearError('email');
                    }
                } else {
                    clearError('email');
                }
            });
        }

        // Mobile Number real-time validation
        const mobileField = document.getElementById('mobile');
        if (mobileField) {
            mobileField.addEventListener('input', function () {
                const value = this.value.trim();

                if (value.length > 0) {
                    const cleanPhone = value.replace(/[\s\-]/g, '');
                    const phoneRegex = /^09[0-9]{9}$/;

                    if (!phoneRegex.test(cleanPhone)) {
                        showError('mobile', 'Format: 09XXXXXXXXX (11 digits)');
                    } else {
                        clearError('mobile');
                    }
                } else {
                    clearError('mobile');
                }
            });
        }

        // Address real-time validation
        const addressField = document.getElementById('address');
        if (addressField) {
            addressField.addEventListener('input', function () {
                const value = this.value.trim();

                if (value.length > 0) {
                    if (value.length < 5) {
                        showError('address', 'Address must be at least 5 characters');
                    } else if (value.length > 500) {
                        showError('address', 'Address is too long');
                    } else {
                        clearError('address');
                    }
                } else {
                    clearError('address');
                }
            });
        }

        // Password real-time validation
        const passwordField = document.getElementById('password');
        if (passwordField) {
            passwordField.addEventListener('input', function () {
                const value = this.value;

                if (value.length > 0) {
                    if (value.length < 8) {
                        showError('password', 'Must be at least 8 characters');
                    } else {
                        const hasUppercase = /[A-Z]/.test(value);
                        const hasLowercase = /[a-z]/.test(value);
                        const hasNumber = /\d/.test(value);
                        const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/`~;']/.test(value);

                        if (!hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
                            showError('password', 'Need: uppercase, lowercase, number, special char');
                        } else {
                            clearError('password');
                        }
                    }
                } else {
                    clearError('password');
                }
            });
        }

        // Confirm Password real-time validation
        const confirmPasswordField = document.getElementById('confirm_password');
        if (confirmPasswordField) {
            confirmPasswordField.addEventListener('input', function () {
                const password = document.getElementById('password').value;
                const confirmPassword = this.value;

                if (confirmPassword.length > 0) {
                    if (password !== confirmPassword) {
                        showError('confirm_password', 'Passwords do not match');
                    } else {
                        clearError('confirm_password');
                    }
                } else {
                    clearError('confirm_password');
                }
            });
        }

        // Form submission validation
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            let hasErrors = false;

            // Validate all fields on submit
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const mobile = document.getElementById('mobile').value.trim();
            const address = document.getElementById('address').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!name || name.length < 3) {
                showError('name', 'Full name is required (min 3 characters)');
                hasErrors = true;
            }

            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('email', 'Valid email address is required');
                hasErrors = true;
            }

            if (!mobile || !/^09[0-9]{9}$/.test(mobile.replace(/[\s\-]/g, ''))) {
                showError('mobile', 'Valid mobile number is required (09XXXXXXXXX)');
                hasErrors = true;
            }

            if (!address || address.length < 5) {
                showError('address', 'Address is required (min 5 characters)');
                hasErrors = true;
            }

            if (!password || password.length < 8) {
                showError('password', 'Password is required (min 8 characters)');
                hasErrors = true;
            }

            if (password !== confirmPassword) {
                showError('confirm_password', 'Passwords do not match');
                hasErrors = true;
            }

            if (hasErrors) {
                e.preventDefault();
                return false;
            }
        });
    }); // End DOMContentLoaded
    </script>
</body>
</html>