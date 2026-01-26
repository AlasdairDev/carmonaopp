<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/user/dashboard.php');
}

$errors = [];
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($mobile)) {
        $errors[] = 'Mobile number is required.';
    } elseif (!preg_match('/^09[0-9]{9}$/', $mobile)) {
        $errors[] = 'Please enter a valid Philippine mobile number (09xxxxxxxxx).';
    }
    
    if (empty($address)) {
        $errors[] = 'Address is required.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $errors[] = 'Email address is already registered.';
        }
    }
    
    // Check if mobile already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ?");
        $stmt->execute([$mobile]);
        
        if ($stmt->rowCount() > 0) {
            $errors[] = 'Mobile number is already registered.';
        }
    }

    
    // Register user if no errors
    if (empty($errors)) {
        $hashedPassword = hashPassword($password);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, mobile, address, role) VALUES (?, ?, ?, ?, ?, 'user')");
        
        if ($stmt->execute([$name, $email, $hashedPassword, $mobile, $address])) {
            $success = 'Registration successful! You can now login.';
            logActivity($pdo->lastInsertId(), 'Registration', 'New user registered');
            
            // Clear form data
            $_POST = [];
            
            // Redirect after 2 seconds
            header("refresh:2;url=login.php");
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/favicon.png">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        
        body.auth-page {
            padding: 0.5rem 0 !important;
        }

        .auth-container,
        .login-container {
            min-height: auto !important;
            padding: 0.5rem !important;
            max-width: 100% !important;
        }

        .auth-card,
        .login-card {
            max-width: 1400px !important;
            width: 95% !important;
            margin: 0 auto !important;
            padding: 1.5rem 5rem !important;
            border-radius: 24px !important;
        }

        /* Logo */
        .auth-header {
            margin-bottom: 1.5rem !important;
            text-align: center !important;
            display: block !important;
        }

        .auth-logo {
    width: 130px !important;
    height: 130px !important;
    margin: 0 auto -10px !important;
    float: none !important;
    display: block !important;
    object-fit: contain !important;
}

        .auth-header h1 {
            font-size: 2rem !important;
            margin-bottom: 0.25rem !important;
            margin-top: 0 !important;
            text-align: center !important;
        }

        .auth-header p {
            font-size: 0.95rem !important;
            margin-bottom: 0 !important;
            text-align: center !important;
            clear: none !important;
        }

        /* Form Layout -  */
        .form-row {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 3rem !important;
            margin-bottom: 1rem !important;
        }

        .auth-form .form-group,
        .form-group {
            margin-bottom: 1rem !important;
        }

        .form-row .form-group {
            margin-bottom: 0 !important;
        }

        /* Labels -  */
        .auth-form label,
        .form-group label {
            margin-bottom: 0.4rem !important;
            font-size: 0.85rem !important;
        }

        /* Inputs -  */
        .auth-form .form-control,
        .form-control,
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            padding: 0.7rem 1rem !important;
            font-size: 0.9rem !important;
            border-radius: 12px !important;
        }

        .form-help {
            margin-top: 0.3rem !important;
            font-size: 0.75rem !important;
        }

        /* Checkbox */
        .checkbox-label {
            font-size: 0.85rem !important;
            margin-top: 0.5rem !important;
        }

        .checkbox-label input[type="checkbox"] {
            width: 16px !important;
            height: 16px !important;
        }

        /* Button */
        .btn-primary,
        button[type="submit"],
        .btn-submit {
            padding: 0.75rem !important;
            font-size: 0.95rem !important;
            margin-top: 0.5rem !important;
            border-radius: 12px !important;
        }

        /* Footer */
        .auth-footer {
            margin-top: 1.25rem !important;
            padding-top: 1.25rem !important;
        }

        .auth-footer p {
            font-size: 0.85rem !important;
            margin-bottom: 0.4rem !important;
        }

        /* Alert */
        .alert {
            padding: 0.75rem 1rem !important;
            margin-bottom: 1rem !important;
            font-size: 0.85rem !important;
        }

        .alert-icon {
            width: 18px !important;
            height: 18px !important;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .auth-card,
            .login-card {
                padding: 1.5rem !important;
            }

            .form-row {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
        }
    </style>
</head>
<body class="auth-page">
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
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="registerForm">
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
                            placeholder="juan.delacruz@example.com"
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
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control"
                            placeholder="Minimum 8 characters"
                            minlength="8"
                            required
                        >
                        <small class="form-help">At least 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control"
                            placeholder="Re-enter password"
                            minlength="8"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" required>
                        I agree to the Terms and Conditions and Privacy Policy
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php" class="text-link-bold">Login here</a></p>
                <p><a href="<?php echo BASE_URL; ?>/index.php" class="text-link">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    <script>
        // Password match validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>
</html>