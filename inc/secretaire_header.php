<?php if (!isset($no_layout) || !$no_layout): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secrétaire - Gestion UVCI</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="logo">
            <img src="/assets/images/logo1.jpeg" alt="UVCI" style="height: 60px;">
        </div>
        <nav>
            <ul>
                <li class="menu-header">ADMINISTRATION</li>
                <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="enseignants.php" class="<?= basename($_SERVER['PHP_SELF']) == 'enseignants.php' ? 'active' : '' ?>">Enseignants</a></li>
                <li><a href="cours.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cours.php' ? 'active' : '' ?>">Cours</a></li>
                <li><a href="sequences.php?cours_id=1" class="<?= basename($_SERVER['PHP_SELF']) == 'sequences.php' ? 'active' : '' ?>">Séquences</a></li>
                <li><a href="ressources.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ressources.php' ? 'active' : '' ?>">Ressources</a></li>
                <li><a href="activites.php" class="<?= basename($_SERVER['PHP_SELF']) == 'activites.php' ? 'active' : '' ?>">Activités</a></li>
                <li><a href="activites_validation.php" class="<?= basename($_SERVER['PHP_SELF']) == 'activites_validation.php' ? 'active' : '' ?>">Validation</a></li>
                <li><a href="volumes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'volumes.php' ? 'active' : '' ?>">Volumes</a></li>
                <li><a href="rapports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rapports.php' ? 'active' : '' ?>">Rapports</a></li>
                <li><a href="charges_contractuelles.php" class="<?= basename($_SERVER['PHP_SELF']) == 'charges_contractuelles.php' ? 'active' : '' ?>">Charges contractuelles</a></li>
            </ul>
        </nav>
    </aside>
    <main class="admin-content">
        <div class="admin-header-bar">
            <h1><?= $page_title ?? 'Dashboard' ?></h1>
            <div class="user-info">
                <span>Bonjour, <?= htmlspecialchars($_SESSION['nom_utilisateur'] ?? 'Invité') ?></span>
                <a href="../logout.php" class="button button-small button-danger">Déconnexion</a>
            </div>
        </div>
<?php endif; ?>
