<?php

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../lang_init.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    jsonResponse(['error' => 'Non autorisé'], 401);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {

    switch (true) {

        // DASHBOARD
        case str_starts_with($action, 'dashboard'):
            require __DIR__ . '/api/dashboard.php';
            break;

        // METROLOGUES
        case str_starts_with($action, 'metro') || str_starts_with($action, 'metrologues'):
            require __DIR__ . '/api/metrologues.php';
            break;

        // PROJETS
        case str_starts_with($action, 'projet'):
            require __DIR__ . '/api/projets.php';
            break;

        // POINTAGES
        case str_starts_with($action, 'pointages'):
            require __DIR__ . '/api/pointages.php';
            break;

        // SCORES
        case str_starts_with($action, 'scores') || str_starts_with($action, 'champion'):
            require __DIR__ . '/api/scores.php';
            break;

        // SYSTEME (reste)
        default:
            require __DIR__ . '/api/systeme.php';
            break;
    }

} catch (PDOException $e) {
    jsonResponse(['error' => 'Erreur BDD : ' . $e->getMessage()], 500);
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}