<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Gestion des cours";
include '../inc/secretaire_header.php';

// Suppression d'un cours
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM cours WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: cours.php?msg=supprime');
    exit;
}

// Filtres
$filiere = $_GET['filiere'] ?? '';
$niveau_id = $_GET['niveau_id'] ?? '';
$semestre = $_GET['semestre'] ?? '';

// Requête
$sql = "SELECT c.*, n.code as niveau_code, d.nom as departement_nom, a.libelle as annee 
        FROM cours c
        JOIN niveaux_etudes n ON c.niveau_id = n.id
        LEFT JOIN departements d ON c.departement_id = d.id
        LEFT JOIN annees_academiques a ON c.annee_academique_id = a.id
        WHERE 1=1";
$params = [];
if ($filiere) {
    $sql .= " AND c.filiere LIKE :filiere";
    $params['filiere'] = "%$filiere%";
}
if ($niveau_id) {
    $sql .= " AND c.niveau_id = :niveau_id";
    $params['niveau_id'] = $niveau_id;
}
if ($semestre) {
    $sql .= " AND c.semestre = :semestre";
    $params['semestre'] = $semestre;
}
$sql .= " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cours = $stmt->fetchAll();

$niveaux = getNiveaux($pdo);
$departements = getDepartements($pdo);
$annees = getAnneesAcademiques($pdo);

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'ajoute') $message = "Cours ajouté.";
    if ($_GET['msg'] == 'modifie') $message = "Cours modifié.";
    if ($_GET['msg'] == 'supprime') $message = "Cours supprimé.";
}
?>
<div class="page-header">
    <h1>Gestion des cours</h1>
    <a href="cours_create.php" class="button button-success">+ Nouveau cours</a>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Filtres -->
<form method="get" class="filters-form" style="background: white; padding: 1rem 1.5rem; border-radius: 24px; margin-bottom: 2rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Filière</label>
            <input type="text" name="filiere" placeholder="Filière" value="<?= htmlspecialchars($filiere) ?>" style="padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1; width: 200px;">
        </div>
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Niveau</label>
            <select name="niveau_id" style="padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
                <option value="">Tous</option>
                <?php foreach ($niveaux as $n): ?>
                    <option value="<?= $n['id'] ?>" <?= $niveau_id == $n['id'] ? 'selected' : '' ?>><?= htmlspecialchars($n['code']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Semestre</label>
            <select name="semestre" style="padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
                <option value="">Tous</option>
                <?php for($i=1;$i<=6;$i++): ?>
                    <option value="<?= $i ?>" <?= $semestre == $i ? 'selected' : '' ?>>S<?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="filter-actions" style="display: flex; gap: 0.5rem;">
            <button type="submit" class="button">Filtrer</button>
            <a href="cours.php" class="button button-outline">Réinitialiser</a>
        </div>
    </div>
</form>

<!-- Tableau des cours -->
<table class="admin-table">
    <thead>
        <tr>
            <th>Intitulé</th>
            <th>Filière</th>
            <th>Niveau</th>
            <th>Semestre</th>
            <th>Heures</th>
            <th>Crédits</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($cours) === 0): ?>
        <tr><td colspan="7" style="text-align: center;">Aucun cours trouvé.<?php else: ?>
        <?php foreach ($cours as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['intitule']) ?></td>
            <td><?= htmlspecialchars($c['filiere']) ?></td>
            <td><?= htmlspecialchars($c['niveau_code']) ?></td>
            <td>S<?= $c['semestre'] ?></td>
            <td><?= $c['heures_total'] ?> h</td>
            <td><?= $c['credits'] ?> credits</td>
            <td style="display: flex; gap: 8px;">
                <a href="cours_edit.php?id=<?= $c['id'] ?>" class="button button-small button-primary">Modifier</a>
                <a href="cours.php?delete=<?= $c['id'] ?>" class="button button-small button-danger" onclick="return confirm('Supprimer ce cours ?')">Supprimer</a>
                <a href="sequences.php?cours_id=<?= $c['id'] ?>" class="button button-small">Séquences</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../inc/secretaire_footer.php'; ?>
