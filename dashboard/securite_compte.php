<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
    header("Location: ../auth/login.php");
    exit();
}

$message = "";

/* =============================
   UPDATE PASSWORD
============================= */

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $current_password = trim($_POST['current_password'] ?? '');
    $new_password     = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Récupérer mot de passe actuel depuis la base
    $stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        $message = "<p style='color:red;'>Utilisateur introuvable</p>";
    }

    // Vérifier mot de passe actuel
    elseif(!password_verify($current_password, $user['mot_de_passe'])){
        $message = "<p style='color:red;'>Mot de passe actuel incorrect</p>";
    }

    // Vérifier confirmation
    elseif($new_password !== $confirm_password){
        $message = "<p style='color:red;'>Les nouveaux mots de passe ne correspondent pas</p>";
    }

    // Vérifier longueur minimale
    elseif(strlen($new_password) < 6){
        $message = "<p style='color:red;'>Le mot de passe doit contenir au moins 6 caractères</p>";
    }

    // Vérifier que le nouveau mot de passe est différent
    elseif(password_verify($new_password, $user['mot_de_passe'])){
        $message = "<p style='color:red;'>Le nouveau mot de passe doit être différent de l'ancien</p>";
    }

    else{
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$new_hashed, $user_id]);

        $message = "<p style='color:green;'>Mot de passe modifié avec succès</p>";
    }
}

/* =============================
   LAST LOGIN
============================= */

$stmt = $pdo->prepare("SELECT last_login FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

$last_login = $user_data['last_login'] ?? null;
?>

<h2>
    <?= $lang['settings'] ?> &gt; 
    <?= $lang['account_security'] ?? 'Sécurité du compte' ?>
</h2>

<!-- ================= PASSWORD ================= -->

<div class="pref-box">

    <h3><?= $lang['password_section'] ?></h3>

    <?= $message ?>

    <form method="POST" style="margin-top:15px;">

        <input type="password"
               name="current_password"
               placeholder="<?= $lang['current_password'] ?>"
               required
               style="display:block; margin-bottom:10px; padding:6px; width:300px;">

        <input type="password"
               name="new_password"
               placeholder="<?= $lang['new_password'] ?>"
               required
               style="display:block; margin-bottom:10px; padding:6px; width:300px;">

        <input type="password"
               name="confirm_password"
               placeholder="<?= $lang['confirm_password'] ?>"
               required
               style="display:block; margin-bottom:15px; padding:6px; width:300px;">

        <button type="submit"
                style="padding:8px 18px;
                       border-radius:6px;
                       border:none;
                       background:#e53935;
                       color:white;
                       cursor:pointer;">
            <?= $lang['change_password'] ?>
        </button>

    </form>

</div>

<br>

<!-- ================= LAST LOGIN ================= -->

<div class="pref-box">

    <h3><?= $lang['security_sessions'] ?></h3>

    <p><strong><?= $lang['last_login'] ?> :</strong></p>

    <?php if($last_login): ?>
        <p style="color:green; font-weight:bold;">
            <?= date('d/m/Y - H:i', strtotime($last_login)) ?>
        </p>
    <?php else: ?>
        <p><?= $lang['no_information'] ?></p>
    <?php endif; ?>

</div>