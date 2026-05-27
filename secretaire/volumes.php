<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Volumes horaires";
include '../inc/secretaire_header.php';

$annee_id = isset($_GET['annee_id']) ? (int)$_GET['annee_id'] : 0;
$departement_id = isset($_GET['departement_id']) ? (int)$_GET['departement_id'] : 0;

$sql = "SELECT e.id, e.nom, e.prenom, e.taux_horaire, d.nom as departement_nom,
               COALESCE(SUM(ae.heures_calculees), 0) as total_heures
        FROM enseignants e
        LEFT JOIN departements d ON e.departement_id = d.id
        LEFT JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
        LEFT JOIN sequences s ON ae.sequence_id = s.id
        LEFT JOIN cours c ON s.cours_id = c.id
        WHERE 1=1";
$params = [];
if ($annee_id > 0) {
    $sql .= " AND c.annee_academique_id = :annee_id";
    $params['annee_id'] = $annee_id;
}
if ($departement_id > 0) {
    $sql .= " AND e.departement_id = :departement_id";
    $params['departement_id'] = $departement_id;
}
$sql .= " GROUP BY e.id ORDER BY e.nom, e.prenom";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$enseignants = $stmt->fetchAll();

$annees = getAnneesAcademiques($pdo);
$departements = getDepartements($pdo);
$seuil = $pdo->query("SELECT valeur FROM parametres_systeme WHERE cle = 'seuil_heures_complementaires'")->fetchColumn() ?: 192;
?>
<div class="page-header">
    <h1>Volumes horaires par enseignant</h1>
</div>
<form method="get" class="filters-form" style="background: white; padding: 1rem 1.5rem; border-radius: 24px; margin-bottom: 2rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
        <div class="filter-group">
            <label>Année académique</label>
            <select name="annee_id" style="padding: 0.5rem 0.8rem; border-radius: 30px;">
                <option value="0">Toutes</option>
                <?php foreach ($annees as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $annee_id == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Département</label>
            <select name="departement_id" style="padding: 0.5rem 0.8rem; border-radius: 30px;">
                <option value="0">Tous</option>
                <?php foreach ($departements as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $departement_id == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-actions" style="display: flex; gap: 0.5rem;">
            <button type="submit" class="button">Filtrer</button>
            <a href="volumes.php" class="button button-outline">Réinitialiser</a>
        </div>
    </div>
</form>
<table class="admin-table">
    <thead>
        <tr>
            <th>Enseignant</th>
            <th>Département</th>
            <th>Taux horaire (FCFA/h)</th>
            <th>Heures validées</th>
            <th>Heures complémentaires</th>
            <th>Rémunération (FCFA)</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($enseignants) === 0): ?>
        <tr><td colspan="6">Aucune donnée trouvée.<?php else: ?>
        <?php foreach ($enseignants as $e):
            $heures = (float)$e['total_heures'];
            $complementaires = max(0, $heures - $seuil);
            $remuneration = $heures * $e['taux_horaire'];
        ?>
        <tr>
            <td><?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?></td>
            <td><?= htmlspecialchars($e['departement_nom']) ?></td>
            <td><?= number_format($e['taux_horaire'], 0) ?> FCFA/h</td>
            <td><?= number_format($heures, 2) ?> h</td>
            <td<?= $complementaires > 0 ? ' style="color: #e67e22; font-weight: bold;"' : '' ?>><?= number_format($complementaires, 2) ?> h</td>
            <td><?= number_format($remuneration, 0) ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
<?php include '../inc/secretaire_footer.php'; ?>
