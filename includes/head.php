<?php
// Include configuration
require_once 'config.php';

// Get page-specific data
$page_title = getPageTitle($current_page ?? '');
$css_files = getPageCSS($current_page ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="shortcut icon" type="x-icon" href="<?php echo $site_config['favicon']; ?>">
    
    <!-- Common Scripts -->
    <link rel="stylesheet" href="css/sweetalert2.min.css">
    <script src="js/sweetalert2.all.min.js"></script>
    
    <?php if ($current_page === 'home'): ?>
    <!-- FullCalendar for home page -->
    <script src="js/fullcalendar.global.min.js"></script>
    <?php endif; ?>
    
    <?php if ($current_page === 'view' || $current_page === 'admin'): ?>
    <!-- Font Awesome for view and admin pages -->
    <link rel="stylesheet" href="css/font-awesome.all.min.css">
    <?php endif; ?>
    
    <?php if ($current_page === 'admin'): ?>
    <!-- jQuery for admin page (required for DataTables) -->
    <script src="js/jquery-3.7.1.min.js"></script>
    <?php endif; ?>
    
    <!-- Page-specific CSS -->
    <?php foreach ($css_files as $css_file): ?>
    <link rel="stylesheet" href="<?php echo $css_file; ?>">
    <?php endforeach; ?>
    
    <?php if (isset($additional_css)): ?>
    <!-- Additional CSS -->
    <?php foreach ($additional_css as $css): ?>
    <link rel="stylesheet" href="<?php echo $css; ?>">
    <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
