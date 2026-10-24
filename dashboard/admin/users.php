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
  <title>Admin — Utilisateurs</title>
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
      <a href="<?= BASE_URL ?>dashboard/admin/users.php" class="active"><span class="icon"><i class="fas fa-users"></i></span><span>Utilisateurs</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/templates.php"><span class="icon"><i class="fas fa-palette"></i></span><span>Templates</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/songs.php"><span class="icon"><i class="fas fa-music"></i></span><span>Chansons</span></a>
    </nav>
    <div class="sidebar-footer"><button class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button></div>
  </aside>

  <main class="main">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div><h1>Utilisateurs</h1><p>Gérer les comptes des couples</p></div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>dashboard/admin/edit-user.php" class="btn btn-gold">+ Nouvel Utilisateur</a>
        <button class="btn btn-blue" onclick="openBatchModal()"><i class="fas fa-users"></i> Création en masse</button>
      </div>
    </div>

    <div id="alert" class="alert"></div>

    <div class="card">
      <div class="card-body">
        <div class="table-wrap">
          <table>
            <thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Invitation</th><th>RSVPs</th><th>Créé le</th><th>Actions</th></tr></thead>
            <tbody id="userTable"><tr><td colspan="8">Chargement...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Batch Create Modal -->
  <div class="modal-overlay" id="batchModal">
    <div class="modal" style="max-width:560px;">
      <h3>Création en masse</h3>
      <p style="color:var(--gray-500);font-size:14px;margin-bottom:20px;">Créez plusieurs comptes utilisateurs en une seule fois.</p>
      <form id="batchForm" autocomplete="off">
        <div class="form-row">
          <div class="form-group">
            <label for="batchPrefix">Préfixe du login</label>
            <input type="text" class="form-control" id="batchPrefix" placeholder="ex: couple" value="couple" required />
          </div>
          <div class="form-group">
            <label for="batchCount">Nombre</label>
            <input type="number" class="form-control" id="batchCount" value="5" min="1" max="100" required />
          </div>
        </div>
        <div class="form-group">
          <label for="batchRole">Rôle</label>
          <select class="form-control" id="batchRole">
            <option value="user">Utilisateur</option>
            <option value="admin">Administrateur</option>
          </select>
        </div>
        <div class="form-group">
          <label for="batchType">Type d'invitation</label>
          <select class="form-control" id="batchType">
            <option value="couple">Mariage (Couple)</option>
            <option value="bac">Baccalauréat (Diplômé)</option>
            <option value="circoncision">Circoncision</option>
          </select>
        </div>
        <div class="form-group">
          <label>Mot de passe</label>
          <div style="padding:10px 14px;background:var(--gray-50);border-radius:8px;border:1px solid var(--gray-200);font-size:14px;color:var(--gray-500);">
            <i class="fas fa-magic" style="color:var(--gold);margin-right:6px;"></i>
            Généré automatiquement : <code style="color:var(--navy);">préfixe1</code>, <code style="color:var(--navy);">préfixe2</code>...
          </div>
          <p style="font-size:12px;color:var(--gray-400);margin-top:4px;">Chaque utilisateur reçoit son identifiant comme mot de passe.</p>
        </div>
        <div id="batchProgress" style="display:none;margin-bottom:16px;">
          <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--gray-500);margin-bottom:6px;">
            <span id="batchProgressText">Création en cours...</span>
            <span id="batchProgressCount">0 / 0</span>
          </div>
          <div style="width:100%;height:6px;background:var(--gray-200);border-radius:4px;overflow:hidden;">
            <div id="batchProgressBar" style="width:0%;height:100%;background:var(--gold);border-radius:4px;transition:width 0.3s;"></div>
          </div>
        </div>
        <div id="batchResults" style="display:none;">
          <label style="display:block;font-size:12px;font-weight:600;color:var(--gray-500);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Résultats — Copiez les identifiants</label>
          <div style="max-height:240px;overflow-y:auto;border:1px solid var(--gray-200);border-radius:8px;">
            <table style="font-size:13px;">
              <thead><tr><th>Identifiant</th><th>Mot de passe</th><th>Email</th></tr></thead>
              <tbody id="batchResultsBody"></tbody>
            </table>
          </div>
          <button type="button" class="btn btn-sm btn-outline" style="margin-top:8px;" onclick="copyBatchResults()"><i class="fas fa-copy"></i> Copier tout</button>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-outline" onclick="closeBatchModal()">Fermer</button>
          <button type="submit" class="btn btn-blue" id="batchSubmit"><i class="fas fa-users"></i> Créer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirm Modal -->
  <div class="modal-overlay" id="delModal">
    <div class="modal">
      <h3>Confirmer la suppression</h3>
      <p id="delBody" style="margin-bottom:16px;color:var(--gray-500);">Voulez-vous supprimer cet utilisateur ?</p>
      <div class="modal-actions">
        <button class="btn btn-outline" onclick="document.getElementById('delModal').classList.remove('open')">Annuler</button>
        <button class="btn btn-danger" id="delConfirm">Supprimer</button>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
  <script>
    let users = [];
    let deleteTarget = null;
    let lastCreatedPw = '';

    document.addEventListener('DOMContentLoaded', async function () {
      const user = await checkAuth();
      if (!user) return;
      document.querySelector('#userInfo strong').textContent = user.username;
      loadUsers();
    });

    async function loadUsers() {
      try {
        const res = await API.get(BASE_URL + 'api/admin/users.php');
        users = res.data.users;
        renderUsers();
      } catch (err) {
        showAlert(err.message);
      }
      showLoading(false);
    }

    function renderUsers() {
      const tbody = document.getElementById('userTable');
      if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8">Aucun utilisateur</td></tr>';
        return;
      }

      tbody.innerHTML = users.map(function (u) {
        const roleBadge = u.role === 'admin'
          ? '<span class="badge badge-admin">Admin</span>'
          : '<span class="badge badge-user">User</span>';
        const invBadge = u.has_invitation == 1
          ? '<span class="badge badge-pub">Publiée</span>'
          : '<span class="badge badge-draft">Brouillon</span>';
        const date = u.created_at ? new Date(u.created_at).toLocaleDateString('fr-FR') : '-';
        return '<tr>' +
          '<td>' + u.id + '</td>' +
          '<td><strong>' + h(u.username) + '</strong></td>' +
          '<td>' + h(u.email) + '</td>' +
          '<td>' + roleBadge + '</td>' +
          '<td>' + invBadge + '</td>' +
          '<td>' + u.rsvp_count + '</td>' +
          '<td>' + date + '</td>' +
          '<td style="display:flex;gap:8px;">' +
            (u.invitation_slug ? '<a href="<?= BASE_URL ?>invitation.php?slug=' + u.invitation_slug + '" class="btn btn-sm btn-blue" target="_blank" rel="noopener">Ouvrir</a>' : '') +
            '<a href="<?= BASE_URL ?>dashboard/admin/edit-user.php?id=' + u.id + '" class="btn btn-sm btn-outline">Modifier</a>' +
            '<button class="btn btn-sm btn-danger" onclick="confirmDelete(' + u.id + ')">Suppr.</button>' +
          '</td>' +
        '</tr>';
      }).join('');
    }

    // ===== BATCH CREATION =====
    let batchCreatedUsers = [];

    function openBatchModal() {
      document.getElementById('batchPrefix').value = 'couple';
      document.getElementById('batchCount').value = 5;
      document.getElementById('batchRole').value = 'user';
      document.getElementById('batchType').value = 'couple';
      document.getElementById('batchPassword').value = 'Welcome123';
      document.getElementById('batchProgress').style.display = 'none';
      document.getElementById('batchResults').style.display = 'none';
      document.getElementById('batchSubmit').innerHTML = '<i class="fas fa-users"></i> Créer';
      document.getElementById('batchSubmit').disabled = false;
      batchCreatedUsers = [];
      document.getElementById('batchModal').classList.add('open');
    }

    function closeBatchModal() {
      document.getElementById('batchModal').classList.remove('open');
    }

    document.getElementById('batchForm').addEventListener('submit', async function (e) {
      e.preventDefault();

      var prefix = document.getElementById('batchPrefix').value.trim();
      var count = parseInt(document.getElementById('batchCount').value);
      var role = document.getElementById('batchRole').value;
      var invType = document.getElementById('batchType').value;

      if (!prefix) { showAlert('Préfixe requis.'); return; }
      if (isNaN(count) || count < 1 || count > 100) { showAlert('Nombre entre 1 et 100.'); return; }

      document.getElementById('batchProgress').style.display = 'block';
      document.getElementById('batchResults').style.display = 'none';
      document.getElementById('batchSubmit').disabled = true;
      document.getElementById('batchSubmit').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';

      batchCreatedUsers = [];
      var done = 0;

      for (var i = 1; i <= count; i++) {
        var uname = prefix + i;
        var pw = uname;
        var email = uname + '@invitation.com';

        document.getElementById('batchProgressText').textContent = 'Création de ' + uname + '...';
        document.getElementById('batchProgressCount').textContent = done + ' / ' + count;
        document.getElementById('batchProgressBar').style.width = (done / count * 100) + '%';

        try {
          var res = await API.rawPost(BASE_URL + 'api/admin/users.php', {
            username: uname,
            email: email,
            password: pw,
            role: role,
            invitation_type: invType,
          });
          if (res.ok) {
            batchCreatedUsers.push({ username: uname, password: pw, email: email });
            done++;
          } else {
            batchCreatedUsers.push({ username: uname, password: '-', email: email, error: res.data ? res.data.error : 'Erreur' });
          }
        } catch (err) {
          batchCreatedUsers.push({ username: uname, password: '-', email: email, error: err.message });
        }
      }

      document.getElementById('batchProgressCount').textContent = done + ' / ' + count;
      document.getElementById('batchProgressBar').style.width = '100%';
      document.getElementById('batchProgressText').textContent = 'Terminé — ' + done + ' utilisateur(s) créé(s)';

      renderBatchResults();
      document.getElementById('batchResults').style.display = 'block';
      document.getElementById('batchSubmit').disabled = false;
      document.getElementById('batchSubmit').innerHTML = '<i class="fas fa-users"></i> Créer';

      loadUsers();
    });

    function renderBatchResults() {
      var tbody = document.getElementById('batchResultsBody');
      tbody.innerHTML = batchCreatedUsers.map(function (u) {
        var cls = u.error ? 'style="color:#991B1B;"' : '';
        return '<tr ' + cls + '>' +
          '<td>' + h(u.username) + '</td>' +
          '<td>' + h(u.password) + '</td>' +
          '<td>' + h(u.email) + '</td>' +
        '</tr>';
      }).join('');
    }

    function copyBatchResults() {
      var text = batchCreatedUsers.map(function (u) {
        return u.username + '\t' + u.password + '\t' + u.email;
      }).join('\n');
      navigator.clipboard.writeText(text).then(function () {
        showAlert('Identifiants copiés dans le presse-papier.', 'success');
      });
    }

    function copyPassword() {
      var pw = document.getElementById('passwordDisplay').textContent;
      navigator.clipboard.writeText(pw).then(function () {
        showAlert('Mot de passe copié.', 'success');
      });
    }

    // Helper to call API and get raw response
    if (!API.rawPost) {
      API.rawPost = async function (url, data) {
        var token = localStorage.getItem('invito_token');
        var res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + (token || '') },
          body: JSON.stringify(data),
        });
        var json = await res.json();
        return { ok: res.ok, data: json };
      };
    }

    // ===== DELETE =====
    function confirmDelete(id) {
      const u = users.find(function (x) { return x.id == id; });
      if (!u) return;
      deleteTarget = id;
      document.getElementById('delBody').textContent = 'Supprimer définitivement "' + u.username + '" ? Cette action est irréversible.';
      document.getElementById('delModal').classList.add('open');
    }

    document.getElementById('delConfirm').addEventListener('click', async function () {
      if (!deleteTarget) return;
      try {
        await API.del(BASE_URL + 'api/admin/users.php', { id: deleteTarget });
        document.getElementById('delModal').classList.remove('open');
        showAlert('Utilisateur supprimé.', 'success');
        deleteTarget = null;
        loadUsers();
      } catch (err) {
        showAlert(err.message);
      }
    });

    function closeModal() { document.getElementById('modal').classList.remove('open'); }

    function toggleSidebar() {
      document.body.classList.toggle('sidebar-open');
    }
  </script>
</body>
</html>
