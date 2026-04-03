<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Projets</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="toasts" id="toasts"></div>

<!-- 🔗 MENU -->
<nav>
  <a href="planning.php">Planning</a>
  <a href="scores.php">Scores</a>
  <a href="metrologues.php">Métrologues</a>
  <a href="projets.php">Projets</a>
  <a href="donnees.php">Données</a>
</nav>

<!-- ── PROJETS ── -->
<div class="sec">

  <div class="sec-hd">
    <div>
      <div class="sec-title">📁 Gestion des Projets</div>
    </div>

    <button class="tb-btn prim" onclick="openAddProj()">
      ＋ Nouveau projet
    </button>
  </div>

  <!-- ✅ UN SEUL GRID -->
  <div class="proj-grid" id="proj-grid">
    <div class="loading" style="grid-column:1/-1">
      <div class="spin"></div>
      Chargement...
    </div>
  </div>

</div>

<!-- 🔥 JS -->
<script src="js/script.js"></script>

<script>
document.addEventListener("DOMContentLoaded", ()=>{
  loadProjets();
});
</script>

<?php include 'components/modal-projet.php'; ?>

</body>
</html>