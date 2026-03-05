<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once "../lang_init.php";
require_once "../config/database.php";

$lang_code = $_SESSION['lang'] ?? 'fr';

$token = $_GET['token'] ?? '';
if(empty($token)) die($lang['token_missing'] ?? "Token missing.");

$stmt=$pdo->prepare("SELECT * FROM reset_tokens WHERE token=? AND used=0 AND expires_at>NOW()");
$stmt->execute([$token]);
$tokenData=$stmt->fetch(PDO::FETCH_ASSOC);

$invalid = !$tokenData;
?>

<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title><?= $lang['reset_password'] ?> — CM2E</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="/systeme_pointage/public/assets/css/style.css">
</head>

<body>

<div class="auth-wrapper">

<div class="auth-panel">

<div class="auth-brand">
<div class="auth-brand-line"></div>
<span class="auth-brand-text">
CM2E · <?= $lang['security'] ?>
</span>
</div>

<div class="auth-card">

<?php if($invalid): ?>

<div class="success-box">

<div class="success-icon" style="background:#FEF2F2;border-color:#FECACA;color:#991B1B">
!
</div>

<div class="success-title" style="color:#991B1B">
<?= $lang['invalid_link'] ?>
</div>

<p class="success-subtext">
<?= $lang['invalid_link_text'] ?>
</p>

<a href="forgot_password.php" class="btn-primary" style="display:block;text-align:center">
<?= $lang['new_link'] ?>
</a>

</div>

<?php else: ?>

<h1><?= $lang['new_password'] ?></h1>

<p class="auth-subtitle">
<?= $lang['choose_secure_password'] ?>
</p>

<form action="update_password.php" method="POST">

<input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

<div class="form-group">

<label><?= $lang['new_password'] ?></label>

<div class="pw-wrap">

<input type="password"
name="new_password"
id="pwd1"
placeholder="<?= $lang['password_min'] ?>"
required>

<button type="button" class="toggle-password" onclick="tog('pwd1')">

<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

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

<div id="strength" style="font-size:12px;margin-top:5px;font-weight:600"></div>

</div>

<div class="form-group">

<label><?= $lang['confirm_password'] ?></label>

<div class="pw-wrap">

<input type="password"
name="confirm_password"
id="pwd2"
placeholder="<?= $lang['repeat_password'] ?>"
required>

<button type="button" class="toggle-password" onclick="tog('pwd2')">

<svg xmlns="http://www.w3.org/2000/svg"
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

<div id="match" style="font-size:12px;margin-top:5px;font-weight:600"></div>

</div>

<button type="submit" class="btn-primary">
<?= $lang['validate_password'] ?>
</button>

</form>

<?php endif; ?>

</div>
</div>

<div class="auth-visual">

<div class="auth-visual-shape"></div>

<div class="auth-visual-inner">

<div class="auth-logo-box">
<img src="/systeme_pointage/public/assets/img/logocm2e.jpg" alt="CM2E">
</div>

<div class="auth-tagline">
<?= $lang['account_security'] ?>
</div>

<div class="auth-tagline-sub">
<?= $lang['security_tagline'] ?>
</div>

</div>
</div>

</div>

<script>

function tog(id){
const i=document.getElementById(id);
i.type=i.type==='password'?'text':'password';
}

const p1=document.getElementById('pwd1'),
p2=document.getElementById('pwd2');

if(p1){
p1.addEventListener('input',()=>{
const v=p1.value;
const s=document.getElementById('strength');

if(!v){s.textContent='';return;}

if(v.length<6){
s.textContent='Weak';
s.style.color='#991B1B';
}
else if(v.length<10){
s.textContent='Medium';
s.style.color='#B45309';
}
else{
s.textContent='Strong ✓';
s.style.color='#0B7B5B';
}
});
}

if(p2){
p2.addEventListener('input',()=>{
const m=document.getElementById('match');

if(!p2.value){
m.textContent='';
return;
}

if(p2.value!==p1.value){
m.textContent='Not matching';
m.style.color='#991B1B';
}
else{
m.textContent='Matching ✓';
m.style.color='#0B7B5B';
}
});
}

</script>

</body>
</html>