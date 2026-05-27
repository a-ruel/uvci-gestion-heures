<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Validation des activités";
include '../inc/secretaire_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['valider_selection']) && !empty($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("UPDATE activites_enseignants SET valide = 1, valide_le = NOW() WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        header('Location: activites_validation.php?msg=valide');
        exit;
    } elseif (isset($_POST['valider_unique'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE activites_enseignants SET valide = 1, valide_le = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: activites_validation.php?msg=valide');
        exit;
    }
}

$stmt = $pdo->query("SELECT a.id, 
                            CONCAT(e.nom, ' ', e.prenom) as enseignant,
                            c.intitule as cours,
                            s.titre as sequence,
                            t.nom as type,
                            a.niveau_complexite,
                            a.date_realisation,
                            a.heures_calculees
                     FROM activites_enseignants a
                     JOIN enseignants e ON a.enseignant_id = e.id
                     JOIN sequences s ON a.sequence_id = s.id
                     JOIN cours c ON s.cours_id = c.id
                     JOIN types_activite t ON a.type_activite_id = t.id
                     WHERE a.valide = 0
                     ORDER BY a.date_realisation DESC");
$activites = $stmt->fetchAll();

$message = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'valide') {
    $message = "Activités validées avec succès.";
}
?>
<div class="page-header">
    <h1>Validation des activités</h1>
    <a href="activites.php" class="button button-outline">← Retour à la liste</a>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<form method="post">
    <table class="admin-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>Enseignant</th>
                <th>Cours</th>
                <th>Séquence</th>
                <th>Type</th>
                <th>Niveau</th>
                <th>Date</th>
                <th>Heures</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($activites) === 0): ?>
                <td><td colspan="9">Aucune activité en attente de validation.<?php else: ?>
                <?php foreach ($activites as $a): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $a['id'] ?>"></td>
                    <td><?= htmlspecialchars($a['enseignant']) ?></td>
                    <td><?= htmlspecialchars($a['cours']) ?></td>
                    <td><?= htmlspecialchars($a['sequence']) ?></td>
                    <td><?= ucfirst($a['type']) ?></td>
                    <td><?= $a['niveau_complexite'] ?></td>
                    <td><?= $a['date_realisation'] ?></td>
                    <td><?= number_format($a['heures_calculees'], 2) ?> h</td>
                    <td style="white-space: nowrap;">
                        <form method="post" style="display:inline">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" name="valider_unique" class="button button-small button-success">Valider</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if (count($activites) > 0): ?>
        <div style="margin-top: 1rem;">
            <button type="submit" name="valider_selection" class="button">Valider la sélection</button>
        </div>
    <?php endif; ?>
</form>
<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
<?php include '../inc/secretaire_footer.php'; ?>
