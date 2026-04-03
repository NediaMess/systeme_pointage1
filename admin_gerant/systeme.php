<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Système</title>

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
  <a href="securite.php">Sécurité</a>
  <a href="systeme.php">Système</a>
</nav>

<!-- ── SYSTÈME ── -->
<div class="sec">

  <div class="sec-hd">
    <div>
      <div class="sec-title">🖥️ Gestion du système</div>
    </div>
  </div>

  <!-- STATUS -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px">

    <div class="card" style="padding:18px">
      <div style="font-size:22px;margin-bottom:8px">📡</div>
      <div style="font-weight:700;font-size:13px;margin-bottom:4px">Connexion Badge</div>
      <div style="display:flex;align-items:center;gap:6px;font-size:12px">
        <span style="width:8px;height:8px;background:var(--green);border-radius:50%"></span>
        <span style="color:var(--green);font-weight:600">Connecté</span>
      </div>
    </div>

    <div class="card" style="padding:18px">
      <div style="font-size:22px;margin-bottom:8px">📺</div>
      <div style="font-weight:700;font-size:13px;margin-bottom:4px">Écrans TFT</div>
      <div style="display:flex;align-items:center;gap:6px;font-size:12px">
        <span style="width:8px;height:8px;background:var(--green);border-radius:50%"></span>
        <span style="color:var(--green);font-weight:600">Actifs</span>
      </div>
    </div>

    <div class="card" style="padding:18px">
      <div style="font-size:22px;margin-bottom:8px">🗄️</div>
      <div style="font-weight:700;font-size:13px;margin-bottom:4px">Base de données</div>
      <div style="display:flex;align-items:center;gap:6px;font-size:12px">
        <span style="width:8px;height:8px;background:var(--green);border-radius:50%"></span>
        <span style="color:var(--green);font-weight:600">En ligne</span>
      </div>
    </div>

  </div>

  <!-- ACTIONS -->
  <div class="params-grid">

    <div class="param-card">
      <div class="pc-title">🔄 Redémarrage du système</div>

      <div style="font-size:12.5px;color:var(--txt2);margin-bottom:14px;line-height:1.6">
        Réinitialise la connexion avec les dispositifs matériels (badge, TFT, LEDs). La BDD reste intacte.
      </div>

      <div style="background:var(--ybg);border:1px solid rgba(217,119,6,.2);border-radius:6px;padding:9px;font-size:11.5px;color:var(--gold);margin-bottom:14px">
        ⚠️ Sessions métrologues interrompues ~30 secondes.
      </div>

      <button class="fb danger" style="width:100%;justify-content:center" onclick="openOv('ov-restart')">
        🔄 Redémarrer le système
      </button>
    </div>

    <div class="param-card">
      <div class="pc-title">📊 Informations système</div>

      <div style="display:flex;flex-direction:column;gap:7px">

        <div style="display:flex;justify-content:space-between;font-size:12px;padding:6px 0;border-bottom:1px solid var(--bdr)">
          <span style="color:var(--txt2)">Version</span>
          <span style="font-weight:700;font-family:'DM Mono',monospace">v2.1.0</span>
        </div>

        <div style="display:flex;justify-content:space-between;font-size:12px;padding:6px 0;border-bottom:1px solid var(--bdr)">
          <span style="color:var(--txt2)">Date du serveur</span>
          <span style="font-weight:600"><?= date('d/m/Y H:i') ?></span>
        </div>

        <div style="display:flex;justify-content:space-between;font-size:12px;padding:6px 0">
          <span style="color:var(--txt2)">PHP</span>
          <span style="font-weight:700;font-family:'DM Mono',monospace"><?= PHP_VERSION ?></span>
        </div>

      </div>
    </div>

  </div>

</div>

<!-- 🔥 JS -->
<script src="js/script.js"></script>

</body>
</html>