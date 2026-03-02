<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../lang_init.php";
require_once "../config/database.php";

$token = $_POST['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Vérification basique
if (empty($token) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error_message'] = "Veuillez remplir tous les champs.";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}

// Vérifier correspondance des mots de passe
if ($new_password !== $confirm_password) {
    $_SESSION['error_message'] = "Les deux mots de passe ne sont pas identiques.";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}

// Vérifier token valide
$stmt = $pdo->prepare("
    SELECT * FROM reset_tokens 
    WHERE token = ? 
    AND used = 0 
    AND expires_at > NOW()
");
$stmt->execute([$token]);
$tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tokenData) {
    $_SESSION['error_message'] = "Le lien est invalide ou expiré.";
    header("Location: forgot_password.php");
    exit();
}

// Hash sécurisé
$hash = password_hash($new_password, PASSWORD_DEFAULT);

// Mise à jour utilisateur
$update = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
$update->execute([$hash, $tokenData['user_id']]);

// Marquer token comme utilisé
$markUsed = $pdo->prepare("UPDATE reset_tokens SET used = 1 WHERE id = ?");
$markUsed->execute([$tokenData['id']]);

// Message succès
$_SESSION['success_message'] = "Votre mot de passe a été modifié avec succès.";
header("Location: login.php");
exit();