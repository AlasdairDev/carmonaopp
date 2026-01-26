<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $mobile = sanitizeInput($_POST['mobile']);
        $address = sanitizeInput($_POST['address']);
        
        // Validate inputs
        if (empty($name) || empty($email)) {
            throw new Exception('Name and email are required fields.');
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }
        
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('Email address is already in use by another account.');
        }
        
        // Update user profile
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, address = ?, updated_at = NOW() WHERE id = ?");
        
        if ($stmt->execute([$name, $email, $mobile, $address, $user_id])) {
            // Update session
            $_SESSION['user_name'] = $name;
            
            $success_message = 'Profile updated successfully!';
            logActivity($user_id, 'Update Profile', 'Updated profile information');
            
            // Redirect to prevent form resubmission
            header('Location: profile.php?updated=1');
            exit;
        } else {
            throw new Exception('Failed to update profile. Please try again.');
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Profile update error for user $user_id: " . $e->getMessage());
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    try {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception('All password fields are required.');
        }
        
        // Get current password hash
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
        
        if (!$user_data) {
            throw new Exception('User not found.');
        }
        
        // Verify current password
        if (!password_verify($current_password, $user_data['password'])) {
            throw new Exception('Current password is incorrect.');
        }
        
        // Check if new passwords match
        if ($new_password !== $confirm_password) {
            throw new Exception('New passwords do not match.');
        }
        
        // Check password length
        if (strlen($new_password) < 8) {
            throw new Exception('New password must be at least 8 characters long.');
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        
        if ($stmt->execute([$hashed_password, $user_id])) {
            $success_message = 'Password changed successfully!';
            logActivity($user_id, 'Change Password', 'Changed account password');
            
            // Redirect to prevent form resubmission
            header('Location: profile.php?password_changed=1');
            exit;
        } else {
            throw new Exception('Failed to change password. Please try again.');
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Password change error for user $user_id: " . $e->getMessage());
    }
}

// Check for redirect messages
if (isset($_GET['updated'])) {
    $success_message = 'Profile updated successfully!';
}
if (isset($_GET['password_changed'])) {
    $success_message = 'Password changed successfully!';
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die('User not found');
    }
} catch (Exception $e) {
    die('Error loading user data: ' . $e->getMessage());
}

// Get user statistics
try {
    $stats = [
        'total_apps' => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn() ?: 0,
        'apps_today' => $pdo->query("SELECT COUNT(*) FROM applications WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0,
        'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?: 0,
        'account_age' => floor((time() - strtotime($user['created_at'])) / 86400)
    ];
} catch (Exception $e) {
    $stats = [
        'total_apps' => 0,
        'apps_today' => 0,
        'total_users' => 0,
        'account_age' => 0
    ];
}

// Get recent activity
try {
    $recent_activity = $pdo->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $recent_activity->execute([$user_id]);
    $activities = $recent_activity->fetchAll();
} catch (Exception $e) {
    $activities = [];
}

$pageTitle = 'My Profile';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #8bc34a;
            --primary-dark: #689f38;
            --primary-light: #dcedc8;
            --secondary: #558b2f;
            --background: #f5f7fa;
            --surface: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius: 12px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem 1.5rem 1.5rem;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--radius);
            padding: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .header-left p {
            font-size: 1rem;
            opacity: 0.95;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            border: 3px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
            border-color: var(--primary);
        }

        .stat-icon-wrapper {
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon-primary {
            background: linear-gradient(135deg, #8bc34a 0%, #689f38 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        .stat-icon-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .stat-icon-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .stat-icon-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }

        .stat-content h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header i {
            color: var(--primary);
        }

        /* Form */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--background);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 195, 74, 0.1);
            background: white;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideInDown 0.4s ease;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Activity Timeline */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            position: relative;
            padding-left: 2.5rem;
            padding-bottom: 1.5rem;
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: 0.625rem;
            top: 2rem;
            width: 2px;
            height: calc(100% - 2rem);
            background: var(--border);
        }

        .activity-item:last-child::before {
            display: none;
        }

        .activity-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 0.875rem;
        }

        .activity-content {
            background: var(--background);
            padding: 1rem;
            border-radius: var(--radius);
        }

        .activity-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .activity-time {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Info Display */
        .info-display {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            padding: 1rem;
            background: var(--background);
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.375rem;
        }

        .info-value {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Role Badge */
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            background: #ffebee;
            color: #c62828;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem 1rem 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header-content {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }

            .info-display {
                grid-template-columns: 1fr;
            }

            .profile-avatar {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 80px;
            left: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 9999;
            animation: slideInLeft 0.4s ease, fadeOut 0.4s ease 2.6s;
            min-width: 300px;
            max-width: 500px;
        }

        .toast-success {
            border-left: 4px solid #22c55e;
        }

        .toast-error {
            border-left: 4px solid #ef4444;
        }

        .toast-icon {
            font-size: 1.5rem;
        }

        .toast-message {
            flex: 1;
            font-weight: 600;
            color: var(--text-primary);
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(-400px);
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <h1><i class="fas fa-user-circle"></i> My Profile</h1>
                <p>Manage your account settings and preferences</p>
            </div>
            <div class="header-right">
                <div class="profile-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div style="color: white;">
                    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem;">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </div>
                    <span class="role-badge" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="fas fa-shield-alt"></i> Administrator
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Total Applications</h3>
                <div class="stat-value"><?php echo number_format($stats['total_apps']); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-info">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Today's Applications</h3>
                <div class="stat-value"><?php echo number_format($stats['apps_today']); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-success">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Total Users</h3>
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper">
                <div class="stat-icon stat-icon-warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-content">
                <h3>Account Age</h3>
                <div class="stat-value"><?php echo $stats['account_age']; ?> <span style="font-size: 1rem;">days</span></div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Left Column -->
        <div>
            <!-- Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-user"></i>
                        Profile Information
                    </h2>
                </div>

                <form method="POST" action="">
                    <div class="info-display">
                        <div class="info-item">
                            <div class="info-label">User ID</div>
                            <div class="info-value">#<?php echo $user['id']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Role</div>
                            <div class="info-value">
                                <span class="role-badge">
                                    <i class="fas fa-shield-alt"></i> Administrator
                                </span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Member Since</div>
                            <div class="info-value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value"><?php echo date('F d, Y h:i A', strtotime($user['updated_at'] ?? $user['created_at'])); ?></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="tel" name="mobile" class="form-control" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" placeholder="09123456789">
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" placeholder="Enter your complete address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-lock"></i>
                        Change Password
                    </h2>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Current Password *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                            Minimum 8 characters
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="8">
                    </div>

                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-history"></i>
                        Recent Activity
                    </h2>
                </div>

                <?php if (empty($activities)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        <i class="fas fa-clock" style="font-size: 2rem; opacity: 0.3; margin-bottom: 0.5rem;"></i>
                        <p>No recent activity</p>
                    </div>
                <?php else: ?>
                    <ul class="activity-list">
                        <?php foreach ($activities as $activity): ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </div>
                                <div class="activity-time">
                                    <?php 
                                    $time = strtotime($activity['created_at']);
                                    $diff = time() - $time;
                                    if ($diff < 60) {
                                        echo 'Just now';
                                    } elseif ($diff < 3600) {
                                        echo floor($diff / 60) . ' minutes ago';
                                    } elseif ($diff < 86400) {
                                        echo floor($diff / 3600) . ' hours ago';
                                    } else {
                                        echo date('M d, Y \a\t h:i A', $time);
                                    }
                                    ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <div style="margin-top: 1rem;">
                        <a href="activity_logs.php" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                            <i class="fas fa-list"></i> View All Activity
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Account Security -->
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-shield-alt"></i>
                        Account Security
                    </h2>
                </div>

                <div style="padding: 1rem; background: var(--background); border-radius: 8px; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <i class="fas fa-check-circle" style="color: #22c55e; font-size: 1.5rem;"></i>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);">Email Verified</div>
                            <div style="font-size: 0.8125rem; color: var(--text-secondary);">Your email address is verified</div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <i class="fas fa-shield-alt" style="color: #3b82f6; font-size: 1.5rem;"></i>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);">Admin Access</div>
                            <div style="font-size: 0.8125rem; color: var(--text-secondary);">Full system privileges enabled</div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <i class="fas fa-lock" style="color: #fbbf24; font-size: 1.5rem;"></i>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);">Password Strength</div>
                            <div style="font-size: 0.8125rem; color: var(--text-secondary);">Last changed: <?php echo date('M d, Y', strtotime($user['updated_at'] ?? $user['created_at'])); ?></div>
                        </div>
                    </div>
                </div>

                <div style="padding: 1rem; background: #fff3cd; border-radius: 8px; border-left: 3px solid #fbbf24;">
                    <div style="display: flex; align-items: start; gap: 0.75rem;">
                        <i class="fas fa-exclamation-triangle" style="color: #f59e0b; margin-top: 0.25rem;"></i>
                        <div style="font-size: 0.8125rem; color: #92400e;">
                            <strong>Security Tip:</strong> Change your password regularly and never share your admin credentials with anyone.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const icon = type === 'success' ? '✓' : '✕';
    const iconColor = type === 'success' ? '#22c55e' : '#ef4444';
    
    toast.innerHTML = `
        <div class="toast-icon" style="color: ${iconColor};">${icon}</div>
        <div class="toast-message">${message}</div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Show toast on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($success_message): ?>
        showToast('<?php echo addslashes($success_message); ?>', 'success');
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        showToast('<?php echo addslashes($error_message); ?>', 'error');
    <?php endif; ?>
});
</script>

</body>
</html>

<?php include '../includes/footer.php'; ?>