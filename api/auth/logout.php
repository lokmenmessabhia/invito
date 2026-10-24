<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/validate-token.php';

setCorsHeaders();
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  jsonError('Method not allowed.', 405);
}

$user = authenticateToken();

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? '';

if ($token) {
  $db = getDB();
  $stmt = $db->prepare('DELETE FROM user_tokens WHERE token = ? AND user_id = ?');
  $stmt->execute([$token, $user['user_id']]);
} else {
  // Delete all tokens for this user
  $db = getDB();
  $stmt = $db->prepare('DELETE FROM user_tokens WHERE user_id = ?');
  $stmt->execute([$user['user_id']]);
}

jsonSuccess();
