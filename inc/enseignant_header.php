<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Enseignant - UVCI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="logo">
            <img src="../assets/images/logo1.jpeg" alt="UVCI" style="height: 60px;">
        </div>
        <nav>
            <ul>
                <li class="menu-header">ESPACE ENSEIGNANT</li>
                <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">📊 Tableau de bord</a></li>
                <li><a href="activites.php" class="<?= basename($_SERVER['PHP_SELF']) == 'activites.php' ? 'active' : '' ?>">📋 Mes activités</a></li>
                <li><a href="volume.php" class="<?= basename($_SERVER['PHP_SELF']) == 'volume.php' ? 'active' : '' ?>">📈 Volumes horaires</a></li>
                <li><a href="telecharger.php" class="<?= basename($_SERVER['PHP_SELF']) == 'telecharger.php' ? 'active' : '' ?>">📄 Ma fiche (PDF)</a></li>
            </ul>
        </nav>
    </aside>
    <main class="admin-content">
        <div class="admin-header-bar">
            <h1><?= $page_title ?? 'Tableau de bord' ?></h1>
            <div class="user-info">
                <span>Bonjour, <?= htmlspecialchars($_SESSION['nom_utilisateur'] ?? 'Enseignant') ?></span>
                <a href="../logout.php" class="button button-small button-danger">Déconnexion</a>
            </div>
        </div>
