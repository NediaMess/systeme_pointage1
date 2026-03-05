<?php
require_once "../lang_init.php";

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once "../config/database.php";

$lang_code = $_SESSION['lang'] ?? 'fr';

$email = $_POST['email'];
$new_password = $_POST['new_password'];

$hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE email = ?");
$stmt->execute([$hash, $email]);

$_SESSION['message'] = "
<h3>".$lang['reset_link_sent']."</h3>
<p>".$lang['check_inbox']."</p>
<p>".$lang['reset_link_valid']."</p>
";

header("Location: forgot_password.php");
exit();