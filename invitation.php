<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
setSecurityHeaders();

$slug = isset($_GET['slug']) ? sanitizeString($_GET['slug']) : '';

if (!$slug) {
  http_response_code(404);
  echo '<h1>Invitation introuvable</h1>';
  exit;
}

$db = getDB();
$stmt = $db->prepare(
  'SELECT i.*, t.folder_path AS template_folder, t.name AS template_name, t.text_offset AS tmpl_text_offset, t.text_delay AS tmpl_text_delay, t.prefix_text AS tmpl_prefix_text, t.prefix_hidden AS tmpl_prefix_hidden, t.font_size_rem AS tmpl_font_size_rem, t.prefix_font_size_rem AS tmpl_prefix_font_size_rem, t.date_font_size_rem AS tmpl_date_font_size_rem, t.date_offset AS tmpl_date_offset, t.hero_text_color AS tmpl_hero_text_color, t.video_loop AS tmpl_video_loop, t.has_two_videos AS tmpl_has_two_videos
   FROM invitations i
   LEFT JOIN templates t ON t.id = i.template_id
   WHERE i.slug = ?
   LIMIT 1'
);
$stmt->execute([$slug]);
$inv = $stmt->fetch();

if (!$inv) {
  http_response_code(404);
  echo '<h1>Invitation introuvable</h1>';
  exit;
}

if ($slug === 'demo-wedding') {
  $inv['template_folder'] = 'template1';
  $inv['venue_name'] = 'Alger, Algérie';
  $inv['groom_name'] = 'Amine';
  $inv['bride_name'] = 'Sarah';
  $songDir = __DIR__ . '/songs';
  if (is_dir($songDir)) {
    $mp3s = glob($songDir . '/*.mp3');
    if ($mp3s) {
      $inv['song_path'] = basename($mp3s[array_rand($mp3s)]);
    }
  }
}

$templateFolder = $inv['template_folder'] ? 'templates/' . $inv['template_folder'] : 'template1';
$videoMp4 = $templateFolder . '/video.mp4';
$videoWebm = $templateFolder . '/video.webm';

// Song: try global songs/ folder first, fall back to template folder
$songMp3 = $templateFolder . '/song.mp3';
if ($inv['song_path']) {
  $globalPath = __DIR__ . '/songs/' . $inv['song_path'];
  if (file_exists($globalPath)) {
    $songMp3 = 'songs/' . $inv['song_path'];
  } else {
    $songMp3 = $templateFolder . '/' . $inv['song_path'];
  }
}

// Detect multi-video (numbered files: 1.mp4, 2.mp4 / 1.webm, 2.webm)
$multiVideo = false;
$videoFiles = [];
$hasTwoVideos = $inv['tmpl_has_two_videos'] ?? 0;
$absFolder = realpath(__DIR__ . '/' . $templateFolder);
if ($absFolder && $hasTwoVideos) {
  $numbered = [];
  foreach (['mp4', 'webm'] as $ext) {
    $found = glob($absFolder . '/*.' . $ext);
    if ($found) {
      foreach ($found as $p) {
        $bn = basename($p);
        if (preg_match('/^(\d+)\.' . $ext . '$/', $bn, $m)) {
          $num = (int)$m[1];
          if (!isset($numbered[$num])) $numbered[$num] = [];
          $numbered[$num][$ext] = $templateFolder . '/' . $bn;
        }
      }
    }
  }
  ksort($numbered);
  if (count($numbered) >= 2) {
    $multiVideo = true;
    $videoFiles = array_values($numbered);
  }
}

// Cache busting: append filemtime to video URLs
$cacheBust = function ($path) {
  $abs = __DIR__ . '/' . $path;
  return $path . '?v=' . (file_exists($abs) ? filemtime($abs) : time());
};
if ($multiVideo) {
  foreach ($videoFiles as &$slots) {
    foreach ($slots as $ext => &$p) {
      $p = $cacheBust($p);
    }
  }
  unset($slots, $p);
}
$videoWebmSrc = $cacheBust($videoWebm);
$videoMp4Src = $cacheBust($videoMp4);

$groom = h($inv['groom_name'] ?: 'Prénom');
$bride = h($inv['bride_name'] ?: 'Prénom');
$invitationType = $inv['invitation_type'] ?? 'couple';
$isBac = $invitationType === 'bac';
$isCircumtion = $invitationType === 'circoncision';
$prefixSource = $inv['prefix_text'] ?: $inv['tmpl_prefix_text'];
$prefixText = $prefixSource ?: ($isBac ? '' : ($isCircumtion ? 'Circoncision de' : 'Mariage de'));
$prefixHidden = $inv['prefix_hidden'] ?? $inv['tmpl_prefix_hidden'] ?? 0;
if (!$prefixSource) $prefixHidden = 0;
$heroPrefix = $prefixHidden ? '' : $prefixText;
$heroNames = $isBac || $isCircumtion ? $groom : ($groom . ' &amp; ' . $bride);
$sectionSubtitleStory = $isBac ? 'Mon Parcours' : ($isCircumtion ? 'À Propos' : 'Notre Voyage Ensemble');
$sectionSubtitleMap = $isBac || $isCircumtion ? 'Lieu de la Célébration' : 'Lieu de la Cérémonie';
$sectionSubtitleRsvp = $isBac || $isCircumtion ? 'Confirmez Votre Présence' : 'Veuillez Confirmer Votre Présence';
if ($inv['wedding_date']) {
  $ts = strtotime($inv['wedding_date']);
  $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
  $mois = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
  $date = $jours[(int)date('w', $ts)] . ' ' . date('d', $ts) . ' ' . $mois[(int)date('n', $ts)] . ' ' . date('Y', $ts);
} else {
  $date = 'Date à définir';
}
$time = h($inv['wedding_time'] ?: 'Horaire à définir');
$venueName = h($inv['venue_name'] ?: 'Lieu à définir');
$venueAddress = h($inv['venue_address'] ?? '');
$venueNameRaw = $inv['venue_name'] ?? '';
$venueAddressRaw = $inv['venue_address'] ?? '';
$mapRaw = $inv['map_iframe'] ?? '';
$mapEmbed = '';
$mapLinkRaw = $inv['map_embed'] ?? '';
$mapLink = '';

// Extract src from iframe HTML if user pasted the embed code
if (preg_match('/<iframe\s+src=["\']([^"\']+)["\']/i', $mapRaw, $m)) {
  $mapRaw = $m[1];
}

if ($mapRaw && (str_contains($mapRaw, 'google.com/maps') || str_contains($mapRaw, 'maps.googleapis.com'))) {
  // User provided a map embed URL → use for iframe
  $mapEmbed = $mapRaw;
} elseif ($venueNameRaw) {
  // Auto-generate iframe from venue name
  $q = urlencode(trim($venueNameRaw . ' ' . $venueAddressRaw) . ', Algérie');
  $mapEmbed = "https://www.google.com/maps?q=$q&output=embed";
}
$mapButtonHref = $mapLinkRaw;
$mapEmbed = h($mapEmbed);
$groomParents = h($inv['groom_parents'] ?? '');
$brideParents = h($inv['bride_parents'] ?? '');
$loveStoryTitle = h($inv['love_story_title'] ?: ($isBac ? 'Mon Parcours' : ($isCircumtion ? 'À Propos' : 'Notre Voyage')));
$loveStoryText = h($inv['love_story_text'] ?? '');
$quoteText = h($inv['quote_text'] ?? '');
$guestName = isset($_GET['to']) ? h(sanitizeString($_GET['to'])) : ($isBac ? 'Cher Invité' : 'Cher Invité');
$published = (bool)$inv['is_published'];
$textColor = validateHexColor($inv['text_color'] ?? '#2C2420');
$textOffset = !empty($inv['text_offset']) ? $inv['text_offset'] : ($inv['tmpl_text_offset'] ?? 'center');
$textOffsetPct = null;
if (is_string($textOffset) && str_starts_with($textOffset, 'pct:')) {
  $textOffsetPct = (int)substr($textOffset, 4);
  $textOffset = 'custom';
}
$textDelay = (float)($inv['tmpl_text_delay'] ?? $inv['text_delay'] ?? 1.25);
$fontSizeRem = $inv['font_size_rem'] ?? $inv['tmpl_font_size_rem'] ?? 1.6;
$dateOffset = !empty($inv['date_offset']) ? $inv['date_offset'] : ($inv['tmpl_date_offset'] ?? '');
$dateOffsetPct = null;
if (is_string($dateOffset) && str_starts_with($dateOffset, 'pct:')) {
  $dateOffsetPct = (int)substr($dateOffset, 4);
} elseif (empty($dateOffset)) {
  // Auto position: below text (matches preview: textPct + 15)
  if ($textOffsetPct !== null) {
    $dateOffsetPct = min($textOffsetPct + 15, 95);
  } else {
    $dateOffsetPct = 65; // centered text + 15%
  }
}
$prefixFontSizeRem = $inv['prefix_font_size_rem'] ?? $inv['tmpl_prefix_font_size_rem'] ?? 1.0;
$dateFontSizeRem = $inv['date_font_size_rem'] ?? $inv['tmpl_date_font_size_rem'] ?? 0.9;
$heroTextColor = validateHexColor($inv['hero_text_color'] ?? $inv['tmpl_hero_text_color'] ?? '#FFFFFF');
$videoLoop = (bool)($inv['video_loop'] ?: $inv['tmpl_video_loop']);

// Profile pictures
$pfpGroom = $inv['pfp_groom'] ?? '';
$pfpBride = $inv['pfp_bride'] ?? '';
$pfpGroomTransform = $inv['pfp_groom_transform'] ?? '';
$pfpBrideTransform = $inv['pfp_bride_transform'] ?? '';
$pfpGroomX = 50; $pfpGroomY = 50; $pfpGroomS = 1;
if ($pfpGroomTransform) {
  $gt = json_decode($pfpGroomTransform, true);
  if ($gt) { $pfpGroomX = $gt['x'] ?? 50; $pfpGroomY = $gt['y'] ?? 50; $pfpGroomS = $gt['s'] ?? 1; }
}
$pfpBrideX = 50; $pfpBrideY = 50; $pfpBrideS = 1;
if ($pfpBrideTransform) {
  $bt = json_decode($pfpBrideTransform, true);
  if ($bt) { $pfpBrideX = $bt['x'] ?? 50; $pfpBrideY = $bt['y'] ?? 50; $pfpBrideS = $bt['s'] ?? 1; }
}

// Parse start time from wedding_time (handles "10h00 - 15h00", "10:00", etc.)
$startTime = '10:00';
if (!empty($inv['wedding_time'])) {
  $t = $inv['wedding_time'];
  if (preg_match('/^(\d{1,2})h(\d{2})/', $t, $m)) {
    $startTime = $m[1] . ':' . $m[2];
  } elseif (preg_match('/^(\d{1,2}):(\d{2})/', $t, $m)) {
    $startTime = $m[1] . ':' . $m[2];
  }
}

if (!$published) {
  http_response_code(403);
  echo '<h1>Cette invitation n\'est pas encore publiée.</h1>';
  exit;
}
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $isBac ? 'Célébration de' : ($isCircumtion ? 'Circoncision de' : 'Mariage de') ?> <?= $groom ?><?= $isBac || $isCircumtion ? '' : ' &amp; ' . $bride ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>favicon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Great+Vibes&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    :root {
      --gold: #C9A96E; --gold-light: #E8D5A3;
      --navy: #1E3A5F; --navy-light: #2B6CB0;
      --blue-light: #EBF4FF; --white: #FFFFFF;
      --gray-50: #F8FAFC; --gray-100: #F1F5F9;
      --gray-200: #E2E8F0; --gray-500: #64748B;
      --text-color: <?= $textColor ?>;
      --hero-color: <?= $heroTextColor ?>;
      --shadow: 0 8px 32px rgba(30,58,95,0.10); --radius: 12px;
      --font-heading: 'Playfair Display', serif;
      --font-body: 'Cormorant Garamond', serif;
      --font-script: 'Great Vibes', cursive;
    }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--font-body); background: var(--blue-light);
      color: var(--text-color); overflow-x: hidden; line-height: 1.7;
    }
    a { text-decoration: none; color: inherit; }
    .container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

    .section-title {
      font-family: var(--font-heading);
      font-size: clamp(28px,5vw,48px); font-weight: 600;
      color: var(--text-color); text-align: center; margin-bottom: 8px;
    }
    .section-subtitle {
      font-family: var(--font-script);
      font-size: clamp(20px,3vw,32px); color: var(--gold);
      text-align: center; margin-bottom: 40px;
    }
    .divider {
      width: 80px; height: 2px;
      background: linear-gradient(90deg,transparent,var(--gold),transparent);
      margin: 16px auto 32px;
    }
    .floral-divider {
      text-align: center; font-size: 28px; color: var(--gold);
      margin: 16px 0 32px; letter-spacing: 12px; opacity: 0.6;
    }

    /* HERO */
    #hero {
      position: relative; width: 100%; height: 100vh;
      background: #0F1A2E; overflow: hidden;
      display: flex; align-items: center; justify-content: center;
    }
    .hero-vid {
      position: absolute; top:0; left:0;
      width: 100%; height: 100%; object-fit: cover;
      transition: opacity 1s ease;
    }
    .hero-loader {
      position: absolute; top:50%; left:50%;
      width: 50px; height: 50px;
      margin: -25px 0 0 -25px;
      border: 3px solid rgba(201,169,110,0.2);
      border-top-color: var(--gold);
      border-radius: 50%;
      z-index: 1;
      animation: spin 0.8s linear infinite;
      transition: opacity 0.5s ease;
    }
    .hero-loader.hidden { opacity: 0; pointer-events: none; }
    @keyframes spin { to { transform: rotate(360deg); } }
    #hero-overlay {
      position: absolute; top:0; left:0;
      width: 100%; height: 100%; background: transparent;
      z-index: 2; cursor: pointer;
    }
    #hero-overlay .hero-text {
      position: absolute; left: 50%; transform: translateX(-50%);
      text-align: center; width: 100%; padding: 0 24px;
      pointer-events: none;
    }
    #hero-overlay .hero-text.centered { top: 50%; transform: translateX(-50%) translateY(-50%); }
    #hero-overlay .hero-date {
      font-family: 'Cormorant Garamond', serif;
      font-size: var(--hero-date-size, 0.9rem); color: var(--hero-color); font-style: italic; text-align: center;
      text-shadow: 0 2px 12px rgba(0,0,0,0.8);
      opacity: 0; visibility: hidden; white-space: nowrap;
    }
    #hero-overlay > .hero-date {
      position: absolute; left: 50%; transform: translateX(-50%);
      pointer-events: none; padding: 2px 12px; border-radius: 4px;
    }
    #hero-overlay.offset-top .hero-text { top: 80px; }
    #hero-overlay.offset-bottom .hero-text { bottom: 80px; top: auto; }
    #hero-overlay.offset-bottom-third .hero-text { bottom: 22%; top: auto; transform: translateX(-50%) translateY(-10rem); }
    #hero-overlay.offset-bac .hero-text { top: 8rem; }
    #hero-overlay .script,
    #hero-overlay h1,
    #hero-overlay .hero-date {
      opacity: 0; visibility: hidden;
    }
    #hero-overlay h1 {
      clip-path: inset(0 50% 0 50%);
      text-shadow: 0 2px 20px rgba(0,0,0,0.5);
    }
    #hero-overlay .script { font-family: var(--font-heading); font-size: var(--hero-prefix-size, clamp(24px,4vw,42px)); color: var(--hero-color); margin-bottom: 8px; }
    #hero-overlay h1 { font-family: var(--font-heading); font-size: var(--hero-font-size, clamp(36px,7vw,80px)); font-weight: 700; color: var(--hero-color); letter-spacing: 2px; margin-bottom: 8px; }
    #hero-overlay .script.revealed { opacity: 1; visibility: visible; animation: fadeUp 0.8s ease forwards; }
    #hero-overlay h1.revealed { opacity: 1; visibility: visible; animation: swipeReveal 1.2s cubic-bezier(0.25,0.46,0.45,0.94) forwards; }
    #hero-overlay .hero-date.revealed { opacity: 1; visibility: visible; animation: fadeUpDate 0.8s ease 0.2s forwards; }
    .click-text {
      position: absolute; bottom: 32px; left: 0; right: 0;
      text-align: center; color: rgba(255,255,255,0.8);
      font-size: 14px; letter-spacing: 2px; text-transform: uppercase;
      animation: pulseBtn 2s ease infinite;
      cursor: pointer;
    }
    .click-text.hidden { display: none; }
    .scroll-down {
      position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%);
      color: rgba(255,255,255,0.6); font-size: 20px;
      animation: bounceDown 2s ease infinite; pointer-events: none;
      opacity: 0.6;
    }
    .scroll-down.hidden { display: none; }
    @keyframes bounceDown {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50% { transform: translateX(-50%) translateY(8px); }
    }
    @keyframes pulseBtn {
      0%, 100% { opacity: 0.5; }
      50% { opacity: 1; }
    }
    @keyframes swipeReveal {
      0% { clip-path: inset(0 50% 0 50%); opacity: 0; }
      100% { clip-path: inset(0 0 0 0); opacity: 1; }
    }
    @keyframes fadeUp {
      0% { opacity:0; transform:translateY(20px); }
      100% { opacity:1; transform:translateY(0); }
    }
    @keyframes fadeUpDate {
      0% { opacity:0; transform: translateX(-50%) translateY(20px); }
      100% { opacity:1; transform: translateX(-50%) translateY(0); }
    }

    /* INVITATION */
    #invitation { padding: 100px 0 80px; background: var(--white); position: relative; }
    #invitation::before {
      content:''; position:absolute; top:0; left:0; right:0;
      height:6px; background:linear-gradient(90deg,transparent,var(--gold),transparent);
    }
    .invite-header { text-align:center; margin-bottom:48px; }
    .invite-header .to {
      font-family:var(--font-script); font-size:clamp(22px,3vw,34px);
      color:var(--gold); margin-bottom:8px;
    }
    .invite-header .guest-name {
      font-family:var(--font-heading); font-size:clamp(28px,5vw,48px);
      font-weight:600; color:var(--text-color); margin-bottom:16px;
    }
    .invite-text {
      max-width:680px; margin:0 auto 40px; text-align:center;
      font-size:clamp(17px,2vw,21px); color:var(--gray-500);
      line-height:1.9; font-style:italic;
    }
    .couple-wrap {
      display:flex; align-items:center; justify-content:center;
      gap:24px; margin:48px 0 32px; flex-wrap:wrap;
    }
    .couple-item { text-align:center; flex:1; min-width:200px; }
    .couple-item .avatar {
      width:140px; height:140px; border-radius:50%;
      margin:0 auto 16px; border:4px solid var(--gold);
      display:flex; align-items:center; justify-content:center;
      font-size:48px; color:var(--white); box-shadow:var(--shadow);
      overflow:hidden;
    }
    .couple-item .avatar img, .couple-item .avatar.has-pfp { width:100%; height:100%; display:block; object-fit:cover; }
    .couple-item .name {
      font-family:var(--font-heading); font-size:clamp(22px,3vw,32px);
      font-weight:600; color:var(--text-color);
    }
    .couple-item .relation {
      font-size:18px; color:var(--gold);
      font-style:italic; font-family:var(--font-script);
    }
    .ampersand {
      font-family:var(--font-script); font-size:clamp(48px,6vw,72px);
      color:var(--gold); line-height:1;
    }
    .wedding-details { text-align:center; margin-top:40px; }
    .wedding-details .date {
      font-family:var(--font-heading); font-size:clamp(24px,4vw,38px);
      font-weight:600; color:var(--text-color);
    }
    .wedding-details .time {
      font-size:clamp(18px,2vw,22px); color:var(--gold);
      margin:8px 0 16px; font-style:italic;
    }
    .wedding-details .venue {
      font-size:clamp(17px,2vw,20px); color:var(--gray-500); max-width:400px; margin:0 auto;
    }
    /* LOVE STORY */
    #story {
      padding:80px 0;
      background:linear-gradient(135deg,#1E3A5F 0%,#2B6CB0 100%);
      color:var(--white); text-align:center;
    }
    #story .section-title { color:var(--gold-light); }
    #story .section-subtitle { color:var(--gold); }
    .story-text {
      max-width:700px; margin:24px auto 0;
      font-size:clamp(17px,2vw,20px);
      color:rgba(255,255,255,0.8); line-height:1.9;
    }
    .quote-card { max-width:700px; margin:0 auto; padding:40px 24px; }
    .quote-card blockquote {
      font-family:var(--font-heading); font-size:clamp(20px,3vw,30px);
      font-style:italic; line-height:1.7; color:var(--gray-100); position:relative;
    }
    .quote-card blockquote::before {
      content:'\201C'; font-family:var(--font-script);
      font-size:80px; color:var(--gold); position:absolute;
      top:-40px; left:-10px; opacity:0.4;
    }
    .quote-card .attribution {
      margin-top:24px; font-size:18px; color:var(--gold); font-style:normal;
    }

    /* COUNTDOWN */
    #countdown { padding:80px 0; background:var(--white); }
    .countdown-grid {
      display:flex; justify-content:center; gap:24px; flex-wrap:wrap; margin-top:32px;
    }
    .countdown-item {
      background:var(--white); border-radius:var(--radius);
      padding:24px 32px; min-width:110px; text-align:center;
      box-shadow:var(--shadow); border-top:3px solid var(--gold);
    }
    .countdown-item .num {
      font-family:var(--font-heading); font-size:clamp(36px,5vw,52px);
      font-weight:700; color:var(--gold); line-height:1;
    }
    .countdown-item .label {
      font-size:14px; text-transform:uppercase; letter-spacing:2px;
      color:var(--gray-500); margin-top:8px;
    }

    /* MAP */
    #map {
      padding:80px 0; background:var(--navy);
    }
    #map .section-title { color:var(--gold-light); }
    #map .section-subtitle { color:var(--gold); }
    .map-container {
      width:100%; height:400px; border-radius:var(--radius);
      overflow:hidden; box-shadow:var(--shadow); margin-top:32px;
    }
    .map-container iframe { width:100%; height:100%; border:0; }
    .btn-venue {
      display:inline-block; margin-top:24px;
      padding:14px 36px; background:var(--gold); color:var(--text-color);
      font-family:var(--font-body); font-size:18px; font-weight:500;
      border-radius:30px; border:none; cursor:pointer;
      transition:all 0.3s ease;
    }
    .btn-venue:hover { background:var(--gold-light); transform:translateY(-2px); }

    /* RSVP */
    #rsvp {
      padding:80px 0;
      background:linear-gradient(135deg,#1E3A5F 0%,#2B6CB0 100%);
    }
    #rsvp .section-title { color:var(--gold-light); }
    #rsvp .section-subtitle { color:var(--gold); }
    .rsvp-form {
      max-width:560px; margin:32px auto 0;
      background:rgba(255,255,255,0.06);
      backdrop-filter:blur(8px); padding:40px;
      border-radius:var(--radius); border:1px solid rgba(255,255,255,0.08);
    }
    .form-group { margin-bottom:20px; }
    .form-group label {
      display:block; font-size:15px; color:var(--gold-light);
      margin-bottom:6px; letter-spacing:1px; text-transform:uppercase;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
      width:100%; padding:14px 16px;
      background:rgba(255,255,255,0.08);
      border:1px solid rgba(255,255,255,0.12);
      border-radius:8px; color:var(--white);
      font-family:var(--font-body); font-size:17px;
      transition:border-color 0.3s ease;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline:none; border-color:var(--gold);
    }
    .form-group input::placeholder,
    .form-group textarea::placeholder { color:rgba(255,255,255,0.3); }
    .form-group select option { color:var(--text-color); }
    .btn-submit {
      width:100%; padding:16px; background:var(--gold);
      color:var(--text-color); font-family:var(--font-body);
      font-size:20px; font-weight:600; border:none;
      border-radius:8px; cursor:pointer;
      transition:all 0.3s ease; letter-spacing:1px;
    }
    .btn-submit:hover { background:var(--gold-light); transform:translateY(-2px); }
    .btn-submit:disabled { opacity:0.6; cursor:not-allowed; transform:none; }
    .rsvp-success { display:none; text-align:center; padding:40px 24px; }
    .rsvp-success.show { display:block; }
    .rsvp-success .icon { font-size:64px; margin-bottom:16px; }
    .rsvp-success h3 {
      font-family:var(--font-heading); font-size:28px;
      color:var(--gold-light); margin-bottom:8px;
    }
    .rsvp-success p { color:rgba(255,255,255,0.7); font-size:18px; }

    /* FOOTER */
    footer {
      padding:48px 24px; background:#0F1A2E;
      text-align:center; color:rgba(255,255,255,0.5);
      font-size:15px; letter-spacing:1px;
    }
    footer .heart { color:var(--gold); display:inline-block; animation:heartbeat 1.4s ease infinite; }
    @keyframes heartbeat { 0%,100%{transform:scale(1)} 50%{transform:scale(1.2)} }
    footer .brand { font-family:var(--font-script); color:var(--gold); font-size:22px; }

    /* REVEAL */
    .reveal { opacity:0; transform:translateY(48px); transition:opacity 0.8s ease, transform 0.8s ease; }
    .reveal.visible { opacity:1; transform:translateY(0); }
    .reveal-scale { opacity:0; transform:scale(0.85); transition:opacity 0.8s ease, transform 0.8s ease; }
    .reveal-scale.visible { opacity:1; transform:scale(1); }

    /* MUTE BUTTON */
    #muteBtn {
      position: fixed; bottom: 24px; right: 24px; z-index: 999;
      width: 48px; height: 48px; border-radius: 50%;
      background: rgba(30,58,95,0.7); color: var(--white);
      font-size: 22px; display: flex; align-items: center; justify-content: center;
      cursor: pointer; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.15);
      backdrop-filter: blur(6px); user-select: none;
    }
    #muteBtn:hover { background: rgba(44,36,32,0.9); transform: scale(1.05); }
    #muteBtn.muted { opacity: 0.5; }

    /* RSVP ERROR */
    .rsvp-error {
      display:none; text-align:center; padding:20px;
      background:rgba(231,76,60,0.15); border:1px solid rgba(231,76,60,0.3);
      border-radius:8px; color:#e74c3c; font-size:16px; margin-top:16px;
    }
    .rsvp-error.show { display:block; }

    /* RESPONSIVE */
    @media (max-width:768px) {
      .couple-wrap { flex-direction:column; gap:16px; }
      .ampersand { font-size:48px; }
      .countdown-grid { gap:12px; }
      .countdown-item { min-width:80px; padding:16px 20px; }
      .countdown-item .num { font-size:32px; }
      .rsvp-form { padding:24px 20px; }
    }
    @media (max-width:480px) {
      .container { padding:0 16px; }
    }
  </style>
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>

  <!-- HERO -->
  <section id="hero">
    <div class="hero-loader" id="heroLoader"></div>
<?php if ($multiVideo): ?>
    <video id="vid1" class="hero-vid" preload="auto" playsinline webkit-playsinline muted>
<?php if (isset($videoFiles[0]['webm'])): ?>
      <source src="<?= $videoFiles[0]['webm'] ?>" type="video/webm" />
<?php endif; ?>
<?php if (isset($videoFiles[0]['mp4'])): ?>
      <source src="<?= $videoFiles[0]['mp4'] ?>" type="video/mp4" />
<?php endif; ?>
    </video>
    <video id="vid2" class="hero-vid" preload="auto" playsinline webkit-playsinline muted <?= $videoLoop ? 'loop' : '' ?> style="opacity:0;">
<?php if (isset($videoFiles[1]['webm'])): ?>
      <source src="<?= $videoFiles[1]['webm'] ?>" type="video/webm" />
<?php endif; ?>
<?php if (isset($videoFiles[1]['mp4'])): ?>
      <source src="<?= $videoFiles[1]['mp4'] ?>" type="video/mp4" />
<?php endif; ?>
    </video>
<?php else: ?>
    <video id="wedding-video" class="hero-vid" preload="auto" playsinline webkit-playsinline muted <?= $videoLoop ? 'loop' : '' ?>>
<?php if (file_exists(__DIR__ . '/' . $videoWebm)): ?>
      <source src="<?= $videoWebmSrc ?>" type="video/webm" />
<?php else: ?>
      <source src="<?= $videoMp4Src ?>" type="video/mp4" />
<?php endif; ?>
    </video>
<?php endif; ?>
    <div id="hero-overlay" class="<?= $textOffset !== 'center' ? ($textOffset === 'custom' ? '' : 'offset-' . $textOffset) : '' ?><?= $isBac || $isCircumtion ? ' offset-bac' : '' ?>"<?php
      $heroStyle = '';
      if ($textOffsetPct !== null) $heroStyle .= '--hero-text-top:' . $textOffsetPct . '%;';
      if ($dateOffsetPct !== null) $heroStyle .= '--hero-date-top:' . $dateOffsetPct . '%;';
      if ($fontSizeRem != 1.6) $heroStyle .= '--hero-font-size:' . $fontSizeRem . 'rem;';
      if ($prefixFontSizeRem != 1.0) $heroStyle .= '--hero-prefix-size:' . $prefixFontSizeRem . 'rem;';
      if ($dateFontSizeRem != 0.9) $heroStyle .= '--hero-date-size:' . $dateFontSizeRem . 'rem;';
      echo $heroStyle ? ' style="' . $heroStyle . '"' : '';
    ?>>
      <div class="hero-text<?= $textOffsetPct === null && $textOffset === 'center' ? ' centered' : '' ?>"<?= $textOffsetPct !== null ? ' style="top:var(--hero-text-top);"' : '' ?>>
        <?php if ($heroPrefix): ?><div class="script"><?= $heroPrefix ?></div><?php endif; ?>
        <h1><?= $heroNames ?></h1>
      </div>
      <div class="hero-date" style="top:var(--hero-date-top);"><?= $date ?></div>
      <div class="click-text">Cliquez pour découvrir l'invitation</div>
      <div class="scroll-down"><i class="fas fa-chevron-down"></i></div>
    </div>
  </section>

  <!-- INVITATION -->
  <section id="invitation">
    <div class="container">
      <div class="invite-header reveal">
        <div class="to"><?= $isBac ? 'À Nos Chers Invités' : ($isCircumtion ? 'À Nos Chers Invités' : 'À Notre Cher Invité') ?></div>
        <div class="guest-name"><?= $guestName ?></div>
        <div class="divider"></div>
      </div>
      <div class="invite-text reveal" style="transition-delay:0.1s;">
<?php if ($isBac): ?>
        C'est avec une immense fierté que nous vous invitons à célébrer l'obtention du baccalauréat de <?= $groom ?>. Après des années de travail et de persévérance, ce jour mérite d'être partagé avec ceux qui comptent. Venez nombreux pour fêter cette réussite ensemble !
<?php elseif ($isCircumtion): ?>
        C'est avec une grande joie que nous vous invitons à célébrer la circoncision de notre cher <?= $groom ?>. Venez partager ce moment de bonheur et de bénédiction avec nous.
<?php else: ?>
        Avec des cœurs remplis de gratitude et de joie, nous vous invitons à partager notre jour spécial
        alors que nous unissons nos vies dans l'amour et commençons notre voyage ensemble en tant que mari et femme.
<?php endif; ?>
      </div>
      <div class="couple-wrap">
<?php if ($isBac || $isCircumtion): ?>
        <div class="couple-item reveal-left">
          <div class="avatar" style="background:linear-gradient(135deg,#e8d5a3,#c9a96e);">
            <?php if ($pfpGroom): ?>
            <img src="<?= BASE_URL . $pfpGroom ?>" alt="<?= h($groom) ?>" style="width:calc(100% * <?= $pfpGroomS ?>);height:calc(100% * <?= $pfpGroomS ?>);max-width:none;object-fit:cover;object-position:<?= $pfpGroomX ?>% <?= $pfpGroomY ?>%;" />
            <?php else: ?>
            <?= mb_substr($groom, 0, 1) ?>
            <?php endif; ?>
          </div>
          <div class="name"><?= $groom ?></div>
<?php if ($isBac): ?>
          <?php if ($bride): ?><div class="relation">Filière : <?= $bride ?></div><?php endif; ?>
<?php elseif ($isCircumtion): ?>
          <div class="relation" style="font-size:15px;">Circoncision</div>
          <?php if ($bride): ?><div class="relation" style="font-size:15px;">Né(e) le : <?= $bride ?></div><?php endif; ?>
<?php endif; ?>
          <?php if ($groomParents): ?>
          <div class="relation" style="font-size:15px;"><?= $groomParents ?></div>
          <?php endif; ?>
        </div>
<?php else: ?>
        <div class="couple-item reveal-left">
          <div class="avatar" style="background:linear-gradient(135deg,#a8b4a0,#7a8a72);">
            <?php if ($pfpGroom): ?>
            <img src="<?= BASE_URL . $pfpGroom ?>" alt="<?= h($groom) ?>" style="width:calc(100% * <?= $pfpGroomS ?>);height:calc(100% * <?= $pfpGroomS ?>);max-width:none;object-fit:cover;object-position:<?= $pfpGroomX ?>% <?= $pfpGroomY ?>%;" />
            <?php else: ?>
            <?= mb_substr($groom, 0, 1) ?>
            <?php endif; ?>
          </div>
          <div class="name"><?= $groom ?></div>
          <?php if ($groomParents): ?>
          <div class="relation"><?= $groomParents ?></div>
          <?php endif; ?>
        </div>
        <div class="ampersand">&amp;</div>
        <div class="couple-item reveal-right">
          <div class="avatar" style="background:linear-gradient(135deg,#e8d5a3,#c9a96e);">
            <?php if ($pfpBride): ?>
            <img src="<?= BASE_URL . $pfpBride ?>" alt="<?= h($bride) ?>" style="width:calc(100% * <?= $pfpBrideS ?>);height:calc(100% * <?= $pfpBrideS ?>);max-width:none;object-fit:cover;object-position:<?= $pfpBrideX ?>% <?= $pfpBrideY ?>%;" />
            <?php else: ?>
            <?= mb_substr($bride, 0, 1) ?>
            <?php endif; ?>
          </div>
          <div class="name"><?= $bride ?></div>
          <?php if ($brideParents): ?>
          <div class="relation"><?= $brideParents ?></div>
          <?php endif; ?>
        </div>
<?php endif; ?>
      </div>
      <div class="wedding-details reveal-scale">

        <div class="date"><?= $date ?></div>
        <?php if ($time): ?>
        <div class="time"><?= $time ?></div>
        <?php endif; ?>
        <div class="venue">
          <?php if ($venueName): ?>
          <?= $venueName ?><br />
          <?php endif; ?>
          <?= $venueAddress ?: '' ?>
        </div>
      </div>
    </div>
  </section>

  <!-- LOVE STORY -->
  <?php if ($loveStoryText || $quoteText): ?>
  <section id="story">
    <div class="container">
      <div class="reveal">
        <div class="section-title"><?= $loveStoryTitle ?></div>
        <div class="section-subtitle"><?= $sectionSubtitleStory ?></div>
      </div>
      <div class="divider" style="background:linear-gradient(90deg,transparent,var(--gold),transparent);"></div>
      <?php if ($loveStoryText): ?>
      <div class="story-text reveal" style="transition-delay:0.1s;">
        <?= nl2br($loveStoryText) ?>
      </div>
      <?php endif; ?>
      <?php if ($quoteText): ?>
      <div class="quote-card reveal" style="transition-delay:0.2s;">
        <blockquote><?= nl2br($quoteText) ?></blockquote>
          <div class="attribution">&mdash; <?= $isBac || $isCircumtion ? $groom : ($groom . ' &amp; ' . $bride) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- COUNTDOWN -->
  <?php if ($inv['wedding_date']): ?>
  <section id="countdown">
    <div class="container">
      <div class="reveal">
        <div class="section-title">Compte à Rebours</div>
        <div class="section-subtitle"><?= $isBac ? "Jusqu'au Grand Jour" : "Jusqu'au Grand Jour" ?></div>
      </div>
      <div class="floral-divider">&#10086; &#10086; &#10086;</div>
      <div class="countdown-grid" id="countdownGrid">
        <div class="countdown-item reveal-scale"><div class="num" id="days">00</div><div class="label">Jours</div></div>
        <div class="countdown-item reveal-scale" style="transition-delay:0.1s;"><div class="num" id="hours">00</div><div class="label">Heures</div></div>
        <div class="countdown-item reveal-scale" style="transition-delay:0.2s;"><div class="num" id="minutes">00</div><div class="label">Minutes</div></div>
        <div class="countdown-item reveal-scale" style="transition-delay:0.3s;"><div class="num" id="seconds">00</div><div class="label">Secondes</div></div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- MAP -->
  <?php if ($venueName && $mapEmbed): ?>
  <section id="map">
    <div class="container">
      <div class="reveal">
        <div class="section-title">Lieu de la Cérémonie</div>
        <div class="section-subtitle"><?= $sectionSubtitleMap ?></div>
      </div>
      <div class="divider" style="background:linear-gradient(90deg,transparent,var(--gold),transparent);"></div>
      <div class="map-container reveal-scale">
        <iframe src="<?= $mapEmbed ?>" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
      <div style="text-align:center;margin-top:24px;">
        <a class="btn-venue" href="<?= h($mapButtonHref) ?>" target="_blank" rel="noopener">
          <?= $venueName ?>
        </a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- RSVP -->
  <section id="rsvp">
    <div class="container">
      <div class="reveal">
        <div class="section-title">Confirmer votre présence</div>
        <div class="section-subtitle"><?= $sectionSubtitleRsvp ?></div>
      </div>
      <div class="divider" style="background:linear-gradient(90deg,transparent,var(--gold),transparent);"></div>

      <div class="rsvp-form" id="rsvpForm">
        <form id="rsvpFormElement" autocomplete="off">
          <div class="form-group">
            <label for="name">Nom Complet</label>
            <input type="text" id="name" name="name" placeholder="Votre nom complet" required />
          </div>
          <div class="form-group">
            <label for="phone">Téléphone</label>
            <input type="tel" id="phone" name="phone" placeholder="+213" />
          </div>
          <div class="form-group">
            <label for="guests">Nombre d'Invités</label>
            <select id="guests" name="guests">
              <option value="1">1 Personne</option>
              <option value="2" selected>2 Personnes</option>
              <option value="3">3 Personnes</option>
              <option value="4">4 Personnes</option>
              <option value="5">5 Personnes</option>
            </select>
          </div>
          <div class="form-group">
            <label for="attendance">Serez-vous Présent ?</label>
            <select id="attendance" name="attendance" required>
              <option value="">Sélectionnez...</option>
              <option value="yes">Oui, Je Serai Présent</option>
              <option value="no">Désolé, Je Ne Peux Pas Venir</option>
            </select>
          </div>
          <div class="form-group">
            <label for="message">Message (Optionnel)</label>
            <textarea id="message" name="message" rows="3" placeholder="Écrivez vos vœux pour nous..."></textarea>
          </div>
          <button type="submit" class="btn-submit" id="rsvpSubmit">Confirmer</button>
          <div class="rsvp-error" id="rsvpError"></div>
        </form>

        <div class="rsvp-success" id="rsvpSuccess">
          <div class="icon" style="font-size:36px;"><i class="fas fa-glass-cheers"></i></div>
          <h3>Merci !</h3>
          <p>Votre confirmation a été reçue. <?= $isBac || $isCircumtion ? 'Nous avons hâte de fêter cela avec vous !' : 'Nous avons hâte de célébrer avec vous !' ?></p>
        </div>
      </div>
    </div>
  </section>

    <!-- AUDIO -->
    <audio id="wedding-song" preload="metadata" loop>
      <source src="<?= $songMp3 ?>" type="audio/mpeg" />
    </audio>

    <!-- MUTE TOGGLE -->
    <div id="muteBtn" title="Lancer la musique"><i class="fas fa-music"></i></div>

    <!-- FOOTER -->
    <footer>
    <div class="container">
      <div style="margin-bottom:12px;">
        Fait avec <span class="heart">&hearts;</span> par <span class="brand">Invito</span>
      </div>
      <div>&copy; 2026 <?= $isBac ? 'Célébration de' : ($isCircumtion ? 'Circoncision de' : 'Mariage de') ?> <?= $groom ?><?= $isBac || $isCircumtion ? '' : ' &amp; ' . $bride ?>. Tous droits réservés.</div>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const isMulti = <?= $multiVideo ? 'true' : 'false' ?>;
      const video = document.getElementById(isMulti ? 'vid1' : 'wedding-video');
      const vid2 = isMulti ? document.getElementById('vid2') : null;
      const audio = document.getElementById('wedding-song');
      const muteBtn = document.getElementById('muteBtn');
      var isMuted = false;
      var started = false;

      // Start buffering video immediately for faster playback on click
      var loader = document.getElementById('heroLoader');

      function hideLoader() {
        if (loader) loader.classList.add('hidden');
      }

      function startBuffering() {
        var els = isMulti
          ? [document.getElementById('vid1'), document.getElementById('vid2')]
          : [document.getElementById('wedding-video')];
        els.forEach(function (v) {
          if (v) { v.preload = 'auto'; v.load(); }
        });
      }
      startBuffering();

      // Show first frame as preview, hide loader when video is ready
      var loadTarget = isMulti
        ? document.getElementById('vid1')
        : document.getElementById('wedding-video');
      if (loadTarget) {
        loadTarget.addEventListener('loadeddata', function () {
          hideLoader();
          // Seek to first frame so it shows as preview image
          loadTarget.currentTime = 0;
        });
        loadTarget.addEventListener('canplay', hideLoader);
      }

      // Also start buffering on hover for even faster response
      document.getElementById('hero').addEventListener('mouseenter', startBuffering);

      function setMuted(muted) {
        isMuted = muted;
        var els = isMulti ? [document.getElementById('vid1'), document.getElementById('vid2')] : [document.getElementById('wedding-video')];
        els.forEach(function (v) { if (v) v.muted = muted; });
        if (audio) audio.muted = muted;
        if (muteBtn) {
          muteBtn.innerHTML = muted ? '<i class=\"fas fa-volume-mute\"></i>' : '<i class=\"fas fa-music\"></i>';
          muteBtn.title = muted ? 'Activer le son' : 'Couper le son';
          muteBtn.classList.toggle('muted', muted);
        }
      }

      function revealText() {
        document.querySelectorAll('#hero-overlay .script, #hero-overlay h1, #hero-overlay .hero-date').forEach(function (el) {
          el.classList.add('revealed');
        });
      }

      function playVideo(el) {
        if (!el) return;
        el.currentTime = 0;
        var p = el.play();
        if (p) p.then(function () { el.muted = false; }).catch(function () {});
      }

      function startEverything() {
        if (started) return;
        started = true;
        hideLoader();

        // Play audio immediately
        if (audio) { audio.muted = false; audio.play().catch(function () {}); }

        // Reveal text after configured delay from video start
        var revealTimer = setTimeout(revealText, <?= $textDelay * 1000 ?>);

        if (isMulti) {
          // Play first video (muted first, then unmute after play starts)
          playVideo(document.getElementById('vid1'));

          // When first video ends, cross-fade to second
          var v1 = document.getElementById('vid1');
          if (v1) v1.onended = function () {
            var v1el = document.getElementById('vid1');
            var v2el = document.getElementById('vid2');
            if (!v2el) return;

            function doCrossFade() {
              if (v1el) v1el.style.opacity = '0';
              v2el.style.opacity = '1';
              playVideo(v2el);
            }

            // Ensure vid2 has loaded a frame before fading in
            if (v2el.readyState >= 2) {
              doCrossFade();
            } else {
              v2el.preload = 'auto';
              v2el.load();
              v2el.onloadeddata = doCrossFade;
            }
          };
        } else {
          // Single video: play with sound
          playVideo(video);
        }

        // Hide click hint and scroll-down
        var ct = document.querySelector('.click-text');
        if (ct) ct.classList.add('hidden');
        var sd = document.querySelector('.scroll-down');
        if (sd) sd.classList.add('hidden');
      }

      // Hero click starts everything
      var heroOverlay = document.getElementById('hero-overlay');
      if (heroOverlay) {
        heroOverlay.addEventListener('click', startEverything);
        heroOverlay.addEventListener('touchend', function (e) {
          if (!started) {
            e.preventDefault();
            startEverything();
          }
        }, { passive: false });
      }

      // Also start on video click directly (for some mobile/in-app browsers)
      if (isMulti) {
        var v1 = document.getElementById('vid1');
        if (v1) v1.addEventListener('click', startEverything);
      } else {
        if (video) video.addEventListener('click', startEverything);
      }

      // Fallback: click-text directly triggers start
      var clickText = document.querySelector('.click-text');
      if (clickText) clickText.addEventListener('click', startEverything);

      // Mute button (toggle)
      if (muteBtn) {
        muteBtn.addEventListener('click', function () {
          if (!started) { startEverything(); return; }
          setMuted(!isMuted);
        });
      }

      // Countdown
      <?php if ($inv['wedding_date']): ?>
      var weddingDate = new Date(<?= jsEscape($inv['wedding_date'] . 'T' . $startTime . ':00' . date('P')) ?>);

      function updateCountdown() {
        var now = new Date();
        var diff = weddingDate - now;

        if (diff <= 0) {
          document.getElementById('days').textContent = '00';
          document.getElementById('hours').textContent = '00';
          document.getElementById('minutes').textContent = '00';
          document.getElementById('seconds').textContent = '00';
          return;
        }

        var days = Math.floor(diff / (1000 * 60 * 60 * 24));
        var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((diff % (1000 * 60)) / 1000);

        document.getElementById('days').textContent = String(days).padStart(2, '0');
        document.getElementById('hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
      }

      updateCountdown();
      setInterval(updateCountdown, 1000);
      <?php endif; ?>

      // Scroll reveal
      var revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
          }
        });
      }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
      revealEls.forEach(function (el) { observer.observe(el); });

      // RSVP Form
      var form = document.getElementById('rsvpFormElement');
      var successDiv = document.getElementById('rsvpSuccess');
      var errorDiv = document.getElementById('rsvpError');
      var submitBtn = document.getElementById('rsvpSubmit');

      if (form) {
        form.addEventListener('submit', async function (e) {
          e.preventDefault();

          var name = document.getElementById('name').value.trim();
          var attendance = document.getElementById('attendance').value;

          if (!name || !attendance) {
            showRsvpError('Veuillez remplir tous les champs obligatoires.');
            return;
          }

          submitBtn.disabled = true;
          submitBtn.textContent = 'Envoi en cours...';
          errorDiv.classList.remove('show');

          try {
            var res = await fetch(BASE_URL + 'api/auth/rsvp.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                slug: <?= jsEscape($slug) ?>,
                name: name,
                phone: document.getElementById('phone').value.trim(),
                guests: parseInt(document.getElementById('guests').value),
                attendance: attendance,
                message: document.getElementById('message').value.trim(),
              }),
            });

            var data = await res.json();

            if (!res.ok) {
              throw new Error(data.error || 'Erreur lors de l\'envoi');
            }

            form.style.display = 'none';
            successDiv.classList.add('show');
          } catch (err) {
            showRsvpError(err.message);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirmer';
          }
        });
      }

      function showRsvpError(msg) {
        errorDiv.textContent = msg;
        errorDiv.classList.add('show');
      }
    });
  </script>
</body>
</html>
