<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['enseignant']);
$page_title = "Volumes horaires";
include '../inc/enseignant_header.php';

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT enseignant_id FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$enseignant_id = $stmt->fetchColumn();
if (!$enseignant_id) die("Compte non lié à un enseignant.");

$seuil = $pdo->query("SELECT valeur FROM parametres_systeme WHERE cle = 'seuil_heures_complementaires'")->fetchColumn();
$seuil = $seuil ?: 192;

// Récupérer les activités validées groupées par année
$stmt = $pdo->prepare("SELECT aa.id, aa.libelle, SUM(ae.heures_calculees) as total
                       FROM activites_enseignants ae
                       JOIN sequences s ON ae.sequence_id = s.id
                       JOIN cours c ON s.cours_id = c.id
                       LEFT JOIN annees_academiques aa ON c.annee_academique_id = aa.id
                       WHERE ae.enseignant_id = ? AND ae.valide = 1
                       GROUP BY aa.id, aa.libelle
                       ORDER BY aa.date_debut DESC");
$stmt->execute([$enseignant_id]);
$volumes = $stmt->fetchAll();

// Récupérer le taux horaire de l'enseignant
$stmt_taux = $pdo->prepare("SELECT taux_horaire FROM enseignants WHERE id = ?");
$stmt_taux->execute([$enseignant_id]);
$taux = $stmt_taux->fetchColumn();

// Vérifier s'il y a des activités (même non groupées) pour un message plus précis
$stmt_check = $pdo->prepare("SELECT COUNT(*) FROM activites_enseignants WHERE enseignant_id = ? AND valide = 1");
$stmt_check->execute([$enseignant_id]);
$has_activities = $stmt_check->fetchColumn() > 0;
?>
<div class="admin-header-bar">
    <h1>Volumes horaires par année</h1>
</div>

<?php if (!$has_activities): ?>
    <div class="alert info">Aucune activité validée pour le moment. Les heures apparaîtront après validation des activités saisies par le secrétaire.</div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr><th>Année académique</th><th>Heures validées</th><th>Heures complémentaires</th><th>Rémunération estimée (FCFA)</th></tr>
        </thead>
        <tbody>
        <?php if (count($volumes) === 0): ?>
            <tr><td colspan="4">Aucune année associée aux activités (les cours n'ont pas d'année académique).</td></tr>
        <?php else: ?>
            <?php foreach ($volumes as $v):
                $total = (float)$v['total'];
                $complementaires = max(0, $total - $seuil);
                $remuneration = $total * $taux;
            ?>
            <tr>
                <td><?= htmlspecialchars($v['libelle'] ?: 'Non définie') ?></td>
                <td><?= number_format($total, 2) ?> h</td>
                <td<?= $complementaires > 0 ? ' style="color: #e67e22; font-weight: bold;"' : '' ?>><?= number_format($complementaires, 2) ?> h</td>
                <td><?= number_format($remuneration, 0) ?> FCFA</td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<div style="margin-top: 2rem;">
    <a href="dashboard.php" class="button button-outline">← Retour au tableau de bord</a>
</div>
<?php include '../inc/enseignant_footer.php'; ?>
