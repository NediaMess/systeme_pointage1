<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){ header("Location: ../auth/login.php"); exit(); }

$msg_type='';
$msg_text='';

if($_SERVER['REQUEST_METHOD']==='POST'){

  $cur=$_POST['current_password']??'';
  $new=$_POST['new_password']??'';
  $conf=$_POST['confirm_password']??'';

  $stmt=$pdo->prepare("SELECT mot_de_passe FROM users WHERE id=?");
  $stmt->execute([$user_id]);
  $u=$stmt->fetch(PDO::FETCH_ASSOC);

  if(!$u){
      $msg_type='error';
      $msg_text=$lang['user_not_found'];
  }
  elseif(!password_verify($cur,$u['mot_de_passe'])){
      $msg_type='error';
      $msg_text=$lang['wrong_password'];
  }
  elseif($new!==$conf){
      $msg_type='error';
      $msg_text=$lang['password_not_match'];
  }
  elseif(strlen($new)<6){
      $msg_type='error';
      $msg_text=$lang['password_min'];
  }
  elseif(password_verify($new,$u['mot_de_passe'])){
      $msg_type='error';
      $msg_text=$lang['password_must_different'];
  }
  else{
      $pdo->prepare("UPDATE users SET mot_de_passe=? WHERE id=?")
          ->execute([password_hash($new,PASSWORD_DEFAULT),$user_id]);

      $msg_type='success';
      $msg_text=$lang['password_changed'];
  }
}

$stmt=$pdo->prepare("SELECT last_login FROM users WHERE id=?");
$stmt->execute([$user_id]);
$last=$stmt->fetch(PDO::FETCH_ASSOC)['last_login']??null;
?>

<div class="page-header animate-in">
  <div>
    <h1><?= $lang['account_security'] ?></h1>
    <div class="page-subtitle"><?= $lang['security_manage'] ?></div>
  </div>
</div>

<div class="settings-section animate-in">

  <div class="settings-section-title">
    <span class="s-icon">🔒</span><?= $lang['password_section'] ?>
  </div>

  <?php if($msg_text): ?>
    <div class="settings-alert <?= $msg_type ?>">
      <?= $msg_type==='success'?'✓':'✕' ?> <?= htmlspecialchars($msg_text) ?>
    </div>
  <?php endif; ?>

  <form method="POST">

    <div class="form-group">
      <label><?= $lang['current_password'] ?></label>

      <div class="pw-wrap">
        <input type="password" name="current_password" placeholder="••••••••" required>

        <button type="button" class="toggle-password" onclick="tog(this)">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5
                     c4.478 0 8.268 2.943 9.542 7
                     -1.274 4.057-5.064 7-9.542 7
                     -4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
        </button>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

      <div class="form-group" style="margin-bottom:0">

        <label><?= $lang['new_password'] ?></label>

        <div class="pw-wrap">
          <input type="password" id="np" name="new_password" placeholder="••••••••" required>

          <button type="button" class="toggle-password" onclick="tog(this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                 fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5
                       c4.478 0 8.268 2.943 9.542 7
                       -1.274 4.057-5.064 7-9.542 7
                       -4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>
        </div>

      </div>

      <div class="form-group" style="margin-bottom:0">

        <label><?= $lang['confirm_password'] ?></label>

        <div class="pw-wrap">
          <input type="password" id="cp2" name="confirm_password" placeholder="••••••••" required>

          <button type="button" class="toggle-password" onclick="tog(this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                 fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5
                       c4.478 0 8.268 2.943 9.542 7
                       -1.274 4.057-5.064 7-9.542 7
                       -4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </button>
        </div>

      </div>

    </div>

    <div class="save-btn-row">
      <button type="submit" class="btn-primary" style="width:auto;padding:11px 28px">
        <?= $lang['change_password'] ?>
      </button>
    </div>

  </form>
</div>

<div class="settings-section animate-in">

  <div class="settings-section-title">
    <span class="s-icon">🛡️</span><?= $lang['security_sessions'] ?>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['last_login'] ?></span>

    <span class="info-value">
      <?= $last ? date('d/m/Y \à H:i', strtotime($last)) : '<span style="color:var(--text-3)">'.$lang['not_available'].'</span>' ?>
    </span>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['current_session'] ?></span>
    <span class="info-value"><span class="status-active">● <?= $lang['active'] ?></span></span>
  </div>

</div>

<script>
function tog(btn){
  const i = btn.closest('.pw-wrap').querySelector('input');
  i.type = i.type==='password' ? 'text' : 'password';
}
</script>