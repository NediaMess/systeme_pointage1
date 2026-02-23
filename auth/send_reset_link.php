<?php
require_once "../lang_init.php";
session_start();
require_once "../config/database.php";

$email = $_POST['email'];

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if($user){
      //  Générer token sécurisé
    $token = bin2hex(random_bytes(32));
    // Expiration 1 heure
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));
     //  Enregistrer en base
    $insert = $pdo->prepare("INSERT INTO reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $insert->execute([$user['id'], $token, $expires_at]);
    // 🔗 Lien (pour test)
    $reset_link = "http://localhost/systeme_pointage/auth/reset_password.php?token=" . $token;

    $_SESSION['reset_message'] = "
    <h3>Lien envoyé</h3>
    <p>Veuillez vérifier votre boîte de réception.</p>
    <p>Le lien est valable pendant une durée limitée.</p>
    ";

} else {

    $_SESSION['reset_message'] = "
    <h3>Aucun lien de réinitialisation n’a pu être envoyé</h3>
    <p>Veuillez vérifier l’identifiant ou l’adresse e-mail saisi(e),</p>
    <p>ou contacter l’administrateur du système.</p>
    ";
}
header("Location: forgot_password.php");
exit();
