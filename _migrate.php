<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pdo = getDB();
$pdo->exec("ALTER TABLE invitations ADD COLUMN pfp_groom VARCHAR(255) DEFAULT NULL AFTER hero_text_color");
echo "Added pfp_groom.\n";
$pdo->exec("ALTER TABLE invitations ADD COLUMN pfp_bride VARCHAR(255) DEFAULT NULL AFTER pfp_groom");
echo "Added pfp_bride.\n";
if (!is_dir(__DIR__ . '/uploads/pfps')) {
  mkdir(__DIR__ . '/uploads/pfps', 0755, true);
  echo "Created uploads/pfps/.\n";
}
