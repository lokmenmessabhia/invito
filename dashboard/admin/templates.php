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
  <title>Admin — Templates</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
  <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
  <div id="loading"><div class="spinner"></div> Chargement...</div>

  <aside class="sidebar">
    <div class="brand">Invito<span>.</span></div>
    <div class="user-info" id="userInfo"><strong>Admin</strong><span>Administrateur</span></div>
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>dashboard/admin/index.php"><span class="icon"><i class="fas fa-home"></i></span><span>Tableau de Bord</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/users.php"><span class="icon"><i class="fas fa-users"></i></span><span>Utilisateurs</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/templates.php" class="active"><span class="icon"><i class="fas fa-palette"></i></span><span>Templates</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/songs.php"><span class="icon"><i class="fas fa-music"></i></span><span>Chansons</span></a>
    </nav>
    <div class="sidebar-footer"><button class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button></div>
  </aside>

  <main class="main">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div><h1>Templates</h1><p>Gérer les thèmes d'invitation</p></div>
      <a href="<?= BASE_URL ?>dashboard/admin/edit-template.php" class="btn btn-gold">+ Nouveau Template</a>
    </div>

    <div id="alert" class="alert"></div>

    <div class="card">
      <div class="card-body">
        <div class="table-wrap">
            <table>
              <thead><tr><th>ID</th><th>Nom</th><th>Dossier</th><th>Fichiers</th><th>Statut</th><th>Créé le</th><th>Actions</th></tr></thead>
              <tbody id="templateTable"><tr><td colspan="7">Chargement...</td></tr></tbody>
            </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Delete Confirm -->
  <div class="modal-overlay" id="delModal">
    <div class="modal">
      <h3>Confirmer la suppression</h3>
      <p id="delBody" style="margin-bottom:16px;color:var(--dark-light);"></p>
      <div class="modal-actions">
        <button class="btn btn-outline" onclick="document.getElementById('delModal').classList.remove('open')">Annuler</button>
        <button class="btn btn-danger" id="delConfirm">Supprimer</button>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
  <script>
    let templates = [];
    let deleteTarget = null;

    document.addEventListener('DOMContentLoaded', async function () {
      const user = await checkAuth();
      if (!user) return;
      document.querySelector('#userInfo strong').textContent = user.username;
      loadTemplates();
    });

    async function loadTemplates() {
      try {
        const res = await API.get(BASE_URL + 'api/admin/templates.php');
        templates = res.data.templates;
        renderTemplates();
      } catch (err) {
        showAlert(err.message);
      }
      showLoading(false);
    }

    function renderTemplates() {
      const tbody = document.getElementById('templateTable');
      if (templates.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">Aucun template</td></tr>';
        return;
      }
      tbody.innerHTML = templates.map(function (t) {
        const badge = t.is_active == 1
          ? '<span class="badge badge-pub">Actif</span>'
          : '<span class="badge badge-draft">Inactif</span>';
        const date = t.created_at ? new Date(t.created_at).toLocaleDateString('fr-FR') : '-';
        const f = t.files || {};
        var filesHtml = '<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">';
        filesHtml += '<span class="file-badge ' + (f.video_mp4 ? 'file-ok' : 'file-missing') + '">' + (f.video_mp4 ? '&#9989;' : '&#10060;') + ' MP4</span>';
        filesHtml += '<span class="file-badge ' + (f.video_webm ? 'file-ok' : 'file-missing') + '">' + (f.video_webm ? '&#9989;' : '&#10060;') + ' WEBM</span>';
        filesHtml += '<span class="file-badge ' + (t.songs && t.songs.length ? 'file-ok' : 'file-missing') + '">' + (t.songs && t.songs.length ? '&#9989;' : '&#10060;') + ' MP3</span>';
        filesHtml += '</div>';
        return '<tr>' +
          '<td>' + t.id + '</td>' +
          '<td><strong>' + h(t.name) + '</strong></td>' +
          '<td><code>' + h(t.folder_path) + '</code></td>' +
          '<td>' + filesHtml + '</td>' +
          '<td>' + badge + '</td>' +
          '<td>' + date + '</td>' +
          '<td style="display:flex;gap:8px;">' +
            '<a href="<?= BASE_URL ?>dashboard/admin/edit-template.php?id=' + t.id + '" class="btn btn-sm btn-outline">Modifier</a>' +
            '<button class="btn btn-sm btn-danger" onclick="confirmDelete(' + t.id + ')">Suppr.</button>' +
          '</td>' +
        '</tr>';
      }).join('');
    }

    function confirmDelete(id) {
      const t = templates.find(function (x) { return x.id == id; });
      if (!t) return;
      deleteTarget = id;
      document.getElementById('delBody').textContent = 'Supprimer "' + t.name + '" ? Les invitations utilisant ce template seront réinitialisées.';
      document.getElementById('delModal').classList.add('open');
    }

    document.getElementById('delConfirm').addEventListener('click', async function () {
      if (!deleteTarget) return;
      try {
        await API.del(BASE_URL + 'api/admin/templates.php', { id: deleteTarget });
        document.getElementById('delModal').classList.remove('open');
        showAlert('Template supprimé.', 'success');
        deleteTarget = null;
        loadTemplates();
      } catch (err) {
        showAlert(err.message);
      }
    });

    function toggleSidebar() {
      document.body.classList.toggle('sidebar-open');
    }
  </script>

</html>
