<?php
// Start session to check user role
session_start();

// DB connection for posts
require_once 'includes/db_connection.php';

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

<?php
// Fetch latest posts
$posts = [];
try {
    // Ensure posts table may or may not exist; if not, skip without fatal
    $stmt = $pdo->query("SELECT p.*, a.first_name, a.last_name FROM posts p JOIN accounts a ON a.id = p.author_id ORDER BY p.created_at DESC LIMIT 50");
    $posts = $stmt->fetchAll();
} catch (Exception $e) {
    error_log('Fetch posts error: ' . $e->getMessage());
}
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
        <?php $canPost = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'moderator']);
        if ($canPost): ?>
            <div class="card" id="uploadCard">
                <div style="display:flex; align-items:center; justify-content: space-between; gap:12px;">
                    <h2 style="margin:0;">Create a post</h2>
                    <button id="toggleUploadBtn" type="button" style="padding:8px 14px;border:none;border-radius:8px;background:#667eea;color:#fff;cursor:pointer;">Upload</button>
                </div>
                <form id="uploadForm" style="display:none; margin-top:12px;" enctype="multipart/form-data" onsubmit="return false;">
                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:180px;">
                            <label for="postType"><strong>Post Type</strong></label>
                            <select id="postType" name="postType" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                                <option value="announcements">Announcements</option>
                                <option value="performances">Performances</option>
                                <option value="albums">Picture Albums</option>
                            </select>
                        </div>
                        <div style="flex:2; min-width:250px;">
                            <label for="postTitle"><strong>Title</strong></label>
                            <input type="text" id="postTitle" name="title" placeholder="Enter a title..." style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                        </div>
                    </div>
                    <div style="margin-top:10px;">
                        <label for="postContent"><strong>Content</strong></label>
                        <textarea id="postContent" name="content" rows="4" placeholder="Write something..." style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;"></textarea>
                    </div>
                    <div id="eventDateRow" style="margin-top:10px; display:none;">
                        <label for="eventDate"><strong>Event Date</strong></label>
                        <input type="datetime-local" id="eventDate" name="event_date" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                    <div id="fileRow" style="margin-top:10px;">
                        <label for="uploadFiles"><strong>Upload images/videos</strong></label>
                        <input type="file" id="uploadFiles" name="files[]" accept="image/*,video/*" multiple>
                        <div id="filePreviewList" class="upload-preview"></div>
                    </div>
                    <div style="margin-top:12px; display:flex; gap:8px;">
                        <button type="submit" id="submitUpload" style="padding:8px 14px;border:none;border-radius:8px;background:#28a745;color:#fff;cursor:pointer;">Post</button>
                        <button type="button" id="cancelUpload" style="padding:8px 14px;border:1px solid #ccc;border-radius:8px;background:#f8f9fa;color:#333;cursor:pointer;">Cancel</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <?php
                $title = htmlspecialchars($post['title'] ?? '');
                $content = nl2br(htmlspecialchars($post['content'] ?? ''));
                $author = htmlspecialchars(($post['first_name'] ?? '') . ' ' . ($post['last_name'] ?? ''));
                $created = isset($post['created_at']) ? date('M j, Y g:i A', strtotime($post['created_at'])) : '';
                $type = htmlspecialchars(ucfirst($post['type'] ?? 'Post'));
                $images = [];
                if (!empty($post['images'])) {
                    $decoded = json_decode($post['images'], true);
                    if (is_array($decoded)) {
                        $images = $decoded;
                    }
                }
                ?>
                <div class="card post" id="post-<?php echo (int)($post['id'] ?? 0); ?>">
                    <?php $canManage = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin','moderator']); ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                        <h2 style="margin:0;"><?php echo $title; ?></h2>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <?php $typeClass = 'post-type-' . htmlspecialchars(strtolower($post['type'] ?? 'post')); ?>
                            <span class="post-type-badge <?php echo $typeClass; ?>"><?php echo $type; ?></span>
                            <?php if ($canManage): ?>
                                <button class="post-btn btn-edit-post" 
                                        data-id="<?php echo (int)$post['id']; ?>" 
                                        data-type="<?php echo htmlspecialchars($post['type'] ?? '', ENT_QUOTES); ?>" 
                                        data-title="<?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES); ?>" 
                                        data-content="<?php echo htmlspecialchars($post['content'] ?? '', ENT_QUOTES); ?>" 
                                        data-event="<?php echo htmlspecialchars($post['event_date'] ?? '', ENT_QUOTES); ?>">Edit</button>
                                <button class="post-btn danger btn-delete-post" data-id="<?php echo (int)$post['id']; ?>">Delete</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($images)): ?>
                        <div class="post-gallery" style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                            <?php foreach ($images as $mediaPath): ?>
                                <?php 
                                    $path = (string)$mediaPath;
                                    $isVideo = (bool)preg_match('/\.(mp4|mov|mpeg|avi|wmv|3gp|3g2|webm|ogv)$/i', $path);
                                ?>
                                <?php if ($isVideo): ?>
                                    <video src="<?php echo htmlspecialchars($path); ?>" class="post-video" controls style="max-height:200px;border-radius:8px;object-fit:cover;"></video>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($path); ?>" alt="Post media" class="post-img" style="max-height:200px;object-fit:cover;border-radius:8px;">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($content)): ?>
                        <p><?php echo $content; ?></p>
                    <?php endif; ?>
                    <?php if (!empty($post['event_date'])): ?>
                        <small>Event: <?php echo date('M j, Y g:i A', strtotime($post['event_date'])); ?></small><br>
                    <?php endif; ?>
                    <small>Posted by <?php echo $author; ?> on <?php echo $created; ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="text-align:center;">
                <p style="margin:0;color:#666;">No posts yet.</p>
            </div>
        <?php endif; ?>

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
            <button id="openCalendarBtn" class="calendar-open-btn">Open Calendar</button>
        </div>
        <div class="card">
            <h3>Date & Time</h3>
            <p id="datetime"></p>
        </div>
        <div class="card">
            <h3 id="sidebarSelectedDate">Events on Selected Date</h3>
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