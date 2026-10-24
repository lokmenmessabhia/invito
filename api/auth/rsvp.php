<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

setCorsHeaders();
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  jsonError('Method not allowed.', 405);
}

// Rate limit: max 5 RSVPs per IP per minute
$ip = getClientIP();
if (!checkRateLimit('rsvp:' . $ip, 5, 60)) {
  jsonError('Trop de tentatives. Veuillez réessayer dans une minute.', 429);
}

// Validate origin/referer to prevent CSRF
$origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
if ($origin) {
  $allowedHosts = ['invitodz.easystoredz.com', 'localhost', '127.0.0.1'];
  $host = parse_url($origin, PHP_URL_HOST);
  if (!$host || !in_array($host, $allowedHosts, true)) {
    jsonError('Requête non autorisée.', 403);
  }
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
  jsonError('Invalid JSON body.', 400);
}

$err = validateRequired($input, ['slug', 'name', 'attendance']);
if ($err) {
  jsonError($err, 400);
}

$slug = sanitizeString($input['slug']);
$name = sanitizeString($input['name']);
$phone = isset($input['phone']) ? sanitizeString($input['phone']) : '';
$guests = isset($input['guests']) ? min(max((int)$input['guests'], 1), 10) : 1;
$attendance = in_array($input['attendance'], ['yes', 'no']) ? $input['attendance'] : 'yes';
$message = isset($input['message']) ? sanitizeText($input['message']) : '';

$db = getDB();

$stmt = $db->prepare('SELECT id FROM invitations WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
$inv = $stmt->fetch();

if (!$inv) {
  jsonError('Invitation not found.', 404);
}

$stmt2 = $db->prepare(
  'INSERT INTO rsvps (invitation_id, name, email, phone, guests, attendance, message) VALUES (?, ?, NULL, ?, ?, ?, ?)'
);
$stmt2->execute([$inv['id'], $name, $phone, $guests, $attendance, $message]);

jsonSuccess(['rsvp_id' => (int)$db->lastInsertId()]);
