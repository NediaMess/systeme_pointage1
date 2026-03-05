<?php
session_start();

require_once "../lang_init.php";
require_once "../config/database.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'metrologue'){
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: index.php?page=projet_courant");
    exit();
}

$project_id = $_GET['id'];

// Sécurité : vérifier que le projet appartient au métrologue
$stmt = $pdo->prepare("
    SELECT id FROM projects 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$project_id, $_SESSION['user_id']]);

if(!$stmt->fetch()){
    die($lang['invalid_project']);
}

// Mettre à jour le projet
$stmt = $pdo->prepare("
    UPDATE projects 
    SET start_time = NOW(),
        statut = 'en_cours'
    WHERE id = ?
");

$stmt->execute([$project_id]);

header("Location: index.php?page=projet_courant");
exit();