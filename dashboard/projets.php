<?php
require_once "../lang_init.php";
require_once "../config/database.php";
$user_id = $_SESSION['user_id'];

$where  = "WHERE p.user_id = ?";
$params = [$user_id];

$search = $_GET['search'] ?? '';
if(!empty($search)){ $where .= " AND p.nom_projet LIKE ?"; $params[] = "%$search%"; }

$mode = $_GET['mode'] ?? '';
if(!empty($mode)){ $where .= " AND p.mode_travail = ?"; $params[] = $mode; }

$allowedSort = ['nom_projet','date_creation'];
$sort  = in_array($_GET['sort'] ?? '', $allowedSort) ? $_GET['sort'] : 'date_creation';
$order = ($_GET['order'] ?? '')==='asc' ? 'ASC' : 'DESC';

$stmt = $pdo->prepare("SELECT * FROM projects p $where ORDER BY p.$sort $order");
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM projects p $where");
$total_stmt->execute($params);
$total_count = $total_stmt->fetchColumn();
?>

<!-- Page header -->
<div class="page-header animate-in">
  <div>
    <h1><?= $lang['project_history'] ?></h1>
    <div class="page-subtitle">
  <?= $total_count ?> <?= $lang['projects_found'] ?>
</div>
  </div>
</div>

<!-- Toolbar -->
<div class="table-toolbar animate-in">

  <!-- Search -->
  <form method="GET" style="flex:1;min-width:200px">
    <input type="hidden" name="page" value="projets">
    <div class="search-input">
      <span class="search-icon">🔍</span>
      <input type="text" name="search" placeholder="<?= $lang['search'] ?>..." value="<?= htmlspecialchars($search) ?>">
    </div>
  </form>

  <!-- Filter mode -->
  <form method="GET">
    <input type="hidden" name="page" value="projets">
    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
    <select name="mode" class="filter-select" onchange="this.form.submit()">
      <option value=""><?= $lang['work_mode'] ?></option>
      <option value="solo"   <?= $mode=='solo'  ?'selected':'' ?>><?= $lang['solo'] ?></option>
      <option value="equipe" <?= $mode=='equipe'?'selected':'' ?>><?= $lang['team'] ?></option>
    </select>
  </form>

  <!-- Sort -->
  <form method="GET">
    <input type="hidden" name="page" value="projets">
    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
    <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
    <select name="sort" class="filter-select" onchange="this.form.submit()">
      <option value="nom_projet"   <?= $sort=='nom_projet'   ?'selected':'' ?>><?= $lang['project_name'] ?></option>
      <option value="date_creation"<?= $sort=='date_creation'?'selected':'' ?>><?= $lang['creation_date'] ?></option>
    </select>
    <select name="order" class="filter-select" onchange="this.form.submit()" style="margin-left:6px">
      <option value="asc"  <?= $order=='ASC' ?'selected':'' ?>>↑ <?= $lang['ascending'] ?></option>
      <option value="desc" <?= $order=='DESC'?'selected':'' ?>>↓ <?= $lang['descending'] ?></option>
    </select>
  </form>

  <a href="index.php?page=projets" class="icon-btn" title="<?= $lang['reset'] ?>">↺</a>
</div>

<!-- Table -->
<div class="animate-in">
  <table class="projects-table">
    <thead>
      <tr>
        <th>#</th>
        <th><?= $lang['project_name'] ?></th>
        <th><?= $lang['work_mode'] ?></th>
        <th><?= $lang['creation_date'] ?></th>
        <th><?= $lang['score_added'] ?></th>
        <th><?= $lang['status'] ?></th>
        <th><?= $lang['end_date'] ?></th>
      </tr>
    </thead>
    <tbody>
    <?php if(count($projects) > 0): ?>
      <?php foreach($projects as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td class="proj-name-cell"><?= htmlspecialchars($p['nom_projet']) ?></td>
          <td><?= $p['mode_travail']=='solo' ? '👤 '.$lang['solo'] : '👥 '.$lang['team'] ?></td>
          <td><?= date('d/m/Y', strtotime($p['date_creation'])) ?></td>
          <td>
            <?php $sc = $p['score_ajoute'];
            if($sc > 0)      echo "<span class='score positive'>+$sc</span>";
            elseif($sc < 0)  echo "<span class='score negative'>$sc</span>";
            else             echo "<span style='color:var(--text-3)'>0</span>"; ?>
          </td>
          <td>
            <?php if($p['statut']=='en_attente'): ?>
  <span class="badge badge-waiting">
    <span class="badge-dot"></span> <?= $lang['pending'] ?>
  </span>

<?php elseif($p['statut']=='en_cours'): ?>
  <span class="badge badge-active">
    <span class="badge-dot"></span> <?= $lang['in_progress'] ?>
  </span>

<?php elseif($p['statut']=='Terminé'): ?>
  <span class="badge badge-done">
    <span class="badge-dot"></span> <?= $lang['completed'] ?>
  </span>
<?php endif; ?>
          </td>
          <td><?= $p['date_fin'] ? date('d/m/Y', strtotime($p['date_fin'])) : '<span style="color:var(--text-3)">—</span>' ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="7">
          <div class="empty-state">
            <div class="empty-state-icon">📂</div>
            <p><?= $lang['no_project_found'] ?></p>
          </div>
        </td>
      </tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
