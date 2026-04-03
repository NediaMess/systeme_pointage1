<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Scores</title>

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

<!-- ── SCORES ── -->
<div class="sec">
  <div class="sec-hd">
    <div>
      <div class="sec-title">🏆 Scores & Classement</div>
      <div class="sec-sub">
        Trimestre <?= ceil(date('n')/3) ?> — <?= date('Y') ?>
      </div>
    </div>

    <div style="display:flex;gap:8px">
      <button class="tb-btn" onclick="openChamp()">🎉 Résultat trimestriel</button>
      <button class="tb-btn prim" onclick="openOv('ov-reset')">🔄 Nouveau trimestre</button>
    </div>
  </div>

  <div class="two-col">

    <div class="card">
      <div class="card-hd">
        <div class="card-title">🥇 Podium</div>
      </div>

      <div class="card-body" id="scores-podium">
        <div class="loading"><div class="spin"></div></div>
      </div>

      <div class="card-body" id="scores-list" style="padding-top:0"></div>
    </div>

    <div class="card">
      <div class="card-hd">
        <div class="card-title">⏱ Compte à rebours</div>
      </div>

      <div class="card-body" style="display:flex;flex-direction:column;gap:14px">

        <div class="timer-cd">
          <div class="timer-lbl">Fin de trimestre dans</div>

          <div class="timer-digs">
            <div class="t-unit">
              <div class="t-num" id="td2">—</div>
              <div class="t-ulbl">Jours</div>
            </div>

            <div class="t-sep">:</div>

            <div class="t-unit">
              <div class="t-num" id="th2">—</div>
              <div class="t-ulbl">H</div>
            </div>

            <div class="t-sep">:</div>

            <div class="t-unit">
              <div class="t-num" id="tm2">—</div>
              <div class="t-ulbl">Min</div>
            </div>
          </div>
        </div>

        <div>
          <div style="display:flex;justify-content:space-between;font-size:11.5px;color:var(--txt2);margin-bottom:4px">
            <span>Progression</span>
            <span style="color:var(--red);font-weight:700">68%</span>
          </div>

          <div class="pbar-bg">
            <div class="pbar-fill" style="width:68%"></div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<!-- 🔥 JS -->
<script src="js/script.js"></script>

<script>
  // ✅ charger les scores uniquement ici
  if(document.getElementById('scores-podium')){
    loadScores();
  }
</script>
<script>
document.addEventListener("DOMContentLoaded", ()=>{
  loadScores();
});
</script>
</body>
</html>