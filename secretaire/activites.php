<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Liste des activités";
include '../inc/secretaire_header.php';

$search = $_GET['search'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$enseignant_id = $_GET['enseignant_id'] ?? '';
$type_id = $_GET['type_id'] ?? '';
$valide = $_GET['valide'] ?? '';

$sql = "SELECT a.id, a.date_realisation, a.niveau_complexite, a.heures_calculees, a.valide,
               CONCAT(e.nom, ' ', e.prenom) as enseignant,
               t.nom as type_nom,
               c.intitule as cours,
               s.titre as sequence
        FROM activites_enseignants a
        JOIN enseignants e ON a.enseignant_id = e.id
        JOIN types_activite t ON a.type_activite_id = t.id
        JOIN sequences s ON a.sequence_id = s.id
        JOIN cours c ON s.cours_id = c.id
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (c.intitule LIKE :search OR s.titre LIKE :search)";
    $params['search'] = "%$search%";
}
if ($date_debut) {
    $sql .= " AND a.date_realisation >= :date_debut";
    $params['date_debut'] = $date_debut;
}
if ($date_fin) {
    $sql .= " AND a.date_realisation <= :date_fin";
    $params['date_fin'] = $date_fin;
}
if ($enseignant_id) {
    $sql .= " AND a.enseignant_id = :enseignant_id";
    $params['enseignant_id'] = $enseignant_id;
}
if ($type_id) {
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

$enseignants = $pdo->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom")->fetchAll();
$types = $pdo->query("SELECT id, nom FROM types_activite")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['valider_unique'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE activites_enseignants SET valide = 1, valide_le = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: activites.php?msg=valide');
        exit;
    } elseif (isset($_POST['supprimer'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM activites_enseignants WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: activites.php?msg=supprime');
        exit;
    }
}

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'valide') $message = "Activité validée.";
    if ($_GET['msg'] == 'supprime') $message = "Activité supprimée.";
}
?>
<div class="page-header">
    <h1>Liste des activités pédagogiques</h1>
    <a href="activites_saisie.php" class="button button-success">+ Nouvelle activité</a>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Filtres modernes alignés en grille -->
<form method="get" class="filters-form" style="background: white; padding: 1rem 1.5rem; border-radius: 24px; margin-bottom: 2rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem 1.5rem; align-items: end;">
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Recherche</label>
            <input type="text" name="search" placeholder="Mots-clés" value="<?= htmlspecialchars($search) ?>" style="width: 100%; padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
        </div>
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Date début</label>
            <input type="date" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>" style="width: 100%; padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
        </div>
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Date fin</label>
            <input type="date" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>" style="width: 100%; padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
        </div>
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Enseignant</label>
            <select name="enseignant_id" style="width: 100%; padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
                <option value="">Tous</option>
                <?php foreach ($enseignants as $e): ?>
                    <option value="<?= $e['id'] ?>" <?= $enseignant_id == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Type</label>
            <select name="type_id" style="width: 100%; padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
                <option value="">Tous</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $type_id == $t['id'] ? 'selected' : '' ?>><?= ucfirst($t['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Statut</label>
            <select name="valide" style="width: 100%; padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1;">
                <option value="">Tous</option>
                <option value="1" <?= $valide == '1' ? 'selected' : '' ?>>Validée</option>
                <option value="0" <?= $valide == '0' ? 'selected' : '' ?>>En attente</option>
            </select>
        </div>
        <div class="filter-actions" style="grid-column: span 1; display: flex; gap: 0.5rem; align-items: center;">
            <button type="submit" class="button">Filtrer</button>
            <a href="activites.php" class="button button-outline">Réinitialiser</a>
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
            <td><?= htmlspecialchars($a['cours']) ?></td>
            <td><?= htmlspecialchars($a['sequence']) ?></td>
            <td><?= ucfirst($a['type_nom']) ?></td>
            <td><?= $a['niveau_complexite'] ?></td>
            <td><?= $a['date_realisation'] ?></td>
            <td><?= number_format($a['heures_calculees'], 2) ?> h</td>
            <td><?= $a['valide'] ? '✅ Validée' : '⏳ En attente' ?></td>
            <td style="white-space: nowrap;">
                <?php if (!$a['valide']): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button type="submit" name="valider_unique" class="button button-small button-success">Valider</button>
                </form>
                <?php endif; ?>
                <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette activité ?')">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button type="submit" name="supprimer" class="button button-small button-danger">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php include '../inc/secretaire_footer.php'; ?>
