<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Paramètres › Profil utilisateur</h2>

<div class="info-card">
    <h3>Informations personnelles</h3>

    <p><strong>Nom :</strong> salma</p>
    <p><strong>Prénom :</strong> salma</p>
    <p><strong>Fonction :</strong> Métrologue industriel</p>
    <p><strong>Service :</strong> Métrologie</p>
</div>

<div class="info-card">
    <h3>Informations du compte</h3>

    <p><strong>Identifiant :</strong> salma@test.com</p>
    <p><strong>Date de création :</strong> 2026-02-18 13:41:56</p>
    <p><strong>Statut du compte :</strong> Actif</p>
</div>

<style>
.profile-section {
    margin-top: 30px;
    width: 600px;
}

.info-box {
    background: #dbe7ff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.info-box h3 {
    margin-top: 0;
}
</style>