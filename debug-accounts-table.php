<?php
/**
 * Debug Account Management Table Issues
 */

session_start();
require_once 'includes/db_connection.php';
require_once 'includes/account-manager.php';

echo "<h2>🔍 Account Management Table Debug</h2>";

// 1. Check session
echo "<h3>1. Session Information:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<p>✅ User logged in</p>";
    echo "<ul>";
    echo "<li><strong>User ID:</strong> " . $_SESSION['user_id'] . "</li>";
    echo "<li><strong>User Role:</strong> " . ($_SESSION['user_role'] ?? 'Not set') . "</li>";
    echo "<li><strong>User Name:</strong> " . ($_SESSION['user_name'] ?? 'Not set') . "</li>";
    echo "</ul>";
    
    $currentUserRole = $_SESSION['user_role'] ?? '';
    
    // Check if admin
    if ($currentUserRole === 'admin') {
        echo "<p style='color: green;'>✅ User has admin role - Account table should show</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ User role is '$currentUserRole' - Only admins can see account management</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No user logged in</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    exit;
}

// 2. Test account manager
echo "<h3>2. Testing Account Manager:</h3>";
try {
    $accounts = $accountManager->getAccounts();
    echo "<p>✅ Account Manager works</p>";
    echo "<p><strong>Found " . count($accounts) . " accounts:</strong></p>";
    
    if (empty($accounts)) {
        echo "<p style='color: red;'>❌ No accounts found in database!</p>";
        
        // Check if accounts table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'accounts'");
        if ($stmt->rowCount() == 0) {
            echo "<p style='color: red;'>❌ Accounts table doesn't exist! Run database/accounts_system.sql</p>";
        } else {
            echo "<p>✅ Accounts table exists but is empty</p>";
            
            // Create test admin account
            echo "<p>Creating admin account...</p>";
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
            echo "<p style='color: green;'>✅ Admin account created!</p>";
            
            // Refresh accounts
            $accounts = $accountManager->getAccounts();
        }
    }
    
    // Display accounts
    if (!empty($accounts)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f5f5f5;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Name</th>";
        echo "<th style='padding: 8px;'>Email</th>";
        echo "<th style='padding: 8px;'>Role</th>";
        echo "<th style='padding: 8px;'>Status</th>";
        echo "<th style='padding: 8px;'>Created</th>";
        echo "</tr>";
        
        foreach ($accounts as $account) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>{$account['id']}</td>";
            echo "<td style='padding: 8px;'>{$account['first_name']} {$account['last_name']}</td>";
            echo "<td style='padding: 8px;'>{$account['email']}</td>";
            echo "<td style='padding: 8px;'>{$account['role']}</td>";
            echo "<td style='padding: 8px;'>{$account['status']}</td>";
            echo "<td style='padding: 8px;'>" . date('M j, Y', strtotime($account['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Account Manager Error: " . $e->getMessage() . "</p>";
}

// 3. Check database directly
echo "<h3>3. Direct Database Check:</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM accounts");
    $result = $stmt->fetch();
    echo "<p>Total accounts in database: " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM accounts GROUP BY role");
    $roles = $stmt->fetchAll();
    
    echo "<p><strong>Accounts by role:</strong></p>";
    echo "<ul>";
    foreach ($roles as $role) {
        echo "<li>{$role['role']}: {$role['count']}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

// 4. Test the admin page condition
echo "<h3>4. Admin Page Condition Test:</h3>";
if (isset($currentUserRole) && $currentUserRole === 'admin') {
    echo "<p style='color: green;'>✅ Condition passes - Account management section should be visible</p>";
    
    echo "<h4>Simulated Account Management Section:</h4>";
    echo "<div style='border: 2px solid green; padding: 15px; background: #f0f8f0;'>";
    echo "<h5>Account Management</h5>";
    echo "<p>This is what should appear on the admin page...</p>";
    
    if (!empty($accounts)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
        echo "<tr style='background: #e8f5e8;'>";
        echo "<th style='padding: 8px;'>Name</th>";
        echo "<th style='padding: 8px;'>Email</th>";
        echo "<th style='padding: 8px;'>Role</th>";
        echo "<th style='padding: 8px;'>Status</th>";
        echo "</tr>";
        
        foreach ($accounts as $account) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>{$account['first_name']} {$account['last_name']}</td>";
            echo "<td style='padding: 8px;'>{$account['email']}</td>";
            echo "<td style='padding: 8px;'>{$account['role']}</td>";
            echo "<td style='padding: 8px;'>{$account['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Condition fails - Account management section will be hidden</p>";
    echo "<p><strong>Current role:</strong> " . ($currentUserRole ?? 'undefined') . "</p>";
    echo "<p><strong>Required role:</strong> admin</p>";
}

// 5. Solutions
echo "<h3>5. Solutions:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px;'>";
echo "<h4>If account table is not showing:</h4>";
echo "<ol>";
echo "<li><strong>Make sure you're logged in as admin:</strong> Use admin@bulwagang-ncst.com / admin123</li>";
echo "<li><strong>Check your session:</strong> Your role must be 'admin' (not 'moderator')</li>";
echo "<li><strong>Clear browser cache:</strong> Hard refresh (Ctrl+F5) the admin page</li>";
echo "<li><strong>Check database:</strong> Make sure accounts table exists and has data</li>";
echo "</ol>";

echo "<h4>Quick fixes:</h4>";
echo "<ul>";
echo "<li><a href='login.php'>Login as Admin</a></li>";
echo "<li><a href='admin.php'>Go to Admin Page</a></li>";
echo "<li><a href='debug-login.php'>Debug Login Issues</a></li>";
echo "</ul>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { text-align: left; }
</style>
