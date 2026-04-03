<?php
session_start();
require_once "../config/database.php";

// Vérif auth — role admin
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Infos admin connecté
$stmt = $pdo->prepare("SELECT CONCAT(prenom,' ',nom) AS name FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$adminRow = $stmt->fetch(PDO::FETCH_ASSOC);
$userName = $adminRow['name'] ?? ($_SESSION['user_name'] ?? 'Admin');
$parts    = explode(' ', $userName);
$userIni  = strtoupper(substr($parts[0], 0, 1) . substr($parts[1] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CM2E — Administration</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>

 <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="toasts" id="toasts"></div>
<div class="app">

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">CM<br>2E</div>
    <div>
      <div class="sb-brand-name">CM2E Admin</div>
      <div class="sb-brand-sub">Centre de Métrologie</div>
    </div>
  </div>
  <div class="sb-user">
    <div class="sb-av"><?= $userIni ?></div>
    <div>
      <div class="sb-uname"><?= htmlspecialchars($userName) ?></div>
      <div class="sb-urole">Gérant / Administrateur</div>
    </div>
    <div class="sb-dot"></div>
  </div>
<nav class="sb-nav">

  <div class="sb-section">
    <div class="sb-label">Vue générale</div>

    <a href="dashboard.php" class="nav-item active">
      <span class="nav-ico">📊</span>Tableau de bord
      <span class="nav-dot"></span>
    </a>

    <a href="planning.php" class="nav-item">
      <span class="nav-ico">📅</span>Planning semaine
    </a>

    <a href="scores.php" class="nav-item">
      <span class="nav-ico">🏆</span>Scores &amp; Classement
      <span class="nav-badge">T<?= ceil(date('n')/3) ?></span>
    </a>
  </div>

  <div class="sb-section">
    <div class="sb-label">Gestion</div>

    <a href="metrologues.php" class="nav-item">
      <span class="nav-ico">🔧</span>Métrologues
    </a>

    <a href="projets.php" class="nav-item">
      <span class="nav-ico">📁</span>Projets
    </a>

    <a href="donnees.php" class="nav-item">
      <span class="nav-ico">🕐</span>Pointages
    </a>
  </div>

  <div class="sb-section">
    <div class="sb-label">Administration</div>

    <a href="donnees.php" class="nav-item">
      <span class="nav-ico">💾</span>Données
    </a>

    <a href="parametres.php" class="nav-item">
      <span class="nav-ico">⚙️</span>Paramètres
    </a>

    <a href="securite.php" class="nav-item">
      <span class="nav-ico">🔐</span>Sécurité
    </a>

    <a href="systeme.php" class="nav-item">
      <span class="nav-ico">🖥️</span>Système
    </a>
  </div>

</nav>
  <div class="sb-foot">
    <button class="sb-reset" onclick="openOv('ov-reset')">🔄 Démarrer les scores</button>
    <button class="sb-logout" onclick="location.href='../auth/logout.php'">🚪 Déconnexion</button>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:10px">
      <div class="tb-badge">ADMIN</div>
      <div class="tb-title" id="page-title">Tableau de bord</div>
    </div>
    <div class="tb-right">
      <div class="tb-conn">Plateforme métrologue connectée</div>
      <button class="tb-btn" onclick="openChamp()">🏅 Résultat trimestriel</button>
      <button class="tb-btn prim" onclick="openAddProj()">＋ Nouveau projet</button>
    </div>
  </div>

  <!-- ── DASHBOARD ── -->
  <div class="sec active" id="sec-dashboard">
    <div class="stats-row" id="stats-row">
      <div class="sc sc-r"><div class="sc-accent"></div><div class="sc-ico">📁</div><div class="sc-val" id="st-projets">—</div><div class="sc-lbl">Projets actifs</div></div>
      <div class="sc sc-g"><div class="sc-accent"></div><div class="sc-ico">🔧</div><div class="sc-val" id="st-metros">—</div><div class="sc-lbl">Métrologues actifs</div></div>
      <div class="sc sc-o"><div class="sc-accent"></div><div class="sc-ico">✅</div><div class="sc-val" id="st-taches">—</div><div class="sc-lbl">Tâches terminées</div></div>
    </div>
    <div class="two-col">
  <!-- Carte gauche : Top 3 -->
  <div class="card">
    <div class="card-hd"><div class="card-title">🏆 Top 3 — Trimestre en cours</div></div>
    <div class="card-body" id="dash-podium">
      <div id="podium-content" style="display:none"></div>
      <div id="podium-timer" style="text-align:center;padding:20px">
        <div style="font-size:13px;color:var(--txt2);margin-bottom:10px">Résultat disponible dans</div>
        <div class="timer-digs">
          <div class="t-unit"><div class="t-num" id="td3">—</div><div class="t-ulbl">Jours</div></div>
          <div class="t-sep">:</div>
          <div class="t-unit"><div class="t-num" id="th3">—</div><div class="t-ulbl">H</div></div>
          <div class="t-sep">:</div>
          <div class="t-unit"><div class="t-num" id="tm3">—</div><div class="t-ulbl">Min</div></div>
        </div>
      </div>
    </div>
  </div>
  <!-- Carte droite : Fin de trimestre -->
  <div class="card">
    <div class="card-hd"><div class="card-title">⏱ Fin de trimestre</div></div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
      <div>
        <div style="display:flex;justify-content:space-between;font-size:11.5px;color:var(--txt2);margin-bottom:4px">
          <span>Trimestre <?= ceil(date('n')/3) ?> — <?= date('Y') ?></span>
          <span style="color:var(--red);font-weight:700" id="trim-pct">68%</span>
        </div>
        <div class="pbar-bg"><div class="pbar-fill" id="trim-bar" style="width:68%"></div></div>
      </div>
      <div style="background:var(--ybg);border:1px solid rgba(217,119,6,.2);border-radius:8px;padding:10px;font-size:12px" id="leader-badge">🏅 Chargement...</div>
      <button class="btn-primary" onclick="openChamp()">
  🎉 Résultat trimestriel
</button>
    </div>
  </div>
</div>
</div><!-- /main -->
</div><!-- /app -->
<script src="js/script.js"></script>
<script>
document.addEventListener("DOMContentLoaded", ()=>{
  loadDashboard();
});
</script>

<?php include 'components/modal-projet.php'; ?>
<?php include 'components/modal-score.php'; ?>

</body>
</html>