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
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed']);
  }

  if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin','moderator'])) {
    json_response(403, ['success' => false, 'message' => 'Forbidden']);
  }

  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) { $data = $_POST; }

  $id = isset($data['id']) ? (int)$data['id'] : 0;
  $postType = isset($data['postType']) ? strtolower(trim($data['postType'])) : '';
  $title = isset($data['title']) ? trim($data['title']) : '';
  $content = isset($data['content']) ? trim($data['content']) : '';
  $eventRaw = isset($data['event_date']) ? trim($data['event_date']) : '';

  if ($id <= 0) {
    json_response(400, ['success' => false, 'message' => 'Invalid post id']);
  }
  if (!$title) {
    json_response(400, ['success' => false, 'message' => 'Title is required']);
  }
  $allowed = ['announcements','performances','albums'];
  if (!in_array($postType, $allowed, true)) {
    json_response(400, ['success' => false, 'message' => 'Invalid post type']);
  }

  $event = null;
  if ($postType === 'announcements' && $eventRaw !== '') {
    $event = str_replace('T', ' ', $eventRaw);
  }

  $stmt = $pdo->prepare("UPDATE posts SET type = ?, title = ?, content = ?, event_date = ? WHERE id = ?");
  $stmt->execute([$postType, $title, $content, $event, $id]);

  json_response(200, ['success' => true, 'message' => 'Post updated']);

} catch (Exception $e) {
  error_log('Update post error: '.$e->getMessage());
  json_response(500, ['success' => false, 'message' => 'Server error updating post']);
}
