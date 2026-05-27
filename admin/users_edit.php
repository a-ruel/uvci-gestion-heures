<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Modification de l'utilisateur";
include '../inc/admin_header.php';

$id = (int)$_GET['id'];
if (!$id) die("ID manquant");

$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) die("Utilisateur introuvable");

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $actif = isset($_POST['actif']) ? 1 : 0;
    $new_password = $_POST['new_password'] ?? '';
    $enseignant_id = $_POST['enseignant_id'] ? (int)$_POST['enseignant_id'] : null;

    if (empty($username)) $erreurs[] = "Nom d'utilisateur obligatoire";
    if (!in_array($role, ['admin', 'secretaire', 'enseignant'])) $erreurs[] = "Rôle invalide";

    if (empty($erreurs)) {
        $sql = "UPDATE utilisateurs SET nom_utilisateur = ?, role = ?, actif = ?, enseignant_id = ? WHERE id = ?";
        $params = [$username, $role, $actif, $enseignant_id, $id];
        if (!empty($new_password)) {
            if (strlen($new_password) < 4) {
                $erreurs[] = "Mot de passe trop court (min 4)";
            } else {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE utilisateurs SET nom_utilisateur = ?, mot_de_passe_hash = ?, role = ?, actif = ?, enseignant_id = ? WHERE id = ?";
                $params = [$username, $hash, $role, $actif, $enseignant_id, $id];
            }
        }
        if (empty($erreurs)) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            header('Location: users.php?msg=modifie');
            exit;
        }
    }
}

$enseignants = $pdo->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom")->fetchAll();
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
        <input type="text" name="username" value="<?= htmlspecialchars($user['nom_utilisateur']) ?>" required>
    </div>
    <div class="admin-form-group">
        <label>Nouveau mot de passe</label>
        <input type="password" name="new_password" placeholder="••••••••">
        <small>Laissez vide pour ne pas changer (minimum 4 caractères)</small>
    </div>
    <div class="admin-form-group">
        <label>Rôle</label>
        <select name="role">
            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrateur</option>
            <option value="secretaire" <?= $user['role'] == 'secretaire' ? 'selected' : '' ?>>Secrétaire principal</option>
            <option value="enseignant" <?= $user['role'] == 'enseignant' ? 'selected' : '' ?>>Enseignant</option>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Lier à un enseignant</label>
        <select name="enseignant_id">
            <option value="">-- Aucun --</option>
            <?php foreach ($enseignants as $e): ?>
                <option value="<?= $e['id'] ?>" <?= ($user['enseignant_id'] == $e['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-form-group">
        <label class="checkbox-label">
            <input type="checkbox" name="actif" <?= $user['actif'] ? 'checked' : '' ?>> Compte actif
        </label>
    </div>
    <div style="display: flex; gap: 1rem;">
        <button type="submit" class="button">Enregistrer</button>
        <a href="users.php" class="button button-outline">Annuler</a>
    </div>
</form>

<?php include '../inc/admin_footer.php'; ?>
