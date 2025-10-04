<?php
// Start session to check user role
session_start();

// Set page-specific variables
$current_page = 'home';
$nav_type = 'topnav';

// Require login to access home page
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php?error=Please+log+in');
    exit;
}

// Include head component
include 'includes/head.php';
?>

<?php 
// Include navigation component
include 'includes/navigation.php'; 
?>

<!-- Main Content -->
<div class="container">
    <!-- Left Sidebar -->
    <div class="left-container">
        <div class="sidebar">
            <h3>Menu</h3>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Members</a></li>
                <li><a href="#">Performances</a></li>
                <li><a href="#">Albums</a></li>
                <?php 
                // Check if user is admin or moderator
                $showAdmin = isset($_SESSION['logged_in']) && 
                             $_SESSION['logged_in'] && 
                             isset($_SESSION['user_role']) && 
                             in_array($_SESSION['user_role'], ['admin', 'moderator']);
                
                if ($showAdmin): 
                ?>
                <li>
                    <a href="admin.php" style="color: #dc3545; font-weight: bold;" id="adminLink">
                        <i class="fas fa-user-shield"></i> Admin
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="#" id="helpDeskLink">
                        <i class="fa-solid fa-headset"></i> Help Desk
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Middle Container -->
    <div class="middle-container">
        <div class="card post">
            <h2>AUDITION PASSED!</h2>
            <div class="post-gallery">
                <img src="asset/img/aud.jpg" alt="Rehearsal 1" class="post-img">
                <img src="asset/img/aud1.jpg" alt="Rehearsal 2" class="post-img">
            </div>
            <p>
                Congratulations to our 1st batch of talented bands, singers, and
                solo musicians who made it!
            </p>
            <small>Posted on: 2025-09-04</small>
        </div>

        <div class="card post">
            <h2>RESCHEDULING DANCERS AUDITION</h2>
            <img src="asset/img/resched.jpg" alt="Dancer Jane" class="post-img">
            <p>
                ANNOUNCEMENT: DANCE AUDITION RESCHEDULING 📢 Please be advised that
                the previously scheduled audition has been moved.
            </p>
            <small>Posted on: 2025-09-05</small>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="right-container">
        <div class="card">
            <h3>Calendar</h3>
            <div id="calendar-gui"></div>
        </div>
        <div class="card">
            <h3>Date & Time</h3>
            <p id="datetime"></p>
        </div>
        <div class="card">
            <h3>Events on Selected Date</h3>
            <ul id="event-list">
                <li>Select a date to see events</li>
            </ul>
        </div>
    </div>
</div>

<div id="helpDeskPopup" class="help-desk-popup">
    <div class="help-desk-header">
        <h3>Help Desk</h3>
        <span id="closeHelpDesk">&times;</span>
    </div>

    <!-- Added body wrapper to match your CSS -->
    <div class="help-desk-body">
        <div class="messages"></div>
        <div class="help-desk-input">
            <textarea id="helpDeskMessage" placeholder="Type your message..."></textarea>
            <button id="helpDeskSend" type="button">Send</button>
        </div>
    </div>
</div>

<?php 
// Include footer component
include 'includes/footer.php'; 
?>
