<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

setCorsHeaders();
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  jsonError('Method not allowed.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
  jsonError('Invalid JSON body.', 400);
}

$err = validateRequired($input, ['username', 'password']);
if ($err) {
  jsonError($err, 400);
}

$username = sanitizeString($input['username']);
$password = $input['password'];

$db = getDB();
$stmt = $db->prepare('SELECT id, username, email, role, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->execute([$username, $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
  jsonError('Invalid credentials.', 401);
}

// Get wedding date for token expiry
$stmt2 = $db->prepare('SELECT wedding_date FROM invitations WHERE user_id = ? LIMIT 1');
$stmt2->execute([$user['id']]);
$inv = $stmt2->fetch();

$expiresAt = getWeddingExpiry($inv['wedding_date'] ?? null);

$token = generateToken();
$stmt3 = $db->prepare('INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
$stmt3->execute([$user['id'], $token, $expiresAt->format('Y-m-d H:i:s')]);

jsonSuccess([
  'token' => $token,
  'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
  'user' => [
    'id'       => (int)$user['id'],
    'username' => $user['username'],
    'email'    => $user['email'],
    'role'     => $user['role'],
  ],
]);
