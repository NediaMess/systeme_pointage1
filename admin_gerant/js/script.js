  const loaders = {
  metrologues:false,
  projets:false,
  scores:false
  };
async function api(action, data=null, method='GET'){
  const url = 'api.php?action=' + action;

  const opts = {
    method: method || (data ? 'POST' : 'GET'),
    headers: { 'Content-Type': 'application/json' }
  };

  if (data) opts.body = JSON.stringify(data);

  const r = await fetch(url, opts);

  const text = await r.text();

  let j;
  try {
    j = JSON.parse(text);
  } catch (e) {
    console.error("Réponse API invalide :", text);
    throw new Error("API retourne du HTML ou erreur PHP");
  }

  if (j.error) throw new Error(j.error);

  return j;
}
/* ═══════════════════════════
   helpers
═══════════════════════════ */

const COLORS=['#E31E24','#D97706','#2563EB','#059669','#7C3AED','#DB2777','#0D9488','#EA580C'];

function ini(name){
  return (name||'').split(' ').map(w=>w[0]||'').join('').slice(0,2).toUpperCase()||'??';
}

function color(id){
  return COLORS[(id-1)%COLORS.length]||COLORS[0];
}

function chip(task){
  const m={Finaliser:'F',Vérifier:'V',Commande:'C',Réception:'R'};
  const c=m[task]||'A';
  return `<span class="chip chip-${c}">${task}</span>`;
}

function chipStatus(s){
  const m={in_progress:'prog',done:'done',pending:'wait'};
  const l={in_progress:'En cours',done:'Terminé',pending:'En attente'};
  return `<span class="chip chip-${m[s]||'e'}">${l[s]||s}</span>`;
}

function toast(type,msg){
  const icons={ok:'✅',info:'ℹ️',warn:'⚠️',err:'❌'};
  const t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<span>${icons[type]||'•'}</span><span>${msg}</span>`;
  const box = document.getElementById('toasts');
  if(box) box.appendChild(t);
  setTimeout(()=>{
    t.style.animation='toastIn .22s ease reverse';
    setTimeout(()=>t.remove(),280);
  },3800);
}
/* ═══════════════════════════
   DASHBOARD
═══════════════════════════ */
async function loadDashboard(){
  try{
    const s=await api('dashboard_stats');
    document.getElementById('st-projets').textContent=s.projets_actifs||0;
    document.getElementById('st-metros').textContent=s.metrologues||0;
    document.getElementById('st-taches').textContent=s.taches_done||0;
  }catch(e){console.error('Stats:',e)}

  try{
    const metros=await api('metrologues_list');
    console.log('metros:', metros);
    if(!metros || metros.length===0){
      document.getElementById('plan-mini').innerHTML='<tr><td colspan="7" style="text-align:center;padding:20px;color:#9AA3B7">Aucun métrologue</td></tr>';
      document.getElementById('dash-podium').innerHTML='<div style="text-align:center;padding:20px;color:#9AA3B7">Aucun score</div>';
      return;
    }

    buildDashPodium(metros);
    buildLeaderBadge(metros);
    const sel = document.getElementById('p-metro');
const sel2 = document.getElementById('filter-metro');

// ✅ RESET AVANT REMPLISSAGE
if(sel) sel.innerHTML = '<option value="">Choisir...</option>';
if(sel2) sel2.innerHTML = '<option value="">Filtrer...</option>';

metros.forEach(m=>{
  if(sel){
    const o=document.createElement('option');
    o.value=m.id;
    o.textContent=m.name;
    sel.appendChild(o);
  }
  if(sel2){
    const o=document.createElement('option');
    o.value=m.id;
    o.textContent=m.name;
    sel2.appendChild(o);
  }
});
  }catch(e){
    console.error('Metros:', e);
    if(document.getElementById('plan-mini')) document.getElementById('plan-mini').innerHTML='<tr><td colspan="7" style="text-align:center;padding:20px;color:#E31E24">Erreur: '+e.message+'</td></tr>';
  }
}

function buildPlanMini(metros){
  const tasks=['Finaliser','Vérifier','Commande','Réception',null,null,'Absent'];
  const b=document.getElementById('plan-mini');
  if(!b) return;
  b.innerHTML=metros.slice(0,6).map(m=>{
    const ini_=ini(m.name);const col=color(m.id);
    const days=[0,1,2,3,4].map(()=>{const t=tasks[Math.floor(Math.random()*tasks.length)];return t?chip(t):'<span class="chip chip-e">—</span>';}).join('');
    return`<tr><td><div style="display:flex;align-items:center;gap:8px"><div class="ava" style="background:${col}18;color:${col}">${ini_}</div><div><div style="font-weight:600;font-size:12px"><button class="ml-btn" onclick="openMetroModal(${m.id})">${m.name}</button></div></div></div></td>${days.split('</span>').filter(Boolean).map(d=>`<td class="dc">${d}</span></td>`).join('')}<td><span style="font-weight:800;font-size:13px;color:var(--gold);font-family:'DM Mono',monospace">${m.score||0}</span></td></tr>`;
  }).join('');
}

function buildDashPodium(metros){
  const s=[...metros].sort((a,b)=>b.score-a.score);
  const published = false;  const podTimer=document.getElementById('podium-timer');
  const podContent=document.getElementById('podium-content');
  if(published && s.length>=1){
    if(podTimer) podTimer.style.display='none';
    if(podContent) podContent.style.display='block';
    if(podContent){
      podContent.innerHTML=s.slice(0,3).map((m,i)=>`
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--bdr)">
         <div style="font-size:18px">${['🥇','🥈','🥉'][i]}</div>
         <div style="flex:1;font-weight:600;font-size:13px">${m.name}</div>
         <div style="font-weight:800;font-size:15px;color:var(--gold);font-family:'DM Mono',monospace">${m.score||0} pts</div>
        </div>`).join('');
}
  } else {
    if(podTimer) podTimer.style.display='block';
    if(podContent) podContent.style.display='none';
  }
  if(s[0]){
    document.getElementById('champ-name').textContent=s[0].name;
    document.getElementById('champ-score').textContent=s[0].score||0;
    champData=s[0];
  }
}

function buildLeaderBadge(metros){
  const s=[...metros].sort((a,b)=>b.score-a.score);
  const lb=document.getElementById('leader-badge');
  if(lb && s[0]) lb.innerHTML=`🏅 <strong style="color:var(--gold)">Leader :</strong> ${s[0].name} — <strong style="color:var(--gold)">${s[0].score||0} pts</strong>`;
}
/* ═══════════════════════════
   MÉTROLOGUES
═══════════════════════════ */
async function loadMetros(){
  try{
    const metros=await api('metrologues_list');
    const el=document.getElementById('metro-grid');
    el.innerHTML=metros.map(m=>{
      const i=ini(m.name);const col=color(m.id);
      return`<div class="metro-card" onclick="openMetroModal(${m.id})">
        <div class="mc-top">
          <div class="ava ava-lg" style="background:${col}18;color:${col}">${i}</div>
          <div class="mc-info"><strong>${m.name}</strong><span>${m.niveau||'—'} · Classeur #${m.classeur_number||'—'}</span></div>
          <div class="mc-badge">${(m.score||0)>800?'🏆':(m.score||0)>600?'⭐':'💼'}</div>
        </div>
        <div class="mc-stats">
          <div class="mc-stat"><div class="mc-stat-val" style="color:var(--gold)">${m.score||0}</div><div class="mc-stat-key">Score</div></div>
          <div class="mc-stat"><div class="mc-stat-val" style="color:var(--blue)">${m.projets||0}</div><div class="mc-stat-key">Projets</div></div>
          <div class="mc-stat"><div class="mc-stat-val" style="color:${(m.absences||0)>4?'var(--red)':'var(--green)'}">${m.absences||0}</div><div class="mc-stat-key">Absences</div></div>
        </div>
        <div style="margin-top:10px;padding-top:8px;border-top:1px solid var(--bdr);font-size:11px;color:var(--txt3)">
          📺 TFT/LED Classeur <strong style="color:var(--red)">#${m.classeur_number||'—'}</strong> · ${m.specialite||'—'}
        </div>
      </div>`;
    }).join('');
  }catch(e){toast('err','Erreur chargement métrologues : '+e.message)}
}

let currentMetroId=null,champData=null,champPublished=false;

async function openMetroModal(id){
  currentMetroId=id;
  openOv('ov-metro');
  document.getElementById('mm-name').textContent='Chargement...';
try {
  const m = await api('metro_detail&id=' + id); 

  const col = color(m.id);
  const i = ini(m.name);

  const av = document.getElementById('mm-av');
  if (av) {
    av.textContent = i;
    av.style.background = `linear-gradient(135deg,${col},${col}99)`;
  }

  const nameEl = document.getElementById('mm-name');
  if (nameEl) nameEl.textContent = m.name;

  const roleEl = document.getElementById('mm-role');
  if (roleEl) {
    roleEl.textContent = `${m.niveau || '—'} · ${m.specialite || '—'} · Classeur #${m.classeur_number || '—'}`;
  }

  const scoreEl = document.getElementById('mm-score');
  if (scoreEl) scoreEl.textContent = m.score || 0;

  const projsEl = document.getElementById('mm-projs');
  if (projsEl) projsEl.textContent = (m.projects || []).length;

  const presEl = document.getElementById('mm-pres');
  if (presEl) presEl.textContent = 'ID #' + m.id;

  const absEl = document.getElementById('mm-abs');
  if (absEl) absEl.textContent = (m.absences_list || []).length;

  // Infos
  const infosEl = document.getElementById('mm-infos');
  if (infosEl) {
    infosEl.innerHTML = `
      <div>
        <span class="fl">ID</span>
        <div style="font-family:'DM Mono',monospace;font-weight:700">
          MTR-${String(m.id).padStart(3,'0')}
        </div>
      </div>

      <div>
        <span class="fl">Classeur TFT/LED</span>
        <div style="font-family:'DM Mono',monospace;font-weight:800;color:var(--red)">
          #${m.classeur_number || '—'}
        </div>
      </div>

      <div>
        <span class="fl">Poste</span>
        <div style="font-weight:600">
          ${m.poste || '—'}
        </div>
      </div>
    `;
  }

  // Projets
  const projetsEl = document.getElementById('mm-projets');
  if (projetsEl) {
    projetsEl.innerHTML = (m.projects || []).length
      ? (m.projects || []).map(p => `
          <div class="pli">
            <div class="pli-dot" style="background:${col}"></div>
            <div class="pli-name">${p.title}</div>
            ${chipStatus(p.status)}
          </div>
        `).join('')
      : `<div style="color:var(--txt3);font-size:12px;padding:6px">Aucun projet</div>`;
  }

  // Absences
  const absencesEl = document.getElementById('mm-absences');
  if (absencesEl) {
    absencesEl.innerHTML = (m.absences_list || []).length
      ? (m.absences_list || []).map(d => `
          <span class="abs-chip">📅 ${d}</span>
        `).join('')
      : `<span style="color:var(--txt3);font-size:12px">Aucune absence ce trimestre</span>`;
  }

} catch (e) {
  const nameEl = document.getElementById('mm-name');
  if (nameEl) nameEl.textContent = 'Erreur : ' + e.message;
}
}
function openAddMetro(){
  const el = document.getElementById('metro-modal-title');
  if(el) el.textContent='🔧 Nouveau Métrologue';
  document.getElementById('m-id').value='';
  ['m-name','m-email','m-tel','m-poste','m-pass'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  document.getElementById('m-classeur').value='';
  document.getElementById('pass-field').style.display='block';
  openOv('ov-addmetro');
}

function openEditMetro(){
  closeOv('ov-metro');
  const el = document.getElementById('metro-modal-title');
  if(el) el.textContent='✏️ Modifier Métrologue';
  document.getElementById('pass-field').style.display='none';
  // Pre-fill depuis le modal précédent (on recharge)
  api('metro_detail&id='+currentMetroId).then(m=>{
    document.getElementById('m-id').value=m.id;
    document.getElementById('m-name').value=m.name;
    document.getElementById('m-email').value=m.email;
    document.getElementById('m-tel').value=m.telephone||'';
    document.getElementById('m-poste').value=m.poste||'';
    document.getElementById('m-classeur').value=m.classeur_number||'';
    document.getElementById('m-niveau').value=m.niveau||'Junior';
    document.getElementById('m-spec').value=m.specialite||'Calibration';
  });
  openOv('ov-addmetro');
}

async function saveMetro(){
  const id=document.getElementById('m-id').value;
  const data={
    id:id?parseInt(id):null,
    name:document.getElementById('m-name').value.trim(),
    email:document.getElementById('m-email').value.trim(),
    telephone:document.getElementById('m-tel').value.trim(),
    poste:document.getElementById('m-poste').value.trim(),
    niveau:document.getElementById('m-niveau').value,
    specialite:document.getElementById('m-spec').value,
    classeur_number:parseInt(document.getElementById('m-classeur').value)||0,
    password:document.getElementById('m-pass')?.value||''
  };
  if(!data.name||!data.email){toast('err','❌ Nom et email obligatoires');return}
  try{
    await api(id?'metro_edit':'metro_add', data, 'POST');
    closeOv('ov-addmetro');
    toast('ok',`✅ Métrologue ${data.name} enregistré !`);
    loaders.metrologues=false;loadMetros();
  }catch(e){toast('err','❌ '+e.message)}
}

async function deleteMetro(){
  if(!confirm('Supprimer ce métrologue ?')) return;
  try{
    await api('metro_delete',{id:currentMetroId},'POST');
    closeOv('ov-metro');
    toast('warn','🗑️ Métrologue supprimé');
    loaders.metrologues=false;loadMetros();
  }catch(e){toast('err','❌ '+e.message)}
}


/* ═══════════════════════════
   PROJETS
═══════════════════════════ */
async function loadProjets(){
  try{
    const projs=await api('projets_list');
    const el=document.getElementById('proj-grid');
    const COLS=['#E31E24','#D97706','#2563EB','#059669','#7C3AED','#DB2777','#0D9488','#EA580C'];
    el.innerHTML=projs.map((p,i)=>{
      const col=COLS[i%8];
      const steps=(p.steps||[]).map(s=>`<div class="pt chip-${s.name==='Finaliser'?'F':s.name==='Vérifier'?'V':s.name==='Commande'?'C':'R'} ${s.status==='done'?'done':''}"><span>${s.status==='done'?'✅':'⬜'}</span>${s.name}</div>`).join('');
      return`<div class="proj-card">
        <div class="pj-top"><span style="font-size:10.5px;font-weight:700;color:${col};background:${col}12;padding:2px 7px;border-radius:20px;border:1px solid ${col}22">Métrologie</span>${chipStatus(p.status)}</div>
        <div class="pj-name">${p.title}</div>
        <div class="pj-tasks">${steps}</div>
        <div class="pb-lbl"><span>Avancement</span><span>${p.progress||0}%</span></div>
        <div class="pb-bg"><div class="pb-fill" style="width:${p.progress||0}%;background:${col}"></div></div>
        <div class="pj-assign">
          <div class="ava" style="background:#E31E2418;color:var(--red)">${ini(p.metro_name||'')}</div>
          <span>${p.metro_name||'—'}</span>
          <span style="margin-left:auto;font-size:10.5px;color:var(--txt3)">Échéance : ${p.deadline||'—'}</span>
        </div>
      </div>`;
    }).join('');
  }catch(e){toast('err','Erreur projets : '+e.message)}
}
function openOv(id){
  document.getElementById(id)?.classList.add('open');
}

function closeOv(id){
  document.getElementById(id)?.classList.remove('open');
}
async function openAddProj(){
  const sel=document.getElementById('p-metro');
  if(sel.options.length<=1){
    try{
      const metros=await api('metrologues_list');
      metros.forEach(m=>{
        const o=document.createElement('option');
        o.value=m.id;
        o.textContent=m.name;
        sel.appendChild(o);
      });
    }catch(e){}
  }
  openOv('ov-addproj');
}
async function saveProj(){
  const data={
    title:document.getElementById('p-nom').value.trim(),
    assigned_to:parseInt(document.getElementById('p-metro').value)||0,
    deadline:document.getElementById('p-deadline').value||null,
    priority:document.getElementById('p-prio').value,
    description:document.getElementById('p-desc').value.trim()
  };
  if(!data.title||!data.assigned_to){toast('err','❌ Titre et métrologue obligatoires');return}
  try{
    await api('projet_add',data,'POST');
    closeOv('ov-addproj');
    toast('ok','✅ Projet créé avec les 4 tâches !');
    loaders.projets=false;loadProjets();
  }catch(e){toast('err','❌ '+e.message)}
}

/* ═══════════════════════════
   SCORES
═══════════════════════════ */
async function loadScores(){
  try{
    const rows=await api('scores_list');
    const mx=(rows[0]?.score)||1;
    const COLS=['#E31E24','#D97706','#2563EB','#059669','#7C3AED','#DB2777','#0D9488','#EA580C'];
    // Podium
    const pod=document.getElementById('scores-podium');
    if(rows.length>=3){
      pod.innerHTML=`<div class="podium">
        <div class="pp pp2"><div class="pa-w"><div class="pp-av" style="width:40px;height:40px">${ini(rows[1]?.name)}</div><div class="pp-crown">🥈</div></div><div class="pp-name">${(rows[1]?.name||'').split(' ')[0]}</div><div class="pp-pts">${rows[1]?.score||0}</div><div class="pp-bar">2ème</div></div>
        <div class="pp pp1"><div class="pa-w"><div class="pp-av" style="width:46px;height:46px">${ini(rows[0]?.name)}</div><div class="pp-crown">👑</div></div><div class="pp-name">${(rows[0]?.name||'').split(' ')[0]}</div><div class="pp-pts">${rows[0]?.score||0}</div><div class="pp-bar">1er</div></div>
        <div class="pp pp3"><div class="pa-w"><div class="pp-av" style="width:36px;height:36px">${ini(rows[2]?.name)}</div><div class="pp-crown">🥉</div></div><div class="pp-name">${(rows[2]?.name||'').split(' ')[0]}</div><div class="pp-pts">${rows[2]?.score||0}</div><div class="pp-bar">3ème</div></div>
      </div>`;
    }
    // Liste
    const list=document.getElementById('scores-list');
    list.innerHTML=rows.map((r,i)=>{const col=COLS[i%8];return`<div class="score-row"><div class="s-rank">${i+1}</div><div class="ava" style="background:${col}18;color:${col}">${ini(r.name)}</div><div class="s-name">${r.name}</div><div class="s-bar-w"><div class="s-bar-bg"><div class="s-bar" style="width:${Math.round((r.score/mx)*100)}%;background:${col}"></div></div></div><div class="s-pts">${r.score||0}</div></div>`;}).join('');
  }catch(e){toast('err','Erreur scores : '+e.message)}
}

/* ═══════════════════════════
   POINTAGES
═══════════════════════════ */
async function loadPointages(){
  try{
    const userId=document.getElementById('filter-metro').value;
    const date=document.getElementById('filter-date').value;
    let url='pointages_list';
    if(userId)url+='&user_id='+userId;
    if(date)url+='&date='+date;
    const rows=await api(url);
    const b=document.getElementById('pointage-body');
    b.innerHTML=rows.map(r=>{
      const sc={ok:'ok',late:'late',absent:'abs'}[r.statut]||'abs';
      const sl={ok:'À l\'heure',late:'Retard',absent:'Absent'}[r.statut]||r.statut;
      const ini_=ini(r.metro_name||'');
      return`<tr>
        <td><div style="display:flex;align-items:center;gap:7px"><div class="ava" style="background:var(--red-bg);color:var(--red)">${ini_}</div><div><div style="font-weight:600;font-size:12px">${r.metro_name}</div><div style="font-size:10.5px;color:var(--txt3)">Classeur #${r.classeur_number||'—'}</div></div></div></td>
        <td style="font-family:'DM Mono',monospace;font-size:12px">${r.date}</td>
        <td style="font-family:'DM Mono',monospace;font-weight:600">${r.check_in||'—'}</td>
        <td style="font-family:'DM Mono',monospace;font-weight:600">${r.check_out||'—'}</td>
        <td style="font-family:'DM Mono',monospace">${r.duree||'—'}</td>
        <td><span class="chip pres-${sc}" style="padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600">${sl}</span></td>
        <td style="font-size:11.5px;color:var(--txt3)">${r.note||'—'}</td>
      </tr>`;
    }).join('')||'<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--txt3)">Aucun enregistrement</td></tr>';
  }catch(e){toast('err','Erreur pointages : '+e.message)}
}

/* ═══════════════════════════
   HORAIRES
═══════════════════════════ */
async function loadHoraires(){
  try{
    const rows=await api('horaires_get');
    const el=document.getElementById('horaires-list');
    el.innerHTML=rows.map(r=>`<div class="hor-row">
      <div class="ava" style="background:var(--red-bg);color:var(--red)">${ini(r.name)}</div>
      <div class="hor-name">${r.name}</div>
      <div class="hor-time">
        <span style="font-size:10.5px;color:var(--txt3)">Arrivée</span>
        <input class="hor-inp" type="time" value="${r.work_start||'08:00'}" data-id="${r.id}" data-type="work_start"/>
        <span style="font-size:10.5px;color:var(--txt3)">→</span>
        <input class="hor-inp" type="time" value="${r.work_end||'17:00'}" data-id="${r.id}" data-type="work_end"/>
        <span style="font-size:10.5px;color:var(--txt3)">Départ</span>
      </div>
    </div>`).join('');
  }catch(e){toast('err','Erreur horaires : '+e.message)}
}

async function saveHoraires(){
  const horaires=[];
  document.querySelectorAll('.hor-inp').forEach(inp=>{
    let h=horaires.find(x=>x.user_id===inp.dataset.id);
    if(!h){h={user_id:inp.dataset.id};horaires.push(h);}
    h[inp.dataset.type]=inp.value;
  });
  try{
    await api('horaires_save',{horaires},'POST');
    toast('ok','✅ Horaires enregistrés !');
  }catch(e){toast('err','❌ '+e.message)}
}

/* ═══════════════════════════
   JOURNAL
═══════════════════════════ */
async function loadJournal(){
  try{
    const rows=await api('journal_get');
    const el=document.getElementById('journal-body');
    if(!rows.length){el.innerHTML='<div style="color:var(--txt3);font-size:12px;text-align:center;padding:20px">Aucune action enregistrée</div>';return;}
    el.innerHTML=rows.map(l=>`<div class="log-row"><div class="log-ico">${l.ico||'•'}</div><div class="log-msg">${l.action} ${l.details?'— '+l.details:''}</div><span class="log-type log-${l.type||'ok'}">${l.type==='ok'?'OK':l.type==='warn'?'AVERT.':'ERR.'}</span><div class="log-time">${l.time}</div></div>`).join('');
  }catch(e){toast('err','Erreur journal : '+e.message)}
}

/* ═══════════════════════════
   SÉCURITÉ
═══════════════════════════ */
async function saveProfile(){
  try{
    await api('update_profile',{name:document.getElementById('sec-name').value,email:document.getElementById('sec-email').value},'POST');
    toast('ok','✅ Profil mis à jour');
  }catch(e){toast('err','❌ '+e.message)}
}
async function changePassword(){
  const o=document.getElementById('sec-old').value;
  const n=document.getElementById('sec-new').value;
  const c=document.getElementById('sec-conf').value;
  if(n!==c){toast('err','❌ Mots de passe différents');return}
  if(n.length<6){toast('err','❌ Trop court (min 6 caractères)');return}
  try{
    await api('change_password',{old_password:o,new_password:n},'POST');
    ['sec-old','sec-new','sec-conf'].forEach(id=>document.getElementById(id).value='');
    toast('ok','✅ Mot de passe modifié !');
  }catch(e){toast('err','❌ '+e.message)}
}

/* ═══════════════════════════
   RESET / RESTART / DELETE
═══════════════════════════ */
async function confirmReset(){
  try{
    await api('scores_reset',{},'POST');
    closeOv('ov-reset');
    toast('warn','🔄 Scores remis à zéro !');
    loaders.scores=false;
  }catch(e){toast('err','❌ '+e.message)}
}
async function confirmRestart(){
  try{
    await api('system_restart',{},'POST');
    closeOv('ov-restart');
    toast('info','🔄 Signal de redémarrage envoyé...');
    setTimeout(()=>toast('ok','✅ Système redémarré !'),2500);
  }catch(e){toast('err','❌ '+e.message)}
}
async function confirmDeleteOld(){
  const d=document.getElementById('delete-before').value;
  if(!d){toast('err','❌ Sélectionnez une date');return}
  if(!confirm(`Supprimer les pointages avant le ${d} ?`)) return;
  try{
    await api('delete_old',{before_date:d},'POST');
    toast('ok',`✅ Pointages avant ${d} supprimés`);
  }catch(e){toast('err','❌ '+e.message)}
}

/* ═══════════════════════════
   CHAMPION
═══════════════════════════ */
function openChamp(){startConf();document.getElementById('ov-champ').classList.add('open');document.getElementById('champ-note').innerHTML=champPublished?'<span style="color:var(--green)">✅ Déjà publié</span>':'';}
function closeChamp(){document.getElementById('ov-champ').classList.remove('open');stopConf()}
const el = document.getElementById('ov-champ');
if(el){
  el.addEventListener('click',e=>{if(e.target===document.getElementById('ov-champ'))closeChamp()});
}
async function publishChamp(){
  try{
    if(champData) await api('champion_publish',{user_id:champData.id},'POST');
    champPublished=true;closeChamp();
    toast('ok','📢 Résultat publié sur la plateforme métrologue !');
  }catch(e){toast('err','❌ '+e.message)}
}
const confCols=['#E31E24','#D97706','#E31E24','#2563EB','#059669'];
function startConf(){const c=document.getElementById('conf-c');c.innerHTML='';for(let i=0;i<55;i++){const p=document.createElement('div');p.className='conf-p';p.style.cssText=`left:${Math.random()*100}%;background:${confCols[Math.floor(Math.random()*confCols.length)]};border-radius:${Math.random()>.5?'50%':'2px'};width:${5+Math.random()*7}px;height:${7+Math.random()*9}px;animation-duration:${2+Math.random()*3}s;animation-delay:${Math.random()*2}s`;c.appendChild(p);}}
function stopConf(){document.getElementById('conf-c').innerHTML=''}

/* ═══════════════════════════
   MODALS
═══════════════════════════ */
window.addEventListener("DOMContentLoaded", ()=>{
  document.querySelectorAll('.overlay').forEach(o=>{
    o.addEventListener('click',e=>{
      if(e.target===o)o.classList.remove('open')
    });
  });
});

/* ═══════════════════════════
   TIMER
═══════════════════════════ */
function tick(){
  // Fin de trimestre = dernier jour du trimestre courant
  const now=new Date();const q=Math.ceil((now.getMonth()+1)/3);
  const endMonth=[2,5,8,11][q-1];
  const lastDay=new Date(now.getFullYear(),endMonth+1,0,23,59,59);
  const diff=Math.max(0,lastDay-now);
  const d=Math.floor(diff/86400000),h=Math.floor((diff%86400000)/3600000),mn=Math.floor((diff%3600000)/60000);
  const f=n=>String(n).padStart(2,'0');
  ['td1','td2'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(d)});
  ['th1','th2'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(h)});
  ['tm1','tm2'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(mn)});
  ['td3'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(d)});
  ['th3'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(h)});
  ['tm3'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=f(mn)});
  // Progress bar
  const startMonth=[0,3,6,9][q-1];
  const start=new Date(now.getFullYear(),startMonth,1);
  const end=lastDay;
  const pct=Math.round(((now-start)/(end-start))*100);
  const pb=document.getElementById('trim-bar');if(pb)pb.style.width=pct+'%';
  const pp=document.getElementById('trim-pct');if(pp)pp.textContent=pct+'%';
}
setInterval(tick,1000);tick();

/* ═══════════════════════════
   INIT
═══════════════════════════ */
async function loadPlanning(){
  try{
    const metros = await api('metrologues_list');
    const b = document.getElementById('plan-full');

    if(!b) return;

    if(!metros || metros.length === 0){
      b.innerHTML = '<tr><td colspan="8">Aucun métrologue</td></tr>';
      return;
    }

    const tasksList = ['Finaliser','Vérifier','Commande','Réception',null,null,'Absent'];
    const TMAP = {Finaliser:'F',Vérifier:'V',Commande:'C',Réception:'R',Absent:'A'};

    b.innerHTML = metros.map(m=>{
      const col = color(m.id);

      const days = [0,1,2,3,4].map(()=>{
        const t = tasksList[Math.floor(Math.random()*tasksList.length)];
        return `<td>${t ? `<span class="chip chip-${TMAP[t]||'e'}">${t}</span>` : '—'}</td>`;
      }).join('');

      return `<tr>
        <td>${m.name}</td>
        ${days}
        <td>${m.score || 0}</td>
        <td><button class="fb sec" onclick="openMetroModal(${m.id})">Voir</button></td>
      </tr>`;
    }).join('');

  }catch(e){
    console.error('Planning:', e);
  }
}
