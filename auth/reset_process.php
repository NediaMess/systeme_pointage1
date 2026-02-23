<?php
require_once "../lang_init.php";
session_start();
require_once "../config/database.php";

$email = $_POST['email'];
$new_password = $_POST['new_password'];

$hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE email = ?");
$stmt->execute([$hash, $email]);

$_SESSION['message'] = "<h3>Lien envoyé</h3>
<p>Veuillez vérifier votre boîte de réception</p>
<p>Le lien est valable pendant une durée limitée</p>
";
header("Location: forgot_password.php");
exit();
