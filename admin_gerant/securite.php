<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Sécurité</title>

  <link rel="stylesheet" href="css/style.css">
</head>
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
?>
<div class="toasts" id="toasts"></div>
<body>

<!-- 🔗 MENU -->
<nav>
  <a href="planning.php">Planning</a>
  <a href="scores.php">Scores</a>
  <a href="metrologues.php">Métrologues</a>
  <a href="projets.php">Projets</a>
  <a href="donnees.php">Données</a>
  <a href="parametres.php">Paramètres</a>
  <a href="securite.php">Sécurité</a>
</nav>

<!-- ── SÉCURITÉ ── -->
<div class="sec">
  <div class="sec-hd">
    <div>
      <div class="sec-title">🔐 Sécurité</div>
      <div class="sec-sub">Profil admin et journal des actions</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">

    <div class="param-card">
      <div class="pc-title">👤 Modifier le profil admin</div>

      <div class="fg">
        <label class="fl">Nom complet</label>
        <input class="fi" id="sec-name" value="<?= htmlspecialchars($userName) ?>"/>
      </div>

      <div class="fg">
        <label class="fl">Email / Identifiant</label>
        <input class="fi" id="sec-email" value="<?= htmlspecialchars($adminRow['name'] ?? '') ?>"/>
      </div>

      <button class="fb prim" onclick="saveProfile()">
        💾 Mettre à jour
      </button>
    </div>

    <div class="param-card">
      <div class="pc-title">🔑 Changer le mot de passe</div>

      <div class="fg">
        <label class="fl">Mot de passe actuel</label>
        <input class="fi" type="password" id="sec-old" placeholder="••••••••"/>
      </div>

      <div class="fg">
        <label class="fl">Nouveau mot de passe</label>
        <input class="fi" type="password" id="sec-new" placeholder="••••••••"/>
      </div>

      <div class="fg">
        <label class="fl">Confirmer</label>
        <input class="fi" type="password" id="sec-conf" placeholder="••••••••"/>
      </div>

      <button class="fb prim" onclick="changePassword()">
        🔑 Changer
      </button>
    </div>

  </div>

  <div class="card">
    <div class="card-hd">
      <div class="card-title">📋 Journal des actions</div>
      <button class="tb-btn" onclick="location.href='api.php?action=export_pointages'">
        📗 Exporter
      </button>
    </div>

    <div class="card-body" id="journal-body">
      <div class="loading">
        <div class="spin"></div>
      </div>
    </div>
  </div>

</div>

<!-- 🔥 JS -->
<script src="js/script.js"></script>

<script>
  // ✅ charger journal seulement ici
  if(document.getElementById('journal-body')){
    loadJournal();
  }
</script>

</body>
</html>