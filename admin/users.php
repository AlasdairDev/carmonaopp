<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
   header('Location: ../login.php');
   exit();
}

// --- LOGIC SECTION ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where = [];
$params = [];

if ($role_filter && $role_filter !== 'all') {
   $where[] = "role = ?";
   $params[] = $role_filter;
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

// Get statistics
$stats = [
   'total' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0,
   'admins' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() ?: 0,
   'users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn() ?: 0,
   'today' => $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0
];

$pageTitle = 'User Management';
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: 1rem;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .header-actions {
            position: relative;
            z-index: 1;
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
            border-radius: var(--radius);
            padding: 1.75rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card:hover::before {
            width: 8px;
        }

        .stat-card h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        /* Filters Section */
        .filters-section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border);
        }

        .filters-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: var(--background);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 195, 74, 0.1);
            background: white;
        }

        .filter-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            grid-column: 1 / -1;
        }

        .filter-actions .btn {
            width: 100%;
            margin: 0;
            padding: 0.75rem 1.5rem;
            text-align: center;
            box-sizing: border-box;
        }

        .filter-group .btn {
            width: 100%;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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

        .btn-white {
            background: white;
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        /* Results Info */
        .results-info {
            background: var(--surface);
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .results-count {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .results-count strong {
            color: var(--text-primary);
            font-weight: 700;
        }

        /* Table */
        .table-card {
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }

        .modern-table thead {
            background: linear-gradient(135deg, rgba(139, 195, 74, 0.1) 0%, rgba(102, 187, 106, 0.1) 100%);
        }

        .modern-table th {
            padding: 1rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
        }

        .modern-table tbody tr {
            transition: background 0.2s ease;
            cursor: default;
            border-bottom: 1px solid var(--border);
        }

        .modern-table tbody tr:hover {
            background: var(--background);
        }

        .modern-table td {
            padding: 1rem;
            font-size: 0.875rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .user-avatar.admin {
            background: #ffebee;
            color: #c62828;
        }

        .user-avatar.user {
            background: #f0f0f0;
            color: #999;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 700;
            color: var(--text-primary);
        }

        .user-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            font-size: 0.625rem;
            font-weight: 800;
            text-transform: uppercase;
            background: #e3f2fd;
            color: #1976d2;
            margin-top: 0.25rem;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-admin {
            background: #ffebee;
            color: #c62828;
        }

        .role-user {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            background: none;
            border: none;
            color: var(--primary);
            font-weight: 800;
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.2s ease;
            font-size: 0.875rem;
        }

        .btn-icon:hover {
            color: var(--primary-dark);
        }

        .btn-icon.delete {
            color: #ef5350;
        }

        .btn-icon.delete:hover {
            color: #c62828;
        }

        .btn-icon:disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .page-btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: white;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .page-btn:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }

        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: #f0f0f0;
            color: #333;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

.modal-actions {
    display: flex !important;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.modal-actions .btn {
    flex: 1;
    height: 48px !important;
    min-height: 48px;
    max-height: 48px;
    padding: 0 1.5rem !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 8px !important;
    box-sizing: border-box;
}

.modal-actions .btn-secondary,
.modal-actions .btn-primary {
    height: 48px !important;
}

        /* Row deletion animation */
        .row-deleting {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-grid {
                grid-template-columns: 1fr 2fr;
            }

            .filters-grid .filter-group:nth-child(3),
            .filters-grid .filter-group:nth-child(4) {
                grid-column: span 1;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem 1rem 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .filters-grid .filter-group:nth-child(3),
            .filters-grid .filter-group:nth-child(4) {
                grid-column: span 1;
            }

            .results-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .table-container {
                overflow-x: scroll;
            }
        }
        /* Ensure consistent button and input styling */
.filter-group .btn {
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px !important;
}

.filter-group input[type="text"],
.filter-group select {
    border-radius: 8px !important;
    height: 48px;
}

.btn-primary {
    border-radius: 8px !important;
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary) 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 195, 74, 0.3);
}

.btn-secondary {
    border-radius: 8px !important;
}

.btn-secondary:hover {
    background: #cbd5e1;
    color: var(--text-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(226, 232, 240, 0.5);
}
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1> User Management</h1>
                <p>Managing <strong><?php echo number_format($total); ?></strong> registered users</p>
            </div>
            <div class="header-actions">
                <button onclick="showAddUserModal()" class="btn btn-white">
                    <i class="fas fa-plus"></i> Add New User
                </button>
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
                <h3>Joined Today</h3>
                <div class="stat-value"><?php echo $stats['today']; ?></div>
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

            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Regular Users</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Administrators</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, Email, or Mobile..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <a href="users.php" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
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
                Showing <strong><?php echo count($users); ?></strong> of <strong><?php echo number_format($total); ?></strong> users
            </div>
        </div>

        <!-- Users Table -->
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
                            <?php foreach ($users as $user):
                                $app_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
                                $app_count_stmt->execute([$user['id']]);
                                $app_count = $app_count_stmt->fetchColumn();
                                $is_self = ($user['id'] == $_SESSION['user_id']);
                                $is_admin = ($user['role'] === 'admin');
                            ?>
                            <tr id="user-row-<?php echo $user['id']; ?>" data-user-id="<?php echo $user['id']; ?>" data-user-role="<?php echo $user['role']; ?>">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar <?php echo $is_admin ? 'admin' : 'user'; ?>">
                                            <i class="fas fa-<?php echo $is_admin ? 'user-shield' : 'user'; ?>"></i>
                                        </div>
                                        <div class="user-details">
                                            <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                                            <?php if ($is_self): ?>
                                                <span class="user-badge">YOU</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        <?php echo htmlspecialchars($user['mobile'] ?: 'No Phone'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo $is_admin ? 'role-admin' : 'role-user'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="applications.php?search=<?php echo urlencode($user['email']); ?>" 
                                    style="color: var(--primary); font-weight: 700; text-decoration: none;">
                                        <?php echo $app_count; ?> <?php echo $app_count == 1 ? 'Application' : 'Applications'; ?>
                                    </a>
                                </td>
                                <td>
                                    <div><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                        <?php echo date('h:i A', strtotime($user['created_at'])); ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-buttons">
                                        <button onclick="editUser(<?php echo $user['id']; ?>)" class="btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php if (!$is_self): ?>
                                            <button onclick="deleteUser(<?php echo $user['id']; ?>, <?php echo $is_admin ? 'true' : 'false'; ?>)" 
                                                    class="btn-icon delete" 
                                                    title="<?php echo $is_admin ? 'Delete Admin (Requires confirmation)' : 'Delete User'; ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <?php
                        $prev_params = $_GET;
                        $prev_params['page'] = $page - 1;
                        ?>
                        <a href="?<?php echo http_build_query($prev_params); ?>" class="page-btn">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                        $page_params = $_GET;
                        $page_params['page'] = $i;
                    ?>
                        <a href="?<?php echo http_build_query($page_params); ?>" class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <?php
                        $next_params = $_GET;
                        $next_params['page'] = $page + 1;
                        ?>
                        <a href="?<?php echo http_build_query($next_params); ?>" class="page-btn">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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
                    <label>Full Name <span style="color: red;">*</span></label>
                    <input type="text" id="fullName" name="full_name" class="form-control" required placeholder="Juan Dela Cruz">
                </div>
                
                <div class="form-group">
                    <label>Email Address <span style="color: red;">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="juan@example.com">
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="09123456789">
                </div>
                
                <div class="form-group">
                    <label>Role <span style="color: red;">*</span></label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="user">Regular User</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                
                <div class="form-group" id="passwordGroup">
                    <label>Password <span style="color: red;">*</span></label>
                    <input type="password" id="password" name="password" class="form-control">
                    <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                        Leave blank to keep current password (when editing)
                    </small>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeUserModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function showAddUserModal() {
        document.getElementById('modalTitle').textContent = 'Add New User';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('password').required = true;
        document.getElementById('userModal').classList.add('show');
    }

    function closeUserModal() {
        document.getElementById('userModal').classList.remove('show');
    }

    async function editUser(userId) {
        try {
            const response = await fetch(`../api/get_user.php?id=${userId}`);
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('userId').value = result.data.id;
                document.getElementById('fullName').value = result.data.full_name || result.data.name || '';
                document.getElementById('email').value = result.data.email || '';
                document.getElementById('phone').value = result.data.phone || result.data.mobile || '';
                document.getElementById('role').value = result.data.role || 'user';
                document.getElementById('password').required = false;
                document.getElementById('userModal').classList.add('show');
            } else {
                alert('❌ Error: ' + result.message);
            }
        } catch (err) { 
            console.error('Edit user error:', err);
            alert('❌ Could not fetch user data. Please try again.'); 
        }
    }

    document.getElementById('userForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('../api/save_user.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert('✅ User saved successfully!');
                location.reload();
            } else {
                alert('❌ Error: ' + result.message);
            }
        } catch (error) { 
            console.error('Save user error:', error);
            alert('❌ An error occurred while saving. Please try again.'); 
        }
    });

    async function deleteUser(userId, isAdmin) {
        if (!confirm('⚠️ Are you sure you want to delete this user?\n\nThis will also delete:\n- All their applications\n- All their documents\n- All their notifications\n\nThis action cannot be undone!')) {
            return;
        }
        
        if (isAdmin) {
            if (!confirm('🚨 ADMIN DELETION WARNING 🚨\n\nYou are about to delete an ADMINISTRATOR account!\n\nThis will:\n✗ Remove all admin privileges\n✗ Delete all their data permanently\n✗ This action will be logged for audit\n\nAre you ABSOLUTELY SURE you want to proceed?')) {
                return;
            }
        }
        
        const row = document.getElementById(`user-row-${userId}`);
        if (!row) {
            console.error('Row not found for user:', userId);
            return;
        }
        
        const userRole = row.dataset.userRole;
        
        try {
            const response = await fetch('../api/delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ user_id: userId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                row.classList.add('row-deleting');
                
                setTimeout(() => {
                    row.remove();
                    updateStatsAfterDelete(userRole);
                    
                    const tbody = document.getElementById('usersTableBody');
                    if (tbody && tbody.children.length === 0) {
                        const tableContainer = tbody.closest('.table-card');
                        if (tableContainer) {
                            tableContainer.innerHTML = `
                                <div class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <h3>No Users Found</h3>
                                    <p>All users have been removed.</p>
                                </div>
                            `;
                        }
                    }
                    
                    const message = result.was_admin 
                        ? '✅ Administrator deleted successfully!\n\n📋 This action has been logged for audit purposes.'
                        : '✅ User deleted successfully!';
                    
                    alert(message);
                }, 300);
            } else {
                alert('❌ Error: ' + result.message);
            }
        } catch (error) { 
            console.error('Delete error:', error);
            alert('❌ An error occurred while deleting. Please try again.'); 
        }
    }

    function updateStatsAfterDelete(userRole) {
        const totalCount = document.getElementById('totalUsersCount');
        if (totalCount) {
            const current = parseInt(totalCount.textContent);
            totalCount.textContent = Math.max(0, current - 1);
        }
        
        if (userRole === 'admin') {
            const adminsCount = document.getElementById('adminsCount');
            if (adminsCount) {
                const current = parseInt(adminsCount.textContent);
                adminsCount.textContent = Math.max(0, current - 1);
            }
        } else {
            const regularCount = document.getElementById('regularUsersCount');
            if (regularCount) {
                const current = parseInt(regularCount.textContent);
                regularCount.textContent = Math.max(0, current - 1);
            }
        }
    }

    window.onclick = (e) => {
        if (e.target == document.getElementById('userModal')) {
            closeUserModal();
        }
    }

    console.log('✅ User management page loaded successfully');
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>