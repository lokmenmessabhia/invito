<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

echo "=== Migration : Ajout du type circoncision ===\n\n";

$db = getDB();

// 1. Alter ENUM to add 'circoncision'
echo "1. Mise à jour de l'ENUM invitation_type... ";
$db->exec("ALTER TABLE invitations MODIFY COLUMN invitation_type ENUM('couple','bac','circoncision') NOT NULL DEFAULT 'couple'");
echo "OK\n";

// 2. Insert the 'thor' template if not exists
$stmt = $db->prepare('SELECT id FROM templates WHERE folder_path = ? LIMIT 1');
$stmt->execute(['thor']);
if (!$stmt->fetch()) {
  echo "2. Création du template 'thor'... ";
  $db->prepare('INSERT INTO templates (name, folder_path, text_offset, text_delay, prefix_text, prefix_hidden, font_size_rem, prefix_font_size_rem, date_font_size_rem, date_offset, hero_text_color, video_loop, has_two_videos, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
    ->execute(['Circoncision', 'thor', 'center', 1.25, '', 1, 1.6, 1.0, 0.9, '', '#FFFFFF', 0, 0, 1]);
  echo "OK\n";
} else {
  echo "2. Template 'thor' existe déjà\n";
}

// 3. Create template folder if missing
$folder = __DIR__ . '/templates/thor';
if (!is_dir($folder)) {
  echo "3. Création du dossier templates/thor/... ";
  mkdir($folder, 0755, true);
  echo "OK\n";
} else {
  echo "3. Dossier templates/thor/ existe déjà\n";
}

echo "\n=== Migration terminée ===\n";