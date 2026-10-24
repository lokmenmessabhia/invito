<?php
declare(strict_types=1);

/**
 * Migration: add map_iframe column to invitations table
 * Users can paste an embed iframe URL for the map display,
 * while map_embed is used for the yellow button link.
 */

require_once __DIR__ . '/config/database.php';

$db = getDB();

// Add map_iframe column
$db->exec("ALTER TABLE invitations ADD COLUMN map_iframe TEXT DEFAULT NULL AFTER map_embed");

echo "Migration complete: map_iframe column added.\n";
