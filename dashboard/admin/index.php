<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin — Tableau de Bord</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
  <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
  <div id="loading"><div class="spinner"></div> Chargement...</div>

  <aside class="sidebar">
    <div class="brand">Invito<span>.</span></div>
    <div class="user-info" id="userInfo">
      <strong>Admin</strong>
      <span>Administrateur</span>
    </div>
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>dashboard/admin/index.php" class="active"><span class="icon"><i class="fas fa-home"></i></span><span>Tableau de Bord</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/users.php"><span class="icon"><i class="fas fa-users"></i></span><span>Utilisateurs</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/templates.php"><span class="icon"><i class="fas fa-palette"></i></span><span>Templates</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/songs.php"><span class="icon"><i class="fas fa-music"></i></span><span>Chansons</span></a>
    </nav>
    <div class="sidebar-footer">
      <button class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
    </div>
  </aside>

  <main class="main">
    <div class="page-header">
      <h1>Tableau de Bord</h1>
      <p>Aperçu général de la plateforme Invito</p>
    </div>

    <div class="stats-grid" id="statsGrid">
      <div class="stat-card"><div class="num">-</div><div class="label">Utilisateurs</div></div>
      <div class="stat-card"><div class="num">-</div><div class="label">Administrateurs</div></div>
      <div class="stat-card"><div class="num">-</div><div class="label">Invitations</div></div>
      <div class="stat-card"><div class="num">-</div><div class="label">Publiées</div></div>
      <div class="stat-card"><div class="num">-</div><div class="label">RSVPs Reçus</div></div>
      <div class="stat-card"><div class="num">-</div><div class="label">Templates Actifs</div></div>
    </div>

    <div class="card">
      <div class="card-header"><h2>Derniers RSVPs</h2></div>
      <div class="card-body">
        <div class="table-wrap">
          <table>
            <thead><tr><th>Nom</th><th>Statut</th><th>Invités</th><th>Invitation</th></tr></thead>
            <tbody id="rsvpTable"><tr><td colspan="4">Aucune donnée</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal-overlay" id="modal">
    <div class="modal">
      <h3 id="modalTitle">Confirmer</h3>
      <p id="modalBody" style="margin-bottom:16px;color:var(--dark-light);"></p>
      <div class="modal-actions">
        <button class="btn btn-outline" onclick="closeModal()">Annuler</button>
        <button class="btn btn-danger" id="modalConfirm">Confirmer</button>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', async function () {
      const user = await checkAuth();
      if (!user) return;

      document.querySelector('#userInfo strong').textContent = user.username;

      try {
        const res = await API.get(BASE_URL + 'api/admin/stats.php');
        const d = res.data;

        const cards = document.querySelectorAll('#statsGrid .stat-card .num');
        cards[0].textContent = d.total_users;
        cards[1].textContent = d.total_admins;
        cards[2].textContent = d.total_invitations;
        cards[3].textContent = d.published_invitations;
        cards[4].textContent = d.total_rsvps;
        cards[5].textContent = d.active_templates;

        const tbody = document.getElementById('rsvpTable');
        tbody.innerHTML = '';
        if (d.recent_rsvps.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4">Aucun RSVP pour le moment</td></tr>';
        } else {
          d.recent_rsvps.forEach(function (r) {
            const badge = r.attendance === 'yes'
              ? '<span class="badge badge-yes">Présent</span>'
              : '<span class="badge badge-no">Absent</span>';
            tbody.innerHTML += '<tr><td>' + h(r.name) + '</td><td>' + badge + '</td><td>' + r.guests + '</td><td><a href="<?= BASE_URL ?>invitation.php?slug=' + encodeURIComponent(r.slug) + '" target="_blank" style="color:var(--gold);">Voir</a></td></tr>';
          });
        }
      } catch (err) {
        showAlert(err.message);
      }

      showLoading(false);
    });

    function closeModal() { document.getElementById('modal').classList.remove('open'); }

    function toggleSidebar() {
      document.body.classList.toggle('sidebar-open');
    }
  </script>
</body>
</html>
