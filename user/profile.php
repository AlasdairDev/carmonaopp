<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if(!isLoggedIn() || $_SESSION['role'] !== 'user') {
    redirect('auth/login.php');
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Handle profile update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $full_name = sanitizeInput($_POST['name']);
        $mobile_number = sanitizeInput($_POST['mobile']);
        $address = sanitizeInput($_POST['address']);
        
        // Validate inputs
        if(empty($full_name) || empty($mobile_number) || empty($address)) {
            throw new Exception('All fields are required');
        }
        
        // Validate mobile format
        if(!preg_match('/^09[0-9]{9}$/', $mobile_number)) {
            throw new Exception('Invalid mobile number format. Use 09XXXXXXXXX');
        }
        
        $query = "UPDATE users SET name = ?, mobile = ?, address = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($query);
        
        if($stmt->execute([$full_name, $mobile_number, $address, $user_id])) {
            $_SESSION['success'] = 'Profile updated successfully';
            
            // Update session name if changed
            $_SESSION['user_name'] = $full_name;
            
            // Refresh user data
            $user = getUserById($user_id);
            
            // Log activity
            logActivity($user_id, 'profile_update', 'User updated their profile', [
                'name' => $full_name,
                'mobile' => $mobile_number
            ]);
            
            // Redirect to prevent form resubmission
            header('Location: profile.php?updated=1');
            exit;
        } else {
            throw new Exception('Failed to update profile');
        }
    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Handle password change
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    try {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception('All password fields are required');
        }
        
        // Verify current password
        if(!password_verify($current_password, $user['password'])) {
            throw new Exception('Current password is incorrect');
        }
        
        // Check if new passwords match
        if($new_password !== $confirm_password) {
            throw new Exception('New passwords do not match');
        }
        
        // Check password length
        if(strlen($new_password) < 6) {
            throw new Exception('Password must be at least 6 characters');
        }
        
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($query);
        
        if($stmt->execute([$hashed, $user_id])) {
            $_SESSION['success'] = 'Password changed successfully';
            
            // Log activity
            logActivity($user_id, 'password_change', 'User changed their password');
            
            // Redirect
            header('Location: profile.php?password_changed=1');
            exit;
        } else {
            throw new Exception('Failed to change password');
        }
    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

include '../includes/header.php';
?>

<style>
:root {
    --primary-color: #8BC34A;
    --primary-dark: #6FA33B;
    --primary-light: #DCEDC8;
    --accent-orange: #FF9800;
    --accent-blue: #2196F3;
    --accent-purple: #9C27B0;
    --text-dark: #2C3E50;
    --text-light: #7F8C8D;
    --bg-light: #F5F7FA;
    --card-shadow: 0 5px 20px rgba(0,0,0,0.08);
    --card-hover: 0 8px 30px rgba(0,0,0,0.12);
}

body {
    background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
    min-height: 100vh;
    box-sizing: border-box;
}

.wrapper {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
    min-height: calc(100vh - 40px);
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
    padding: 4rem 2rem;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        radial-gradient(circle at 20% 80%, rgba(124, 179, 66, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(156, 204, 101, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(139, 195, 74, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.page-wrapper {
    position: relative;
    z-index: 1;
    padding: 2rem 0 4rem;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Alert Messages */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    animation: slideInDown 0.3s ease;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dashboard-banner {
    background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
    border-radius: 30px;
    padding: 3rem 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    position: relative;
    overflow: hidden;
}

.dashboard-banner h1 {
    font-size: 2.5rem;
    color: white;
    font-weight: 700;
    margin: 0;
}

.dashboard-banner p {
    color: rgba(255,255,255,0.95);
    font-size: 1.1rem;
    margin: 0.5rem 0 0 0;
}

.profile-card {
    background: linear-gradient(135deg, #f1f8e9 0%, #ffffff 100%);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 2px solid #dcedc8;
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
}

.profile-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(180deg, #7cb342 0%, #9ccc65 100%);
    transition: width 0.3s ease;
}

.profile-card:hover::before {
    width: 8px;
}

.profile-card:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 30px rgba(124, 179, 66, 0.2);
}

.card-header-custom {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--primary-light);
}

.card-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    box-shadow: 0 5px 15px rgba(139, 195, 74, 0.3);
}

.card-icon svg {
    width: 24px;
    height: 24px;
    stroke: white;
    fill: none;
    stroke-width: 2;
}

.card-header-custom h2 {
    font-size: 1.4rem;
    color: #33691e;
    font-weight: 700;
}

.form-group-custom {
    margin-bottom: 25px;
}

.form-group-custom label {
    display: block;
    margin-bottom: 8px;
    color: #558b2f;
    font-weight: 600;
    font-size: 0.95rem;
}

.form-group-custom .required {
    color: #e74c3c;
}

.form-control-custom {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border: 2px solid #dcedc8;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
    color: var(--text-dark);
}

.form-control-custom:focus {
    outline: none;
    border-color: #7cb342;
    background: white;
    box-shadow: 0 0 0 4px rgba(124, 179, 66, 0.1);
}

.form-control-custom:read-only {
    background: #f1f8e9;
    cursor: not-allowed;
    color: var(--text-light);
}

.form-control-custom::placeholder {
    color: #bdc3c7;
}

textarea.form-control-custom {
    resize: vertical;
    min-height: 100px;
}

.form-text-custom {
    display: block;
    margin-top: 6px;
    font-size: 0.85rem;
    color: #7cb342;
}

.btn-custom {
    padding: 14px 32px;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #7cb342 0%, #9ccc65 100%);
    color: white;
    box-shadow: 0 10px 30px rgba(124, 179, 66, 0.3);
}

.btn-primary-custom:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(124, 179, 66, 0.4);
}

.btn-warning-custom {
    background: linear-gradient(135deg, #FF9800, #F57C00);
    color: white;
    box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3);
}

.btn-warning-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4);
}

.sidebar-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 2rem;
}

.info-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border-left: 4px solid #7cb342;
}

.info-card h3 {
    font-size: 1.1rem;
    margin-bottom: 1.25rem;
    font-weight: 700;
    color: #33691e;
}

.info-content {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-item label {
    font-size: 0.85rem;
    color: var(--text-light);
    font-weight: 500;
}

.info-item span,
.info-item strong {
    font-weight: 600;
    color: var(--text-dark);
    font-size: 0.95rem;
}

.badge-custom {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.badge-success {
    background: #27ae60;
    color: white;
}

.badge-danger {
    background: #e74c3c;
    color: white;
}

.stats-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border-left: 4px solid #2196F3;
}

.stats-card h3 {
    color: #33691e;
    font-size: 1.1rem;
    margin-bottom: 1.25rem;
    font-weight: 700;
}

.stats-content {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 15px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
}

.stat-item label {
    color: var(--text-light);
    font-size: 0.85rem;
    font-weight: 500;
}

.stat-item strong {
    color: #7cb342;
    font-size: 1.8rem;
    font-weight: 700;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.profile-card {
    animation: fadeIn 0.6s ease;
}

.info-card {
    animation: fadeIn 0.6s ease 0.2s both;
}

.stats-card {
    animation: fadeIn 0.6s ease 0.3s both;
}

@media (max-width: 768px) {
    .sidebar-cards {
        grid-template-columns: 1fr;
    }
    
    .info-content {
        grid-template-columns: 1fr;
    }
    
    .stats-content {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>

<div class="wrapper">
    <div class="page-wrapper">
        <div class="container">
            <!-- Dashboard Banner -->
            <div class="dashboard-banner">
                <h1>My Profile</h1>
                <p>Manage your account information and settings</p>
            </div>

            <!-- Display Success/Error Messages -->
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Personal Information -->
            <div class="profile-card">
                <div class="card-header-custom">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h2>Personal Information</h2>
                </div>
                <form method="POST" action="">
                    <div class="form-group-custom">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" class="form-control-custom" id="name" name="name"
                            value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group-custom">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control-custom" id="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        <small class="form-text-custom">Email cannot be changed</small>
                    </div>

                    <div class="form-group-custom">
                        <label for="mobile">Mobile Number <span class="required">*</span></label>
                        <input type="text" class="form-control-custom" id="mobile" name="mobile"
                            value="<?php echo htmlspecialchars($user['mobile']); ?>"
                            pattern="09[0-9]{9}" required>
                        <small class="form-text-custom">Format: 09XXXXXXXXX</small>
                    </div>

                    <div class="form-group-custom">
                        <label for="address">Complete Address <span class="required">*</span></label>
                        <textarea class="form-control-custom" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>

                    <button type="submit" name="update_profile" class="btn-custom btn-primary-custom">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="profile-card">
                <div class="card-header-custom">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h2>Change Password</h2>
                </div>
                <form method="POST" action="" id="passwordForm">
                    <div class="form-group-custom">
                        <label for="current_password">Current Password <span class="required">*</span></label>
                        <input type="password" class="form-control-custom" id="current_password"
                            name="current_password" required>
                    </div>

                    <div class="form-group-custom">
                        <label for="new_password">New Password <span class="required">*</span></label>
                        <input type="password" class="form-control-custom" id="new_password"
                            name="new_password" minlength="6" required>
                        <small class="form-text-custom">Minimum 6 characters</small>
                    </div>

                    <div class="form-group-custom">
                        <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                        <input type="password" class="form-control-custom" id="confirm_password"
                            name="confirm_password" minlength="6" required>
                    </div>

                    <button type="submit" name="change_password" class="btn-custom btn-warning-custom">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        Change Password
                    </button>
                </form>
            </div>

            <!-- Account Information and Statistics Side by Side -->
            <div class="sidebar-cards">
                <!-- Account Info -->
                <div class="info-card">
                    <h3>Account Information</h3>
                    <div class="info-content">
                        <div class="info-item">
                            <label>Account Status</label>
                            <span class="badge-custom <?php echo $user['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Member Since</label>
                            <span><?php echo formatDate($user['created_at']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Last Updated</label>
                            <span><?php echo formatDate($user['updated_at']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="stats-card">
                    <h3>Application Statistics</h3>
                    <?php
                    // Get user stats
                    $stats_query = "SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN LOWER(status) = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN LOWER(status) = 'processing' THEN 1 ELSE 0 END) as processing,
                        SUM(CASE WHEN LOWER(status) = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN LOWER(status) = 'completed' THEN 1 ELSE 0 END) as completed
                        FROM applications WHERE user_id = ?";
                    $stmt = $pdo->prepare($stats_query);
                    $stmt->execute([$user_id]);
                    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="stats-content">
                        <div class="stat-item">
                            <label>Total</label>
                            <strong><?php echo $stats['total']; ?></strong>
                        </div>
                        <div class="stat-item">
                            <label>Pending</label>
                            <strong><?php echo $stats['pending']; ?></strong>
                        </div>
                        <div class="stat-item">
                            <label>Processing</label>
                            <strong><?php echo $stats['processing']; ?></strong>
                        </div>
                        <div class="stat-item">
                            <label>Approved</label>
                            <strong><?php echo $stats['approved']; ?></strong>
                        </div>
                        <div class="stat-item">
                            <label>Completed</label>
                            <strong><?php echo $stats['completed']; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if(newPass !== confirmPass) {
        e.preventDefault();
        alert('New passwords do not match!');
        document.getElementById('confirm_password').focus();
    }
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>

<?php include '../includes/footer.php'; ?>