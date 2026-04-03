<?php include 'components/modal-systeme.php'; ?>
<div class="overlay" id="ov-restart"> 
    <div class="conf-card"> <div class="conf-icon">🔄</div> 
    <div class="conf-title">Redémarrer le système ?</div> <div class="conf-desc">Le système sera indisponible ~30 secondes. La BDD reste intacte.</div> <div class="conf-warn">⚠️ Toutes les sessions actives seront interrompues.</div> <div class="conf-btns"> <button class="fb sec" onclick="closeOv('ov-restart')">Annuler</button> <button class="fb danger" onclick="confirmRestart()">🔄 Redémarrer</button>
 </div> 
</div> 
</div>