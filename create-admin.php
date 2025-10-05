<?php
/**
 * One-time script to create or update an admin account.
 * After running successfully, DELETE this file for security.
 */

session_start();
require_once __DIR__ . '/includes/db_connection.php';

// ==== Configuration (from user request) ====
$targetEmail   = 'jovensanchez2210@gmail.com';
$plainPassword = 'benjobenjo221';
$firstName     = 'Admin'; // You can change after login in Profile page
$lastName      = 'User';  // You can change after login in Profile page
// ===========================================

header('Content-Type: text/html; charset=utf-8');

echo '<h2>🛠 Create/Update Admin Account</h2>';

echo '<p><strong>Email:</strong> ' . htmlspecialchars($targetEmail) . '</p>';

try {
    // Basic validation
    if (!filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format.');
    }
    if (strlen($plainPassword) < 8) {
        throw new Exception('Password must be at least 8 characters.');
    }

    // Ensure accounts table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'accounts'");
    if ($stmt->rowCount() === 0) {
        echo "<p style='color: red;'>❌ 'accounts' table not found.</p>";
        echo "<p><strong>Run this SQL first:</strong> database/accounts_system.sql</p>";
        exit;
    }

    $pdo->beginTransaction();

    // Check if account exists
    $select = $pdo->prepare("SELECT id, first_name, last_name FROM accounts WHERE email = ?");
    $select->execute([$targetEmail]);
    $existing = $select->fetch();

    $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

    if ($existing) {
        // Update existing account to admin with new password
        $update = $pdo->prepare("UPDATE accounts 
            SET password_hash = ?, role = 'admin', status = 'active', email_verified = 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?");
        $update->execute([$passwordHash, $existing['id']]);
        $accountId = (int)$existing['id'];
        $action = 'updated';
    } else {
        // Create new admin account
        $insert = $pdo->prepare("INSERT INTO accounts 
            (email, password_hash, first_name, last_name, role, status, email_verified) 
            VALUES (?, ?, ?, ?, 'admin', 'active', 1)");
        $insert->execute([$targetEmail, $passwordHash, $firstName, $lastName]);
        $accountId = (int)$pdo->lastInsertId();
        $action = 'created';
    }

    // Verify password hash works
    $verifyStmt = $pdo->prepare("SELECT password_hash FROM accounts WHERE id = ?");
    $verifyStmt->execute([$accountId]);
    $row = $verifyStmt->fetch();
    $verifyOk = $row ? password_verify($plainPassword, $row['password_hash']) : false;

    $pdo->commit();

    echo "<p style='color: green;'>✅ Admin account {$action} successfully.</p>";
    echo "<ul>";
    echo "<li><strong>Account ID:</strong> " . htmlspecialchars((string)$accountId) . "</li>";
    echo "<li><strong>Role:</strong> admin</li>";
    echo "<li><strong>Status:</strong> active</li>";
    echo "<li><strong>Password check:</strong> " . ($verifyOk ? "<span style='color:green;'>OK</span>" : "<span style='color:red;'>FAILED</span>") . "</li>";
    echo "</ul>";

    echo "<div style='background:#e8f5e9;padding:12px;border-radius:8px;margin-top:10px;'>";
    echo "<p><strong>Next:</strong> <a href='login.php'>Go to Login</a></p>";
    echo "<p><strong>Login with:</strong> " . htmlspecialchars($targetEmail) . " / " . htmlspecialchars($plainPassword) . "</p>";
    echo "</div>";

    echo "<div style='background:#fff3cd;padding:12px;border-radius:8px;margin-top:10px;'>";
    echo "<p>⚠️ <strong>Security:</strong> Delete this file (create-admin.php) after successful login.</p>";
    echo "</div>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
