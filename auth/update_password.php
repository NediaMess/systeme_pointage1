<?php
require_once "../lang_init.php";
session_start();
require_once "../config/database.php";

$token = $_POST['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Vérifier token valide
$stmt = $pdo->prepare("SELECT * FROM reset_tokens 
                       WHERE token = ? 
                       AND used = 0 
                       AND expires_at > NOW()");
$stmt->execute([$token]);
$tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$tokenData){
    die("Token invalide ou expiré.");
}

// Vérifier correspondance des mots de passe
if($new_password !== $confirm_password){
    die("Les mots de passe ne correspondent pas.");
}
$token = $_POST['token'];
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if($new_password !== $confirm_password){
    $_SESSION['error_message'] = "Les deux mots de passe ne sont pas identiques.";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}

// Hash sécurisé
$hash = password_hash($new_password, PASSWORD_DEFAULT);

// Mise à jour utilisateur AVEC LE HASH
$update = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
$update->execute([$hash, $tokenData['user_id']]);

// Marquer token comme utilisé
$markUsed = $pdo->prepare("UPDATE reset_tokens SET used = 1 WHERE id = ?");
$markUsed->execute([$tokenData['id']]);

$_SESSION['success_message'] = "Mot de passe mis à jour avec succès.";
header("Location: login.php");
exit();
