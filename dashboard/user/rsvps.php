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
  <title>RSVPs Reçus</title>
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
      <a href="<?= BASE_URL ?>dashboard/user/index.php"><span class="icon"><i class="fas fa-home"></i></span><span>Tableau de Bord</span></a>
      <a href="<?= BASE_URL ?>dashboard/user/edit.php"><span class="icon"><i class="fas fa-edit"></i></span><span>Modifier l'Invitation</span></a>
      <a href="<?= BASE_URL ?>dashboard/user/rsvps.php" class="active"><span class="icon"><i class="fas fa-phone-alt"></i></span><span>RSVPs Reçus</span></a>
    </nav>
    <div class="sidebar-footer"><button class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button></div>
  </aside>

  <main class="main">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div><h1>RSVPs Reçus</h1><p>Liste des réponses de vos invités</p></div>
      <div class="rsvp-badges" style="display:flex;gap:8px;flex-wrap:wrap;">
        <span class="badge badge-yes" id="yesCount">0 Présents</span>
        <span class="badge badge-no" id="noCount">0 Absents</span>
        <span class="badge badge-user" id="totalCount">0 Total</span>
      </div>
    </div>

    <div id="alert" class="alert"></div>

    <div class="card rsvp-table-wrap">
      <div class="card-body">
        <div class="table-wrap">
          <table>
            <thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Invités</th><th>Réponse</th><th>Message</th><th>Date</th></tr></thead>
            <tbody id="rsvpTable"><tr><td colspan="7">Chargement...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="rsvpCards" class="rsvp-cards"></div>

    <style>
      .rsvp-cards { display: none; }

      @media (max-width: 768px) {
        .rsvp-table-wrap { display: none; }
        .rsvp-cards { display: flex; flex-direction: column; gap: 12px; }
        .rsvp-card {
          background: var(--white);
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          padding: 16px;
          border-left: 4px solid var(--gold);
        }
        .rsvp-card .row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 14px; border-bottom: 1px solid var(--gray-200); }
        .rsvp-card .row:last-child { border-bottom: none; }
        .rsvp-card .label { font-weight: 600; color: var(--dark-light); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .rsvp-card .value { text-align: right; color: var(--dark); }
        .rsvp-card .value.msg { text-align: left; margin-top: 4px; font-style: italic; color: var(--dark-light); }
        .rsvp-badges .badge { font-size: 11px; padding: 2px 8px; }
      }
    </style>
  </main>

  <script src="<?= BASE_URL ?>assets/js/dashboard.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', async function () {
      const user = await checkAuth();
      if (!user) return;
      document.querySelector('#userInfo strong').textContent = user.username;

      // Load invitation type for sidebar label
      try {
        const invRes = await API.get(BASE_URL + 'api/user/invitation.php');
        var roleLabel = document.getElementById('userRoleLabel');
        if (roleLabel) roleLabel.textContent = invRes.data.invitation_type === 'bac' ? 'Dipl\u00f4m\u00e9' : (invRes.data.invitation_type === 'circoncision' ? 'Circoncision' : 'Couple');
      } catch (e) {}

      try {
        const res = await API.get(BASE_URL + 'api/user/rsvps.php');
        const rsvps = res.data.rsvps;
        renderRsvps(rsvps);
      } catch (err) {
        showAlert(err.message);
      }

      showLoading(false);
    });

    function renderRsvps(rsvps) {
      const tbody = document.getElementById('rsvpTable');
      const cards = document.getElementById('rsvpCards');

      if (!rsvps || rsvps.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">Aucun RSVP reçu pour le moment.</td></tr>';
        cards.innerHTML = '<div class="rsvp-card" style="text-align:center;color:var(--dark-light);">Aucun RSVP reçu pour le moment.</div>';
        document.getElementById('yesCount').textContent = '0 Présents';
        document.getElementById('noCount').textContent = '0 Absents';
        document.getElementById('totalCount').textContent = '0 Total';
        return;
      }

      let yes = 0, no = 0;
      var rows = [], cardHtml = [];

      rsvps.forEach(function (r) {
        if (r.attendance === 'yes') yes++;
        else no++;

        var badge = r.attendance === 'yes'
          ? '<span class="badge badge-yes">Présent</span>'
          : '<span class="badge badge-no">Absent</span>';
        var date = r.created_at ? new Date(r.created_at).toLocaleString('fr-FR') : '-';
        var msg = r.message ? h(r.message) : '';
        var msgShort = msg ? msg.substring(0, 80) + (msg.length > 80 ? '...' : '') : '-';
        var name = h(r.name);
        var email = r.email ? h(r.email) : '-';
        var phone = r.phone ? h(r.phone) : '-';
        var guests = r.guests;

        rows.push(
          '<tr>' +
            '<td><strong>' + name + '</strong></td>' +
            '<td>' + email + '</td>' +
            '<td>' + phone + '</td>' +
            '<td>' + guests + '</td>' +
            '<td>' + badge + '</td>' +
            '<td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + (msg || '') + '">' + msgShort + '</td>' +
            '<td>' + date + '</td>' +
          '</tr>'
        );

        cardHtml.push(
          '<div class="rsvp-card">' +
            '<div class="row"><span class="label">Nom</span><span class="value"><strong>' + name + '</strong></span></div>' +
            '<div class="row"><span class="label">Email</span><span class="value">' + email + '</span></div>' +
            '<div class="row"><span class="label">Téléphone</span><span class="value">' + phone + '</span></div>' +
            '<div class="row"><span class="label">Invités</span><span class="value">' + guests + '</span></div>' +
            '<div class="row"><span class="label">Réponse</span><span class="value">' + badge + '</span></div>' +
            (msg ? '<div class="row"><span class="label">Message</span><span class="value msg">' + msg + '</span></div>' : '') +
            '<div class="row"><span class="label">Date</span><span class="value">' + date + '</span></div>' +
          '</div>'
        );
      });

      tbody.innerHTML = rows.join('');
      cards.innerHTML = cardHtml.join('');

      document.getElementById('yesCount').textContent = yes + ' Présents';
      document.getElementById('noCount').textContent = no + ' Absents';
      document.getElementById('totalCount').textContent = rsvps.length + ' Total';
    }

    function toggleSidebar() {
      document.body.classList.toggle('sidebar-open');
    }
  </script>
</body>
</html>
