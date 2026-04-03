<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Données</title>

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

<!-- ── DONNÉES ── -->
<div class="sec">
  <div class="sec-hd">
    <div>
      <div class="sec-title">💾 Gestion des Données</div>
      <div class="sec-sub">Export, sauvegarde et nettoyage</div>
    </div>
  </div>

  <div class="exp-cards">

    <div class="exp-card">
      <div class="exp-ico">📊</div>
      <div class="exp-title">Exporter les pointages</div>
      <div class="exp-desc">CSV complet des présences/absences</div>
      <button class="fb prim" onclick="location.href='api.php?action=export_pointages'">
        📗 Télécharger CSV
      </button>
    </div>

    <div class="exp-card">
      <div class="exp-ico">📁</div>
      <div class="exp-title">Exporter les projets</div>
      <div class="exp-desc">CSV : nom projet, métrologue, tâches, dates</div>
      <button class="fb prim" onclick="location.href='api.php?action=export_projets'">
        📗 Télécharger CSV
      </button>
    </div>

    <div class="exp-card">
      <div class="exp-ico">💾</div>
      <div class="exp-title">Backup complet (JSON)</div>
      <div class="exp-desc">Sauvegarde totale de la base de données</div>
      <button class="fb prim" onclick="location.href='api.php?action=backup'">
        ⬇️ Télécharger
      </button>
    </div>

  </div>

  <div class="card">
    <div class="card-hd">
      <div class="card-title">🗑️ Suppression des anciens enregistrements</div>
    </div>

    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        <div>
          <div class="fl">Supprimer les pointages antérieurs à</div>

          <div style="display:flex;gap:8px;align-items:center">
            <input class="fi" type="date" id="delete-before" style="flex:1"/>

            <button class="fb danger" onclick="confirmDeleteOld()">
              🗑️ Supprimer
            </button>
          </div>

          <div style="font-size:11px;color:var(--txt3);margin-top:6px">
            ⚠️ Un CSV sera généré automatiquement avant suppression
          </div>
        </div>

      </div>
    </div>
  </div>

</div>

<!-- 🔥 JS -->
<script src="js/script.js"></script>

</body>
</html>