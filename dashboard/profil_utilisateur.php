<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

// Upload photo
if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0){
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if(in_array($ext, $allowed)){
        $filename = 'user_'.$user_id.'.'.$ext;
        $dest = __DIR__.'/../uploads/'.$filename;

        if(move_uploaded_file($_FILES['photo']['tmp_name'], $dest)){
            $pdo->prepare("UPDATE users SET photo=? WHERE id=?")
                ->execute([$filename, $user_id]);

            $_SESSION['user_photo'] = $filename;
        }
    }

    header("Location: index.php?page=profil_utilisateur");
    exit();
}

// Delete photo
if(isset($_POST['delete_photo'])){
    $pdo->prepare("UPDATE users SET photo=NULL WHERE id=?")
        ->execute([$user_id]);

    $_SESSION['user_photo'] = null;

    header("Location: index.php?page=profil_utilisateur");
    exit();
}

// Get user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$_SESSION['user_photo'] = $user['photo'] ?? null;

$initiale = strtoupper(substr($user['prenom'] ?? 'U', 0, 1));

$photo_path = !empty($user['photo'])
    ? '/systeme_pointage/uploads/'.$user['photo']
    : null;
?>

<div class="page-header animate-in">
  <div>
    <h1><?= $lang['user_profile'] ?></h1>
    <div class="page-subtitle"><?= $lang['personal_info_and_photo'] ?></div>
  </div>
</div>

<!-- PHOTO -->
<div class="settings-section animate-in">

  <div class="settings-section-title">
    <span class="s-icon">🖼️</span><?= $lang['profile_photo'] ?>
  </div>

  <div style="display:flex;align-items:center;gap:28px;padding:10px 0">

    <!-- Avatar -->
    <div class="profile-avatar-lg" id="avatarPreview">

      <?php if($photo_path): ?>

        <img src="<?= $photo_path ?>?v=<?= time() ?>" alt="Photo">

      <?php else: ?>

        <span><?= $initiale ?></span>

      <?php endif; ?>

    </div>

    <!-- Actions -->
    <div style="display:flex;flex-direction:column;gap:10px">

      <form method="POST" enctype="multipart/form-data"
        style="display:flex;align-items:center;gap:10px">

        <label class="btn-upload">
          📷 <?= $lang['change_photo'] ?>

          <input type="file"
                 name="photo"
                 accept="image/*"
                 style="display:none"
                 onchange="previewPhoto(this);this.form.submit()">

        </label>

      </form>

      <?php if($photo_path): ?>

      <form method="POST">

        <input type="hidden" name="delete_photo" value="1">

        <button type="submit" class="btn-delete-photo">
          🗑 <?= $lang['delete_photo'] ?>
        </button>

      </form>

      <?php endif; ?>

      <p style="font-size:12px;color:var(--text-3)">
        JPG, PNG, WEBP · Max 5MB
      </p>

    </div>

  </div>

</div>

<!-- INFOS PERSONNELLES -->
<div class="settings-section animate-in">

  <div class="settings-section-title">
    <span class="s-icon">👤</span><?= $lang['personal_information'] ?>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['last_name'] ?></span>
    <span class="info-value"><?= htmlspecialchars($user['nom'] ?? '—') ?></span>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['first_name'] ?></span>
    <span class="info-value"><?= htmlspecialchars($user['prenom'] ?? '—') ?></span>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['role'] ?></span>
    <span class="info-value"><?= $lang['job_title'] ?></span>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['department'] ?></span>
    <span class="info-value">Métrologie</span>
  </div>

</div>

<!-- COMPTE -->
<div class="settings-section animate-in">

  <div class="settings-section-title">
    <span class="s-icon">🔑</span><?= $lang['account_information'] ?>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['email'] ?></span>
    <span class="info-value"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['role'] ?></span>
    <span class="info-value"><?= ucfirst($user['role'] ?? '—') ?></span>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['member_since'] ?></span>
    <span class="info-value"><?= date('d/m/Y', strtotime($user['date_creation'])) ?></span>
  </div>

  <div class="info-row">
    <span class="info-label"><?= $lang['status'] ?></span>
    <span class="info-value">
      <span class="status-active">● <?= $lang['active'] ?></span>
    </span>
  </div>

</div>

<script>

function previewPhoto(input){

  if(input.files && input.files[0]){

    const reader = new FileReader();

    reader.onload = e => {

      const av = document.getElementById('avatarPreview');

      av.innerHTML = '<img src="'+e.target.result+'" alt="Photo">';

    };

    reader.readAsDataURL(input.files[0]);
  }

}

</script>