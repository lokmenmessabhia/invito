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
  <title>Invito — Invitations de Mariage Digitales</title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Great+Vibes&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --gold: #C9A96E;
      --gold-light: #E8D5A3;
      --navy: #1E3A5F;
      --navy-light: #2B6CB0;
      --blue: #3B82F6;
      --blue-light: #EBF4FF;
      --white: #FFFFFF;
      --gray-100: #F1F5F9;
      --gray-200: #E2E8F0;
      --gray-400: #94A3B8;
      --gray-500: #64748B;
      --gray-600: #475569;
      --gray-700: #334155;
    }
    html { scroll-behavior: smooth; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, sans-serif;
      color: var(--gray-700);
      overflow-x: hidden;
    }
    a { text-decoration: none; color: inherit; }
    .container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

    /* ===== NAV ===== */
    nav {
      position: fixed; top: 0; left: 0; right: 0;
      z-index: 100;
      padding: 14px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(30,58,95,0.97);
      backdrop-filter: blur(12px);
    }
    nav .brand {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      color: var(--white);
      font-weight: 700;
    }
    nav .brand span { color: var(--gold); font-weight: 400; }
    nav .nav-links { display: flex; gap: 24px; align-items: center; }
    nav .nav-links a {
      color: rgba(255,255,255,0.6);
      font-size: 14px;
      font-weight: 500;
      transition: color 0.2s;
    }
    nav .nav-links a:hover { color: var(--gold); }
    .btn-nav-outline {
      padding: 8px 24px;
      border: 1.5px solid var(--gold);
      color: var(--gold) !important;
      border-radius: 30px;
      font-weight: 600;
      font-size: 14px !important;
      transition: all 0.2s;
    }
    .btn-nav-outline:hover { background: var(--gold); color: var(--navy) !important; }

    /* ===== HERO ===== */
    #hero {
      min-height: 100vh;
      background: linear-gradient(135deg, #1E3A5F 0%, #2B6CB0 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 120px 24px 80px;
      position: relative;
      overflow: hidden;
    }
    #hero::before {
      content: '';
      position: absolute;
      top: -50%; left: -50%;
      width: 200%; height: 200%;
      background: radial-gradient(circle at 50% 50%, rgba(201,169,110,0.06) 0%, transparent 60%);
      animation: float 20s ease-in-out infinite;
    }
    @keyframes float {
      0%, 100% { transform: translate(0, 0); }
      50% { transform: translate(-30px, 30px); }
    }
    .hero-content { position: relative; z-index: 1; }
    .hero-content .script {
      font-family: 'Great Vibes', cursive;
      font-size: clamp(26px, 3.5vw, 38px);
      color: var(--gold-light);
      margin-bottom: 8px;
    }
    .hero-content h1 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(44px, 7vw, 80px);
      font-weight: 700;
      color: var(--white);
      letter-spacing: 1px;
      margin-bottom: 12px;
    }
    .hero-content .tagline {
      font-size: clamp(16px, 2vw, 20px);
      color: rgba(255,255,255,0.55);
      font-weight: 400;
      margin-bottom: 36px;
      line-height: 1.6;
    }
    .hero-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .btn-primary {
      padding: 14px 36px;
      background: var(--gold);
      color: var(--navy);
      font-family: inherit;
      font-size: 17px;
      font-weight: 700;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .btn-primary:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,169,110,0.25); }
    .btn-secondary {
      padding: 14px 36px;
      border: 1.5px solid rgba(255,255,255,0.2);
      color: rgba(255,255,255,0.8);
      font-family: inherit;
      font-size: 17px;
      font-weight: 500;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      background: rgba(255,255,255,0.06);
    }
    .btn-secondary:hover { background: rgba(255,255,255,0.1); border-color: var(--gold); color: var(--gold); transform: translateY(-2px); }

    .scroll-indicator {
      position: absolute;
      bottom: 28px;
      left: 50%;
      transform: translateX(-50%);
      color: rgba(255,255,255,0.25);
      font-size: 12px;
      letter-spacing: 2px;
      text-transform: uppercase;
      animation: bounce 2s infinite;
    }
    @keyframes bounce {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50% { transform: translateX(-50%) translateY(8px); }
    }

    /* ===== SECTIONS ===== */
    section { padding: 96px 0; }
    .section-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(28px, 4vw, 40px);
      font-weight: 600;
      color: var(--navy);
      text-align: center;
      margin-bottom: 6px;
    }
    .section-subtitle {
      font-family: 'Great Vibes', cursive;
      font-size: clamp(20px, 2.5vw, 28px);
      color: var(--gold);
      text-align: center;
      margin-bottom: 40px;
    }
    .section-divider {
      width: 60px;
      height: 3px;
      background: var(--gold);
      border-radius: 4px;
      margin: 12px auto 40px;
    }

    /* ===== FEATURES ===== */
    #features { background: var(--blue-light); }
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px;
    }
    .feature-card {
      text-align: center;
      padding: 36px 24px;
      background: var(--white);
      border-radius: 16px;
      box-shadow: var(--shadow-sm, 0 1px 3px rgba(30,58,95,0.06));
      border: 1px solid var(--gray-200);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .feature-card:hover { transform: translateY(-3px); box-shadow: 0 8px 32px rgba(30,58,95,0.1); }
    .feature-card .icon {
      font-size: 36px;
      color: var(--navy-light);
      margin-bottom: 14px;
    }
    .feature-card h3 {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      margin-bottom: 8px;
      color: var(--navy);
    }
    .feature-card p {
      font-size: 15px;
      color: var(--gray-500);
      line-height: 1.7;
    }

    /* ===== HOW IT WORKS ===== */
    #how { background: var(--white); }
    .steps {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 32px;
      position: relative;
    }
    .step {
      text-align: center;
      position: relative;
    }
    .step .num {
      width: 52px; height: 52px;
      border-radius: 50%;
      background: var(--navy);
      color: var(--gold);
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 14px;
    }
    .step h3 {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      margin-bottom: 8px;
      color: var(--navy);
    }
    .step p {
      font-size: 15px;
      color: var(--gray-500);
      line-height: 1.7;
    }

    /* ===== DEMO ===== */
    #demo {
      background: linear-gradient(135deg, #1E3A5F 0%, #2B6CB0 100%);
      text-align: center;
    }
    #demo .section-title { color: var(--gold-light); }
    #demo .section-subtitle { color: var(--gold); }
    #demo p {
      color: rgba(255,255,255,0.6);
      font-size: 17px;
      max-width: 560px;
      margin: 0 auto 28px;
      line-height: 1.7;
    }
    #demo .btn-primary {
      border-radius: 12px;
      padding: 14px 40px;
      font-size: 16px;
    }

    /* ===== LOGIN ===== */
    #login {
      background: var(--blue-light);
      padding: 80px 0;
    }

    .login-box {
      background: var(--white);
      border-radius: 16px;
      padding: 40px 36px;
      width: 100%;
      max-width: 420px;
      margin: 0 auto;
      box-shadow: 0 8px 32px rgba(30,58,95,0.08);
      border: 1px solid var(--gray-200);
      text-align: center;
    }
    .login-box .login-title {
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      color: var(--navy);
      margin-bottom: 4px;
    }
    .login-box .login-icon {
      font-size: 36px;
      color: var(--navy-light);
      margin-bottom: 8px;
    }
    .login-box .login-sub {
      font-size: 15px;
      color: var(--gray-500);
      margin-bottom: 24px;
    }
    .form-group { margin-bottom: 16px; text-align: left; }
    .form-group label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--gray-500);
      margin-bottom: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .form-group input {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid var(--gray-200);
      border-radius: 10px;
      font-size: 15px;
      font-family: inherit;
      transition: all 0.2s;
      background: var(--white);
      color: var(--gray-700);
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
    }
    .pw-toggle { position:relative; }
    .pw-toggle input { padding-right:40px !important; }
    .pw-toggle i { position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#999; font-size:16px; }
    .pw-toggle i:hover { color:var(--gold); }
    .btn-login {
      width: 100%;
      padding: 12px;
      background: var(--navy-light);
      color: var(--white);
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      font-family: inherit;
    }
    .btn-login:hover { background: var(--navy); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(43,108,176,0.3); }
    .btn-login:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .error-msg {
      background: #FEE2E2;
      color: #991B1B;
      padding: 10px;
      border-radius: 8px;
      font-size: 14px;
      margin-bottom: 16px;
      display: none;
    }
    .error-msg.show { display: block; }
    .loading-text { font-size: 14px; color: var(--gray-500); margin-top: 12px; display: none; }
    .loading-text.show { display: block; }

    /* ===== FOOTER ===== */
    footer {
      padding: 40px 24px;
      background: #0F1A2E;
      text-align: center;
      color: rgba(255,255,255,0.35);
      font-size: 14px;
    }
    footer .brand {
      font-family: 'Great Vibes', cursive;
      color: var(--gold);
      font-size: 26px;
      margin-bottom: 8px;
    }
    footer .heart { color: var(--gold); animation: pulse 1.4s ease infinite; display: inline-block; }
    @keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.2)} }
    footer .links { margin: 10px 0; display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
    footer .links a { color: rgba(255,255,255,0.35); font-size: 13px; transition: color 0.2s; }
    footer .links a:hover { color: var(--gold); }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
      nav .nav-links a:not(.btn-nav-outline) { display: none; }
      .hero-content h1 { font-size: 38px; }
      #hero { min-height: 90vh; padding-top: 100px; }
      section { padding: 60px 0; }
      .login-box { padding: 28px 20px; }
    }
    @media (max-width: 480px) {
      nav .nav-links { gap: 10px; }
      .btn-nav-outline { padding: 6px 16px; font-size: 13px !important; }
      .hero-btns .btn-primary,
      .hero-btns .btn-secondary { width: 100%; }
    }
  </style>
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>

  <!-- ===== NAV ===== -->
  <nav>
    <div class="brand">Invito<span>.</span></div>
    <div class="nav-links">
      <a href="#features">Fonctionnalités</a>
      <a href="#how">Comment ça marche</a>
      <a href="#demo">Démo</a>
      <a href="#login" class="btn-nav-outline">Connexion</a>
    </div>
  </nav>

  <!-- ===== HERO ===== -->
  <section id="hero">
    <div class="hero-content">
      <div class="script">Bienvenue sur</div>
      <h1>Invito</h1>
      <p class="tagline">L'art de l'invitation de mariage numérique,<br />élégant et personnalisable.</p>
      <div class="hero-btns">
        <a href="#login" class="btn-primary">Se connecter</a>
        <a href="#demo" class="btn-secondary">Voir une démo</a>
      </div>
    </div>
    <div class="scroll-indicator">Découvrir</div>
  </section>

  <!-- ===== FEATURES ===== -->
  <section id="features">
    <div class="container">
      <div class="section-title">Une invitation, des possibilités infinies</div>
      <div class="section-subtitle">Tout ce dont vous avez besoin</div>
      <div class="section-divider"></div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="icon"><i class="fas fa-video"></i></div>
          <h3>Vidéo d'ouverture</h3>
          <p>Accueillez vos invités avec une vidéo de votre plus beau jour. Son et image pour une entrée en matière inoubliable.</p>
        </div>
        <div class="feature-card">
          <div class="icon"><i class="fas fa-calendar-alt"></i></div>
          <h3>Compte à rebours</h3>
          <p>Un compteur en direct qui défile jusqu'au grand jour. Vos invités suivront l'excitation monter.</p>
        </div>
        <div class="feature-card">
          <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
          <h3>Carte intégrée</h3>
          <p>Indiquez le lieu de la cérémonie avec une carte interactive. Plus d'excuses pour se perdre.</p>
        </div>
        <div class="feature-card">
          <div class="icon"><i class="fas fa-phone-alt"></i></div>
          <h3>RSVP en ligne</h3>
          <p>Recevez les confirmations de présence directement sur votre tableau de bord. Plus de paperasse.</p>
        </div>
        <div class="feature-card">
          <div class="icon"><i class="fas fa-magic"></i></div>
          <h3>Design élégant</h3>
          <p>Des templates au style raffiné, typographie calligraphique et animations fluides.</p>
        </div>
        <div class="feature-card">
          <div class="icon"><i class="fas fa-shield-alt"></i></div>
          <h3>Sécurisé & fiable</h3>
          <p>Authentification par token, données chiffrées. Vos informations sont protégées.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== HOW IT WORKS ===== -->
  <section id="how">
    <div class="container">
      <div class="section-title">Comment ça marche</div>
      <div class="section-subtitle">Trois étapes simples</div>
      <div class="section-divider"></div>
      <div class="steps">
        <div class="step">
          <div class="num">1</div>
          <h3>Créez votre compte</h3>
          <p>L'administrateur crée votre espace couple. Vous recevez vos identifiants par email.</p>
        </div>
        <div class="step">
          <div class="num">2</div>
          <h3>Personnalisez</h3>
          <p>Choisissez un template, ajoutez vos prénoms, la date, le lieu, votre histoire d'amour et bien plus.</p>
        </div>
        <div class="step">
          <div class="num">3</div>
          <h3>Partagez</h3>
          <p>Publiez votre invitation et partagez le lien unique avec tous vos invités. Ils pourront confirmer en un clic.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== DEMO ===== -->
  <section id="demo">
    <div class="container">
      <div class="section-title">Voir une invitation en action</div>
      <div class="section-subtitle">Découvrez le rendu final</div>
      <div class="section-divider" style="background:linear-gradient(90deg,transparent,var(--gold),transparent);"></div>
      <p>Vous voulez voir à quoi ressemble une invitation Invito avant de créer la vôtre ? Jetez un œil à notre démo interactive.</p>
      <a href="<?= BASE_URL ?>invitation.php?slug=demo-wedding" class="btn-primary" target="_blank">Voir la démo &rarr;</a>
    </div>
  </section>

  <!-- ===== LOGIN ===== -->
  <section id="login">
    <div class="container">
      <div class="section-title">Espace personnel</div>
      <div class="section-subtitle">Connectez-vous à votre tableau de bord</div>
      <div class="section-divider"></div>

      <div class="login-box" style="max-width:440px;margin:0 auto;">
        <div class="login-icon"><i class="fas fa-heart"></i></div>
        <div class="login-title">Espace Couple</div>
        <div class="login-sub">Gérez votre invitation personnalisée</div>

        <div class="error-msg" id="errorMsgCouple"></div>

        <form class="loginForm" data-endpoint="<?= BASE_URL ?>api/auth/login-user.php" autocomplete="off">
          <div class="form-group">
            <label for="usernameCouple">Identifiant</label>
            <input type="text" class="login-username" id="usernameCouple" placeholder="Nom d'utilisateur ou email" required autocomplete="username" />
          </div>
          <div class="form-group">
            <label for="passwordCouple">Mot de passe</label>
            <div class="pw-toggle">
              <input type="password" class="login-password" id="passwordCouple" placeholder="Votre mot de passe" required autocomplete="current-password" />
              <i class="fas fa-eye" onclick="togglePw(this,'passwordCouple')"></i>
            </div>
          </div>
          <button type="submit" class="btn-login">Se connecter</button>
          <div class="loading-text" style="margin-top:12px;">Connexion en cours...</div>
        </form>
      </div>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer>
    <div class="container">
      <div class="brand">Invito</div>
      <div class="links">
        <a href="#features">Fonctionnalités</a>
        <a href="#how">Comment ça marche</a>
        <a href="#demo">Démo</a>
        <a href="#login">Connexion</a>
      </div>
      <div style="margin-bottom:8px;">
        Fait avec <span class="heart">&hearts;</span> pour les mariages du monde entier
      </div>
      <div>&copy; 2026 Invito. Tous droits réservés.</div>
    </div>
  </footer>

  <script>
    function togglePw(icon, id) {
      var inp = document.getElementById(id);
      if (inp.type === 'password') { inp.type = 'text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
      else { inp.type = 'password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
    // ===== LOGIN FORM =====
    document.querySelectorAll('.loginForm').forEach(function (form) {
      form.addEventListener('submit', async function (e) {
        e.preventDefault();

        var endpoint = form.dataset.endpoint;
        var errorEl = form.parentElement.querySelector('.error-msg');
        var btn = form.querySelector('.btn-login');
        var loading = form.querySelector('.loading-text');
        var usernameInput = form.querySelector('.login-username');
        var passwordInput = form.querySelector('.login-password');

        btn.disabled = true;
        loading.classList.add('show');
        errorEl.classList.remove('show');

        var username = usernameInput.value.trim();
        var password = passwordInput.value;

        try {
          var res = await fetch(endpoint, {
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

          if (data.data.user.role === 'admin') {
            window.location.href = BASE_URL + 'dashboard/admin/index.php';
          } else {
            window.location.href = BASE_URL + 'dashboard/user/index.php';
          }
        } catch (err) {
          errorEl.textContent = err.message;
          errorEl.classList.add('show');
          btn.disabled = false;
          loading.classList.remove('show');
        }
      });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var target = document.querySelector(this.getAttribute('href'));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

  </script>
</body>
</html>
