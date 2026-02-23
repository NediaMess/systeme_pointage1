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

<h2>Historique des projets</h2>

<!-- =============================
     TOOLBAR
============================= -->

<div class="table-header">

    <!-- SEARCH -->
    <form method="GET" class="search-box">
        <input type="hidden" name="page" value="projets">
        <input type="text" name="search"
               placeholder="Rechercher..."
               value="<?= htmlspecialchars($search) ?>">
    </form>

    <div class="table-actions">

        <!-- FILTER MODE -->
        <form method="GET">
            <input type="hidden" name="page" value="projets">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <select name="mode" onchange="this.form.submit()" class="filter-select">
                <option value="">Mode de travail</option>
                <option value="solo" <?= $mode=='solo'?'selected':'' ?>>Solo</option>
                <option value="equipe" <?= $mode=='equipe'?'selected':'' ?>>Equipe</option>
            </select>
        </form>

        <!-- SORT -->
        <form method="GET">
            <input type="hidden" name="page" value="projets">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

            <select name="sort" onchange="this.form.submit()" class="filter-select">
                <option value="nom_projet" <?= $sort=='nom_projet'?'selected':'' ?>>Nom</option>
                <option value="date_creation" <?= $sort=='date_creation'?'selected':'' ?>>Date</option>
            </select>

            <select name="order" onchange="this.form.submit()" class="filter-select">
                <option value="asc" <?= $order=='ASC'?'selected':'' ?>>↑ Asc</option>
                <option value="desc" <?= $order=='DESC'?'selected':'' ?>>↓ Desc</option>
            </select>
        </form>

        <!-- REFRESH -->
        <a href="index.php?page=projets" class="icon-btn">🔄</a>

    </div>

</div>

<!-- =============================
     TABLE
============================= -->

<table class="projects-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom Projet</th>
            <th>Mode</th>
            <th>Date création</th>
            <th>Score ajouté</th>
            <th>Statut</th>
            <th>Date fin</th>
        </tr>
    </thead>

    <tbody>
    <?php if(count($projects) > 0): ?>
        <?php foreach($projects as $project): ?>
            <tr>
                <td><?= $project['id']; ?></td>
                <td><?= htmlspecialchars($project['nom_projet']); ?></td>
                <td><?= htmlspecialchars($project['mode_travail']); ?></td>
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

     <td>
<?php
    $today = date('Y-m-d');

    if(!empty($project['date_fin']) && $project['date_fin'] < $today){
        echo '<span style="background-color: green; color:white; padding:5px 12px; border-radius:20px; font-size:12px;">Terminé</span>';
    } else {
        echo '<span style="background-color: orange; color:white; padding:5px 12px; border-radius:20px; font-size:12px;">En cours</span>';
    }
?>
</td>

                <td>
                    <?= $project['date_fin'] ? date('d/m/Y', strtotime($project['date_fin'])) : '-' ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7">Aucun projet trouvé</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<style>

/* TOOLBAR */
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.search-box input {
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid #d1d5db;
    width: 250px;
    background: #f9fafb;
}

.table-actions {
    display: flex;
    gap: 8px;
}

.filter-select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background: #f9fafb;
}

.icon-btn {
    background: #e5e7eb;
    padding: 6px 10px;
    border-radius: 6px;
    text-decoration: none;
    color: black;
}

.icon-btn:hover {
    background: #d1d5db;
}

/* TABLE */
.projects-table {
    width: 100%;
    border-collapse: collapse;
    background: #f3f4f6;
}

.projects-table th {
    background: #e5e7eb;
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #d1d5db;
}

.projects-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.projects-table tr:hover {
    background: #eaeaea;
}

/* SCORE COLORS */
.score.positive {
    color: green;
    font-weight: bold;
}

.score.negative {
    color: red;
    font-weight: bold;
}

/* BADGE */
.badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
}

.badge.active {
    background: #4CAF50;
    color: white;
}

.badge.finished {
    background: #999;
    color: white;
}

</style>