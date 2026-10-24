<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();

$db->exec("ALTER TABLE invitations ADD COLUMN pfp_groom_transform VARCHAR(100) DEFAULT NULL AFTER pfp_groom");
$db->exec("ALTER TABLE invitations ADD COLUMN pfp_bride_transform VARCHAR(100) DEFAULT NULL AFTER pfp_bride");
echo "Added pfp_groom_transform and pfp_bride_transform columns.\n";
