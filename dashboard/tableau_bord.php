<?php
require_once "../lang_init.php";
require_once "../config/database.php";

/* ===== Sécurité : vérifier si connecté ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


/* ===========================
   SCORE TRIMESTRIEL (3 derniers mois automatique)
=========================== */
$stmt = $pdo->prepare("
    SELECT SUM(score) as total_score
    FROM daily_scores
    WHERE user_id = ?
    AND date_score >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
");
$stmt->execute([$user_id]);
$score = $stmt->fetch(PDO::FETCH_ASSOC);
$score_trimestre = $score['total_score'] ?? 0;


/* ===========================
   PROJETS RÉALISÉS (3 derniers mois séparés)
=========================== */

$stmt = $pdo->prepare("
    SELECT 
        MONTH(date_fin) as mois,
        COUNT(*) as total
    FROM projects
    WHERE user_id = ?
    AND statut = 'termine'
    AND date_fin IS NOT NULL
    AND date_fin >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    GROUP BY MONTH(date_fin)
");

$stmt->execute([$user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Initialiser 3 mois à 0 */
$mois_actuel = date('n');
$mois_1 = date('n', strtotime('-1 month'));
$mois_2 = date('n', strtotime('-2 month'));

$projets_mois = [
    $mois_2 => 0,
    $mois_1 => 0,
    $mois_actuel => 0
];

foreach($results as $row){
    $projets_mois[$row['mois']] = $row['total'];

}
$mois_noms = [
    1=>"Jan",2=>"Fev",3=>"Mar",4=>"Avr",5=>"Mai",6=>"Jun",
    7=>"Juil",8=>"Août",9=>"Sept",10=>"Oct",11=>"Nov",12=>"Déc"
];
/* ===========================
   PROJET ACTUEL
=========================== */

$stmt = $pdo->prepare("
    SELECT nom_projet, statut
    FROM projects
    WHERE user_id = ?
    AND statut = 'en_cours'
    ORDER BY date_creation DESC
    LIMIT 1
");

$stmt->execute([$user_id]);
$projet_actuel = $stmt->fetch(PDO::FETCH_ASSOC);
/* ===========================
   MODE DE TRAVAIL (3 derniers mois)
=========================== */

$stmt = $pdo->prepare("
    SELECT mode_travail
    FROM projects
    WHERE user_id = ?
    AND statut = 'termine'
    AND date_fin IS NOT NULL
    AND date_fin >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
");

$stmt->execute([$user_id]);
$modes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($modes);
$solo = 0;
$equipe = 0;

foreach($modes as $m){
    if($m['mode_travail'] == 'solo'){
        $solo++;
    } elseif($m['mode_travail'] == 'equipe'){
        $equipe++;
    }
}

$pourcentage_solo = $total > 0 ? round(($solo/$total)*100) : 0;
$pourcentage_equipe = $total > 0 ? round(($equipe/$total)*100) : 0;
?>

<h2>Tableau de bord</h2>

<p><strong>Score trimestriel :</strong> <?= $score_trimestre ?> pts</p>

<p><strong>Projets réalisés (3 derniers mois) :</strong></p>

<?php foreach($projets_mois as $mois => $total): ?>
    <p><?= $mois_noms[$mois] ?> : <?= $total ?></p>
<?php endforeach; ?>

<div class="card-projet-actuel">
    <h3>Projet actuel</h3>

    <?php if($projet_actuel): ?>
        <p><strong><?= htmlspecialchars($projet_actuel['nom_projet']) ?></strong></p>
        <p style="color:orange;">En cours</p>
    <?php else: ?>
        <p>Aucun projet en cours</p>
    <?php endif; ?>
</div>

<div class="card-mode-travail">
    <h3>Mode de travail</h3>

    <div style="display:flex; justify-content:space-between;">
        <span>Solo</span>
        <span><?= $pourcentage_solo ?>%</span>
    </div>

    <div style="display:flex; justify-content:space-between;">
        <span>Équipe</span>
        <span><?= $pourcentage_equipe ?>%</span>
    </div>
