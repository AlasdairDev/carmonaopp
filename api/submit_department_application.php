<?php
// FILE: api/submit_department_application.php
// FIXED VERSION - Uses PDO instead of MySQLi

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

// Log function
function writeLog($message) {
    $logFile = __DIR__ . '/../debug.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

try {
    writeLog("=== NEW APPLICATION SUBMISSION ===");
    writeLog("POST: " . json_encode($_POST));
    writeLog("FILES: " . json_encode(array_keys($_FILES)));
    writeLog("SESSION: " . json_encode([
        'user_id' => $_SESSION['user_id'] ?? 'none',
        'role' => $_SESSION['role'] ?? 'none'
    ]));
    
    // Load database config (uses PDO)
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/functions.php';
    
    writeLog("Config loaded");
    
    // Check session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not logged in. Please log in and try again.');
    }
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
        throw new Exception('Invalid role. Only regular users can submit applications.');
    }
    
    writeLog("Session check passed");
    
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    writeLog("Request method check passed");
    
    $user_id = $_SESSION['user_id'];
    
    // Get POST data
    $department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;
    $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
    $purpose = isset($_POST['purpose']) ? trim($_POST['purpose']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
    
    writeLog("Data - Dept: $department_id, Service: $service_id, Purpose length: " . strlen($purpose));
    
    // Validate required fields
    if (!$department_id || !$service_id) {
        throw new Exception("Department and Service are required");
    }
    
    if (empty($purpose)) {
        throw new Exception('Purpose is required');
    }
    
    writeLog("Validation passed");
    
    // Check file upload
    if (!isset($_FILES['compiled_document'])) {
        throw new Exception('Document file is required');
    }
    
    $file = $_FILES['compiled_document'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        throw new Exception($error_messages[$file['error']] ?? 'Unknown upload error');
    }
    
    // Validate file type
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_ext !== 'pdf') {
        throw new Exception('Only PDF files are allowed');
    }
    
    // Validate file size (10MB max)
    $max_size = 10 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        throw new Exception('File size exceeds 10MB limit');
    }
    
    writeLog("File validation passed - " . $file['name']);
    
    // Get service information using PDO
    $stmt = $pdo->prepare("
        SELECT s.*, d.name as department_name, d.code as department_code
        FROM services s 
        JOIN departments d ON s.department_id = d.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$service) {
        throw new Exception("Service not found");
    }
    
    writeLog("Service found: " . $service['service_name']);
    
    // Generate unique tracking number
    $tracking_number = 'CRMN-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
    
    // Ensure uniqueness
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE tracking_number = ?");
    $stmt->execute([$tracking_number]);
    while ($stmt->fetchColumn() > 0) {
        $tracking_number = 'CRMN-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $stmt->execute([$tracking_number]);
    }
    
    writeLog("Tracking number: $tracking_number");
    
    // Upload file with original filename preserved
    $upload_dir = __DIR__ . '/../assets/uploads/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }
    
    // Sanitize original filename
    $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
    $sanitized_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $original_name);
    
    // Create unique filename: OriginalName_YYYYMMDD_HHMMSS.pdf
    $new_filename = $sanitized_name . '_' . date('Ymd_His') . '.pdf';
    $upload_path = $upload_dir . $new_filename;
    $relative_path = 'assets/uploads/' . $new_filename;
    
    // Ensure filename is unique
    $counter = 1;
    while (file_exists($upload_path)) {
        $new_filename = $sanitized_name . '_' . date('Ymd_His') . '_' . $counter . '.pdf';
        $upload_path = $upload_dir . $new_filename;
        $relative_path = 'assets/uploads/' . $new_filename;
        $counter++;
    }
    
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to upload file');
    }
    
    writeLog("File uploaded: $relative_path");
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Insert application using PDO
        $stmt = $pdo->prepare("
            INSERT INTO applications (
                user_id, department_id, service_id, service_name, tracking_number,
                purpose, location, remarks, compiled_document, document_file_size,
                status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW(), NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $department_id,
            $service_id,
            $service['service_name'],
            $tracking_number,
            $purpose,
            $location,
            $remarks,
            $relative_path,
            $file['size']
        ]);
        
        $application_id = $pdo->lastInsertId();
        writeLog("Application inserted: ID $application_id");
        
        // Insert document record
        $stmt = $pdo->prepare("
            INSERT INTO documents (
                application_id, filename, file_path, 
                file_size, uploaded_at
            ) VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $application_id,
            $file['name'],
            $relative_path,
            $file['size']
        ]);
        
        writeLog("Document record inserted");
        
        // Create notification for user
        $stmt = $pdo->prepare("
            INSERT INTO notifications (
                user_id, application_id, title, message, type, is_read, created_at
            ) VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");

        $notification_title = 'Application Submitted Successfully';
        $notification_message = "Your application for {$service['service_name']} has been submitted. Tracking Number: {$tracking_number}";

        $stmt->execute([
            $user_id,
            $application_id,
            $notification_title,
            $notification_message,
            'success'
        ]);

        writeLog("User notification created");

        // Create notification for admins
        $admin_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin'");
        $admin_stmt->execute();
        $admins = $admin_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($admins as $admin) {
            $stmt->execute([
                $admin['id'],
                $application_id,
                'New Application Received',
                "New application submitted. Tracking Number: {$tracking_number}",
                'info'
            ]);
        }

        writeLog("Admin notifications created for " . count($admins) . " admins");
        
        // Log activity
        logActivity(
            $user_id,
            'Submit Application',
            "Submitted application: {$tracking_number} for {$service['service_name']}"
        );
        
        // Commit transaction
        $pdo->commit();
        
        writeLog("=== SUBMISSION SUCCESSFUL ===");
        
        // Success response
        echo json_encode([
            'success' => true,
            'message' => 'Application submitted successfully!',
            'tracking_number' => $tracking_number,
            'application_id' => $application_id,
            'service_name' => $service['service_name'],
            'department' => $service['department_name'],
            'original_filename' => $file['name'],
            'stored_filename' => $new_filename
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollBack();
        
        // Delete uploaded file
        if (file_exists($upload_path)) {
            @unlink($upload_path);
        }
        
        throw $e;
    }
    
} catch (Exception $e) {
    // Delete uploaded file if exists
    if (isset($upload_path) && file_exists($upload_path)) {
        @unlink($upload_path);
    }
    
    // Log error
    writeLog("ERROR: " . $e->getMessage());
    writeLog("Stack trace: " . $e->getTraceAsString());
    
    // Error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}