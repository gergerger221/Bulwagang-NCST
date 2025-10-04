<?php
/**
 * Account Management System
 * Handles creation, authentication, and management of user accounts
 */

require_once 'db_connection.php';
require_once 'email-sender.php';

class AccountManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Generate a secure random password
     */
    public function generateRandomPassword($length = 12) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $charactersLength = strlen($characters);
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $password;
    }
    
    /**
     * Create a new account
     */
    public function createAccount($email, $firstName, $lastName, $role, $phone = null, $createdBy = null) {
        try {
            // Check if email already exists
            $stmt = $this->pdo->prepare("SELECT id FROM accounts WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'An account with this email already exists.'
                ];
            }
            
            // Generate random password
            $plainPassword = $this->generateRandomPassword();
            $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
            
            // Insert new account
            $stmt = $this->pdo->prepare("
                INSERT INTO accounts (email, password_hash, first_name, last_name, role, phone, created_by, status, email_verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', FALSE)
            ");
            
            $stmt->execute([
                $email,
                $passwordHash,
                $firstName,
                $lastName,
                $role,
                $phone,
                $createdBy
            ]);
            
            $accountId = $this->pdo->lastInsertId();
            
            // Log account creation
            $this->logActivity($accountId, 'account_created', "Account created by user ID: " . ($createdBy ?? 'system'));
            
            // Send welcome email
            $this->sendWelcomeEmail($email, $firstName, $lastName, $plainPassword, $role);
            
            return [
                'success' => true,
                'message' => 'Account created successfully. Welcome email sent.',
                'account_id' => $accountId,
                'password' => $plainPassword // Return for immediate display if needed
            ];
            
        } catch (Exception $e) {
            error_log("Account creation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create account. Please try again.'
            ];
        }
    }
    
    /**
     * Create member account from approved audition
     */
    public function createMemberFromAudition($auditionId, $approvedBy) {
        try {
            // Get audition details
            $stmt = $this->pdo->prepare("
                SELECT first_name, last_name, email, phone 
                FROM pending_audition 
                WHERE id = ? AND status = 'approved'
            ");
            $stmt->execute([$auditionId]);
            $audition = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$audition) {
                return [
                    'success' => false,
                    'message' => 'Audition not found or not approved.'
                ];
            }
            
            // Create member account
            $result = $this->createAccount(
                $audition['email'],
                $audition['first_name'],
                $audition['last_name'],
                'member',
                $audition['phone'],
                $approvedBy
            );
            
            if ($result['success']) {
                // Link account to audition
                $stmt = $this->pdo->prepare("
                    UPDATE pending_audition 
                    SET account_id = ?, approved_by = ?, approved_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$result['account_id'], $approvedBy, $auditionId]);
                
                // Log the approval
                $this->logActivity($result['account_id'], 'audition_approved', "Audition approved and member account created");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Member creation from audition error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create member account from audition.'
            ];
        }
    }
    
    /**
     * Authenticate user login
     */
    public function authenticateUser($email, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, email, password_hash, first_name, last_name, role, status 
                FROM accounts 
                WHERE email = ? AND status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Update last login
                $stmt = $this->pdo->prepare("UPDATE accounts SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Log successful login
                $this->logActivity($user['id'], 'login_success', 'User logged in successfully');
                
                // Remove password hash from returned data
                unset($user['password_hash']);
                
                return [
                    'success' => true,
                    'user' => $user
                ];
            } else {
                // Log failed login attempt
                if ($user) {
                    $this->logActivity($user['id'], 'login_failed', 'Invalid password attempt');
                }
                
                return [
                    'success' => false,
                    'message' => 'Invalid email or password.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Authentication service unavailable.'
            ];
        }
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $permission) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT rp.permission 
                FROM accounts a 
                JOIN role_permissions rp ON a.role = rp.role 
                WHERE a.id = ? AND rp.permission = ?
            ");
            $stmt->execute([$userId, $permission]);
            
            return $stmt->fetch() !== false;
            
        } catch (Exception $e) {
            error_log("Permission check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user permissions
     */
    public function getUserPermissions($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT rp.permission, rp.description 
                FROM accounts a 
                JOIN role_permissions rp ON a.role = rp.role 
                WHERE a.id = ?
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get permissions error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log user activity
     */
    public function logActivity($accountId, $action, $description = null, $ipAddress = null, $userAgent = null) {
        try {
            $ipAddress = $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $stmt = $this->pdo->prepare("
                INSERT INTO account_activity_log (account_id, action, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$accountId, $action, $description, $ipAddress, $userAgent]);
            
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Send welcome email to new user
     */
    private function sendWelcomeEmail($email, $firstName, $lastName, $password, $role) {
        try {
            $templateName = ($role === 'member') ? 'new_member_account' : 'new_moderator_account';
            
            // Get email template
            $stmt = $this->pdo->prepare("SELECT subject, body_html, body_text FROM email_templates WHERE template_name = ?");
            $stmt->execute([$templateName]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$template) {
                error_log("Email template not found: " . $templateName);
                return false;
            }
            
            // Replace template variables
            $variables = [
                '{{first_name}}' => $firstName,
                '{{last_name}}' => $lastName,
                '{{email}}' => $email,
                '{{password}}' => $password,
                '{{login_url}}' => 'http://' . $_SERVER['HTTP_HOST'] . '/Project(1)/login.php',
                '{{admin_url}}' => 'http://' . $_SERVER['HTTP_HOST'] . '/Project(1)/admin.php'
            ];
            
            $subject = str_replace(array_keys($variables), array_values($variables), $template['subject']);
            $bodyHtml = str_replace(array_keys($variables), array_values($variables), $template['body_html']);
            $bodyText = str_replace(array_keys($variables), array_values($variables), $template['body_text']);
            
            // Send email using the email sender
            $emailSender = new EmailSender();
            return $emailSender->sendEmail($email, $subject, $bodyHtml, $bodyText);
            
        } catch (Exception $e) {
            error_log("Welcome email error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all accounts with pagination
     */
    public function getAccounts($role = null, $limit = 50, $offset = 0) {
        try {
            $sql = "
                SELECT a.*, creator.first_name as created_by_name, creator.last_name as created_by_lastname
                FROM accounts a 
                LEFT JOIN accounts creator ON a.created_by = creator.id
            ";
            $params = [];
            
            if ($role) {
                $sql .= " WHERE a.role = ?";
                $params[] = $role;
            }
            
            $sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Get accounts error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update account status
     */
    public function updateAccountStatus($accountId, $status, $updatedBy) {
        try {
            $stmt = $this->pdo->prepare("UPDATE accounts SET status = ? WHERE id = ?");
            $stmt->execute([$status, $accountId]);
            
            $this->logActivity($accountId, 'status_changed', "Status changed to: $status by user ID: $updatedBy");
            
            return [
                'success' => true,
                'message' => 'Account status updated successfully.'
            ];
            
        } catch (Exception $e) {
            error_log("Update account status error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update account status.'
            ];
        }
    }
}

// Initialize account manager
$accountManager = new AccountManager($pdo);
?>
