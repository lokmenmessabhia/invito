<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();

// Check if column already exists
$stmt = $db->query("SHOW COLUMNS FROM templates LIKE 'video_loop'");
if (!$stmt->fetch()) {
  $db->exec("ALTER TABLE templates ADD COLUMN video_loop TINYINT(1) NOT NULL DEFAULT 0 AFTER hero_text_color");
  echo "Column 'video_loop' added to templates table.\n";
} else {
  echo "Column 'video_loop' already exists.\n";
}
