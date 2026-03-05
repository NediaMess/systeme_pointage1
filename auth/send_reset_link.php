<?php
require_once "../lang_init.php";

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once "../config/database.php";

$email = $_POST['email'];

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if($user){

    // Générer token sécurisé
    $token = bin2hex(random_bytes(32));

    // Expiration 1 heure
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Enregistrer en base
    $insert = $pdo->prepare("INSERT INTO reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $insert->execute([$user['id'], $token, $expires_at]);

    // Lien (test local)
    $reset_link = "http://localhost/systeme_pointage/auth/reset_password.php?token=" . $token;

    $_SESSION['reset_message'] = "
    <h3>".$lang['reset_link_sent']."</h3>
    <p>".$lang['check_inbox']."</p>
    <p>".$lang['reset_link_valid']."</p>
    ";

} else {

    $_SESSION['reset_message'] = "
    <h3>".$lang['reset_failed']."</h3>
    <p>".$lang['check_email']."</p>
    <p>".$lang['contact_admin']."</p>
    ";
}

header("Location: forgot_password.php");
exit();