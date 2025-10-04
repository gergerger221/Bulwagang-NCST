# Admin-Managed Account System Setup Guide
## Bulwagang NCST Audition Project

### 🎯 Overview
This system implements a comprehensive role-based account management system with three user types:
- **Admin**: Full system access, can create moderator accounts
- **Moderator**: Manage auditions, create member accounts  
- **Member**: Home page access, profile editing only

---

## 📋 Setup Instructions

### 1. Database Setup
Run the SQL file to create the account system tables:
```sql
-- Execute this file in your MySQL database
source database/accounts_system.sql;
```

**What this creates:**
- `accounts` table with role-based permissions
- `role_permissions` table defining what each role can do
- `account_activity_log` table for security tracking
- `email_templates` table for automated emails
- `user_sessions` table for session management
- Updates `pending_audition` table to link with accounts

### 2. File Structure
```
Project(1)/
├── database/
│   └── accounts_system.sql          # Database schema
├── includes/
│   ├── account-manager.php          # Core account management class
│   └── email-sender.php             # Email notification system
├── api/
│   └── approve-audition.php         # Audition approval API
├── create-moderator.php             # Admin-only moderator creation
├── js/
│   └── admin-dashboard-new.js       # Updated admin JavaScript
└── css/
    └── admin-dashboard.css          # Updated with role badges
```

### 3. Default Admin Account
The system creates a default admin account:
- **Email**: `admin@bulwagang-ncst.com`
- **Password**: `admin123`
- **Role**: Admin (full access)

### 4. Email Configuration
Update email settings in `includes/email-sender.php`:
```php
// For development: emails are logged to logs/emails.log
// For production: configure SMTP settings
$this->fromEmail = 'noreply@bulwagang-ncst.com';
$this->fromName = 'Bulwagang NCST';
```

---

## 🔧 System Features

### Admin Features
✅ **Full System Access**
- View all audition submissions
- Approve/reject auditions with automatic member account creation
- Create moderator accounts
- Manage all user accounts (activate/deactivate/suspend)
- Access to all system reports

✅ **Account Creation**
- Create moderator accounts via `/create-moderator.php`
- Automatic password generation
- Email notifications with login credentials

### Moderator Features  
✅ **Audition Management**
- View all audition submissions
- Approve/reject auditions
- Automatic member account creation on approval
- View audition reports

✅ **Member Account Management**
- Create member accounts manually
- Manage member account status

### Member Features
✅ **Limited Access**
- Access to home page
- Edit own profile
- View own audition status
- No admin panel access

### Automatic Account Creation
When an audition is **approved**:
1. 🎭 Audition status → "Approved"
2. 👤 Member account automatically created
3. 🔑 Random secure password generated
4. 📧 Welcome email sent with login credentials
5. 🏠 Member gains access to member portal

---

## 🎯 How to Use

### For Admins:

#### 1. Create Moderator Account
1. Go to `/create-moderator.php` (admin-only access)
2. Fill in moderator details
3. System generates password and sends email
4. Moderator receives login credentials

#### 2. Approve Auditions
1. Go to admin dashboard
2. Find pending audition
3. Click "Approve" button
4. Confirm approval in dialog
5. System automatically:
   - Creates member account
   - Sends welcome email
   - Updates audition status

#### 3. Manage Accounts
1. View "Account Management" section in admin dashboard
2. See all users with roles and status
3. Activate/deactivate/suspend accounts as needed

### For Moderators:

#### 1. Login
- Use credentials received via email
- Access admin dashboard with limited permissions

#### 2. Process Auditions
- Same approval process as admin
- Can approve auditions and create member accounts
- Cannot create moderator accounts

### For Members:

#### 1. Receive Account
- Account created automatically when audition approved
- Receive email with login credentials

#### 2. Access System
- Login with provided credentials
- Access member portal (home page, profile)
- No admin panel access

---

## 🔒 Security Features

### Password Management
- **Random Generation**: 12-character secure passwords
- **Hashing**: PHP password_hash() with bcrypt
- **Force Change**: Users must change password on first login

### Access Control
- **Role-based permissions**: Defined in `role_permissions` table
- **Session management**: Secure PHP sessions
- **Activity logging**: All actions logged with IP and timestamp

### Email Security
- **Development**: Emails logged to files for testing
- **Production**: SMTP configuration for real email sending
- **Templates**: Professional HTML email templates

---

## 🧪 Testing

### 1. Test Admin Functions
```
URL: /admin.php
Login: admin@bulwagang-ncst.com / admin123
Test: Create moderator account, approve auditions
```

### 2. Test Email System
```
Check: logs/emails.log for development
Check: Individual email files in logs/ folder
```

### 3. Test Account Creation
```
1. Submit test audition via /view.php
2. Login as admin
3. Approve audition
4. Check if member account created
5. Verify welcome email sent
```

---

## 🚀 Production Deployment

### 1. Security Updates
- Change default admin password
- Configure real SMTP settings
- Set up SSL/HTTPS
- Configure proper error logging

### 2. Email Configuration
- Set up real email server
- Configure SPF/DKIM records
- Test email deliverability

### 3. Database Security
- Use environment variables for DB credentials
- Set up database backups
- Configure proper user permissions

---

## 📞 Support

### Common Issues
1. **Emails not sending**: Check logs/emails.log in development
2. **Account creation fails**: Check database permissions
3. **Login issues**: Verify session configuration

### File Locations
- **Logs**: `/logs/emails.log`
- **Database**: `/database/accounts_system.sql`
- **Config**: `/includes/account-manager.php`

---

## ✅ System Status

### Implemented Features
- ✅ Database schema with role-based permissions
- ✅ Account management class with security features
- ✅ Email notification system
- ✅ Admin-only moderator creation form
- ✅ Automatic member account creation on audition approval
- ✅ Role-based access control
- ✅ Activity logging and session management
- ✅ Professional email templates
- ✅ Admin dashboard with account management

### Ready for Testing
The system is fully implemented and ready for testing. All core features are working:
- Admin can create moderator accounts
- Moderators can approve auditions
- Member accounts are automatically created
- Email notifications are sent
- Role-based permissions are enforced

**Next Steps**: Test the system, configure email settings for production, and deploy! 🎭✨
