<?php
require_once "../lang_init.php";
?>

<h2>
    <?= $translations['settings'] ?> &gt;
    🎨 <?= $translations['display_preferences'] ?>
</h2>

<form method="POST">

    <!-- ================= THEME ================= -->
    <div class="pref-box">
        <h3><?= $translations['theme'] ?></h3>

        <label>
            <input type="radio" name="theme" value="light"
            <?= (($_SESSION['theme'] ?? 'light') == 'light') ? 'checked' : '' ?>>
            <?= $translations['light'] ?>
        </label>

        <label style="margin-left:20px;">
            <input type="radio" name="theme" value="dark"
            <?= (($_SESSION['theme'] ?? 'light') == 'dark') ? 'checked' : '' ?>>
            <?= $translations['dark'] ?>
        </label>
    </div>

    <br>

    <!-- ================= LANGUE ================= -->
    <div class="pref-box">

        <h3><?= $translations['language_section'] ?? 'Language'; ?></h3>

        <p><strong><?= $translations['language'] ?> :</strong></p>

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

        <p><strong><?= $translations['text_size'] ?> :</strong></p>

        <label>
            <input type="radio" name="taille" value="normal"
            <?= (($_SESSION['taille'] ?? 'normal') == 'normal') ? 'checked' : '' ?>>
            <?= $translations['normal'] ?>
        </label>

        <label style="margin-left:20px;">
            <input type="radio" name="taille" value="grand"
            <?= (($_SESSION['taille'] ?? 'normal') == 'grand') ? 'checked' : '' ?>>
            <?= $translations['large'] ?>
        </label>

    </div>

    <br>

    <button type="submit">
        <?= $translations['save'] ?>
    </button>

</form>