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

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : 'user';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validation
if (empty($full_name)) {
    echo json_encode(['success' => false, 'message' => 'Full name is required']);
    exit();
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email is required']);
    exit();
}

if (!in_array($role, ['user', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
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
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit();
    }
    
    if ($user_id > 0) {
        // Update existing user
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, mobile = ?, address = ?, role = ?, password = ?
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $phone, $address, $role, $hashed_password, $user_id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, mobile = ?, address = ?, role = ?
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $phone, $address, $role, $user_id]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully',
            'user_id' => $user_id
        ]);
    } else {
        // Create new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, mobile, address, password, role, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$full_name, $email, $phone, $address, $hashed_password, $role]);
                
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $pdo->lastInsertId()
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error saving user: ' . $e->getMessage()
    ]);
}
?>