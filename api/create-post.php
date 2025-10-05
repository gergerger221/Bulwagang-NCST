<?php
/**
 * Create Post API (images + videos)
 * Fields (multipart/form-data):
 * - postType: announcements | performances | albums
 * - title: string (required)
 * - content: string (optional)
 * - files[]: images/videos (optional)
 * Requires logged-in admin or moderator.
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../includes/db_connection.php';

function json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(405, ['success' => false, 'message' => 'Method not allowed']);
    }

    // Must be logged in as admin or moderator
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin','moderator'])) {
        json_response(403, ['success' => false, 'message' => 'Forbidden: Admin/Moderator only']);
    }

    $authorId = (int)($_SESSION['user_id'] ?? 0);
    if (!$authorId) {
        json_response(401, ['success' => false, 'message' => 'Unauthorized']);
    }

    $allowedTypes = ['announcements', 'performances', 'albums'];
    $postType = isset($_POST['postType']) ? strtolower(trim($_POST['postType'])) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $eventDateRaw = isset($_POST['event_date']) ? trim($_POST['event_date']) : '';
    $eventDate = null;
    if ($postType === 'announcements' && $eventDateRaw !== '') {
        // Expecting HTML datetime-local format: YYYY-MM-DDTHH:MM
        $eventDate = str_replace('T', ' ', $eventDateRaw);
    }

    if (!$title) {
        json_response(400, ['success' => false, 'message' => 'Title is required']);
    }
    if (!in_array($postType, $allowedTypes, true)) {
        json_response(400, ['success' => false, 'message' => 'Invalid post type']);
    }

    // Ensure posts table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT(11) NOT NULL AUTO_INCREMENT,
        author_id INT(11) NOT NULL,
        type ENUM('announcements','performances','albums') NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NULL,
        event_date DATETIME NULL,
        images JSON NULL,
        status ENUM('published','draft') NOT NULL DEFAULT 'published',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_type_created (type, created_at),
        KEY idx_author (author_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // If table existed without event_date, try to add it (ignore error if exists)
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN event_date DATETIME NULL AFTER content");
    } catch (Exception $ignored) {}

    // File upload handling (images + videos)
    $savedFiles = [];
    $uploadBaseDir = dirname(__DIR__) . '/uploads/posts';
    if (!is_dir($uploadBaseDir)) {
        @mkdir($uploadBaseDir, 0755, true);
    }

    // Allow larger size for videos
    $maxFileSize = 50 * 1024 * 1024; // 50MB
    $allowedMime = [
        'image/jpeg','image/png','image/gif','image/webp',
        'video/mp4','video/mpeg','video/quicktime','video/x-msvideo','video/x-ms-wmv','video/3gpp','video/3gpp2','video/webm','video/ogg'
    ];
    $extMap = [
        'image/jpeg'=>'.jpg','image/png'=>'.png','image/gif'=>'.gif','image/webp'=>'.webp',
        'video/mp4'=>'.mp4','video/mpeg'=>'.mpeg','video/quicktime'=>'.mov','video/x-msvideo'=>'.avi','video/x-ms-wmv'=>'.wmv','video/3gpp'=>'.3gp','video/3gpp2'=>'.3g2','video/webm'=>'.webm','video/ogg'=>'.ogv'
    ];

    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        $names = $_FILES['files']['name'];
        $tmpNames = $_FILES['files']['tmp_name'];
        $types = $_FILES['files']['type'];
        $sizes = $_FILES['files']['size'];
        $errors = $_FILES['files']['error'];

        for ($i = 0; $i < count($names); $i++) {
            if (empty($names[$i])) continue;
            if ($errors[$i] !== UPLOAD_ERR_OK) continue; // skip failed upload

            $tmp = $tmpNames[$i];
            $size = (int)$sizes[$i];
            $clientType = $types[$i] ?? '';

            if ($size > $maxFileSize) {
                continue; // skip too large
            }

            // Validate mime safely
            $mime = $clientType;
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $detected = $finfo->file($tmp);
                if ($detected) { $mime = $detected; }
            } elseif (function_exists('mime_content_type')) {
                $detected = @mime_content_type($tmp);
                if ($detected) { $mime = $detected; }
            }
            if (!in_array($mime, $allowedMime, true)) {
                continue; // skip unsupported
            }

            // Determine extension
            $ext = $extMap[$mime] ?? ('.' . strtolower(preg_replace('/[^A-Za-z0-9]/', '', pathinfo($names[$i], PATHINFO_EXTENSION) ?: 'bin')));

            // Build safe unique name
            $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($names[$i], PATHINFO_FILENAME));
            $unique = date('Ymd_His') . '_' . $authorId . '_' . bin2hex(random_bytes(4));
            $filename = $safeBase . '_' . $unique . $ext;
            $destPath = $uploadBaseDir . '/' . $filename;

            if (@move_uploaded_file($tmp, $destPath)) {
                $savedFiles[] = 'uploads/posts/' . $filename; // relative URL path for public access
            }
        }
    }

    // Insert post
    $imagesJson = !empty($savedFiles) ? json_encode($savedFiles) : null;
    $stmt = $pdo->prepare("INSERT INTO posts (author_id, type, title, content, event_date, images, status) VALUES (?, ?, ?, ?, ?, ?, 'published')");
    $stmt->execute([$authorId, $postType, $title, $content, $eventDate, $imagesJson]);
    $postId = (int)$pdo->lastInsertId();

    json_response(200, [
        'success' => true,
        'message' => 'Post created successfully',
        'post' => [
            'id' => $postId,
            'type' => $postType,
            'title' => $title,
            'content' => $content,
            'images' => $savedFiles,
            'event_date' => $eventDate,
        ]
    ]);

} catch (Exception $e) {
    error_log('Create post error: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Server error creating post']);
}
