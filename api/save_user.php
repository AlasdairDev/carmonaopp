<?php
require_once __DIR__ . '/../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
$full_name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : 'user';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
// ✅ ADD THIS - Get department_id from POST
$department_id = isset($_POST['department_id']) ? (int) $_POST['department_id'] : null;

// Validation
if (empty($full_name)) {
    echo json_encode(['success' => false, 'message' => 'Full name is required']);
    exit();
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email is required']);
    exit();
}

// ✅ UPDATE THIS - Add department_admin to valid roles
if (!in_array($role, ['user', 'admin', 'department_admin', 'superadmin'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit();
}

// ✅ ADD THIS - Validate department for department_admin
if ($role === 'department_admin' && empty($department_id)) {
    echo json_encode(['success' => false, 'message' => 'Department is required for Department Admin role']);
    exit();
}

// For new users, password is required
if ($user_id == 0 && empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Password is required for new users']);
    exit();
}

try {
    // Check if email already exists (for other users)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email address is already in use']);
        exit();
    }

    // Check if phone number already exists (if phone is provided)
    if (!empty($phone)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ? AND id != ?");
        $stmt->execute([$phone, $user_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Mobile number is already in use']);
            exit();
        }
    }

    if ($user_id > 0) {
        // ✅ UPDATE - Update existing user WITH department_id
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, mobile = ?, address = ?, role = ?, department_id = ?, password = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $phone, $address, $role, $department_id, $hashed_password, $user_id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, mobile = ?, address = ?, role = ?, department_id = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $phone, $address, $role, $department_id, $user_id]);
        }

        logActivity($_SESSION['user_id'], 'Update User', "Updated user: $full_name (ID: $user_id)");

        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully',
            'user_id' => $user_id
        ]);
    } else {
        // ✅ UPDATE - Create new user WITH department_id
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, mobile, address, password, role, department_id, is_active, is_verified, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, NOW())
        ");
        $stmt->execute([$full_name, $email, $phone, $address, $hashed_password, $role, $department_id]);

        $new_user_id = $pdo->lastInsertId();

        logActivity($_SESSION['user_id'], 'Create User', "Created new user: $full_name (ID: $new_user_id)");

        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $new_user_id
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error saving user: ' . $e->getMessage()
    ]);
}
?>