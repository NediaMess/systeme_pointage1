<?php  
require_once "../lang_init.php";
session_start(); ?>

<h2>Mot de passe oublié</h2>

<?php
if(isset($_SESSION['reset_message'])){
    echo $_SESSION['reset_message'];
    unset($_SESSION['reset_message']);
} else {
?>

<form action="send_reset_link.php" method="POST">
    <input type="email" name="email" placeholder="Votre email" required>
    <br><br>
    <button type="submit">Envoyer le lien</button>
</form>

<?php } ?>

<br>
<a href="login.php">Retour à la connexion</a>
