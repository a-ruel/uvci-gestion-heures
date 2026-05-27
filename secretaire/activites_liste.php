<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);

// Filtres
$enseignant_id = $_GET['enseignant_id'] ?? '';
$type_id = $_GET['type_id'] ?? '';
$valide = $_GET['valide'] ?? '';

$sql = "SELECT a.*, 
               CONCAT(e.nom, ' ', e.prenom) as enseignant,
               t.nom as type_nom,
               c.intitule as cours_intitule,
               s.titre as seq_titre
        FROM activites_enseignants a
        JOIN enseignants e ON a.enseignant_id = e.id
        JOIN types_activite t ON a.type_activite_id = t.id
        JOIN sequences s ON a.sequence_id = s.id
        JOIN cours c ON s.cours_id = c.id
        WHERE 1=1";
$params = [];

if (!empty($enseignant_id)) {
    $sql .= " AND a.enseignant_id = :enseignant_id";
    $params['enseignant_id'] = $enseignant_id;
}
if (!empty($type_id)) {
    $sql .= " AND a.type_activite_id = :type_id";
    $params['type_id'] = $type_id;
}
if ($valide !== '') {
    $sql .= " AND a.valide = :valide";
    $params['valide'] = $valide;
}
$sql .= " ORDER BY a.date_realisation DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activites = $stmt->fetchAll();

$enseignants = getEnseignants($pdo);
$types = $pdo->query("SELECT id, nom FROM types_activite")->fetchAll();

// Suppression (si besoin)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM activites_enseignants WHERE id = ?")->execute([$id]);
    header('Location: ../admin/activites_liste_scolarite.php?msg=supprime');
    exit;
}
?>
<div class="admin-header-bar">
    <h1>Gestion des activités</h1>
    <a href="../admin/activites_scolarite.php" class="button button-success">+ Nouvelle activité</a>
</div>
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'supprime'): ?>
    <div class="alert success">Activité supprimée.</div>
<?php endif; ?>

<!-- Filtres -->
<form method="get" style="background: white; padding: 1rem; border-radius: 20px; margin-bottom: 1.5rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;">
        <div>
            <label style="display: block; font-size: 0.7rem;">Enseignant</label>
            <select name="enseignant_id" style="padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
                <option value="">Tous</option>
                <?php foreach ($enseignants as $e): ?>
                    <option value="<?= $e['id'] ?>" <?= $enseignant_id == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display: block; font-size: 0.7rem;">Type</label>
            <select name="type_id" style="padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
                <option value="">Tous</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $type_id == $t['id'] ? 'selected' : '' ?>><?= ucfirst($t['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display: block; font-size: 0.7rem;">Statut</label>
            <select name="valide" style="padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
                <option value="">Tous</option>
                <option value="1" <?= $valide === '1' ? 'selected' : '' ?>>Validée</option>
                <option value="0" <?= $valide === '0' ? 'selected' : '' ?>>En attente</option>
            </select>
        </div>
        <div>
            <button type="submit" class="button">Filtrer</button>
            <a href="../admin/activites_liste_scolarite.php" class="button button-outline">Réinitialiser</a>
        </div>
    </div>
</form>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Enseignant</th>
            <th>Cours</th>
            <th>Séquence</th>
            <th>Type</th>
            <th>Niveau</th>
            <th>Date</th>
            <th>Heures</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($activites) === 0): ?>
        <tr><td colspan="10">Aucune activité trouvée.<?php else: ?>
        <?php foreach ($activites as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['enseignant']) ?></td>
            <td><?= htmlspecialchars($a['cours_intitule']) ?></td>
            <td><?= htmlspecialchars($a['seq_titre']) ?></td>
            <td><?= ucfirst($a['type_nom']) ?></td>
            <td><?= $a['niveau_complexite'] ?></td>
            <td><?= $a['date_realisation'] ?></td>
            <td><?= number_format($a['heures_calculees'], 2) ?> h</td>
            <td><?= $a['valide'] ? '✅ Validée' : '⏳ En attente' ?></td>
            <td style="display: flex; gap: 8px;">
                <?php if (!$a['valide']): ?>
                    <a href="../admin/activites_validation_scolarite.php?valider=<?= $a['id'] ?>" class="button button-small button-success">Valider</a>
                <?php endif; ?>
                <a href="?delete=<?= $a['id'] ?>" class="button button-small button-danger" onclick="return confirm('Supprimer cette activité ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
<?php
function getEnseignants($pdo) {
    $stmt = $pdo->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom");
    return $stmt->fetchAll();
}
?>
