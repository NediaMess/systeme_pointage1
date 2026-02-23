<?php
require_once "../lang_init.php";
require_once "../config/database.php";
$user_id = $_SESSION['user_id'];

/* ===============================
   GESTION MOIS / ANNEE
=================================*/

$mois_courant = isset($_GET['mois']) ? (int)$_GET['mois'] : date('n');
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : date('Y');

/* Si date sélectionnée */
if(isset($_GET['date_select']) && !empty($_GET['date_select'])){
    $date = $_GET['date_select'];
    $mois_courant = date('n', strtotime($date));
    $annee = date('Y', strtotime($date));
}

/* Déterminer trimestre */
$trimestre = ceil($mois_courant / 3);
$premier_mois_trimestre = ($trimestre - 1) * 3 + 1;

/* Générer les 3 mois */
$mois = [];
for ($i = 0; $i < 3; $i++) {
    $mois[] = [
        'mois' => $premier_mois_trimestre + $i,
        'annee' => $annee
    ];
}

/* Navigation */
$mois_precedent = $premier_mois_trimestre - 3;
$mois_suivant = $premier_mois_trimestre + 3;
$annee_precedente = $annee;
$annee_suivante = $annee;

if ($mois_precedent < 1) {
    $mois_precedent += 12;
    $annee_precedente--;
}

if ($mois_suivant > 12) {
    $mois_suivant -= 12;
    $annee_suivante++;
}

/* ===============================
   SCORES
=================================*/

$stmt = $pdo->prepare("
    SELECT date_score, score
    FROM daily_scores
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$scores_par_date = [];
foreach ($scores as $s) {
    $scores_par_date[date('Y-m-d', strtotime($s['date_score']))] = $s['score'];
}

$today = date('Y-m-d');
?>

<!-- ===============================
     FLATPICKR (Mini Calendrier)
=================================-->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
body { font-family: Arial, sans-serif; }

.navigation {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.navigation a {
    font-size: 20px;
    text-decoration: none;
    color: #333;
}
.hidden-date-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}
.calendar-btn {
    background: white;
    border: 1px solid #ccc;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
}

.calendar-btn:hover {
    background: #f4f4f4;
}

.calendrier-container {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}

.calendrier-container > div {
    text-align: center;
}

table { border-collapse: collapse; }

th {
    background-color: #f4f4f4;
    padding: 4px;
    font-size: 13px;
}

td {
    width: 50px;
    height: 60px;
    border: 1px solid #ddd;
    vertical-align: top;
    text-align: center;
}

.day-number {
    font-weight: bold;
    font-size: 13px;
}

.today-dot {
    width: 5px;
    height: 5px;
    background: orange;
    border-radius: 50%;
    margin: 3px auto;
}

.score-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    font-size: 10px;
    font-weight: bold;
    color: white;
    margin: 3px auto;
}

.score-green { background-color: #4CAF50; }
.score-red { background-color: #f44336; }
</style>

<h2>Calendrier de performances</h2>

<!-- ===============================
     NAVIGATION
=================================-->

<div class="navigation">

    <a href="?page=calendrier_performance&mois=<?= $mois_precedent ?>&annee=<?= $annee_precedente ?>">←</a>

    <strong>
        <?= date('M', mktime(0,0,0,$mois[0]['mois'],1,$mois[0]['annee'])) ?>
        -
        <?= date('M', mktime(0,0,0,$mois[1]['mois'],1,$mois[1]['annee'])) ?>
        -
        <?= date('M', mktime(0,0,0,$mois[2]['mois'],1,$mois[2]['annee'])) ?>
        <?= $annee ?>
    </strong>

    <a href="?page=calendrier_performance&mois=<?= $mois_suivant ?>&annee=<?= $annee_suivante ?>">→</a>

    <!-- Bouton mini calendrier -->
    <form method="GET" id="dateForm">
        <input type="hidden" name="page" value="calendrier_performance">
        <input type="text" id="datePicker" name="date_select" class="hidden-date-input">
        <button type="button" id="openCalendar" class="calendar-btn">📅</button>
    </form>

</div>

<!-- ===============================
     CALENDRIERS
=================================-->

<div class="calendrier-container">

<?php foreach($mois as $item):

    $m = $item['mois'];
    $annee_affiche = $item['annee'];

    $premier_jour = mktime(0,0,0,$m,1,$annee_affiche);
    $nb_jours = date('t', $premier_jour);
    $nom_mois = date('F', $premier_jour);
?>

<div>
<h3><?= $nom_mois ?> <?= $annee_affiche ?></h3>

<table>
<tr>
<th>L</th><th>M</th><th>M</th><th>J</th>
<th>V</th><th>S</th><th>D</th>
</tr>
<tr>

<?php
$jour_semaine = date('N', $premier_jour);

for($i=1; $i<$jour_semaine; $i++){
    echo "<td></td>";
}

for($jour=1; $jour<=$nb_jours; $jour++){

    $date_complete = $annee_affiche . "-" .
        str_pad($m,2,"0",STR_PAD_LEFT) . "-" .
        str_pad($jour,2,"0",STR_PAD_LEFT);

    echo "<td>";
    echo "<div class='day-number'>$jour</div>";

    if($date_complete == $today){
        echo "<div class='today-dot'></div>";
    }

    if(array_key_exists($date_complete, $scores_par_date)){
        $score = $scores_par_date[$date_complete];
        $class = $score < 0 ? "score-red" : "score-green";
        echo "<div class='score-circle $class'>$score</div>";
    }

    echo "</td>";

    if(($jour + $jour_semaine -1) % 7 == 0){
        echo "</tr><tr>";
    }
}
?>

</tr>
</table>
</div>

<?php endforeach; ?>

</div>

<!-- ===============================
     SCRIPT MINI CALENDRIER
=================================-->

<script>
document.addEventListener("DOMContentLoaded", function(){

    const dateInput = document.getElementById("datePicker");
    const button = document.getElementById("openCalendar");

    const fp = flatpickr(dateInput, {
        dateFormat: "Y-m-d",
        appendTo: button.parentElement,   // IMPORTANT
        position: "auto center",
        onChange: function(selectedDates, dateStr) {
            document.getElementById("dateForm").submit();
        }
    });

    button.addEventListener("click", function(){
        fp.open();
    });

});
</script>