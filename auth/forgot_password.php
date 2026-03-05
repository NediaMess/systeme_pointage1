<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once "../lang_init.php";
$lang_code = $_SESSION['lang'] ?? 'fr';
?>

<!DOCTYPE html>
<html lang="<?= $lang_code ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title><?= $lang['forgot_password'] ?> — CM2E</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/systeme_pointage/public/assets/css/style.css">
</head>

<body>

<div class="auth-wrapper">

  <div class="auth-panel">

    <div class="auth-brand">
      <div class="auth-brand-line"></div>
      <span class="auth-brand-text">
        CM2E · <?= $lang['account_recovery'] ?>
      </span>
    </div>

    <div class="auth-card">

      <?php if(isset($_SESSION['reset_message'])): ?>

        <div class="success-box">

          <div class="success-icon">✓</div>

          <div class="success-title">
            <?= $lang['link_sent'] ?>
          </div>

          <p class="success-text">
            <?= $lang['check_inbox'] ?>
          </p>

          <p class="success-subtext">
            <?= $lang['link_valid'] ?> <strong>1 <?= $lang['hour'] ?></strong>.
          </p>

          <a href="login.php"
             class="btn-primary"
             style="display:block;text-align:center">

            <?= $lang['back_to_login'] ?>

          </a>

        </div>

        <?php unset($_SESSION['reset_message']); ?>

      <?php else: ?>

        <h1><?= $lang['forgot_password'] ?></h1>

        <p class="auth-subtitle">
          <?= $lang['enter_email_reset'] ?>
        </p>

        <form action="send_reset_link.php" method="POST">

          <div class="form-group">

            <label><?= $lang['email_address'] ?></label>

            <input type="email"
                   name="email"
                   placeholder="votre@cm2e.com"
                   required>

          </div>

          <button type="submit" class="btn-primary">
            <?= $lang['send_link'] ?>
          </button>

        </form>

        <a href="login.php"
           class="auth-link"
           style="margin-top:20px">

          ← <?= $lang['back_to_login'] ?>

        </a>

      <?php endif; ?>

    </div>

  </div>

  <div class="auth-visual">

    <div class="auth-visual-shape"></div>

    <div class="auth-visual-inner">

      <div class="auth-logo-box">
        <img src="/systeme_pointage/public/assets/img/logocm2e.jpg" alt="CM2E">
      </div>

      <div class="auth-tagline">
        <?= $lang['secure_account_recovery'] ?>
      </div>

      <div class="auth-tagline-sub">
        <?= $lang['security_words'] ?>
      </div>

    </div>

  </div>

</div>

</body>
</html>