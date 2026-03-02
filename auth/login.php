<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once "../lang_init.php";
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'fr') ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($lang['login']) ?></title>

    <!-- LIAISON CSS -->
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>

<div class="container">
    <!-- LEFT SIDE -->
    <div class="login-box">
        <?php if(isset($_SESSION['success_message'])): ?>
    <div class="success-alert">
        <?= $_SESSION['success_message']; ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

        <h1>CM2E | Pointage intelligent</h1>

        <form action="login_process.php" method="POST">

            <label><?= htmlspecialchars($lang['email']) ?></label>
            <input type="email" name="email" required>

            <label><?= htmlspecialchars($lang['password']) ?></label>
            <input type="password" name="password" required>

            <button type="submit" class="btn-login">
                <?= htmlspecialchars($lang['login_button']) ?>
            </button>

        </form>

        <a href="forgot_password.php" class="forgot">
            <?= htmlspecialchars($lang['forgot_password']) ?>
        </a>

        <a href="register.php" class="forgot">
            Créer un compte
        </a>

        <?php if(isset($_SESSION['error'])): ?>
            <p class="login-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

    </div>

    <!-- RIGHT SIDE -->
    <div class="right-box">
        <div class="slogan">
            Simplicité. Fiabilité. Performance.
        </div>

        <div class="logo">
    <img src="/systeme_pointage/public/assets/img/logocm2e.png" alt="CM2E Logo">
</div>
    </div>

</div>

</body>
</html>