<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM enseignants WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: enseignants.php?msg=supprime');
    exit;
}

$search = trim($_GET['search'] ?? '');
$departement_id = $_GET['departement_id'] ?? '';
$grade_id = $_GET['grade_id'] ?? '';
$statut_id = $_GET['statut_id'] ?? '';

$sql = "SELECT e.*, g.nom as grade_nom, s.nom as statut_nom, d.nom as departement_nom
        FROM enseignants e
        LEFT JOIN grades g ON e.grade_id = g.id
        LEFT JOIN statuts s ON e.statut_id = s.id
        LEFT JOIN departements d ON e.departement_id = d.id
        WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (e.nom LIKE :search OR e.prenom LIKE :search OR e.email LIKE :search)";
    $params['search'] = "%$search%";
}
if ($departement_id) {
    $sql .= " AND e.departement_id = :departement_id";
    $params['departement_id'] = $departement_id;
}
if ($grade_id) {
    $sql .= " AND e.grade_id = :grade_id";
    $params['grade_id'] = $grade_id;
}
if ($statut_id) {
    $sql .= " AND e.statut_id = :statut_id";
    $params['statut_id'] = $statut_id;
}
$sql .= " ORDER BY e.nom, e.prenom";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$enseignants = $stmt->fetchAll();

$departements = getDepartements($pdo);
$grades = getGrades($pdo);
$statuts = getStatuts($pdo);
$seuil = $pdo->query("SELECT valeur FROM parametres_systeme WHERE cle = 'seuil_heures_complementaires'")->fetchColumn() ?: 192;

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'ajoute') $message = "Enseignant ajouté.";
    if ($_GET['msg'] == 'modifie') $message = "Enseignant modifié.";
    if ($_GET['msg'] == 'supprime') $message = "Enseignant supprimé.";
}

// Inclusion conditionnelle du layout du secrétaire (seulement si $no_layout n'est pas true)
if (!isset($no_layout) || !$no_layout) {
    include '../inc/secretaire_header.php';
}
?>
<div class="page-header">
    <h1>Gestion des enseignants</h1>
    <a href="enseignants_create.php" class="button button-success">+ Nouvel enseignant</a>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="get" style="background: white; padding: 1rem 1.5rem; border-radius: 24px; margin-bottom: 2rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
        <div><label>Rechercher</label><input type="text" name="search" placeholder="Nom, prénom, email..." value="<?= htmlspecialchars($search) ?>" style="padding:0.5rem; border-radius:30px;"></div>
        <div><label>Département</label><select name="departement_id" style="padding:0.5rem; border-radius:30px;"><option value="">Tous</option><?php foreach ($departements as $d): ?><option value="<?= $d['id'] ?>" <?= $departement_id==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['nom']) ?></option><?php endforeach; ?></select></div>
        <div><label>Grade</label><select name="grade_id" style="padding:0.5rem; border-radius:30px;"><option value="">Tous</option><?php foreach ($grades as $g): ?><option value="<?= $g['id'] ?>" <?= $grade_id==$g['id']?'selected':'' ?>><?= htmlspecialchars($g['nom']) ?></option><?php endforeach; ?></select></div>
        <div><label>Statut</label><select name="statut_id" style="padding:0.5rem; border-radius:30px;"><option value="">Tous</option><?php foreach ($statuts as $s): ?><option value="<?= $s['id'] ?>" <?= $statut_id==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['nom']) ?></option><?php endforeach; ?></select></div>
        <div><button type="submit" class="button">Filtrer</button> <a href="enseignants.php" class="button button-outline">Réinitialiser</a></div>
    </div>
</form>

<table class="admin-table">
    <thead>
        <tr>
            <th>CODE</th>
            <th>NOM</th>
            <th>PRÉNOM</th>
            <th>GRADE</th>
            <th>STATUT</th>
            <th>DÉPARTEMENT</th>
            <th>HEURES</th>
            <th>COMPL.</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($enseignants) === 0): ?>
        <tr><td colspan="9">Aucun enseignant trouvé.<?php else: ?>
        <?php foreach ($enseignants as $e):
            $stmt_h = $pdo->prepare("SELECT SUM(heures_calculees) FROM activites_enseignants WHERE enseignant_id = ? AND valide = 1");
            $stmt_h->execute([$e['id']]);
            $total_heures = (float)$stmt_h->fetchColumn();
            $compl = max(0, $total_heures - $seuil);
        ?>
        <tr>
            <td><?= "ENS" . str_pad($e['id'], 4, "0", STR_PAD_LEFT) ?></td>
            <td><?= htmlspecialchars($e['nom']) ?></td>
            <td><?= htmlspecialchars($e['prenom']) ?></td>
            <td><?= htmlspecialchars($e['grade_nom']) ?></td>
            <td><?= htmlspecialchars($e['statut_nom']) ?></td>
            <td><?= htmlspecialchars($e['departement_nom']) ?></td>
            <td><?= number_format($total_heures, 2) ?> h</td>
            <td><?= number_format($compl, 2) ?> h</td>
            <td style="display: flex; gap: 8px;">
                <a href="enseignants_edit.php?id=<?= $e['id'] ?>" class="button button-small button-primary">Modifier</a>
                <a href="enseignants.php?delete=<?= $e['id'] ?>" class="button button-small button-danger" onclick="return confirm('Supprimer définitivement cet enseignant ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php
if (!isset($no_layout) || !$no_layout) {
    include '../inc/secretaire_footer.php';
}
?>
