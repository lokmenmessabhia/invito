<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/validate-token.php';

setCorsHeaders();
setSecurityHeaders();

$admin = requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  jsonError('Method not allowed.', 405);
}

$stats = [];

$stats['total_users'] = (int)$db->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetchColumn();
$stats['total_admins'] = (int)$db->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetchColumn();
$stats['total_invitations'] = (int)$db->query('SELECT COUNT(*) FROM invitations')->fetchColumn();
$stats['published_invitations'] = (int)$db->query('SELECT COUNT(*) FROM invitations WHERE is_published = 1')->fetchColumn();
$stats['total_rsvps'] = (int)$db->query('SELECT COUNT(*) FROM rsvps')->fetchColumn();
$stats['total_templates'] = (int)$db->query('SELECT COUNT(*) FROM templates')->fetchColumn();
$stats['active_templates'] = (int)$db->query('SELECT COUNT(*) FROM templates WHERE is_active = 1')->fetchColumn();

// Recent RSVPs
$stmt = $db->query(
  'SELECT r.name, r.attendance, r.guests, i.slug
   FROM rsvps r
   JOIN invitations i ON i.id = r.invitation_id
   ORDER BY r.created_at DESC
   LIMIT 10'
);
$stats['recent_rsvps'] = $stmt->fetchAll();

jsonSuccess($stats);
