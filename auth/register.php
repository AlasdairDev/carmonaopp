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
    if (empty($name)) $errors[] = 'Name is required.';
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
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) $errors[] = 'Email address is already registered.';
    }
    
    // Check if mobile already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ?");
        $stmt->execute([$mobile]);
        if ($stmt->rowCount() > 0) $errors[] = 'Mobile number is already registered.';
    }
    
    // Register user if no errors
    if (empty($errors)) {
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, mobile, address, role) VALUES (?, ?, ?, ?, ?, 'user')");
        
        if ($stmt->execute([$name, $email, $hashedPassword, $mobile, $address])) {
            $success = 'Registration successful! You can now login.';
            logActivity($pdo->lastInsertId(), 'Registration', 'New user registered');
            $_POST = [];
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
            color: #ef4444;
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

        .form-help {
            display: block;
            margin-top: 0.3rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #4b5563;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .checkbox-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #9ACD32;
        }

.btn-primary {
    display: block; 
    width: 280px; 
    margin: 1.5rem auto 0; 
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

        .text-link, .text-link-bold {
            color: #9ACD32;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease;
        }

        .text-link:hover, .text-link-bold:hover {
            color: #7BA428;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-container {
                max-width: 500px;
            }

            .auth-card {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .form-row .form-group {
                margin-bottom: 1rem;
            }

            .form-row .form-group:last-child {
                margin-bottom: 0;
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
                    <div><?php echo $success; ?></div>
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
                            placeholder="juan@example.com"
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

                <button type="submit" class="btn-primary">
                    Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php" class="text-link-bold">Login here</a></p>
                <p><a href="<?php echo BASE_URL; ?>/index.php" class="text-link">← Back to Home</a></p>
            </div>
        </div>
    </div>

    <script>
        // Password match validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                document.getElementById('confirm_password').focus();
                return false;
            }
        });
    </script>
</body>
</html>