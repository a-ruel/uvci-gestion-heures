<?php
require_once 'inc/config.php';
session_start();

$erreur = '';
$message = '';

// Vérifier que l'utilisateur a bien demandé une réinitialisation
if (!isset($_SESSION['reset_user_id'])) {
    header('Location: forgot_password.php');
    exit;
}

$user_id = $_SESSION['reset_user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    if (empty($otp)) {
        $erreur = "Veuillez saisir le code OTP.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE utilisateur_id = ? AND otp = ? AND used = 0 AND expiration > NOW()");
        $stmt->execute([$user_id, $otp]);
        $reset = $stmt->fetch();
        if ($reset) {
            // Marquer comme utilisé pour éviter une réutilisation
            $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$reset['id']]);
            $_SESSION['reset_verified'] = true;
            header('Location: reset_password.php');
            exit;
        } else {
            $erreur = "Code OTP invalide ou expiré. Veuillez refaire la demande.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du code - UVCI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* mêmes styles que forgot_password.php */
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
        .login-container { width: 100%; max-width: 480px; padding: 1rem; }
        .login-card { background: white; border-radius: 32px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); padding: 2rem; }
        .login-header { text-align: center; margin-bottom: 2rem; }
        .login-logo img { max-width: 180px; height: auto; display: block; margin: 0 auto 1rem auto; }
        .login-header h1 { font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; }
        .login-header p { color: #6b7280; font-size: 0.9rem; margin: 0; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 0.75rem 1rem; border: 1px solid #e5e7eb; border-radius: 12px; font-size: 1rem; background-color: #f9fafb; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); background-color: white; }
        .login-btn { width: 100%; background: #831C91; color: white; border: none; padding: 0.75rem; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .login-btn:hover { background: #5e1368; }
        .login-footer { text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 0.75rem; }
        .alert { padding: 1rem; border-radius: 12px; margin-bottom: 1rem; }
        .success { background-color: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .error { background-color: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="assets/images/logo1.jpeg" alt="UVCI">
                </div>
                <h1>Vérification</h1>
                <p>Saisissez le code reçu par email</p>
            </div>
            <?php if ($message): ?><div class="alert success"><?= $message ?></div><?php endif; ?>
            <?php if ($erreur): ?><div class="alert error"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>
            <div class="login-form">
                <form method="post" action="">
                    <div class="form-group">
                        <label>Code OTP</label>
                        <input type="text" name="otp" placeholder="123456" required autofocus>
                    </div>
                    <button type="submit" class="login-btn">Vérifier</button>
                </form>
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="forgot_password.php">← Retour</a>
                </div>
            </div>
            <div class="login-footer">
                <p>© 2026 UVCI System</p>
            </div>
        </div>
    </div>
</body>
</html>
