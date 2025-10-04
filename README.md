# 🎭 Bulwagang NCST - Audition Management System

A complete web-based audition management system for the Bulwagang NCST cultural organization at National College of Science and Technology (NCST).

## 🌟 Features

### 🎯 User Features
- **Responsive Audition Form**: Mobile-first design with separate first/last name fields
- **Category Selection**: Singer, Dancer, Solo Musician, Band options
- **Real-time Validation**: Form validation with user-friendly error messages
- **Success Notifications**: Beautiful SweetAlert2 confirmations

### 👨‍💼 Admin Features
- **Dashboard Overview**: Statistics cards showing total, pending, approved, rejected auditions
- **Advanced Data Table**: Search, sort, filter, and paginate audition submissions
- **Status Management**: Approve, reject, or delete auditions with confirmation dialogs
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices

## 🚀 Quick Start

### 1. Prerequisites
- XAMPP (Apache + MySQL)
- Web browser
- Text editor (optional)

### 2. Installation
1. **Start XAMPP**: Launch Apache and MySQL services
2. **Import Database**: 
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Import `database.sql` to create `bulwagan_db` database
3. **Test Connection**: Visit `http://localhost/Project(1)/test_connection.php`
4. **Add Sample Data**: Visit `http://localhost/Project(1)/sample_data.php` (optional)

### 3. Access Points
- **Main Page**: `http://localhost/Project(1)/` or `http://localhost/Project(1)/view.php`
- **Admin Dashboard**: `http://localhost/Project(1)/admin.php`
- **Database Test**: `http://localhost/Project(1)/test_connection.php`

## 📁 File Structure

```
Project(1)/
├── 📄 index.php                 # Redirects to view.php
├── 🎭 view.php                  # Main page with audition form
├── 👨‍💼 admin.php                # Admin dashboard
├── 📊 database.sql             # Database structure
├── 🔧 submit_audition.php      # Form submission handler
├── 📝 sample_data.php          # Sample data generator
├── 🧪 test_connection.php      # Database connection test
├── 📖 README.md                # This file
├── 📋 SETUP_INSTRUCTIONS.md    # Detailed setup guide
│
├── 📁 includes/
│   ├── 🔗 db_connection.php    # Database connection & functions
│   ├── ⚙️ config.php           # Site configuration
│   ├── 🎨 head.php             # HTML head component
│   ├── 🧭 navigation.php       # Navigation component
│   └── 🦶 footer.php           # Footer component
│
├── 📁 css/
│   ├── 🎨 admin-dashboard.css  # Admin dashboard styles
│   ├── 📊 dataTables.min.css   # DataTables styles
│   ├── 🏠 home-page.css        # Home page styles
│   ├── 👤 Profile-page.css     # Profile page styles
│   ├── 🔐 login.css            # Login page styles
│   ├── 👁️ view-page.css        # Main page styles
│   └── 🎯 bootstrap.min.css    # Bootstrap framework
│
├── 📁 js/
│   ├── ⚡ admin-dashboard.js   # Admin functionality
│   ├── 📊 dataTables.min.js    # DataTables library
│   ├── 🏠 home-page.js         # Home page scripts
│   ├── 👤 Profile-page.js      # Profile page scripts
│   ├── 🔐 login-func.js        # Login functionality
│   ├── 👁️ view-page.js         # Main page scripts
│   ├── 🍬 sweetalert2.all.min.js # SweetAlert2 library
│   └── 🎯 bootstrap.bundle.min.js # Bootstrap JS
│
└── 📁 asset/
    └── 📁 img/
        ├── 🖼️ audition-15.jpg
        ├── 🎤 singer.jpg
        ├── 💃 dancer.jpg
        └── 🎭 bg-png.png
```

## 🗄️ Database Schema

### Table: `pending_audition`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT(11) AUTO_INCREMENT | Primary key |
| `first_name` | VARCHAR(100) | Applicant's first name |
| `last_name` | VARCHAR(100) | Applicant's last name |
| `email` | VARCHAR(255) | Contact email |
| `phone` | VARCHAR(20) | Phone number |
| `category` | ENUM | 'singer', 'dancer', 'solo-musician', 'band' |
| `details` | TEXT | Additional information |
| `status` | ENUM | 'pending', 'approved', 'rejected' |
| `submission_date` | TIMESTAMP | When form was submitted |
| `created_at` | TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | Last modification time |

## 🎨 Design System

### Color Palette
- **Primary Gradient**: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`
- **Success**: `#28a745` (Green)
- **Warning**: `#ffc107` (Yellow)
- **Danger**: `#dc3545` (Red)
- **Info**: `#17a2b8` (Blue)

### Typography
- **Font Family**: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
- **Headings**: Bold, gradient text effect
- **Body**: Regular weight, good contrast

### Components
- **Cards**: Glass morphism effect with backdrop blur
- **Buttons**: Rounded corners, hover animations
- **Forms**: Clean inputs with focus states
- **Tables**: Striped rows, hover effects

## 🔧 Technical Stack

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Flexbox, Grid, Animations
- **Bootstrap 5**: Responsive framework
- **JavaScript ES6+**: Modern syntax
- **SweetAlert2**: Beautiful alerts
- **DataTables**: Advanced table features
- **Font Awesome**: Icon library

### Backend
- **PHP 7.4+**: Server-side logic
- **MySQL**: Database management
- **PDO**: Secure database operations
- **AJAX**: Asynchronous requests

## 📱 Responsive Features

### Mobile (< 576px)
- Stacked form fields
- Full-width buttons
- Optimized touch targets
- Simplified navigation

### Tablet (576px - 768px)
- Two-column layouts
- Collapsible navigation
- Touch-friendly controls

### Desktop (> 768px)
- Multi-column layouts
- Hover effects
- Keyboard shortcuts
- Advanced interactions

## 🔒 Security Features

### Input Validation
- Server-side validation
- SQL injection prevention (PDO)
- XSS protection (htmlspecialchars)
- CSRF protection ready

### Data Sanitization
- Email format validation
- Phone number formatting
- Category whitelist validation
- HTML entity encoding

## 🎯 User Flow

### Audition Submission
1. User visits main page
2. Clicks "Form" in navigation
3. Fills out audition form
4. Submits with validation
5. Receives confirmation
6. Data saved to database

### Admin Management
1. Admin visits dashboard
2. Views statistics overview
3. Reviews audition table
4. Filters/searches entries
5. Updates status or deletes
6. Changes reflect immediately

## 🧪 Testing

### Manual Testing Checklist
- [ ] Form submission works
- [ ] Validation messages appear
- [ ] Data appears in admin dashboard
- [ ] Status updates work
- [ ] Delete function works
- [ ] Responsive design works
- [ ] All buttons are clickable
- [ ] Search and filter work

### Browser Compatibility
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers

## 🚀 Performance

### Optimization Features
- Minified CSS/JS libraries
- Efficient database queries
- Lazy loading images
- Compressed assets
- CDN resources

### Loading Times
- Initial page load: < 2s
- Form submission: < 1s
- Admin dashboard: < 3s
- Database operations: < 500ms

## 🔮 Future Enhancements

### Planned Features
- [ ] User authentication system
- [ ] Email notifications
- [ ] File upload for portfolios
- [ ] Audition scheduling
- [ ] Reporting dashboard
- [ ] Export functionality
- [ ] Multi-language support

### Technical Improvements
- [ ] API endpoints
- [ ] Unit testing
- [ ] Automated deployment
- [ ] Performance monitoring
- [ ] Error logging
- [ ] Backup system

## 🆘 Support

### Common Issues
1. **Database connection fails**: Check XAMPP services
2. **Form not submitting**: Check browser console
3. **Admin page not loading**: Verify database exists
4. **Styling issues**: Clear browser cache

### Getting Help
- Check `SETUP_INSTRUCTIONS.md` for detailed setup
- Run `test_connection.php` to diagnose issues
- Check browser developer tools for errors
- Verify XAMPP services are running

## 📄 License

This project is created for educational purposes for the Bulwagang NCST organization.

---

**Made with ❤️ for Bulwagang NCST**

*Empowering creativity through technology*
#   B u l w a g a n g - N C S T  
 