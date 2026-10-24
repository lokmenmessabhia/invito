<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

try {
  $db = getDB();
  $stmt = $db->query("SHOW COLUMNS FROM templates LIKE 'has_two_videos'");
  if ($stmt->fetch()) {
    echo "Column 'has_two_videos' already exists.\n";
    exit(0);
  }
  $db->exec("ALTER TABLE templates ADD has_two_videos TINYINT(1) NOT NULL DEFAULT 0 AFTER video_loop");
  echo "Migration successful: added has_two_videos.\n";
} catch (Exception $e) {
  echo 'Migration failed: ' . $e->getMessage() . "\n";
  exit(1);
}
