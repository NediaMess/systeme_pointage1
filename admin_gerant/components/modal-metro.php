<!-- MODAL PROFIL MÉTROLOGUE -->
<div class="overlay" id="ov-metro">
  <div class="modal modal-lg">
    <div class="modal-hd">
      <div class="ava ava-xl" id="mm-av" style="color:white">KB</div>
      <div><div class="modal-title" id="mm-name">—</div><div class="modal-sub" id="mm-role">—</div></div>
      <button class="modal-x" onclick="closeOv('ov-metro')">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-stats">
        <div class="ms"><div class="ms-val" style="color:var(--gold)" id="mm-score">—</div><div class="ms-key">Score</div></div>
        <div class="ms"><div class="ms-val" style="color:var(--blue)" id="mm-projs">—</div><div class="ms-key">Projets</div></div>
        <div class="ms"><div class="ms-val" style="color:var(--green)" id="mm-pres">—</div><div class="ms-key">ID</div></div>
        <div class="ms"><div class="ms-val" style="color:var(--red)" id="mm-abs">—</div><div class="ms-key">Absences</div></div>
      </div>
      <div class="modal-sec">🔧 Informations</div>
      <div class="fr3" id="mm-infos"></div>
      <div class="modal-sec">📁 Projets &amp; Tâches</div>
      <div id="mm-projets"></div>
      <div class="modal-sec">📅 Absences (trimestre)</div>
      <div style="display:flex;flex-wrap:wrap;gap:5px" id="mm-absences"></div>
    </div>
    <div class="modal-foot">
      <button class="fb danger" id="btn-del-metro" onclick="deleteMetro()">🗑️ Supprimer</button>
      <button class="fb sec" id="btn-edit-metro" onclick="openEditMetro()">✏️ Modifier</button>
      <button class="fb prim" onclick="closeOv('ov-metro')">Fermer</button>
    </div>
  </div>
</div>

<!-- MODAL AJOUTER/MODIFIER MÉTROLOGUE -->
<div class="overlay" id="ov-addmetro">
  <div class="modal modal-md">
    <div class="modal-hd">
      <div><div class="modal-title" id="metro-modal-title">🔧 Nouveau Métrologue</div><div class="modal-sub">Compte + classeur TFT/LED</div></div>
      <button class="modal-x" onclick="closeOv('ov-addmetro')">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="m-id"/>
      <div class="fr2">
        <div class="fg"><label class="fl">Nom complet *</label><input class="fi" id="m-name" placeholder="Prénom Nom"/></div>
        <div class="fg"><label class="fl">Email *</label><input class="fi" type="email" id="m-email" placeholder="prenom.nom@cm2e.tn"/></div>
      </div>
      <div class="fr3">
        <div class="fg"><label class="fl">Téléphone</label><input class="fi" id="m-tel" placeholder="+216 XX XXX XXX"/></div>
        <div class="fg"><label class="fl">Niveau</label><select class="fi" id="m-niveau"><option>Junior</option><option>Intermédiaire</option><option>Senior</option><option>Expert</option></select></div>
        <div class="fg"><label class="fl">Spécialité</label><select class="fi" id="m-spec"><option>Calibration</option><option>Vérification</option><option>Maintenance</option><option>Audit</option></select></div>
      </div>
      <div class="fr2">
        <div class="fg"><label class="fl">Poste / Fonction</label><input class="fi" id="m-poste" placeholder="Métrologie industrielle"/></div>
        <div class="fg">
          <label class="fl">N° Classeur (TFT/LED) *</label>
          <select class="fi" id="m-classeur">
            <option value="">— Sélectionner —</option>
            <option>1</option><option>2</option><option>3</option><option>4</option>
            <option>5</option><option>6</option><option>7</option><option>8</option>
          </select>
          <div style="font-size:10.5px;color:var(--txt3);margin-top:3px">Identifie ce métrologue sur l'écran TFT et active les LEDs</div>
        </div>
      </div>
      <div class="fg" id="pass-field">
        <label class="fl">Mot de passe initial *</label>
        <input class="fi" type="password" id="m-pass" placeholder="••••••••"/>
      </div>
    </div>
    <div class="modal-foot">
      <button class="fb sec" onclick="closeOv('ov-addmetro')">Annuler</button>
      <button class="fb prim" onclick="saveMetro()">✓ Enregistrer</button>
    </div>
  </div>
</div>