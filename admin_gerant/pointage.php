<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Pointages</title>

  <link rel="stylesheet" href="css/style.css">
</head>
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

<!-- ── POINTAGES ── -->
<div class="sec">
  <div class="sec-hd">
    <div>
      <div class="sec-title">🕐 Consultation des Pointages</div>
      <div class="sec-sub">Historique des présences</div>
    </div>

    <div style="display:flex;gap:8px">
      <select class="fi" style="width:160px;padding:6px 10px;font-size:12px" id="filter-metro">
        <option value="">Tous</option>
      </select>

      <input class="fi" type="date" style="width:140px;padding:6px 10px;font-size:12px" id="filter-date"/>

      <button class="tb-btn prim" onclick="loadPointages()">🔍 Filtrer</button>

      <button class="tb-btn" onclick="location.href='api.php?action=export_pointages'">
        📗 Export CSV
      </button>
    </div>
  </div>

  <div class="card">
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Métrologue</th>
            <th>Date</th>
            <th>Arrivée</th>
            <th>Départ</th>
            <th>Durée</th>
            <th>Statut</th>
            <th>Note</th>
          </tr>
        </thead>

        <tbody id="pointage-body">
          <tr>
            <td colspan="7" class="loading">
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
  // ✅ charger les pointages seulement ici
  if(document.getElementById('pointage-body')){
    loadPointages();
  }
</script>
<script>
document.addEventListener("DOMContentLoaded", ()=>{
  loadPointages();
});
</script>
</body>
</html>