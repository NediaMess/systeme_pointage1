<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['lang']))   $_SESSION['lang']   = $_POST['lang'];
    if (isset($_POST['theme']))  $_SESSION['theme']  = $_POST['theme'];
    if (isset($_POST['taille'])) $_SESSION['taille'] = $_POST['taille'];

    // Redirect back to same page after saving preferences
    if (isset($_POST['_redirect'])) {
        header("Location: " . $_POST['_redirect']);
        exit();
    }
}

$lang_code = $_SESSION['lang'] ?? 'fr';
$path = __DIR__ . "/lang/$lang_code.php";

if (file_exists($path)) {
    $lang = require $path;
} else {
    $lang = require __DIR__ . "/lang/fr.php";
}

/* ================= TRANSLATION FUNCTION ================= */
if (!function_exists('__')) {
    function __($key) {
        global $lang;
        return $lang[$key] ?? $key;
    }
}