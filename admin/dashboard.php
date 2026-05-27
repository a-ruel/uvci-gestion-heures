<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Tableau de bord";
include '../inc/admin_header.php';

// Statistiques
$nbUsers = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE actif = 1")->fetchColumn();
$nbEnseignants = $pdo->query("SELECT COUNT(*) FROM enseignants WHERE actif = 1")->fetchColumn();
$activeYear = $pdo->query("SELECT libelle FROM annees_academiques WHERE est_active = 1 LIMIT 1")->fetchColumn() ?: 'Aucune';
$nbCours = $pdo->query("SELECT COUNT(*) FROM cours WHERE actif = 1")->fetchColumn();
$nbActivitesValidees = $pdo->query("SELECT COUNT(*) FROM activites_enseignants WHERE valide = 1")->fetchColumn();
$nbActivitesAttente = $pdo->query("SELECT COUNT(*) FROM activites_enseignants WHERE valide = 0")->fetchColumn();

$topEnseignants = $pdo->query("SELECT CONCAT(e.nom, ' ', e.prenom) as nom, SUM(ae.heures_calculees) as total
                               FROM enseignants e
                               LEFT JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                               GROUP BY e.id ORDER BY total DESC LIMIT 10")->fetchAll();

$repartition = $pdo->query("SELECT t.nom, COUNT(*) as nb
                            FROM activites_enseignants ae
                            JOIN types_activite t ON ae.type_activite_id = t.id
                            WHERE ae.valide = 1
                            GROUP BY t.id")->fetchAll();

$row = $pdo->query("SELECT COUNT(*) as total, SUM(valide) as validees FROM activites_enseignants")->fetch();
$totalAct = $row['total'];
$validees = $row['validees'];
$tauxValidation = ($totalAct > 0) ? round(($validees / $totalAct) * 100, 1) : 0;

$seuil = $pdo->query("SELECT valeur FROM parametres_systeme WHERE cle = 'seuil_heures_complementaires'")->fetchColumn() ?: 192;
$surcharges = $pdo->prepare("SELECT CONCAT(e.nom, ' ', e.prenom) as nom, SUM(ae.heures_calculees) as total
                             FROM enseignants e
                             JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                             GROUP BY e.id
                             HAVING total > ?");
$surcharges->execute([$seuil]);
$enseignantsSurcharges = $surcharges->fetchAll();

$activitesAttente = $pdo->query("SELECT CONCAT(e.nom, ' ', e.prenom) as enseignant, t.nom as type, a.heures_calculees
                                 FROM activites_enseignants a
                                 JOIN enseignants e ON a.enseignant_id = e.id
                                 JOIN types_activite t ON a.type_activite_id = t.id
                                 WHERE a.valide = 0
                                 ORDER BY a.date_realisation DESC LIMIT 5")->fetchAll();
?>
<div class="stats-grid">
    <div class="stat-card"><h3>Utilisateurs actifs</h3><div class="stat-number"><?= $nbUsers ?></div></div>
    <div class="stat-card"><h3>Enseignants actifs</h3><div class="stat-number"><?= $nbEnseignants ?></div></div>
    <div class="stat-card"><h3>Année académique</h3><div class="stat-number"><?= htmlspecialchars($activeYear) ?></div></div>
    <div class="stat-card"><h3>Cours actifs</h3><div class="stat-number"><?= $nbCours ?></div></div>
    <div class="stat-card"><h3>Activités validées</h3><div class="stat-number"><?= $nbActivitesValidees ?></div></div>
    <div class="stat-card"><h3>Activités en attente</h3><div class="stat-number"><?= $nbActivitesAttente ?></div></div>
</div>

<div class="two-columns">
    <div class="chart-card"><h3>Top 10 enseignants (heures)</h3><canvas id="topTeachersChart"></canvas></div>
    <div class="chart-card"><h3>Répartition des activités</h3><canvas id="activitesChart"></canvas></div>
</div>
<div class="two-columns">
    <div class="chart-card"><h3>Taux de validation</h3><canvas id="validationChart"></canvas></div>
    <div class="chart-card"><h3>Enseignants surchargés (heures > <?= $seuil ?>)</h3><?php if ($enseignantsSurcharges): ?><ul><?php foreach ($enseignantsSurcharges as $es): ?><li><?= htmlspecialchars($es['nom']) ?> : <?= number_format($es['total'],2) ?> h</li><?php endforeach; ?></ul><?php else: ?><p>Aucun enseignant surchargé.</p><?php endif; ?></div>
</div>
<div class="chart-card"><h3>Activités en attente</h3><table class="admin-table"><thead><tr><th>Enseignant</th><th>Type</th><th>Heures</th></tr></thead><tbody><?php foreach ($activitesAttente as $act): ?><tr><td><?= htmlspecialchars($act['enseignant']) ?></td><td><?= htmlspecialchars($act['type']) ?></td><td><?= number_format($act['heures_calculees'],2) ?> h</td></tr><?php endforeach; ?></tbody></table></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('topTeachersChart'), { type: 'bar', data: { labels: <?= json_encode(array_column($topEnseignants, 'nom')) ?>, datasets: [{ label: 'Heures', data: <?= json_encode(array_column($topEnseignants, 'total')) ?>, backgroundColor: '#6E026F' }] } });
new Chart(document.getElementById('activitesChart'), { type: 'pie', data: { labels: <?= json_encode(array_column($repartition, 'nom')) ?>, datasets: [{ data: <?= json_encode(array_column($repartition, 'nb')) ?>, backgroundColor: ['#6E026F','#9b59b6','#e67e22'] }] } });
new Chart(document.getElementById('validationChart'), { type: 'doughnut', data: { labels: ['Validées','En attente'], datasets: [{ data: [<?= $validees ?>, <?= $totalAct - $validees ?>], backgroundColor: ['#2ecc71','#e74c3c'] }] } });
</script>
<?php include '../inc/admin_footer.php'; ?>
