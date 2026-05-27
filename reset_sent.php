<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email envoyé - UVCI</title>
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
            margin: 0 0 1rem 0;
        }
        .login-btn {
            display: inline-block;
            background: #831C91;
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
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
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="assets/images/logo1.jpeg" alt="UVCI">
                </div>
                <h1>Email envoyé</h1>
                <p>Un lien de réinitialisation a été envoyé à votre adresse email.</p>
                <p>Consultez votre boîte de réception (et vos spams) pour continuer.</p>
            </div>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="login.php" class="login-btn">Retour à la connexion</a>
            </div>
            <div class="login-footer">
                <p>© 2026 UVCI System</p>
            </div>
        </div>
    </div>
</body>
</html>
