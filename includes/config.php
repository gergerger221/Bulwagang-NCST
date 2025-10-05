<?php
// Site Configuration
define('SITE_NAME', 'Bulwagang NCST');
define('SITE_LOGO', 'asset/img/bg-png.png');
define('BASE_URL', '/Bulwagang-NCST/');

// SMTP configuration (Gmail)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // TLS
define('SMTP_USERNAME', 'ver.smtp221@gmail.com');
define('SMTP_PASSWORD', 'lwzonwviegzghidw'); // App Password
define('SMTP_FROM_EMAIL', 'ver.smtp221@gmail.com');
define('SMTP_FROM_NAME', 'Bulwagang NCST');

// Common page data
$site_config = [
    'name' => SITE_NAME,
    'logo' => SITE_LOGO,
    'favicon' => SITE_LOGO,
    'base_url' => BASE_URL
];

// Function to get page title
function getPageTitle($page = '') {
    $titles = [
        'home' => 'Bulwagang NCST Organization',
        'profile' => 'My Profile',
        'login' => 'Login',
        'view' => 'Welcome!',
        'admin' => 'Admin Dashboard'
    ];
    
    return isset($titles[$page]) ? $titles[$page] : SITE_NAME;
}

// Function to get page-specific CSS files
function getPageCSS($page = '') {
    $css_files = [
        'home' => ['css/home-page.css'],
        'profile' => ['css/Profile-page.css'],
        'login' => ['css/bootstrap.min.css', 'css/login.css'],
        'view' => ['css/bootstrap.min.css', 'css/view-page.css'],
        'admin' => ['css/bootstrap.min.css', 'css/dataTables.min.css', 'css/admin-dashboard.css']
    ];
    
    return isset($css_files[$page]) ? $css_files[$page] : [];
}

// Function to get page-specific JS files
function getPageJS($page = '') {
    $js_files = [
        'home' => ['js/home-page.js'],
        'profile' => ['js/Profile-page.js'],
        'login' => ['js/bootstrap.bundle.min.js', 'js/login-func.js'],
        'view' => ['js/bootstrap.bundle.min.js', 'js/view-page.js'],
        'admin' => ['js/bootstrap.bundle.min.js', 'js/dataTables.min.js', 'js/admin-dashboard.js']
    ];
    
    return isset($js_files[$page]) ? $js_files[$page] : [];
}
?>
