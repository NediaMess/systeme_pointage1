<?php require_once "../lang_init.php"; ?>

<div class="page-header animate-in">
  <div>
    <h1><?= $lang['settings'] ?></h1>
    <div class="page-subtitle"><?= $lang['manage_account'] ?></div>
  </div>
</div>

<div class="settings-list animate-in">

  <a href="?page=profil_utilisateur" class="settings-list-item">
    <div class="settings-list-icon">👤</div>
    <div class="settings-list-info">
      <span class="settings-list-title"><?= $lang['user_profile'] ?></span>
      <span class="settings-list-desc"><?= $lang['profile_info'] ?></span>
    </div>
    <span class="settings-list-arrow">›</span>
  </a>

  <a href="?page=preferences_affichage" class="settings-list-item">
    <div class="settings-list-icon">🎨</div>
    <div class="settings-list-info">
      <span class="settings-list-title"><?= $lang['display_preferences'] ?></span>
      <span class="settings-list-desc"><?= $lang['display_options'] ?></span>
    </div>
    <span class="settings-list-arrow">›</span>
  </a>

  <a href="?page=param_calendrier" class="settings-list-item">
    <div class="settings-list-icon">📅</div>
    <div class="settings-list-info">
      <span class="settings-list-title"><?= $lang['calendar_settings'] ?></span>
      <span class="settings-list-desc"><?= $lang['calendar_options'] ?></span>
    </div>
    <span class="settings-list-arrow">›</span>
  </a>

  <a href="?page=securite_compte" class="settings-list-item">
    <div class="settings-list-icon">🔐</div>
    <div class="settings-list-info">
      <span class="settings-list-title"><?= $lang['account_security'] ?></span>
      <span class="settings-list-desc"><?= $lang['security_sessions'] ?></span>
    </div>
    <span class="settings-list-arrow">›</span>
  </a>

  <a href="?page=apropos" class="settings-list-item">
    <div class="settings-list-icon">🧩</div>
    <div class="settings-list-info">
      <span class="settings-list-title"><?= $lang['about'] ?></span>
      <span class="settings-list-desc"><?= $lang['version'] ?> 1.0 · CM2E 2026</span>
    </div>
    <span class="settings-list-arrow">›</span>
  </a>

</div>