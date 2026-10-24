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
    $stmt = $db->prepare(
      'SELECT i.*, t.name AS template_name, t.folder_path AS template_folder
       FROM invitations i
       LEFT JOIN templates t ON t.id = i.template_id
       WHERE i.user_id = ?
       LIMIT 1'
    );
    $stmt->execute([$user['user_id']]);
    $inv = $stmt->fetch();

    if (!$inv) {
      // Create default invitation for new user
      $slug = 'wedding-' . $user['user_id'] . '-' . bin2hex(random_bytes(4));
      $stmt2 = $db->prepare(
        'INSERT INTO invitations (user_id, slug, groom_name, bride_name) VALUES (?, ?, ?, ?)'
      );
      $stmt2->execute([$user['user_id'], $slug, $user['username'], '']);
      $stmt->execute([$user['user_id']]);
      $inv = $stmt->fetch();
    }

    unset($inv['id']); // don't expose internal id
    jsonSuccess($inv);
    break;

  case 'POST':
  case 'PUT':
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
      jsonError('Invalid JSON body.', 400);
    }

    $allowedFields = [
      'groom_name', 'bride_name', 'groom_parents', 'bride_parents',
      'wedding_date', 'wedding_time', 'venue_name', 'venue_address',
      'map_embed', 'map_iframe', 'quote_text', 'love_story_title', 'love_story_text',
      'template_id', 'is_published', 'song_path', 'text_color',
      'text_offset', 'text_delay', 'video_loop', 'invitation_type',
      'pfp_groom_transform', 'pfp_bride_transform',
    ];

    if (array_key_exists('invitation_type', $input) && !in_array($input['invitation_type'], ['couple', 'bac', 'circoncision'])) {
      jsonError('Type d\'invitation invalide.', 400);
    }

    $sets = [];
    $params = [];
    foreach ($allowedFields as $f) {
    if (array_key_exists($f, $input)) {
      if (in_array($f, ['love_story_text', 'venue_address', 'quote_text'])) {
        $val = sanitizeText((string)$input[$f]);
      } elseif ($f === 'map_embed' || $f === 'map_iframe') {
        $raw = trim((string)$input[$f]);
        if (preg_match('/<iframe\s+src=["\']([^"\']+)["\']/i', $raw, $m)) {
          $raw = $m[1];
        }
        $val = str_starts_with($raw, 'https://') ? $raw : '';
        $val = mb_substr($val, 0, 2000);
      } elseif (in_array($f, ['is_published', 'video_loop', 'prefix_hidden'])) {
        $val = $input[$f] ? 1 : 0;
      } elseif ($f === 'template_id') {
        $val = $input[$f] !== null ? (int)$input[$f] : null;
      } elseif ($f === 'wedding_date') {
        $val = $input[$f] ?: null;
        if ($val && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
          jsonError('Format de date invalide (YYYY-MM-DD).', 400);
        }
      } elseif ($f === 'text_delay') {
        $val = $input[$f] !== null && $input[$f] !== '' ? (float)$input[$f] : null;
      } elseif ($f === 'text_color') {
        $val = validateHexColor((string)($input[$f] ?? ''));
      } else {
        $val = sanitizeString((string)$input[$f]);
      }
      $sets[] = "`$f` = ?";
      $params[] = $val;
    }
    }

    if (empty($sets)) {
      jsonError('No fields to update.', 400);
    }

    // Update token expiry if wedding_date changed
    if (array_key_exists('wedding_date', $input)) {
      $expiresAt = getWeddingExpiry($input['wedding_date']);
      $db->prepare('UPDATE user_tokens SET expires_at = ? WHERE user_id = ?')
         ->execute([$expiresAt->format('Y-m-d H:i:s'), $user['user_id']]);
    }

    $params[] = $user['user_id'];
    $sql = 'UPDATE invitations SET ' . implode(', ', $sets) . ' WHERE user_id = ?';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    jsonSuccess(['updated' => true]);
    break;

  default:
    jsonError('Method not allowed.', 405);
}
