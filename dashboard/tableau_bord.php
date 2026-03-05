<?php
require_once "../lang_init.php";
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT SUM(score) as total FROM daily_scores WHERE user_id=? AND date_score >= DATE_SUB(NOW(), INTERVAL 3 MONTH)");
$stmt->execute([$user_id]);
$score_trimestre = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

$mois_selectionnes = $_SESSION['mois_selectionnes'] ?? [date('n', strtotime('-2 month')), date('n', strtotime('-1 month')), date('n')];
$projets_mois = array_fill_keys($mois_selectionnes, 0);

$placeholders = implode(',', array_fill(0, count($mois_selectionnes), '?'));

$stmt = $pdo->prepare("
SELECT MONTH(date_fin) as mois, COUNT(*) as total
FROM projects
WHERE user_id=?
AND statut='terminé'
AND date_fin IS NOT NULL
AND MONTH(date_fin) IN ($placeholders)
GROUP BY MONTH(date_fin)
");

$stmt->execute(array_merge([$user_id], $mois_selectionnes));

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $projets_mois[$row['mois']] = $row['total'];
}

$mois_noms = [1=>"Jan",2=>"Fév",3=>"Mar",4=>"Avr",5=>"Mai",6=>"Jun",7=>"Juil",8=>"Août",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Déc"];

$max_projets = max(max($projets_mois), 1);

/* Projet actuel */
$stmt = $pdo->prepare("
SELECT nom_projet, statut, start_time, estimated_time, date_fin, date_creation
FROM projects
WHERE user_id=?
AND statut IN ('en_attente','en_cours','Terminé')
ORDER BY
CASE
WHEN statut='en_cours' THEN 1
WHEN statut='en_attente' THEN 2
ELSE 3
END,
date_creation DESC
LIMIT 1
");

$stmt->execute([$user_id]);
$projet_actuel = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM projects WHERE user_id=?");
$stmt->execute([$user_id]);
$total_projets = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM projects WHERE user_id=? AND statut='Terminé'");
$stmt->execute([$user_id]);
$projets_termines = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

/* Modes travail */
$stmt = $pdo->prepare("
SELECT mode_travail
FROM projects
WHERE user_id=?
AND statut='Terminé'
AND date_fin IS NOT NULL
AND date_fin >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
");

$stmt->execute([$user_id]);
$modes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_modes = count($modes);
$solo = 0;
$equipe = 0;

foreach ($modes as $m) {
    if ($m['mode_travail'] === 'solo') $solo++;
    elseif ($m['mode_travail'] === 'equipe') $equipe++;
}

$pct_solo   = $total_modes > 0 ? round(($solo/$total_modes)*100) : 0;
$pct_equipe = $total_modes > 0 ? round(($equipe/$total_modes)*100) : 0;

$r = 44;
$circ = round(2 * M_PI * $r, 2);

function circleOffset($pct, $circ) {
    return round($circ - ($pct/100)*$circ, 2);
}

/* Date FR */
function strftime_fr(){
    $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $mois  = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    return $jours[date('w')].' '.date('j').' '.$mois[(int)date('n')].' '.date('Y');
}

/* Temps projet */
$temps_info = '';

if ($projet_actuel) {

    $statut = $projet_actuel['statut'];

    if ($statut === 'en_cours' && $projet_actuel['start_time']) {

        $elapsed  = time() - strtotime($projet_actuel['start_time']);
        $remaining = ($projet_actuel['estimated_time'] * 60) - $elapsed;

        if ($remaining > 0) {
            $h = floor($remaining/3600);
            $m = floor(($remaining%3600)/60);
            $temps_info = $h > 0 ? "⏱ {$h}h {$m}min restant" : "⏱ {$m}min restant";
        } else {
            $over = abs($remaining);
            $h = floor($over/3600);
            $m = floor(($over%3600)/60);
            $temps_info = $h > 0 ? "⚠ +{$h}h {$m}min dépassé" : "⚠ +{$m}min dépassé";
        }

    } elseif ($statut === 'Terminé' && $projet_actuel['date_fin'] && $projet_actuel['start_time']) {

        $duree = strtotime($projet_actuel['date_fin']) - strtotime($projet_actuel['start_time']);
        $h = floor($duree/3600);
        $m = floor(($duree%3600)/60);
        $temps_info = $h > 0 ? "Durée : {$h}h {$m}min" : "Durée : {$m}min";

    } elseif ($statut === 'en_attente') {

        $h = floor($projet_actuel['estimated_time']/60);
        $m = $projet_actuel['estimated_time']%60;

        $temps_info = "Estimé : " . ($h>0 ? "{$h}h {$m}min" : "{$m}min");
    }
}
?>

<!-- Welcome -->
<div class="welcome-bar animate-in">
  <div>
    <h2><?= $lang['hello'] ?>, <?= htmlspecialchars($_SESSION['user_prenom']) ?> 👋</h2>
    <div class="welcome-date"><?= strftime_fr() ?></div>
  </div>
  <div class="welcome-badge"><?= $lang['job_title'] ?></div>
</div>

<!-- Stats -->
<div class="stats-row">

  <div class="stat-chip animate-in">
    <div class="stat-icon red">📊</div>
    <div class="stat-info">
      <span><?= $lang['score_trimester'] ?></span>
      <strong><?= $score_trimestre ?></strong>
    </div>
  </div>

  <div class="stat-chip animate-in">
    <div class="stat-icon blue">📁</div>
    <div class="stat-info">
      <span><?= $lang['projects'] ?></span>
      <strong><?= $total_projets ?></strong>
    </div>
  </div>

  <div class="stat-chip animate-in">
    <div class="stat-icon green">✓</div>
    <div class="stat-info">
      <span><?= $lang['completed'] ?></span>
      <strong><?= $projets_termines ?></strong>
    </div>
  </div>

  <div class="stat-chip animate-in">
    <div class="stat-icon orange">⏳</div>
    <div class="stat-info">
      <span><?= $lang['in_progress'] ?></span>
      <strong><?= $total_projets - $projets_termines ?></strong>
    </div>
  </div>

</div>

<!-- Header -->
<div class="page-header animate-in">
  <div>
    <h1><?= $lang['dashboard'] ?></h1>
    <div class="page-subtitle"><?= $lang['quarter_overview'] ?></div>
  </div>
</div>

<!-- Dashboard -->
<div class="dashboard-grid">

  <!-- Performance -->
  <div class="card perf-card animate-in">
    <div class="card-title"><?= $lang['quarterly_performance'] ?></div>
    <div class="perf-number"><?= min($score_trimestre,100) ?><sup>%</sup></div>
    <div class="perf-tag">▲ <?= $lang['quarterly'] ?></div>
    <div class="progress-track">
      <div class="progress-fill" id="perfFill"></div>
    </div>
  </div>

  <!-- Projects -->
  <div class="card animate-in">
    <div class="card-title"><?= $lang['completed_projects'] ?></div>

    <?php foreach($projets_mois as $mois => $count): ?>

      <div class="proj-bar-row">
        <span class="proj-bar-label"><?= $mois_noms[$mois] ?></span>

        <div class="proj-bar-track">
          <div class="proj-bar-fill"
          style="width:<?= round(($count/$max_projets)*100) ?>%">
          </div>
        </div>

        <span class="proj-bar-count"><?= $count ?></span>
      </div>

    <?php endforeach; ?>

  </div>

  <!-- Current project -->
  <div class="card db-current-card animate-in">

    <div class="card-title"><?= $lang['current_project'] ?></div>

    <?php if($projet_actuel):

      $s = $projet_actuel['statut'];

      $badge_class =
        $s==='en_cours' ? 'db-badge-orange' :
        ($s==='Terminé' ? 'db-badge-green' : 'db-badge-red');

      $badge_label =
        $s==='en_cours' ? '● '.$lang['in_progress'] :
        ($s==='Terminé' ? '✓ '.$lang['completed'] : '◌ '.$lang['pending']);

    ?>

      <div class="db-proj-name">
        <?= htmlspecialchars($projet_actuel['nom_projet']) ?>
      </div>

      <div class="db-proj-status <?= $badge_class ?>">
        <?= $badge_label ?>
      </div>

      <?php if($temps_info): ?>
        <div class="db-proj-time"><?= $temps_info ?></div>
      <?php endif; ?>

    <?php else: ?>

      <div class="db-proj-name"
      style="color:var(--text-3);font-size:16px;font-style:italic">
        <?= $lang['no_project'] ?>
      </div>

    <?php endif; ?>

  </div>

  <!-- Work mode -->
  <div class="card mode-card animate-in" style="background:var(--red);border:none">

    <div class="card-title">
      <?= $lang['work_mode'] ?> — <?= $lang['quarterly'] ?>
    </div>

    <div class="circles-row">

      <div class="circle-item circle-solo">

        <svg class="circle-svg" width="100" height="100" viewBox="0 0 100 100">
          <circle class="circle-track" cx="50" cy="50" r="<?= $r ?>"/>
          <circle class="circle-progress"
          cx="50" cy="50"
          r="<?= $r ?>"
          stroke-dasharray="<?= $circ ?>"
          stroke-dashoffset="<?= circleOffset($pct_solo,$circ) ?>"/>
        </svg>

        <span class="circle-pct"><?= $pct_solo ?>%</span>
        <span class="circle-lbl"><?= $lang['solo'] ?></span>

      </div>

      <div class="circle-item circle-team">

        <svg class="circle-svg" width="100" height="100" viewBox="0 0 100 100">
          <circle class="circle-track" cx="50" cy="50" r="<?= $r ?>"/>
          <circle class="circle-progress"
          cx="50" cy="50"
          r="<?= $r ?>"
          stroke-dasharray="<?= $circ ?>"
          stroke-dashoffset="<?= circleOffset($pct_equipe,$circ) ?>"/>
        </svg>

        <span class="circle-pct"><?= $pct_equipe ?>%</span>
        <span class="circle-lbl"><?= $lang['team'] ?></span>

      </div>

    </div>

  </div>

</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    document.getElementById('perfFill').style.width =
    '<?= min($score_trimestre,100) ?>%';
  }, 200);
});
</script>