<?php
session_start();
require_once "../config/database.php";

$message = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(strlen($password) < 6){
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {

        // Vérifier email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if($stmt->fetch()){
            $message = "Cet email existe déjà.";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (nom, prenom, email, mot_de_passe, role, date_creation)
                VALUES (?, ?, ?, ?, 'metrologue', NOW())
            ");

            $stmt->execute([$nom, $prenom, $email, $hashed]);

            header("Location: login.php");
            exit();
        }
    }
}
?>

<h2>Créer un compte</h2>

<?php if($message): ?>
<p style="color:red;"><?= $message ?></p>
<?php endif; ?>

<form method="POST">

    <input type="text" name="nom" placeholder="Nom" required><br><br>

    <input type="text" name="prenom" placeholder="Prénom" required><br><br>

    <input type="email" name="email" placeholder="Email" required><br><br>

    <input type="password" name="password" placeholder="Mot de passe" required><br><br>

    <button type="submit">S'inscrire</button>

</form>

<br>

<a href="login.php">Retour à la connexion</a>