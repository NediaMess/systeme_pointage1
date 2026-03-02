<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../lang_init.php";
require_once "../config/database.php";

// Récupérer token
$token = $_GET['token'] ?? '';

if(empty($token)){
    die("Token manquant.");
}

// Vérifier token valide
$stmt = $pdo->prepare("SELECT * FROM reset_tokens WHERE token = ? AND used = 0 AND expires_at > NOW()");
$stmt->execute([$token]);
$tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$tokenData){
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lien invalide</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>

<div class="container">
    <div class="login-box">

        <div class="success-container">

            <div class="success-icon" style="background:#fdecea; color:#c62828;">!</div>

            <h2 class="success-title" style="color:#c62828;">
                Lien invalide ou expiré
            </h2>

            <p class="success-subtext">
                Ce lien de réinitialisation n'est plus valide.<br>
                Veuillez demander un nouveau lien.
            </p>

            <a href="forgot_password.php" class="btn-login success-btn">
                Demander un nouveau lien
            </a>

        </div>

    </div>

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
<?php
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>

<div class="container">

    <!-- LEFT SIDE -->
    <div class="login-box">

        <h1>Réinitialiser le mot de passe</h1>

        <form action="update_password.php" method="POST" class="reset-form">

    <div class="reset-icon">🔒</div>

    <p class="reset-description">
        Veuillez saisir votre nouveau mot de passe sécurisé.
    </p>

    <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

<div class="input-group password-group">
    <label>Nouveau mot de passe</label>
    <div class="password-wrapper">
        <input type="password" name="new_password" id="newPassword" required>
        <button type="button" class="toggle-password" onclick="togglePassword('newPassword', this)">
            <svg class="eye-icon"
                 xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5
                         c4.477 0 8.268 2.943 9.542 7
                         -1.274 4.057-5.065 7-9.542 7
                         -4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>
    </div>
    <div class="password-strength" id="strengthText"></div>
</div>

<div class="input-group password-group">
    <label>Confirmer le mot de passe</label>
    <div class="password-wrapper">
        <input type="password" name="confirm_password" id="confirmPassword" required>
        <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword', this)">
            <svg class="eye-icon"
                 xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5
                         c4.477 0 8.268 2.943 9.542 7
                         -1.274 4.057-5.065 7-9.542 7
                         -4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>
    </div>
    <div class="password-match" id="matchText"></div>
</div>

<button type="submit" class="btn-login">
    Valider
</button>

</form>
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

<script>
const password = document.getElementById('newPassword');
const confirmPassword = document.getElementById('confirmPassword');
const strengthText = document.getElementById('strengthText');
const matchText = document.getElementById('matchText');

password.addEventListener('input', () => {
    const value = password.value;
    if (value.length < 6) {
        strengthText.textContent = "Mot de passe faible";
        strengthText.style.color = "red";
    } else if (value.length < 10) {
        strengthText.textContent = "Mot de passe moyen";
        strengthText.style.color = "orange";
    } else {
        strengthText.textContent = "Mot de passe fort";
        strengthText.style.color = "green";
    }
});

confirmPassword.addEventListener('input', () => {
    if (confirmPassword.value !== password.value) {
        matchText.textContent = "Les mots de passe ne correspondent pas";
        matchText.style.color = "red";
    } else {
        matchText.textContent = "Les mots de passe correspondent";
        matchText.style.color = "green";
    }
});
</script>
<script>
function togglePassword(fieldId, button) {
    const input = document.getElementById(fieldId);
    const icon = button.querySelector('.eye-icon');

    if (input.type === "password") {
        input.type = "text";
        icon.style.color = "#3f7de0";
    } else {
        input.type = "password";
        icon.style.color = "#888";
    }
}
</script>
</body>
</html>