<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/validate-token.php';

setCorsHeaders();
setSecurityHeaders();

$user = requireUser();
$db = getDB();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'DELETE') {
  $input = json_decode(file_get_contents('php://input'), true);
  $field = isset($input['field']) ? $input['field'] : '';
  if (!in_array($field, ['pfp_groom', 'pfp_bride'])) {
    jsonError('Champ invalide.', 400);
  }
  $stmt = $db->prepare("SELECT $field FROM invitations WHERE user_id = ?");
  $stmt->execute([$user['user_id']]);
  $row = $stmt->fetch();
  if ($row && $row[$field]) {
    $filePath = realpath(__DIR__ . '/../../' . $row[$field]);
    if ($filePath && file_exists($filePath)) {
      @unlink($filePath);
    }
  }
  $transformField = $field . '_transform';
  $stmt = $db->prepare("UPDATE invitations SET `$field` = NULL, `$transformField` = NULL WHERE user_id = ?");
  $stmt->execute([$user['user_id']]);
  jsonSuccess(['deleted' => true]);
}

if ($method !== 'POST') {
  jsonError('Method not allowed.', 405);
}

$ip = getClientIP();
if (!checkRateLimit('upload:' . $ip, 20, 3600)) {
  jsonError('Trop de téléversements. Veuillez réessayer plus tard.', 429);
}

$field = isset($_POST['field']) ? $_POST['field'] : '';
if (!in_array($field, ['pfp_groom', 'pfp_bride'])) {
  jsonError('Champ invalide.', 400);
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

if (!isset($_FILES['file'])) {
  jsonError('Aucun fichier reçu.', 400);
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExts)) {
  jsonError('Format non autorisé. Formats acceptés : jpg, png, webp.', 400);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedTypes)) {
  jsonError('Le type MIME du fichier ne correspond pas (JPEG/PNG/WEBP attendu).', 400);
}

$maxSize = 5 * 1024 * 1024; // 5 MB
if ($file['size'] > $maxSize) {
  jsonError('Fichier trop volumineux (max 5 Mo).', 400);
}

$targetDir = realpath(__DIR__ . '/../../uploads/pfps');
if (!$targetDir) {
  @mkdir(__DIR__ . '/../../uploads/pfps', 0755, true);
  $targetDir = realpath(__DIR__ . '/../../uploads/pfps');
}

$filename = $user['user_id'] . '_' . $field . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
$destPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
  jsonError('Erreur lors de l\'enregistrement du fichier.', 500);
}

$url = 'uploads/pfps/' . $filename;

// Save path to invitation
$stmt = $db->prepare("UPDATE invitations SET `$field` = ? WHERE user_id = ?");
$stmt->execute([$url, $user['user_id']]);

jsonSuccess(['path' => $url]);
