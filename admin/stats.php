<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Statistiques";
include '../inc/admin_header.php';

// Heures par département
$stmt = $pdo->query("SELECT d.nom as departement, COALESCE(SUM(ae.heures_calculees), 0) as total
                     FROM departements d
                     LEFT JOIN enseignants e ON e.departement_id = d.id
                     LEFT JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                     GROUP BY d.id
                     ORDER BY total DESC");
$departements = $stmt->fetchAll();

// Évolution mensuelle (12 derniers mois)
$stmt = $pdo->query("SELECT DATE_FORMAT(date_realisation, '%Y-%m') as mois, SUM(heures_calculees) as total
                     FROM activites_enseignants
                     WHERE valide = 1
                     GROUP BY mois
                     ORDER BY mois DESC
                     LIMIT 12");
$evolution = $stmt->fetchAll();
$evolution = array_reverse($evolution); // pour ordre chronologique

// Top 5 enseignants
$stmt = $pdo->query("SELECT CONCAT(e.nom, ' ', e.prenom) as nom, SUM(ae.heures_calculees) as total
                     FROM enseignants e
                     JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                     GROUP BY e.id
                     ORDER BY total DESC
                     LIMIT 5");
$topEnseignants = $stmt->fetchAll();

// Types d'activités
$stmt = $pdo->query("SELECT t.nom, COUNT(*) as nb, SUM(ae.heures_calculees) as total
                     FROM activites_enseignants ae
                     JOIN types_activite t ON ae.type_activite_id = t.id
                     WHERE ae.valide = 1
                     GROUP BY t.id");
$types = $stmt->fetchAll();
?>
<div class="admin-header-bar">
    <h1>Statistiques détaillées</h1>
    <div style="display: flex; gap: 1rem;">
        <a href="export_stats_pdf.php" class="button">PDF</a>
        <a href="export_stats_excel.php" class="button">Excel</a>
    </div>
</div>

<div class="two-columns">
    <div class="chart-card">
        <h3>Heures par département</h3>
        <canvas id="departementChart" style="max-height: 300px;"></canvas>
    </div>
    <div class="chart-card">
        <h3>Évolution mensuelle (12 mois)</h3>
        <canvas id="evolutionChart" style="max-height: 300px;"></canvas>
    </div>
</div>

<div class="two-columns">
    <div class="chart-card">
        <h3>Top 5 enseignants (heures)</h3>
        <canvas id="topChart" style="max-height: 300px;"></canvas>
    </div>
    <div class="chart-card">
        <h3>Répartition par type d'activité</h3>
        <canvas id="typeChart" style="max-height: 300px;"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Heures par département
new Chart(document.getElementById('departementChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($departements, 'departement')) ?>,
        datasets: [{ label: 'Heures', data: <?= json_encode(array_column($departements, 'total')) ?>, backgroundColor: '#831C91' }]
    }
});
// Évolution mensuelle
new Chart(document.getElementById('evolutionChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($evolution, 'mois')) ?>,
        datasets: [{ label: 'Heures', data: <?= json_encode(array_column($evolution, 'total')) ?>, borderColor: '#831C91', fill: false }]
    }
});
// Top 5 enseignants
new Chart(document.getElementById('topChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($topEnseignants, 'nom')) ?>,
        datasets: [{ label: 'Heures', data: <?= json_encode(array_column($topEnseignants, 'total')) ?>, backgroundColor: '#831C91' }]
    }
});
// Types d'activités
new Chart(document.getElementById('typeChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($types, 'nom')) ?>,
        datasets: [{ data: <?= json_encode(array_column($types, 'total')) ?>, backgroundColor: ['#831C91', '#9b59b6', '#e67e22'] }]
    }
});
</script>

<?php include '../inc/admin_footer.php'; ?>
