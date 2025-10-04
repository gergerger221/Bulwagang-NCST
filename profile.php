<?php
// Set page-specific variables
$current_page = 'profile';
$nav_type = 'topnav';
$show_back_button = true;

// Include head component
include 'includes/head.php';
?>

<?php 
// Include navigation component
include 'includes/navigation.php'; 
?>

<main>
    <h1>My Profile</h1>
    <div class="profile-container">
        <img src="asset/img/bg-png.png" alt="Profile Picture" class="profile-pic-preview" id="profile-preview">
        <button id="edit-profile-btn">Edit Profile</button>

        <form id="profile-form">
            <h2>Personal Info</h2>
            <label for="profile-name">Full Name</label>
            <input type="text" id="profile-name" value="John Doe" readonly>

            <label for="profile-role">Role</label>
            <input type="text" id="profile-role" value="Singer" readonly>

            <label for="profile-section">Section</label>
            <input type="text" id="profile-section" value="Vocal Group" readonly>

            <label for="profile-email">Email</label>
            <input type="email" id="profile-email" value="johndoe@example.com" readonly>
        </form>
    </div>

    <h2>Schedule</h2>
    <table id="schedule">
        <thead>
            <tr>
                <th>Day</th>
                <th>Subject</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Monday</td><td>Music Theory</td><td>9:00 - 10:30</td></tr>
            <tr><td>Tuesday</td><td>Vocal Training</td><td>10:45 - 12:15</td></tr>
            <tr><td>Wednesday</td><td>Rehearsals</td><td>1:00 - 3:00</td></tr>
        </tbody>
    </table>
</main>

<?php 
// Include footer component
include 'includes/footer.php'; 
?>
