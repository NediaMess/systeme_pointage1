<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// Vérifier que le formulaire est bien envoyé
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: dashboard.php");
    exit();
}

$nom_projet = trim($_POST['nom_projet'] ?? '');
$user_id = $_POST['user_id'] ?? null;

$days = (int)($_POST['days'] ?? 0);
$hours = (int)($_POST['hours'] ?? 0);
$minutes = (int)($_POST['minutes'] ?? 0);

if(empty($nom_projet) || empty($user_id)){
    die("Données invalides.");
}

$minutes_per_day = 8 * 60; // 1 jour = 8 heures

$estimated_time = ($days * $minutes_per_day)
                + ($hours * 60)
                + $minutes;

// Sécurité : vérifier que le user sélectionné est bien un métrologue
$checkUser = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'metrologue'");
$checkUser->execute([$user_id]);

if(!$checkUser->fetch()){
    die("Métrologue invalide.");
}

// INSERT PROPRE
$stmt = $pdo->prepare("
    INSERT INTO projects 
    (user_id, nom_projet, estimated_time, statut, date_creation)
    VALUES (?, ?, ?, 'en_attente', NOW())
");

$stmt->execute([$user_id, $nom_projet, $estimated_time]);

header("Location: dashboard.php");
exit();