<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Créer un utilisateur";
include '../inc/admin_header.php';

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'enseignant';
    $actif = isset($_POST['actif']) ? 1 : 0;

    if (empty($username)) $erreurs[] = "Nom d'utilisateur obligatoire";
    if (strlen($password) < 4) $erreurs[] = "Mot de passe trop court (min 4 caractères)";
    if (!in_array($role, ['admin', 'secretaire', 'enseignant'])) $erreurs[] = "Rôle invalide";

    if (empty($erreurs)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe_hash, role, actif) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $hash, $role, $actif])) {
            header('Location: users.php?msg=ajoute');
            exit;
        } else {
            $erreurs[] = "Erreur lors de la création (nom peut-être déjà utilisé)";
        }
    }
}
?>
<div class="admin-header-bar">
    <a href="users.php" class="button button-outline">← Retour à la liste</a>
</div>

<?php if ($erreurs): ?>
    <div class="alert error"><?= implode('<br>', $erreurs) ?></div>
<?php endif; ?>

<form method="post" class="admin-form">
    <div class="admin-form-group">
        <label>Nom d'utilisateur</label>
        <input type="text" name="username" required>
    </div>
    <div class="admin-form-group">
        <label>Mot de passe</label>
        <input type="password" name="password" required>
        <small>Minimum 4 caractères</small>
    </div>
    <div class="admin-form-group">
        <label>Rôle</label>
        <select name="role">
            <option value="admin">Administrateur</option>
            <option value="secretaire">Secrétaire principal</option>
            <option value="enseignant">Enseignant</option>
        </select>
    </div>
    <div class="admin-form-group">
        <label><input type="checkbox" name="actif" checked> Actif</label>
    </div>
    <div>
        <button type="submit" class="button">Créer</button>
        <a href="users.php" class="button button-outline">Annuler</a>
    </div>
</form>

<?php include '../inc/admin_footer.php'; ?>
