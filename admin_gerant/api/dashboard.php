<?php

switch ($action) {

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

        // version sécurisée
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM daily_scores WHERE date_score=? AND is_absent=1");
        $stmt->execute([date('Y-m-d')]);
        $stats['absences'] = $stmt->fetchColumn();

        $stats['projets_done'] = $pdo->query(
            "SELECT COUNT(*) FROM projects WHERE status='done' OR statut='termine'"
        )->fetchColumn();

        jsonResponse($stats);
        break;


    default:
        jsonResponse(['error' => 'Action inconnue'], 404);
        break;
}