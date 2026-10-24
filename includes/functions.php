<?php
declare(strict_types=1);

function h(mixed $value): string {
  return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonResponse(mixed $data, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function jsonError(string $message, int $status = 400): void {
  jsonResponse(['error' => $message], $status);
}

function jsonSuccess(?array $data = null): void {
  $res = ['success' => true];
  if ($data !== null) {
    $res['data'] = $data;
  }
  jsonResponse($res);
}

function validateRequired(array $data, array $fields): ?string {
  foreach ($fields as $f) {
    if (!isset($data[$f]) || (is_string($data[$f]) && trim($data[$f]) === '')) {
      return "Field '$f' is required.";
    }
  }
  return null;
}

function sanitizeString(string $value): string {
  $v = trim($value);
  $v = strip_tags($v);
  return mb_substr($v, 0, 1000);
}

function sanitizeText(string $value): string {
  $v = trim($value);
  $v = strip_tags($v);
  return mb_substr($v, 0, 5000);
}

function generateToken(): string {
  return bin2hex(random_bytes(64));
}

function getWeddingExpiry(?string $weddingDate): DateTime {
  if ($weddingDate) {
    $dt = new DateTime($weddingDate);
    $dt->setTime(23, 59, 59);
    return $dt;
  }
  return (new DateTime())->modify('+30 days');
}

function setSecurityHeaders(): void {
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: DENY');
  header('X-XSS-Protection: 1; mode=block');
  header('Referrer-Policy: no-referrer-when-downgrade');
  header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline'; frame-src https://www.google.com; img-src 'self' data:; media-src 'self'");
}

function setCorsHeaders(): void {
  $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
  $allowedOrigins = [
    'https://invitodz.easystoredz.com',
    'http://invitodz.easystoredz.com',
    'http://localhost',
    'http://localhost:5173',
    'http://localhost:3000',
    'http://127.0.0.1',
  ];
  if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
  }
  header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, Authorization');
  header('Access-Control-Max-Age: 86400');
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
  }
}

function jsEscape(string $value): string {
  return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function validateHexColor(string $color): string {
  $color = trim($color);
  if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
    return $color;
  }
  if (preg_match('/^[a-zA-Z]+$/', $color) && in_array(strtolower($color), ['black', 'white', 'inherit', 'transparent', 'currentcolor'], true)) {
    return $color;
  }
  return '#2C2420';
}

function checkRateLimit(string $key, int $maxAttempts = 10, int $windowSeconds = 300): bool {
  $db = getDB();
  try {
    $db->exec(
      'CREATE TABLE IF NOT EXISTS rate_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rate_key VARCHAR(255) NOT NULL,
        attempts INT NOT NULL DEFAULT 1,
        expires_at DATETIME NOT NULL,
        INDEX idx_rate_key (rate_key),
        INDEX idx_expires (expires_at)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
  } catch (\PDOException $e) {
    return true;
  }

  static $cleanupCounter = 0;
  if (++$cleanupCounter % 10 === 0) {
    try {
      $db->exec('DELETE FROM rate_limits WHERE expires_at < NOW()');
    } catch (\PDOException $e) {}
  }

  $stmt = $db->prepare('SELECT id, attempts FROM rate_limits WHERE rate_key = ? AND expires_at > NOW() LIMIT 1');
  $stmt->execute([$key]);
  $row = $stmt->fetch();

  if ($row) {
    if ((int)$row['attempts'] >= $maxAttempts) {
      return false;
    }
    $stmt2 = $db->prepare('UPDATE rate_limits SET attempts = attempts + 1 WHERE id = ?');
    $stmt2->execute([$row['id']]);
  } else {
    $expires = new DateTimeImmutable("+{$windowSeconds} seconds");
    $stmt3 = $db->prepare('INSERT INTO rate_limits (rate_key, attempts, expires_at) VALUES (?, 1, ?)');
    $stmt3->execute([$key, $expires->format('Y-m-d H:i:s')]);
  }
  return true;
}

function getClientIP(): string {
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    return trim($ips[0]);
  }
  if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
    return $_SERVER['HTTP_X_REAL_IP'];
  }
  return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
