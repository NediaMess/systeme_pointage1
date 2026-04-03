<?php

$uid = $_SESSION['user_id'];

switch ($action) {

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
        break;


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
            $stmt->execute([
                (int)$h['user_id'],
                $h['work_start'],
                $h['work_end']
            ]);
        }

        logAction($uid, '⏰ Horaires mis à jour', count($data['horaires']) . ' métrologues');

        jsonResponse(['success' => true]);
        break;


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

        foreach ($rows as $r) {
            fputcsv($out, $r, ';');
        }

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

        if ($rows) {
            fputcsv($out, array_keys($rows[0]), ';');
        }

        foreach ($rows as $r) {
            fputcsv($out, $r, ';');
        }

        fclose($out);
        exit;


    /* ══════════════════════════════
       SUPPRIMER anciens pointages
    ══════════════════════════════ */
    case 'delete_old':

        $data   = json_decode(file_get_contents('php://input'), true);
        $before = $data['before_date'] ?? '';

        if (!$before || !strtotime($before)) {
            jsonResponse(['error' => 'Date invalide'], 400);
        }

        $cnt = $pdo->prepare("SELECT COUNT(*) FROM daily_scores WHERE date_score < ?");
        $cnt->execute([$before]);
        $count = $cnt->fetchColumn();

        $pdo->prepare("DELETE FROM daily_scores WHERE date_score < ?")
            ->execute([$before]);

        logAction($uid, '🗑️ Suppression anciens pointages', "Avant $before ($count lignes)");

        jsonResponse(['success' => true, 'deleted' => $count]);
        break;


    /* ══════════════════════════════
       CHANGER MOT DE PASSE
    ══════════════════════════════ */
    case 'change_password':

        $data = json_decode(file_get_contents('php://input'), true);

        $old = $data['old_password'] ?? '';
        $new = $data['new_password'] ?? '';

        if (strlen($new) < 6) {
            jsonResponse(['error' => 'Mot de passe trop court (6 min)'], 400);
        }

        $stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id=?");
        $stmt->execute([$uid]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($old, $hash)) {
            jsonResponse(['error' => 'Ancien mot de passe incorrect'], 403);
        }

        $pdo->prepare("UPDATE users SET mot_de_passe=? WHERE id=?")
            ->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);

        logAction($uid, '🔑 Mot de passe modifié', '');

        jsonResponse(['success' => true]);
        break;


    /* ══════════════════════════════
       MODIFIER PROFIL
    ══════════════════════════════ */
    case 'update_profile':

        $data  = json_decode(file_get_contents('php://input'), true);
        $name  = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');

        if (!$name || !$email) {
            jsonResponse(['error' => 'Champs obligatoires'], 400);
        }

        $parts  = explode(' ', $name, 2);
        $prenom = $parts[0] ?? '';
        $nom    = $parts[1] ?? '';

        $pdo->prepare("UPDATE users SET nom=?, prenom=?, email=? WHERE id=?")
            ->execute([$nom, $prenom, $email, $uid]);

        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;

        logAction($uid, '👤 Profil mis à jour', $name);

        jsonResponse(['success' => true]);
        break;


    /* ══════════════════════════════
       JOURNAL
    ══════════════════════════════ */
    case 'journal_get':

        jsonResponse($_SESSION['journal'] ?? []);
        break;


    /* ══════════════════════════════
       BACKUP JSON
    ══════════════════════════════ */
    case 'backup':

        $users = $pdo->query("
            SELECT id, CONCAT(prenom,' ',nom) AS name, email, role
            FROM users
            WHERE role='metrologue' AND is_active=1
        ")->fetchAll(PDO::FETCH_ASSOC);

        $projects  = $pdo->query("SELECT id, nom_projet, statut, date_creation FROM projects")->fetchAll(PDO::FETCH_ASSOC);
        $scores    = $pdo->query("SELECT * FROM quarter_scores")->fetchAll(PDO::FETCH_ASSOC);
        $pointages = $pdo->query("
            SELECT ds.date_score, ds.check_in, ds.check_out, ds.is_absent,
                   CONCAT(u.prenom,' ',u.nom) AS name
            FROM daily_scores ds
            JOIN users u ON u.id=ds.user_id
            ORDER BY date_score DESC LIMIT 1000
        ")->fetchAll(PDO::FETCH_ASSOC);

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

        jsonResponse([
            'success' => true,
            'message' => 'Signal envoyé aux dispositifs'
        ]);
        break;


    default:
        jsonResponse(['error' => 'Action inconnue'], 404);
        break;
}