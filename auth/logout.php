<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Log activity before destroying session
if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'Logout', 'User logged out');
}

// Destroy all session data
session_unset();
session_destroy();

// Start a new session for the flash message
session_start();

// Set a specific logout flag instead of generic success message
$_SESSION['logout_success'] = true;

// Redirect to login page using the redirect function
redirect('auth/login.php');
?>