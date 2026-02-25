<?php
require_once "../lang_init.php";
require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

/* =============================
   FILTRE / RECHERCHE / TRI
============================= */

$where = "WHERE p.user_id = ?";
$params = [$user_id];

/* Recherche */
$search = $_GET['search'] ?? '';
if(!empty($search)){
    $where .= " AND p.nom_projet LIKE ?";
    $params[] = "%$search%";
}

/* Filter mode */
$mode = $_GET['mode'] ?? '';
if(!empty($mode)){
    $where .= " AND p.mode_travail = ?";
    $params[] = $mode;
}

/* Colonnes autorisées */
$allowedSort = ['nom_projet','date_creation'];
$sort = in_array($_GET['sort'] ?? '', $allowedSort) ? $_GET['sort'] : 'date_creation';
$order = ($_GET['order'] ?? '') === 'asc' ? 'ASC' : 'DESC';

/* REQUETE */
$stmt = $pdo->prepare("
    SELECT p.*, COALESCE(SUM(d.score),0) AS score_total
    FROM projects p
    LEFT JOIN daily_scores d ON p.id = d.project_id
    $where
    GROUP BY p.id
    ORDER BY p.$sort $order
");

$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2><?= $lang['project_history'] ?></h2>

<div class="table-header">

    <!-- SEARCH -->
    <form method="GET" class="search-box">
        <input type="hidden" name="page" value="projets">
        <input type="text" name="search"
               placeholder="<?= $lang['search'] ?>"
               value="<?= htmlspecialchars($search) ?>">
    </form>

    <div class="table-actions">

        <!-- FILTER MODE -->
        <form method="GET">
            <input type="hidden" name="page" value="projets">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">

            <select name="mode" onchange="this.form.submit()" class="filter-select">
                <option value=""><?= $lang['work_mode'] ?></option>
                <option value="solo" <?= $mode=='solo'?'selected':'' ?>>
                    <?= $lang['solo'] ?>
                </option>
                <option value="equipe" <?= $mode=='equipe'?'selected':'' ?>>
                    <?= $lang['team'] ?>
                </option>
            </select>
        </form>

        <!-- SORT -->
        <form method="GET">
            <input type="hidden" name="page" value="projets">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

            <select name="sort" onchange="this.form.submit()" class="filter-select">
                <option value="nom_projet" <?= $sort=='nom_projet'?'selected':'' ?>>
                    <?= $lang['project_name'] ?>
                </option>

                <option value="date_creation" <?= $sort=='date_creation'?'selected':'' ?>>
                    <?= $lang['creation_date'] ?>
                </option>
            </select>

            <select name="order" onchange="this.form.submit()" class="filter-select">
                <option value="asc" <?= $order=='ASC'?'selected':'' ?>>
                    ↑ <?= $lang['ascending'] ?>
                </option>

                <option value="desc" <?= $order=='DESC'?'selected':'' ?>>
                    ↓ <?= $lang['descending'] ?>
                </option>
            </select>
        </form>

        <!-- REFRESH -->
        <a href="index.php?page=projets" class="icon-btn">🔄</a>

    </div>

</div>

<table class="projects-table">
    <thead>
        <tr>
            <th>ID</th>
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
        <?php foreach($projects as $project): ?>
            <tr>
                <td><?= $project['id']; ?></td>

                <td><?= htmlspecialchars($project['nom_projet']); ?></td>

                <td>
                    <?= $project['mode_travail'] == 'solo' 
                        ? $lang['solo'] 
                        : $lang['team'] ?>
                </td>

                <td><?= date('d/m/Y', strtotime($project['date_creation'])); ?></td>

                <!-- SCORE -->
                <td>
                    <?php
                        $score = $project['score_total'];

                        if($score > 0){
                            echo "<span class='score positive'>+$score</span>";
                        } elseif($score < 0){
                            echo "<span class='score negative'>$score</span>";
                        } else {
                            echo "0";
                        }
                    ?>
                </td>

                <!-- STATUS -->
                <td>
                    <?php
                        $today = date('Y-m-d');

                        if(!empty($project['date_fin']) && $project['date_fin'] < $today){
                            echo '<span style="background-color: green; color:white; padding:5px 12px; border-radius:20px; font-size:12px;">'
                                 . $lang['completed'] .
                                 '</span>';
                        } else {
                            echo '<span style="background-color: orange; color:white; padding:5px 12px; border-radius:20px; font-size:12px;">'
                                 . $lang['in_progress'] .
                                 '</span>';
                        }
                    ?>
                </td>

                <td>
                    <?= $project['date_fin'] 
                        ? date('d/m/Y', strtotime($project['date_fin'])) 
                        : '-' ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7"><?= $lang['no_project_found'] ?></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>