<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/validate-token.php';

setCorsHeaders();
setSecurityHeaders();

$user = requireUser();
$db = getDB();

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    $stmt = $db->prepare('SELECT id FROM invitations WHERE user_id = ? LIMIT 1');
    $stmt->execute([$user['user_id']]);
    $inv = $stmt->fetch();

    if (!$inv) {
      jsonSuccess(['rsvps' => []]);
      break;
    }

    $stmt2 = $db->prepare(
      'SELECT id, name, email, phone, guests, attendance, message, created_at
       FROM rsvps WHERE invitation_id = ? ORDER BY created_at DESC'
    );
    $stmt2->execute([$inv['id']]);
    $rsvps = $stmt2->fetchAll();

    foreach ($rsvps as &$r) {
      if ($r['email'] !== null) {
        $parts = explode('@', $r['email']);
        if (count($parts) === 2) {
          $r['email'] = substr($parts[0], 0, 2) . '***@' . $parts[1];
        }
      }
    }
    unset($r);

    jsonSuccess(['rsvps' => $rsvps]);
    break;

  default:
    jsonError('Method not allowed.', 405);
}
