<?php
/**
 * Test script to check if accounts exist in database
 */

require_once 'includes/db_connection.php';

try {
    // Check if accounts table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'accounts'");
    if ($stmt->rowCount() == 0) {
        echo "<h2>❌ Accounts table does not exist!</h2>";
        echo "<p>Please run the SQL file: database/accounts_system.sql</p>";
        exit;
    }
    
    echo "<h2>✅ Accounts table exists</h2>";
    
    // Get all accounts
    $stmt = $pdo->query("SELECT id, email, first_name, last_name, role, status, created_at FROM accounts ORDER BY created_at DESC");
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($accounts)) {
        echo "<h3>❌ No accounts found in database</h3>";
        echo "<p>Creating default admin account...</p>";
        
        // Create default admin account
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO accounts (email, password_hash, first_name, last_name, role, status, email_verified) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'admin@bulwagang-ncst.com',
            $passwordHash,
            'System',
            'Administrator',
            'admin',
            'active',
            1
        ]);
        
        echo "<p>✅ Default admin account created!</p>";
        echo "<p><strong>Email:</strong> admin@bulwagang-ncst.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        
        // Refresh to show the new account
        $stmt = $pdo->query("SELECT id, email, first_name, last_name, role, status, created_at FROM accounts ORDER BY created_at DESC");
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo "<h3>📋 Current Accounts:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f5f5f5;'>";
    echo "<th style='padding: 10px;'>ID</th>";
    echo "<th style='padding: 10px;'>Email</th>";
    echo "<th style='padding: 10px;'>Name</th>";
    echo "<th style='padding: 10px;'>Role</th>";
    echo "<th style='padding: 10px;'>Status</th>";
    echo "<th style='padding: 10px;'>Created</th>";
    echo "</tr>";
    
    foreach ($accounts as $account) {
        $roleColor = [
            'admin' => '#dc3545',
            'moderator' => '#007bff', 
            'member' => '#6c757d'
        ][$account['role']] ?? '#6c757d';
        
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$account['id']}</td>";
        echo "<td style='padding: 8px;'>{$account['email']}</td>";
        echo "<td style='padding: 8px;'>{$account['first_name']} {$account['last_name']}</td>";
        echo "<td style='padding: 8px; color: {$roleColor}; font-weight: bold;'>" . ucfirst($account['role']) . "</td>";
        echo "<td style='padding: 8px;'>" . ucfirst($account['status']) . "</td>";
        echo "<td style='padding: 8px;'>" . date('M j, Y g:i A', strtotime($account['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🔑 Test Login Credentials:</h3>";
    echo "<ul>";
    foreach ($accounts as $account) {
        if ($account['email'] === 'admin@bulwagang-ncst.com') {
            echo "<li><strong>Admin:</strong> {$account['email']} / admin123</li>";
        } elseif ($account['role'] === 'moderator') {
            echo "<li><strong>Moderator:</strong> {$account['email']} / (check email log for password)</li>";
        }
    }
    echo "</ul>";
    
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Database Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and make sure the accounts_system.sql file has been executed.</p>";
}
?>
