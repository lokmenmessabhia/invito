<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function cleanupExpiredTokens(): void {
  try {
    $db = getDB();
    $db->exec('DELETE FROM user_tokens WHERE expires_at < NOW()');
    $db->exec('DELETE FROM rate_limits WHERE expires_at < NOW()');
  } catch (\PDOException $e) {}
}

function authenticateToken(): array {
  cleanupExpiredTokens();
  $auth = $_SERVER['HTTP_AUTHORIZATION']
    ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
    ?? '';

  // Fallback: getallheaders for Apache CGI/FastCGI
  if (!$auth && function_exists('getallheaders')) {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
  }

  if (!preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
    jsonError('Missing or invalid Authorization header.', 401);
  }

  $token = $m[1];
  if (strlen($token) !== 128 || !ctype_xdigit($token)) {
    jsonError('Invalid token format.', 401);
  }

  $db = getDB();
  $stmt = $db->prepare(
    'SELECT u.id, u.username, u.email, u.role, t.expires_at
     FROM user_tokens t
     JOIN users u ON u.id = t.user_id
     WHERE t.token = ?
     LIMIT 1'
  );
  $stmt->execute([$token]);
  $row = $stmt->fetch();

  if (!$row) {
    jsonError('Token not found.', 401);
  }

  $now = new DateTime();
  $exp = new DateTime($row['expires_at']);

  if ($now > $exp) {
    $db->prepare('DELETE FROM user_tokens WHERE token = ?')->execute([$token]);
    jsonError('Token expired.', 401);
  }

  return [
    'user_id'  => (int)$row['id'],
    'username' => $row['username'],
    'email'    => $row['email'],
    'role'     => $row['role'],
  ];
}

function requireAdmin(): array {
  $user = authenticateToken();
  if ($user['role'] !== 'admin') {
    jsonError('Forbidden: admin access required.', 403);
  }
  return $user;
}

function requireUser(): array {
  return authenticateToken();
}
