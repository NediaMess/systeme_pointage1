<?php
require_once "../lang_init.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}
$page = $_GET['page'] ?? 'tableau_bord';
if(isset($_POST['theme'])){
    $_SESSION['theme'] = $_POST['theme'];
}
if(isset($_POST['taille'])){
    $_SESSION['taille'] = $_POST['taille'];
}


/* Sécurité : pages autorisées */
$pages_autorisees = [
    'tableau_bord',
    'calendrier_performance',
    'projets',
    'projet_courant',
    'parametres',
    'profil_utilisateur', 
    'preferences_affichage',
    'param_calendrier',
    'securite_compte',
    'apropos'
];

if(!in_array($page, $pages_autorisees)){
    $page = 'tableau_bord';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
       body {
       margin: 0;
       font-family: 'Segoe UI', sans-serif;
   }
        .sidebar {
            width: 270px;
            height: 100vh;
            background-color: #e53935;
            position: fixed;
            padding: 20px;
            box-sizing: border-box;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .logo {
            background: white;
            padding: 10px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
            color: black;
        }

        .profile {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
        }

        .menu a {
            display: block;
            background: #d32f2f;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 8px;
            color: white;
            text-decoration: none;
        }

        .menu a:hover {
            background: #b71c1c;
        }

        .logout {
            margin-top: auto;
        }

        .logout a {
            display: block;
            background: #eeeeee;
            color: black;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
        }

        .content {
            margin-left: 290px;
            padding: 30px;
        }
        .pref-box {
    background: #dbe6f7;
    padding: 20px;
    border-radius: 10px;
    width: 500px;
    border: 1px solid #b0c4de;
}

.pref-box h3 {
    margin-top: 0;
}
body.light {
    background-color: #f0f2f5;
    color: #000;
}

body.dark {
    background-color: #2b2b2b;   /* plus doux */
    color: #f1f1f1;
}

body.dark .sidebar {
    background-color: #202020;
}

body.dark .menu a {
    background-color: #2f2f2f;
}

body.dark .menu a:hover {
    background-color: #3a3a3a;
}

body.dark .pref-box {
    background: #353535;
    border: 1px solid #555;
}
    </style>
</head>
<body class="<?= $_SESSION['theme'] ?? 'light' ?>">

<div class="sidebar">

    <div class="logo">
        <strong>CM2E</strong>
    </div>

    <div class="profile">
        <img src="../uploads/default.png" alt="Profile">
        <h4><?= htmlspecialchars($_SESSION['user_prenom'] . " " . $_SESSION['user_nom']); ?></h4>
        <small>Métrologue industriel</small>
    </div>

    <div class="menu">
        <a href="?page=tableau_bord">Tableau de bord</a>
        <a href="?page=calendrier_performance">Calendrier de performance</a>
        <a href="?page=projets">Projets</a>
        <a href="?page=projet_courant">Projet courant</a>
        <a href="?page=parametres">Paramètres</a>
    </div>

    <div class="logout">
        <a href="../auth/logout.php">Déconnexion</a>
    </div>

</div>

<div class="content">
    <h2>Bienvenue <?= htmlspecialchars($_SESSION['user_prenom']) ?></h2>

    <?php
    // Inclusion de la page AU BON ENDROIT
    include __DIR__ . '/' . $page . '.php';
    
    ?>
</div>

</body>
</html>