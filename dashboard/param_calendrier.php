<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'] ?? null;

/* =============================
   SAVE SETTINGS
============================= */

if($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['new_off_day'])){

    $_SESSION['calendar_view'] = $_POST['calendar_view'] ?? 'trimester';
    $_SESSION['first_day'] = $_POST['first_day'] ?? 'monday';

    header("Location: index.php?page=param_calendrier");
    exit();
}

/* =============================
   ADD NEW NON WORKING DAY
============================= */

if(isset($_POST['new_off_day'])){

    $date_off = $_POST['new_off_day'];

    $stmt = $pdo->prepare("
        INSERT INTO non_working_days (user_id, date_off)
        VALUES (?, ?)
    ");

    $stmt->execute([$user_id, $date_off]);

    header("Location: index.php?page=param_calendrier");
    exit();
}

/* =============================
   CURRENT VALUES
============================= */

$calendar_view = $_SESSION['calendar_view'] ?? 'trimester';
$first_day = $_SESSION['first_day'] ?? 'monday';
?>

<h2>
    <?= $lang['settings'] ?> &gt; 
    <?= $lang['calendar_settings'] ?>
</h2>

<!-- ================= SETTINGS FORM ================= -->

<form method="POST">

    <!-- Bloc 1 -->
    <div class="pref-box">
        <h3><?= $lang['calendar_display'] ?></h3>

        <p><strong><?= $lang['default_view'] ?> :</strong></p>

        <label>
            <input type="radio" name="calendar_view" value="trimester"
            <?= $calendar_view=='trimester'?'checked':'' ?>>
            <?= $lang['quarterly'] ?>
        </label>

        <label style="margin-left:20px;">
            <input type="radio" name="calendar_view" value="year"
            <?= $calendar_view=='year'?'checked':'' ?>>
            <?= $lang['annual'] ?>
        </label>

        <br><br>

        <p><strong><?= $lang['first_day_week'] ?> :</strong></p>

        <label>
            <input type="radio" name="first_day" value="monday"
            <?= $first_day=='monday'?'checked':'' ?>>
            <?= $lang['monday'] ?>
        </label>

        <label style="margin-left:20px;">
            <input type="radio" name="first_day" value="sunday"
            <?= $first_day=='sunday'?'checked':'' ?>>
            <?= $lang['sunday'] ?>
        </label>
    </div>

    <br>


<br>

<!-- ================= ADD SPECIFIC OFF DAY ================= -->

<div class="pref-box">

    <h3><?= $lang['non_working_days'] ?></h3>

    <form method="POST"
          style="margin-top:10px; display:flex; gap:10px; align-items:center;">

        <input type="date" name="new_off_day" required
               style="padding:6px; border-radius:6px; border:1px solid #ccc;">

        <button type="submit"
                style="padding:6px 14px;
                       border-radius:6px;
                       border:none;
                       background:#e53935;
                       color:white;
                       cursor:pointer;">
            Ajouter
        </button>

    </form>

</div>
    <div style="text-align:center; margin-top:20px;">
        <button type="submit"
                style="padding:8px 20px;
                       border-radius:6px;
                       border:none;
                       background:#e53935;
                       color:white;
                       cursor:pointer;">
            <?= $lang['save'] ?>
        </button>
    </div>

</form>