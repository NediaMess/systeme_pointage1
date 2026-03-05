<?php require_once "../lang_init.php"; ?>

<div class="page-header animate-in">
  <div>
    <h1><?= $lang['about'] ?></h1>
    <div class="page-subtitle"><?= $lang['about_subtitle'] ?></div>
  </div>
</div>

<div class="about-grid">

  <!-- Hero -->
  <div class="about-hero animate-in">
    <div class="about-logo">
      <img src="/systeme_pointage/public/assets/img/logocm2e.jpg" alt="CM2E">
    </div>

    <div class="about-hero-text">
      <h2><?= $lang['app_name'] ?></h2>

      <p><?= $lang['app_description'] ?></p>

      <span class="about-version">
        <?= $lang['version'] ?> 1.0 · <?= $lang['february'] ?> 2026
      </span>
    </div>
  </div>

  <!-- Projet -->
  <div class="settings-section animate-in">

    <div class="settings-section-title">
      <span class="s-icon">🎓</span><?= $lang['academic_project'] ?>
    </div>

    <div class="info-row">
      <span class="info-label"><?= $lang['developed_by'] ?></span>
      <span class="info-value">Nedia Messadi</span>
    </div>

    <div class="info-row">
      <span class="info-label"><?= $lang['framework'] ?></span>
      <span class="info-value"><?= $lang['final_project'] ?></span>
    </div>

    <div class="info-row">
      <span class="info-label"><?= $lang['year'] ?></span>
      <span class="info-value">2026</span>
    </div>

    <div class="info-row">
      <span class="info-label"><?= $lang['technology'] ?></span>
      <span class="info-value">PHP · MySQL · HTML/CSS</span>
    </div>

  </div>

  <!-- Contact -->
  <div class="settings-section animate-in">

    <div class="settings-section-title">
      <span class="s-icon">📍</span><?= $lang['contact_support'] ?>
    </div>

    <div class="contact-item">
      <div class="contact-icon">📍</div>
      <div>
        <div class="contact-label"><?= $lang['address'] ?></div>
        <div class="contact-value">
          Zone Industrielle Sidi Abdelhamid CP 4061, Gouvernorat de Sousse
        </div>
      </div>
    </div>

    <div class="contact-item">
      <div class="contact-icon">📞</div>
      <div>
        <div class="contact-label"><?= $lang['phone'] ?></div>
        <div class="contact-value">+216 25 415 000</div>
      </div>
    </div>

    <div class="contact-item">
      <div class="contact-icon">📠</div>
      <div>
        <div class="contact-label"><?= $lang['fax'] ?></div>
        <div class="contact-value">+216 73 320 225</div>
      </div>
    </div>

    <div class="contact-item">
      <div class="contact-icon">✉️</div>
      <div>
        <div class="contact-label"><?= $lang['email'] ?></div>
        <div class="contact-value">
          <a href="mailto:saif@cm2e.com.tn">saif@cm2e.com.tn</a>
        </div>
      </div>
    </div>

    <div class="social-row">
      <a href="https://www.linkedin.com/company/cm2e/?originalSubdomain=tn"
         target="_blank"
         class="social-btn">🔗 <?= $lang['linkedin'] ?></a>

      <a href="https://www.facebook.com/cm2e.tn/?locale=fr_FR"
         target="_blank"
         class="social-btn">👍 <?= $lang['facebook'] ?></a>
    </div>

  </div>

</div>