<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
    header("Location: ../auth/login.php");
    exit();
}

/* ===== TERMINER PROJET ===== */
if(isset($_POST['terminer_projet'])){
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id=? AND statut='en_cours' LIMIT 1");
    $stmt->execute([$user_id]);
    $proj = $stmt->fetch(PDO::FETCH_ASSOC);

    if($proj){
        $elapsed = round((time()-strtotime($proj['start_time']))/60);
        $est = $proj['estimated_time'];

        $score = ($elapsed==$est)?0:($elapsed<$est?1:-2);

        $pdo->prepare("UPDATE projects SET statut='Terminé',date_fin=NOW(),score_ajoute=? WHERE id=?")
            ->execute([$score,$proj['id']]);

        $pdo->prepare("INSERT INTO daily_scores(user_id,project_id,score,date_score) VALUES(?,?,?,CURDATE())")
            ->execute([$user_id,$proj['id'],$score]);
    }

    header("Location: index.php?page=projet_courant");
    exit();
}

/* ===== PROJET ACTUEL ===== */

$stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id=? AND statut='en_cours' ORDER BY date_creation DESC LIMIT 1");
$stmt->execute([$user_id]);
$projet = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$projet){
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id=? AND statut='en_attente' ORDER BY date_creation ASC LIMIT 1");
    $stmt->execute([$user_id]);
    $projet = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ===== RAPPORT ===== */

$report = null;

if($projet){

    $stmt = $pdo->prepare("SELECT * FROM project_reports WHERE project_id=?");
    $stmt->execute([$projet['id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$report){
        $pdo->prepare("INSERT INTO project_reports(project_id) VALUES(?)")
            ->execute([$projet['id']]);

        $stmt->execute([$projet['id']]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/* ===== ACTIONS ===== */

if(isset($_POST['mode_travail']) && $projet){

    $m = $_POST['mode_travail'];

    if(in_array($m,['solo','equipe'])){
        $pdo->prepare("UPDATE projects SET mode_travail=? WHERE id=?")
            ->execute([$m,$projet['id']]);
    }

    header("Location: index.php?page=projet_courant");
    exit();
}

if(isset($_POST['toggle_checklist']) && $projet){

    $pdo->prepare("UPDATE projects SET checklist_rempli=NOT checklist_rempli WHERE id=?")
        ->execute([$projet['id']]);

    header("Location: index.php?page=projet_courant");
    exit();
}

if(isset($_POST['toggle_report']) && $projet){

    $f = $_POST['toggle_report'];

    if(in_array($f,['finaliser','verifier','envoyer','commande'])){
        $pdo->prepare("UPDATE project_reports SET $f=NOT $f WHERE project_id=?")
            ->execute([$projet['id']]);
    }

    header("Location: index.php?page=projet_courant");
    exit();
}

if(isset($_POST['description']) && $projet){

    $pdo->prepare("UPDATE projects SET description=? WHERE id=?")
        ->execute([$_POST['description'],$projet['id']]);

    header("Location: index.php?page=projet_courant");
    exit();
}

/* ===== SI AUCUN PROJET ===== */

if(!$projet): ?>

<div class="page-header animate-in">
  <div>
    <h1><?= $lang['current_project'] ?></h1>
  </div>
</div>

<div class="card animate-in" style="text-align:center;padding:60px 20px">

  <div style="font-size:40px;margin-bottom:16px">📭</div>

  <div style="font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--text);margin-bottom:8px">
    <?= $lang['no_project'] ?>
  </div>

  <p style="color:var(--text-2);font-size:14px;margin-bottom:24px">
    <?= $lang['no_project_available'] ?>
  </p>

  <a href="?page=projets" class="btn-secondary">
    <?= $lang['projects'] ?>
  </a>

</div>

<?php return; endif;

/* ===== TIMER ===== */

$is_en_cours = $projet['statut'] === 'en_cours';

$remaining = null;
$is_late = false;

if($is_en_cours && !empty($projet['start_time'])){

    $elapsed   = time() - strtotime($projet['start_time']);
    $remaining = ($projet['estimated_time']*60) - $elapsed;
    $is_late   = $remaining < 0;
}

$rapports = [
    'finaliser'=>$lang['finalize'],
    'verifier'=>$lang['verify'],
    'envoyer'=>$lang['send'],
    'commande'=>$lang['order']
];

$checklist_rempli = !empty($projet['checklist_rempli']);
?>

<div class="page-header animate-in">
  <div>
    <h1><?= $lang['current_project'] ?></h1>
    <div class="page-subtitle"><?= $lang['real_time_tracking'] ?></div>
  </div>
</div>

<!-- HERO -->
<div class="proj-hero animate-in">

  <div>

    <div class="proj-hero-label">
      <?= $projet['statut']==='en_cours'
        ? '⬤ '.$lang['in_progress']
        : '◌ '.$lang['pending'] ?>
    </div>

    <div class="proj-hero-name">
      <?= htmlspecialchars($projet['nom_projet']) ?>
    </div>

    <div class="proj-hero-meta">
      <?= $lang['creation_date'] ?>
      <?= date('d/m/Y', strtotime($projet['date_creation'])) ?>
    </div>

  </div>

  <div class="proj-hero-actions">

    <?php if($projet['statut']==='en_attente'): ?>

      <button class="btn-start"
        onclick="document.getElementById('startModal').classList.add('open')">

        ▶ <?= $lang['start_project'] ?>

      </button>

    <?php endif; ?>

    <?php if($projet['statut']==='en_cours'): ?>

      <button class="btn-finish"
        onclick="document.getElementById('finishModal').classList.add('open')">

        ✓ <?= $lang['finish_project'] ?>

      </button>

    <?php endif; ?>

  </div>

</div>

<div class="current-page-grid">

<!-- MODE TRAVAIL -->

<div class="card animate-in">

  <div class="card-title"><?= $lang['work_mode'] ?></div>

  <form method="POST">

    <div class="mode-toggle">

      <button type="submit" name="mode_travail" value="solo"
        class="mode-btn <?= $projet['mode_travail']==='solo'?'selected':'' ?>">

        👤 <?= $lang['solo'] ?>

      </button>

      <button type="submit" name="mode_travail" value="equipe"
        class="mode-btn <?= $projet['mode_travail']==='equipe'?'selected':'' ?>">

        👥 <?= $lang['team'] ?>

      </button>

    </div>

  </form>

</div>

<!-- CHECKLIST -->

<div class="card animate-in">

<div class="card-title"><?= $lang['checklist'] ?></div>

<form method="POST">

<input type="hidden" name="toggle_checklist" value="1">

<div class="check-item <?= $checklist_rempli?'checked':'' ?>">

<input type="checkbox"
 onchange="this.form.submit()"
 <?= $checklist_rempli?'checked':'' ?>>

<label><?= $lang['checklist_completed'] ?></label>

<span class="checklist-status <?= $checklist_rempli?'checklist-ok':'checklist-pending' ?>">

<?= $checklist_rempli ? '✓ '.$lang['completed'] : '✗ '.$lang['pending'] ?>

</span>

</div>

</form>

</div>

<!-- RAPPORT -->

<div class="card animate-in">

<div class="card-title"><?= $lang['report'] ?></div>

<?php foreach($rapports as $field => $label):

$checked = !empty($report[$field]);

?>

<form method="POST">

<input type="hidden" name="toggle_report" value="<?= $field ?>">

<div class="check-item <?= $checked?'rapport-checked':'' ?>">

<input type="checkbox"
 onchange="this.form.submit()"
 <?= $checked?'checked':'' ?>>

<label class="<?= $checked?'rapport-label-done':'' ?>">

<?= $label ?>

</label>

<?php if($checked): ?>

<span style="color:var(--blue);font-size:12px;font-weight:700">✓</span>

<?php endif; ?>

</div>

</form>

<?php endforeach; ?>

</div>

<!-- DESCRIPTION -->

<div class="card col-full animate-in">

<div class="card-title">

<?= $lang['description_checklist'] ?>

</div>

<form method="POST">

<textarea name="description"
class="desc-textarea"
placeholder="<?= $lang['write_remark'] ?>"
required>

<?= htmlspecialchars($projet['description'] ?? '') ?>

</textarea>

<button type="submit"
class="btn-primary"
style="width:auto;padding:11px 28px">

<?= $lang['save'] ?>

</button>

</form>

</div>

</div>