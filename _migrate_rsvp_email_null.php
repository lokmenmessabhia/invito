<?php
declare(strict_types=1);
/**
 * Migration: make rsvps.email nullable (email field removed from RSVP form)
 *
 * Run via browser or CLI: php _migrate_rsvp_email_null.php
 */

require_once __DIR__ . '/config/database.php';

try {
  $db = getDB();

  // Check column info
  $stmt = $db->query("SHOW COLUMNS FROM rsvps LIKE 'email'");
  $col = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$col) {
    echo "Column 'email' not found in rsvps table.\n";
    exit(1);
  }

  if ($col['Null'] === 'YES') {
    echo "Column 'email' is already nullable. Nothing to do.\n";
    exit(0);
  }

  // Make nullable
  $db->exec("ALTER TABLE rsvps MODIFY email VARCHAR(100) DEFAULT NULL");

  echo "Migration successful: rsvps.email is now nullable.\n";
} catch (Exception $e) {
  echo 'Migration failed: ' . $e->getMessage() . "\n";
  exit(1);
}
