<?php
/**
 * Debug Login Issues
 * Check database setup and test authentication
 */

require_once 'includes/db_connection.php';

echo "<h2>🔍 Login Debug Tool</h2>";

try {
    // 1. Check if accounts table exists
    echo "<h3>1. Checking Database Setup...</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'accounts'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Accounts table does not exist!</p>";
        echo "<p><strong>Solution:</strong> Run the SQL file: database/accounts_system.sql</p>";
        exit;
    }
    echo "<p style='color: green;'>✅ Accounts table exists</p>";
    
    // 2. Check if admin account exists
    echo "<h3>2. Checking Admin Account...</h3>";
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE email = ?");
    $stmt->execute(['admin@bulwagang-ncst.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "<p style='color: red;'>❌ Admin account does not exist!</p>";
        echo "<p>Creating admin account now...</p>";
        
        // Create admin account with correct password hash
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
        
        echo "<p style='color: green;'>✅ Admin account created successfully!</p>";
        
        // Get the newly created account
        $stmt = $pdo->prepare("SELECT * FROM accounts WHERE email = ?");
        $stmt->execute(['admin@bulwagang-ncst.com']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "<p style='color: green;'>✅ Admin account exists</p>";
    }
    
    // 3. Display admin account details
    echo "<h3>3. Admin Account Details:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><td><strong>ID:</strong></td><td>{$admin['id']}</td></tr>";
    echo "<tr><td><strong>Email:</strong></td><td>{$admin['email']}</td></tr>";
    echo "<tr><td><strong>Name:</strong></td><td>{$admin['first_name']} {$admin['last_name']}</td></tr>";
    echo "<tr><td><strong>Role:</strong></td><td>{$admin['role']}</td></tr>";
    echo "<tr><td><strong>Status:</strong></td><td>{$admin['status']}</td></tr>";
    echo "<tr><td><strong>Email Verified:</strong></td><td>" . ($admin['email_verified'] ? 'Yes' : 'No') . "</td></tr>";
    echo "<tr><td><strong>Created:</strong></td><td>{$admin['created_at']}</td></tr>";
    echo "</table>";
    
    // 4. Test password verification
    echo "<h3>4. Testing Password Verification...</h3>";
    $testPassword = 'admin123';
    $isValid = password_verify($testPassword, $admin['password_hash']);
    
    if ($isValid) {
        echo "<p style='color: green;'>✅ Password verification works! 'admin123' is correct.</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification failed!</p>";
        echo "<p>Updating password hash...</p>";
        
        // Update with fresh password hash
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE accounts SET password_hash = ? WHERE id = ?");
        $stmt->execute([$newHash, $admin['id']]);
        
        echo "<p style='color: green;'>✅ Password hash updated!</p>";
    }
    
    // 5. Test authentication API
    echo "<h3>5. Testing Authentication API...</h3>";
    
    $testData = [
        'email' => 'admin@bulwagang-ncst.com',
        'password' => 'admin123'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/Project(1)/api/authenticate.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response && $httpCode == 200) {
        $result = json_decode($response, true);
        if ($result && $result['success']) {
            echo "<p style='color: green;'>✅ Authentication API works!</p>";
            echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ Authentication API returned error:</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ Could not connect to authentication API</p>";
        echo "<p>HTTP Code: $httpCode</p>";
        if ($response) {
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }
    
    // 6. Final instructions
    echo "<h3>6. Login Instructions:</h3>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px;'>";
    echo "<p><strong>✅ Use these credentials to login:</strong></p>";
    echo "<p><strong>Email:</strong> admin@bulwagang-ncst.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>Login URL:</strong> <a href='login.php'>login.php</a></p>";
    echo "</div>";
    
    // 7. Check for common issues
    echo "<h3>7. Common Issues Check:</h3>";
    
    // Check if SweetAlert is loaded
    echo "<p>🔍 <strong>Browser Console:</strong> Check for JavaScript errors in browser developer tools (F12)</p>";
    echo "<p>🔍 <strong>Network Tab:</strong> Check if API calls are being made to authenticate.php</p>";
    echo "<p>🔍 <strong>Clear Cache:</strong> Try hard refresh (Ctrl+F5) on login page</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>
