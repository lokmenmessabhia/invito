<?php
declare(strict_types=1);

/**
 * Database configuration.
 *
 * Values can be overridden with environment variables (recommended for
 * production). The defaults match a standard local XAMPP install.
 */
function envValue(string $name, string $default): string {
  $value = getenv($name);
  return $value === false || $value === '' ? $default : $value;
}

define('DB_HOST', envValue('DB_HOST', '127.0.0.1'));
define('DB_PORT', envValue('DB_PORT', '3306'));
define('DB_NAME', envValue('DB_NAME', 'invito'));
define('DB_USER', envValue('DB_USER', 'root'));
define('DB_PASS', getenv('DB_PASS') === false ? '' : (string)getenv('DB_PASS'));
define('DB_CHARSET', envValue('DB_CHARSET', 'utf8mb4'));

// Base URL: default to root, auto-detect subfolder if applicable
$detected = '/';
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
  $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
  $dir = str_replace('\\', '/', __DIR__);
  $relPath = substr($dir, strlen($docRoot));
  if ($relPath !== false) {
    $projectRoot = dirname($relPath);
    if ($projectRoot !== '/' && $projectRoot !== '.') {
      $detected = $projectRoot . '/';
    }
  }
}
define('BASE_URL', $detected);

function getDB(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $dsn = sprintf(
      'mysql:host=%s;port=%s;dbname=%s;charset=%s',
      DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
  }
  return $pdo;
}
