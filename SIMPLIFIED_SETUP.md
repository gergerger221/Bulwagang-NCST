# 🎭 Bulwagang NCST - Simplified Account System

## ✅ **System Overview**

The account system has been simplified:
- **All users redirect to home page after login**
- **Admin button only shows for admin/moderator roles**
- **No account management interface** (accounts managed via database)
- **Simple, streamlined workflow**

---

## 🚀 **Quick Setup**

### 1. **Run the SQL File**
Execute this in your MySQL database (phpMyAdmin or command line):

```sql
-- Select your database
USE bulwagan_db;

-- Run the accounts system SQL
SOURCE C:/xampp/htdocs/Project(1)/database/accounts_system.sql;
```

### 2. **Default Login Credentials**

**Admin Account:**
- **Email:** `admin@bulwagang-ncst.com`
- **Password:** `admin123`

---

## 📱 **How It Works**

### **Login Flow:**
1. User goes to `/login.php`
2. Enters email and password
3. **All users redirect to `/view.php` (home page)**
4. Navigation shows role-based options

### **Navigation Display:**

#### **Not Logged In:**
- Login button
- Form
- About Us
- Contact

#### **Logged In (Member):**
- Form
- About Us
- Contact
- User dropdown (Profile, Logout)

#### **Logged In (Admin/Moderator):**
- **Admin button** ✅ (visible)
- Form
- About Us
- Contact
- User dropdown (Profile, Logout)

---

## 👥 **Account Roles**

### 🔐 **Admin**
- Full system access
- Can view/manage auditions
- Can approve/reject submissions
- Admin dashboard access

### 👮‍♀️ **Moderator**
- Can view/manage auditions
- Can approve/reject submissions
- Admin dashboard access
- Limited permissions

### 👤 **Member**
- Can view home page
- Can edit profile
- Can submit auditions
- No admin access

---

## 🔧 **Managing Accounts**

Accounts are managed directly in the database:

### **Create New Account (via phpMyAdmin):**
```sql
INSERT INTO accounts (email, password_hash, first_name, last_name, role, status, email_verified) 
VALUES (
    'user@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    'First',
    'Last',
    'moderator', -- or 'admin' or 'member'
    'active',
    1
);
```

### **Change User Role:**
```sql
UPDATE accounts 
SET role = 'admin' 
WHERE email = 'user@example.com';
```

### **Activate/Deactivate Account:**
```sql
UPDATE accounts 
SET status = 'inactive' 
WHERE email = 'user@example.com';
```

### **Reset Password:**
```sql
-- Password will be: admin123
UPDATE accounts 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'user@example.com';
```

---

## 🎯 **Testing the System**

### 1. **Test Login:**
```
1. Go to: http://localhost/Project(1)/login.php
2. Login with: admin@bulwagang-ncst.com / admin123
3. Should redirect to: http://localhost/Project(1)/view.php
4. Navigation should show "Admin" button
```

### 2. **Test Admin Access:**
```
1. After logging in, click "Admin" button in navigation
2. Should access admin dashboard
3. Can view/manage audition submissions
```

### 3. **Test Member Login:**
```
1. Create member account in database
2. Login with member credentials
3. Should redirect to home page
4. No "Admin" button in navigation
```

---

## 📂 **Key Files Modified**

- ✅ `api/authenticate.php` - All users redirect to view.php
- ✅ `includes/navigation.php` - Admin button shows based on role
- ✅ `admin.php` - Removed account management section
- ✅ `view.php` - Added session check for navigation

---

## 🔒 **Security Features**

- **Password hashing** with bcrypt
- **Session-based authentication**
- **Role-based access control**
- **Protected admin routes**
- **Logout functionality**

---

## 🆘 **Troubleshooting**

### **Can't Login:**
1. Run the SQL file to create accounts table
2. Verify admin account exists in database
3. Clear browser cache (Ctrl+F5)

### **Admin Button Not Showing:**
1. Check you're logged in as admin or moderator
2. Verify session is active (check browser console)
3. Refresh the page

### **Access Denied to Admin Page:**
1. Verify your account role is 'admin' or 'moderator'
2. Check database: `SELECT role FROM accounts WHERE email = 'your@email.com'`

---

## ✨ **System Benefits**

✅ **Simplified** - No complex account management UI  
✅ **Fast** - Direct database access for admin tasks  
✅ **Secure** - Role-based permissions enforced  
✅ **User-friendly** - Single home page for all users  
✅ **Flexible** - Easy to modify roles via database  

---

## 📞 **Quick Reference**

**Login URL:** `http://localhost/Project(1)/login.php`  
**Home URL:** `http://localhost/Project(1)/view.php`  
**Admin URL:** `http://localhost/Project(1)/admin.php`  

**Admin Login:** `admin@bulwagang-ncst.com` / `admin123`

---

**System Status:** ✅ Simplified and Ready to Use!
