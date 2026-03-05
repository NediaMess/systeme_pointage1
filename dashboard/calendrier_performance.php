<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

$calendar_view = $_SESSION['calendar_view'] ?? 'trimester';
$first_day     = $_SESSION['first_day'] ?? 'monday';

$mois_courant = isset($_GET['mois'])  ? (int)$_GET['mois']  : date('n');
$annee        = isset($_GET['annee']) ? (int)$_GET['annee'] : date('Y');

if(isset($_GET['date_select']) && !empty($_GET['date_select'])){
    $d = $_GET['date_select'];
    $mois_courant = date('n', strtotime($d));
    $annee        = date('Y', strtotime($d));
}

$trimestre = ceil($mois_courant / 3);
$premier_mois_trimestre = ($trimestre - 1) * 3 + 1;

if($calendar_view == 'trimester'){
    $_SESSION['mois_selectionnes'] = [$premier_mois_trimestre, $premier_mois_trimestre+1, $premier_mois_trimestre+2];
} else {
    $_SESSION['mois_selectionnes'] = range(1,12);
}

$mois = [];
if($calendar_view == 'trimester'){
    for($i=0;$i<3;$i++) $mois[] = ['mois'=>$premier_mois_trimestre+$i,'annee'=>$annee];
} else {
    for($m=1;$m<=12;$m++) $mois[] = ['mois'=>$m,'annee'=>$annee];
}

if($calendar_view=='trimester'){
    $mp = $premier_mois_trimestre-3; $ap = $annee;
    $ms = $premier_mois_trimestre+3; $as = $annee;
    if($mp<1){$mp+=12;$ap--;}
    if($ms>12){$ms-=12;$as++;}
} else {
    $mp=$mois_courant; $ap=$annee-1;
    $ms=$mois_courant; $as=$annee+1;
}

$stmt = $pdo->prepare("SELECT date_score, score FROM daily_scores WHERE user_id=?");
$stmt->execute([$user_id]);

$scores_par_date = [];
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $s){
    $scores_par_date[date('Y-m-d',strtotime($s['date_score']))] = $s['score'];
}

$today = date('Y-m-d');

$stmt  = $pdo->prepare("SELECT date_off FROM non_working_days WHERE user_id=?");
$stmt->execute([$user_id]);
$off_days_db = $stmt->fetchAll(PDO::FETCH_COLUMN);

$mois_noms = ($lang_code === 'en') ? [
1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'
] : [
1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'
];
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Page header -->
<div class="page-header animate-in">
  <div>
    <h1><?= $lang['performance_calendar'] ?></h1>
    <div class="page-subtitle"><?= $lang['daily_scores'] ?></div>
  </div>
</div>

<!-- Navigation -->
<div class="cal-nav animate-in">

  <a class="cal-nav-btn" href="?page=calendrier_performance&mois=<?= $mp ?>&annee=<?= $ap ?>">←</a>

  <div class="cal-nav-center">

    <span class="cal-nav-title">

      <?php if($calendar_view=='trimester'): ?>

         <?= $mois_noms[$mois[0]['mois']] ?>
         <?= $mois_noms[$mois[1]['mois']] ?>
         <?= $mois_noms[$mois[2]['mois']] ?> <?= $annee ?>

      <?php else: ?>

        <?= $lang['year'] ?> <?= $annee ?>

      <?php endif; ?>

    </span>

    <!-- Date picker -->
    <form method="GET" id="dateForm" style="position:relative">
      <input type="hidden" name="page" value="calendrier_performance">

      <input type="text"
             id="datePicker"
             name="date_select"
             style="position:absolute;opacity:0;width:0;height:0;pointer-events:none">

      <button type="button" id="openCal" class="cal-picker-btn">
        📅 <?= $lang['go_to_date'] ?>
      </button>
    </form>

  </div>

  <a class="cal-nav-btn" href="?page=calendrier_performance&mois=<?= $ms ?>&annee=<?= $as ?>">→</a>

</div>

<!-- Calendars -->
<div class="cal-grid <?= $calendar_view=='year' ? 'year-mode' : '' ?> animate-in">

<?php foreach($mois as $item):

    $m = $item['mois'];
    $y = $item['annee'];

    $first_ts  = mktime(0,0,0,$m,1,$y);
    $nb_jours  = date('t',$first_ts);

    $day_start = $first_day=='monday'
        ? date('N',$first_ts)
        : (date('w',$first_ts)==0?1:date('w',$first_ts)+1);

?>

<div class="cal-month">

  <div class="cal-month-title">
    <?= $mois_noms[$m] ?> <?= $y ?>
  </div>

  <table class="cal-table">

    <tr>

      <?php if($first_day=='monday'): ?>

        <th>L</th>
        <th>M</th>
        <th>M</th>
        <th>J</th>
        <th>V</th>
        <th>S</th>
        <th>D</th>

      <?php else: ?>

        <th>D</th>
        <th>L</th>
        <th>M</th>
        <th>M</th>
        <th>J</th>
        <th>V</th>
        <th>S</th>

      <?php endif; ?>

    </tr>

    <tr>

<?php

for($i=1;$i<$day_start;$i++)
    echo "<td class='cal-empty'></td>";

for($j=1;$j<=$nb_jours;$j++){

    $date = $y.'-'.str_pad($m,2,'0',STR_PAD_LEFT).'-'.str_pad($j,2,'0',STR_PAD_LEFT);

    $isToday = ($date==$today);
    $isOff   = in_array($date,$off_days_db);

    $cls = ($isToday?'cal-today ':'').($isOff?'cal-day-off':'');

    echo "<td class='$cls'>";
    echo "<span class='cal-day-num'>$j</span>";

    if($isToday)
        echo "<div class='cal-dot'></div>";

    if(array_key_exists($date,$scores_par_date)){

        $sc = $scores_par_date[$date];
        $cl = $sc>=0 ? 'green' : 'red';

        echo "<div class='cal-score $cl'>$sc</div>";
    }

    echo "</td>";

    if((($j+$day_start-1)%7)==0)
        echo "</tr><tr>";
}

?>

    </tr>
  </table>

</div>

<?php endforeach; ?>

</div>

<script>

document.addEventListener('DOMContentLoaded',function(){

  const fp = flatpickr(document.getElementById('datePicker'),{

    dateFormat:'Y-m-d',

    onChange:function(){
      document.getElementById('dateForm').submit();
    }

  });

  document.getElementById('openCal')
    .addEventListener('click',function(){ fp.open(); });

});

</script>