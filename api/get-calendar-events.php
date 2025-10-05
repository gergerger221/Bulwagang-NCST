<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../includes/db_connection.php';

function json_response($code, $data) {
  http_response_code($code);
  echo json_encode($data);
  exit;
}

try {
  // Optional: require login since home.php requires it
  if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    json_response(401, ['success' => false, 'message' => 'Unauthorized']);
  }

  $stmt = $pdo->prepare("SELECT id, title, content, event_date FROM posts WHERE type = 'announcements' AND event_date IS NOT NULL AND status='published' ORDER BY event_date ASC");
  $stmt->execute();
  $rows = $stmt->fetchAll();

  $events = [];
  foreach ($rows as $r) {
    if (empty($r['event_date'])) continue;
    $ts = strtotime($r['event_date']);
    if ($ts === false) continue;
    $postId = (int)$r['id'];
    $title = (string)$r['title'];
    $desc = (string)($r['content'] ?? '');
    $events[] = [
      'id' => (string)$postId,
      'title' => $title,
      'start' => date('c', $ts), // ISO8601
      'allDay' => false,
      'url' => 'home.php#post-' . $postId,
      'extendedProps' => [
        'description' => $desc,
        'postId' => $postId
      ]
    ];
  }

  json_response(200, ['success' => true, 'events' => $events]);

} catch (Exception $e) {
  error_log('Get calendar events error: ' . $e->getMessage());
  json_response(500, ['success' => false, 'message' => 'Server error fetching events']);
}
