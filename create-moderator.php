<?php
/**
 * Create Moderator Account Page
 * Only accessible by Admin users
 */

session_start();
require_once 'includes/db_connection.php';
require_once 'includes/account-manager.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get current user info
$currentUserId = $_SESSION['user_id'];
$currentUserName = $_SESSION['user_name'] ?? 'Admin';

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $message = 'First name, last name, and email are required.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        // Create moderator account
        $result = $accountManager->createAccount($email, $firstName, $lastName, 'moderator', $phone, $currentUserId);
        
        if ($result['success']) {
            $message = 'Moderator account created successfully! Welcome email sent to ' . $email;
            $messageType = 'success';
            
            // Clear form data on success
            $firstName = $lastName = $email = $phone = '';
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Moderator Account - Bulwagang NCST</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/sweetalert2.min.css">
    <script src="js/sweetalert2.all.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            padding-top: 50px;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .card-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .moderator-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="moderator-icon">👮‍♀️</div>
                        <h3 class="mb-0">Create Moderator Account</h3>
                        <p class="mb-0 mt-2">Admin-Only Access</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Current User Info -->
                        <div class="text-center mb-4">
                            <span class="admin-badge">🔐 Admin: <?php echo htmlspecialchars($currentUserName); ?></span>
                        </div>
                        
                        <!-- Success/Error Messages -->
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> mb-4">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Create Moderator Form -->
                        <form method="POST" id="createModeratorForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" 
                                           value="<?php echo htmlspecialchars($firstName ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" 
                                           value="<?php echo htmlspecialchars($lastName ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                <div class="form-text">The moderator will receive login credentials at this email.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="phone" class="form-label">Phone Number (Optional)</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                            </div>
                            
                            <!-- Moderator Permissions Info -->
                            <div class="alert alert-info mb-4">
                                <h6 class="alert-heading">👮‍♀️ Moderator Permissions:</h6>
                                <ul class="mb-0">
                                    <li>Manage audition submissions</li>
                                    <li>Approve/reject auditions</li>
                                    <li>Create member accounts</li>
                                    <li>View audition reports</li>
                                    <li>Access moderator dashboard</li>
                                </ul>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="admin.php" class="btn btn-secondary me-md-2">
                                    ← Back to Admin Panel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    🚀 Create Moderator Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Additional Info Card -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">📋 Account Creation Process:</h6>
                        <ol class="mb-0">
                            <li>System generates a secure random password</li>
                            <li>Account is created with moderator permissions</li>
                            <li>Welcome email is sent with login credentials</li>
                            <li>Moderator must change password on first login</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Form validation and submission
        document.getElementById('createModeratorForm').addEventListener('submit', function(e) {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!firstName || !lastName || !email) {
                e.preventDefault();
                Swal.fire({
                    title: 'Missing Information',
                    text: 'Please fill in all required fields.',
                    icon: 'warning',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                Swal.fire({
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address.',
                    icon: 'error',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            
            // Show loading state
            Swal.fire({
                title: 'Creating Account...',
                text: 'Please wait while we create the moderator account.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
        
        <?php if ($messageType === 'success'): ?>
        // Show success message
        setTimeout(() => {
            Swal.fire({
                title: 'Success! 🎉',
                text: '<?php echo addslashes($message); ?>',
                icon: 'success',
                confirmButtonColor: '#667eea'
            });
        }, 100);
        <?php endif; ?>
    </script>
</body>
</html>
