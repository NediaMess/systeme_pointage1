<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Métrologues</title>

 <link rel="stylesheet" href="css/style.css">
</head>
<?php include 'components/modal-metro.php'; ?>
<div class="toasts" id="toasts"></div>
<body>

<!-- 🔗 MENU -->
<nav>
  <a href="planning.php">Planning</a>
  <a href="scores.php">Scores</a>
  <a href="metrologues.php">Métrologues</a>
  <a href="projets.php">Projets</a>
  <a href="donnees.php">Données</a>
</nav>

<!-- ── MÉTROLOGUES ── -->
<div class="sec">
  <div class="sec-hd">
    <div>
      <div class="sec-title">🔧 Gestion des Métrologues</div>
      <div class="sec-sub">Classeur TFT/LED associé à chaque métrologue</div>
    </div>

    <button class="tb-btn prim" onclick="openAddMetro()">＋ Nouveau métrologue</button>
  </div>

  <div class="metro-grid" id="metro-grid">
    <div class="loading" style="grid-column:1/-1">
      <div class="spin"></div>
      Chargement...
    </div>
  </div>
</div>

<!-- 🔥 JS -->
<script src="js/script.js"></script>

<script>
  // ✅ charger les métrologues seulement ici
  if(document.getElementById('metro-grid')){
    loadMetrologues();
  }
</script>

</body>
</html>