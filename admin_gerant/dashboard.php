<?php
session_start();
require_once "../config/database.php";
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php"); exit();
}
// Stats
$total_users    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='metrologue'")->fetchColumn();
$total_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$en_cours       = $pdo->query("SELECT COUNT(*) FROM projects WHERE statut='en_cours'")->fetchColumn();
$termines       = $pdo->query("SELECT COUNT(*) FROM projects WHERE statut='Terminé'")->fetchColumn();
// Recent projects
$recents = $pdo->query("SELECT p.*, u.nom, u.prenom FROM projects p JOIN users u ON p.user_id=u.id ORDER BY p.date_creation DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin — CM2E</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/systeme_pointage/public/assets/css/style.css">
</head>
<body>
<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-accent"></div>
    <div class="admin-logo" style="padding:18px 20px 14px;border-bottom:1px solid var(--border)">
      <img src="/systeme_pointage/public/assets/img/logocm2e.jpg" alt="CM2E">
    </div>
    <nav class="admin-nav">
      <div class="nav-section-label" style="padding:14px 12px 6px;font-size:10px;font-weight:700;color:var(--text-3);letter-spacing:1.8px;text-transform:uppercase">Administration</div>
      <a href="dashboard.php" class="active"><span>⊞</span> Tableau de bord</a>
      <a href="create_project.php"><span>＋</span> Créer un projet</a>
    </nav>
    <div class="sidebar-footer">
      <a href="../auth/logout.php"><span>⏻</span> Déconnexion</a>
    </div>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <div class="admin-header">
      <h2>⚙ Panel Administrateur</h2>
      <span class="admin-badge">Admin</span>
    </div>

    <!-- Stats -->
    <div class="stats-row" style="margin-bottom:28px">
      <div class="stat-chip animate-in">
        <div class="stat-icon blue">👥</div>
        <div class="stat-info"><span>Métrologues</span><strong><?= $total_users ?></strong></div>
      </div>
      <div class="stat-chip animate-in">
        <div class="stat-icon orange">📁</div>
        <div class="stat-info"><span>Total projets</span><strong><?= $total_projects ?></strong></div>
      </div>
      <div class="stat-chip animate-in">
        <div class="stat-icon red">▶</div>
        <div class="stat-info"><span>En cours</span><strong><?= $en_cours ?></strong></div>
      </div>
      <div class="stat-chip animate-in">
        <div class="stat-icon green">✓</div>
        <div class="stat-info"><span>Terminés</span><strong><?= $termines ?></strong></div>
      </div>
    </div>

    <!-- Recent projects table -->
    <div class="page-header animate-in" style="margin-bottom:18px">
      <div><h1>Projets récents</h1><div class="page-subtitle">Dernières créations de projets</div></div>
      <a href="create_project.php" class="btn-primary" style="width:auto;padding:10px 20px;font-size:13px">+ Nouveau projet</a>
    </div>

    <div class="animate-in">
    <table class="projects-table">
      <thead>
        <tr>
          <th>#</th><th>Projet</th><th>Métrologue</th><th>Date création</th><th>Temps estimé</th><th>Statut</th>
        </tr>
      </thead>
      <tbody>
      <?php if($recents): foreach($recents as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td class="proj-name-cell"><?= htmlspecialchars($p['nom_projet']) ?></td>
          <td><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></td>
          <td><?= date('d/m/Y', strtotime($p['date_creation'])) ?></td>
          <td><?php
            $min=$p['estimated_time'];
            $h=floor($min/60); $m=$min%60;
            echo $h>0 ? "{$h}h {$m}min" : "{$m}min";
          ?></td>
          <td>
            <?php if($p['statut']==='en_attente'): ?>
              <span class="badge badge-waiting"><span class="badge-dot"></span> En attente</span>
            <?php elseif($p['statut']==='en_cours'): ?>
              <span class="badge badge-active"><span class="badge-dot"></span> En cours</span>
            <?php elseif($p['statut']==='Terminé'): ?>
              <span class="badge badge-done"><span class="badge-dot"></span> Terminé</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="6"><div class="empty-state"><div class="empty-state-icon">📂</div><p>Aucun projet.</p></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </main>
</div>
</body>
</html>
