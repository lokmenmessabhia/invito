<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/validate-token.php';

setCorsHeaders();
setSecurityHeaders();

$db = getDB();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
  http_response_code(204);
  exit;
}

if ($method === 'GET') {
  authenticateToken(); // any authenticated user can list templates
  $stmt = $db->query('SELECT * FROM templates ORDER BY name ASC');
  $templates = $stmt->fetchAll();

  foreach ($templates as &$t) {
    $folderPath = realpath(__DIR__ . '/../../templates/' . $t['folder_path']);
    $t['files'] = [
      'video_mp4'     => $folderPath && file_exists($folderPath . '/video.mp4'),
      'video_webm'    => $folderPath && file_exists($folderPath . '/video.webm'),
      'video_first'   => $folderPath && file_exists($folderPath . '/1.mp4'),
      'video_second'  => $folderPath && file_exists($folderPath . '/2.mp4'),
      'video_first_webm'  => $folderPath && file_exists($folderPath . '/1.webm'),
      'video_second_webm' => $folderPath && file_exists($folderPath . '/2.webm'),
    ];
    $t['songs'] = [];
    if ($folderPath) {
      $mp3s = glob($folderPath . '/*.mp3');
      if ($mp3s) {
        foreach ($mp3s as $mp3) {
          $t['songs'][] = basename($mp3);
        }
      }
    }
  }

  jsonSuccess(['templates' => $templates]);
}

if ($method === 'POST') {
  requireAdmin();
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input) jsonError('Invalid JSON body.', 400);

  $err = validateRequired($input, ['name', 'folder_path']);
  if ($err) jsonError($err, 400);

  $name = sanitizeString($input['name']);
  $folder = sanitizeString($input['folder_path']);
  $thumbnail = isset($input['thumbnail']) ? sanitizeString($input['thumbnail']) : '';
  $textOffset = isset($input['text_offset']) ? sanitizeString($input['text_offset']) : 'center';
  $textDelay = isset($input['text_delay']) ? (float)$input['text_delay'] : 1.25;
  $prefixText = isset($input['prefix_text']) ? sanitizeString($input['prefix_text']) : 'Mariage de';
  $prefixHidden = isset($input['prefix_hidden']) ? ($input['prefix_hidden'] ? 1 : 0) : 0;
  $fontSizeRem = isset($input['font_size_rem']) ? (float)$input['font_size_rem'] : 1.6;
  $prefixFontSizeRem = isset($input['prefix_font_size_rem']) ? (float)$input['prefix_font_size_rem'] : 1.0;
  $dateFontSizeRem = isset($input['date_font_size_rem']) ? (float)$input['date_font_size_rem'] : 0.9;
  $dateOffset = isset($input['date_offset']) ? sanitizeString($input['date_offset']) : '';
  $heroTextColor = isset($input['hero_text_color']) ? validateHexColor($input['hero_text_color']) : '#FFFFFF';
  $videoLoop = isset($input['video_loop']) ? ($input['video_loop'] ? 1 : 0) : 0;
  $hasTwoVideos = isset($input['has_two_videos']) ? ($input['has_two_videos'] ? 1 : 0) : 0;

  if (!preg_match('/^[a-zA-Z0-9_-]+$/', $folder)) {
    jsonError('Le dossier ne doit contenir que des lettres, chiffres, tirets et underscores.', 400);
  }

  $folderPath = __DIR__ . '/../../templates/' . $folder;
  if (!is_dir($folderPath)) {
    if (!mkdir($folderPath, 0755, true) && !is_dir($folderPath)) {
      jsonError("Impossible de créer le dossier 'templates/$folder'.", 500);
    }
  }

  try {
    $stmt = $db->prepare('INSERT INTO templates (name, folder_path, thumbnail, text_offset, text_delay, prefix_text, prefix_hidden, font_size_rem, prefix_font_size_rem, date_font_size_rem, date_offset, hero_text_color, video_loop, has_two_videos) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $folder, $thumbnail, $textOffset, $textDelay, $prefixText, $prefixHidden, $fontSizeRem, $prefixFontSizeRem, $dateFontSizeRem, $dateOffset, $heroTextColor, $videoLoop, $hasTwoVideos]);
  } catch (PDOException $e) {
    if ($e->getCode() == 23000) {
      jsonError("Ce dossier '$folder' est déjà utilisé par un autre template.", 400);
    }
    jsonError('Erreur lors de la création du template.', 500);
  }

  jsonSuccess(['template_id' => (int)$db->lastInsertId()], 201);
}

if ($method === 'PUT') {
  requireAdmin();
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input || empty($input['id'])) jsonError('Template ID required.', 400);

  $id = (int)$input['id'];
  $fields = [];
  $params = [];

  foreach (['name', 'folder_path', 'thumbnail', 'text_offset', 'text_delay', 'prefix_text', 'font_size_rem', 'prefix_font_size_rem', 'date_font_size_rem', 'date_offset'] as $f) {
    if (isset($input[$f])) {
      $fields[] = "`$f` = ?";
      $params[] = sanitizeString((string)$input[$f]);
    }
  }
  if (isset($input['hero_text_color'])) {
    $fields[] = 'hero_text_color = ?';
    $params[] = validateHexColor($input['hero_text_color']);
  }
  if (isset($input['is_active'])) {
    $fields[] = 'is_active = ?';
    $params[] = $input['is_active'] ? 1 : 0;
  }
  if (isset($input['prefix_hidden'])) {
    $fields[] = 'prefix_hidden = ?';
    $params[] = $input['prefix_hidden'] ? 1 : 0;
  }
  if (isset($input['video_loop'])) {
    $fields[] = 'video_loop = ?';
    $params[] = $input['video_loop'] ? 1 : 0;
  }
  if (isset($input['has_two_videos'])) {
    $fields[] = 'has_two_videos = ?';
    $params[] = $input['has_two_videos'] ? 1 : 0;
  }

  if (empty($fields)) jsonError('No fields to update.', 400);

  $params[] = $id;
  try {
    $stmt = $db->prepare('UPDATE templates SET ' . implode(', ', $fields) . ' WHERE id = ?');
    $stmt->execute($params);
  } catch (PDOException $e) {
    if ($e->getCode() == 23000) {
      jsonError('Ce dossier est déjà utilisé par un autre template.', 400);
    }
    jsonError('Erreur lors de la mise à jour du template.', 500);
  }

  jsonSuccess(['updated' => true]);
}

if ($method === 'DELETE') {
  requireAdmin();
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input || empty($input['id'])) jsonError('Template ID required.', 400);

  $id = (int)$input['id'];
  $stmt = $db->prepare('DELETE FROM templates WHERE id = ?');
  $stmt->execute([$id]);

  jsonSuccess(['deleted' => true]);
}

jsonError('Method not allowed.', 405);
