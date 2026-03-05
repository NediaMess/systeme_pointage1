<?php
if(session_status() === PHP_SESSION_NONE){ session_start(); }
require_once "../lang_init.php";
$lang_code = $_SESSION['lang'] ?? 'fr';
?>
<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $lang['login'] ?> — CM2E Pointage</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/systeme_pointage/public/assets/css/style.css">
</head>

<body>

<div class="auth-wrapper">

<!-- LEFT -->
<div class="auth-panel">

<div class="auth-brand">
<div class="auth-brand-line"></div>
<span class="auth-brand-text">
CM2E · <?= $lang['time_tracking_system'] ?>
</span>
</div>

<div class="auth-card">

<?php if(isset($_SESSION['success_message'])): ?>
<div class="alert alert-success">
✓ <?= $_SESSION['success_message']; ?>
</div>
<?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-error">
✕ <?= htmlspecialchars($_SESSION['error']) ?>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<h1><?= $lang['welcome_back'] ?> 👋</h1>

<p class="auth-subtitle">
<?= $lang['login_workspace'] ?>
</p>

<form action="login_process.php" method="POST">

<div class="form-group">
<label><?= $lang['email_address'] ?></label>
<input type="email" name="email" placeholder="votre@cm2e.com" required autocomplete="email">
</div>

<div class="form-group">

<label><?= $lang['password'] ?></label>

<div class="pw-wrap">

<input type="password"
name="password"
id="pwd"
placeholder="••••••••"
required
autocomplete="current-password">

<button type="button" class="toggle-password" onclick="togglePwd()">

<svg id="eye-icon"
xmlns="http://www.w3.org/2000/svg"
width="18"
height="18"
fill="none"
viewBox="0 0 24 24"
stroke="currentColor"
stroke-width="2">

<path stroke-linecap="round" stroke-linejoin="round"
d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>

<path stroke-linecap="round" stroke-linejoin="round"
d="M2.458 12C3.732 7.943 7.523 5
12 5c4.478 0 8.268 2.943
9.542 7-1.274 4.057-5.064
7-9.542 7-4.477 0-8.268-2.943
-9.542-7z"/>

</svg>

</button>

</div>
</div>

<button type="submit" class="btn-primary">
<?= $lang['login_button'] ?>
</button>

</form>

<div class="auth-links">

<a href="forgot_password.php" class="auth-link">
<?= $lang['forgot_password'] ?>
</a>

<a href="register.php" class="auth-link">
<?= $lang['no_account'] ?>
<span class="accent"><?= $lang['create_account'] ?></span>
</a>

</div>

</div>
</div>

<!-- RIGHT -->
<div class="auth-visual">

<div class="auth-visual-shape"></div>

<div class="auth-visual-inner">

<div class="auth-logo-box">
<img src="/systeme_pointage/public/assets/img/logocm2e.jpg" alt="CM2E">
</div>

<div class="auth-tagline">
<?= $lang['company_full'] ?>
</div>

<div class="auth-tagline-sub">
<?= $lang['tracking_words'] ?>
</div>

<div class="auth-stats">

<div class="auth-stat">
<div class="auth-stat-num">100%</div>
<div class="auth-stat-label"><?= $lang['digital'] ?></div>
</div>

<div class="auth-stat">
<div class="auth-stat-num"><?= $lang['real'] ?></div>
<div class="auth-stat-label"><?= $lang['time'] ?></div>
</div>

<div class="auth-stat">
<div class="auth-stat-num"><?= $lang['secure'] ?></div>
<div class="auth-stat-label">& <?= $lang['reliable'] ?></div>
</div>

</div>

</div>
</div>

</div>

<script>

function togglePwd(){

const i=document.getElementById('pwd');
const icon=document.getElementById('eye-icon');

if(i.type==='password'){

i.type='text';

icon.innerHTML='<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';

}else{

i.type='password';

icon.innerHTML='<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';

}

}

</script>

</body>
</html>