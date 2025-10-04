<?php
/**
 * AJAX Email Validation Endpoint
 * Provides real-time email validation for the audition form
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include email validation library
require_once 'includes/email-validator.php';
require_once 'includes/db_connection.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'valid' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Also check for form data
if (!$input && isset($_POST['email'])) {
    $input = ['email' => $_POST['email']];
}

if (!$input || !isset($input['email'])) {
    echo json_encode([
        'valid' => false,
        'message' => 'Email parameter required'
    ]);
    exit;
}

$email = trim($input['email']);

// Basic validation first
if (empty($email)) {
    echo json_encode([
        'valid' => false,
        'message' => 'Email address is required'
    ]);
    exit;
}

try {
    // Check if email already exists in database
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT id FROM pending_audition WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode([
                'valid' => false,
                'message' => 'An audition with this email address already exists',
                'exists' => true
            ]);
            exit;
        }
    }
    
    // Validate email
    $validation = validateAuditionEmail($email);
    
    // Add timing information
    $validation['timestamp'] = date('Y-m-d H:i:s');
    
    // Log validation attempt (optional)
    error_log("Email validation: {$email} - " . ($validation['valid'] ? 'VALID' : 'INVALID') . " - " . $validation['message']);
    
    echo json_encode($validation);
    
} catch (Exception $e) {
    error_log("Email validation error: " . $e->getMessage());
    
    echo json_encode([
        'valid' => false,
        'message' => 'Validation service temporarily unavailable',
        'error' => 'server_error'
    ]);
}
?>
