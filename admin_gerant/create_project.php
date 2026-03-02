<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// récupérer les métrologues
$stmt = $pdo->prepare("SELECT id, nom, prenom FROM users WHERE role = 'metrologue'");
$stmt->execute();
$metrologues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Créer un projet</h2>

<form method="POST" action="store_project.php">

    <label>Nom du projet :</label><br>
    <input type="text" name="nom_projet" required><br><br>

    <label>Choisir Métrologue :</label><br>
    <select name="user_id" required>
        <option value="">-- Sélectionner --</option>
        <?php foreach($metrologues as $m): ?>
            <option value="<?= htmlspecialchars($m['id']) ?>">
                <?= htmlspecialchars($m['nom']) ?> <?= htmlspecialchars($m['prenom']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <strong>Temps estimé :</strong><br><br>

    <label>Jours :</label><br>
    <input type="number" name="days" min="0" value="0"><br><br>

    <label>Heures :</label><br>
    <input type="number" name="hours" min="0" max="23" value="0"><br><br>

    <label>Minutes :</label><br>
    <input type="number" name="minutes" min="0" max="59" value="0"><br><br>

    <button type="submit">Créer</button>

</form>

<br>
<a href="dashboard.php">⬅ Retour Dashboard</a>