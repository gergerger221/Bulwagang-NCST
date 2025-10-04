<?php
/**
 * Forgot Password API
 * Generates a temporary password and emails it to the account's email via SMTP.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/account-manager.php';
require_once __DIR__ . '/../includes/email-sender.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    $email = trim($input['email']);

    // Find account
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, status FROM accounts WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    // Always respond with success to avoid email enumeration
    $genericResponse = function() {
        echo json_encode([
            'success' => true,
            'message' => 'If the email exists in our system, a password reset email has been sent.'
        ]);
        exit;
    };

    if (!$account) {
        $genericResponse();
    }

    if ($account['status'] !== 'active') {
        $genericResponse();
    }

    // Generate temporary password
    $manager = new AccountManager($pdo);
    $tempPassword = $manager->generateRandomPassword(12);
    $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

    // Update password
    $upd = $pdo->prepare("UPDATE accounts SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
    $upd->execute([$passwordHash, $account['id']]);

    // Build email
    $loginUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/Project(1)/login.php';
    $subject = 'Password Reset - Bulwagang NCST';
    $bodyHtml = '<html><body style="font-family: Arial, sans-serif; line-height:1.6;color:#333;">'
        . '<div style="max-width:600px;margin:0 auto;padding:20px;">'
        . '<h2 style="color:#2c3e50;">Password Reset Instructions</h2>'
        . '<p>Dear ' . htmlspecialchars($account['first_name']) . ' ' . htmlspecialchars($account['last_name']) . ',</p>'
        . '<p>Your password has been reset. Use the temporary password below to login, then change it immediately in your profile settings.</p>'
        . '<div style="background:#f8f9fa;padding:16px;border-radius:8px;border:1px solid #e0e0e0;">'
        . '<p style="margin:0 0 8px 0;"><strong>Email:</strong> ' . htmlspecialchars($account['email']) . '</p>'
        . '<p style="margin:0;"><strong>Temporary Password:</strong> '
        . '<code style="background:#e9ecef;padding:4px 8px;border-radius:4px;">' . htmlspecialchars($tempPassword) . '</code></p>'
        . '</div>'
        . '<p style="margin-top:16px;"><strong>Login here:</strong> <a href="' . $loginUrl . '">' . $loginUrl . '</a></p>'
        . '<p style="color:#856404;background:#fff3cd;padding:10px;border-left:4px solid #ffc107;border-radius:6px;">For security, please change your password after your first login.</p>'
        . '<p>Best regards,<br>Bulwagang NCST</p>'
        . '</div></body></html>';
    $bodyText = 'Password Reset Instructions\n\n'
        . 'Dear ' . $account['first_name'] . ' ' . $account['last_name'] . ',\n'
        . 'Your password has been reset. Use the temporary password below to login, then change it immediately.\n\n'
        . 'Email: ' . $account['email'] . "\n"
        . 'Temporary Password: ' . $tempPassword . "\n"
        . 'Login URL: ' . $loginUrl . "\n\n"
        . 'Best regards,\nBulwagang NCST';

    $sender = new EmailSender();
    $sender->sendEmail($account['email'], $subject, $bodyHtml, $bodyText);

    $genericResponse();

} catch (Exception $e) {
    error_log('Forgot password error: ' . $e->getMessage());
    echo json_encode(['success' => true, 'message' => 'If the email exists, a password reset email has been sent.']);
}
