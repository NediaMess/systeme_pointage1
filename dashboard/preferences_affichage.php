<?php
require_once "../lang_init.php";
?>

<h2>
    <?= $lang['settings'] ?> &gt;
    🎨 <?= $lang['display_preferences'] ?>
</h2>

<form method="POST">

    <!-- ================= THEME ================= -->
    <div class="pref-box">
        <h3><?= $lang['theme'] ?></h3>

        <label>
            <input type="radio" name="theme" value="light"
            <?= (($_SESSION['theme'] ?? 'light') == 'light') ? 'checked' : '' ?>>
            <?= $lang['light'] ?>
        </label>

        <label style="margin-left:20px;">
            <input type="radio" name="theme" value="dark"
            <?= (($_SESSION['theme'] ?? 'light') == 'dark') ? 'checked' : '' ?>>
            <?= $lang['dark'] ?>
        </label>
    </div>

    <br>

    <!-- ================= LANGUAGE ================= -->
    <div class="pref-box">

        <h3><?= $lang['language_section'] ?></h3>

        <p><strong><?= $lang['language'] ?> :</strong></p>

        <select name="lang">
            <option value="fr"
                <?= ($_SESSION['lang'] ?? 'fr') == 'fr' ? 'selected' : '' ?>>
                Français
            </option>

            <option value="en"
                <?= ($_SESSION['lang'] ?? 'fr') == 'en' ? 'selected' : '' ?>>
                English
            </option>
        </select>

        <br><br>

        <!-- ================= TEXT SIZE ================= -->

        <p><strong><?= $lang['text_size'] ?> :</strong></p>

        <label>
            <input type="radio" name="taille" value="normal"
            <?= (($_SESSION['taille'] ?? 'normal') == 'normal') ? 'checked' : '' ?>>
            <?= $lang['normal'] ?>
        </label>

        <label style="margin-left:20px;">
            <input type="radio" name="taille" value="grand"
            <?= (($_SESSION['taille'] ?? 'normal') == 'grand') ? 'checked' : '' ?>>
            <?= $lang['large'] ?>
        </label>

    </div>

    <br>

    <button type="submit">
        <?= $lang['save'] ?>
    </button>

</form>