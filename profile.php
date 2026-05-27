<?php
require_once 'inc/config.php';
require_once 'inc/auth.php';
verifierAccesPage(['admin', 'secretaire', 'enseignant']);

$userId = $_SESSION['user_id'];
$message = '';

$stmt = $pdo->prepare("SELECT id, nom_utilisateur, role FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) die("Utilisateur introuvable.");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changer_mdp'])) {
    $ancien = $_POST['ancien_mdp'] ?? '';
    $nouveau = $_POST['nouveau_mdp'] ?? '';
    $confirmer = $_POST['confirmer_mdp'] ?? '';

    if (empty($ancien) || empty($nouveau) || $nouveau !== $confirmer) {
        $message = "Erreur : vérifiez les mots de passe.";
    } else {
        $check = $pdo->prepare("SELECT mot_de_passe_hash FROM utilisateurs WHERE id = ?");
        $check->execute([$userId]);
        $hash = $check->fetchColumn();
        if (password_verify($ancien, $hash)) {
            $newHash = password_hash($nouveau, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe_hash = ? WHERE id = ?");
            $update->execute([$newHash, $userId]);
            $message = "Mot de passe modifié avec succès.";
        } else {
            $message = "Ancien mot de passe incorrect.";
        }
    }
}
?>
<?php include 'inc/header.php'; ?>
<h1>Mon profil</h1>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<p><strong>Identifiant :</strong> <?= htmlspecialchars($user['nom_utilisateur']) ?></p>
<p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>
<h2>Changer le mot de passe</h2>
<form method="post" action="">
    <div><label>Ancien mot de passe :</label> <input type="password" name="ancien_mdp" required></div>
    <div><label>Nouveau mot de passe :</label> <input type="password" name="nouveau_mdp" required></div>
    <div><label>Confirmer :</label> <input type="password" name="confirmer_mdp" required></div>
    <button type="submit" name="changer_mdp">Changer le mot de passe</button>
</form>
<?php include 'inc/footer.php'; ?>
