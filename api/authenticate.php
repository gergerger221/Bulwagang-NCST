<?php
/**
 * Authentication API Endpoint
 * Handles user login with the new account system
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../includes/db_connection.php';
require_once '../includes/account-manager.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Also check for form data
if (!$input) {
    $input = $_POST;
}

if (!$input || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit;
}

$email = trim($input['email']);
$password = $input['password'];

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password cannot be empty'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address'
    ]);
    exit;
}

try {
    // Authenticate user
    $result = $accountManager->authenticateUser($email, $password);
    
    if ($result['success']) {
        $user = $result['user'];
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_status'] = $user['status'];
        $_SESSION['logged_in'] = true;
        
        // All users redirect to home page after login
        $redirectUrl = 'home.php';
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'role' => $user['role'],
                'status' => $user['status']
            ],
            'redirect' => $redirectUrl
        ]);
        
    } else {
        // Log failed login attempt
        error_log("Failed login attempt for email: $email from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
    
} catch (Exception $e) {
    error_log("Authentication error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Authentication service unavailable. Please try again later.'
    ]);
}
?>
