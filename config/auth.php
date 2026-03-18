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

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
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