<?php

require_once "../lang_init.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'fr') ?>">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>

<div class="container">

    <!-- LEFT SIDE -->
    <div class="login-box">

        <h1>Mot de passe oublié</h1>

        <?php if(isset($_SESSION['reset_message'])): ?>

    <div class="success-container">

        <div class="success-icon">✓</div>

        <h2 class="success-title">
            Lien envoyé avec succès
        </h2>

        <p class="success-text">
            <?= $_SESSION['reset_message']; ?>
        </p>

        <p class="success-subtext">
            Veuillez vérifier votre boîte de réception.<br>
            Le lien est valable pendant <strong>une durée limitée</strong>.
        </p>

        <a href="login.php" class="btn-login success-btn">
            Retour à la connexion
        </a>

    </div>

    <?php unset($_SESSION['reset_message']); ?>

<?php else: ?>

            <form action="send_reset_link.php" method="POST">

                <p style="margin-bottom:25px; text-align:center; color:#666;">
                    Veuillez saisir votre adresse e-mail.
                    Un lien de réinitialisation vous sera envoyé.
                </p>

                <label>Email</label>
                <input type="email" name="email" placeholder="Votre email" required>

                <button type="submit" class="btn-login">
                    Envoyer le lien
                </button>

            </form>

        <?php endif; ?>

        <a href="login.php" class="forgot">
            Retour à la connexion
        </a>

    </div>

    <!-- RIGHT SIDE -->
    <div class="right-box">
        <div class="slogan">
            Simplicité. Fiabilité. Performance.
        </div>

        <div class="logo">
            <img src="../public/assets/img/logocm2e.png" alt="CM2E Logo">
        </div>
    </div>

</div>

</body>
</html>