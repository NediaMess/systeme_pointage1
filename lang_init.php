<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['lang'])) {
        $_SESSION['lang'] = $_POST['lang'];
    }

    if (isset($_POST['theme'])) {
        $_SESSION['theme'] = $_POST['theme'];
    }

    if (isset($_POST['taille'])) {
        $_SESSION['taille'] = $_POST['taille'];
    }
}

$lang = $_SESSION['lang'] ?? 'fr';

$path = __DIR__ . "/lang/$lang.php";

if (file_exists($path)) {
    $lang = require $path;
} else {
    die("Fichier langue introuvable !");
}