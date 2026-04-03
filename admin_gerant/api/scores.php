<?php

$uid = $_SESSION['user_id'];

switch ($action) {

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
        break;


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
        break;


    /* ══════════════════════════════
       CHAMPION — publier
    ══════════════════════════════ */
    case 'champion_publish':

        $data   = json_decode(file_get_contents('php://input'), true);
        $userId = (int)($data['user_id'] ?? 0);

        $_SESSION['champion'] = [
            'user_id'   => $userId,
            'quarter'   => date('n'), // ⚠️ corrigé (date('Q') n'existe pas)
            'year'      => date('Y'),
            'published' => true
        ];

        logAction($uid, '📢 Champion publié', "User #$userId");

        jsonResponse(['success' => true]);
        break;


    default:
        jsonResponse(['error' => 'Action inconnue'], 404);
        break;
}