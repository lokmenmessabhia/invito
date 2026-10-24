<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/validate-token.php';

setCorsHeaders();
setSecurityHeaders();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
  http_response_code(204);
  exit;
}

$songsDir = realpath(__DIR__ . '/../../songs');

if ($method === 'GET') {
  authenticateToken();
  $songs = [];
  if ($songsDir) {
    $mp3s = glob($songsDir . '/*.mp3');
    if ($mp3s) {
      sort($mp3s);
      foreach ($mp3s as $mp3) {
        $songs[] = basename($mp3);
      }
    }
  }
  jsonSuccess(['songs' => $songs]);
}

if ($method === 'POST') {
  requireAdmin();
  if (!isset($_FILES['file'])) {
    jsonError('Aucun fichier reçu.', 400);
  }
  if (!$songsDir) {
    jsonError("Le dossier 'songs/' n'existe pas.", 500);
  }

  $file = $_FILES['file'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if ($ext !== 'mp3') {
    jsonError('Seuls les fichiers MP3 sont acceptés.', 400);
  }

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  if ($mime !== 'audio/mpeg') {
    jsonError('Le type MIME du fichier n\'est pas audio/mpeg.', 400);
  }

  $maxSize = 50 * 1024 * 1024;
  if ($file['size'] > $maxSize) {
    jsonError('Fichier trop volumineux (max 50 Mo).', 400);
  }

  $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME)) . '.mp3';
  $destPath = $songsDir . DIRECTORY_SEPARATOR . $filename;
  $i = 1;
  while (file_exists($destPath)) {
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME)) . "_$i.mp3";
    $destPath = $songsDir . DIRECTORY_SEPARATOR . $filename;
    $i++;
  }

  if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    jsonError('Erreur lors de l\'enregistrement.', 500);
  }

  jsonSuccess(['filename' => $filename, 'path' => 'songs/' . $filename, 'size' => $file['size']], 201);
}

if ($method === 'DELETE') {
  requireAdmin();
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input || empty($input['filename'])) {
    jsonError('Filename required.', 400);
  }
  $filename = basename($input['filename']);
  if (!preg_match('/^[a-zA-Z0-9_-]+\.mp3$/', $filename)) {
    jsonError('Invalid filename.', 400);
  }
  $filePath = $songsDir . DIRECTORY_SEPARATOR . $filename;
  if (!file_exists($filePath)) {
    jsonError('Fichier introuvable.', 404);
  }
  if (!unlink($filePath)) {
    jsonError('Erreur lors de la suppression.', 500);
  }
  jsonSuccess(['deleted' => $filename]);
}

if ($method === 'PUT') {
  requireAdmin();
  $input = json_decode(file_get_contents('php://input'), true);
  if (!$input || empty($input['filename']) || empty($input['new_name'])) {
    jsonError('Filename and new_name required.', 400);
  }
  $oldName = basename($input['filename']);
  $newName = basename($input['new_name']);
  if (!preg_match('/^[a-zA-Z0-9_-]+\.mp3$/', $oldName) || !preg_match('/^[a-zA-Z0-9_-]+\.mp3$/', $newName)) {
    jsonError('Invalid filename format.', 400);
  }
  $oldPath = $songsDir . DIRECTORY_SEPARATOR . $oldName;
  $newPath = $songsDir . DIRECTORY_SEPARATOR . $newName;
  if (!file_exists($oldPath)) {
    jsonError('Fichier introuvable.', 404);
  }
  if (file_exists($newPath)) {
    jsonError('Un fichier avec ce nom existe déjà.', 400);
  }
  if (!rename($oldPath, $newPath)) {
    jsonError('Erreur lors du renommage.', 500);
  }
  jsonSuccess(['renamed' => ['from' => $oldName, 'to' => $newName]]);
}

jsonError('Method not allowed.', 405);
