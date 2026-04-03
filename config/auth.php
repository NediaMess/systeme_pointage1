<?php
/**
 * config/auth.php
 * Vérification session gérant
 */

function requireGerant(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ../auth/login.php');
        exit;
    }
    return $_SESSION;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function logAction(int $userId, string $action, string $details = ''): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['journal'])) $_SESSION['journal'] = [];
    array_unshift($_SESSION['journal'], [
        'ico'     => '•',
        'action'  => $action,
        'details' => $details,
        'time'    => date('d/m/Y H:i'),
        'type'    => 'ok',
    ]);
    $_SESSION['journal'] = array_slice($_SESSION['journal'], 0, 50);
}