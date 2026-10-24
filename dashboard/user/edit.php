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
  <title>Modifier l'Invitation</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
  <style>
    .map-preview { margin-top:8px; width:100%; height:200px; border-radius:8px; overflow:hidden; border:1px solid #eee; display:none; }
    .map-preview iframe { width:100%; height:100%; border:0; }
    .char-count { font-size:12px; color:var(--gray-500); text-align:right; margin-top:2px; }

    /* Preset colors */
    .color-presets { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:8px; }
    .color-swatch {
      width:36px; height:36px; border-radius:8px; cursor:pointer;
      border:2px solid transparent; transition:all .2s;
    }
    .color-swatch:hover { transform:scale(1.1); }
    .color-swatch.selected { border-color:var(--navy); box-shadow:0 0 0 3px rgba(30,58,95,0.15); }

    /* Song inline player */
    .song-item { display:flex; align-items:center; gap:8px; padding:8px 12px; border-radius:8px; cursor:pointer; transition:all .2s; border:1px solid var(--gray-200); margin-bottom:4px; }
    .song-item:hover { background:var(--gray-50); border-color:var(--navy-light); }
    .song-item.selected { background:#FFFBEB; border-color:var(--gold); }
    .song-item .play-icon { width:32px; height:32px; border-radius:50%; background:var(--navy); color:var(--white); display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; }
    .song-item .play-icon.playing { background:var(--gold); }
    .song-item .song-name { flex:1; font-size:14px; color:var(--navy); font-weight:500; }
    .song-item .check { color:var(--gold); font-size:16px; }
    .song-item .default-label { font-size:11px; color:var(--gray-400); font-style:italic; }
    .song-list { max-height:200px; overflow-y:auto; }

    /* Toggle switch for video loop */
    .toggle-wrap { display:flex; align-items:center; gap:12px; }
    .toggle-switch {
      position:relative; width:48px; height:26px; flex-shrink:0;
      background:var(--gray-200); border-radius:13px; cursor:pointer; transition:background .3s;
    }
    .toggle-switch.on { background:var(--gold); }
    .toggle-switch::after {
      content:''; position:absolute; top:3px; left:3px;
      width:20px; height:20px; border-radius:50%; background:var(--white);
      transition:transform .3s; box-shadow:0 1px 4px rgba(0,0,0,0.15);
    }
    .toggle-switch.on::after { transform:translateX(22px); }
    .toggle-label { font-size:14px; color:var(--gray-500); }

    /* Helper text */
    .help-text { font-size:13px; color:var(--gray-400); margin-top:6px; line-height:1.5; }

    /* Advanced toggle */
    .advanced-toggle { text-align:center; padding:8px; cursor:pointer; color:var(--navy-light); font-size:13px; }
    .advanced-toggle:hover { text-decoration:underline; }
    .advanced-content { display:none; }
    .advanced-content.open { display:block; }

    /* Template cards */
    .tmpl-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; }
    .tmpl-card {
      border:2px solid var(--gray-200); border-radius:12px; overflow:hidden;
      cursor:pointer; transition:all .2s ease; background:var(--white);
      position:relative;
    }
    .tmpl-card:hover { border-color:var(--navy-light); transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,0.08); }
    .tmpl-card.selected { border-color:var(--gold); box-shadow:0 0 0 3px rgba(201,169,110,0.25); }
    .tmpl-card .tmpl-thumb {
      height:110px; display:flex; align-items:center; justify-content:center;
      font-size:36px; color:rgba(255,255,255,0.6); position:relative;
    }
    .tmpl-card .tmpl-thumb .play-badge {
      position:absolute; bottom:8px; right:8px;
      background:rgba(0,0,0,0.5); color:var(--white); border:none;
      border-radius:6px; padding:4px 10px; font-size:11px; cursor:pointer;
      display:flex; align-items:center; gap:4px; transition:all .2s;
    }
    .tmpl-card .tmpl-thumb .play-badge:hover { background:rgba(0,0,0,0.8); }
    .tmpl-card .tmpl-thumb .play-badge i { font-size:10px; }
    .tmpl-card .tmpl-info { padding:10px 12px; }
    .tmpl-card .tmpl-info .tmpl-name { font-size:14px; font-weight:600; color:var(--navy); }
    .tmpl-card .tmpl-info .tmpl-desc { font-size:11px; color:var(--gray-400); margin-top:2px; }
    .tmpl-card .check-mark {
      position:absolute; top:8px; right:8px; width:22px; height:22px;
      border-radius:50%; background:var(--gold); color:var(--white);
      display:none; align-items:center; justify-content:center; font-size:12px;
    }
    .tmpl-card.selected .check-mark { display:flex; }

    /* Preview modal */
    .modal-overlay {
      display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85);
      z-index:9999; align-items:center; justify-content:center; padding:20px;
    }
    .modal-overlay.open { display:flex; }
    .modal-overlay .modal-content {
      max-width:800px; width:100%; border-radius:12px; overflow:hidden;
      background:#000; position:relative;
    }
    .modal-overlay .modal-content video { width:100%; display:block; max-height:80vh; }
    .modal-overlay .modal-close {
      position:absolute; top:12px; right:12px; width:36px; height:36px;
      border-radius:50%; background:rgba(0,0,0,0.5); border:none;
      color:var(--white); font-size:18px; cursor:pointer;
      display:flex; align-items:center; justify-content:center;
      transition:background .2s;
    }
    .modal-overlay .modal-close:hover { background:rgba(0,0,0,0.8); }
    .modal-overlay .modal-label {
      position:absolute; bottom:0; left:0; right:0;
      padding:12px 16px; background:linear-gradient(transparent,rgba(0,0,0,0.8));
      color:var(--white); font-size:14px; text-align:center;
    }

    .pfp-upload {
      position:relative; width:100%; max-width:200px; aspect-ratio:1;
      border:2px dashed var(--gray-200); border-radius:12px;
      display:flex; align-items:center; justify-content:center;
      cursor:pointer; overflow:hidden; transition:all .2s;
    }
    .pfp-upload:hover { border-color:var(--gold); background:var(--gray-50); }
    .pfp-upload .pfp-placeholder {
      display:flex; flex-direction:column; align-items:center; gap:8px;
      color:var(--gray-400); font-size:13px;
    }
    .pfp-upload .pfp-placeholder i { font-size:36px; }
    .pfp-upload .pfp-preview {
      position:absolute; inset:0; display:flex;
      align-items:center; justify-content:center; overflow:hidden;
    }
    .pfp-upload .pfp-preview img {
      width:100%; height:100%; object-fit:cover;
    }
    .pfp-upload .pfp-remove {
      position:absolute; top:4px; right:4px; width:28px; height:28px;
      border-radius:50%; background:rgba(0,0,0,0.5); color:#fff; border:none;
      font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center;
      line-height:1; transition:background .2s;
    }
    .pfp-upload .pfp-remove:hover { background:rgba(200,0,0,0.8); }
    .pfp-upload.uploading { opacity:0.5; pointer-events:none; }

    /* Cropper */
    .cropper-circle {
      width:260px; height:260px; border-radius:50%;
      margin:0 auto 16px; overflow:hidden;
      border:3px solid var(--gold); box-shadow:var(--shadow);
      position:relative; cursor:grab; background:#000;
      user-select:none;
    }
    .cropper-circle:active { cursor:grabbing; }
    .cropper-circle img {
      width:calc(100% * var(--cs,1));
      height:calc(100% * var(--cs,1));
      max-width:none; display:block;
      pointer-events:none; user-select:none;
      object-fit:cover;
      object-position:var(--cx, 50%) var(--cy, 50%);
      position:absolute; top:50%; left:50%;
      transform:translate(-50%,-50%);
    }
    .cropper-zoom {
      display:flex; align-items:center; gap:12px;
      justify-content:center; margin-bottom:16px;
    }
    .cropper-zoom input[type=range] { width:180px; }
    .cropper-zoom .zoom-val {
      font-size:14px; color:var(--gray-500); min-width:36px; text-align:center;
    }

    @media (max-width:600px) {
      .tmpl-grid { grid-template-columns:repeat(2,1fr); }
    }
  </style>
</head>
<body>
  <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
  <div id="loading"><div class="spinner"></div> Chargement...</div>

  <aside class="sidebar">
    <div class="brand">Invito<span>.</span></div>
    <div class="user-info" id="userInfo"><strong>Utilisateur</strong><span id="userRoleLabel">Couple</span></div>
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>dashboard/user/index.php"><span class="icon"><i class="fas fa-home"></i></span><span>Tableau de Bord</span></a>
      <a href="<?= BASE_URL ?>dashboard/user/edit.php" class="active"><span class="icon"><i class="fas fa-edit"></i></span><span>Modifier l'Invitation</span></a>
      <a href="<?= BASE_URL ?>dashboard/user/rsvps.php"><span class="icon"><i class="fas fa-phone-alt"></i></span><span>RSVPs Reçus</span></a>
    </nav>
    <div class="sidebar-footer"><button class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button></div>
  </aside>

  <main class="main">
    <div class="page-header">
      <h1>Modifier l'Invitation</h1>
      <p id="pageSubtitle">Personnalisez votre invitation de mariage</p>
    </div>

    <div id="alert" class="alert"></div>

    <form id="invitationForm" autocomplete="off">
      <!-- Couple / Diplômé -->
      <div class="card" id="coupleSection">
        <div class="card-header"><h2 id="coupleSectionTitle"><i class="fas fa-heart" style="color:var(--gold);"></i> <span data-type="couple">Les Mariés</span><span data-type="bac" style="display:none;">Le Diplômé(e)</span><span data-type="circoncision" style="display:none;">L'Enfant</span></h2></div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group">
              <label for="groom_name"><span data-type="couple">Nom du Marié</span><span data-type="bac" style="display:none;">Nom du Diplômé(e)</span><span data-type="circoncision" style="display:none;">Prénom de l'enfant</span></label>
              <input type="text" class="form-control" id="groom_name" name="groom_name" placeholder="Alex" />
            </div>
            <div class="form-group">
              <label for="bride_name"><span data-type="couple">Nom de la Mariée</span><span data-type="bac" style="display:none;">Filière / Mention</span><span data-type="circoncision" style="display:none;">Date de naissance</span></label>
              <input type="text" class="form-control" id="bride_name" name="bride_name" placeholder="Sarah" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="groom_parents"><span data-type="couple">Parents du Marié</span><span data-type="bac" style="display:none;">Parents</span><span data-type="circoncision" style="display:none;">Parents</span></label>
              <input type="text" class="form-control" id="groom_parents" name="groom_parents" placeholder="Fils de M. & Mme ..." />
            </div>
            <div class="form-group" id="brideParentsGroup">
              <label for="bride_parents"><span data-type="couple">Parents de la Mariée</span></label>
              <input type="text" class="form-control" id="bride_parents" name="bride_parents" placeholder="Fille de M. & Mme ..." />
            </div>
          </div>
        </div>
      </div>

      <!-- Date & Venue -->
      <div class="card">
        <div class="card-header"><h2><i class="fas fa-calendar-alt" style="color:var(--gold);"></i> <span data-type="couple">Date & Lieu</span><span data-type="bac" style="display:none;">Date & Lieu</span><span data-type="circoncision" style="display:none;">Date & Lieu</span></h2></div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group">
              <label for="wedding_date"><span data-type="couple">Date du Mariage</span><span data-type="bac" style="display:none;">Date de la Célébration</span><span data-type="circoncision" style="display:none;">Date de la Cérémonie</span></label>
              <input type="date" class="form-control" id="wedding_date" name="wedding_date" />
            </div>
            <div class="form-group">
              <label for="wedding_time">Horaire</label>
              <input type="text" class="form-control" id="wedding_time" name="wedding_time" placeholder="ex: 10h00 - 15h00" />
            </div>
          </div>
          <div class="form-group">
            <label for="venue_name">Lieu de la Cérémonie</label>
            <input type="text" class="form-control" id="venue_name" name="venue_name" placeholder="Nom du lieu" />
          </div>
          <div class="form-group">
            <label for="venue_address">Adresse</label>
            <textarea class="form-control" id="venue_address" name="venue_address" rows="2" placeholder="Adresse complète"></textarea>
          </div>
          <div class="advanced-toggle" onclick="toggleAdvanced('mapSection')"><i class="fas fa-cog"></i> Lien Google Maps (optionnel)</div>
          <div class="advanced-content" id="mapSection">
            <div class="form-group">
              <label for="map_embed">Lien du bouton (Google Maps)</label>
              <input type="text" class="form-control" id="map_embed" name="map_embed" placeholder="https://maps.app.goo.gl/..." />
              <div class="help-text">Le bouton jaune ouvrira ce lien.</div>
            </div>
            <div class="form-group">
              <label for="map_iframe">Lien de l'iframe (Carte intégrée)</label>
              <input type="text" class="form-control" id="map_iframe" name="map_iframe" placeholder="https://www.google.com/maps/embed?pb=..." />
              <div class="help-text">Collez le lien "Intégrer" de Google Maps pour afficher la carte. Si vide, généré automatiquement depuis le lieu.</div>
              <div class="map-preview" id="mapPreview"><iframe id="mapIframe" src=""></iframe></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Love Story / Parcours -->
      <div class="card">
        <div class="card-header"><h2><i class="fas fa-book-open" style="color:var(--gold);"></i> <span data-type="couple">Notre Histoire</span><span data-type="bac" style="display:none;">Mon Parcours</span><span data-type="circoncision" style="display:none;">À Propos</span></h2></div>
        <div class="card-body">
          <div class="form-group">
            <label for="love_story_title"><span data-type="couple">Titre de l'histoire</span><span data-type="bac" style="display:none;">Titre du parcours</span><span data-type="circoncision" style="display:none;">Titre</span></label>
            <input type="text" class="form-control" id="love_story_title" name="love_story_title" placeholder="Notre Voyage" />
          </div>
          <div class="form-group">
            <label for="love_story_text"><span data-type="couple">Racontez votre histoire</span><span data-type="bac" style="display:none;">Décrivez votre parcours</span><span data-type="circoncision" style="display:none;">Description</span></label>
            <textarea class="form-control" id="love_story_text" name="love_story_text" rows="4" placeholder="Comment vous êtes-vous rencontrés ?"></textarea>
            <div class="char-count"><span id="storyCount">0</span> / 5000</div>
          </div>
        </div>
      </div>

      <!-- Quote -->
      <div class="card">
        <div class="card-header"><h2><i class="fas fa-quote-right" style="color:var(--gold);"></i> Citation</h2></div>
        <div class="card-body">
          <div class="form-group">
            <label for="quote_text">Votre citation ou message</label>
            <textarea class="form-control" id="quote_text" name="quote_text" rows="3" placeholder="Une phrase qui vous représente..."></textarea>
          </div>
        </div>
      </div>

      <!-- Photos -->
      <div class="card">
        <div class="card-header"><h2><i class="fas fa-camera" style="color:var(--gold);"></i> Photos</h2></div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group" id="pfpGroomGroup">
              <label><span data-type="couple">Photo du Marié</span><span data-type="bac" style="display:none;">Votre Photo</span><span data-type="circoncision" style="display:none;">Photo de l'enfant</span></label>
              <div class="pfp-upload" id="pfpGroomUpload">
                <div class="pfp-preview" id="pfpGroomPreview" style="display:none;">
                  <img id="pfpGroomImg" src="" alt="" />
                  <button type="button" class="pfp-remove" id="pfpGroomRemove" title="Supprimer">&times;</button>
                </div>
                <div class="pfp-placeholder" id="pfpGroomPlaceholder">
                  <i class="fas fa-user"></i>
                  <span>Ajouter une photo</span>
                </div>
                <input type="file" id="pfpGroomInput" accept="image/jpeg,image/png,image/webp" style="display:none;" />
              </div>
            </div>
            <div class="form-group" id="pfpBrideGroup">
              <label>Photo de la Mariée</label>
              <div class="pfp-upload" id="pfpBrideUpload">
                <div class="pfp-preview" id="pfpBridePreview" style="display:none;">
                  <img id="pfpBrideImg" src="" alt="" />
                  <button type="button" class="pfp-remove" id="pfpBrideRemove" title="Supprimer">&times;</button>
                </div>
                <div class="pfp-placeholder" id="pfpBridePlaceholder">
                  <i class="fas fa-user"></i>
                  <span>Ajouter une photo</span>
                </div>
                <input type="file" id="pfpBrideInput" accept="image/jpeg,image/png,image/webp" style="display:none;" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Template & Apparence -->
      <div class="card">
        <div class="card-header"><h2><i class="fas fa-palette" style="color:var(--gold);"></i> Thème & Apparence</h2></div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group">
              <label>Style d'invitation</label>
              <div class="tmpl-grid" id="tmplGrid"></div>
              <input type="hidden" id="template_id" name="template_id" value="" />
              <div class="help-text">Cliquez sur un style pour le sélectionner. Utilisez <i class="fas fa-play"></i> pour prévisualiser la vidéo.</div>
            </div>
            <div class="form-group">
              <label for="text_color">Couleur du texte</label>
              <div class="color-presets" id="colorPresets"></div>
              <div style="display:flex;gap:12px;align-items:center;">
                <input type="color" class="form-control" id="text_color" name="text_color" style="width:60px;height:44px;padding:4px;cursor:pointer;" />
                <input type="text" class="form-control" id="text_color_hex" placeholder="#2C2420" style="flex:1;font-family:monospace;" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Music -->
      <div class="card">
        <div class="card-header"><h2><i class="fas fa-music" style="color:var(--gold);"></i> Musique d'ambiance</h2></div>
        <div class="card-body">
          <input type="hidden" id="song_path" name="song_path" value="" />
          <div class="song-list" id="songList"></div>
          <audio id="previewAudio" style="display:none;"></audio>
          <div class="help-text" style="margin-top:8px;">Cliquez sur une chanson pour l'écouter et la sélectionner.</div>
        </div>
      </div>

      <!-- Publish -->
      <div class="card">
        <div class="card-header"><h2><i class="fas fa-globe" style="color:var(--gold);"></i> Publication</h2></div>
        <div class="card-body">
          <div class="toggle-wrap">
            <div class="toggle-switch" id="publishToggle" onclick="this.classList.toggle('on');document.getElementById('is_published').value=this.classList.contains('on')?'1':'0';"></div>
            <div class="toggle-label">Invitation visible par les invités</div>
          </div>
          <input type="hidden" id="is_published" name="is_published" value="0" />
          <input type="hidden" id="invitation_type" name="invitation_type" value="couple" />
          <div class="help-text">Activez pour que vos invités puissent voir l'invitation via le lien.</div>
        </div>
      </div>

      <div style="display:flex;gap:12px;margin-top:24px;flex-wrap:wrap;">
        <button type="submit" class="btn btn-gold" style="flex:1;padding:14px;font-size:16px;"><i class="fas fa-save"></i> Enregistrer</button>
        <a href="#" id="previewLink" class="btn btn-outline" style="padding:14px 24px;font-size:16px;" target="_blank"><i class="fas fa-external-link-alt"></i> Voir l'invitation</a>
      </div>
    </form>
  </main>

  <!-- Cropper Modal -->
  <div class="modal-overlay" id="cropperModal">
    <div class="modal-content" style="max-width:400px;">
      <button class="modal-close" id="cropperClose">&times;</button>
      <h3 style="font-family:var(--font-heading);margin-bottom:16px;color:var(--navy);text-align:center;">Ajuster la photo</h3>
      <div class="cropper-circle" id="cropperCircle">
        <img id="cropperImg" src="" alt="" />
      </div>
      <div class="cropper-zoom">
        <i class="fas fa-search-minus" style="color:var(--gray-400);"></i>
        <input type="range" id="cropperZoom" min="1" max="3" step="0.05" value="1" />
        <i class="fas fa-search-plus" style="color:var(--gray-400);"></i>
      </div>
      <div style="display:flex;gap:12px;justify-content:center;">
        <button class="btn btn-sm btn-outline" id="cropperCancel">Annuler</button>
        <button class="btn btn-sm" id="cropperSave" style="background:var(--gold);color:#fff;border:none;">Appliquer</button>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
  <script>
    var allSongs = [];
    var currentSlug = '';

    function setInvitationType(type) {
      type = type === 'circoncision' ? 'circoncision' : (type === 'bac' ? 'bac' : 'couple');
      document.getElementById('invitation_type').value = type;

      // Update sidebar label
      var roleLabel = document.getElementById('userRoleLabel');
      if (roleLabel) roleLabel.textContent = type === 'bac' ? 'Dipl\u00f4m\u00e9' : (type === 'circoncision' ? 'Circoncision' : 'Couple');

      // Update page subtitle
      document.getElementById('pageSubtitle').textContent = type === 'bac'
        ? 'Personnalisez votre invitation de f\u00eate'
        : (type === 'circoncision'
          ? 'Personnalisez votre faire-part de circoncision'
          : 'Personnalisez votre invitation de mariage');

      // Toggle all data-type spans
      document.querySelectorAll('[data-type]').forEach(function (el) {
        el.style.display = el.getAttribute('data-type') === type ? '' : 'none';
      });

      // Hide bride parents group for bac/circoncision
      var bpGroup = document.getElementById('brideParentsGroup');
      if (bpGroup) {
        bpGroup.style.display = type === 'couple' ? '' : 'none';
      }
      // Hide bride photo for bac/circoncision
      var pfpBride = document.getElementById('pfpBrideGroup');
      if (pfpBride) {
        pfpBride.style.display = type === 'couple' ? '' : 'none';
      }

      // Update placeholders
      document.getElementById('groom_name').placeholder = type === 'bac' ? 'Pr\u00e9nom du dipl\u00f4m\u00e9' : (type === 'circoncision' ? 'Pr\u00e9nom de l\'enfant' : 'Alex');
      document.getElementById('bride_name').placeholder = type === 'bac' ? 'Sciences, Lettres...' : (type === 'circoncision' ? 'ex: 15 mars 2026' : 'Sarah');
      document.getElementById('groom_parents').placeholder = type === 'bac' ? 'Parents du dipl\u00f4m\u00e9' : (type === 'circoncision' ? 'Parents de l\'enfant' : 'Fils de M. & Mme ...');
      document.getElementById('bride_parents').placeholder = type === 'couple' ? 'Fille de M. & Mme ...' : '';
      document.getElementById('love_story_title').placeholder = type === 'bac' ? 'Mon Parcours' : (type === 'circoncision' ? '\u00c0 Propos' : 'Notre Voyage');
      document.getElementById('love_story_text').placeholder = type === 'bac' ? 'D\u00e9crivez votre parcours...' : (type === 'circoncision' ? 'D\u00e9crivez le parcours de votre enfant...' : 'Comment vous \u00eates-vous rencontr\u00e9s ?');
      document.getElementById('quote_text').placeholder = type === 'bac' ? 'Une citation inspirante...' : (type === 'circoncision' ? 'Une citation ou un message...' : 'Une phrase qui vous repr\u00e9sente...');
    }

    function toggleAdvanced(id) {
      var el = document.getElementById(id);
      el.classList.toggle('open');
    }

    // Color presets
    var presetColors = ['#2C2420', '#1E3A5F', '#6B3A2A', '#2D6A4F', '#4A1942', '#8B4513', '#B8860B', '#000000'];
    var presetsContainer = document.getElementById('colorPresets');
    presetsContainer.innerHTML = presetColors.map(function (c) {
      return '<div class="color-swatch" data-color="' + c + '" style="background:' + c + ';"></div>';
    }).join('');
    document.querySelectorAll('.color-swatch').forEach(function (sw) {
      sw.addEventListener('click', function () {
        document.querySelectorAll('.color-swatch').forEach(function (s) { s.classList.remove('selected'); });
        this.classList.add('selected');
        var color = this.dataset.color;
        document.getElementById('text_color').value = color;
        document.getElementById('text_color_hex').value = color;
      });
    });

    // Song list
    function renderSongList() {
      var list = document.getElementById('songList');
      var currentVal = document.getElementById('song_path').value;
      var html = '';
      html += '<div class="song-item' + (!currentVal ? ' selected' : '') + '" data-song="">';
      html += '<div class="play-icon" data-play=""><i class="fas fa-play"></i></div>';
      html += '<span class="song-name">Par défaut du thème</span>';
      html += '<span class="check">' + (!currentVal ? '<i class="fas fa-check-circle"></i>' : '') + '</span>';
      html += '</div>';
      allSongs.forEach(function (s) {
        var sel = s === currentVal ? ' selected' : '';
        var name = s.replace('.mp3', '');
        var safe = s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        html += '<div class="song-item' + sel + '" data-song="' + safe + '">';
        html += '<div class="play-icon" data-play="' + safe + '"><i class="fas fa-play"></i></div>';
        html += '<span class="song-name">' + h(name) + '</span>';
        html += '<span class="check">' + (sel ? '<i class="fas fa-check-circle"></i>' : '') + '</span>';
        html += '</div>';
      });
      list.innerHTML = html;
    }

    // Song click: play or select
    document.getElementById('songList').addEventListener('click', function (e) {
      var item = e.target.closest('.song-item');
      if (!item) return;
      var playIcon = e.target.closest('.play-icon');
      var song = item.dataset.song;

      if (playIcon) {
        // Play/pause
        var audio = document.getElementById('previewAudio');
        if (playIcon.classList.contains('playing')) {
          audio.pause(); audio.src = '';
          document.querySelectorAll('.play-icon').forEach(function (i) { i.classList.remove('playing'); i.innerHTML = '<i class=\"fas fa-play\"></i>'; });
          return;
        }
        var url = song ? BASE_URL + 'songs/' + encodeURIComponent(song) : null;
        if (!url) return;
        audio.src = url;
        audio.play().then(function () {
          document.querySelectorAll('.play-icon').forEach(function (i) { i.classList.remove('playing'); i.innerHTML = '<i class=\"fas fa-play\"></i>'; });
          playIcon.classList.add('playing');
          playIcon.innerHTML = '<i class=\"fas fa-pause\"></i>';
        }).catch(function () {});
        return;
      }

      // Select song
      document.querySelectorAll('.song-item').forEach(function (i) { i.classList.remove('selected'); });
      item.classList.add('selected');
      document.querySelectorAll('.check').forEach(function (c) { c.innerHTML = ''; });
      var check = item.querySelector('.check');
      if (check) check.innerHTML = '<i class=\"fas fa-check-circle\"></i>';
      document.getElementById('song_path').value = song;
    });

    // Audio ended
    document.getElementById('previewAudio').addEventListener('ended', function () {
      document.querySelectorAll('.play-icon').forEach(function (i) { i.classList.remove('playing'); i.innerHTML = '<i class=\"fas fa-play\"></i>'; });
    });

    document.addEventListener('DOMContentLoaded', async function () {
      const user = await checkAuth();
      if (!user) return;
      document.querySelector('#userInfo strong').textContent = user.username;

      try {
        // Load templates
        const tmplRes = await fetch(BASE_URL + 'api/admin/templates.php', {
          headers: { 'Authorization': 'Bearer ' + API.getToken() }
        });
        const tmplData = await tmplRes.json();
        const tmplGrid = document.getElementById('tmplGrid');
        var templates = [];
        if (tmplData.data && tmplData.data.templates) {
          templates = tmplData.data.templates;
          // Template thumb gradients
          var gradients = [
            'linear-gradient(135deg,#1E3A5F,#2D6A4F)',
            'linear-gradient(135deg,#6B3A2A,#8B4513)',
            'linear-gradient(135deg,#4A1942,#2C2420)',
            'linear-gradient(135deg,#2D6A4F,#1E3A5F)',
            'linear-gradient(135deg,#B8860B,#6B3A2A)',
            'linear-gradient(135deg,#2C2420,#4A1942)',
          ];
          templates.forEach(function (t, i) {
            var card = document.createElement('div');
            card.className = 'tmpl-card';
            card.dataset.id = t.id;
            card.dataset.name = t.name;
            var videoUrl = '';
            var hasVid = false;
            var folderPath = 'templates/' + t.folder_path + '/';
            if (t.files && t.files.video_mp4) {
              videoUrl = BASE_URL + folderPath + 'video.mp4';
              hasVid = true;
            }
            // Fallback: multi-video (1.mp4)
            if (!hasVid) {
              videoUrl = BASE_URL + folderPath + '1.mp4';
              hasVid = true;
            }
            card.dataset.video = videoUrl;
            card.innerHTML =
              '<div class="tmpl-thumb" style="background:' + (gradients[i % gradients.length]) + '">' +
                '<i class="fas fa-images"></i>' +
                (videoUrl ? '<button class="play-badge" onclick="event.stopPropagation();openPreview(' + t.id + ')"><i class="fas fa-play"></i> Prévisualiser</button>' : '') +
              '</div>' +
              '<div class="tmpl-info">' +
                '<div class="tmpl-name">' + h(t.name) + '</div>' +
                '<div class="tmpl-desc">' + (t.files && t.files.video_mp4 ? 'Vidéo HD' : 'Vidéo') + '</div>' +
              '</div>' +
              '<div class="check-mark"><i class="fas fa-check"></i></div>';
            card.addEventListener('click', function () {
              document.querySelectorAll('.tmpl-card').forEach(function (c) { c.classList.remove('selected'); });
              this.classList.add('selected');
              document.getElementById('template_id').value = this.dataset.id;
              // Auto-detect invitation type from template name
              var name = (this.dataset.name || '').toLowerCase();
              var type = name.indexOf('baccalaureate') !== -1 ? 'bac' : (name.indexOf('thor') !== -1 ? 'circoncision' : 'couple');
              setInvitationType(type);
            });
            tmplGrid.appendChild(card);
          });
        }

        // Load songs
        const songsRes = await fetch(BASE_URL + 'api/admin/songs.php', {
          headers: { 'Authorization': 'Bearer ' + API.getToken() }
        });
        const songsData = await songsRes.json();
        if (songsData.data && songsData.data.songs) {
          allSongs = songsData.data.songs;
        }

        // Load invitation
        const invRes = await API.get(BASE_URL + 'api/user/invitation.php');
        const inv = invRes.data;
        currentSlug = inv.slug;

        // Fill form
        Object.keys(inv).forEach(function (key) {
          var el = document.getElementById(key);
          if (!el) return;
          if (key === 'is_published') {
            el.value = (inv[key] == 1) ? '1' : '0';
            if (inv[key] == 1) document.getElementById('publishToggle').classList.add('on');
          } else if (key === 'template_id') {
            el.value = inv[key] || '';
            // Select the matching card
            if (inv[key]) {
              var card = document.querySelector('.tmpl-card[data-id="' + inv[key] + '"]');
              if (card) card.classList.add('selected');
            }
          } else if (key === 'invitation_type') {
            el.value = inv[key] || 'couple';
            // Don't call setInvitationType here - it's called after form fill
            // by a separate block below
          } else if (el.tagName === 'INPUT' && el.type === 'hidden') {
            el.value = inv[key] || '';
          } else if (el.type === 'checkbox') {
            el.checked = inv[key] == 1;
          } else {
            el.value = inv[key] || '';
          }
        });

        // Apply invitation type UI
        setInvitationType(inv.invitation_type || 'couple');

        // Default texts
        if (!inv.love_story_text) {
          var invType = inv.invitation_type || 'couple';
          if (invType === 'bac') {
            document.getElementById('love_story_text').value =
              'Mon parcours a commencé bien avant cette étape importante... ' +
              'Entre défis relevés, nuits d\'étude et moments de doute, ' +
              'chaque effort m\'a mené jusqu\'à ce jour de célébration. ' +
              'Je suis fier(ère) de partager cette réussite avec ceux qui m\'ont soutenu(e).';
          } else if (invType === 'circoncision') {
            document.getElementById('love_story_text').value =
              'Notre enfant est arrivé dans nos vies comme un rayon de soleil... ' +
              'Aujourd\'hui, nous célébrons une belle étape de sa vie ' +
              'entourés de ceux qui lui portent tant d\'amour.';
          } else {
            document.getElementById('love_story_text').value =
              'C\'est en [date] que nos regards se sont croisés pour la première fois... ' +
              'Depuis ce jour, chaque instant passé ensemble a été un chapitre de notre belle histoire d\'amour. ' +
              'Entre rires partagés, voyages et projets communs, notre complicité n\'a cessé de grandir. ' +
              'Aujourd\'hui, nous sommes plus unis que jamais et avons hâte d\'écrire la suite ensemble.';
          }
        }
        if (!inv.quote_text) {
          var invType2 = inv.invitation_type || 'couple';
          if (invType2 === 'bac') {
            document.getElementById('quote_text').value =
              'Le succès n\'est pas la clé du bonheur. Le bonheur est la clé du succès. ' +
              'Si vous aimez ce que vous faites, vous réussirez.';
          } else if (invType2 === 'circoncision') {
            document.getElementById('quote_text').value =
              'Chaque enfant est une promesse de vie et d\'amour. ' +
              'Puissent ses pas être guidés par la lumière et la bienveillance.';
          } else {
            document.getElementById('quote_text').value =
              'L\'amour véritable ne connaît pas de limites. ' +
              'Il est le fil invisible qui relie deux cœurs, la force silencieuse qui nous pousse à devenir meilleurs.';
          }
        }

        // Map
        var mapSrc = inv.map_iframe || inv.map_embed || '';
        var match = mapSrc.match(/<iframe\s+src=["']([^"']+)["']/i);
        if (match) mapSrc = match[1];
        if (mapSrc) {
          document.getElementById('mapPreview').style.display = 'block';
          document.getElementById('mapIframe').src = mapSrc;
        }

        updateStoryCount();

        // Song list
        renderSongList();

        // Color preset
        var col = inv.text_color;
        if (col) {
          var sw = document.querySelector('.color-swatch[data-color="' + col + '"]');
          if (sw) sw.classList.add('selected');
        }

        // PFP
        if (inv.pfp_groom) {
          var gt = inv.pfp_groom_transform ? JSON.parse(inv.pfp_groom_transform) : null;
          showPFP('groom', inv.pfp_groom, gt ? gt.x : 50, gt ? gt.y : 50, gt ? gt.s : 1);
        }
        if (inv.pfp_bride) {
          var bt = inv.pfp_bride_transform ? JSON.parse(inv.pfp_bride_transform) : null;
          showPFP('bride', inv.pfp_bride, bt ? bt.x : 50, bt ? bt.y : 50, bt ? bt.s : 1);
        }

        // Preview link
        if (currentSlug) {
          document.getElementById('previewLink').href = BASE_URL + 'invitation.php?slug=' + encodeURIComponent(currentSlug);
        }
      } catch (err) {
        showAlert(err.message);
      }

      showLoading(false);
    });

    // Map preview
    function setupMapPreview(inputId) {
      document.getElementById(inputId).addEventListener('input', function () {
        var val = this.value.trim();
        var match = val.match(/<iframe\s+src=["']([^"']+)["']/i);
        if (match) val = match[1];
        var preview = document.getElementById('mapPreview');
        var iframe = document.getElementById('mapIframe');
        if (val) {
          preview.style.display = 'block';
          iframe.src = val;
        } else {
          preview.style.display = 'none';
          iframe.src = '';
        }
      });
    }
    setupMapPreview('map_embed');
    setupMapPreview('map_iframe');

    // Color sync
    var colorPicker = document.getElementById('text_color');
    var colorHex = document.getElementById('text_color_hex');
    colorPicker.addEventListener('input', function () { colorHex.value = this.value; document.querySelectorAll('.color-swatch').forEach(function (s) { s.classList.remove('selected'); }); });
    colorHex.addEventListener('input', function () {
      if (/^#[0-9a-f]{6}$/i.test(this.value)) {
        colorPicker.value = this.value;
        document.querySelectorAll('.color-swatch').forEach(function (s) { s.classList.remove('selected'); });
      }
    });

    // PFP upload + cropper
    var pfpData = { field: null, path: null, x: 50, y: 50, s: 1 };
    var isDragging = false, dragStartX, dragStartY, startX, startY;

    function uploadPFP(field, file) {
      var fd = new FormData();
      fd.append('file', file);
      fd.append('field', 'pfp_' + field);
      var container = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Upload');
      container.classList.add('uploading');
      return fetch(BASE_URL + 'api/user/upload-pfp.php', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + API.getToken() },
        body: fd
      }).then(function (r) { return r.json(); }).then(function (d) {
        container.classList.remove('uploading');
        if (d.error) throw new Error(d.error);
        // Open cropper
        pfpData.field = field;
        pfpData.path = d.data.path;
        pfpData.x = 50; pfpData.y = 50; pfpData.s = 1;
        openCropper();
      });
    }

    function openCropper() {
      var img = document.getElementById('cropperImg');
      img.src = BASE_URL + pfpData.path;
      img.style.setProperty('--cs', pfpData.s);
      img.style.setProperty('--cx', pfpData.x + '%');
      img.style.setProperty('--cy', pfpData.y + '%');
      document.getElementById('cropperZoom').value = pfpData.s;
      document.getElementById('cropperModal').classList.add('open');
    }

    function closeCropper() {
      document.getElementById('cropperModal').classList.remove('open');
    }

    // Drag to pan
    var circle = document.getElementById('cropperCircle');
    var cropperImg = document.getElementById('cropperImg');

    circle.addEventListener('mousedown', function (e) {
      if (e.target.closest('input')) return;
      isDragging = true;
      dragStartX = e.clientX;
      dragStartY = e.clientY;
      var cs = pfpData.s * 1;
      startX = pfpData.x;
      startY = pfpData.y;
      circle.style.cursor = 'grabbing';
    });

    document.addEventListener('mousemove', function (e) {
      if (!isDragging) return;
      var dx = e.clientX - dragStartX;
      var dy = e.clientY - dragStartY;
      var circleSize = 260;
      var cs = pfpData.s * 1;
      pfpData.x = Math.max(0, Math.min(100, startX - (dx / (circleSize * cs)) * 100));
      pfpData.y = Math.max(0, Math.min(100, startY - (dy / (circleSize * cs)) * 100));
      cropperImg.style.setProperty('--cs', pfpData.s);
      cropperImg.style.setProperty('--cx', pfpData.x + '%');
      cropperImg.style.setProperty('--cy', pfpData.y + '%');
    });

    document.addEventListener('mouseup', function () {
      if (isDragging) {
        isDragging = false;
        circle.style.cursor = 'grab';
      }
    });

    // Zoom via slider
    document.getElementById('cropperZoom').addEventListener('input', function () {
      pfpData.s = parseFloat(this.value);
      cropperImg.style.setProperty('--cs', pfpData.s);
    });

    // Zoom via scroll wheel
    circle.addEventListener('wheel', function (e) {
      e.preventDefault();
      var step = e.deltaY > 0 ? -0.1 : 0.1;
      pfpData.s = Math.max(1, Math.min(3, pfpData.s + step));
      document.getElementById('cropperZoom').value = pfpData.s;
      cropperImg.style.setProperty('--cs', pfpData.s);
    }, { passive: false });

    // Apply transform
    document.getElementById('cropperSave').addEventListener('click', function () {
      var transform = JSON.stringify({ x: Math.round(pfpData.x), y: Math.round(pfpData.y), s: Math.round(pfpData.s * 100) / 100 });
      // Save transform to DB
      var saveData = {};
      saveData['pfp_' + pfpData.field + '_transform'] = transform;
      API.put(BASE_URL + 'api/user/invitation.php', saveData).catch(function (err) { showAlert(err.message); });
      // Show PFP with transform
      showPFP(pfpData.field, pfpData.path, pfpData.x, pfpData.y, pfpData.s);
      closeCropper();
    });

    document.getElementById('cropperCancel').addEventListener('click', function () {
      closeCropper();
    });

    document.getElementById('cropperClose').addEventListener('click', function () {
      closeCropper();
    });



    function deletePFP(field) {
      return API.del(BASE_URL + 'api/user/upload-pfp.php', { field: 'pfp_' + field }).then(function (d) {
        hidePFP(field);
      });
    }

    function showPFP(field, path, x, y, s) {
      var img = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Img');
      var preview = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Preview');
      var placeholder = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Placeholder');
      img.src = BASE_URL + path;
      img.style.setProperty('--cx', (x || 50) + '%');
      img.style.setProperty('--cy', (y || 50) + '%');
      img.style.setProperty('--cs', s || 1);
      preview.style.display = 'flex';
      placeholder.style.display = 'none';
    }

    function hidePFP(field) {
      var img = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Img');
      var preview = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Preview');
      var placeholder = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Placeholder');
      img.src = '';
      preview.style.display = 'none';
      placeholder.style.display = 'flex';
    }

    ['groom', 'bride'].forEach(function (field) {
      var input = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Input');
      var uploadDiv = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Upload');
      var removeBtn = document.getElementById('pfp' + field.charAt(0).toUpperCase() + field.slice(1) + 'Remove');

      input.addEventListener('change', function () {
        if (this.files && this.files[0]) {
          uploadPFP(field, this.files[0]).catch(function (err) { showAlert(err.message); });
        }
      });

      uploadDiv.addEventListener('click', function (e) {
        if (e.target.closest('.pfp-remove')) return;
        input.click();
      });

      removeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        deletePFP(field).catch(function (err) { showAlert(err.message); });
      });
    });

    // Story char count
    document.getElementById('love_story_text').addEventListener('input', updateStoryCount);
    function updateStoryCount() {
      const len = document.getElementById('love_story_text').value.length;
      document.getElementById('storyCount').textContent = len;
    }

    // Submit
    document.getElementById('invitationForm').addEventListener('submit', async function (e) {
      e.preventDefault();

      const fields = [
        'groom_name', 'bride_name', 'groom_parents', 'bride_parents',
        'wedding_date', 'wedding_time', 'venue_name', 'venue_address',
        'map_embed', 'map_iframe', 'quote_text', 'love_story_title', 'love_story_text',
        'template_id', 'is_published', 'text_color', 'song_path',
        'invitation_type',
      ];

      var data = {};
      fields.forEach(function (f) {
        if (f === 'is_published') {
          data[f] = document.getElementById(f).value == '1';
          return;
        }
        var el = document.getElementById(f);
        if (el) {
          if (el.type === 'checkbox') {
            data[f] = el.checked;
          } else if (f === 'template_id') {
            data[f] = el.value ? parseInt(el.value) : null;
          } else {
            data[f] = el.value;
          }
        }
      });

      try {
        showLoading(true);
        await API.put(BASE_URL + 'api/user/invitation.php', data);
        showAlert('Invitation enregistrée avec succès !', 'success');
      } catch (err) {
        showAlert(err.message);
      }
      showLoading(false);
    });

    function toggleSidebar() {
      document.body.classList.toggle('sidebar-open');
    }
  </script>

  <!-- Preview Modal -->
  <div class="modal-overlay" id="previewModal">
    <div class="modal-content">
      <button class="modal-close" onclick="closePreview()">&times;</button>
      <video id="previewVideo" controls autoplay></video>
      <div class="modal-label" id="previewLabel"></div>
    </div>
  </div>
  <script>
    var previewTemplate = null;
    function openPreview(tmplId) {
      var cards = document.querySelectorAll('.tmpl-card');
      var found = null;
      cards.forEach(function (c) {
        if (c.dataset.id == tmplId) found = c;
      });
      if (!found) return;
      var videoUrl = found.dataset.video;
      if (!videoUrl) { showAlert('Aucune vidéo disponible pour ce style.'); return; }
      previewTemplate = tmplId;
      document.getElementById('previewVideo').src = videoUrl;
      document.getElementById('previewLabel').textContent = found.dataset.name;
      document.getElementById('previewModal').classList.add('open');
    }
    function closePreview() {
      document.getElementById('previewModal').classList.remove('open');
      document.getElementById('previewVideo').pause();
      document.getElementById('previewVideo').src = '';
    }
    document.getElementById('previewModal').addEventListener('click', function (e) {
      if (e.target === this) closePreview();
    });
  </script>
</body>
</html>
