<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UVCI - Gestion pédagogique</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="dashboard.php">
                <img src="/assets/images/logo1.jpeg" alt="UVCI">
            </a>
        </div>
        <div class="header-title">
            <h1>UVCI - Gestion pédagogique</h1>
        </div>
        <div class="header-actions">
            <?php if (isset($_SESSION['nom_utilisateur'])): ?>
                <span class="user-greeting">Bonjour, <?= htmlspecialchars($_SESSION['nom_utilisateur']) ?></span>
                <a href="profile.php" class="button button-outline">Mon profil</a>
                <a href="logout.php" class="button button-danger">Déconnexion</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="main-content">
