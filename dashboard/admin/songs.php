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
  <title>Admin — Gestion des Chansons</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
  <style>
    .songs-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
    .upload-area { border: 2px dashed var(--gray-300); border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; background: var(--gray-50); margin-bottom: 24px; }
    .upload-area:hover, .upload-area.dragover { border-color: var(--gold); background: var(--gold-light); }
    .upload-area .icon { font-size: 48px; color: var(--gold); margin-bottom: 12px; }
    .upload-area p { color: var(--gray-500); font-size: 15px; }
    .upload-area input { display: none; }
    .song-list { display: grid; gap: 12px; }
    .song-card { display: flex; align-items: center; gap: 16px; padding: 16px 20px; background: var(--white); border-radius: 10px; border: 1px solid var(--gray-200); transition: all 0.2s; }
    .song-card:hover { border-color: var(--gold); box-shadow: 0 2px 12px rgba(201,169,110,0.1); }
    .song-card .play-btn { width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--gold); background: transparent; color: var(--gold); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
    .song-card .play-btn:hover { background: var(--gold); color: var(--white); }
    .song-card .play-btn.playing { background: var(--gold); color: var(--white); }
    .song-card .info { flex: 1; min-width: 0; }
    .song-card .info .name { font-weight: 600; color: var(--gray-700); font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .song-card .info .size { font-size: 13px; color: var(--gray-400); }
    .song-card .actions { display: flex; gap: 4px; flex-shrink: 0; }
    .song-card .action-btn { width: 36px; height: 36px; border-radius: 50%; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
    .song-card .rename-btn { color: var(--gray-400); }
    .song-card .rename-btn:hover { background: #E0F2FE; color: #0284C7; }
    .song-card .delete-btn { color: var(--gray-400); }
    .song-card .delete-btn:hover { background: #FEE2E2; color: #EF4444; }
    .rename-input { border: 1px solid var(--gold); border-radius: 6px; padding: 4px 8px; font-size: 14px; width: 200px; outline: none; }
    .empty-state { text-align: center; padding: 60px 20px; color: var(--gray-400); }
    .empty-state .icon { font-size: 64px; margin-bottom: 16px; }
    .toast { position: fixed; bottom: 24px; right: 24px; padding: 14px 24px; border-radius: 8px; color: var(--white); font-size: 14px; z-index: 9999; transform: translateY(100px); opacity: 0; transition: all 0.3s; }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast.success { background: #059669; }
    .toast.error { background: #DC2626; }
    .progress-wrap { margin-top: 16px; display: none; }
    .progress-wrap.show { display: block; }
    .progress-bar { width: 100%; height: 6px; background: var(--gray-200); border-radius: 3px; overflow: hidden; }
    .progress-bar .fill { height: 100%; width: 0%; background: linear-gradient(90deg, var(--gold), var(--gold-light)); border-radius: 3px; transition: width 0.2s ease; }
    .progress-text { font-size: 13px; color: var(--gray-500); margin-top: 6px; }
  </style>
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
      <a href="<?= BASE_URL ?>dashboard/admin/index.php"><span class="icon"><i class="fas fa-home"></i></span><span>Tableau de Bord</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/users.php"><span class="icon"><i class="fas fa-users"></i></span><span>Utilisateurs</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/templates.php"><span class="icon"><i class="fas fa-palette"></i></span><span>Templates</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/songs.php" class="active"><span class="icon"><i class="fas fa-music"></i></span><span>Chansons</span></a>
    </nav>
    <div class="sidebar-footer">
      <button class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
    </div>
  </aside>

  <main class="main">
    <div class="page-header">
      <h1>Gestion des Chansons</h1>
      <p>Importez et gérez les chansons globales</p>
    </div>

    <div class="upload-area" id="uploadArea">
      <div class="icon"><i class="fas fa-cloud-upload-alt"></i></div>
      <p><strong>Cliquez</strong> ou <strong>glissez-déposez</strong> un fichier MP3 ici</p>
      <p style="font-size:13px;margin-top:4px;">Taille max : 50 Mo</p>
      <div class="progress-wrap" id="progressWrap">
        <div class="progress-bar"><div class="fill" id="progressFill"></div></div>
        <div class="progress-text" id="progressText">Téléversement... 0%</div>
      </div>
      <input type="file" id="fileInput" accept=".mp3,audio/mpeg" />
    </div>

    <div class="song-list" id="songList"></div>
    <div class="empty-state" id="emptyState">
      <div class="icon"><i class="fas fa-music"></i></div>
      <p>Aucune chanson importée</p>
    </div>
  </main>

  <div class="toast" id="toast"></div>

  <script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
  <script>
    var audioPlayer = null;
    var currentPlayBtn = null;
    var uploadArea = null;

    document.addEventListener('DOMContentLoaded', function () {
      loadSongs();
      setupUpload();
    });

    function loadSongs() {
      API.get(BASE_URL + 'api/admin/songs.php').then(function (data) {
        renderSongs(data.data.songs || []);
      }).catch(function () {
        showToast('Erreur lors du chargement des chansons.', 'error');
      });
    }

    function escapeAttr(s) {
      return s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
    function escapeHtml(s) {
      return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function renderSongs(songs) {
      var list = document.getElementById('songList');
      var empty = document.getElementById('emptyState');
      list.innerHTML = '';
      if (songs.length === 0) {
        empty.style.display = 'block';
        return;
      }
      empty.style.display = 'none';
      songs.forEach(function (s, i) {
        var div = document.createElement('div');
        div.className = 'song-card';
        div.dataset.index = i;
        var safe = escapeAttr(s);
        var display = escapeHtml(s);
        div.innerHTML =
          '<button class="play-btn" data-song="' + safe + '"><i class="fas fa-play"></i></button>' +
          '<div class="info"><div class="name" data-name-idx="' + i + '">' + display + '</div><div class="size">MP3</div></div>' +
          '<div class="actions">' +
            '<button class="action-btn rename-btn" data-song="' + safe + '" title="Renommer"><i class="fas fa-pen"></i></button>' +
            '<button class="action-btn delete-btn" data-song="' + safe + '" title="Supprimer"><i class="fas fa-trash"></i></button>' +
          '</div>';
        list.appendChild(div);
      });
    }

    document.getElementById('songList').addEventListener('click', function (e) {
      var playBtn = e.target.closest('.play-btn');
      var deleteBtn = e.target.closest('.delete-btn');
      var renameBtn = e.target.closest('.rename-btn');
      if (playBtn) { e.preventDefault(); togglePlay(playBtn); }
      if (deleteBtn) { e.preventDefault(); deleteSong(deleteBtn.dataset.song); }
      if (renameBtn) { e.preventDefault(); startRename(renameBtn); }
    });

    function togglePlay(btn) {
      var song = btn.dataset.song;
      if (audioPlayer && audioPlayer.src.endsWith('/' + encodeURIComponent(song))) {
        if (audioPlayer.paused) {
          audioPlayer.play();
          btn.classList.add('playing');
          btn.innerHTML = '<i class="fas fa-pause"></i>';
        } else {
          audioPlayer.pause();
          btn.classList.remove('playing');
          btn.innerHTML = '<i class="fas fa-play"></i>';
        }
        return;
      }
      if (audioPlayer) { audioPlayer.pause(); if (currentPlayBtn) { currentPlayBtn.classList.remove('playing'); currentPlayBtn.innerHTML = '<i class="fas fa-play"></i>'; } }
      audioPlayer = new Audio(BASE_URL + 'songs/' + encodeURIComponent(song));
      audioPlayer.addEventListener('ended', function () {
        btn.classList.remove('playing');
        btn.innerHTML = '<i class="fas fa-play"></i>';
      });
      audioPlayer.play().catch(function () {});
      btn.classList.add('playing');
      btn.innerHTML = '<i class="fas fa-pause"></i>';
      currentPlayBtn = btn;
    }

    function startRename(btn) {
      var oldName = btn.dataset.song;
      var card = btn.closest('.song-card');
      var nameEl = card ? card.querySelector('.name') : null;
      if (!nameEl) return;
      var current = nameEl.textContent;
      var input = document.createElement('input');
      input.type = 'text';
      input.className = 'rename-input';
      input.value = current;
      input.dataset.old = oldName;
      nameEl.innerHTML = '';
      nameEl.appendChild(input);
      input.focus();
      input.select();

      function finish(confirm) {
        if (confirm && input.value.trim() && input.value.trim() !== current) {
          var newName = input.value.trim();
          if (!newName.endsWith('.mp3')) newName += '.mp3';
          doRename(oldName, newName, function () {
            nameEl.textContent = newName;
            btn.dataset.song = newName;
          });
        } else {
          nameEl.textContent = current;
        }
      }

      input.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') { ev.preventDefault(); finish(true); }
        if (ev.key === 'Escape') { ev.preventDefault(); finish(false); }
      });
      input.addEventListener('blur', function () { finish(true); });
    }

    function doRename(oldName, newName, cb) {
      API.put(BASE_URL + 'api/admin/songs.php', { filename: oldName, new_name: newName }).then(function () {
        showToast('Chanson renomm\u00e9e.', 'success');
        if (cb) cb();
      }).catch(function (err) {
        showToast(err.message || 'Erreur lors du renommage.', 'error');
        loadSongs();
      });
    }

    function deleteSong(filename) {
      if (!confirm('Supprimer ' + filename + ' ?')) return;
      API.del(BASE_URL + 'api/admin/songs.php', { filename: filename }).then(function () {
        showToast('Chanson supprim\u00e9e.', 'success');
        loadSongs();
      }).catch(function (err) {
        showToast(err.message || 'Erreur lors de la suppression.', 'error');
      });
    }

    function setupUpload() {
      uploadArea = document.getElementById('uploadArea');
      var input = document.getElementById('fileInput');

      uploadArea.addEventListener('click', function () { input.click(); });

      uploadArea.addEventListener('dragover', function (e) { e.preventDefault(); uploadArea.classList.add('dragover'); });
      uploadArea.addEventListener('dragleave', function () { uploadArea.classList.remove('dragover'); });
      uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
      });

      input.addEventListener('change', function () {
        if (input.files.length) uploadFile(input.files[0]);
      });
    }

    function uploadFile(file) {
      if (file.type !== 'audio/mpeg' && !file.name.endsWith('.mp3')) {
        showToast('Seuls les fichiers MP3 sont accept\u00e9s.', 'error');
        return;
      }
      if (file.size > 50 * 1024 * 1024) {
        showToast('Fichier trop volumineux (max 50 Mo).', 'error');
        return;
      }

      var formData = new FormData();
      formData.append('file', file);

      var token = localStorage.getItem('invito_token');
      var pw = document.getElementById('progressWrap');
      var pf = document.getElementById('progressFill');
      var pt = document.getElementById('progressText');
      pw.classList.add('show');
      var xhr = new XMLHttpRequest();
      xhr.open('POST', BASE_URL + 'api/admin/songs.php');
      xhr.setRequestHeader('Authorization', 'Bearer ' + token);
      xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
          var pct = Math.round((e.loaded / e.total) * 100);
          pf.style.width = pct + '%';
          pt.textContent = 'T\u00e9l\u00e9versement... ' + pct + '%';
        }
      };
      xhr.onload = function () {
        pw.classList.remove('show');
        pf.style.width = '0%';
        if (xhr.status >= 200 && xhr.status < 300) {
          showToast('Chanson import\u00e9e avec succ\u00e8s !', 'success');
          loadSongs();
        } else {
          try { var d = JSON.parse(xhr.responseText); showToast(d.error || 'Erreur.', 'error'); } catch(e) { showToast('Erreur.', 'error'); }
        }
      };
      xhr.onerror = function () { showToast('Erreur r\u00e9seau.', 'error'); };
      xhr.send(formData);
    }

    function showToast(msg, type) {
      var t = document.getElementById('toast');
      t.textContent = msg;
      t.className = 'toast ' + type + ' show';
      setTimeout(function () { t.classList.remove('show'); }, 3000);
    }
  </script>
</body>
</html>
