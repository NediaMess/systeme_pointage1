<?php
session_start();
require_once "../config/database.php";
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit();
}
$stmt = $pdo->prepare("SELECT id, nom, prenom FROM users WHERE role='metrologue' ORDER BY nom");
$stmt->execute();
$metrologues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Créer un projet — CM2E Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/systeme_pointage/public/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="sidebar-accent"></div>
    <div class="admin-logo" style="padding:18px 20px 14px;border-bottom:1px solid var(--border)">
      <img src="/systeme_pointage/public/assets/img/logocm2e.jpg" alt="CM2E">
    </div>
    <nav class="admin-nav">
      <div class="nav-section-label" style="padding:14px 12px 6px;font-size:10px;font-weight:700;color:var(--text-3);letter-spacing:1.8px;text-transform:uppercase">Administration</div>
      <a href="dashboard.php"><span>⊞</span> Tableau de bord</a>
      <a href="create_project.php" class="active"><span>＋</span> Créer un projet</a>
    </nav>
    <div class="sidebar-footer">
      <a href="../auth/logout.php"><span>⏻</span> Déconnexion</a>
    </div>
  </aside>

  <main class="admin-main">
    <div class="admin-header">
      <h2>＋ Créer un projet</h2>
      <span class="admin-badge">Admin</span>
    </div>

    <div class="page-header animate-in" style="margin-bottom:24px">
      <div><h1>Nouveau projet</h1><div class="page-subtitle">Assignez un projet à un métrologue</div></div>
      <a href="dashboard.php" class="btn-secondary">← Retour</a>
    </div>

    <div class="settings-section animate-in" style="max-width:640px">
      <div class="settings-section-title"><span class="s-icon">📁</span>Informations du projet</div>
      <form method="POST" action="store_project.php">

        <div class="form-group">
          <label>Nom du projet</label>
          <input type="text" name="nom_projet" placeholder="Ex : Étalonnage capteur thermique" required>
        </div>

        <div class="form-group">
          <label>Assigner à un métrologue</label>
          <select name="user_id" class="metrologue-select" required>
            <option value="">— Sélectionner un métrologue —</option>
            <?php foreach($metrologues as $m): ?>
              <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom'].' '.$m['prenom']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Temps estimé</label>
          <div class="time-inputs">
            <div class="time-group">
              <label>Jours</label>
              <input type="number" name="days" min="0" value="0">
            </div>
            <div class="time-sep" style="font-size:22px;color:var(--text-3);align-self:flex-end;padding-bottom:10px">:</div>
            <div class="time-group">
              <label>Heures</label>
              <input type="number" name="hours" min="0" max="23" value="0">
            </div>
            <div class="time-sep" style="font-size:22px;color:var(--text-3);align-self:flex-end;padding-bottom:10px">:</div>
            <div class="time-group">
              <label>Minutes</label>
              <input type="number" name="minutes" min="0" max="59" value="0">
            </div>
          </div>
        </div>

        <div class="save-btn-row">
          <button type="submit" class="btn-primary" style="width:auto;padding:12px 32px">Créer le projet</button>
        </div>
      </form>
    </div>
  </main>
</div>
</body>
</html>
