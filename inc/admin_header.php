<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion UVCI</title>
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
                <!-- ADMINISTRATION -->
                <li class="menu-header">ADMINISTRATION</li>
                <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">Utilisateurs</a></li>
                <li><a href="enseignants.php" class="<?= basename($_SERVER['PHP_SELF']) == 'enseignants.php' ? 'active' : '' ?>">Enseignants</a></li>
                <li><a href="annees.php" class="<?= basename($_SERVER['PHP_SELF']) == 'annees.php' ? 'active' : '' ?>">Années académiques</a></li>
                <li><a href="parametres.php" class="<?= basename($_SERVER['PHP_SELF']) == 'parametres.php' ? 'active' : '' ?>">Paramètres</a></li>
                <li><a href="taux.php" class="<?= basename($_SERVER['PHP_SELF']) == 'taux.php' ? 'active' : '' ?>">Barèmes & Taux</a></li>
                <li><a href="departements.php" class="<?= basename($_SERVER['PHP_SELF']) == 'departements.php' ? 'active' : '' ?>">Départements</a></li>
                <li><a href="stats.php" class="<?= basename($_SERVER['PHP_SELF']) == 'stats.php' ? 'active' : '' ?>">Statistiques</a></li>

                <!-- RAPPORTS & STATISTIQUES -->
                <li class="menu-header">RAPPORTS & STATISTIQUES</li>
                <li><a href="export_enseignants_pdf.php">PDF Enseignants</a></li>
                <li><a href="export_enseignants_excel.php">Excel Enseignants</a></li>
                <li><a href="export_activites_pdf.php">PDF Activités</a></li>
                <li><a href="export_activites_excel.php">Excel Activités</a></li>

                <!-- SCOLARITÉ -->
                <li class="menu-header">SCOLARITÉ</li>
                <li><a href="scolarite_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'scolarite_dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                <li><a href="cours_scolarite.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cours_scolarite.php' ? 'active' : '' ?>">Cours</a></li>
                <li><a href="sequences_scolarite.php" class="<?= basename($_SERVER['PHP_SELF']) == 'sequences_scolarite.php' ? 'active' : '' ?>">Séquences</a></li>
                <li><a href="ressources_scolarite.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ressources_scolarite.php' ? 'active' : '' ?>">Ressources</a></li>
                <li><a href="enseignants_scolarite.php" class="<?= basename($_SERVER['PHP_SELF']) == 'enseignants_scolarite.php' ? 'active' : '' ?>">Enseignants</a></li>
                <li><a href="activites_liste_scolarite.php" class="<?= basename($_SERVER['PHP_SELF']) == 'activites_liste_scolarite.php' ? 'active' : '' ?>">Activités</a></li>
                <li><a href="activites_validation_scolarite.php" class="<?= basename($_SERVER['PHP_SELF']) == 'activites_validation_scolarite.php' ? 'active' : '' ?>">Validation</a></li>
                <li><a href="volumes_scolarite.php" class="<?= basename($_SERVER['PHP_SELF']) == 'volumes_scolarite.php' ? 'active' : '' ?>">Volumes</a></li>
                <li><a href="rapports_scolarite.php" class="<?= basename($_SERVER['PHP_SELF']) == 'rapports_scolarite.php' ? 'active' : '' ?>">Rapports</a></li>
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
