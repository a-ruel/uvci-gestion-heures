<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['enseignant']);

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT enseignant_id FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$enseignant_id = $stmt->fetchColumn();
if (!$enseignant_id) die("Votre compte n'est pas lié à un enseignant. Contactez l'administrateur.");

// Récupérer les infos de l'enseignant
$stmt = $pdo->prepare("SELECT nom, prenom FROM enseignants WHERE id = ?");
$stmt->execute([$enseignant_id]);
$enseignant = $stmt->fetch();
$nom_complet = $enseignant['prenom'] . ' ' . $enseignant['nom'];
$page_title = "Tableau de bord";

// Heures du mois
$stmt = $pdo->prepare("SELECT SUM(heures_calculees) FROM activites_enseignants 
                       WHERE enseignant_id = ? AND valide = 1 
                       AND MONTH(date_realisation) = MONTH(CURDATE()) 
                       AND YEAR(date_realisation) = YEAR(CURDATE())");
$stmt->execute([$enseignant_id]);
$heuresMois = $stmt->fetchColumn() ?: 0;

// Total heures validées
$stmt = $pdo->prepare("SELECT SUM(heures_calculees) FROM activites_enseignants WHERE enseignant_id = ? AND valide = 1");
$stmt->execute([$enseignant_id]);
$totalHeures = $stmt->fetchColumn() ?: 0;

// Heures complémentaires
$seuil = $pdo->query("SELECT valeur FROM parametres_systeme WHERE cle = 'seuil_heures_complementaires'")->fetchColumn();
$seuil = $seuil ?: 192;
$heuresComplementaires = max(0, $totalHeures - $seuil);

// Dernières activités validées (5 dernières)
$stmt = $pdo->prepare("SELECT a.*, s.titre as seq_titre, c.intitule as cours_intitule, t.nom as type_nom
                       FROM activites_enseignants a
                       JOIN sequences s ON a.sequence_id = s.id
                       JOIN cours c ON s.cours_id = c.id
                       JOIN types_activite t ON a.type_activite_id = t.id
                       WHERE a.enseignant_id = ? AND a.valide = 1
                       ORDER BY a.date_realisation DESC LIMIT 5");
$stmt->execute([$enseignant_id]);
$activites = $stmt->fetchAll();

// Répartition par type
$stmt = $pdo->prepare("SELECT t.nom, COUNT(*) as nb
                       FROM activites_enseignants a
                       JOIN types_activite t ON a.type_activite_id = t.id
                       WHERE a.enseignant_id = ? AND a.valide = 1
                       GROUP BY t.id");
$stmt->execute([$enseignant_id]);
$repartition = $stmt->fetchAll();
if (empty($repartition)) {
    $repartition = [['nom' => 'Aucune activité', 'nb' => 1]];
}
$repartLabels = json_encode(array_column($repartition, 'nom'));
$repartData = json_encode(array_column($repartition, 'nb'));

// Évolution 6 derniers mois
$stmt = $pdo->prepare("SELECT DATE_FORMAT(date_realisation, '%Y-%m') as mois, SUM(heures_calculees) as total
                       FROM activites_enseignants
                       WHERE enseignant_id = ? AND valide = 1 AND date_realisation >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                       GROUP BY mois ORDER BY mois ASC");
$stmt->execute([$enseignant_id]);
$evolution = $stmt->fetchAll();
$mois_labels = json_encode(array_column($evolution, 'mois'));
$mois_data = json_encode(array_column($evolution, 'total'));
?>
<?php include '../inc/enseignant_header.php'; ?>
<div class="stats-grid">
    <div class="stat-card"><h3>Heures ce mois</h3><div class="stat-number"><?= number_format($heuresMois, 2) ?> h</div></div>
    <div class="stat-card"><h3>Heures complémentaires</h3><div class="stat-number"><?= number_format($heuresComplementaires, 2) ?> h</div></div>
    <div class="stat-card"><h3>Total heures validées</h3><div class="stat-number"><?= number_format($totalHeures, 2) ?> h</div></div>
</div>

<div class="two-columns">
    <div class="chart-card">
        <h3>Répartition de mes activités</h3>
        <canvas id="repartitionChart" style="max-height: 250px;"></canvas>
    </div>
    <div class="chart-card">
        <h3>Évolution des heures (6 derniers mois)</h3>
        <canvas id="evolutionChart" style="max-height: 250px;"></canvas>
    </div>
</div>

<h2>Dernières activités validées</h2>
<?php if (count($activites) > 0): ?>
<table class="admin-table">
    <thead><tr><th>Cours</th><th>Séquence</th><th>Type</th><th>Niveau</th><th>Date</th><th>Heures</th></tr></thead>
    <tbody>
    <?php foreach ($activites as $a): ?>
    <tr>
        <td><?= htmlspecialchars($a['cours_intitule']) ?></td>
        <td><?= htmlspecialchars($a['seq_titre']) ?></td>
        <td><?= ucfirst($a['type_nom']) ?></td>
        <td><?= $a['niveau_complexite'] ?></td>
        <td><?= $a['date_realisation'] ?></td>
        <td><?= number_format($a['heures_calculees'], 2) ?> h</td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>Aucune activité validée pour le moment.</p>
<?php endif; ?>

<div style="margin-top: 2rem; display: flex; gap: 1rem;">
    <a href="activites.php" class="button">Voir toutes mes activités</a>
    <a href="volume.php" class="button">Détail des volumes horaires</a>
    <a href="telecharger.php" class="button">Télécharger ma fiche (PDF)</a>
</div>

<script>
if (<?= count($repartition) > 0 ?>) {
    new Chart(document.getElementById('repartitionChart'), {
        type: 'pie',
        data: { labels: <?= $repartLabels ?>, datasets: [{ data: <?= $repartData ?>, backgroundColor: ['#6E026F', '#B840FF', '#9b59b6'] }] }
    });
}
if (<?= count($evolution) > 0 ?>) {
    new Chart(document.getElementById('evolutionChart'), {
        type: 'line',
        data: { labels: <?= $mois_labels ?>, datasets: [{ label: 'Heures', data: <?= $mois_data ?>, borderColor: '#6E026F', tension: 0.2 }] }
    });
}
</script>
<?php include '../inc/enseignant_footer.php'; ?>
