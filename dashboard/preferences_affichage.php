<?php require_once "../lang_init.php"; ?>

<div class="page-header animate-in">
  <div>
    <h1><?= $lang['display_preferences'] ?></h1>

    <div class="page-subtitle">
      <?= (($_SESSION['lang'] ?? 'fr') === 'en')
          ? 'Customize appearance and language'
          : 'Personnalisez l\'apparence et la langue' ?>
    </div>

  </div>
</div>

<form method="POST" action="index.php?page=preferences_affichage">

  <input type="hidden" name="_redirect" value="index.php?page=preferences_affichage">

  <!-- THEME -->
  <div class="settings-section animate-in">

    <div class="settings-section-title">
      <span class="s-icon">🎨</span><?= $lang['theme'] ?>
    </div>

    <div class="option-group">

      <label class="option-pill">
        <input type="radio"
               name="theme"
               value="light"
               <?= (($_SESSION['theme']??'light')==='light')?'checked':'' ?>>

        ☀️ <?= $lang['light'] ?>
      </label>

      <label class="option-pill">
        <input type="radio"
               name="theme"
               value="dark"
               <?= (($_SESSION['theme']??'light')==='dark')?'checked':'' ?>>

        🌙 <?= $lang['dark'] ?>
      </label>

    </div>

  </div>

  <!-- LANGUE -->
  <div class="settings-section animate-in">

    <div class="settings-section-title">
      <span class="s-icon">🌐</span><?= $lang['language_section'] ?>
    </div>

    <span class="field-label"><?= $lang['language'] ?></span>

    <div class="option-group">

      <label class="option-pill">
        <input type="radio"
               name="lang"
               value="fr"
               <?= (($_SESSION['lang']??'fr')==='fr')?'checked':'' ?>>

        🇫🇷 Français
      </label>

      <label class="option-pill">
        <input type="radio"
               name="lang"
               value="en"
               <?= (($_SESSION['lang']??'fr')==='en')?'checked':'' ?>>

        🇬🇧 English
      </label>

    </div>

  </div>

  <!-- TEXT SIZE -->
  <div class="settings-section animate-in">

    <div class="settings-section-title">
      <span class="s-icon">🔤</span><?= $lang['text_size'] ?>
    </div>

    <div class="option-group">

      <label class="option-pill">

        <input type="radio"
               name="taille"
               value="normal"
               <?= (($_SESSION['taille']??'normal')==='normal')?'checked':'' ?>>

        <span style="font-size:13px">Aa</span> <?= $lang['normal'] ?>

      </label>

      <label class="option-pill">

        <input type="radio"
               name="taille"
               value="grand"
               <?= (($_SESSION['taille']??'normal')==='grand')?'checked':'' ?>>

        <span style="font-size:16px">Aa</span> <?= $lang['large'] ?>

      </label>

    </div>

  </div>

  <div class="save-btn-row animate-in">

    <button type="submit"
            class="btn-primary"
            style="width:auto;padding:11px 32px">

      <?= $lang['save'] ?>

    </button>

  </div>

</form>