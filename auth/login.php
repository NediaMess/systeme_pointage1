<?php
require_once "../lang_init.php";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $translations['login'] ?></title>
</head>
<body>

<h2><?= $translations['login'] ?></h2>

<form action="login_process.php" method="POST">

    <input type="email"
           name="email"
           placeholder="<?= $translations['email'] ?>"
           required>

    <br><br>

    <input type="password"
           name="password"
           placeholder="<?= $translations['password'] ?>"
           required>

    <br><br>

    <button type="submit">
        <?= $translations['login_button'] ?>
    </button>

</form>

<br>

<a href="forgot_password.php">
    <?= $translations['forgot_password'] ?>
</a>

<?php
if(isset($_SESSION['error'])){
    echo "<p style='color:red'>" . htmlspecialchars($_SESSION['error']) . "</p>";
    unset($_SESSION['error']);
}
?>

</body>
</html>