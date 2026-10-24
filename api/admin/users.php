<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/validate-token.php';

setCorsHeaders();
setSecurityHeaders();

$admin = requireAdmin();
$db = getDB();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
  http_response_code(204);
  exit;
}

if ($method === 'GET') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id) {
    $stmt = $db->prepare(
      'SELECT u.id, u.username, u.email, u.role, u.created_at,
              COALESCE(i.is_published, 0) AS has_invitation, i.slug AS invitation_slug,
              (SELECT COUNT(*) FROM rsvps r WHERE r.invitation_id = i.id) AS rsvp_count,
              (SELECT COUNT(*) FROM rsvps r WHERE r.invitation_id = i.id AND r.attendance = "yes") AS rsvp_yes,
              (SELECT COUNT(*) FROM rsvps r WHERE r.invitation_id = i.id AND r.attendance = "no") AS rsvp_no
       FROM users u
       LEFT JOIN invitations i ON i.user_id = u.id
       WHERE u.id = ?'
    );
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) jsonError('User not found.', 404);
    jsonSuccess(['user' => $user]);
  } else {
    $stmt = $db->query(
      'SELECT u.id, u.username, u.email, u.role, u.created_at,
              COALESCE(i.is_published, 0) AS has_invitation,
              i.slug AS invitation_slug,
              (SELECT COUNT(*) FROM rsvps r JOIN invitations i2 ON r.invitation_id = i2.id WHERE i2.user_id = u.id) AS rsvp_count
       FROM users u
       LEFT JOIN invitations i ON i.user_id = u.id
       ORDER BY u.created_at DESC'
    );
    jsonSuccess(['users' => $stmt->fetchAll()]);
  }
}

if ($method === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input) jsonError('Invalid JSON body.', 400);

  $err = validateRequired($input, ['username', 'password', 'email']);
  if ($err) jsonError($err, 400);

  $username = sanitizeString($input['username']);
  $email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
  if (!$email) jsonError('Valid email required.', 400);
  $password = $input['password'];
  if (strlen($password) < 8) jsonError('Password must be at least 8 characters.', 400);
  $role = in_array($input['role'] ?? '', ['admin', 'user']) ? $input['role'] : 'user';

  // Check unique
  $check = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
  $check->execute([$username, $email]);
  if ($check->fetch()) jsonError('Username or email already exists.', 409);

  $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
  $stmt = $db->prepare('INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?)');
  $stmt->execute([$username, $hash, $email, $role]);

  $userId = (int)$db->lastInsertId();

  // Auto-create invitation
  $invType = in_array($input['invitation_type'] ?? '', ['couple', 'bac', 'circoncision']) ? $input['invitation_type'] : 'couple';
  $slug = 'invitation-' . $userId . '-' . bin2hex(random_bytes(4));
  $stmt2 = $db->prepare('INSERT INTO invitations (user_id, slug, groom_name, bride_name, invitation_type) VALUES (?, ?, ?, ?, ?)');
  $stmt2->execute([$userId, $slug, $username, '', $invType]);

  jsonSuccess(['user_id' => $userId, 'username' => $username, 'password' => $password, 'email' => $email], 201);
}

if ($method === 'PUT') {
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input) jsonError('Invalid JSON body.', 400);
  if (empty($input['id'])) jsonError('User ID required.', 400);

  $userId = (int)$input['id'];
  $fields = [];
  $params = [];

  if (isset($input['username'])) {
    $fields[] = 'username = ?';
    $params[] = sanitizeString($input['username']);
  }
  if (isset($input['email'])) {
    $email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
    if (!$email) jsonError('Valid email required.', 400);
    $fields[] = 'email = ?';
    $params[] = $email;
  }
  if (isset($input['role'])) {
    $role = in_array($input['role'], ['admin', 'user']) ? $input['role'] : 'user';
    $fields[] = 'role = ?';
    $params[] = $role;
  }
  if (!empty($input['password'])) {
    if (strlen($input['password']) < 8) jsonError('Password must be at least 8 characters.', 400);
    $fields[] = 'password_hash = ?';
    $params[] = password_hash($input['password'], PASSWORD_BCRYPT, ['cost' => 12]);
  }

  if (empty($fields)) jsonError('No fields to update.', 400);

  $params[] = $userId;
  $stmt = $db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?');
  $stmt->execute($params);

  jsonSuccess(['updated' => true]);
}

if ($method === 'DELETE') {
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input || empty($input['id'])) jsonError('User ID required.', 400);

  $userId = (int)$input['id'];
  if ($userId === $admin['user_id']) jsonError('Cannot delete yourself.', 403);

  $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
  $stmt->execute([$userId]);

  jsonSuccess(['deleted' => true]);
}

jsonError('Method not allowed.', 405);
