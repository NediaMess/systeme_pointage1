<?php
require_once "../lang_init.php";
?>

<h2><?= $lang['settings'] ?></h2>


<div class="settings-container">

    <a href="?page=profil_utilisateur" class="settings-card">
        👤 Profil utilisateur
    </a>

    <a href="?page=preferences_affichage" class="settings-card">
        🎨 Préférences d'affichage
    </a>

    <a href="?page=param_calendrier" class="settings-card">
        📅 Paramètres du calendrier
    </a>

    <a href="?page=securite_compte" class="settings-card">
        🔐 Sécurité du compte
    </a>

    <a href="?page=apropos" class="settings-card">
        🧩 À propos
    </a>

</div>

<hr style="margin:30px 0;">

<?php
