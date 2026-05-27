<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Gestion des utilisateurs";
include '../inc/admin_header.php';

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != 1) {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Utilisateur supprimé avec succès.";
    } else {
        $message = "Impossible de supprimer l'utilisateur principal.";
    }
    header("Location: users.php?msg=" . urlencode($message));
    exit;
}

// Message flash
$message = '';
if (isset($_GET['msg'])) {
    $message = htmlspecialchars(urldecode($_GET['msg']));
} elseif (isset($_GET['ajoute'])) {
    $message = "Utilisateur créé avec succès.";
} elseif (isset($_GET['modifie'])) {
    $message = "Utilisateur modifié avec succès.";
}

// Récupération des filtres
$search = trim($_GET['search'] ?? '');
$role_filter = $_GET['role'] ?? '';

// Construction de la requête
$sql = "SELECT id, nom_utilisateur, role, actif, dernier_acces FROM utilisateurs WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND nom_utilisateur LIKE :search";
    $params['search'] = "%$search%";
}
if ($role_filter !== '' && $role_filter !== 'tous') {
    $sql .= " AND role = :role";
    $params['role'] = $role_filter;
}
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
$count = count($users);
?>
<div class="admin-header-bar">
    <div>
        <h1>Utilisateurs</h1>
        <p>Gestion des comptes utilisateurs</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a class="button button-outline" href="export_users_pdf.php<?= ($search ? '?search='.urlencode($search) : '') . ($role_filter && $role_filter!='tous' ? '&role='.$role_filter : '') ?>" class="button">PDF</a>
        <a class="button button-outline" href="export_users_excel.php<?= ($search ? '?search='.urlencode($search) : '') . ($role_filter && $role_filter!='tous' ? '&role='.$role_filter : '') ?>" class="button">Excel</a>
        <a href="users_create.php" class="button button-success">+ Nouveau</a>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert success"><?= $message ?></div>
<?php endif; ?>

<!-- Barre de recherche et filtre -->
<form method="get" action="" style="margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <input type="text" name="search" placeholder="Rechercher par email..." value="<?= htmlspecialchars($search) ?>" style="flex: 2; padding: 0.6rem; border-radius: 12px; border: 1px solid #e2e8f0;">
        <select name="role" style="padding: 0.6rem; border-radius: 12px; border: 1px solid #e2e8f0;">
            <option value="tous" <?= $role_filter == '' || $role_filter == 'tous' ? 'selected' : '' ?>>Tous les rôles</option>
            <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="secretaire" <?= $role_filter == 'secretaire' ? 'selected' : '' ?>>Secrétaire</option>
            <option value="enseignant" <?= $role_filter == 'enseignant' ? 'selected' : '' ?>>Enseignant</option>
        </select>
        <button type="submit" class="button button-outline">Filtrer</button>
        <?php if ($search || ($role_filter && $role_filter != 'tous')): ?>
            <a href="users.php" class="button button-small button-outline">Réinitialiser</a>
        <?php endif; ?>
    </div>
</form>

<!-- Tableau des utilisateurs -->
<table class="admin-table">
    <thead>
        <tr>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actif</th>
            <th>Dernier accès</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($count === 0): ?>
            <tr>
                <td colspan="5" style="text-align: center;">Aucun utilisateur trouvé.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nom_utilisateur']) ?></td>
                <td><?= strtoupper($u['role']) ?></td>
                <td><?= $u['actif'] ? '✅ Oui' : '❌ Non' ?></td>
                <td><?= $u['dernier_acces'] ?: 'Jamais' ?></td>
                <td style="display: flex; gap: 24px; align-items: center;">
                    <a href="users_edit.php?id=<?= $u['id'] ?>" class="button button-small button-primary">Modifier</a>
                    <?php if ($u['id'] != 1): ?>
                        <a href="users.php?delete=<?= $u['id'] ?>" class="button button-small button-danger" onclick="return confirm('Supprimer définitivement cet utilisateur ?')">Supprimer</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../inc/admin_footer.php'; ?>
