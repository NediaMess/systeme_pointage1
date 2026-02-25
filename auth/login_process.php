<?php
require_once "../lang_init.php";
session_start();
require_once "../config/database.php";

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if(empty($email) || empty($password)){
    $_SESSION['error'] = "Veuillez remplir tous les champs.";
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user && password_verify($password, $user['mot_de_passe'])){

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_prenom'] = $user['prenom'];
    // Enregistrer la dernière connexion
    $stmt2 = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$stmt2->execute([$user['id']]);

    header("Location: ../dashboard/index.php");
    exit();

} else {
    $_SESSION['error'] = "Identifiant ou mot de passe incorrect";
    header("Location: login.php");
    exit();
}
