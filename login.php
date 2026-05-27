<?php
require_once 'inc/config.php';
require_once 'inc/auth.php';

if (estConnecte()) {
    redirigerSelonRole();
    exit;
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, nom_utilisateur, mot_de_passe_hash, role, actif FROM utilisateurs WHERE nom_utilisateur = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $user['actif'] && password_verify($password, $user['mot_de_passe_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom_utilisateur'] = $user['nom_utilisateur'];
            $_SESSION['role'] = $user['role'];

            if ($remember) {
                ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
                session_regenerate_id(true);
            }

            $update = $pdo->prepare("UPDATE utilisateurs SET dernier_acces = NOW() WHERE id = ?");
            $update->execute([$user['id']]);

            redirigerSelonRole();
            exit;
        } else {
            $erreur = "La connexion a échoué, veuillez réessayer.";
        }
    } else {
        $erreur = "";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - UVCI System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .login-body {
            background: none;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        .login-body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('assets/images/back.png') no-repeat center center/cover;
            filter: blur(8px);
            z-index: -1;
        }
        .login-body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }
        .login-container {
            width: 100%;
            max-width: 480px;
            padding: 1rem;
        }
        .login-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            padding: 2rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo img {
            max-width: 180px;
            height: auto;
            display: block;
            margin: 0 auto 1rem auto;
        }
        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }
        .login-form h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
            text-align: center;
        }
        .login-form .subtitle {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s;
            background-color: #f9fafb;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
            background-color: white;
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #4b5563;
            cursor: pointer;
        }
        .checkbox input {
            width: 1rem;
            height: 1rem;
            margin: 0;
        }
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        .login-btn {
            width: 100%;
            background: #831C91;
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-btn:hover {
            background: #5e1368;
        }
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 0.75rem;
        }
        .login-error {
            color: #e74c3c;
            font-size: 0.8rem;
            text-align: center;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="assets/images/logo1.jpeg" alt="UVCI">
                </div>
                <h1>Connexion</h1>
                <p>Accédez à votre espace UVCI</p>
                <?php if (!empty($erreur)): ?>
                    <div class="login-error"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
            </div>
            <div class="login-form">
                <form method="post" action="">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" name="username" placeholder="Nom d'utilisateur ou courriel" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="password" placeholder="Mot de passe" required>
                    </div>
                    <div class="form-options">
                        <label class="checkbox">
                            <input type="checkbox" name="remember"> Se souvenir
                        </label>
                        <a href="forgot_password.php" class="forgot-password">Mot de passe oublié</a>
                    </div>
                    <button type="submit" class="login-btn">Se connecter</button>
                </form>
            </div>
            <div class="login-footer">
                <p>© 2026 UVCI System</p>
            </div>
        </div>
    </div>
</body>
</html>
