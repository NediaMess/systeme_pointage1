<?php
require_once "../lang_init.php";
session_start();
require_once "../config/database.php";

// Récupérer token
$token = $_GET['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if(empty($token)){
    die("Token manquant.");
}

// Vérifier token valide
$stmt = $pdo->prepare("SELECT * FROM reset_tokens WHERE token = ? AND used = 0 AND expires_at > NOW()");
$stmt->execute([$token]);
$tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$tokenData){
    echo "<h3>Lien invalide ou expiré.</h3>";
    echo "<a href='forgot_password.php'>Demander un nouveau lien</a>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Réinitialiser le mot de passe</title>
</head>
<body>

<h2>Réinitialiser le mot de passe</h2>

<p>Veuillez saisir votre nouveau mot de passe</p>

<form action="update_password.php" method="POST">

    <!-- Token caché -->
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

    <label>Nouveau mot de passe</label><br>
    <input type="password" name="new_password" placeholder="Nouveau mot de passe" required><br><br>

    <label>Confirmer le mot de passe</label><br>
    <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required><br><br>

    <button type="submit">Valider</button>

</form>

</body>
</html>
