<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'] ?? null;

if($_SERVER['REQUEST_METHOD']==='POST' && !isset($_POST['new_off_day'])){
  $_SESSION['calendar_view'] = $_POST['calendar_view'] ?? 'trimester';
  $_SESSION['first_day']     = $_POST['first_day'] ?? 'monday';

  header("Location: index.php?page=param_calendrier");
  exit();
}

if(isset($_POST['new_off_day'])){
  $pdo->prepare("INSERT INTO non_working_days(user_id,date_off) VALUES(?,?)")
      ->execute([$user_id,$_POST['new_off_day']]);

  header("Location: index.php?page=param_calendrier");
  exit();
}

$cv  = $_SESSION['calendar_view'] ?? 'trimester';
$fd  = $_SESSION['first_day'] ?? 'monday';

$stmt=$pdo->prepare("SELECT date_off FROM non_working_days WHERE user_id=? ORDER BY date_off DESC LIMIT 10");
$stmt->execute([$user_id]);
$off_days=$stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="page-header animate-in">
  <div>
    <h1><?= $lang['calendar_settings'] ?></h1>
    <div class="page-subtitle"><?= $lang['calendar_preferences_desc'] ?></div>
  </div>
</div>

<form method="POST">

<div class="settings-section animate-in">

  <div class="settings-section-title">
    <span class="s-icon">📅</span><?= $lang['calendar_display'] ?>
  </div>

  <span class="field-label"><?= $lang['default_view'] ?></span>

  <div class="option-group">

    <label class="option-pill">
      <input type="radio" name="calendar_view" value="trimester"
        <?= $cv==='trimester'?'checked':'' ?>>

      📊 <?= $lang['quarterly'] ?>
    </label>

    <label class="option-pill">
      <input type="radio" name="calendar_view" value="year"
        <?= $cv==='year'?'checked':'' ?>>

      📆 <?= $lang['annual'] ?>
    </label>

  </div>

  <span class="field-label"><?= $lang['first_day_week'] ?></span>

  <div class="option-group">

    <label class="option-pill">
      <input type="radio" name="first_day" value="monday"
        <?= $fd==='monday'?'checked':'' ?>>

      <?= $lang['monday'] ?>
    </label>

    <label class="option-pill">
      <input type="radio" name="first_day" value="sunday"
        <?= $fd==='sunday'?'checked':'' ?>>

      <?= $lang['sunday'] ?>
    </label>

  </div>

  <div class="save-btn-row">
    <button type="submit" class="btn-primary" style="width:auto;padding:11px 28px">
      <?= $lang['save'] ?>
    </button>
  </div>

</div>

</form>

<div class="settings-section animate-in">

  <div class="settings-section-title">
    <span class="s-icon">🚫</span><?= $lang['non_working_days'] ?>
  </div>

  <form method="POST"
        style="display:flex;gap:12px;align-items:flex-end;margin-bottom:20px;flex-wrap:wrap">

    <div class="form-group"
         style="margin-bottom:0;flex:1;min-width:200px;max-width:260px">

      <label><?= $lang['add_date'] ?></label>

      <input type="date" name="new_off_day" required>

    </div>

    <button type="submit"
            class="btn-primary"
            style="width:auto;padding:11px 20px;margin-top:0">

      + <?= $lang['add'] ?>

    </button>

  </form>

  <?php if($off_days): ?>

    <?php foreach($off_days as $d): ?>

      <div class="info-row">
        <span class="info-label">🚫 <?= $lang['non_working_day'] ?></span>
        <span class="info-value"><?= date('d/m/Y',strtotime($d)) ?></span>
      </div>

    <?php endforeach; ?>

  <?php else: ?>

    <p style="color:var(--text-3);font-size:13px;text-align:center;padding:16px 0">
      <?= $lang['no_non_working_days'] ?>
    </p>

  <?php endif; ?>

</div>