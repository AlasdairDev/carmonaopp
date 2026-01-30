<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}
if (!isSuperAdmin()) {
    $_SESSION['error'] = 'Access denied. Only superadmins can manage users.';
    header('Location: dashboard.php');
    exit();
}
// --- LOGIC SECTION ---
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query - show ALL users (both active and deactivated)
$where = [];
$params = [];

if ($role_filter && $role_filter !== 'all') {
    $where[] = "role = ?";
    $params[] = $role_filter;
}

if ($status_filter) {
    if ($status_filter === 'active') {
        $where[] = "is_active = 1";
    } elseif ($status_filter === 'deactivated') {
        $where[] = "is_active = 0";
    }
}

if ($search) {
    $where[] = "(name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM users $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Get users
$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get statistics - count all users
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0,
    'admins' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() ?: 0,
    'users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?: 0,
    'deactivated' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0")->fetchColumn() ?: 0
];

// Get current user data for header
$current_user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$current_user_stmt->execute([$_SESSION['user_id']]);
$current_user = $current_user_stmt->fetch();

$pageTitle = 'User Management';
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

    <link rel="stylesheet" href="../assets/css/admin-responsive.css">
    <link rel="stylesheet" href="../assets/css/admin/users_styles.css">

</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <div class="page-header-content">
                <div class="page-header-left">
                    <h1>
                        User Management
                    </h1>
                    <p>Managing <strong id="managingCount"><?php echo number_format($total); ?></strong> registered
                        users</p>
                </div>
                <div class="page-header-right">
                    <button onclick="showAddUserModal()" class="btn btn-white">
                        <i class="fas fa-plus"></i> Add New User
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-value" id="totalUsersCount"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Administrators</h3>
                <div class="stat-value" id="adminsCount"><?php echo $stats['admins']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Regular Users</h3>
                <div class="stat-value" id="regularUsersCount"><?php echo $stats['users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Deactivated</h3>
                <div class="stat-value" style="color: #f59e0b;" id="deactivatedCount">
                    <?php echo $stats['deactivated']; ?>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-header">
                <h3>
                    <i class="fas fa-filter"></i>
                    Filter Users
                </h3>
            </div>

            <form id="filterForm">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Role</label>
                        <select name="role" id="roleFilter" class="form-control">
                            <option value="">All Roles</option>
                            <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Regular Users
                            </option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Administrators
                            </option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status" id="statusFilter" class="form-control">
                            <option value="">All Users</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active
                                Only</option>
                            <option value="deactivated" <?php echo $status_filter === 'deactivated' ? 'selected' : ''; ?>>
                                Deactivated Only</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" id="searchFilter" class="form-control"
                            placeholder="Name, Email, or Mobile..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <button type="button" id="resetBtn" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="results-info">
            <div class="results-count">
                Showing <strong id="showingCount"><?php echo count($users); ?></strong> of
                <strong id="totalCount"><?php echo number_format($total); ?></strong> users
            </div>
        </div>

        <!-- Users Table -->
        <div id="usersTableWrapper">
            <?php if (empty($users)): ?>
                <div class="table-card">
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <h3>No Users Found</h3>
                        <p>Try adjusting your filters or search criteria</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-card">
                    <div class="table-container">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Contact Info</th>
                                    <th>Role</th>
                                    <th>Applications</th>
                                    <th>Registered</th>
                                    <th style="text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $app_count_query = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
                                    $app_count_query->execute([$user['id']]);
                                    $app_count = $app_count_query->fetchColumn();

                                    $isCurrentUser = $user['id'] === $_SESSION['user_id'];
                                    $initials = '';
                                    $name_parts = explode(' ', $user['name']);
                                    foreach ($name_parts as $part) {
                                        if (!empty($part)) {
                                            $initials .= strtoupper(substr($part, 0, 1));
                                        }
                                    }
                                    $initials = substr($initials, 0, 2);

                                    // Check if user is deactivated
                                    $isDeactivated = $user['is_active'] == 0;
                                    ?>
                                    <tr id="user-row-<?php echo $user['id']; ?>" data-user-role="<?php echo $user['role']; ?>"
                                        style="<?php echo $isDeactivated ? 'background: #fff7ed;' : ''; ?>">
                                        <td>
                                            <div class="user-info-cell">
                                                <div class="user-avatar"
                                                    style="<?php echo $isDeactivated ? 'background: #f59e0b;' : ''; ?>">
                                                    <span><?php echo $initials; ?></span>
                                                </div>
                                                <div class="user-details">
                                                    <div class="user-name">
                                                        <?php echo htmlspecialchars($user['name']); ?>
                                                        <?php if ($isCurrentUser): ?>
                                                            <span class="badge badge-info" style="font-size: 0.625rem;">YOU</span>
                                                        <?php endif; ?>
                                                        <?php if ($isDeactivated): ?>
                                                            <span class="badge"
                                                                style="background: #fed7aa; color: #c2410c; font-size: 0.625rem; margin-left: 0.5rem;">DEACTIVATED</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <div>
                                                    <i class="fas fa-envelope"></i>
                                                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                                                </div>
                                                <div>
                                                    <i class="fas fa-phone"></i>
                                                    <span><?php echo htmlspecialchars($user['mobile'] ?? 'N/A'); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-<?php echo $user['role'] === 'admin' ? 'admin' : 'user'; ?>">
                                                <?php echo strtoupper($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-applications">
                                                <?php echo $app_count; ?> Application<?php echo $app_count != 1 ? 's' : ''; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-cell">
                                                <?php
                                                $date = new DateTime($user['created_at']);
                                                echo $date->format('M d, Y');
                                                ?>
                                                <div class="date-time">
                                                    <?php echo $date->format('h:i A'); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="action-buttons">
                                                <?php if ($isDeactivated): ?>
                                                    <button class="btn-action btn-reactivate"
                                                        onclick="reactivateUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>', <?php echo $user['role'] === 'admin' ? 'true' : 'false'; ?>)"
                                                        title="Reactivate User">
                                                        <i class="fas fa-check-circle"></i> Reactivate
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn-action btn-edit"
                                                        onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                                        title="Edit User">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <?php if (!$isCurrentUser): ?>
                                                        <button class="btn-action btn-deactivate"
                                                            onclick="deactivateUser(<?php echo $user['id']; ?>, <?php echo $user['role'] === 'admin' ? 'true' : 'false'; ?>)"
                                                            title="Deactivate User">
                                                            <i class="fas fa-user-slash"></i> Deactivate
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div id="paginationWrapper">
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="#" onclick="loadPage(<?php echo ($page - 1); ?>); return false;" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <span class="pagination-info">
                            Page <span id="currentPage"><?php echo $page; ?></span> of <span
                                id="totalPages"><?php echo $total_pages; ?></span>
                        </span>

                        <?php if ($page < $total_pages): ?>
                            <a href="#" onclick="loadPage(<?php echo ($page + 1); ?>); return false;" class="pagination-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New User</h3>
                <button class="close-modal" onclick="closeUserModal()">&times;</button>
            </div>

            <form id="userForm">
                <input type="hidden" id="userId" name="user_id">

                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="name" class="form-control" required>
                    <div class="error-message" id="fullName-error"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                    <div class="error-message" id="email-error"></div>
                </div>

                <div class="form-group">
                    <label for="phone">Mobile Number</label>
                    <input type="tel" id="phone" name="mobile" class="form-control" placeholder="09XXXXXXXXX">
                    <div class="error-message" id="phone-error"></div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"
                        placeholder="Enter complete address"></textarea>
                    <div class="error-message" id="address-error"></div>
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="user">User</option>
                        <option value="department_admin">Department Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>

                <!-- Department field -->
                <div class="form-group" id="departmentGroup" style="display: none;">
                    <label for="department">Department <span class="text-danger">*</span></label>
                    <select class="form-control" id="department" name="department_id">
                        <option value="">Select Department</option>
                        <?php
                        $dept_stmt = $pdo->query("SELECT id, name, code FROM departments WHERE is_active = 1 ORDER BY name");
                        while ($dept = $dept_stmt->fetch()) {
                            echo "<option value='{$dept['id']}'>{$dept['name']} ({$dept['code']})</option>";
                        }
                        ?>
                    </select>
                    <small class="form-text text-muted">Required for Department Admin role</small>
                </div>
                <div class="form-group" id="passwordGroup">
                    <label for="password">Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                            <i class="fas fa-eye-slash"></i> <!-- Changed from fa-eye -->
                        </button>
                    </div>
                    <div class="error-message" id="password-error"></div>
                </div>

                <div class="form-group" id="confirmPasswordGroup">
                    <label for="confirmPassword">Confirm Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirmPassword" name="confirm_password" class="form-control">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">
                            <i class="fas fa-eye-slash"></i> <!-- Changed from fa-eye -->
                        </button>
                    </div>
                    <div class="error-message" id="confirmPassword-error"></div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Deactivation Modal -->
    <div id="deactivateModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i> Confirm Deactivation</h3>
                <button class="close-modal" onclick="closeDeactivateModal()">&times;</button>
            </div>

            <div style="padding: 1.5rem 0;">
                <div
                    style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <div style="font-weight: 700; color: #92400e; margin-bottom: 0.5rem;">
                        <i class="fas fa-exclamation-triangle"></i> Warning
                    </div>
                    <div style="color: #78350f; font-size: 0.875rem;">
                        You are about to deactivate a user account
                    </div>
                </div>

                <div id="deactivateUserInfo"
                    style="background: var(--background); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <!-- User info will be inserted here -->
                </div>

                <div style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.8;">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.75rem;">This
                        will:</strong>
                    <div style="display: flex; align-items: start; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-times-circle" style="color: #ef4444; margin-top: 0.25rem;"></i>
                        <span>Prevent the user from logging in</span>
                    </div>
                    <div style="display: flex; align-items: start; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-database" style="color: #3b82f6; margin-top: 0.25rem;"></i>
                        <span>Keep all their data intact</span>
                    </div>
                    <div style="display: flex; align-items: start; gap: 0.5rem;">
                        <i class="fas fa-undo" style="color: #10b981; margin-top: 0.25rem;"></i>
                        <span>Can be reversed by reactivating the account</span>
                    </div>
                </div>

                <div id="adminWarning"
                    style="display: none; background: #fee2e2; border-left: 4px solid #dc2626; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                    <div style="font-weight: 700; color: #991b1b; margin-bottom: 0.5rem;">
                        <i class="fas fa-exclamation-circle"></i> ADMIN DEACTIVATION
                    </div>
                    <div style="color: #7f1d1d; font-size: 0.875rem;">
                        This will temporarily remove administrator access. This action will be logged for audit
                        purposes.
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDeactivateModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn" id="confirmDeactivateBtn"
                    style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                    <i class="fas fa-user-slash"></i> Deactivate User
                </button>
            </div>
        </div>
    </div>

    <!-- Reactivation Confirmation Modal -->
    <div id="reactivateModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle" style="color: #10b981;"></i> Confirm Reactivation</h3>
                <button class="close-modal" onclick="closeReactivateModal()">&times;</button>
            </div>

            <div style="padding: 1.5rem 0;">
                <div
                    style="background: #d1fae5; border-left: 4px solid #10b981; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <div style="font-weight: 700; color: #065f46; margin-bottom: 0.5rem;">
                        <i class="fas fa-check-circle"></i> Reactivate Account
                    </div>
                    <div style="color: #047857; font-size: 0.875rem;">
                        You are about to reactivate a user account
                    </div>
                </div>

                <div id="reactivateUserInfo"
                    style="background: var(--background); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <!-- User info will be inserted here -->
                </div>

                <div style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.8;">
                    <strong style="color: var(--text-primary); display: block; margin-bottom: 0.75rem;">This
                        will:</strong>
                    <div style="display: flex; align-items: start; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-check-circle" style="color: #10b981; margin-top: 0.25rem;"></i>
                        <span>Allow the user to login again</span>
                    </div>
                    <div style="display: flex; align-items: start; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-unlock" style="color: #3b82f6; margin-top: 0.25rem;"></i>
                        <span>Restore full account access</span>
                    </div>
                    <div style="display: flex; align-items: start; gap: 0.5rem;">
                        <i class="fas fa-shield-alt" style="color: #8b5cf6; margin-top: 0.25rem;"></i>
                        <span>Reactivate all privileges</span>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeReactivateModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn" id="confirmReactivateBtn"
                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                    <i class="fas fa-check-circle"></i> Reactivate User
                </button>
            </div>
        </div>
    </div>
    <script>
        let currentPage = <?php echo $page; ?>;

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

        function showAddUserModal() {
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('passwordGroup').style.display = 'block';
            document.getElementById('confirmPasswordGroup').style.display = 'block';
            document.getElementById('password').setAttribute('required', 'required');
            document.getElementById('confirmPassword').setAttribute('required', 'required');

            // Reset password fields to password type
            document.getElementById('password').type = 'password';
            document.getElementById('confirmPassword').type = 'password';

            // Reset icons to eye-slash (closed eye) for hidden passwords
            document.querySelectorAll('.password-toggle i').forEach(icon => {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');        
            });

            document.getElementById('userModal').classList.add('show');

            // Clear all error states
            document.querySelectorAll('.form-control').forEach(field => {
                field.classList.remove('error', 'success');
            });
            document.querySelectorAll('.error-message').forEach(msg => {
                msg.classList.remove('show');
                msg.textContent = '';
            });
        }

        function editUser(user) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('userId').value = user.id;
            document.getElementById('fullName').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('phone').value = user.mobile || '';
            document.getElementById('address').value = user.address || '';
            document.getElementById('role').value = user.role;

            // Hide password fields for edit
            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('confirmPasswordGroup').style.display = 'none';
            document.getElementById('password').removeAttribute('required');
            document.getElementById('confirmPassword').removeAttribute('required');

            document.getElementById('userModal').classList.add('show');

            // Clear all error states
            document.querySelectorAll('.form-control').forEach(field => {
                field.classList.remove('error', 'success');
            });
            document.querySelectorAll('.error-message').forEach(msg => {
                msg.classList.remove('show');
                msg.textContent = '';
            });
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('show');
            document.getElementById('userForm').reset();
        }

        function showToast(message, type = 'success') {
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;

            const icon = type === 'success' ? '✓' : '✕';
            const iconColor = type === 'success' ? '#22c55e' : '#ef4444';

            toast.innerHTML = `
            <div class="toast-icon" style="color: ${iconColor}">${icon}</div>
            <div class="toast-message">${message}</div>
        `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.getElementById(`${fieldId}-error`);

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
            const field = document.getElementById(fieldId);
            const errorDiv = document.getElementById(`${fieldId}-error`);

            if (field) {
                field.classList.remove('error');
            }

            if (errorDiv) {
                errorDiv.classList.remove('show');
                errorDiv.textContent = '';
            }
        }

        // Add input listeners to clear errors on input
        ['fullName', 'email', 'phone', 'address', 'password', 'confirmPassword'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', () => clearError(fieldId));
            }
        });
        document.getElementById('userForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            // Clear all previous errors
            document.querySelectorAll('.form-control').forEach(field => {
                field.classList.remove('error', 'success');
            });
            document.querySelectorAll('.error-message').forEach(msg => {
                msg.classList.remove('show');
                msg.textContent = '';
            });

            // Get form values
            const fullName = document.getElementById('fullName').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const password = document.getElementById('password').value;
            const userId = document.getElementById('userId').value;
            const isEditing = userId !== '';

            const errors = [];

            // 1. Full Name Validation
            if (fullName.length < 3) {
                showError('fullName', 'Must be at least 3 characters long');
                errors.push('Full name must be at least 3 characters');
            } else if (fullName.length > 100) {
                showError('fullName', 'Cannot exceed 100 characters');
                errors.push('Full name is too long');
            } else if (!/^[a-zA-ZÀ-ÿ\s.\-']+$/.test(fullName)) {
                showError('fullName', 'Only letters, spaces, hyphens, periods allowed');
                errors.push('Full name contains invalid characters');
            }

            // 2. Email Validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('email', 'Please enter a valid email address');
                errors.push('Invalid email address');
            } else if (email.length > 255) {
                showError('email', 'Email address is too long');
                errors.push('Email is too long');
            }

            // 3. Phone Validation
            if (phone) {
                const cleanPhone = phone.replace(/[\s\-]/g, '');
                const phoneRegex = /^09[0-9]{9}$/;

                if (!phoneRegex.test(cleanPhone)) {
                    showError('phone', 'Format: 09XXXXXXXXX (11 digits)');
                    errors.push('Invalid phone number format');
                }
            }

            // 4. Password Validation
            if (!isEditing || password) {
                if (password.length > 0 && password.length < 8) {
                    showError('password', 'Must be at least 8 characters');
                    errors.push('Password too short');
                }

                if (password.length > 0) {
                    const hasUppercase = /[A-Z]/.test(password);
                    const hasLowercase = /[a-z]/.test(password);
                    const hasNumber = /\d/.test(password);
                    const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/`~;']/.test(password);

                    if (!hasUppercase || !hasLowercase || !hasNumber || !hasSpecial) {
                        showError('password', 'Need: uppercase, lowercase, number, special char');
                        errors.push('Password must contain uppercase, lowercase, number, and special character');
                    }

                    // Check confirm password
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    if (password !== confirmPassword) {
                        showError('confirmPassword', 'Passwords do not match');
                        errors.push('Passwords do not match');
                    }
                }

                if (!isEditing && password.length === 0) {
                    showError('password', 'Password is required for new users');
                    errors.push('Password is required');
                }
            }

            if (errors.length > 0) {
                // Show first error 
                showToast(errors[0], 'error');
                return false;
            }

            // If validation passes, submit the form
            const formData = new FormData(e.target);

            try {
                const response = await fetch('../api/save_user.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    // Show success message briefly before reload
                    showToast(result.message || 'User saved successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
    
                    showToast(result.message || 'An error occurred while saving.', 'error');

                    if (result.message.includes('Email')) {
                        showError('email', result.message);
                    } else if (result.message.includes('Mobile')) {
                        showError('phone', result.message);
                    }
                }
            } catch (error) {
                console.error('Save user error:', error);
                showToast('An error occurred while saving. Please try again.', 'error');
            }
        });

        let deactivateUserId = null;
        let deactivateUserIsAdmin = false;
        let reactivateUserId = null;
        let reactivateUserName = '';
        let reactivateUserIsAdmin = false;

        function deactivateUser(userId, isAdmin) {
            deactivateUserId = userId;  
            deactivateUserIsAdmin = isAdmin;

            // Get user info from the row
            const row = document.getElementById(`user-row-${userId}`);
            if (!row) {
                showToast('User not found', 'error');
                return;
            }

            const userNameElement = row.querySelector('.user-name');
            let userName = userNameElement.textContent;
            // Remove all badges from the name
            const badges = userNameElement.querySelectorAll('.badge');
            badges.forEach(badge => {
                userName = userName.replace(badge.textContent, '');
            });
            userName = userName.trim();

            const userEmail = row.querySelector('.contact-info .fa-envelope').nextElementSibling.textContent;
            const userRole = isAdmin ? 'Administrator' : 'Regular User';

            // Update modal content
            document.getElementById('deactivateUserInfo').innerHTML = `
        <div style="margin-bottom: 0.5rem;">
            <strong style="color: var(--text-primary);">User:</strong> 
            <span style="color: var(--text-secondary);">${userName}</span>
        </div>
        <div style="margin-bottom: 0.5rem;">
            <strong style="color: var(--text-primary);">Email:</strong> 
            <span style="color: var(--text-secondary);">${userEmail}</span>
        </div>
        <div>
            <strong style="color: var(--text-primary);">Role:</strong> 
            <span class="badge badge-${isAdmin ? 'admin' : 'user'}" style="margin-left: 0.5rem;">${userRole.toUpperCase()}</span>
        </div>
    `;

            // Show/hide admin warning
            document.getElementById('adminWarning').style.display = isAdmin ? 'block' : 'none';

            // Show modal
            document.getElementById('deactivateModal').classList.add('show');
        }

        function closeDeactivateModal() {
            document.getElementById('deactivateModal').classList.remove('show');
            deactivateUserId = null;
            deactivateUserIsAdmin = false;
        }

        function reactivateUser(userId, userName, isAdmin) {
            reactivateUserId = userId;
            reactivateUserName = userName;
            reactivateUserIsAdmin = isAdmin;

            const row = document.getElementById(`user-row-${userId}`);
            if (!row) {
                showToast('User not found', 'error');
                return;
            }

            const userNameElement = row.querySelector('.user-name');
            let extractedName = userNameElement.textContent;
            const badges = userNameElement.querySelectorAll('.badge');
            badges.forEach(badge => {
                extractedName = extractedName.replace(badge.textContent, '');
            });
            extractedName = extractedName.trim();

            const userEmail = row.querySelector('.contact-info .fa-envelope').nextElementSibling.textContent;
            const userRole = isAdmin ? 'Administrator' : 'Regular User';

            document.getElementById('reactivateUserInfo').innerHTML = `
        <div style="margin-bottom: 0.5rem;">
            <strong style="color: var(--text-primary);">User:</strong> 
            <span style="color: var(--text-secondary);">${extractedName}</span>
        </div>
        <div style="margin-bottom: 0.5rem;">
            <strong style="color: var(--text-primary);">Email:</strong> 
            <span style="color: var(--text-secondary);">${userEmail}</span>
        </div>
        <div>
            <strong style="color: var(--text-primary);">Role:</strong> 
            <span class="badge badge-${isAdmin ? 'admin' : 'user'}" style="margin-left: 0.5rem;">${userRole.toUpperCase()}</span>
        </div>
    `;

            document.getElementById('reactivateModal').classList.add('show');
        }

        function closeReactivateModal() {
            document.getElementById('reactivateModal').classList.remove('show');
            reactivateUserId = null;
            reactivateUserName = '';
            reactivateUserIsAdmin = false;
        }

        // Handle the actual deactivation
        document.getElementById('confirmDeactivateBtn').addEventListener('click', async function () {
            if (!deactivateUserId) return;

            const userIdToDeactivate = deactivateUserId;

            // Close modal
            closeDeactivateModal();

            try {
                const response = await fetch('../api/deactivate_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ user_id: userIdToDeactivate })
                });

                const result = await response.json();

                if (result.success) {
                    const message = result.was_admin
                        ? 'Administrator deactivated successfully!'
                        : 'User deactivated successfully!';

                    showToast(message, 'success');

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(result.message || 'Failed to deactivate user', 'error');
                }
            } catch (error) {
                console.error('Deactivate error:', error);
                showToast('An error occurred while deactivating. Please try again.', 'error');
            }
        });

        // Handle the actual reactivation
        document.getElementById('confirmReactivateBtn').addEventListener('click', async function () {
            if (!reactivateUserId) return;

            const userIdToReactivate = reactivateUserId;

            // Close modal
            closeReactivateModal();

            try {
                const response = await fetch('../api/reactivate_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ user_id: userIdToReactivate })
                });

                const result = await response.json();

                if (result.success) {
                    const message = result.was_admin
                        ? 'Administrator reactivated successfully!'
                        : 'User reactivated successfully!'

                    showToast(message, 'success');

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(result.message || 'Failed to reactivate user', 'error');
                }
            } catch (error) {
                console.error('Reactivate error:', error);
                showToast('An error occurred while reactivating. Please try again.', 'error');
            }
        });

        console.log('✅ User management page loaded successfully');

        // ============================================
        // REAL-TIME VALIDATION FOR ADD USER MODAL
        // ============================================

        // Full Name real-time validation
        const fullNameField = document.getElementById('fullName');
        if (fullNameField) {
            fullNameField.addEventListener('input', function () {
                const value = this.value.trim();

                if (value.length > 0) {
                    if (value.length < 3) {
                        showError('fullName', 'Must be at least 3 characters long');
                    } else if (value.length > 100) {
                        showError('fullName', 'Cannot exceed 100 characters');
                    } else if (!/^[a-zA-ZÀ-ÿ\s.\-']+$/.test(value)) {
                        showError('fullName', 'Only letters, spaces, hyphens, periods allowed');
                    } else {
                        clearError('fullName');
                    }
                } else {
                    clearError('fullName');
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

        // Phone real-time validation
        const phoneField = document.getElementById('phone');
        if (phoneField) {
            phoneField.addEventListener('input', function () {
                const value = this.value.trim();

                if (value.length > 0) {
                    const cleanPhone = value.replace(/[\s\-]/g, '');
                    const phoneRegex = /^09[0-9]{9}$/;

                    if (!phoneRegex.test(cleanPhone)) {
                        showError('phone', 'Format: 09XXXXXXXXX (11 digits)');
                    } else {
                        clearError('phone');
                    }
                } else {
                    clearError('phone');
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
        const confirmPasswordField = document.getElementById('confirmPassword');
        if (confirmPasswordField) {
            confirmPasswordField.addEventListener('input', function () {
                const password = document.getElementById('password').value;
                const confirmPassword = this.value;

                if (confirmPassword.length > 0) {
                    if (password !== confirmPassword) {
                        showError('confirmPassword', 'Passwords do not match');
                    } else {
                        clearError('confirmPassword');
                    }
                } else {
                    clearError('confirmPassword');
                }
            });
        }

        // ============================================
        // AJAX FILTER FUNCTIONALITY
        // ============================================

        document.getElementById('filterForm').addEventListener('submit', function (e) {
            e.preventDefault();
            loadFilteredUsers();
        });

        document.getElementById('resetBtn').addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('roleFilter').value = '';
            document.getElementById('searchFilter').value = '';
            document.getElementById('statusFilter').value = '';
            currentPage = 1;
            loadFilteredUsers();
        });

        function loadPage(page) {
            currentPage = page;
            loadFilteredUsers();
        }

        async function loadFilteredUsers() {
            const role = document.getElementById('roleFilter').value;
            const search = document.getElementById('searchFilter').value;
            const status = document.getElementById('statusFilter').value;

            const params = new URLSearchParams();
            params.append('page', currentPage);
            if (role) params.append('role', role);
            if (search) params.append('search', search);
            if (status) params.append('status', status);
            params.append('ajax', '1');

            try {
                const response = await fetch(`users.php?${params.toString()}`);
                const html = await response.text();

                // Parse the HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Update table
                const newTable = doc.querySelector('#usersTableWrapper');
                if (newTable) {
                    document.getElementById('usersTableWrapper').innerHTML = newTable.innerHTML;
                }

                // Update pagination
                const newPagination = doc.querySelector('#paginationWrapper');
                if (newPagination) {
                    document.getElementById('paginationWrapper').innerHTML = newPagination.innerHTML;
                }

                // Update stats
                const newStats = {
                    showing: doc.querySelector('#showingCount')?.textContent || '0',
                    total: doc.querySelector('#totalCount')?.textContent || '0',
                    totalUsers: doc.querySelector('#totalUsersCount')?.textContent || '0',
                    admins: doc.querySelector('#adminsCount')?.textContent || '0',
                    regularUsers: doc.querySelector('#regularUsersCount')?.textContent || '0',
                    deactivated: doc.querySelector('#deactivatedCount')?.textContent || '0',
                    managing: doc.querySelector('#managingCount')?.textContent || '0'
                };

                document.getElementById('showingCount').textContent = newStats.showing;
                document.getElementById('totalCount').textContent = newStats.total;
                document.getElementById('totalUsersCount').textContent = newStats.totalUsers;
                document.getElementById('adminsCount').textContent = newStats.admins;
                document.getElementById('regularUsersCount').textContent = newStats.regularUsers;
                document.getElementById('deactivatedCount').textContent = newStats.deactivated;
                document.getElementById('managingCount').textContent = newStats.managing;

            } catch (error) {
                console.error('Filter error:', error);
                showToast('An error occurred while filtering. Please try again.', 'error');
            }
        }
        // Show/hide department field based on role
        document.getElementById('role').addEventListener('change', function () {
            const departmentGroup = document.getElementById('departmentGroup');
            const departmentSelect = document.getElementById('department');

            if (this.value === 'department_admin') {
                departmentGroup.style.display = 'block';
                departmentSelect.required = true;
            } else {
                departmentGroup.style.display = 'none';
                departmentSelect.required = false;
                departmentSelect.value = '';
            }
        });

    </script>
</body>

</html>

<?php include '../includes/footer.php'; ?>
