<?php
require_once "../lang_init.php";

if(!isset($_SESSION['user_id'])){
  header("Location: ../auth/login.php");
  exit();
}

$page = $_GET['page'] ?? 'tableau_bord';

$pages_autorisees = [
  'tableau_bord','calendrier_performance','projets','projet_courant',
  'parametres','profil_utilisateur','preferences_affichage',
  'param_calendrier','securite_compte','apropos'
];

if(!in_array($page,$pages_autorisees)){
  $page = 'tableau_bord';
}

$theme  = $_SESSION['theme']  ?? 'light';
$taille = $_SESSION['taille'] ?? 'normal';
$lang_code = $_SESSION['lang'] ?? 'fr';

/* classes body */
$body_classes = [];

if($theme==='dark'){
  $body_classes[]='dark-mode';
}

if($taille==='grand'){
  $body_classes[]='text-large';
}

$body_class = implode(' ',$body_classes);
?>

<!DOCTYPE html>
<html lang="<?= $lang_code ?>">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= $lang['app_name'] ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="/systeme_pointage/public/assets/css/style.css?v=<?= filemtime('../public/assets/css/style.css') ?>">

</head>

<body class="<?= $body_class ?>">

<div class="app-layout">

<!-- SIDEBAR -->
<aside class="sidebar">

<div class="sidebar-accent"></div>

<div class="sidebar-logo">
<img src="/systeme_pointage/public/assets/img/logocm2e.jpg" alt="CM2E">
</div>

<div class="sidebar-profile">

<?php
$photo = $_SESSION['user_photo'] ?? null;
$initiale = strtoupper(substr($_SESSION['user_prenom'] ?? 'U',0,1));
?>

<div class="sidebar-avatar">

<?php if($photo): ?>

<img src="/systeme_pointage/uploads/<?= htmlspecialchars($photo) ?>?v=<?= time() ?>" alt="Photo">

<?php else: ?>

<span><?= $initiale ?></span>

<?php endif; ?>

</div>

<div class="sidebar-profile-info">

<h4><?= htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']) ?></h4>

<small><?= $lang['job_title'] ?></small>

</div>

</div>

<nav class="sidebar-nav">

<div class="nav-section-label"><?= $lang['nav_main'] ?></div>

<a href="?page=tableau_bord" class="<?= $page==='tableau_bord'?'active':'' ?>">
<span class="nav-icon">▦</span>
<?= $lang['dashboard'] ?>
</a>

<a href="?page=calendrier_performance" class="<?= $page==='calendrier_performance'?'active':'' ?>">
<span class="nav-icon">📅</span>
<?= $lang['performance_calendar'] ?>
</a>

<div class="nav-section-label"><?= $lang['nav_projects'] ?></div>

<a href="?page=projets" class="<?= $page==='projets'?'active':'' ?>">
<span class="nav-icon">📁</span>
<?= $lang['projects'] ?>
</a>

<a href="?page=projet_courant" class="<?= $page==='projet_courant'?'active':'' ?>">
<span class="nav-icon">▶</span>
<?= $lang['current_project'] ?>
</a>

<div class="nav-section-label"><?= $lang['nav_account'] ?></div>

<a href="?page=parametres" class="<?= $page==='parametres'?'active':'' ?>">
<span class="nav-icon">⚙</span>
<?= $lang['settings'] ?>
</a>

</nav>

<div class="sidebar-footer">

<a href="../auth/logout.php">
<span>⏻</span> <?= $lang['logout'] ?>
</a>

</div>

</aside>

<!-- MAIN -->
<main class="main-content">

<?php include __DIR__.'/'.$page.'.php'; ?>

</main>

</div>

</body>
</html>