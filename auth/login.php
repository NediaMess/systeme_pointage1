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
</head>
<body>

<h2><?= htmlspecialchars($lang['login']) ?></h2>

<form action="login_process.php" method="POST">

    <div>
        <label><?= htmlspecialchars($lang['email']) ?> :</label><br>
        <input type="email"
               name="email"
               required>
    </div>

    <br>

    <div>
        <label><?= htmlspecialchars($lang['password']) ?> :</label><br>
        <input type="password"
               name="password"
               required>
    </div>

    <br>

    <button type="submit">
        <?= htmlspecialchars($lang['login_button']) ?>
    </button>

</form>

<br>

<a href="forgot_password.php">
    <?= htmlspecialchars($lang['forgot_password']) ?>
</a>
<br><br>

<a href="register.php">
    Créer un compte
</a>

<?php if(isset($_SESSION['error'])): ?>
    <p style="color:red;">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

</body>
</html>