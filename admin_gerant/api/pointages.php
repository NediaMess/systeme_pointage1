<?php

switch ($action) {

    case 'pointages_list':

        $filterUser = (int)($_GET['user_id'] ?? 0);
        $filterDate = $_GET['date'] ?? '';
        $limit      = max(1, min(100, (int)($_GET['limit'] ?? 50)));

        $where  = ['1=1'];
        $params = [];

        if ($filterUser) {
            $where[] = 'ds.user_id = ?';
            $params[] = $filterUser;
        }

        if ($filterDate) {
            $where[] = 'ds.date_score = ?';
            $params[] = $filterDate;
        }

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
        break;


    default:
        jsonResponse(['error' => 'Action inconnue'], 404);
        break;
}