<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $enseignant_id = (int)$_POST['enseignant_id'];
    $stmt = $pdo->prepare("UPDATE utilisateurs SET enseignant_id = ? WHERE id = ?");
    $stmt->execute([$enseignant_id, $user_id]);
    $message = "Liaison effectuée avec succès.";
}

$utilisateurs = $pdo->query("SELECT id, nom_utilisateur, role FROM utilisateurs WHERE role = 'enseignant' AND (enseignant_id IS NULL OR enseignant_id = 0)")->fetchAll();
$enseignants = $pdo->query("SELECT id, nom, prenom, email FROM enseignants ORDER BY nom")->fetchAll();
?>
<?php include '../inc/header.php'; ?>
<h1>Lier un compte enseignant à une fiche enseignant</h1>
<?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<form method="post" class="admin-form">
    <div class="admin-form-group">
        <label>Compte utilisateur (non lié)</label>
        <select name="user_id" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($utilisateurs as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nom_utilisateur']) ?> (<?= $u['role'] ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Fiche enseignant</label>
        <select name="enseignant_id" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($enseignants as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom'] . ' ' . $e['prenom'] . ' (' . $e['email'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="button">Lier</button>
</form>
<?php if (count($utilisateurs) == 0): ?>
    <p>Aucun utilisateur enseignant sans liaison.</p>
<?php endif; ?>
<?php include '../inc/footer.php'; ?>
