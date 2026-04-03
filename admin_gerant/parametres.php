<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Paramètres</title>

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
  <a href="parametres.php">Paramètres</a>
</nav>

<!-- ── PARAMÈTRES ── -->
<div class="sec">
  <div class="sec-hd">
    <div>
      <div class="sec-title">⚙️ Paramètres</div>
      <div class="sec-sub">Horaires et configuration du système</div>
    </div>
  </div>

  <div class="params-grid">

    <div class="param-card">
      <div class="pc-title">🕐 Horaires par métrologue</div>

      <div id="horaires-list">
        <div class="loading">
          <div class="spin"></div>
        </div>
      </div>

      <button class="fb prim" style="margin-top:8px" onclick="saveHoraires()">
        💾 Enregistrer les horaires
      </button>
    </div>

    <div class="param-card">
      <div class="pc-title">🏆 Système de scores</div>

      <div class="fg">
        <label class="fl">Durée du cycle</label>
        <select class="fi">
          <option selected>3 mois (trimestriel)</option>
          <option>6 mois</option>
        </select>
      </div>

      <div class="fr2">
        <div class="fg">
          <label class="fl">Points / tâche complétée</label>
          <input class="fi" type="number" value="50"/>
        </div>

        <div class="fg">
          <label class="fl">Pénalité / absence</label>
          <input class="fi" type="number" value="-20"/>
        </div>
      </div>

      <div class="fr2">
        <div class="fg">
          <label class="fl">Bonus leader</label>
          <input class="fi" type="number" value="30"/>
        </div>

        <div class="fg">
          <label class="fl">Pénalité retard</label>
          <input class="fi" type="number" value="-5"/>
        </div>
      </div>

      <div style="background:var(--sur2);border:1px solid var(--bdr);border-radius:6px;padding:9px;font-size:11.5px;color:var(--txt2);margin-bottom:12px">
        📋 <strong>4 tâches fixes :</strong> Finaliser · Vérifier · Commande · Réception
      </div>

      <button class="fb prim" onclick="toast('ok','✅ Paramètres scores enregistrés')">
        💾 Enregistrer
      </button>
    </div>

  </div>
</div>

<!-- 🔥 JS -->
<script src="js/script.js"></script>

<script>
  // ✅ charger horaires seulement ici
  if(document.getElementById('horaires-list')){
    loadHoraires();
  }
</script>
<script>
document.addEventListener("DOMContentLoaded", ()=>{
  loadHoraires();
});
</script>
</body>
</html>