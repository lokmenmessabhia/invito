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
  <title>Admin — Modifier Utilisateur</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    .user-header {
      display: flex; align-items: center; gap: 16px; margin-bottom: 24px;
    }
    .user-avatar {
      width: 56px; height: 56px; border-radius: 50%;
      background: var(--navy); color: var(--gold);
      display: flex; align-items: center; justify-content: center;
      font-size: 24px; font-weight: 700;
    }
    .user-meta { flex: 1; }
    .user-meta h2 { font-size: 20px; color: var(--navy); margin-bottom: 2px; }
    .user-meta p { font-size: 14px; color: var(--gray-500); }
    .info-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
    }
    .info-item {
      padding: 14px 16px; background: var(--gray-50);
      border-radius: var(--radius-sm); border: 1px solid var(--gray-100);
    }
    .info-item .lbl { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500); font-weight: 600; margin-bottom: 2px; }
    .info-item .val { font-size: 15px; color: var(--navy); font-weight: 500; }
    .alert-code {
      background: #FEF3C7; padding: 4px 8px; border-radius: 4px;
      font-family: monospace; font-size: 14px; color: #92400E;
    }
    .pw-display {
      display: flex; align-items: center; gap: 8px;
      padding: 10px 14px; background: #FEF3C7;
      border: 1px solid #FDE68A; border-radius: 8px;
      font-family: 'Courier New', monospace; font-size: 16px; color: #92400E;
      letter-spacing: 1px;
    }
    .pw-display .hint {
      font-family: -apple-system, sans-serif; font-size: 12px;
      color: #A16207; letter-spacing: normal;
    }
  </style>
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
    <div class="page-header" style="display:flex;align-items:center;gap:12px;">
      <a href="<?= BASE_URL ?>dashboard/admin/users.php" class="btn btn-sm btn-outline"><i class="fas fa-arrow-left"></i> Retour</a>
      <div>
        <h1 id="pageTitle">Modifier Utilisateur</h1>
        <p id="pageSub" style="color:var(--gray-500);font-size:14px;">Consultez et modifiez les informations du compte</p>
      </div>
    </div>

    <div id="alert" class="alert"></div>

    <div class="card" id="userInfoCard">
      <div class="card-header"><h2>Informations du compte</h2></div>
      <div class="card-body">
        <div class="user-header">
          <div class="user-avatar"><i class="fas fa-user"></i></div>
          <div class="user-meta">
            <h2 id="dispUsername">-</h2>
            <p id="dispEmail">-</p>
          </div>
          <div id="dispRole" style="margin-left:auto;"></div>
        </div>
        <div class="info-grid">
          <div class="info-item">
            <div class="lbl">ID</div>
            <div class="val" id="dispId">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Rôle</div>
            <div class="val" id="dispRole2">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Invitation</div>
            <div class="val" id="dispInvitation">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">RSVPs</div>
            <div class="val" id="dispRsvp">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Créé le</div>
            <div class="val" id="dispCreated">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Mot de passe</div>
            <div class="val" id="dispPassword">-</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h2>Modifier</h2></div>
      <div class="card-body">
        <form id="editForm" autocomplete="off">
          <input type="hidden" id="editId" value="" />
          <div class="form-row">
            <div class="form-group">
              <label for="editUsername">Identifiant</label>
              <input type="text" class="form-control" id="editUsername" required />
            </div>
            <div class="form-group">
              <label for="editEmail">Email</label>
              <input type="email" class="form-control" id="editEmail" required />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="editRole">Rôle</label>
              <select class="form-control" id="editRole">
                <option value="user">Utilisateur</option>
                <option value="admin">Administrateur</option>
              </select>
            </div>
            <div class="form-group">
              <label for="editPassword">Nouveau mot de passe</label>
              <input type="text" class="form-control" id="editPassword" placeholder="Laisser vide pour ne pas changer" />
              <p style="font-size:12px;color:var(--gray-400);margin-top:4px;">Min. 8 caractères. Remplissez pour changer.</p>
            </div>
          </div>
          <div style="display:flex;gap:12px;margin-top:8px;">
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Enregistrer</button>
            <a href="<?= BASE_URL ?>dashboard/admin/users.php" class="btn btn-outline">Annuler</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Invitation Settings (admin only) -->
    <div class="card" id="invitationCard" style="display:none;">
      <div class="card-header"><h2>Paramètres de l'invitation</h2></div>
      <div class="card-body">
        <form id="invitationForm" autocomplete="off">
          <div class="form-row">
            <div class="form-group">
              <label for="inv_template_id">Template</label>
              <select class="form-control" id="inv_template_id">
                <option value="">Sélectionner un template</option>
              </select>
            </div>
            <div class="form-group">
              <label for="inv_song_path">Chanson</label>
              <select class="form-control" id="inv_song_path">
                <option value="">Par défaut du template</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
          <label for="inv_type">Type d'invitation</label>
          <select class="form-control" id="inv_type">
            <option value="couple">Mariage (Couple)</option>
            <option value="bac">Baccalauréat (Diplômé)</option>
            <option value="circoncision">Circoncision</option>
          </select>
            </div>
            <div class="form-group">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="inv_text_offset">Position du texte</label>
              <select class="form-control" id="inv_text_offset">
                <option value="">Par défaut du template</option>
                <option value="center">Centré</option>
                <option value="top">Haut</option>
                <option value="bottom">Bas</option>
                <option value="bottom-third">3e tiers (bas)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="inv_font_size_rem">Taille noms (rem)</label>
              <input type="number" class="form-control" id="inv_font_size_rem" min="0.8" max="4.0" step="0.1" placeholder="Par défaut du template" />
            </div>
            <div class="form-group">
              <label for="inv_prefix_font_size_rem">Taille en-tête (rem)</label>
              <input type="number" class="form-control" id="inv_prefix_font_size_rem" min="0.5" max="3.0" step="0.1" placeholder="Par défaut du template" />
            </div>
            <div class="form-group">
              <label for="inv_date_font_size_rem">Taille date (rem)</label>
              <input type="number" class="form-control" id="inv_date_font_size_rem" min="0.5" max="2.5" step="0.1" placeholder="Par défaut du template" />
            </div>
            <div class="form-group">
              <label for="inv_date_offset">Position de la date (pct:NN)</label>
              <input type="text" class="form-control" id="inv_date_offset" placeholder="ex: pct:85 ou laisser vide" />
              <p style="font-size:12px;color:var(--gray-500);margin-top:4px;">Laissez vide pour utiliser la position automatique.</p>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="inv_hero_text_color">Couleur du texte vidéo</label>
              <div style="display:flex;gap:12px;align-items:center;">
                <input type="color" class="form-control" id="inv_hero_text_color" style="width:60px;height:44px;padding:4px;cursor:pointer;" />
                <input type="text" class="form-control" id="inv_hero_text_color_hex" placeholder="#FFFFFF" style="flex:1;font-family:monospace;" />
              </div>
              <p style="font-size:12px;color:var(--gray-500);margin-top:4px;">Laissez vide pour utiliser la couleur par défaut du template.</p>
            </div>
            <div class="form-group">
              <label for="inv_text_color">Couleur du texte (page)</label>
              <div style="display:flex;gap:12px;align-items:center;">
                <input type="color" class="form-control" id="inv_text_color" style="width:60px;height:44px;padding:4px;cursor:pointer;" />
                <input type="text" class="form-control" id="inv_text_color_hex" placeholder="#2C2420" style="flex:1;font-family:monospace;" />
              </div>
            </div>
            <div class="form-group">
              <label for="inv_prefix_text">Texte d'en-tête</label>
              <input type="text" class="form-control" id="inv_prefix_text" placeholder="Mariage de" />
              <label style="font-size:13px;color:var(--gray-500);margin-top:6px;display:flex;align-items:center;gap:6px;">
                <input type="checkbox" id="inv_prefix_hidden" />
                Masquer le texte d'en-tête
              </label>
            </div>
            <div class="form-group">
              <label for="inv_font_size_rem">Taille du texte (rem)</label>
              <input type="number" class="form-control" id="inv_font_size_rem" min="0.8" max="4.0" step="0.1" placeholder="Par défaut du template" />
            <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:12px;gap:16px;">
              <label>
                <input type="checkbox" id="inv_video_loop" />
                Vidéo en boucle
              </label>
              <label>
                <input type="checkbox" id="inv_is_published" />
                Publiée
              </label>
            </div>
          </div>
          <div style="display:flex;gap:12px;margin-top:8px;">
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Enregistrer l'invitation</button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
  <script>
    var userId = new URLSearchParams(window.location.search).get('id');

    document.addEventListener('DOMContentLoaded', async function () {
      var user = await checkAuth();
      if (!user) return;
      document.querySelector('#userInfo strong').textContent = user.username;

      if (userId) {
        document.getElementById('editPassword').placeholder = 'Laisser vide pour ne pas changer';
        await loadUser();
        await loadInvitationData();
      } else {
        document.getElementById('pageTitle').textContent = 'Nouvel Utilisateur';
        document.getElementById('pageSub').textContent = 'Créez un nouveau compte';
        document.getElementById('userInfoCard').style.display = 'none';
        document.getElementById('editPassword').placeholder = 'Min. 8 caractères';
        document.getElementById('editPassword').required = true;
        document.querySelector('#editForm .btn-gold').innerHTML = '<i class="fas fa-plus"></i> Créer';
      }
      showLoading(false);
    });

    async function loadUser() {
      try {
        var res = await API.get(BASE_URL + 'api/admin/users.php?id=' + encodeURIComponent(userId));
        var u = res.data.user;
        if (!u) { showAlert('Utilisateur introuvable.'); return; }

        document.getElementById('pageTitle').textContent = u.username;
        document.getElementById('pageSub').textContent = 'Consultez et modifiez les informations de ' + u.username;
        document.getElementById('dispUsername').textContent = u.username;
        document.getElementById('dispEmail').textContent = u.email;
        document.getElementById('dispId').textContent = u.id;

        var roleBadge = u.role === 'admin'
          ? '<span class="badge badge-admin">Administrateur</span>'
          : '<span class="badge badge-user">Utilisateur</span>';
        document.getElementById('dispRole').innerHTML = roleBadge;
        document.getElementById('dispRole2').textContent = u.role === 'admin' ? 'Administrateur' : 'Utilisateur';

        if (u.invitation_slug) {
          var status = u.has_invitation == 1 ? 'Publiée' : 'Brouillon';
          document.getElementById('dispInvitation').innerHTML =
            '<a href="<?= BASE_URL ?>invitation.php?slug=' + encodeURIComponent(u.invitation_slug) + '" target="_blank" style="color:var(--navy-light);">' +
            status + ' <i class="fas fa-external-link-alt" style="font-size:11px;"></i></a>';
        } else {
          document.getElementById('dispInvitation').textContent = 'Aucune';
        }

        document.getElementById('dispRsvp').textContent = (u.rsvp_yes || 0) + ' présents / ' + (u.rsvp_no || 0) + ' absents (' + u.rsvp_count + ' total)';
        document.getElementById('dispCreated').textContent = u.created_at ? new Date(u.created_at).toLocaleDateString('fr-FR') : '-';

        var savedPw = sessionStorage.getItem('pw_' + u.id);
        if (savedPw) {
          document.getElementById('dispPassword').innerHTML = '<span class="pw-display"><i class="fas fa-key" style="font-size:14px;"></i> ' + h(savedPw) + ' <span class="hint">(visible dans cette session)</span></span>';
        } else {
          document.getElementById('dispPassword').textContent = '(haché — définissez un nouveau pour afficher)';
        }

        document.getElementById('editId').value = u.id;
        document.getElementById('editUsername').value = u.username;
        document.getElementById('editEmail').value = u.email;
        document.getElementById('editRole').value = u.role;
      } catch (err) {
        showAlert(err.message);
      }
    }

    document.getElementById('editForm').addEventListener('submit', async function (e) {
      e.preventDefault();
      var id = document.getElementById('editId').value;
      var data = {
        username: document.getElementById('editUsername').value.trim(),
        email: document.getElementById('editEmail').value.trim(),
        role: document.getElementById('editRole').value,
      };
      var pw = document.getElementById('editPassword').value.trim();

      if (id) {
        data.id = parseInt(id);
        if (pw) {
          if (pw.length < 8) { showAlert('Le mot de passe doit faire au moins 8 caractères.'); return; }
          data.password = pw;
        }
          try {
            var res = await API.put(BASE_URL + 'api/admin/users.php', data);
            var msg = 'Utilisateur modifié avec succès.';
            if (pw) {
              msg += '<br>Nouveau mot de passe : <span class="alert-code">' + h(pw) + '</span>';
              sessionStorage.setItem('pw_' + id, pw);
            }
            showAlert(msg, 'success');
            await loadUser();
            document.getElementById('editPassword').value = '';
          } catch (err) {
            showAlert(err.message);
          }
        } else {
        if (!pw) { showAlert('Mot de passe requis.'); return; }
        if (pw.length < 8) { showAlert('Le mot de passe doit faire au moins 8 caractères.'); return; }
        data.password = pw;
        data.invitation_type = document.getElementById('inv_type') ? document.getElementById('inv_type').value : 'couple';
        try {
          var res = await API.post(BASE_URL + 'api/admin/users.php', data);
          var newId = res.data.user_id;
          sessionStorage.setItem('pw_' + newId, pw);
          showAlert(
            'Utilisateur <strong>' + h(data.username) + '</strong> créé<br>' +
            'Identifiant : <code style="background:#FEF3C7;padding:2px 6px;border-radius:4px;">' + h(data.username) + '</code><br>' +
            'Mot de passe : <code style="background:#FEF3C7;padding:2px 6px;border-radius:4px;">' + h(pw) + '</code>',
            'success'
          );
          document.getElementById('editId').value = newId;
          document.getElementById('editPassword').value = '';
          document.getElementById('editPassword').required = false;
          document.getElementById('editPassword').placeholder = 'Laisser vide pour ne pas changer';
          document.querySelector('#editForm .btn-gold').innerHTML = '<i class="fas fa-save"></i> Enregistrer';
          document.getElementById('pageTitle').textContent = data.username;
          document.getElementById('pageSub').textContent = 'Consultez et modifiez les informations de ' + data.username;
          document.getElementById('userInfoCard').style.display = 'block';
          await loadUser();
        } catch (err) {
          showAlert(err.message);
        }
      }
    });

    // --- Invitation editor ---
    var allSongs = [];
    var allTemplates = [];

    async function loadInvitationData() {
      try {
        // Load templates
        var tmplRes = await fetch(BASE_URL + 'api/admin/templates.php', {
          headers: { 'Authorization': 'Bearer ' + API.getToken() }
        });
        var tmplData = await tmplRes.json();
        var tmplSelect = document.getElementById('inv_template_id');
        if (tmplData.data && tmplData.data.templates) {
          allTemplates = tmplData.data.templates;
          allTemplates.forEach(function (t) {
            var opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            tmplSelect.appendChild(opt);
          });
        }

        // Load songs
        var songsRes = await fetch(BASE_URL + 'api/admin/songs.php', {
          headers: { 'Authorization': 'Bearer ' + API.getToken() }
        });
        var songsData = await songsRes.json();
        var songSel = document.getElementById('inv_song_path');
        if (songsData.data && songsData.data.songs) {
          allSongs = songsData.data.songs;
          allSongs.forEach(function (s) {
            var opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s.replace('.mp3', '');
            songSel.appendChild(opt);
          });
        }

        // Load invitation
        var invRes = await fetch(BASE_URL + 'api/admin/invitation.php?user_id=' + encodeURIComponent(userId), {
          headers: { 'Authorization': 'Bearer ' + API.getToken() }
        });
        var invData = await invRes.json();
        var inv = invData.data && invData.data.invitation;
        if (inv) {
          document.getElementById('invitationCard').style.display = 'block';

          if (inv.template_id) document.getElementById('inv_template_id').value = inv.template_id;
          if (inv.song_path) {
            var opt = songSel.querySelector('option[value="' + inv.song_path.replace(/"/g, '\\"') + '"]');
            if (opt) opt.selected = true;
          }
          if (inv.invitation_type) document.getElementById('inv_type').value = inv.invitation_type;
          if (inv.text_offset) document.getElementById('inv_text_offset').value = inv.text_offset;
          if (inv.text_delay != null) { var el = document.getElementById('inv_text_delay'); if (el) el.value = inv.text_delay; }
          if (inv.text_color) {
            document.getElementById('inv_text_color').value = inv.text_color;
            document.getElementById('inv_text_color_hex').value = inv.text_color;
          }
          document.getElementById('inv_video_loop').checked = inv.video_loop == 1;
          document.getElementById('inv_is_published').checked = inv.is_published == 1;
          if (inv.prefix_text) document.getElementById('inv_prefix_text').value = inv.prefix_text;
          document.getElementById('inv_prefix_hidden').checked = inv.prefix_hidden == 1;
          if (inv.font_size_rem != null) document.getElementById('inv_font_size_rem').value = inv.font_size_rem;
          if (inv.prefix_font_size_rem != null) document.getElementById('inv_prefix_font_size_rem').value = inv.prefix_font_size_rem;
          if (inv.date_font_size_rem != null) document.getElementById('inv_date_font_size_rem').value = inv.date_font_size_rem;
          if (inv.date_offset) document.getElementById('inv_date_offset').value = inv.date_offset;
          if (inv.hero_text_color) {
            document.getElementById('inv_hero_text_color').value = inv.hero_text_color;
            document.getElementById('inv_hero_text_color_hex').value = inv.hero_text_color;
          }
        }
      } catch (err) {
        console.error(err);
      }
    }

    document.getElementById('invitationForm').addEventListener('submit', async function (e) {
      e.preventDefault();
      var data = { user_id: parseInt(userId) };

      var tmpl = document.getElementById('inv_template_id').value;
      data.template_id = tmpl ? parseInt(tmpl) : null;
      data.song_path = document.getElementById('inv_song_path').value || '';
      data.is_published = document.getElementById('inv_is_published').checked;
      data.text_offset = document.getElementById('inv_text_offset').value;
      var invTextDelayEl = document.getElementById('inv_text_delay');
      if (invTextDelayEl) data.text_delay = invTextDelayEl.value;
      data.video_loop = document.getElementById('inv_video_loop').checked;
      data.text_color = document.getElementById('inv_text_color_hex').value || '#2C2420';
      data.invitation_type = document.getElementById('inv_type').value;
      data.prefix_text = document.getElementById('inv_prefix_text').value;
      data.prefix_hidden = document.getElementById('inv_prefix_hidden').checked;
      data.font_size_rem = document.getElementById('inv_font_size_rem').value || null;
      data.prefix_font_size_rem = document.getElementById('inv_prefix_font_size_rem').value || null;
      data.date_font_size_rem = document.getElementById('inv_date_font_size_rem').value || null;
      data.date_offset = document.getElementById('inv_date_offset').value || '';
      data.hero_text_color = document.getElementById('inv_hero_text_color_hex').value || null;

      try {
        await API.put(BASE_URL + 'api/admin/invitation.php', data);
        showAlert('Invitation mise à jour.', 'success');
      } catch (err) {
        showAlert(err.message);
      }
    });

    // Color sync for invitation form
    document.getElementById('inv_text_color').addEventListener('input', function () {
      document.getElementById('inv_text_color_hex').value = this.value;
    });
    document.getElementById('inv_text_color_hex').addEventListener('input', function () {
      if (/^#[0-9a-f]{6}$/i.test(this.value)) {
        document.getElementById('inv_text_color').value = this.value;
      }
    });

    document.getElementById('inv_hero_text_color').addEventListener('input', function () {
      document.getElementById('inv_hero_text_color_hex').value = this.value;
    });
    document.getElementById('inv_hero_text_color_hex').addEventListener('input', function () {
      if (/^#[0-9a-f]{6}$/i.test(this.value)) {
        document.getElementById('inv_hero_text_color').value = this.value;
      }
    });

    function toggleSidebar() {
      document.body.classList.toggle('sidebar-open');
    }
  </script>
</body>
</html>
