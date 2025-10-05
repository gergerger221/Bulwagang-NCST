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
  if ($id <= 0) {
    json_response(400, ['success' => false, 'message' => 'Invalid post id']);
  }

  // Optional: delete files from disk (skipped for safety unless required)
  $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
  $stmt->execute([$id]);

  json_response(200, ['success' => true, 'message' => 'Post deleted']);

} catch (Exception $e) {
  error_log('Delete post error: '.$e->getMessage());
  json_response(500, ['success' => false, 'message' => 'Server error deleting post']);
}
