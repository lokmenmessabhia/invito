<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin — Connexion</title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Great+Vibes&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --gold: #C9A96E; --gold-light: #E8D5A3; --navy: #1E3A5F;
      --navy-light: #2B6CB0; --blue: #3B82F6; --white: #FFFFFF;
      --gray-100: #F1F5F9; --gray-200: #E2E8F0; --gray-500: #64748B;
      --gray-700: #334155;
    }
    html { scroll-behavior: smooth; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, sans-serif;
      background: linear-gradient(135deg, #EBF4FF 0%, #F8FAFC 100%);
      color: var(--gray-700);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .login-card {
      background: var(--white);
      border-radius: 16px;
      padding: 40px 36px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 8px 32px rgba(30,58,95,0.08);
      border: 1px solid var(--gray-200);
      text-align: center;
    }
    .login-card .brand {
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      color: var(--navy);
      font-weight: 700;
    }
    .login-card .brand span { color: var(--gold); font-weight: 400; }
    .login-icon { font-size: 36px; color: var(--navy-light); margin: 16px 0 10px; line-height: 1; }
    .login-title { font-size: 22px; font-weight: 600; color: var(--navy); margin-bottom: 4px; }
    .login-sub { font-size: 14px; color: var(--gray-500); margin-bottom: 24px; }
    .error-msg {
      background: #FEE2E2; color: #991B1B; padding: 10px; border-radius: 8px;
      font-size: 14px; margin-bottom: 16px; display: none;
    }
    .error-msg.show { display: block; }
    .form-group { margin-bottom: 16px; text-align: left; }
    .form-group label {
      display: block; font-size: 12px; font-weight: 600; color: var(--gray-500);
      margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .form-group input {
      width: 100%; padding: 11px 14px; border: 1.5px solid var(--gray-200);
      border-radius: 10px; font-size: 15px; font-family: inherit;
      transition: all 0.2s; color: var(--gray-700);
    }
    .form-group input:focus {
      outline: none; border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
    }
    .btn-login {
      width: 100%; padding: 12px; background: var(--navy-light); color: var(--white);
      border: none; border-radius: 10px; font-size: 16px; font-weight: 600;
      font-family: inherit; cursor: pointer; transition: all 0.2s;
    }
    .btn-login:hover { background: var(--navy); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(43,108,176,0.3); }
    .loading-text { font-size: 14px; color: var(--gray-500); margin-top: 12px; display: none; }
    .loading-text.show { display: block; }
    .back-link {
      display: inline-block; margin-top: 20px; color: var(--gray-500);
      font-size: 14px; text-decoration: none; transition: color 0.2s;
    }
    .back-link:hover { color: var(--gold); }
    .back-link i { margin-right: 6px; }
    .pw-toggle { position:relative; }
    .pw-toggle input { padding-right:40px !important; }
    .pw-toggle i { position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#999; font-size:16px; }
    .pw-toggle i:hover { color:var(--gold); }
  </style>
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
  <div class="login-card">
    <div class="brand">Invito<span>.</span></div>
    <div class="login-icon"><i class="fas fa-cog"></i></div>
    <div class="login-title">Espace Administrateur</div>
    <div class="login-sub">Gérez les utilisateurs et les templates</div>

    <div class="error-msg" id="errorMsg"></div>

    <form id="adminLoginForm" autocomplete="off">
      <div class="form-group">
        <label for="username">Identifiant</label>
        <input type="text" id="username" placeholder="Nom d'utilisateur" required autocomplete="username" />
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <div class="pw-toggle">
          <input type="password" id="password" placeholder="Votre mot de passe" required autocomplete="current-password" />
          <i class="fas fa-eye" onclick="togglePw(this,'password')"></i>
        </div>
      </div>
      <button type="submit" class="btn-login">Se connecter</button>
      <div class="loading-text">Connexion en cours...</div>
    </form>

    <a href="<?= BASE_URL ?>" class="back-link"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
  </div>

  <script>
    function togglePw(icon, id) {
      var inp = document.getElementById(id);
      if (inp.type === 'password') { inp.type = 'text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
      else { inp.type = 'password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
    document.getElementById('adminLoginForm').addEventListener('submit', async function (e) {
      e.preventDefault();

      var errorEl = document.getElementById('errorMsg');
      var btn = this.querySelector('.btn-login');
      var loading = this.querySelector('.loading-text');
      var username = document.getElementById('username').value.trim();
      var password = document.getElementById('password').value;

      btn.disabled = true;
      loading.classList.add('show');
      errorEl.classList.remove('show');

      try {
        var res = await fetch(BASE_URL + 'api/auth/login-admin.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username: username, password: password }),
        });

        var data = await res.json();

        if (!res.ok) {
          throw new Error(data.error || 'Échec de la connexion');
        }

        localStorage.setItem('invito_token', data.data.token);
        localStorage.setItem('invito_user', JSON.stringify(data.data.user));
        window.location.href = BASE_URL + 'dashboard/admin/index.php';
      } catch (err) {
        errorEl.textContent = err.message;
        errorEl.classList.add('show');
        btn.disabled = false;
        loading.classList.remove('show');
      }
    });
  </script>
</body>
</html>
