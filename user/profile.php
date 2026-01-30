<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
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
$stats = [
    'total_apps' => $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?"),
    'pending_apps' => $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND status = 'pending'"),
    'approved_apps' => $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ? AND status IN ('approved', 'paid', 'completed')"),
    'account_age' => floor((time() - strtotime($user['created_at'])) / 86400)
];

$stats['total_apps']->execute([$user_id]);
$total_apps = $stats['total_apps']->fetchColumn() ?: 0;

$stats['pending_apps']->execute([$user_id]);
$pending_apps = $stats['pending_apps']->fetchColumn() ?: 0;

$stats['approved_apps']->execute([$user_id]);
$approved_apps = $stats['approved_apps']->fetchColumn() ?: 0;

$account_age = $stats['account_age'];

// Get recent activity
$recent_activity = $pdo->prepare("
    SELECT al.*, u.name, u.email 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    WHERE al.user_id = ?
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$recent_activity->execute([$user_id]);
$activities = $recent_activity->fetchAll();

$pageTitle = 'My Profile';
include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-responsive.css">
<style>
    .wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
        min-height: calc(100vh - 40px);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        padding: 4rem 2rem;
    }

    .page-wrapper {
        position: relative;
        z-index: 2;
        padding: 0;
    }

    body {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        min-height: 100vh;
        box-sizing: border-box;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .dashboard-banner {
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        border-radius: 30px;
        padding: 3rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
        margin: 0 0 2rem 0;
    }

    .dashboard-banner h1 {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
    }

    .dashboard-banner p {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1.1rem;
        margin: 0.5rem 0 0 0;
    }

    /* Card Styling */
    .card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .card-header {
        padding: 2rem 2.5rem;
        border-bottom: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
        color: white;
    }

    .card-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: white;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-body {
        padding: 1rem 2rem;
    }

    /* Form Styling */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0rem 0.75rem;;
        /* Reduced from 1.5rem */
        margin-bottom: 0rem;
        /* Reduced from 2rem */
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.1rem;
        font-size: 0.95rem;
    }

    .required {
        color: #ef4444;
    }

    .input-wrapper {
        position: relative;
    }


    input[type="text"],
    input[type="email"],
    input[type="password"],
    textarea {
        width: 100%;
        padding: 0.65rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.2s;
        background: white;
        padding-right: 3rem !important;
    }

    textarea {
        resize: vertical;
        min-height: 100px;
    }

    input:focus,
    textarea:focus {
        outline: none;
        border-color: #7cb342;
        box-shadow: 0 0 0 3px rgba(124, 179, 66, 0.1);
    }

    input.error,
    textarea.error {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .error-message {
        display: none;
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        font-weight: 500;
        position: relative;
    }

    .form-help {
        display: block;
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    /* Password Toggle */
    .toggle-password {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 0.5rem;
        display: flex !important;
        /* Force display */
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
        z-index: 10;
        width: 32px;
        height: 32px;
    }

    .toggle-password:hover {
        color: #7cb342;
    }

    .toggle-password i {
        font-size: 1rem;
        /* Make icon visible */
        display: block;
    }

    /* Add space for password toggle button */
    input[type="password"] {
        padding-right: 3rem !important;
    }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.75rem;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        font-size: 1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #7fb842, #6a9c35);
        color: white;
        box-shadow: 0 4px 12px rgba(127, 184, 66, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(127, 184, 66, 0.4);
    }

    .btn-warning {
        background: linear-gradient(135deg, #FF9800, #F57C00);
        color: white;
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(255, 152, 0, 0.4);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        padding-top: 1rem;
        /* Less space before button */
        margin-top: 0;
        border-top: 1px solid #e2e8f0;
    }

    /* Activity Section */
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item:hover {
        background: #f8fafc;
    }

    .activity-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #7cb342, #9ccc65);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .activity-icon i {
        font-size: 0.75rem;
    }

    .activity-content {
        flex: 1;
    }

    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 0.25rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .activity-action {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .activity-time {
        font-size: 0.875rem;
        color: #94a3b8;
    }

    .activity-description {
        color: #64748b;
        font-size: 0.9rem;
        margin: 0;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 1rem;
        display: block;
    }

    .empty-state p {
        font-size: 1rem;
        margin: 0;
    }

    /* Toast Notifications */
    .toast-notification {
        position: fixed;
        top: 100px;
        left: 20px;
        background: white;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 10000;
        min-width: 300px;
        max-width: 400px;
        animation: slideInLeft 0.3s ease;
    }

    @keyframes slideInLeft {
        from {
            transform: translateX(-100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .toast-success {
        border-left: 4px solid #22c55e;
    }

    .toast-error {
        border-left: 4px solid #ef4444;
    }

    .toast-icon {
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toast-message {
        flex: 1;
        color: #1e293b;
        font-weight: 500;
        font-size: 0.95rem;
    }

    /* Content Grid Layout */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    @media (min-width: 1024px) {
        .content-grid {
            grid-template-columns: 2fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .container {
            padding: 0 1rem;
        }

        .wrapper {
            padding: 2rem 1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .dashboard-banner h1 {
            font-size: 2rem;
        }
    }

    textarea {
        resize: vertical;
        min-height: 100px;
        padding: 0.875rem 1rem !important;
        /* Override the left padding */
    }

    textarea+.input-icon {
        display: none;
    }
</style>

<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">

            <div class="dashboard-banner">
                <h1>My Profile</h1>
                <p>Manage your account settings and preferences</p>
            </div>

            <div class="content-grid">
                <!-- Left Column - Forms -->
                <div>
                    <!-- Profile Information Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-user-circle"></i> Profile Information</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" onsubmit="return handleProfileSubmit(event)">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="name">Full Name <span class="required">*</span></label>
                                        <div class="input-wrapper">
                                            <input type="text" id="name" name="name"
                                                value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                            <span class="error-message" id="name-error"></span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email Address <span class="required">*</span></label>
                                        <div class="input-wrapper">
                                            <input type="email" id="email" name="email"
                                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            <span class="error-message" id="email-error"></span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="mobile">Mobile Number</label>
                                        <div class="input-wrapper">
                                            <input type="text" id="mobile" name="mobile"
                                                value="<?php echo htmlspecialchars($user['mobile']); ?>"
                                                placeholder="09XXXXXXXXX">
                                            <span class="error-message" id="mobile-error"></span>
                                        </div>
                                    </div>

                                    <div class="form-group full-width">
                                        <label for="address">Address</label>
                                        <div class="input-wrapper">
                                            <textarea id="address" name="address"
                                                rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                            <span class="error-message" id="address-error"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-lock"></i> Change Password</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" onsubmit="return handlePasswordSubmit(event)">
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="currentPassword">Current Password <span
                                                class="required">*</span></label>
                                        <div class="input-wrapper">
                                            <input type="password" id="currentPassword" name="current_password"
                                                required>
                                            <button type="button" class="toggle-password"
                                                onclick="togglePassword('currentPassword', this)" tabindex="-1">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group full-width">
                                        <label for="newPassword">New Password <span class="required">*</span></label>
                                        <div class="input-wrapper">
                                            <input type="password" id="newPassword" name="new_password" required>
                                            <button type="button" class="toggle-password"
                                                onclick="togglePassword('newPassword', this)" tabindex="-1">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                        <span class="error-message" id="newPassword-error"></span>
                                        <small class="form-help">Must be at least 8 characters with uppercase,
                                            lowercase,
                                            number, and special character</small>
                                    </div>

                                    <div class="form-group full-width">
                                        <label for="confirmNewPassword">Confirm New Password <span
                                                class="required">*</span></label>
                                        <div class="input-wrapper">
                                            <input type="password" id="confirmNewPassword" name="confirm_password"
                                                required>
                                            <button type="button" class="toggle-password"
                                                onclick="togglePassword('confirmNewPassword', this)" tabindex="-1">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                        <span class="error-message" id="confirmNewPassword-error"></span>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="change_password" class="btn btn-warning">
                                        <i class="fas fa-shield-alt"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Activity -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-history"></i> Recent Activity</h2>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <?php if (count($activities) > 0): ?>
                                <div class="activity-list">
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-circle"></i>
                                            </div>
                                            <div class="activity-content">
                                                <div class="activity-header">
                                                    <span
                                                        class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></span>
                                                    <span
                                                        class="activity-time"><?php echo formatDate($activity['created_at']); ?></span>
                                                </div>
                                                <p class="activity-description">
                                                    <?php echo htmlspecialchars($activity['description']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>No recent activity</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // ============================================
    // ERROR HANDLING FUNCTIONS
    // ============================================

    function showError(fieldId, message) {
        const errorElement = document.getElementById(`${fieldId}-error`);
        const inputElement = document.getElementById(fieldId);

        if (errorElement && inputElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            inputElement.classList.add('error');
        }
    }

    function clearError(fieldId) {
        const errorElement = document.getElementById(`${fieldId}-error`);
        const inputElement = document.getElementById(fieldId);

        if (errorElement && inputElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
            inputElement.classList.remove('error');
        }
    }

    // ============================================
    // PROFILE FORM VALIDATION
    // ============================================

    // Real-time validation for name
    const nameField = document.getElementById('name');
    if (nameField) {
        nameField.addEventListener('input', function () {
            const value = this.value.trim();

            if (value.length > 0) {
                if (value.length < 3) {
                    showError('name', 'Name must be at least 3 characters');
                } else if (value.length > 100) {
                    showError('name', 'Name is too long (max 100 characters)');
                } else if (!/^[a-zA-ZÀ-ÿ\s.\-']+$/.test(value)) {
                    showError('name', 'Name can only contain letters, spaces, dots, hyphens, and apostrophes');
                } else {
                    clearError('name');
                }
            } else {
                clearError('name');
            }
        });
    }

    // Real-time validation for mobile
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

    // Real-time validation for address
    const addressField = document.getElementById('address');
    if (addressField) {
        addressField.addEventListener('input', function () {
            const value = this.value.trim();

            if (value.length > 500) {
                showError('address', 'Address is too long (max 500 characters)');
            } else {
                clearError('address');
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
        console.log('🚀 USER PROFILE FORM SUBMITTING');

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

    // Show toast notifications on page load
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
    // AJAX Profile form submission
    function handleProfileSubmit(event) {
        event.preventDefault(); // Always prevent default for AJAX

        console.log('🚀 USER PROFILE FORM SUBMITTING');

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
            showToast(errors[0], 'error');
            return false;
        }

        // Submit via AJAX
        const form = event.target;
        const formData = new FormData(form);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(html => {
                // Check if update was successful by looking for success message in response
                if (html.includes('Profile updated successfully')) {
                    showToast('Profile updated successfully!', 'success');
                } else if (html.includes('Email address is already in use')) {
                    showToast('Email address is already in use by another account.', 'error');
                } else {
                    showToast('Profile updated successfully!', 'success');
                }
            })
            .catch(error => {
                showToast('Error updating profile', 'error');
                console.error('Error:', error);
            });

        return false;
    }

    // AJAX Password form submission
    function handlePasswordSubmit(event) {
        event.preventDefault(); // Always prevent default for AJAX

        console.log('🔐 PASSWORD FORM SUBMITTING');

        const currentPass = document.getElementById('currentPassword').value;
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmNewPassword').value;

        // Validate new password
        if (newPass.length < 8) {
            showError('newPassword', 'Must be at least 8 characters');
            showToast('Password must be at least 8 characters', 'error');
            return false;
        }

        const hasUppercase = /[A-Z]/.test(newPass);
        const hasLowercase = /[a-z]/.test(newPass);
        const hasNumber = /\d/.test(newPass);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/`~;']/.test(newPass);

        if (!hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
            showError('newPassword', 'Need: uppercase, lowercase, number, special char');
            showToast('Password must contain uppercase, lowercase, number, and special character', 'error');
            return false;
        }

        // Validate password match
        if (newPass !== confirmPass) {
            showError('confirmNewPassword', 'Passwords do not match');
            showToast('Passwords do not match', 'error');
            return false;
        }

        // Submit via AJAX
        const form = event.target;
        const formData = new FormData(form);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(html => {
                // Check response for success/error messages
                if (html.includes('Password changed successfully')) {
                    showToast('Password changed successfully!', 'success');
                    // Clear the password fields
                    form.reset();
                } else if (html.includes('Current password is incorrect')) {
                    showToast('Current password is incorrect', 'error');
                } else if (html.includes('New passwords do not match')) {
                    showToast('New passwords do not match', 'error');
                } else {
                    showToast('Password changed successfully!', 'success');
                    form.reset();
                }
            })
            .catch(error => {
                showToast('Error changing password', 'error');
                console.error('Error:', error);
            });

        return false;
    }
</script>

<?php include '../includes/footer.php'; ?>