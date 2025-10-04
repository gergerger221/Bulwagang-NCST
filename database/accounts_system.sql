-- Bulwagang NCST Account Management System
-- Database schema for role-based account system

-- Create accounts table
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('admin', 'moderator', 'member') NOT NULL DEFAULT 'member',
  `status` enum('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `email_verified` boolean DEFAULT FALSE,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  KEY `fk_created_by` (`created_by`),
  CONSTRAINT `fk_accounts_created_by` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create role permissions table
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('admin', 'moderator', 'member') NOT NULL,
  `permission` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role`, `permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create account activity log table
CREATE TABLE IF NOT EXISTS `account_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_activity_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default permissions for each role
INSERT INTO `role_permissions` (`role`, `permission`, `description`) VALUES
-- Admin permissions
('admin', 'system.full_access', 'Full system access and control'),
('admin', 'accounts.create_admin', 'Create new admin accounts'),
('admin', 'accounts.create_moderator', 'Create new moderator accounts'),
('admin', 'accounts.create_member', 'Create new member accounts'),
('admin', 'accounts.view_all', 'View all user accounts'),
('admin', 'accounts.edit_all', 'Edit all user accounts'),
('admin', 'accounts.delete_all', 'Delete any user account'),
('admin', 'auditions.view_all', 'View all audition submissions'),
('admin', 'auditions.approve', 'Approve audition submissions'),
('admin', 'auditions.reject', 'Reject audition submissions'),
('admin', 'auditions.delete', 'Delete audition submissions'),
('admin', 'system.settings', 'Access system settings'),
('admin', 'reports.view_all', 'View all system reports'),

-- Moderator permissions  
('moderator', 'accounts.create_member', 'Create new member accounts'),
('moderator', 'accounts.view_members', 'View member accounts'),
('moderator', 'accounts.edit_members', 'Edit member accounts'),
('moderator', 'auditions.view_all', 'View all audition submissions'),
('moderator', 'auditions.approve', 'Approve audition submissions'),
('moderator', 'auditions.reject', 'Reject audition submissions'),
('moderator', 'reports.view_auditions', 'View audition reports'),

-- Member permissions
('member', 'profile.view_own', 'View own profile'),
('member', 'profile.edit_own', 'Edit own profile'),
('member', 'home.access', 'Access home page'),
('member', 'auditions.view_own', 'View own audition status');

-- Insert default admin account (password: admin123)
-- Password hash for 'admin123' using PHP password_hash()
INSERT INTO `accounts` (`email`, `password_hash`, `first_name`, `last_name`, `role`, `status`, `email_verified`, `created_at`) VALUES
('admin@bulwagang-ncst.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active', TRUE, NOW());

-- Add indexes for better performance
CREATE INDEX `idx_accounts_role_status` ON `accounts` (`role`, `status`);
CREATE INDEX `idx_accounts_created_at` ON `accounts` (`created_at`);
CREATE INDEX `idx_accounts_last_login` ON `accounts` (`last_login`);

-- Update pending_audition table to link with accounts
ALTER TABLE `pending_audition` 
ADD COLUMN `account_id` int(11) DEFAULT NULL AFTER `id`,
ADD COLUMN `approved_by` int(11) DEFAULT NULL AFTER `status`,
ADD COLUMN `approved_at` timestamp NULL DEFAULT NULL AFTER `approved_by`,
ADD COLUMN `rejection_reason` text DEFAULT NULL AFTER `approved_at`,
ADD KEY `fk_audition_account_id` (`account_id`),
ADD KEY `fk_audition_approved_by` (`approved_by`),
ADD CONSTRAINT `fk_audition_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_audition_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

-- Create email templates table for automated emails
CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL UNIQUE,
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL,
  `body_text` text NOT NULL,
  `variables` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_template_name` (`template_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default email templates
INSERT INTO `email_templates` (`template_name`, `subject`, `body_html`, `body_text`, `variables`) VALUES
('new_member_account', 'Welcome to Bulwagang NCST - Your Account Details', 
'<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
<h2 style="color: #2c3e50; text-align: center;">🎭 Welcome to Bulwagang NCST!</h2>
<p>Dear {{first_name}} {{last_name}},</p>
<p>Congratulations! Your audition has been <strong style="color: #27ae60;">APPROVED</strong> and we have created your member account.</p>
<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
<h3 style="color: #2c3e50; margin-top: 0;">Your Account Details:</h3>
<p><strong>Email:</strong> {{email}}</p>
<p><strong>Temporary Password:</strong> <code style="background: #e9ecef; padding: 4px 8px; border-radius: 4px;">{{password}}</code></p>
<p><strong>Login URL:</strong> <a href="{{login_url}}">{{login_url}}</a></p>
</div>
<div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
<p style="margin: 0;"><strong>⚠️ Important:</strong> Please change your password after your first login for security purposes.</p>
</div>
<p>Welcome to the Bulwagang NCST family! We look forward to working with you.</p>
<p>Best regards,<br>The Bulwagang NCST Team</p>
</div></body></html>',
'Welcome to Bulwagang NCST!

Dear {{first_name}} {{last_name}},

Congratulations! Your audition has been APPROVED and we have created your member account.

Your Account Details:
Email: {{email}}
Temporary Password: {{password}}
Login URL: {{login_url}}

IMPORTANT: Please change your password after your first login for security purposes.

Welcome to the Bulwagang NCST family! We look forward to working with you.

Best regards,
The Bulwagang NCST Team',
'{"first_name": "string", "last_name": "string", "email": "string", "password": "string", "login_url": "string"}'),

('new_moderator_account', 'Bulwagang NCST - Moderator Account Created', 
'<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
<h2 style="color: #2c3e50; text-align: center;">🎭 Bulwagang NCST Moderator Access</h2>
<p>Dear {{first_name}} {{last_name}},</p>
<p>You have been granted <strong style="color: #e74c3c;">MODERATOR</strong> access to the Bulwagang NCST system.</p>
<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
<h3 style="color: #2c3e50; margin-top: 0;">Your Account Details:</h3>
<p><strong>Email:</strong> {{email}}</p>
<p><strong>Temporary Password:</strong> <code style="background: #e9ecef; padding: 4px 8px; border-radius: 4px;">{{password}}</code></p>
<p><strong>Admin Panel URL:</strong> <a href="{{admin_url}}">{{admin_url}}</a></p>
</div>
<div style="background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;">
<h4 style="color: #155724; margin-top: 0;">Your Moderator Permissions:</h4>
<ul style="color: #155724;">
<li>Manage audition submissions</li>
<li>Approve/reject auditions</li>
<li>Create member accounts</li>
<li>View audition reports</li>
</ul>
</div>
<p>Please change your password after your first login.</p>
<p>Best regards,<br>The Bulwagang NCST Admin Team</p>
</div></body></html>',
'Bulwagang NCST - Moderator Account Created

Dear {{first_name}} {{last_name}},

You have been granted MODERATOR access to the Bulwagang NCST system.

Your Account Details:
Email: {{email}}
Temporary Password: {{password}}
Admin Panel URL: {{admin_url}}

Your Moderator Permissions:
- Manage audition submissions
- Approve/reject auditions  
- Create member accounts
- View audition reports

Please change your password after your first login.

Best regards,
The Bulwagang NCST Admin Team',
'{"first_name": "string", "last_name": "string", "email": "string", "password": "string", "admin_url": "string"}');

-- Create sessions table for better session management
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` varchar(128) NOT NULL,
  `account_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `is_active` boolean DEFAULT TRUE,
  PRIMARY KEY (`id`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `fk_sessions_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
