<?php

$uid = $_SESSION['user_id'];

switch ($action) {

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
        break;


    case 'projet_add':

        $data    = json_decode(file_get_contents('php://input'), true);
        $title   = trim($data['title'] ?? '');
        $metroId = (int)($data['assigned_to'] ?? 0);

        if (!$title || !$metroId) {
            jsonResponse(['error' => 'Titre et métrologue obligatoires'], 400);
        }

        try {

            $pdo->beginTransaction();

            $ins = $pdo->prepare("
                INSERT INTO projects 
                (nom_projet, user_id, assigned_to, statut, status, date_fin, description, priority, date_creation) 
                VALUES (?,?,?,'en_attente','pending',?,?,?,NOW())
            ");

            $ins->execute([
                $title, $metroId, $metroId,
                $data['deadline'] ?? null,
                $data['description'] ?? '',
                $data['priority'] ?? 'normale'
            ]);

            $pid = $pdo->lastInsertId();

            $ist = $pdo->prepare("
                INSERT INTO steps (project_id, nom_step, name, status, step_order)
                VALUES (?,?,?,'pending',?)
            ");

            $steps = ['Finaliser', 'Vérifier', 'Commande', 'Réception'];

            foreach ($steps as $i => $s) {
                $ist->execute([$pid, $s, $s, $i + 1]);
            }

            $pdo->commit();

            logAction($uid, '📁 Projet créé', $title);

            jsonResponse(['success' => true, 'id' => $pid]);

        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse(['error' => $e->getMessage()], 500);
        }

        break;


    default:
        jsonResponse(['error' => 'Action inconnue'], 404);
        break;
}