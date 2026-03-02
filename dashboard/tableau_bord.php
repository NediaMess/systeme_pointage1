<?php
require_once "../lang_init.php";
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= SCORE TRIMESTRIEL ================= */
$stmt = $pdo->prepare("
    SELECT SUM(score) as total_score
    FROM daily_scores
    WHERE user_id = ?
    AND date_score >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
");
$stmt->execute([$user_id]);
$score = $stmt->fetch(PDO::FETCH_ASSOC);
$score_trimestre = $score['total_score'] ?? 0;


/* ================= PROJETS RÉALISÉS ================= */

/* 1️⃣ Définir les 3 mois à afficher */
$mois_selectionnes = $_SESSION['mois_selectionnes'] ?? [
    date('n', strtotime('-2 month')),
    date('n', strtotime('-1 month')),
    date('n')
];

/* 2️⃣ Préparer tableau vide pour éviter bug affichage */
$projets_mois = [];
foreach ($mois_selectionnes as $mois) {
    $projets_mois[$mois] = 0;
}

/* 3️⃣ Construire placeholders dynamiques */
$placeholders = implode(',', array_fill(0, count($mois_selectionnes), '?'));

/* 4️⃣ Requête propre limitée aux 3 mois */
$stmt = $pdo->prepare("
    SELECT MONTH(date_fin) as mois, COUNT(*) as total
    FROM projects
    WHERE user_id = ?
    AND statut = 'terminé'
    AND date_fin IS NOT NULL
    AND MONTH(date_fin) IN ($placeholders)
    GROUP BY MONTH(date_fin)
");

/* 5️⃣ Exécuter avec paramètres */
$params = array_merge([$user_id], $mois_selectionnes);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* 6️⃣ Remplir tableau avec résultats */
foreach($results as $row){
    $projets_mois[$row['mois']] = $row['total'];
}
$mois_noms = [
    1=>"Jan",2=>"Fev",3=>"Mar",4=>"Avr",5=>"Mai",6=>"Jun",
    7=>"Juil",8=>"Août",9=>"Sept",10=>"Oct",11=>"Nov",12=>"Déc"
];


/* ================= PROJET ACTUEL ================= */
$stmt = $pdo->prepare("
    SELECT nom_projet, statut
    FROM projects
    WHERE user_id = ?
    AND statut IN ('en_attente', 'en_cours')
    ORDER BY 
        CASE 
            WHEN statut = 'en_cours' THEN 1
            WHEN statut = 'en_attente' THEN 2
        END,
        date_creation DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$projet_actuel = $stmt->fetch(PDO::FETCH_ASSOC);


/* ================= MODE DE TRAVAIL ================= */
/* ================= MODE DE TRAVAIL ================= */

$stmt = $pdo->prepare("
    SELECT mode_travail
    FROM projects
    WHERE user_id = ?
    AND statut = 'Terminé'
    AND date_fin IS NOT NULL
    AND date_fin >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
");

$stmt->execute([$user_id]);
$modes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($modes);
$solo = 0;
$equipe = 0;

foreach($modes as $m){
    if($m['mode_travail'] === 'solo'){
        $solo++;
    } elseif($m['mode_travail'] === 'equipe'){
        $equipe++;
    }
}

$pourcentage_solo = $total > 0 ? round(($solo/$total)*100) : 0;
$pourcentage_equipe = $total > 0 ? round(($equipe/$total)*100) : 0;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>

    <link rel="stylesheet" href="/systeme_pointage/public/assets/css/style.css?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="main-content">

  <div class="page-header">
    <h1>Tableau de bord</h1>
</div>

    <div class="cards">

<div class="performance-card">
    <h2 class="performance-title">Performance trimestrielle</h2>

    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>
        <span class="progress-score" id="progressText">0%</span>
    </div>
</div>
<script>
function setProgress(score) {
    const fill = document.getElementById("progressFill");
    const text = document.getElementById("progressText");

    fill.style.width = score + "%";

    let current = 0;
    const interval = setInterval(() => {
        if (current >= score) {
            clearInterval(interval);
        } else {
            current++;
            text.textContent = current + "%";
        }
    }, 15);
}

/* Exemple : */
setProgress(<?= min($score_trimestre,100) ?>);
</script>
<!-- PROJETS RÉALISÉS -->
<div class="projects-card">
    <h3 class="projects-title">Projets réalisés</h3>

    <?php foreach($projets_mois as $mois => $total): ?>
        <div class="project-row">
            
            <div class="project-header">
                <span class="month"><?= $mois_noms[$mois] ?></span>
                <span class="project-count"><?= $total ?></span>
            </div>

            <div class="mini-bar">
                <div class="mini-progress"
                     style="width: <?= min($total*15,100) ?>%">
                </div>
            </div>

        </div>
    <?php endforeach; ?>
</div>
        <!-- PROJET ACTUEL -->
        <!-- PROJET ACTUEL -->
<div class="current-project-card">

    <h3 class="current-title">Projet actuel</h3>

    <?php if($projet_actuel): ?>

        <!-- NOM DU PROJET -->
        <div class="project-code">
            <?= htmlspecialchars($projet_actuel['nom_projet']) ?>
        </div>

        <!-- STATUS SOUS LE NOM -->
        <div class="project-status">
            <?= ucfirst(str_replace('_',' ', $projet_actuel['statut'])) ?>
        </div>

    <?php else: ?>

        <div class="project-status">Aucun projet</div>

    <?php endif; ?>

</div>


        <!-- MODE DE TRAVAIL -->
        <!-- MODE DE TRAVAIL -->
<div class="card mode-travail">
    <h3>Mode de travail par trimestre</h3>

    <div class="circle-container">

        <!-- SOLO -->
        <div class="progress-circle solo" 
             style="--value: <?= $pourcentage_solo ?>;">
            <span><?= $pourcentage_solo ?>%</span>
            <p>Solo</p>
        </div>

        <!-- EQUIPE -->
        <div class="progress-circle team" 
             style="--value: <?= $pourcentage_equipe ?>;">
            <span><?= $pourcentage_equipe ?>%</span>
            <p>En équipe</p>
        </div>

    </div>
</div>
</div>
</body>
</html>