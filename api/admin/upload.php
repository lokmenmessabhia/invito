<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/validate-token.php';

setCorsHeaders();
setSecurityHeaders();

$admin = requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  jsonError('Method not allowed.', 405);
}

$ip = getClientIP();
if (!checkRateLimit('upload:' . $ip, 20, 3600)) {
  jsonError('Trop de téléversements. Veuillez réessayer plus tard.', 429);
}

$templateId = isset($_POST['template_id']) ? (int)$_POST['template_id'] : 0;
$slot = isset($_POST['slot']) ? sanitizeString($_POST['slot']) : '';
if (!$templateId) {
  jsonError('ID du template requis.', 400);
}

$stmt = $db->prepare('SELECT id, folder_path FROM templates WHERE id = ? LIMIT 1');
$stmt->execute([$templateId]);
$template = $stmt->fetch();

if (!$template) {
  jsonError('Template introuvable.', 404);
}

$folder = $template['folder_path'];
$targetDir = __DIR__ . '/../../templates/' . $folder;
if (!is_dir($targetDir)) {
  if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
    jsonError("Impossible de créer le dossier 'templates/$folder'.", 500);
  }
}
$targetDir = realpath($targetDir);
if (!$targetDir) {
  jsonError("Le dossier 'templates/$folder' n'existe pas sur le serveur.", 400);
}

$allowedTypes = ['video/webm', 'audio/mpeg', 'video/mp4'];
$allowedExts = ['webm', 'mp3', 'mp4'];

if (!isset($_FILES['file'])) {
  jsonError('Aucun fichier reçu.', 400);
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExts)) {
  jsonError('Format non autorisé. Formats acceptés : webm, mp3, mp4.', 400);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$validMimes = ['video/webm', 'audio/mpeg', 'video/mp4'];
if (!in_array($mime, $validMimes)) {
  jsonError('Le type MIME du fichier ne correspond pas (webm/mp3/mp4 attendu).', 400);
}

$maxSize = 100 * 1024 * 1024; // 100 MB
if ($file['size'] > $maxSize) {
  jsonError('Fichier trop volumineux (max 100 Mo).', 400);
}

$filename = match ($ext) {
  'webm' => $slot === 'second' ? '2.webm' : ($slot === 'first' ? '1.webm' : 'video.webm'),
  'mp4'  => $slot === 'second' ? '2.mp4' : ($slot === 'first' ? '1.mp4' : 'video.mp4'),
  'mp3'  => preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME)) . '.mp3',
};

$destPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

if (file_exists($destPath)) {
  unlink($destPath);
}

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
  jsonError('Erreur lors de l\'enregistrement du fichier.', 500);
}

jsonSuccess([
  'filename' => $filename,
  'path' => 'templates/' . $folder . '/' . $filename,
  'size' => $file['size'],
]);