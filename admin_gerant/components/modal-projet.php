<!-- MODAL AJOUTER PROJET -->
<div class="overlay" id="ov-addproj">
  <div class="modal modal-md">
    <div class="modal-hd">
      <div><div class="modal-title">📁 Nouveau Projet</div><div class="modal-sub">4 tâches assignées automatiquement</div></div>
      <button class="modal-x" onclick="closeOv('ov-addproj')">✕</button>
    </div>
    <div class="modal-body">
      <div class="fg"><label class="fl">Titre du projet *</label><input class="fi" id="p-nom" placeholder="Ex: Étalonnage capteurs pression P-12"/></div>
      <div class="fg"><label class="fl">Métrologue assigné *</label><select class="fi" id="p-metro"><option value="">— Sélectionner —</option></select></div>
      <div class="fr2">
        <div class="fg"><label class="fl">Échéance</label><input class="fi" type="date" id="p-deadline"/></div>
        <div class="fg"><label class="fl">Priorité</label><select class="fi" id="p-prio"><option value="normale">Normale</option><option value="haute">Haute</option><option value="urgente">Urgente</option></select></div>
      </div>
      <div class="fg" style="margin-top:11px"><label class="fl">Description</label><textarea class="fi" id="p-desc" rows="2" placeholder="Détails..." style="resize:vertical"></textarea></div>
    </div>
    <div class="modal-foot">
      <button class="fb sec" onclick="closeOv('ov-addproj')">Annuler</button>
      <button class="fb prim" onclick="saveProj()">✓ Créer le projet</button>
    </div>
  </div>
</div>