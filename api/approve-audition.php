<?php
/**
 * Approve Audition API Endpoint
 * Handles audition approval and automatic member account creation
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../includes/db_connection.php';
require_once '../includes/account-manager.php';

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Check if user has permission to approve auditions
$allowedRoles = ['admin', 'moderator'];
if (!in_array($_SESSION['user_role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Insufficient permissions'
    ]);
    exit;
}

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

if (!$input || !isset($input['audition_id']) || !isset($input['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Audition ID and action are required'
    ]);
    exit;
}

$auditionId = (int)$input['audition_id'];
$action = $input['action']; // 'approve' or 'reject'
$rejectionReason = $input['rejection_reason'] ?? null;
$currentUserId = $_SESSION['user_id'];

try {
    // Get audition details
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, phone, category, status 
        FROM pending_audition 
        WHERE id = ?
    ");
    $stmt->execute([$auditionId]);
    $audition = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$audition) {
        echo json_encode([
            'success' => false,
            'message' => 'Audition not found'
        ]);
        exit;
    }
    
    if ($audition['status'] !== 'pending') {
        echo json_encode([
            'success' => false,
            'message' => 'Audition has already been processed'
        ]);
        exit;
    }
    
    if ($action === 'approve') {
        // Approve audition and create member account
        $pdo->beginTransaction();
        
        try {
            // Update audition status
            $stmt = $pdo->prepare("
                UPDATE pending_audition 
                SET status = 'approved', approved_by = ?, approved_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$currentUserId, $auditionId]);
            
            // Create member account
            $result = $accountManager->createMemberFromAudition($auditionId, $currentUserId);
            
            if ($result['success']) {
                $pdo->commit();
                
                // Log the approval
                $accountManager->logActivity(
                    $currentUserId, 
                    'audition_approved', 
                    "Approved audition ID: $auditionId for {$audition['first_name']} {$audition['last_name']}"
                );
                
                echo json_encode([
                    'success' => true,
                    'message' => "Audition approved successfully! Member account created for {$audition['first_name']} {$audition['last_name']}. Welcome email sent to {$audition['email']}.",
                    'account_created' => true,
                    'audition' => [
                        'id' => $auditionId,
                        'name' => $audition['first_name'] . ' ' . $audition['last_name'],
                        'email' => $audition['email'],
                        'category' => $audition['category']
                    ]
                ]);
            } else {
                $pdo->rollback();
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create member account: ' . $result['message']
                ]);
            }
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
        
    } elseif ($action === 'reject') {
        // Reject audition
        $stmt = $pdo->prepare("
            UPDATE pending_audition 
            SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? 
            WHERE id = ?
        ");
        $stmt->execute([$currentUserId, $rejectionReason, $auditionId]);
        
        // Log the rejection
        $accountManager->logActivity(
            $currentUserId, 
            'audition_rejected', 
            "Rejected audition ID: $auditionId for {$audition['first_name']} {$audition['last_name']}. Reason: " . ($rejectionReason ?? 'No reason provided')
        );
        
        echo json_encode([
            'success' => true,
            'message' => "Audition rejected successfully.",
            'account_created' => false,
            'audition' => [
                'id' => $auditionId,
                'name' => $audition['first_name'] . ' ' . $audition['last_name'],
                'email' => $audition['email'],
                'category' => $audition['category'],
                'rejection_reason' => $rejectionReason
            ]
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action. Use "approve" or "reject".'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Audition approval error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing the audition.'
    ]);
}
?>
