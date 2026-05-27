<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['secretaire', 'admin']);

// Si la variable $no_layout est définie et true, on n'inclut pas l'en-tête/pied de page
if (!isset($no_layout) || !$no_layout) {
    include '../inc/secretaire_header.php';
}

$nbEnseignants = $pdo->query("SELECT COUNT(*) FROM enseignants")->fetchColumn();
$nbCours = $pdo->query("SELECT COUNT(*) FROM cours")->fetchColumn();
$nbActivitesAttente = $pdo->query("SELECT COUNT(*) FROM activites_enseignants WHERE valide = 0")->fetchColumn();
$totalHeuresValidees = $pdo->query("SELECT SUM(heures_calculees) FROM activites_enseignants WHERE valide = 1")->fetchColumn() ?: 0;
$seuil = $pdo->query("SELECT valeur FROM parametres_systeme WHERE cle = 'seuil_heures_complementaires'")->fetchColumn() ?: 192;
$heuresComplementaires = max(0, $totalHeuresValidees - $seuil);
$heuresMois = $pdo->query("SELECT SUM(heures_calculees) FROM activites_enseignants WHERE valide = 1 AND MONTH(date_realisation) = MONTH(CURDATE()) AND YEAR(date_realisation) = YEAR(CURDATE())")->fetchColumn() ?: 0;

$topEnseignants = $pdo->query("SELECT CONCAT(e.nom, ' ', e.prenom) as nom, SUM(ae.heures_calculees) as total
                               FROM enseignants e
                               LEFT JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                               GROUP BY e.id ORDER BY total DESC LIMIT 10")->fetchAll();
$repartitionDept = $pdo->query("SELECT d.nom, SUM(ae.heures_calculees) as total
                                FROM departements d
                                LEFT JOIN enseignants e ON e.departement_id = d.id
                                LEFT JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                                GROUP BY d.id ORDER BY total DESC")->fetchAll();
$activitesRecentes = $pdo->query("SELECT a.*, e.nom, e.prenom, s.titre as seq_titre 
                                  FROM activites_enseignants a
                                  JOIN enseignants e ON a.enseignant_id = e.id
                                  JOIN sequences s ON a.sequence_id = s.id
                                  WHERE a.valide = 0
                                  ORDER BY a.date_realisation DESC LIMIT 5")->fetchAll();
?>
<div class="stats-grid">
    <div class="stat-card"><h3>Enseignants</h3><div class="stat-number"><?= $nbEnseignants ?></div></div>
    <div class="stat-card"><h3>Cours</h3><div class="stat-number"><?= $nbCours ?></div></div>
    <div class="stat-card"><h3>Activités à valider</h3><div class="stat-number"><?= $nbActivitesAttente ?></div></div>
    <div class="stat-card"><h3>Heures complémentaires</h3><div class="stat-number"><?= number_format($heuresComplementaires, 2) ?> h</div></div>
    <div class="stat-card"><h3>Heures ce mois</h3><div class="stat-number"><?= number_format($heuresMois, 2) ?> h</div></div>
</div>
<div class="two-columns">
    <div class="chart-card"><h3>Top 10 enseignants (heures)</h3><canvas id="topTeachersChart"></canvas></div>
    <div class="chart-card"><h3>Répartition des heures par département</h3><canvas id="deptChart"></canvas></div>
</div>
<h2>Activités récentes en attente</h2>
<?php if ($activitesRecentes): ?>
<table class="admin-table"><thead><tr><th>Enseignant</th><th>Séquence</th><th>Date</th><th>Heures</th><th>Action</th></tr></thead>
<tbody><?php foreach ($activitesRecentes as $act): ?><tr><td><?= htmlspecialchars($act['nom'].' '.$act['prenom']) ?></td><td><?= htmlspecialchars($act['seq_titre']) ?></td><td><?= $act['date_realisation'] ?></td><td><?= number_format($act['heures_calculees'],2) ?> h</td><td><a href="activites_validation.php" class="button button-small">Valider</a></td></tr><?php endforeach; ?></tbody></table>
<?php else: ?><p>Aucune activité en attente.</p><?php endif; ?>
<div style="margin-top:2rem; display:flex; gap:1rem;"><a href="activites_saisie.php" class="button">+ Saisir une activité</a><a href="activites_validation.php" class="button">Gérer les validations</a></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('topTeachersChart'), { type: 'bar', data: { labels: <?= json_encode(array_column($topEnseignants, 'nom')) ?>, datasets: [{ label: 'Heures', data: <?= json_encode(array_column($topEnseignants, 'total')) ?>, backgroundColor: '#6E026F' }] } });
new Chart(document.getElementById('deptChart'), { type: 'pie', data: { labels: <?= json_encode(array_column($repartitionDept, 'nom')) ?>, datasets: [{ data: <?= json_encode(array_column($repartitionDept, 'total')) ?>, backgroundColor: ['#6E026F','#9b59b6','#3498db','#e67e22','#2ecc71'] }] } });
</script>
<?php
if (!isset($no_layout) || !$no_layout) {
    include '../inc/secretaire_footer.php';
}
?>
