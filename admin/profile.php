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

        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);

        if ($stmt->rowCount() > 0) {
            $error_message = 'Email address is already in use by another account.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, address = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $email, $mobile, $address, $user_id]);

            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            $success_message = 'Profile updated successfully!';
            logActivity($user_id, 'Update Profile', 'Updated profile information');
        }
    } catch (Exception $e) {
        $error_message = 'Error updating profile: ' . $e->getMessage();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    try {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Get current password hash
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!password_verify($current_password, $user['password'])) {
            $error_message = 'Current password is incorrect.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match.';
        } elseif (strlen($new_password) < 8) {
            $error_message = 'New password must be at least 8 characters long.';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);

            $success_message = 'Password changed successfully!';
            logActivity($user_id, 'Change Password', 'Changed account password');
        }
    } catch (Exception $e) {
        $error_message = 'Error changing password: ' . $e->getMessage();
    }
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get user statistics
if (isDepartmentAdmin()) {
    $dept_id = $_SESSION['department_id'];

    // Total applications for department
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE department_id = ?");
    $stmt->execute([$dept_id]);
    $total_apps = $stmt->fetchColumn() ?: 0;

    // Today's applications for department
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE department_id = ? AND DATE(created_at) = CURDATE()");
    $stmt->execute([$dept_id]);
    $apps_today = $stmt->fetchColumn() ?: 0;

    // Total users (all departments)
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?: 0;

    $stats = [
        'total_apps' => $total_apps,
        'apps_today' => $apps_today,
        'total_users' => $total_users,
        'account_age' => floor((time() - strtotime($user['created_at'])) / 86400)
    ];
} else {
    // Super Admin - show all stats
    $stats = [
        'total_apps' => $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn() ?: 0,
        'apps_today' => $pdo->query("SELECT COUNT(*) FROM applications WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0,
        'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?: 0,
        'account_age' => floor((time() - strtotime($user['created_at'])) / 86400)
    ];
}

// Get recent activity
if (isDepartmentAdmin()) {
    $dept_id = $_SESSION['department_id'];
    $recent_activity = $pdo->prepare("
        SELECT al.*, u.name, u.email 
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        WHERE (al.department_id = ? OR al.related_department_id = ?)
        ORDER BY al.created_at DESC 
        LIMIT 10
    ");
    $recent_activity->execute([$dept_id, $dept_id]);
} else {
    // Super admin sees all recent activity
    $recent_activity = $pdo->prepare("
        SELECT al.*, u.name, u.email 
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC 
        LIMIT 10
    ");
    $recent_activity->execute();
}
$activities = $recent_activity->fetchAll();

$pageTitle = 'My Profile';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../assets/css/admin/profile_styles.css">
    <link rel="stylesheet" href="../assets/css/admin-responsive.css">

</head>

<body>

    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <h1>My Profile</h1>
                    <p>Manage your account settings and preferences</p>
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
                    <div class="stat-value"><?php echo $stats['account_age']; ?> <span
                            style="font-size: 1rem;">days</span></div>
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

                    <!-- id and onsubmit handler -->
                    <form method="POST" action="" id="profileForm" onsubmit="return handleProfileSubmit(event);">
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
                                <div class="info-value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Last Updated</div>
                                <div class="info-value">
                                    <?php echo date('F d, Y h:i A', strtotime($user['updated_at'] ?? $user['created_at'])); ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" class="form-control"
                                value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" class="form-control"
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="tel" name="mobile" class="form-control"
                                value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>"
                                placeholder="09123456789">
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control"
                                placeholder="Enter your complete address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
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

                    <!-- id and onsubmit handler -->
                    <form method="POST" action="" id="passwordForm" onsubmit="return handlePasswordSubmit(event);">
                        <div class="form-group">
                            <label>Current Password *</label>
                            <div class="password-wrapper">
                                <input type="password" name="current_password" id="currentPassword" class="form-control"
                                    required>
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('currentPassword', this)">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>New Password *</label>
                            <div class="password-wrapper">
                                <input type="password" id="newPassword" name="new_password" class="form-control"
                                    required minlength="8">
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('newPassword', this)">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                            <div class="error-message" id="newPasswordError"></div>
                            <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                                Must be 8+ chars with uppercase, lowercase, number, special char
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password *</label>
                            <div class="password-wrapper">
                                <input type="password" id="confirmNewPassword" name="confirm_password"
                                    class="form-control" required minlength="8">
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('confirmNewPassword', this)">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                            <div class="error-message" id="confirmNewPasswordError"></div>
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
                                            <?php
                                            if (isDepartmentAdmin() || !isAdmin()) {
                                                echo '<strong>' . htmlspecialchars($activity['name'] ?? 'System') . ':</strong> ';
                                            }
                                            echo htmlspecialchars($activity['description']);
                                            ?>
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
                            <a href="activity_logs.php" class="btn btn-secondary"
                                style="width: 100%; justify-content: center;">
                                <i class="fas fa-list"></i> View All Activity
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- inline form submission handlers -->
    <script>
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId) || document.querySelector(`[name="${fieldId}"]`);
            const errorDiv = document.getElementById(fieldId + 'Error');

            if (field) {
                field.classList.add('error');
                field.classList.remove('success');
            }

            if (errorDiv) {
                errorDiv.textContent = message;
                errorDiv.classList.add('show');
            }
        }

        function clearError(fieldId) {
            const field = document.getElementById(fieldId) || document.querySelector(`[name="${fieldId}"]`);
            const errorDiv = document.getElementById(fieldId + 'Error');

            if (field) {
                field.classList.remove('error');
                field.classList.add('success');
            }

            if (errorDiv) {
                errorDiv.classList.remove('show');
            }
        }

        // ============================================
        // REAL-TIME VALIDATION FOR PROFILE FORM
        // ============================================

        // Name validation
        const nameField = document.querySelector('input[name="name"]');
        if (nameField) {
            nameField.addEventListener('input', function () {
                const value = this.value.trim();
                if (value.length > 0 && value.length < 3) {
                    this.classList.add('error');
                    this.classList.remove('success');
                } else if (value.length > 100) {
                    this.classList.add('error');
                    this.classList.remove('success');
                } else if (value.length > 0 && !/^[a-zA-ZÀ-ÿ\s.\-']+$/.test(value)) {
                    this.classList.add('error');
                    this.classList.remove('success');
                } else if (value.length >= 3) {
                    this.classList.remove('error');
                    this.classList.add('success');
                }
            });
        }

        // Email validation with real-time feedback
        const emailField = document.querySelector('input[name="email"]');
        if (emailField) {
            // Create error message div if it doesn't exist
            if (!document.getElementById('emailError')) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'emailError';
                errorDiv.className = 'error-message';
                emailField.parentNode.appendChild(errorDiv);
            }

            emailField.addEventListener('input', function () {
                const value = this.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (value.length > 0 && !emailRegex.test(value)) {
                    showError('email', 'Please enter a valid email address');
                } else if (value.length > 255) {
                    showError('email', 'Email address is too long');
                } else if (value.length > 0 && emailRegex.test(value)) {
                    clearError('email');
                }
            });
        }

        // Mobile validation with real-time feedback
        const mobileField = document.querySelector('input[name="mobile"]');
        if (mobileField) {
            // Create error message div if it doesn't exist
            if (!document.getElementById('mobileError')) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'mobileError';
                errorDiv.className = 'error-message';
                mobileField.parentNode.appendChild(errorDiv);
            }

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

        // ============================================
        // PASSWORD FORM VALIDATION
        // ============================================

        // Real-time validation for new password
        const newPasswordField = document.getElementById('newPassword');
        if (newPasswordField) {
            newPasswordField.addEventListener('input', function () {
                const value = this.value;

                if (value.length > 0) {
                    if (value.length < 8) {
                        showError('newPassword', 'Must be at least 8 characters');
                    } else {
                        const hasUppercase = /[A-Z]/.test(value);
                        const hasLowercase = /[a-z]/.test(value);
                        const hasNumber = /\d/.test(value);
                        const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/`~;']/.test(value);

                        if (!hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
                            showError('newPassword', 'Need: uppercase, lowercase, number, special char');
                        } else {
                            clearError('newPassword');
                        }
                    }
                }
            });
        }

        // Real-time validation for confirm password
        const confirmNewPasswordField = document.getElementById('confirmNewPassword');
        if (confirmNewPasswordField) {
            confirmNewPasswordField.addEventListener('input', function () {
                const password = document.getElementById('newPassword').value;
                const confirmPassword = this.value;

                if (confirmPassword.length > 0) {
                    if (password !== confirmPassword) {
                        showError('confirmNewPassword', 'Passwords do not match');
                    } else {
                        clearError('confirmNewPassword');
                    }
                }
            });
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;

            let icon;
            if (type === 'success') {
                icon = '<i class="fas fa-check-circle"></i>';
            } else if (type === 'error') {
                icon = '<i class="fas fa-exclamation-circle"></i>';
            }

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

        // ============================================
        // FORM SUBMISSION HANDLERS
        // ============================================

        // Profile form submission with validation
        function handleProfileSubmit(event) {
            console.log('🚀 ADMIN PROFILE FORM SUBMITTING');

            const name = document.querySelector('input[name="name"]').value.trim();
            const email = document.querySelector('input[name="email"]').value.trim();
            const mobile = document.querySelector('input[name="mobile"]').value.trim();

            const errors = [];

            // Name validation
            if (name.length < 3) {
                errors.push('Name must be at least 3 characters');
            } else if (name.length > 100) {
                errors.push('Name is too long');
            } else if (!/^[a-zA-ZÀ-ÿ\s.\-']+$/.test(name)) {
                errors.push('Name contains invalid characters');
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('email', 'Please enter a valid email address');
                errors.push('Invalid email address');
            } else if (email.length > 255) {
                showError('email', 'Email address is too long');
                errors.push('Email is too long');
            }

            // Mobile validation (optional field)
            if (mobile) {
                const cleanPhone = mobile.replace(/[\s\-]/g, '');
                const phoneRegex = /^09[0-9]{9}$/;

                if (!phoneRegex.test(cleanPhone)) {
                    showError('mobile', 'Format: 09XXXXXXXXX (11 digits)');
                    errors.push('Invalid mobile number format');
                }
            }

            if (errors.length > 0) {
                event.preventDefault();
                showToast(errors[0], 'error');
                return false;
            }

            return true;
        }

        // Password form submission with validation
        function handlePasswordSubmit(event) {
            console.log('🔐 PASSWORD FORM SUBMITTING');

            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmNewPassword').value;

            // Validate new password
            if (newPass.length < 8) {
                event.preventDefault();
                showError('newPassword', 'Must be at least 8 characters');
                showToast('Password must be at least 8 characters', 'error');
                return false;
            }

            const hasUppercase = /[A-Z]/.test(newPass);
            const hasLowercase = /[a-z]/.test(newPass);
            const hasNumber = /\d/.test(newPass);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/`~;']/.test(newPass);

            if (!hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
                event.preventDefault();
                showError('newPassword', 'Need: uppercase, lowercase, number, special char');
                showToast('Password must contain uppercase, lowercase, number, and special character', 'error');
                return false;
            }

            // Validate password match
            if (newPass !== confirmPass) {
                event.preventDefault();
                showError('confirmNewPassword', 'Passwords do not match');
                showToast('Passwords do not match', 'error');
                return false;
            }

            // All validation passed - allow form to submit
            console.log('✅ Password validation passed - form will submit');
            return true;
        }

        // Show notifications on page load
        document.addEventListener('DOMContentLoaded', function () {
            <?php if ($success_message): ?>
                showToast('<?php echo addslashes($success_message); ?>', 'success');
            <?php endif; ?>

            <?php if ($error_message): ?>
                showToast('<?php echo addslashes($error_message); ?>', 'error');
            <?php endif; ?>
        });
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            const icon = button.querySelector('i');

            if (field.type === 'password') {
                // Show password
                field.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                // Hide password
                field.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>

</html>

<?php include '../includes/footer.php'; ?>
