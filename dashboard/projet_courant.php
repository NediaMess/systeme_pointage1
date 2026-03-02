<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
    header("Location: ../auth/login.php");
    exit();
}
if(isset($_POST['terminer_projet'])){

    // 1️⃣ Récupérer le projet en cours
    $stmt = $pdo->prepare("
        SELECT * FROM projects
        WHERE user_id = ?
        AND statut = 'en_cours'
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $projet = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$projet){
        die("Aucun projet en cours.");
    }

    // 2️⃣ Calcul du temps réel
    $start = strtotime($projet['start_time']);
    $end = time();
    $real_time = round(($end - $start) / 60);
    $estimated = $projet['estimated_time'];

    // 3️⃣ Calcul du score
    if($real_time == $estimated){
        $score = 0;
    }
    elseif($real_time < $estimated){
        $score = 1;
    }
    else{
        $score = -2;
    }

    // 4️⃣ Mise à jour du projet
    $stmt = $pdo->prepare("
        UPDATE projects
        SET statut = 'Terminé',
            date_fin = NOW(),
            score_ajoute = ?
        WHERE id = ?
    ");
    $stmt->execute([$score, $projet['id']]);

    // 5️⃣ Ajouter score au calendrier
    $stmt = $pdo->prepare("
        INSERT INTO daily_scores (user_id, project_id, score, date_score)
        VALUES (?, ?, ?, CURDATE())
    ");
    $stmt->execute([$user_id, $projet['id'], $score]);

    header("Location: index.php?page=projet_courant");
    exit();
}
/* ==============================
   RÉCUPÉRER PROJET (OBLIGATOIRE)
============================== */

// Chercher projet en cours
$stmt = $pdo->prepare("
    SELECT * FROM projects
    WHERE user_id = ?
    AND statut = 'en_cours'
    ORDER BY date_creation DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$projet = $stmt->fetch(PDO::FETCH_ASSOC);

// Sinon chercher projet en attente
if(!$projet){
    $stmt = $pdo->prepare("
        SELECT * FROM projects
        WHERE user_id = ?
        AND statut = 'en_attente'
        ORDER BY date_creation ASC
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $projet = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Si aucun projet
if(!$projet){
    echo "<h3>Aucun projet en cours</h3>";
    return;
}
/* ==============================
   RÉCUPÉRER RAPPORT (SÉCURISÉ)
============================== */

$report = null;

if($projet){

    $stmt = $pdo->prepare("
        SELECT * FROM project_reports
        WHERE project_id = ?
    ");
    $stmt->execute([$projet['id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$report){
        $stmt = $pdo->prepare("
            INSERT INTO project_reports (project_id)
            VALUES (?)
        ");
        $stmt->execute([$projet['id']]);

        $stmt = $pdo->prepare("
            SELECT * FROM project_reports
            WHERE project_id = ?
        ");
        $stmt->execute([$projet['id']]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/* ==============================
   RÉCUPÉRER RAPPORT
============================== */

if($projet){

    $stmt = $pdo->prepare("
        SELECT * FROM project_reports
        WHERE project_id = ?
    ");
    $stmt->execute([$projet['id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$report){
        $stmt = $pdo->prepare("
            INSERT INTO project_reports (project_id)
            VALUES (?)
        ");
        $stmt->execute([$projet['id']]);

        $stmt = $pdo->prepare("
            SELECT * FROM project_reports
            WHERE project_id = ?
        ");
        $stmt->execute([$projet['id']]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
    }

}

if(!$report){
    $stmt = $pdo->prepare("
        INSERT INTO project_reports (project_id)
        VALUES (?)
    ");
    $stmt->execute([$projet['id']]);

    $stmt = $pdo->prepare("
        SELECT * FROM project_reports
        WHERE project_id = ?
    ");
    $stmt->execute([$projet['id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
}
/* ==============================
   UPDATE MODE TRAVAIL
============================== */

if(isset($_POST['mode_travail'])){

    $mode = $_POST['mode_travail'];

    if(in_array($mode, ['solo','equipe'])){
        $stmt = $pdo->prepare("
            UPDATE projects
            SET mode_travail = ?
            WHERE id = ?
        ");
        $stmt->execute([$mode, $projet['id']]);
    }

    header("Location: index.php?page=projet_courant");
    exit();
}
/* ==============================
   TOGGLE CHECKLIST
============================== */

if(isset($_POST['toggle_checklist'])){

    $stmt = $pdo->prepare("
        UPDATE projects
        SET checklist_rempli = NOT checklist_rempli
        WHERE id = ?
    ");
    $stmt->execute([$projet['id']]);

    header("Location: index.php?page=projet_courant");
    exit();
}

/* ==============================
   TOGGLE RAPPORT
============================== */

if(isset($_POST['toggle_report'])){

    $field = $_POST['toggle_report'];

    $allowed = ['finaliser','verifier','envoyer','commande'];

    if(in_array($field, $allowed)){
        $stmt = $pdo->prepare("
            UPDATE project_reports
            SET $field = NOT $field
            WHERE project_id = ?
        ");
        $stmt->execute([$projet['id']]);
    }

    header("Location: index.php?page=projet_courant");
    exit();
}

/* ==============================
   UPDATE DESCRIPTION
============================== */

if(isset($_POST['description'])){

    $description = $_POST['description'];

    $update = $pdo->prepare("
        UPDATE projects
        SET description = ?
        WHERE id = ?
    ");
    $update->execute([$description, $projet['id']]);

    header("Location: index.php?page=projet_courant");
    exit();
}
?>

<h2>
    Projet courant : 
    <p style="color:red;">
STATUT ACTUEL : <?= $projet['statut']; ?>
</p>
    <span style="color:green;">
        <?= htmlspecialchars($projet['nom_projet']) ?>
    </span>
</h2>
<?php if(trim(strtolower($projet['statut'])) == 'en_attente'): ?>

<button type="button"
        onclick="openStartModal()"
        style="padding:6px 16px;
               background:#1976d2;
               color:white;
               border:none;
               border-radius:6px;
               cursor:pointer;
               margin-top:10px;">
    ▶ Commencer le projet
</button>

<?php endif; ?>
<?php if($projet['statut'] == 'en_cours'): ?>

<?php
$estimated_seconds = $projet['estimated_time'] * 60;
$start = strtotime($projet['start_time']);
$now = time();

$elapsed = $now - $start;
$remaining = $estimated_seconds - $elapsed;

$is_late = $remaining < 0;
?>

<h3 style="color: <?= $is_late ? 'red' : 'green' ?>;">
    Temps restant :
    <?= gmdate("H:i:s", abs($remaining)) ?>
</h3>

<?php endif; ?>
<br>

<p>
   <h3>Mode de travail :</h3>

<form method="POST">

    <label>
        <input type="radio"
               name="mode_travail"
               value="solo"
               onchange="this.form.submit()"
               <?= $projet['mode_travail'] === 'solo' ? 'checked' : '' ?>>
        Solo
    </label>

    <br>

    <label>
        <input type="radio"
               name="mode_travail"
               value="equipe"
               onchange="this.form.submit()"
               <?= $projet['mode_travail'] === 'equipe' ? 'checked' : '' ?>>
        En équipe
    </label>

</form>
</p>

<br>

<h3>CheckList :</h3>

<form method="POST">
    <input type="hidden" name="toggle_checklist" value="1">
    <input type="checkbox"
           onchange="this.form.submit()"
           <?= !empty($projet['checklist_rempli']) ? 'checked' : '' ?>>
</form>

<span style="color: <?= !empty($projet['checklist_rempli']) ? 'green' : 'red' ?>;">
    Rempli
</span>

<br><br>

<h3>Rapport :</h3>

<?php
$rapports = [
    'finaliser' => 'Finaliser',
    'verifier' => 'Verifier',
    'envoyer' => 'Envoyer',
    'commande' => 'Commande'
];

foreach($rapports as $field => $label):
?>

<form method="POST" style="display:inline;">
    <input type="hidden" name="toggle_report" value="<?= $field ?>">
    <input type="checkbox"
           onchange="this.form.submit()"
           <?= !empty($report[$field]) ? 'checked' : '' ?>>
</form>

<span style="color: <?= !empty($report[$field]) ? 'blue' : 'black' ?>;">
    <?= $label ?>
</span>

<br>

<?php endforeach; ?>

<br>

<!-- ================= DESCRIPTION ================= -->

<form method="POST">

    <label><strong>Description (CheckList) :</strong></label><br><br>

     <textarea name="description"
          rows="5"
          style="width:400px; padding:8px;"
          placeholder="Veuillez écrire ici vos remarques concernant la checklist..."
          required><?= htmlspecialchars($projet['description'] ?? '') ?></textarea>

    <br><br>

    <button type="submit"
            style="padding:6px 16px;
                   background:#e53935;
                   color:white;
                   border:none;
                   border-radius:6px;
                   cursor:pointer;">
        Envoyer
    </button>

</form>
<form method="POST" onsubmit="return confirm('Êtes-vous sûr d\'avoir terminé ce projet ?');">

    <input type="hidden" name="terminer_projet" value="1">

    <button type="button"
        onclick="openModal()"
        style="padding:6px 16px;
               background:#2e7d32;
               color:white;
               border:none;
               border-radius:6px;
               cursor:pointer;">
    Terminer le projet
</button>

</form>
<!-- ================= MODALE TERMINER ================= -->

<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h3>Confirmer</h3>
        <p>Êtes-vous sûr d'avoir terminé ce projet ?</p>

        <div class="modal-buttons">
            <form method="POST">
                <input type="hidden" name="terminer_projet" value="1">
                <button type="submit" class="btn-confirm">
                    Oui, terminer
                </button>
            </form>

            <button onclick="closeModal()" class="btn-cancel">
                Annuler
            </button>
        </div>
    </div>
</div>

<!-- ================= MODALE START ================= -->

<div id="startModal" class="modal">
    <div class="modal-content">
        <h3>Confirmer</h3>
        <p>Le chrono va commencer pour ce projet.</p>

        <div class="modal-buttons">
            <form method="GET" action="start_project.php">
                <input type="hidden" name="id" value="<?= $projet['id'] ?>">
                <button type="submit" class="btn-confirm">
                    Oui, commencer
                </button>
            </form>

            <button onclick="closeStartModal()" class="btn-cancel">
                Annuler
            </button>
        </div>
    </div>
</div>
</div>
<script>
function openModal(){
    document.getElementById("confirmModal").style.display = "flex";
}

function closeModal(){
    document.getElementById("confirmModal").style.display = "none";
}
function openStartModal(){
    document.getElementById("startModal").style.display = "flex";
}

function closeStartModal(){
    document.getElementById("startModal").style.display = "none";
}
</script>
<?php if($projet['statut'] == 'en_attente'): ?>

<button type="button"
        onclick="openStartModal()"
        style="padding:6px 16px;
               background:#1976d2;
               color:white;
               border:none;
               border-radius:6px;
               cursor:pointer;">
    ▶ Commencer le projet
</button>

<?php endif; ?>