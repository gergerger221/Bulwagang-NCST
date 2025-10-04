<?php
// Navigation component - supports different navigation styles
$nav_type = $nav_type ?? 'default';

if ($nav_type === 'topnav'): ?>
<!-- Top Navigation (for home and profile pages) -->
<nav class="topnav">
    <div class="logo"><?php echo $site_config['name']; ?></div>
    <div class="user-profile" id="user-profile">
        <img src="<?php echo $site_config['logo']; ?>" alt="User" class="profile-pic" id="profile-pic">
        <div class="dropdown" id="dropdown">
            <a href="profile.php">Profile</a>
            <a href="#" onclick="logout()">Logout</a>
        </div>
    </div>
    <div class="menu-toggle" id="menu-toggle">&#9776;</div>
</nav>

<?php elseif ($nav_type === 'bootstrap'): ?>
<!-- Bootstrap Navigation (for view page) -->
<nav class="topnav navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid px-3">
        <a id="logoLink" class="navbar-brand d-flex align-items-center" href="#">
            <img src="<?php echo $site_config['logo']; ?>" alt="Logo" class="img-fluid" style="height: 40px">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" 
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a href="login.php" class="nav-link">Login</a>
                </li>
                <li class="nav-item">
                    <a href="#" id="formLink" class="nav-link">Form</a>
                </li>
                <li class="nav-item">
                    <a href="#" id="aboutLink" class="nav-link">About Us</a>
                </li>
                <li class="nav-item">
                    <a href="#" id="contactLink" class="nav-link">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php endif; ?>

<?php if ($nav_type === 'topnav' && isset($show_back_button) && $show_back_button): ?>
<!-- Floating Back Button -->
<button id="backButton" class="floating-back-btn">&#8592; Back</button>
<?php endif; ?>
