<!-- CHAMPION -->
<div class="champ-ov" id="ov-champ">
  <div class="conf-c" id="conf-c"></div>
  <div class="champ-card">
    <div class="champ-crown">👑</div>
    <div class="champ-lbl">🎊 Champion du Trimestre 🎊</div>
    <div class="champ-name" id="champ-name">—</div>
    <div class="champ-sub">meilleur score du trimestre</div>
    <div class="champ-score" id="champ-score">—</div>
    <div class="champ-q">Trimestre <?= ceil(date('n')/3) ?> — <?= date('Y') ?></div>
    <div class="champ-btns">
      <button class="champ-pub" onclick="publishChamp()">📢 Publier sur plateforme métrologue</button>
      <button class="champ-cancel" onclick="closeChamp()">✕ Annuler</button>
    </div>
    <div class="champ-note" id="champ-note"></div>
  </div>
</div>

<!-- MODALS CONFIRMATION -->
<div class="overlay" id="ov-reset">
  <div class="conf-card">
    <div class="conf-icon">🔄</div>
    <div class="conf-title">Démarrer un nouveau trimestre ?</div>
    <div class="conf-desc">Tous les scores seront remis à <strong>0</strong>. L'historique est conservé.</div>
    <div class="conf-warn">⚠️ <strong>Action irréversible.</strong> Validez le résultat du trimestre d'abord.</div>
    <div class="conf-btns">
      <button class="fb sec" onclick="closeOv('ov-reset')">Annuler</button>
      <button class="fb prim" onclick="confirmReset()">🔄 Confirmer</button>
    </div>
  </div>
</div>