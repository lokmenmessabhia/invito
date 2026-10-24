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
  <title>Admin — Modifier Template</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    /* ============================================================
       NEW CONTENT DESIGN SYSTEM — scoped to this page's content.
       (.page-header and .sidebar are intentionally left untouched.)
       ============================================================ */
    :root {
      --nt-bg: #F6F3EE;
      --nt-surface: #FFFFFF;
      --nt-surface-2: #FBF9F5;
      --nt-border: #E8E1D6;
      --nt-ink: #211D18;
      --nt-muted: #8B8478;
      --nt-accent: #C1502E;      /* terracotta */
      --nt-accent-ink: #7A3018;
      --nt-accent-soft: #FBE7DD;
      --nt-teal: #1F6F63;
      --nt-teal-soft: #E1F0EC;
      --nt-rose: #B23A48;
      --nt-rose-soft: #FBE4E6;
      --nt-radius-lg: 22px;
      --nt-radius-md: 14px;
      --nt-radius-sm: 10px;
      --nt-shadow: 0 1px 2px rgba(33,29,24,0.04), 0 18px 40px -22px rgba(33,29,24,0.28);
    }

    body { background: var(--nt-bg); font-family: 'Inter', -apple-system, sans-serif; }

    .main h1, .main h2, .main h3 { font-family: 'Fraunces', Georgia, serif; }

    /* Cards */
    .card {
      background: var(--nt-surface);
      border: 1px solid var(--nt-border);
      border-radius: var(--nt-radius-lg);
      box-shadow: var(--nt-shadow);
      overflow: hidden;
      margin-bottom: 20px;
    }
    .card-header {
      padding: 20px 26px;
      border-bottom: 1px solid var(--nt-border);
      background: linear-gradient(180deg, var(--nt-surface-2), var(--nt-surface));
    }
    .card-header h2 {
      font-size: 17px; font-weight: 600; color: var(--nt-ink);
      letter-spacing: -0.2px; margin: 0;
    }
    .card-body { padding: 26px; }

    /* Template preview header block */
    .template-preview {
      display: flex; align-items: center; gap: 18px; margin-bottom: 26px;
      padding-bottom: 22px; border-bottom: 1px dashed var(--nt-border);
    }
    .template-avatar {
      width: 60px; height: 60px; border-radius: 18px;
      background: linear-gradient(135deg, var(--nt-accent), var(--nt-accent-ink));
      color: #fff; display: flex; align-items: center; justify-content: center;
      font-size: 24px; box-shadow: 0 8px 18px -8px rgba(193,80,46,0.55);
    }
    .template-meta h2 { font-size: 21px; color: var(--nt-ink); margin-bottom: 3px; }
    .template-meta p { font-size: 13px; color: var(--nt-muted); font-family: 'Inter', sans-serif; }

    /* Info grid */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .info-item {
      padding: 14px 16px; background: var(--nt-surface-2);
      border-radius: var(--nt-radius-sm); border: 1px solid var(--nt-border);
      transition: border-color .15s ease;
    }
    .info-item:hover { border-color: #D8CDBC; }
    .info-item .lbl {
      font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.6px;
      color: var(--nt-muted); font-weight: 600; margin-bottom: 4px;
    }
    .info-item .val { font-size: 15px; color: var(--nt-ink); font-weight: 600; }

    /* Badges (status pills used in dispStatus / songs-list) */
    .badge {
      display: inline-block; padding: 4px 11px; border-radius: 999px;
      font-size: 12px; font-weight: 600;
    }
    .badge-pub { background: var(--nt-teal-soft); color: var(--nt-teal); }
    .badge-draft { background: var(--nt-rose-soft); color: var(--nt-rose); }

    .songs-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
    .songs-list code {
      background: var(--nt-accent-soft); padding: 3px 10px; border-radius: 999px;
      font-size: 11.5px; color: var(--nt-accent-ink); font-family: 'Inter', monospace;
    }

    /* File chips */
    .file-badge {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 8px 14px; border-radius: 999px;
      font-size: 12.5px; font-weight: 600; cursor: pointer;
      border: 1px solid transparent;
      transition: transform .12s ease, filter .12s ease;
    }
    .file-badge:hover { transform: translateY(-1px); filter: brightness(0.96); }
    .file-badge.file-ok { background: var(--nt-teal-soft); color: var(--nt-teal); border-color: #CFE6DE; }
    .file-badge.file-missing { background: var(--nt-rose-soft); color: var(--nt-rose); border-color: #F3D2D6; }
    .file-badge.uploading { pointer-events: none; opacity: 0.5; filter: grayscale(1); }

    /* Upload progress */
    .upload-progress-wrap { display: none; margin-top: 12px; padding: 12px 14px; background: var(--nt-surface-2); border-radius: var(--nt-radius-sm); }
    .upload-progress-wrap.show { display: block; }
    .upload-progress-bar { width: 100%; height: 6px; background: var(--nt-border); border-radius: 3px; overflow: hidden; }
    .upload-progress-fill { height: 100%; width: 0%; background: linear-gradient(90deg, var(--nt-accent), var(--nt-accent-ink)); border-radius: 3px; transition: width 0.2s ease; }
    .upload-progress-text { font-size: 13px; color: var(--nt-muted); margin-top: 6px; }

    /* Form elements */
    .form-row { display: flex; gap: 18px; flex-wrap: wrap; }
    .form-group { margin-bottom: 18px; flex: 1; min-width: 220px; }
    .form-group label {
      display: block; font-size: 12.5px; font-weight: 600; color: var(--nt-ink);
      margin-bottom: 6px; letter-spacing: 0.1px;
    }
    .form-control {
      width: 100%; padding: 11px 14px; border: 1px solid var(--nt-border);
      border-radius: var(--nt-radius-sm); font-size: 14px; color: var(--nt-ink);
      background: var(--nt-surface-2); transition: border-color .15s ease, background .15s ease;
      font-family: 'Inter', sans-serif;
    }
    .form-control:focus {
      outline: none; border-color: var(--nt-accent); background: #fff;
      box-shadow: 0 0 0 3px var(--nt-accent-soft);
    }

    /* Buttons */
    .btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 11px 18px; border-radius: 999px; font-size: 13.5px; font-weight: 600;
      cursor: pointer; border: 1px solid transparent; text-decoration: none;
      transition: transform .12s ease, filter .12s ease, box-shadow .12s ease;
    }
    .btn:hover { transform: translateY(-1px); }
    .btn-sm { padding: 7px 12px; font-size: 12px; }
    .btn-gold {
      background: linear-gradient(135deg, var(--nt-accent), var(--nt-accent-ink));
      color: #fff; box-shadow: 0 10px 22px -10px rgba(193,80,46,0.6);
    }
    .btn-gold:hover { filter: brightness(1.05); }
    .btn-outline {
      background: var(--nt-surface); color: var(--nt-ink); border-color: var(--nt-border);
    }
    .btn-outline:hover { border-color: #C9BFAE; background: var(--nt-surface-2); }

    /* Video preview with draggable text overlay */
    .preview-container {
      position: relative; width: 100%; max-width: 480px; margin: 16px 0;
      border-radius: var(--nt-radius-md); overflow: hidden; background: #14110E;
      cursor: grab; user-select: none; aspect-ratio: 9/16;
      border: 1px solid var(--nt-border);
      box-shadow: var(--nt-shadow);
    }
    .preview-container video {
      width: 100%; height: 100%; display: block; object-fit: cover; opacity: 0.92;
    }
    .preview-container .drag-text {
      position: absolute; left: 50%; transform: translateX(-50%);
      color: #fff; font-family: 'Fraunces', serif;
      font-size: 1.6rem; font-weight: 700; text-align: center;
      text-shadow: 0 2px 14px rgba(0,0,0,0.85);
      pointer-events: auto; transition: none;
      padding: 4px 12px; border-radius: 4px;
      display: flex; flex-direction: column; align-items: center; gap: 2px;
    }
    .preview-container .drag-prefix { font-weight: 400; opacity: 0.9; font-family: 'Inter', sans-serif; }
    .preview-container .drag-names { font-size: 1em; font-weight: 700; }
    .preview-container .drag-line {
      position: absolute; left: 0; right: 0; height: 1px;
      background: repeating-linear-gradient(90deg, var(--nt-accent) 0 6px, transparent 6px 12px);
      pointer-events: none; display: flex; align-items: center; justify-content: space-between;
      padding: 0 8px; font-size: 10px; color: var(--nt-accent);
    }
    .preview-container .drag-handle {
      position: absolute; right: 8px; width: 30px; height: 30px;
      border-radius: 50%; background: var(--nt-accent); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; cursor: grab; pointer-events: auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.35); transform: translateY(-50%);
      z-index: 5;
    }
    .preview-container .drag-handle:active { cursor: grabbing; }
    .preview-container .drag-date {
      position: absolute; left: 50%; transform: translateX(-50%);
      color: #fff; font-family: 'Inter', serif; font-style: italic;
      font-size: 0.9rem; text-align: center;
      text-shadow: 0 2px 12px rgba(0,0,0,0.8);
      pointer-events: auto; cursor: grab; user-select: none;
      padding: 2px 12px; border-radius: 4px; white-space: nowrap;
      opacity: 0.75; z-index: 4;
    }
    .preview-container .drag-date:active { cursor: grabbing; }
    .drag-date-handle { z-index: 4 !important; background: var(--nt-teal) !important; }

    .pos-value {
      display: inline-flex; align-items: center; gap: 8px;
      font-size: 13.5px; color: var(--nt-ink); font-weight: 600;
      margin-top: 10px;
    }
    .pos-value input {
      width: 66px; padding: 7px 8px; border: 1px solid var(--nt-border);
      border-radius: var(--nt-radius-sm); text-align: center;
      font-family: 'Inter', monospace; font-size: 13px; background: var(--nt-surface-2);
    }
    .pos-value input:focus { outline: none; border-color: var(--nt-accent); }

    .preset-positions { display: flex; gap: 6px; flex-wrap: wrap; margin: 10px 0; }
    .preset-positions button {
      padding: 6px 13px; border: 1px solid var(--nt-border);
      border-radius: 999px; background: var(--nt-surface); cursor: pointer;
      font-size: 12px; color: var(--nt-muted); transition: all 0.15s;
    }
    .preset-positions button:hover { border-color: var(--nt-accent); color: var(--nt-accent-ink); }
    .preset-positions button.active {
      background: var(--nt-accent); color: #fff; border-color: var(--nt-accent);
    }

    /* checkboxes as compact toggles */
    .form-group input[type="checkbox"] {
      width: 16px; height: 16px; accent-color: var(--nt-accent); margin-right: 6px;
      vertical-align: middle;
    }

    #alert.alert { border-radius: var(--nt-radius-sm); }
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
      <a href="<?= BASE_URL ?>dashboard/admin/users.php"><span class="icon"><i class="fas fa-users"></i></span><span>Utilisateurs</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/templates.php" class="active"><span class="icon"><i class="fas fa-palette"></i></span><span>Templates</span></a>
      <a href="<?= BASE_URL ?>dashboard/admin/songs.php"><span class="icon"><i class="fas fa-music"></i></span><span>Chansons</span></a>
    </nav>
    <div class="sidebar-footer"><button class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button></div>
  </aside>

  <main class="main">
    <div class="page-header" style="display:flex;align-items:center;gap:12px;">
      <a href="<?= BASE_URL ?>dashboard/admin/templates.php" class="btn btn-sm btn-outline"><i class="fas fa-arrow-left"></i> Retour</a>
      <div>
        <h1 id="pageTitle">Modifier Template</h1>
        <p id="pageSub" style="color:var(--gray-500);font-size:14px;">Consultez et modifiez les informations du template</p>
      </div>
    </div>

    <div id="alert" class="alert"></div>

    <div class="card" id="tmplInfoCard">
      <div class="card-header"><h2>Informations du template</h2></div>
      <div class="card-body">
        <div class="template-preview">
          <div class="template-avatar"><i class="fas fa-palette"></i></div>
          <div class="template-meta">
            <h2 id="dispName">-</h2>
            <p id="dispFolder">-</p>
          </div>
        </div>
        <div class="info-grid">
          <div class="info-item">
            <div class="lbl">ID</div>
            <div class="val" id="dispId">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Statut</div>
            <div class="val" id="dispStatus">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Boucle vidéo</div>
            <div class="val" id="dispVideoLoop">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Position du texte</div>
            <div class="val" id="dispOffset">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Délai texte (s)</div>
            <div class="val" id="dispDelay">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Fichiers vidéo</div>
            <div class="val" id="dispFiles">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Chansons</div>
            <div class="val" id="dispSongs">-</div>
          </div>
          <div class="info-item">
            <div class="lbl">Créé le</div>
            <div class="val" id="dispCreated">-</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h2 id="formTitle">Modifier</h2></div>
      <div class="card-body">
        <form id="tmplForm" autocomplete="off">
          <input type="hidden" id="editId" value="" />
          <div class="form-row">
            <div class="form-group">
              <label for="editName">Nom</label>
              <input type="text" class="form-control" id="editName" required />
            </div>
            <div class="form-group">
              <label for="editFolder">Dossier</label>
              <input type="text" class="form-control" id="editFolder" placeholder="ex: template1" required />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="editThumb">Miniature (chemin)</label>
              <input type="text" class="form-control" id="editThumb" placeholder="templates/template1/thumbnail.jpg" />
            </div>
            <div class="form-group">
                <label for="previewText">Position du texte sur la vidéo</label>
                <div class="preview-container" id="previewContainer">
                  <video id="previewVideo" muted playsinline></video>
                  <div class="drag-line" id="dragGuide"></div>
                  <div class="drag-line" id="dragDateGuide" style="background:repeating-linear-gradient(90deg, var(--nt-teal) 0 6px, transparent 6px 12px);"></div>
                  <div class="drag-text" id="dragText">
                    <div class="drag-prefix" id="dragPrefix">Mariage de</div>
                    <div class="drag-names" id="dragNames">Prénom &amp; Prénom</div>
                  </div>
                  <div class="drag-handle" id="dragHandle"><i class="fas fa-grip-vertical"></i></div>
                  <div class="drag-date" id="dragDate">Date de l'événement</div>
                  <div class="drag-handle drag-date-handle" id="dragDateHandle"><i class="fas fa-grip-vertical"></i></div>
                </div>
                <div class="pos-value">
                  <span>Position verticale :</span>
                  <input type="number" id="editOffsetPct" min="0" max="100" value="50" />
                  <span>%</span>
                </div>
                <div class="preset-positions">
                  <button data-pct="10">Haut</button>
                  <button data-pct="30">1/3</button>
                  <button data-pct="50">Milieu</button>
                  <button data-pct="72">2/3</button>
                  <button data-pct="88">Bas</button>
                </div>
                <input type="hidden" id="editOffset" name="text_offset" value="" />
                <input type="hidden" id="editDateOffset" name="date_offset" value="" />
                <div style="margin-top:14px;padding-top:14px;border-top:1px dashed var(--nt-border);">
                  <label style="font-size:12.5px;color:var(--nt-muted);display:block;margin-bottom:8px;font-weight:600;">Position de la date</label>
                  <div class="pos-value" style="margin-top:4px;">
                    <span>Verticale :</span>
                    <input type="number" id="editDateOffsetPct" min="0" max="100" value="50" />
                    <span>%</span>
                  </div>
                  <div class="preset-positions" style="margin-top:4px;">
                    <button data-date-pct="10">Haut</button>
                    <button data-date-pct="30">1/3</button>
                    <button data-date-pct="50">Milieu</button>
                    <button data-date-pct="72">2/3</button>
                    <button data-date-pct="88">Bas</button>
                    <button data-date-pct="">Auto</button>
                  </div>
                </div>
                <div style="display:flex;gap:16px;margin-top:14px;flex-wrap:wrap;">
                  <div style="flex:1;min-width:150px;">
                    <label style="font-size:12.5px;color:var(--nt-muted);display:block;margin-bottom:4px;font-weight:600;">
                      <input type="checkbox" id="showPrefix" checked style="margin-right:4px;" />
                      Ligne 1 (ex: Mariage de)
                    </label>
                    <input type="text" id="previewPrefix" class="form-control" value="Mariage de" style="font-size:14px;" />
                  </div>
                  <div style="flex:1;min-width:150px;">
                    <label style="font-size:12.5px;color:var(--nt-muted);display:block;margin-bottom:4px;font-weight:600;">Ligne 2 (noms)</label>
                    <input type="text" id="previewNames" class="form-control" value="Prénom &amp; Prénom" style="font-size:14px;" />
                  </div>
                  <div style="flex:0;min-width:120px;">
                    <label style="font-size:12.5px;color:var(--nt-muted);display:block;margin-bottom:4px;font-weight:600;">Taille noms</label>
                    <div style="display:flex;align-items:center;gap:8px;">
                      <input type="range" id="previewNamesSize" min="0.8" max="3.0" step="0.1" value="1.6" style="flex:1;min-width:80px;accent-color:var(--nt-accent);" />
                      <span id="namesSizeLabel" style="font-size:13px;color:var(--nt-ink);font-weight:600;min-width:36px;">1.6</span>
                    </div>
                  </div>
                  <div style="flex:0;min-width:120px;">
                    <label style="font-size:12.5px;color:var(--nt-muted);display:block;margin-bottom:4px;font-weight:600;">Taille en-tête</label>
                    <div style="display:flex;align-items:center;gap:8px;">
                      <input type="range" id="previewPrefixSize" min="0.5" max="2.5" step="0.1" value="1.0" style="flex:1;min-width:80px;accent-color:var(--nt-accent);" />
                      <span id="prefixSizeLabel" style="font-size:13px;color:var(--nt-ink);font-weight:600;min-width:36px;">1.0</span>
                    </div>
                  </div>
                  <div style="flex:0;min-width:120px;">
                    <label style="font-size:12.5px;color:var(--nt-muted);display:block;margin-bottom:4px;font-weight:600;">Taille date</label>
                    <div style="display:flex;align-items:center;gap:8px;">
                      <input type="range" id="previewDateSize" min="0.5" max="2.0" step="0.1" value="0.9" style="flex:1;min-width:80px;accent-color:var(--nt-teal);" />
                      <span id="dateSizeLabel" style="font-size:13px;color:var(--nt-ink);font-weight:600;min-width:36px;">0.9</span>
                    </div>
                  </div>
                  <div style="flex:0;min-width:120px;">
                    <label style="font-size:12.5px;color:var(--nt-muted);display:block;margin-bottom:4px;font-weight:600;">Couleur du texte</label>
                    <div style="display:flex;align-items:center;gap:8px;">
                      <input type="color" id="previewTextColor" value="#FFFFFF" style="width:42px;height:36px;padding:2px;cursor:pointer;border:1px solid var(--nt-border);border-radius:8px;" />
                      <button type="button" id="resetTextColor" class="btn btn-sm btn-outline" style="font-size:11px;padding:6px 10px;" title="Réinitialiser">RàZ</button>
                    </div>
                  </div>
                </div>
                <p style="font-size:12px;color:var(--nt-muted);margin-top:8px;">Faites glisser le texte ou utilisez les presets pour positionner précisément.</p>
              </div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="editDelay">Délai d'apparition du texte (secondes)</label>
              <input type="number" class="form-control" id="editDelay" step="0.01" min="0" value="1.25" />
              <p style="font-size:12px;color:var(--nt-muted);margin-top:6px;">Temps avant que le texte n'apparaisse après le clic (ex: 1.25 = 1.25s)</p>
            </div>
          </div>
          <div class="form-group">
            <label style="font-weight:500;">
              <input type="checkbox" id="editActive" checked />
              Template actif
            </label>
          </div>
          <div class="form-group">
            <label style="font-weight:500;">
              <input type="checkbox" id="editVideoLoop" />
              Boucle vidéo
            </label>
            <p style="font-size:12px;color:var(--nt-muted);margin-top:4px;">Répéter la vidéo en boucle</p>
          </div>
          <div class="form-group">
            <label style="font-weight:500;">
              <input type="checkbox" id="editTwoVideos" />
              Activer deux vidéos
            </label>
            <p style="font-size:12px;color:var(--nt-muted);margin-top:4px;">Affiche deux vidéos en séquence (1ère → 2ème). Les fichiers doivent être nommés 1.mp4/2.mp4 ou 1.webm/2.webm.</p>
          </div>
          <div style="display:flex;gap:12px;margin-top:12px;">
            <button type="submit" class="btn btn-gold" id="saveBtn"><i class="fas fa-save"></i> Enregistrer</button>
            <a href="<?= BASE_URL ?>dashboard/admin/templates.php" class="btn btn-outline">Annuler</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card" id="uploadCard">
      <div class="card-header"><h2>Fichiers du template</h2></div>
      <div class="card-body">
        <p style="color:var(--nt-muted);font-size:14px;margin-bottom:16px;">Cliquez sur le statut d'un fichier pour uploader.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;" id="fileActions"></div>
        <div class="upload-progress-wrap" id="uploadProgressWrap">
          <div class="upload-progress-bar"><div class="upload-progress-fill" id="uploadProgressFill"></div></div>
          <div class="upload-progress-text" id="uploadProgressText">Téléversement... 0%</div>
        </div>
      </div>
    </div>
  </main>

  <!-- Hidden file input reused for uploads -->
  <input type="file" id="uploadInput" style="display:none" accept=".webm,.mp3,.mp4" />

  <script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
  <script>
    var tmplId = new URLSearchParams(window.location.search).get('id');
    var currentData = null;

    document.addEventListener('DOMContentLoaded', async function () {
      var user = await checkAuth();
      if (!user) return;
      document.querySelector('#userInfo strong').textContent = user.username;

      if (tmplId) {
        await loadTemplate();
      } else {
        document.getElementById('pageTitle').textContent = 'Nouveau Template';
        document.getElementById('pageSub').textContent = 'Créez un nouveau template';
        document.getElementById('tmplInfoCard').style.display = 'none';
        document.getElementById('uploadCard').style.display = 'none';
        document.getElementById('formTitle').textContent = 'Créer';
        document.getElementById('saveBtn').innerHTML = '<i class="fas fa-plus"></i> Créer';
      }
      showLoading(false);
    });

    async function loadTemplate() {
      try {
        var res = await API.get(BASE_URL + 'api/admin/templates.php');
        var t = res.data.templates.find(function (x) { return x.id == tmplId; });
        if (!t) { showAlert('Template introuvable.'); return; }
        currentData = t;

        document.getElementById('pageTitle').textContent = t.name;
        document.getElementById('pageSub').textContent = 'Consultez et modifiez les informations de ' + t.name;
        document.getElementById('dispName').textContent = t.name;
        document.getElementById('dispFolder').textContent = t.folder_path;
        document.getElementById('dispId').textContent = t.id;

        var statusBadge = t.is_active == 1
          ? '<span class="badge badge-pub">Actif</span>'
          : '<span class="badge badge-draft">Inactif</span>';
        document.getElementById('dispStatus').innerHTML = statusBadge;

        var offsetLabels = { center: 'Centr\u00e9', top: 'Haut', bottom: 'Bas', 'bottom-third': '3e tiers' };
        var dispOffset = t.text_offset || 'center';
        if (dispOffset.indexOf('pct:') === 0) {
          dispOffset = dispOffset.substring(4) + '% du haut';
        } else {
          dispOffset = offsetLabels[dispOffset] || dispOffset || 'Centr\u00e9';
        }
        document.getElementById('dispOffset').textContent = dispOffset;
        document.getElementById('dispDelay').textContent = t.text_delay != null ? t.text_delay + 's' : '1.25s';

        var f = t.files || {};
        var videoHtml = '';
        videoHtml += '<span class="file-badge ' + (f.video_mp4 ? 'file-ok' : 'file-missing') + '">' + (f.video_mp4 ? '&#9989; MP4' : '&#10060; MP4') + '</span>';
        videoHtml += '<span class="file-badge ' + (f.video_webm ? 'file-ok' : 'file-missing') + '">' + (f.video_webm ? '&#9989; WEBM' : '&#10060; WEBM') + '</span>';
        videoHtml += '<br style="margin:4px 0">';
        videoHtml += '<span class="file-badge ' + (f.video_first ? 'file-ok' : 'file-missing') + '">' + (f.video_first ? '&#9989; 1ère vidéo' : '&#10060; 1ère vidéo') + '</span>';
        videoHtml += '<span class="file-badge ' + (f.video_second ? 'file-ok' : 'file-missing') + '">' + (f.video_second ? '&#9989; 2ème vidéo' : '&#10060; 2ème vidéo') + '</span>';
        document.getElementById('dispFiles').innerHTML = videoHtml;

        var songs = t.songs || [];
        if (songs.length) {
          document.getElementById('dispSongs').innerHTML = '<div class="songs-list">' + songs.map(function (s) { return '<code>' + h(s) + '</code>'; }).join('') + '</div>';
        } else {
          document.getElementById('dispSongs').textContent = 'Aucune';
        }

        document.getElementById('dispCreated').textContent = t.created_at ? new Date(t.created_at).toLocaleDateString('fr-FR') : '-';
        document.getElementById('dispVideoLoop').textContent = t.video_loop ? 'Oui' : 'Non';

        document.getElementById('editId').value = t.id;
        document.getElementById('editName').value = t.name;
        document.getElementById('editFolder').value = t.folder_path;
        document.getElementById('editThumb').value = t.thumbnail || '';
        // Load video preview
        loadPreviewVideo(t);
        // Parse saved position or default to 50%
        var savedPct = 50;
        if (t.text_offset && t.text_offset.indexOf('pct:') === 0) {
          savedPct = parseInt(t.text_offset.substring(4));
        } else if (t.text_offset === 'top') { savedPct = 10; }
        else if (t.text_offset === 'bottom') { savedPct = 88; }
        else if (t.text_offset === 'bottom-third') { savedPct = 72; }
        else if (t.text_offset === 'center' || !t.text_offset) { savedPct = 50; }
        setTextPosition(savedPct);
        document.getElementById('editDelay').value = t.text_delay != null ? t.text_delay : '1.25';
        document.getElementById('editActive').checked = t.is_active == 1;
        document.getElementById('editVideoLoop').checked = t.video_loop == 1;
        document.getElementById('editTwoVideos').checked = t.has_two_videos == 1;
        // Populate preview prefix fields from template
        document.getElementById('previewPrefix').value = t.prefix_text || 'Mariage de';
        document.getElementById('showPrefix').checked = !t.prefix_hidden;
        var fs = t.font_size_rem || 1.6;
        document.getElementById('previewNamesSize').value = fs;
        document.getElementById('namesSizeLabel').textContent = fs;
        document.getElementById('dragNames').style.fontSize = fs + 'rem';
        var pfs = t.prefix_font_size_rem || 1.0;
        document.getElementById('previewPrefixSize').value = pfs;
        document.getElementById('prefixSizeLabel').textContent = pfs;
        document.getElementById('dragPrefix').style.fontSize = pfs + 'rem';
        var dfs = t.date_font_size_rem || 0.9;
        document.getElementById('previewDateSize').value = dfs;
        document.getElementById('dateSizeLabel').textContent = dfs;
        document.getElementById('dragDate').style.fontSize = dfs + 'rem';
        // Load text color
        var hc = t.hero_text_color || '#FFFFFF';
        document.getElementById('previewTextColor').value = hc;
        updatePreviewColor(hc);
        updateDragText();
        // Load date position
        var savedDatePct = null;
        if (t.date_offset && t.date_offset.indexOf('pct:') === 0) {
          savedDatePct = parseInt(t.date_offset.substring(4));
        }
        setDatePosition(savedDatePct);

        renderUploadButtons(t.id, f, songs);
      } catch (err) {
        showAlert(err.message);
      }
    }

    function loadPreviewVideo(t) {
      var video = document.getElementById('previewVideo');
      var folderPath = BASE_URL + 'templates/' + t.folder_path + '/';
      var f = t.files || {};

      function trySource(url) {
        video.src = url + '?t=' + Date.now();
        video.load();
        video.play().catch(function(){});
      }

      if (f.video_webm) {
        trySource(folderPath + 'video.webm');
      } else if (f.video_mp4) {
        trySource(folderPath + 'video.mp4');
      } else if (f.video_first_webm) {
        trySource(folderPath + '1.webm');
      } else if (f.video_first) {
        trySource(folderPath + '1.mp4');
      } else if (f.video_second_webm) {
        trySource(folderPath + '2.webm');
      } else if (f.video_second) {
        trySource(folderPath + '2.mp4');
      }
    }

    function renderUploadButtons(tid, files, songs) {
      var container = document.getElementById('fileActions');
      var isTwoVideos = currentData && currentData.has_two_videos == 1;
      var html = '';
      html += '<div style="margin-bottom:16px;"><strong style="font-size:14px;color:var(--nt-ink);">Vid\u00e9o unique</strong>';
      html += '<div style="display:flex;gap:8px;margin-top:6px;flex-wrap:wrap;">';
      html += '<button class="file-badge ' + (files.video_mp4 ? 'file-ok' : 'file-missing') + '" data-tid="' + tid + '" data-ext="mp4">';
      html += files.video_mp4 ? '&#9989;' : '&#10060;';
      html += ' video.mp4</button>';
      html += '<button class="file-badge ' + (files.video_webm ? 'file-ok' : 'file-missing') + '" data-tid="' + tid + '" data-ext="webm">';
      html += files.video_webm ? '&#9989;' : '&#10060;';
      html += ' video.webm</button>';
      html += '</div></div>';

      if (isTwoVideos) {
        html += '<div style="margin-bottom:16px;padding:14px;background:var(--nt-surface-2);border-radius:14px;border:1px solid var(--nt-border);">';
        html += '<strong style="font-size:14px;color:var(--nt-ink);">Deux vid\u00e9os</strong>';
        html += '<p style="font-size:12px;color:var(--nt-muted);margin:4px 0 8px;">La 1\u00e8re est l\'arri\u00e8re-plan, la 2\u00e8me passe devant.</p>';

        html += '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px;">';
        html += '<button class="file-badge ' + (files.video_first ? 'file-ok' : 'file-missing') + '" data-tid="' + tid + '" data-ext="mp4" data-slot="first">';
        html += files.video_first ? '&#9989;' : '&#10060;';
        html += ' 1\u00e8re (MP4)</button>';
        html += '<button class="file-badge ' + (files.video_first_webm ? 'file-ok' : 'file-missing') + '" data-tid="' + tid + '" data-ext="webm" data-slot="first">';
        html += files.video_first_webm ? '&#9989;' : '&#10060;';
        html += ' 1\u00e8re (WEBM)</button>';
        html += '</div>';

        html += '<div style="display:flex;gap:8px;flex-wrap:wrap;">';
        html += '<button class="file-badge ' + (files.video_second ? 'file-ok' : 'file-missing') + '" data-tid="' + tid + '" data-ext="mp4" data-slot="second">';
        html += files.video_second ? '&#9989;' : '&#10060;';
        html += ' 2\u00e8me (MP4)</button>';
        html += '<button class="file-badge ' + (files.video_second_webm ? 'file-ok' : 'file-missing') + '" data-tid="' + tid + '" data-ext="webm" data-slot="second">';
        html += files.video_second_webm ? '&#9989;' : '&#10060;';
        html += ' 2\u00e8me (WEBM)</button>';
        html += '</div></div>';
      }

      html += '<div style="width:100%;margin-top:12px;padding-top:12px;border-top:1px dashed var(--nt-border);">';
      html += '<p style="font-size:13px;color:var(--nt-muted);margin-bottom:8px;">Chansons globales (dossier songs/)</p>';
      html += '<button class="btn btn-sm btn-outline" id="uploadSongBtn"><i class="fas fa-music"></i> Uploader un MP3</button>';
      html += ' <span id="songCount" style="font-size:13px;color:var(--nt-muted);">' + songs.length + ' chanson(s)</span>';
      html += '</div>';
      container.innerHTML = html;
    }

    document.getElementById('tmplForm').addEventListener('submit', async function (e) {
      e.preventDefault();
      var id = document.getElementById('editId').value;
      var data = {
        name: document.getElementById('editName').value.trim(),
        folder_path: document.getElementById('editFolder').value.trim(),
        thumbnail: document.getElementById('editThumb').value.trim(),
        text_offset: document.getElementById('editOffset').value,
        text_delay: parseFloat(document.getElementById('editDelay').value) || 1.25,
        prefix_text: document.getElementById('previewPrefix').value.trim() || 'Mariage de',
        prefix_hidden: !document.getElementById('showPrefix').checked,
        font_size_rem: parseFloat(document.getElementById('previewNamesSize').value) || 1.6,
        prefix_font_size_rem: parseFloat(document.getElementById('previewPrefixSize').value) || 1.0,
        date_font_size_rem: parseFloat(document.getElementById('previewDateSize').value) || 0.9,
        date_offset: document.getElementById('editDateOffset').value,
        hero_text_color: document.getElementById('previewTextColor').value,
        is_active: document.getElementById('editActive').checked,
        video_loop: document.getElementById('editVideoLoop').checked,
        has_two_videos: document.getElementById('editTwoVideos').checked,
      };

      try {
        if (id) {
          data.id = parseInt(id);
          await API.put(BASE_URL + 'api/admin/templates.php', data);
          showAlert('Template modifié avec succès.', 'success');
          await loadTemplate();
        } else {
          if (!data.folder_path) { showAlert('Le dossier est requis.'); return; }
          var res = await API.post(BASE_URL + 'api/admin/templates.php', data);
          var newId = res.data.template_id;
          showAlert('Template <strong>' + h(data.name) + '</strong> créé.', 'success');
          document.getElementById('editId').value = newId;
          document.getElementById('formTitle').textContent = 'Modifier';
          document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save"></i> Enregistrer';
          document.getElementById('pageTitle').textContent = data.name;
          document.getElementById('pageSub').textContent = 'Consultez et modifiez les informations de ' + data.name;
          document.getElementById('tmplInfoCard').style.display = 'block';
          document.getElementById('uploadCard').style.display = 'block';
          tmplId = newId;
          await loadTemplate();
        }
      } catch (err) {
        showAlert(err.message);
      }
    });

    // Upload via click on file badges or song upload button
    document.getElementById('fileActions').addEventListener('click', function (e) {
      var input = document.getElementById('uploadInput');
      var songBtn = e.target.closest('#uploadSongBtn');
      if (songBtn) {
        input.accept = '.mp3,audio/mpeg';
        input.dataset.ext = 'mp3';
        input.dataset.dest = 'songs';
        input.click();
        return;
      }
      var btn = e.target.closest('.file-badge');
      if (!btn) return;
      var tid = btn.dataset.tid;
      var ext = btn.dataset.ext;
      var slot = btn.dataset.slot || '';
      if (ext === 'mp4') input.accept = '.mp4,video/mp4';
      else if (ext === 'webm') input.accept = '.webm,video/webm';
      input.dataset.tid = tid;
      input.dataset.ext = ext;
      input.dataset.slot = slot;
      input.dataset.dest = 'template';
      input.click();
    });

    document.getElementById('uploadInput').addEventListener('change', async function () {
      var file = this.files[0];
      if (!file) return;
      var ext = this.dataset.ext;
      var dest = this.dataset.dest;
      var tid = this.dataset.tid;

      var formData = new FormData();
      var url;

      if (dest === 'songs') {
        formData.append('file', file);
        url = BASE_URL + 'api/admin/songs.php';
      } else {
        formData.append('template_id', tid);
        formData.append('file', file);
        formData.append('slot', this.dataset.slot || '');
        url = BASE_URL + 'api/admin/upload.php';
      }

      // Disable buttons and show progress
      var btns = document.querySelectorAll('.file-badge, #uploadSongBtn');
      btns.forEach(function (b) { b.classList.add('uploading'); });
      var pw = document.getElementById('uploadProgressWrap');
      var pf = document.getElementById('uploadProgressFill');
      var pt = document.getElementById('uploadProgressText');
      pw.classList.add('show');
      pf.style.width = '0%';
      pt.textContent = 'Préparation du téléversement...';

      try {
        var result = await new Promise(function (resolve, reject) {
          var xhr = new XMLHttpRequest();
          xhr.open('POST', url);
          xhr.setRequestHeader('Authorization', 'Bearer ' + API.getToken());
          xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
              var pct = Math.round((e.loaded / e.total) * 100);
              pf.style.width = pct + '%';
              pt.textContent = 'Téléversement... ' + pct + '%';
            }
          };
          xhr.onload = function () {
            try {
              var data = JSON.parse(xhr.responseText);
              if (xhr.status >= 200 && xhr.status < 300) {
                resolve(data);
              } else {
                reject(new Error(data.error || 'Upload échoué'));
              }
            } catch (e) {
              reject(new Error('Réponse invalide du serveur'));
            }
          };
          xhr.onerror = function () { reject(new Error('Erreur réseau')); };
          xhr.send(formData);
        });

        pt.textContent = 'Téléversement terminé !';
        showAlert(ext.toUpperCase() + ' uploadé avec succès.', 'success');
        await loadTemplate();
      } catch (err) {
        pt.textContent = 'Échec du téléversement';
        showAlert(err.message);
        await loadTemplate();
      }

      btns.forEach(function (b) { b.classList.remove('uploading'); });
      setTimeout(function () { pw.classList.remove('show'); }, 3000);
      this.value = '';
    });

    function setTextPosition(pct) {
      pct = Math.max(0, Math.min(100, pct));
      document.getElementById('editOffsetPct').value = pct;
      document.getElementById('editOffset').value = 'pct:' + pct;
      var container = document.getElementById('previewContainer');
      var h = container.offsetHeight;
      document.getElementById('dragText').style.top = (h * pct / 100) + 'px';
      document.getElementById('dragGuide').style.top = (h * pct / 100) + 'px';
      document.getElementById('dragHandle').style.top = (h * pct / 100) + 'px';
      document.querySelectorAll('.preset-positions button').forEach(function(b) {
        b.classList.toggle('active', b.dataset.pct == pct);
      });
    }

    function setDatePosition(pct) {
      var container = document.getElementById('previewContainer');
      var h = container.offsetHeight;
      var el = document.getElementById('dragDate');
      var handle = document.getElementById('dragDateHandle');
      var guide = document.getElementById('dragDateGuide');
      if (pct === '' || pct === null || pct === undefined) {
        // Auto: position date below the text block (text_pct + 15%)
        var textPct = parseInt(document.getElementById('editOffsetPct').value) || 50;
        var autoPct = Math.min(textPct + 15, 95);
        el.style.display = '';
        handle.style.display = '';
        guide.style.display = '';
        document.getElementById('editDateOffset').value = '';
        document.getElementById('editDateOffsetPct').value = autoPct;
        el.style.top = (h * autoPct / 100) + 'px';
        handle.style.top = (h * autoPct / 100) + 'px';
        guide.style.top = (h * autoPct / 100) + 'px';
        document.querySelectorAll('[data-date-pct]').forEach(function(b) {
          b.classList.toggle('active', b.dataset.datePct === '');
        });
        return;
      }
      pct = Math.max(0, Math.min(100, parseInt(pct)));
      el.style.display = '';
      handle.style.display = '';
      guide.style.display = '';
      document.getElementById('editDateOffsetPct').value = pct;
      document.getElementById('editDateOffset').value = 'pct:' + pct;
      el.style.top = (h * pct / 100) + 'px';
      handle.style.top = (h * pct / 100) + 'px';
      guide.style.top = (h * pct / 100) + 'px';
      document.querySelectorAll('[data-date-pct]').forEach(function(b) {
        b.classList.toggle('active', b.dataset.datePct == pct);
      });
    }

    // Drag interaction for text
    (function() {
      var container = document.getElementById('previewContainer');
      var handle = document.getElementById('dragHandle');
      var dragText = document.getElementById('dragText');
      var isDragging = false;

      function startDrag(e) {
        isDragging = true;
        container.style.cursor = 'grabbing';
        e.preventDefault();
      }

      handle.addEventListener('mousedown', startDrag);
      dragText.addEventListener('mousedown', startDrag);

      document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        var rect = container.getBoundingClientRect();
        var pct = ((e.clientY - rect.top) / rect.height) * 100;
        pct = Math.max(0, Math.min(100, pct));
        pct = Math.round(pct);
        setTextPosition(pct);
      });

      document.addEventListener('mouseup', function() {
        if (isDragging) {
          isDragging = false;
          container.style.cursor = 'grab';
        }
      });
    })();

    // Drag interaction for date
    (function() {
      var container = document.getElementById('previewContainer');
      var handle = document.getElementById('dragDateHandle');
      var dragDate = document.getElementById('dragDate');
      var isDragging = false;

      function startDrag(e) {
        isDragging = true;
        container.style.cursor = 'grabbing';
        e.preventDefault();
      }

      handle.addEventListener('mousedown', startDrag);
      dragDate.addEventListener('mousedown', startDrag);

      document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        var rect = container.getBoundingClientRect();
        var pct = ((e.clientY - rect.top) / rect.height) * 100;
        pct = Math.max(0, Math.min(100, pct));
        pct = Math.round(pct);
        setDatePosition(pct);
      });

      document.addEventListener('mouseup', function() {
        if (isDragging) {
          isDragging = false;
          container.style.cursor = 'grab';
        }
      });
    })();

    // Preview text inputs
    document.getElementById('previewPrefix').addEventListener('input', updateDragText);
    document.getElementById('previewNames').addEventListener('input', updateDragText);
    document.getElementById('showPrefix').addEventListener('change', updateDragText);

    function updateDragText() {
      var prefix = document.getElementById('previewPrefix').value;
      var names = document.getElementById('previewNames').value || 'Prénom & Prénom';
      var show = document.getElementById('showPrefix').checked;
      document.getElementById('dragPrefix').textContent = prefix;
      document.getElementById('dragPrefix').style.display = show ? '' : 'none';
      document.getElementById('dragNames').textContent = names;
    }

    function updatePreviewColor(color) {
      document.getElementById('dragText').style.color = color;
      document.getElementById('dragDate').style.color = color;
    }

    // Text color picker
    document.getElementById('previewTextColor').addEventListener('input', function() {
      updatePreviewColor(this.value);
    });
    document.getElementById('resetTextColor').addEventListener('click', function() {
      document.getElementById('previewTextColor').value = '#FFFFFF';
      updatePreviewColor('#FFFFFF');
    });

    // Font size sliders
    document.getElementById('previewNamesSize').addEventListener('input', function() {
      document.getElementById('dragNames').style.fontSize = this.value + 'rem';
      document.getElementById('namesSizeLabel').textContent = this.value;
    });
    document.getElementById('previewPrefixSize').addEventListener('input', function() {
      document.getElementById('dragPrefix').style.fontSize = this.value + 'rem';
      document.getElementById('prefixSizeLabel').textContent = this.value;
    });
    document.getElementById('previewDateSize').addEventListener('input', function() {
      document.getElementById('dragDate').style.fontSize = this.value + 'rem';
      document.getElementById('dateSizeLabel').textContent = this.value;
    });

    // Preset positions
    document.querySelectorAll('.preset-positions button').forEach(function(btn) {
      btn.addEventListener('click', function() {
        setTextPosition(parseInt(this.dataset.pct));
      });
    });

    // Numeric input for position
    document.getElementById('editOffsetPct').addEventListener('input', function() {
      var v = parseInt(this.value);
      if (!isNaN(v)) setTextPosition(v);
    });

    // Date preset positions
    document.querySelectorAll('[data-date-pct]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var v = this.dataset.datePct;
        if (v === '') setDatePosition(null);
        else setDatePosition(parseInt(v));
      });
    });

    // Numeric input for date position
    document.getElementById('editDateOffsetPct').addEventListener('input', function() {
      var v = parseInt(this.value);
      if (!isNaN(v)) setDatePosition(v);
    });

    function toggleSidebar() {
      document.body.classList.toggle('sidebar-open');
    }
  </script>
</body>
</html>