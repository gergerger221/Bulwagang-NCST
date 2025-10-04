<?php
/**
 * Logout API Endpoint
 * Destroys user session and redirects to login
 */

session_start();

// Log the logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    require_once '../includes/db_connection.php';
    require_once '../includes/account-manager.php';
    
    $accountManager->logActivity(
        $_SESSION['user_id'], 
        'logout', 
        'User logged out successfully'
    );
}

// Destroy all session data
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ../login.php?message=You have been logged out successfully');
exit;
?>
