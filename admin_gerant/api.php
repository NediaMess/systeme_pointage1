<?php
/**
 * CM2E — admin_gerant/api.php
 * Utilise config/database.php existant (variable $pdo)
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../lang_init.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
// Vérification accès admin
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    jsonResponse(['error' => 'Non autorisé'], 401);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$uid    = (int)$_SESSION['user_id'];

try {
    switch ($action) {

        /* ══════════════════════════════
           DASHBOARD STATS
        ══════════════════════════════ */
        case 'dashboard_stats':
            $stats = [];

            $stats['projets_actifs'] = $pdo->query(
                "SELECT COUNT(*) FROM projects WHERE statut='en_cours'"
            )->fetchColumn();

            $stats['metrologues'] = $pdo->query(
                "SELECT COUNT(DISTINCT user_id) FROM projects WHERE statut='en_cours'"
            )->fetchColumn();

            $stats['taches_done'] = $pdo->query(
                "SELECT COUNT(*) FROM daily_scores WHERE date_score=CURDATE() AND score > 0"
            )->fetchColumn();

            $today = date('Y-m-d');
            $stats['absences'] = $pdo->query(
                "SELECT COUNT(*) FROM daily_scores WHERE date_score='$today' AND is_absent=1"
            )->fetchColumn();

            $stats['projets_done'] = $pdo->query(
                "SELECT COUNT(*) FROM projects WHERE status='done' OR statut='termine'"
            )->fetchColumn();

            jsonResponse($stats);

        /* ══════════════════════════════
           MÉTROLOGUES — liste
        ══════════════════════════════ */
        case 'metrologues_list':
            $rows = $pdo->query("
                SELECT
                    u.id,
                    CONCAT(u.prenom, ' ', u.nom) AS name,
                    u.email,
                    u.role,
                    us.classeur_number,
                    us.poste,
                    us.specialite,
                    us.niveau,
                    us.telephone,
                    COALESCE(qs.total_score, 0) AS score,
                    (SELECT COUNT(*) FROM projects
                        WHERE (assigned_to = u.id OR user_id = u.id)
                        AND (status='in_progress' OR statut='en_cours')
                    ) AS projets,
                    (SELECT COUNT(*) FROM daily_scores ds
                        WHERE ds.user_id = u.id
                        AND ds.is_absent = 1
                        AND QUARTER(ds.date_score) = QUARTER(NOW())
                        AND YEAR(ds.date_score) = YEAR(NOW())
                    ) AS absences
                FROM users u
                LEFT JOIN user_settings us ON us.user_id = u.id
                LEFT JOIN quarter_scores qs ON qs.user_id = u.id
                    AND qs.quarter = QUARTER(NOW())
                    AND qs.year = YEAR(NOW())
                WHERE u.role = 'metrologue' AND u.is_active = 1
                ORDER BY score DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            jsonResponse($rows);

        /* ══════════════════════════════
           MÉTROLOGUE — détail
        ══════════════════════════════ */
        case 'metro_detail':
            $id   = (int)($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT
                    u.id,
                    CONCAT(u.prenom, ' ', u.nom) AS name,
                    u.email,
                    us.classeur_number,
                    us.poste,
                    us.specialite,
                    us.niveau,
                    us.telephone,
                    COALESCE(qs.total_score, 0) AS score
                FROM users u
                LEFT JOIN user_settings us ON us.user_id = u.id
                LEFT JOIN quarter_scores qs ON qs.user_id = u.id
                    AND qs.quarter = QUARTER(NOW())
                    AND qs.year = YEAR(NOW())
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            $metro = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$metro) jsonResponse(['error' => 'Introuvable'], 404);

            // Projets
            $pstmt = $pdo->prepare("
                SELECT
                    p.id,
                    p.nom_projet AS title,
                    COALESCE(p.status, p.statut) AS status,
                    p.date_fin AS deadline,
                    (SELECT COUNT(*) FROM steps WHERE project_id=p.id AND status='done') AS done_steps,
                    (SELECT COUNT(*) FROM steps WHERE project_id=p.id) AS total_steps
                FROM projects p
                WHERE p.assigned_to = ? OR p.user_id = ?
                ORDER BY p.date_creation DESC LIMIT 10
            ");
            $pstmt->execute([$id, $id]);
            $metro['projects'] = $pstmt->fetchAll(PDO::FETCH_ASSOC);

            // Absences ce trimestre
            $astmt = $pdo->prepare("
                SELECT date_score AS date FROM daily_scores
                WHERE user_id=? AND is_absent=1
                AND QUARTER(date_score)=QUARTER(NOW())
                AND YEAR(date_score)=YEAR(NOW())
                ORDER BY date_score DESC LIMIT 20
            ");
            $astmt->execute([$id]);
            $metro['absences_list'] = array_column($astmt->fetchAll(PDO::FETCH_ASSOC), 'date');

            jsonResponse($metro);

        /* ══════════════════════════════
           MÉTROLOGUE — ajouter
        ══════════════════════════════ */
        case 'metro_add':
            $data     = json_decode(file_get_contents('php://input'), true);
            $fullname = trim($data['name'] ?? '');
            $parts    = explode(' ', $fullname, 2);
            $prenom   = $parts[0] ?? '';
            $nom      = $parts[1] ?? '';
            $email    = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';
            $classeur = (int)($data['classeur_number'] ?? 0);

            if (!$prenom || !$email || !$password)
                jsonResponse(['error' => 'Champs obligatoires manquants'], 400);

            // Email unique ?
            $chk = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $chk->execute([$email]);
            if ($chk->fetch()) jsonResponse(['error' => 'Email déjà utilisé'], 409);

            // Classeur unique ?
            if ($classeur > 0) {
                $chkC = $pdo->prepare("SELECT user_id FROM user_settings WHERE classeur_number=?");
                $chkC->execute([$classeur]);
                if ($chkC->fetch()) jsonResponse(['error' => "Classeur #$classeur déjà assigné à un autre métrologue"], 409);
            }

            $pdo->beginTransaction();

            $ins = $pdo->prepare("
                INSERT INTO users (nom, prenom, email, mot_de_passe, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'metrologue', 1, NOW())
            ");
            $ins->execute([$nom, $prenom, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $newId = $pdo->lastInsertId();

            $insS = $pdo->prepare("
                INSERT INTO user_settings (user_id, classeur_number, poste, specialite, niveau, telephone)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    classeur_number = VALUES(classeur_number),
                    poste           = VALUES(poste),
                    specialite      = VALUES(specialite),
                    niveau          = VALUES(niveau),
                    telephone       = VALUES(telephone)
            ");
            $insS->execute([
                $newId, $classeur,
                $data['poste']      ?? '',
                $data['specialite'] ?? '',
                $data['niveau']     ?? 'Junior',
                $data['telephone']  ?? ''
            ]);

            $pdo->commit();
            logAction($uid, '➕ Métrologue ajouté', "$fullname (classeur #$classeur)");
            jsonResponse(['success' => true, 'id' => $newId]);

        /* ══════════════════════════════
           MÉTROLOGUE — modifier
        ══════════════════════════════ */
        case 'metro_edit':
            $data     = json_decode(file_get_contents('php://input'), true);
            $id       = (int)($data['id'] ?? 0);
            if (!$id) jsonResponse(['error' => 'ID manquant'], 400);

            $fullname = trim($data['name'] ?? '');
            $parts    = explode(' ', $fullname, 2);
            $prenom   = $parts[0] ?? '';
            $nom      = $parts[1] ?? '';

            $pdo->prepare("UPDATE users SET nom=?, prenom=?, email=? WHERE id=?")
                ->execute([$nom, $prenom, $data['email'] ?? '', $id]);

            $pdo->prepare("
                INSERT INTO user_settings (user_id, classeur_number, poste, specialite, niveau, telephone)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    classeur_number = VALUES(classeur_number),
                    poste           = VALUES(poste),
                    specialite      = VALUES(specialite),
                    niveau          = VALUES(niveau),
                    telephone       = VALUES(telephone)
            ")->execute([
                $id,
                (int)($data['classeur_number'] ?? 0),
                $data['poste']      ?? '',
                $data['specialite'] ?? '',
                $data['niveau']     ?? '',
                $data['telephone']  ?? ''
            ]);

            logAction($uid, '✏️ Métrologue modifié', $fullname);
            jsonResponse(['success' => true]);

        /* ══════════════════════════════
           MÉTROLOGUE — supprimer
        ══════════════════════════════ */
        case 'metro_delete':
            $data = json_decode(file_get_contents('php://input'), true);
            $id   = (int)($data['id'] ?? 0);
            if (!$id) jsonResponse(['error' => 'ID manquant'], 400);

            $stmt = $pdo->prepare("SELECT CONCAT(prenom,' ',nom) FROM users WHERE id=?");
            $stmt->execute([$id]);
            $nm = $stmt->fetchColumn();

            $pdo->prepare("UPDATE users SET is_active=0 WHERE id=?")->execute([$id]);

            logAction($uid, '🗑️ Métrologue supprimé', $nm);
            jsonResponse(['success' => true]);

        /* ══════════════════════════════
           PROJETS — liste
        ══════════════════════════════ */
        case 'projets_list':
            $rows = $pdo->query("
                SELECT
                    p.id,
                    p.nom_projet AS title,
                    COALESCE(p.status, p.statut) AS status,
                    p.date_fin AS deadline,
                    p.description,
                    p.priority,
                    CONCAT(u.prenom,' ',u.nom) AS metro_name,
                    us.classeur_number,
                    (SELECT COUNT(*) FROM steps WHERE project_id=p.id AND status='done') AS done_steps,
                    (SELECT COUNT(*) FROM steps WHERE project_id=p.id) AS total_steps
                FROM projects p
                LEFT JOIN users u ON u.id = COALESCE(p.assigned_to, p.user_id)
                LEFT JOIN user_settings us ON us.user_id = COALESCE(p.assigned_to, p.user_id)
                ORDER BY p.date_creation DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$r) {
                $s = $pdo->prepare("SELECT nom_step AS name, status FROM steps WHERE project_id=? ORDER BY step_order");
                $s->execute([$r['id']]);
                $r['steps']    = $s->fetchAll(PDO::FETCH_ASSOC);
                $total         = max((int)$r['total_steps'], 1);
                $r['progress'] = round($r['done_steps'] / $total * 100);
            }

            jsonResponse($rows);

        /* ══════════════════════════════
           PROJET — ajouter
        ══════════════════════════════ */
        case 'projet_add':
            $data    = json_decode(file_get_contents('php://input'), true);
            $title   = trim($data['title'] ?? '');
            $metroId = (int)($data['assigned_to'] ?? 0);

            if (!$title || !$metroId)
                jsonResponse(['error' => 'Titre et métrologue obligatoires'], 400);

            $pdo->beginTransaction();
            $ins = $pdo->prepare("INSERT INTO projects 
               (nom_projet, user_id, assigned_to, statut, status, date_fin, description, priority, date_creation) 
               VALUES (?,?,?,'en_attente','pending',?,?,?,NOW())");        
                $ins->execute([
                $title, $metroId, $metroId,
                $data['deadline']    ?? null,
                $data['description'] ?? '',
                $data['priority']    ?? 'normale'
            ]);
            $pid = $pdo->lastInsertId();

            // 4 tâches fixes
            $ist   = $pdo->prepare("INSERT INTO steps (project_id, nom_step, name, status, step_order) VALUES (?,?,?,'pending',?)");
            $steps = ['Finaliser', 'Vérifier', 'Commande', 'Réception'];
            foreach ($steps as $i => $s) {
                $ist->execute([$pid, $s, $s, $i + 1]);
            }

            $pdo->commit();
            logAction($uid, '📁 Projet créé', $title);
            jsonResponse(['success' => true, 'id' => $pid]);

        /* ══════════════════════════════
           POINTAGES — historique
        ══════════════════════════════ */
        case 'pointages_list':
            $filterUser = (int)($_GET['user_id'] ?? 0);
            $filterDate = $_GET['date'] ?? '';
            $limit      = (int)($_GET['limit'] ?? 50);

            $where  = ['1=1'];
            $params = [];

            if ($filterUser) { $where[] = 'ds.user_id = ?'; $params[] = $filterUser; }
            if ($filterDate) { $where[] = 'ds.date_score = ?'; $params[] = $filterDate; }

            $stmt = $pdo->prepare("
                SELECT
                    ds.date_score AS date,
                    ds.check_in,
                    ds.check_out,
                    ds.is_absent,
                    ds.note,
                    CONCAT(u.prenom,' ',u.nom) AS metro_name,
                    us.classeur_number,
                    TIMEDIFF(ds.check_out, ds.check_in) AS duree,
                    CASE
                        WHEN ds.is_absent = 1 THEN 'absent'
                        WHEN TIME(ds.check_in) > '08:15:00' THEN 'late'
                        ELSE 'ok'
                    END AS statut
                FROM daily_scores ds
                JOIN users u ON u.id = ds.user_id
                LEFT JOIN user_settings us ON us.user_id = ds.user_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY ds.date_score DESC
                LIMIT $limit
            ");
            $stmt->execute($params);
            jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));

        /* ══════════════════════════════
           SCORES — classement
        ══════════════════════════════ */
        case 'scores_list':
            $rows = $pdo->query("
                SELECT
                    u.id,
                    CONCAT(u.prenom,' ',u.nom) AS name,
                    COALESCE(qs.total_score, 0) AS score,
                    COALESCE(qs.tasks_done, 0) AS tasks_done,
                    COALESCE(qs.absences, 0) AS absences
                FROM users u
                LEFT JOIN quarter_scores qs ON qs.user_id = u.id
                    AND qs.quarter = QUARTER(NOW())
                    AND qs.year = YEAR(NOW())
                WHERE u.role = 'metrologue' AND u.is_active = 1
                ORDER BY score DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse($rows);

        /* ══════════════════════════════
           SCORES — reset trimestre
        ══════════════════════════════ */
        case 'scores_reset':
            $pdo->query("
                UPDATE quarter_scores
                SET total_score=0, tasks_done=0, absences=0
                WHERE quarter=QUARTER(NOW()) AND year=YEAR(NOW())
            ");
            logAction($uid, '🔄 Scores remis à zéro', 'Nouveau trimestre');
            jsonResponse(['success' => true]);

        /* ══════════════════════════════
           CHAMPION — publier
        ══════════════════════════════ */
        case 'champion_publish':
            $data   = json_decode(file_get_contents('php://input'), true);
            $userId = (int)($data['user_id'] ?? 0);
            $_SESSION['champion'] = [
                'user_id'   => $userId,
                'quarter'   => date('Q'),
                'year'      => date('Y'),
                'published' => true
            ];
            logAction($uid, '📢 Champion publié', "User #$userId");
            jsonResponse(['success' => true]);

        /* ══════════════════════════════
           HORAIRES — lire
        ══════════════════════════════ */
        case 'horaires_get':
            $rows = $pdo->query("
                SELECT
                    u.id,
                    CONCAT(u.prenom,' ',u.nom) AS name,
                    COALESCE(us.work_start, '08:00') AS work_start,
                    COALESCE(us.work_end, '17:00')   AS work_end
                FROM users u
                LEFT JOIN user_settings us ON us.user_id = u.id
                WHERE u.role = 'metrologue' AND u.is_active = 1
                ORDER BY u.nom
            ")->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse($rows);

        /* ══════════════════════════════
           HORAIRES — sauvegarder
        ══════════════════════════════ */
        case 'horaires_save':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("
                INSERT INTO user_settings (user_id, work_start, work_end)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    work_start = VALUES(work_start),
                    work_end   = VALUES(work_end)
            ");
            foreach (($data['horaires'] ?? []) as $h) {
                $stmt->execute([(int)$h['user_id'], $h['work_start'], $h['work_end']]);
            }
            logAction($uid, '⏰ Horaires mis à jour', count($data['horaires']) . ' métrologues');
            jsonResponse(['success' => true]);

        /* ══════════════════════════════
           EXPORT CSV pointages
        ══════════════════════════════ */
        case 'export_pointages':
            $rows = $pdo->query("
                SELECT
                    CONCAT(u.prenom,' ',u.nom)         AS Métrologue,
                    us.classeur_number                  AS Classeur,
                    ds.date_score                       AS Date,
                    ds.check_in                         AS Arrivée,
                    ds.check_out                        AS Départ,
                    TIMEDIFF(ds.check_out, ds.check_in) AS Durée,
                    IF(ds.is_absent=1,'Absent',
                        IF(TIME(ds.check_in)>'08:15','Retard','À l heure')
                    ) AS Statut,
                    ds.note AS Note
                FROM daily_scores ds
                JOIN users u ON u.id = ds.user_id
                LEFT JOIN user_settings us ON us.user_id = ds.user_id
                ORDER BY ds.date_score DESC, u.nom
            ")->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=pointages_' . date('Y-m-d') . '.csv');
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Métrologue','Classeur','Date','Arrivée','Départ','Durée','Statut','Note'], ';');
            foreach ($rows as $r) fputcsv($out, $r, ';');
            fclose($out);
            exit;

        /* ══════════════════════════════
           EXPORT CSV projets
        ══════════════════════════════ */
        case 'export_projets':
            $rows = $pdo->query("
                SELECT
                    p.nom_projet                        AS 'Nom Projet',
                    CONCAT(u.prenom,' ',u.nom)          AS 'Métrologue',
                    COALESCE(p.status, p.statut)        AS 'Statut',
                    p.priority                          AS 'Priorité',
                    p.date_fin                          AS 'Échéance',
                    p.date_creation                     AS 'Créé le',
                    (SELECT COUNT(*) FROM steps WHERE project_id=p.id AND status='done') AS 'Tâches OK',
                    (SELECT COUNT(*) FROM steps WHERE project_id=p.id) AS 'Total Tâches'
                FROM projects p
                LEFT JOIN users u ON u.id = COALESCE(p.assigned_to, p.user_id)
                ORDER BY p.date_creation DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=projets_' . date('Y-m-d') . '.csv');
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            if ($rows) fputcsv($out, array_keys($rows[0]), ';');
            foreach ($rows as $r) fputcsv($out, $r, ';');
            fclose($out);
            exit;

        /* ══════════════════════════════
           SUPPRIMER anciens pointages
        ══════════════════════════════ */
        case 'delete_old':
            $data   = json_decode(file_get_contents('php://input'), true);
            $before = $data['before_date'] ?? '';
            if (!$before || !strtotime($before))
                jsonResponse(['error' => 'Date invalide'], 400);

            $cnt = $pdo->prepare("SELECT COUNT(*) FROM daily_scores WHERE date_score < ?");
            $cnt->execute([$before]);
            $count = $cnt->fetchColumn();

            $pdo->prepare("DELETE FROM daily_scores WHERE date_score < ?")->execute([$before]);

            logAction($uid, '🗑️ Suppression anciens pointages', "Avant $before ($count lignes)");
            jsonResponse(['success' => true, 'deleted' => $count]);

        /* ══════════════════════════════
           CHANGER MOT DE PASSE
        ══════════════════════════════ */
        case 'change_password':
            $data = json_decode(file_get_contents('php://input'), true);
            $old  = $data['old_password'] ?? '';
            $new  = $data['new_password'] ?? '';

            if (strlen($new) < 6)
                jsonResponse(['error' => 'Mot de passe trop court (6 min)'], 400);

            $stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id=?");
            $stmt->execute([$uid]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($old, $hash))
                jsonResponse(['error' => 'Ancien mot de passe incorrect'], 403);

            $pdo->prepare("UPDATE users SET mot_de_passe=? WHERE id=?")
                ->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);

            logAction($uid, '🔑 Mot de passe modifié', '');
            jsonResponse(['success' => true]);

        /* ══════════════════════════════
           MODIFIER PROFIL
        ══════════════════════════════ */
        case 'update_profile':
            $data  = json_decode(file_get_contents('php://input'), true);
            $name  = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');

            if (!$name || !$email)
                jsonResponse(['error' => 'Champs obligatoires'], 400);

            $parts  = explode(' ', $name, 2);
            $prenom = $parts[0] ?? '';
            $nom    = $parts[1] ?? '';

            $pdo->prepare("UPDATE users SET nom=?, prenom=?, email=? WHERE id=?")
                ->execute([$nom, $prenom, $email, $uid]);

            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;

            logAction($uid, '👤 Profil mis à jour', $name);
            jsonResponse(['success' => true]);

        /* ══════════════════════════════
           JOURNAL
        ══════════════════════════════ */
        case 'journal_get':
            jsonResponse($_SESSION['journal'] ?? []);

        /* ══════════════════════════════
           BACKUP JSON
        ══════════════════════════════ */
        case 'backup':
            $users     = $pdo->query("SELECT id, CONCAT(prenom,' ',nom) AS name, email, role FROM users WHERE role='metrologue' AND is_active=1")->fetchAll(PDO::FETCH_ASSOC);
            $projects  = $pdo->query("SELECT id, nom_projet, statut, date_creation FROM projects")->fetchAll(PDO::FETCH_ASSOC);
            $scores    = $pdo->query("SELECT * FROM quarter_scores")->fetchAll(PDO::FETCH_ASSOC);
            $pointages = $pdo->query("SELECT ds.date_score, ds.check_in, ds.check_out, ds.is_absent, CONCAT(u.prenom,' ',u.nom) AS name FROM daily_scores ds JOIN users u ON u.id=ds.user_id ORDER BY date_score DESC LIMIT 1000")->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename=cm2e_backup_' . date('Y-m-d') . '.json');
            echo json_encode(compact('users','projects','scores','pointages'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            logAction($uid, '💾 Backup téléchargé', date('Y-m-d H:i'));
            exit;

        /* ══════════════════════════════
           REDÉMARRAGE SYSTÈME
        ══════════════════════════════ */
        case 'system_restart':
            logAction($uid, '🔄 Redémarrage système', 'Signal envoyé');
            jsonResponse(['success' => true, 'message' => 'Signal envoyé aux dispositifs']);

        default:
            jsonResponse(['error' => "Action '$action' inconnue"], 404);
    }

} catch (PDOException $e) {
    jsonResponse(['error' => 'Erreur BDD : ' . $e->getMessage()], 500);
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}