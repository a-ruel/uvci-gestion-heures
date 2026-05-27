<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['enseignant']);
$page_title = "Mes activités";
include '../inc/enseignant_header.php';

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT enseignant_id FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$enseignant_id = $stmt->fetchColumn();
if (!$enseignant_id) die("Compte non lié à un enseignant.");

// Filtres
$annee_id = $_GET['annee_id'] ?? '';
$cours_id = $_GET['cours_id'] ?? '';
$type_id = $_GET['type_id'] ?? '';

$sql = "SELECT a.*, s.titre as seq_titre, c.intitule as cours, t.nom as type_nom,
               c.annee_academique_id, aa.libelle as annee
        FROM activites_enseignants a
        JOIN sequences s ON a.sequence_id = s.id
        JOIN cours c ON s.cours_id = c.id
        JOIN types_activite t ON a.type_activite_id = t.id
        LEFT JOIN annees_academiques aa ON c.annee_academique_id = aa.id
        WHERE a.enseignant_id = ? AND a.valide = 1";
$params = [$enseignant_id];

if ($annee_id) {
    $sql .= " AND c.annee_academique_id = ?";
    $params[] = $annee_id;
}
if ($cours_id) {
    $sql .= " AND c.id = ?";
    $params[] = $cours_id;
}
if ($type_id) {
    $sql .= " AND a.type_activite_id = ?";
    $params[] = $type_id;
}
$sql .= " ORDER BY a.date_realisation DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activites = $stmt->fetchAll();

// Listes pour les filtres
$annees = $pdo->query("SELECT id, libelle FROM annees_academiques ORDER BY date_debut DESC")->fetchAll();
$cours_list = $pdo->prepare("SELECT DISTINCT c.id, c.intitule FROM cours c
                             JOIN sequences s ON s.cours_id = c.id
                             JOIN activites_enseignants a ON a.sequence_id = s.id
                             WHERE a.enseignant_id = ?");
$cours_list->execute([$enseignant_id]);
$cours_list = $cours_list->fetchAll();
$types = $pdo->query("SELECT id, nom FROM types_activite")->fetchAll();
?>
<div class="admin-header-bar">
    <h1>Mes activités</h1>
</div>

<!-- Filtres améliorés -->
<form method="get" style="background: white; padding: 1rem 1.5rem; border-radius: 24px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Année</label>
            <select name="annee_id" style="padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1; background: white;">
                <option value="">Toutes</option>
                <?php foreach ($annees as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $annee_id == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Cours</label>
            <select name="cours_id" style="padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1; background: white;">
                <option value="">Tous</option>
                <?php foreach ($cours_list as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $cours_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['intitule']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label style="display: block; font-size: 0.7rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Type</label>
            <select name="type_id" style="padding: 0.5rem 0.8rem; border-radius: 30px; border: 1px solid #cbd5e1; background: white;">
                <option value="">Tous</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $type_id == $t['id'] ? 'selected' : '' ?>><?= ucfirst($t['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="button">Filtrer</button>
            <a href="activites.php" class="button button-outline">Réinitialiser</a>
        </div>
    </div>
</form>

<!-- Tableau des activités -->
<table class="admin-table">
    <thead><tr><th>Cours</th><th>Séquence</th><th>Type</th><th>Niveau</th><th>Date</th><th>Heures</th><th>Année</th></tr></thead>
    <tbody>
    <?php if (count($activites) === 0): ?>
        <tr><td colspan="7">Aucune activité trouvée.<?php else: ?>
        <?php foreach ($activites as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['cours']) ?></td>
            <td><?= htmlspecialchars($a['seq_titre']) ?></td>
            <td><?= ucfirst($a['type_nom']) ?></td>
            <td><?= $a['niveau_complexite'] ?></td>
            <td><?= $a['date_realisation'] ?></td>
            <td><?= number_format($a['heures_calculees'], 2) ?> h</td>
            <td><?= htmlspecialchars($a['annee'] ?? 'N/A') ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
<?php include '../inc/enseignant_footer.php'; ?>
