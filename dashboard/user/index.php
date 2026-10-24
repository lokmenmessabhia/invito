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
  <title>Mon Invitation</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
  <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
  <div id="loading"><div class="spinner"></div> Chargement...</div>

  <aside class="sidebar">
    <div class="brand">Invito<span>.</span></div>
    <div class="user-info" id="userInfo"><strong>Utilisateur</strong><span id="userRoleLabel">Couple</span></div>
    <nav class="sidebar-nav">
      <a href="<?= BASE_URL ?>dashboard/user/index.php" class="active"><span class="icon"><i class="fas fa-home"></i></span><span>Tableau de Bord</span></a>
      <a href="<?= BASE_URL ?>dashboard/user/edit.php"><span class="icon"><i class="fas fa-edit"></i></span><span>Modifier l'Invitation</span></a>
      <a href="<?= BASE_URL ?>dashboard/user/rsvps.php"><span class="icon"><i class="fas fa-phone-alt"></i></span><span>RSVPs Reçus</span></a>
    </nav>
    <div class="sidebar-footer"><button class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button></div>
  </aside>

  <main class="main">
    <div class="page-header">
      <h1>Mon Invitation</h1>
      <p>Bienvenue sur votre tableau de bord</p>
    </div>

    <div id="alert" class="alert"></div>

    <div class="stats-grid" id="invStats">
      <div class="stat-card"><div class="num" id="rsvpCount">0</div><div class="label">RSVPs Reçus</div></div>
      <div class="stat-card"><div class="num" id="guestCount">0</div><div class="label">Invités Confirmés</div></div>
      <div class="stat-card"><div class="num" id="statusBadge">-</div><div class="label">Statut</div></div>
    </div>

    <div class="card">
      <div class="card-header"><h2>Lien de votre Invitation</h2></div>
      <div class="card-body">
        <div class="invite-link-box">
          <input type="text" id="inviteLink" readonly />
          <button class="btn-copy" onclick="copyLink()">Copier</button>
        </div>
        <p style="margin-top:12px;font-size:14px;color:var(--dark-light);">
          Partagez ce lien avec vos invités pour qu'ils puissent voir votre invitation et confirmer leur présence.
        </p>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h2>Aperçu rapide</h2></div>
      <div class="card-body" id="quickPreview">
        <p style="color:var(--dark-light);">Chargement...</p>
      </div>
    </div>
  </main>

  <script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', async function () {
      const user = await checkAuth();
      if (!user) return;

      document.querySelector('#userInfo strong').textContent = user.username;

      try {
        // Load invitation data
        const invRes = await API.get(BASE_URL + 'api/user/invitation.php');
        const inv = invRes.data;

        // Update sidebar label
        var roleLabel = document.getElementById('userRoleLabel');
        if (roleLabel) roleLabel.textContent = inv.invitation_type === 'bac' ? 'Dipl\u00f4m\u00e9' : (inv.invitation_type === 'circoncision' ? 'Circoncision' : 'Couple');

        // Build link
        const link = window.location.origin + BASE_URL + 'invitation.php?slug=' + encodeURIComponent(inv.slug);
        document.getElementById('inviteLink').value = link;

        // Load RSVPs
        const rsvpRes = await API.get(BASE_URL + 'api/user/rsvps.php');
        const rsvps = rsvpRes.data.rsvps;

        const total = rsvps.length;
        const yesCount = rsvps.filter(function (r) { return r.attendance === 'yes'; }).length;
        let totalGuests = 0;
        rsvps.forEach(function (r) {
          if (r.attendance === 'yes') totalGuests += parseInt(r.guests);
        });

        document.getElementById('rsvpCount').textContent = total;
        document.getElementById('guestCount').textContent = totalGuests;

        const pubBadge = document.getElementById('statusBadge');
        if (inv.is_published == 1) {
          pubBadge.textContent = 'Publiée';
          pubBadge.style.color = '#27AE60';
        } else {
          pubBadge.textContent = 'Brouillon';
          pubBadge.style.color = '#E67E22';
        }

        // Quick preview
        const preview = document.getElementById('quickPreview');
        if (inv.groom_name || inv.bride_name) {
          preview.innerHTML = '<p style="font-size:18px;font-weight:600;">' + h(inv.groom_name || '?') + ' & ' + h(inv.bride_name || '?') + '</p>' +
            '<p style="margin-top:8px;color:var(--dark-light);">' + (inv.wedding_date || 'Date non définie') + '</p>' +
            '<p style="color:var(--dark-light);">' + h(inv.venue_name || 'Lieu non défini') + '</p>';
        } else {
          preview.innerHTML = '<p style="color:var(--dark-light);">Complétez votre invitation depuis l\'onglet "Modifier l\'Invitation".</p>';
        }

      } catch (err) {
        showAlert(err.message);
      }

      showLoading(false);
    });

    function copyLink() {
      const input = document.getElementById('inviteLink');
      input.select();
      document.execCommand('copy');
      showAlert('Lien copié !', 'success');
    }

    function toggleSidebar() {
      document.body.classList.toggle('sidebar-open');
    }
  </script>
</body>
</html>
