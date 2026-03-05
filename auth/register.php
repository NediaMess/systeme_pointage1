<?php
session_start();
require_once "../lang_init.php";
require_once "../config/database.php";

$lang_code = $_SESSION['lang'] ?? 'fr';

$errors = [];

if($_SERVER['REQUEST_METHOD']==='POST'){

  $nom=$_POST['nom'];
  $prenom=$_POST['prenom'];
  $email=$_POST['email'];
  $password=$_POST['password'];

  if(strlen($password)<6){
      $errors[]=$lang['password_min'];
  }else{

      $s=$pdo->prepare("SELECT id FROM users WHERE email=?");
      $s->execute([$email]);

      if($s->fetch()){
          $errors[]=$lang['email_exists'];
      }else{

          $pdo->prepare("INSERT INTO users(nom,prenom,email,mot_de_passe,role,date_creation)
                         VALUES(?,?,?,?,?,NOW())")
              ->execute([$nom,$prenom,$email,password_hash($password,PASSWORD_DEFAULT),'metrologue']);

          $_SESSION['success_message']=$lang['account_created'];
          header("Location: login.php");
          exit();
      }
  }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title><?= $lang['create_account'] ?> — CM2E</title>

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
CM2E · <?= $lang['new_account'] ?>
</span>
</div>

<div class="auth-card">

<?php if($errors): ?>
<div class="alert alert-error">
✕ <?= htmlspecialchars($errors[0]) ?>
</div>
<?php endif; ?>

<h1><?= $lang['create_account'] ?></h1>

<p class="auth-subtitle">
<?= $lang['join_system'] ?>
</p>

<form method="POST">

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

<div class="form-group" style="margin-bottom:0">
<label><?= $lang['last_name'] ?></label>
<input type="text" name="nom" placeholder="Dupont" required
value="<?= htmlspecialchars($_POST['nom']??'') ?>">
</div>

<div class="form-group" style="margin-bottom:0">
<label><?= $lang['first_name'] ?></label>
<input type="text" name="prenom" placeholder="Jean" required
value="<?= htmlspecialchars($_POST['prenom']??'') ?>">
</div>

</div>

<div class="form-group" style="margin-top:20px">
<label><?= $lang['email'] ?></label>
<input type="email" name="email" placeholder="jean@cm2e.com" required
value="<?= htmlspecialchars($_POST['email']??'') ?>">
</div>

<div class="form-group">

<label><?= $lang['password'] ?></label>

<div class="pw-wrap">

<input type="password" name="password" id="pwd"
placeholder="<?= $lang['password_min'] ?>" required>

<button type="button" class="toggle-password"
onclick="document.getElementById('pwd').type=document.getElementById('pwd').type==='password'?'text':'password'">

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
</div>

<button type="submit" class="btn-primary">
<?= $lang['create_account_button'] ?>
</button>

</form>

<a href="login.php" class="auth-link" style="margin-top:20px">
<?= $lang['already_account'] ?>
<span class="accent"><?= $lang['login'] ?></span>
</a>

</div>
</div>

<div class="auth-visual">

<div class="auth-visual-shape"></div>

<div class="auth-visual-inner">

<div class="auth-logo-box">
<img src="/systeme_pointage/public/assets/img/logocm2e.jpg" alt="CM2E">
</div>

<div class="auth-tagline">
<?= $lang['join_cm2e'] ?>
</div>

<div class="auth-tagline-sub">
<?= $lang['digital_workspace'] ?>
</div>

</div>
</div>

</div>

</body>
</html>