<?php
session_start();
require_once "../config/database.php";

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}
?>

<h2>Bienvenue Admin</h2>

<br>

<a href="create_project.php">➕ Créer un nouveau projet</a>

<br><br>

<a href="../auth/logout.php">🚪 Déconnexion</a>