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
  $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
  if (!$userId) jsonError('user_id required.', 400);

  $stmt = $db->prepare(
    'SELECT i.*, t.name AS template_name, t.folder_path AS template_folder
     FROM invitations i
     LEFT JOIN templates t ON t.id = i.template_id
     WHERE i.user_id = ?
     LIMIT 1'
  );
  $stmt->execute([$userId]);
  $inv = $stmt->fetch();

  if (!$inv) {
    jsonSuccess(['invitation' => null]);
  }

  unset($inv['id']);
  jsonSuccess(['invitation' => $inv]);
}

if ($method === 'PUT') {
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input || !isset($input['user_id'])) {
    jsonError('user_id required.', 400);
  }

  $userId = (int)$input['user_id'];

  $allowedFields = [
    'groom_name', 'bride_name', 'groom_parents', 'bride_parents',
    'wedding_date', 'wedding_time', 'venue_name', 'venue_address',
    'map_embed', 'map_iframe', 'quote_text', 'love_story_title', 'love_story_text',
      'template_id', 'is_published', 'song_path', 'text_color',
      'text_offset', 'text_delay', 'video_loop', 'invitation_type',
      'prefix_text', 'prefix_hidden', 'font_size_rem', 'prefix_font_size_rem', 'date_font_size_rem', 'date_offset',
      'hero_text_color', 'pfp_groom_transform', 'pfp_bride_transform',
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
      } elseif ($f === 'font_size_rem' || $f === 'prefix_font_size_rem' || $f === 'date_font_size_rem') {
        $val = $input[$f] !== null && $input[$f] !== '' ? (float)$input[$f] : null;
      } elseif ($f === 'text_color' || $f === 'hero_text_color') {
        $val = $input[$f] ? validateHexColor((string)$input[$f]) : null;
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

  $params[] = $userId;
  $sql = 'UPDATE invitations SET ' . implode(', ', $sets) . ' WHERE user_id = ?';
  $db->prepare($sql)->execute($params);

  jsonSuccess(['updated' => true]);
}

jsonError('Method not allowed.', 405);
