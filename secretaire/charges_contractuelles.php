<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Charges contractuelles";
include '../inc/secretaire_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $enseignant_id = (int)$_POST['enseignant_id'];
        $annee_id = (int)$_POST['annee_id'];
        $heures_base = (int)$_POST['heures_base'];
        $stmt = $pdo->prepare("INSERT INTO charges_contractuelles (enseignant_id, annee_academique_id, heures_base) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE heures_base = VALUES(heures_base)");
        $stmt->execute([$enseignant_id, $annee_id, $heures_base]);
        header('Location: charges_contractuelles.php?msg=ajoute');
        exit;
    } elseif (isset($_POST['supprimer'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM charges_contractuelles WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: charges_contractuelles.php?msg=supprime');
        exit;
    }
}

$enseignants = $pdo->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom")->fetchAll();
$annees = $pdo->query("SELECT id, libelle FROM annees_academiques ORDER BY date_debut DESC")->fetchAll();
$charges = $pdo->query("SELECT c.*, e.nom, e.prenom, a.libelle as annee 
                        FROM charges_contractuelles c
                        JOIN enseignants e ON c.enseignant_id = e.id
                        JOIN annees_academiques a ON c.annee_academique_id = a.id
                        ORDER BY a.date_debut DESC, e.nom")->fetchAll();

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'ajoute') $message = "Charge contractuelle enregistrée.";
    if ($_GET['msg'] == 'supprime') $message = "Charge contractuelle supprimée.";
}
?>
<div class="page-header">
    <h1>Charges contractuelles</h1>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<div class="card">
    <h2>Ajouter / modifier une charge</h2>
    <form method="post" class="admin-form" style="max-width: 500px;">
        <div class="admin-form-group">
            <label>Enseignant</label>
            <select name="enseignant_id" required>
                <?php foreach ($enseignants as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="admin-form-group">
            <label>Année académique</label>
            <select name="annee_id" required>
                <?php foreach ($annees as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="admin-form-group">
            <label>Heures de base</label>
            <input type="number" name="heures_base" required>
        </div>
        <button type="submit" name="ajouter" class="button">Enregistrer</button>
    </form>
</div>
<h2>Charges existantes</h2>
<table class="admin-table">
    <thead>
        <tr>
            <th>Enseignant</th>
            <th>Année</th>
            <th>Heures de base</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($charges) === 0): ?>
        <tr><td colspan="4">Aucune charge contractuelle définie.<?php else: ?>
        <?php foreach ($charges as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></td>
            <td><?= htmlspecialchars($c['annee']) ?></td>
            <td><?= $c['heures_base'] ?> h</td>
            <td style="display: flex; gap: 8px;">
                <form method="post" onsubmit="return confirm('Supprimer cette charge ?')">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button type="submit" name="supprimer" class="button button-small button-danger">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
<?php include '../inc/secretaire_footer.php'; ?>
