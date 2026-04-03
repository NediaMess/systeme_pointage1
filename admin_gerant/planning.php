<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Planning</title>

  <!-- ton CSS -->
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

<!-- ── PLANNING ── -->
<div class="sec">
  <div class="sec-hd">
    <div>
      <div class="sec-title">📅 Planning Hebdomadaire</div>
      <div class="sec-sub">Suivi des tâches par métrologue</div>
    </div>
  </div>

  <div class="card">
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th style="min-width:155px">Métrologue</th>
            <th class="dc">Lun</th>
            <th class="dc">Mar</th>
            <th class="dc">Mer</th>
            <th class="dc">Jeu</th>
            <th class="dc">Ven</th>
            <th>Score</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody id="plan-full">
          <tr>
            <td colspan="8" class="loading">
              <div class="spin"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- 🔥 JS -->
<script src="js/script.js"></script>

<script>
  // ✅ charger planning seulement si présent
  if(document.getElementById('plan-full')){
    loadPlanning();
  }
</script>

</body>
</html>