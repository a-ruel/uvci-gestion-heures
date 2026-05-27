<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Barèmes & Taux";
include '../inc/admin_header.php';

// Gestion des actions (ajout, modification, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $grade_id = (int)$_POST['grade_id'];
        $statut_id = (int)$_POST['statut_id'];
        $taux = (float)$_POST['taux_horaire'];
        $stmt = $pdo->prepare("INSERT INTO taux_reference (grade_id, statut_id, taux_horaire) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE taux_horaire = ?");
        $stmt->execute([$grade_id, $statut_id, $taux, $taux]);
        header('Location: taux.php?msg=ajoute');
        exit;
    } elseif (isset($_POST['modifier'])) {
        $id = (int)$_POST['id'];
        $taux = (float)$_POST['taux_horaire'];
        $stmt = $pdo->prepare("UPDATE taux_reference SET taux_horaire = ? WHERE id = ?");
        $stmt->execute([$taux, $id]);
        header('Location: taux.php?msg=modifie');
        exit;
    } elseif (isset($_POST['supprimer'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM taux_reference WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: taux.php?msg=supprime');
        exit;
    }
}

// Filtres
$search = trim($_GET['search'] ?? '');
$statut_id = $_GET['statut_id'] ?? '';

$sql = "SELECT t.id, g.nom as grade_nom, s.nom as statut_nom, t.taux_horaire
        FROM taux_reference t
        JOIN grades g ON t.grade_id = g.id
        JOIN statuts s ON t.statut_id = s.id
        WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND g.nom LIKE :search";
    $params['search'] = "%$search%";
}
if ($statut_id) {
    $sql .= " AND t.statut_id = :statut_id";
    $params['statut_id'] = $statut_id;
}
$sql .= " ORDER BY g.nom, s.nom";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$taux_list = $stmt->fetchAll();

$grades = $pdo->query("SELECT id, nom FROM grades ORDER BY id")->fetchAll();
$statuts = $pdo->query("SELECT id, nom FROM statuts ORDER BY id")->fetchAll();

$message = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'ajoute': $message = "Taux ajouté/modifié."; break;
        case 'modifie': $message = "Taux modifié."; break;
        case 'supprime': $message = "Taux supprimé."; break;
    }
}
?>
<div class="admin-header-bar">
    <div>
        <h1>Taux horaires</h1>
        <p>Grille des taux horaires par grade</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="export_taux_pdf.php" class="button">PDF</a>
        <a href="export_taux_excel.php" class="button">Excel</a>
        <button onclick="showAddModal()" class="button button-success">+ Nouveau</button>
    </div>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="get" style="margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
    <input type="text" name="search" placeholder="Rechercher par grade..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; padding: 0.6rem; border-radius: 30px; border: 1px solid #ddd;">
    <select name="statut_id" style="padding: 0.6rem; border-radius: 30px;">
        <option value="">Tous les statuts</option>
        <?php foreach ($statuts as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $statut_id == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['nom']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="button">Filtrer</button>
    <a href="taux.php" class="button button-outline">Réinitialiser</a>
</form>

<table class="admin-table">
    <thead>
        <tr><th>GRADE</th><th>STATUT</th><th>TAUX HORAIRE (FCFA)</th><th>ACTIONS</th></tr>
    </thead>
    <tbody>
        <?php if (empty($taux_list)): ?>
            <tr><td colspan="4">Aucun taux trouvé. Vous pouvez en ajouter.<?php else: ?>
            <?php foreach ($taux_list as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['grade_nom']) ?></td>
                <td><?= htmlspecialchars($t['statut_nom']) ?></td>
                <td><?= number_format($t['taux_horaire'], 0) ?> FCFA/h</td>
                <td style="display: flex; gap: 8px;">
                    <button onclick="editTaux(<?= $t['id'] ?>, <?= $t['taux_horaire'] ?>)" class="button button-small button-primary">Modifier</button>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce taux ?')">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit" name="supprimer" class="button button-small button-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modal pour ajout / modification -->
<div id="tauxModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 24px; width: 400px;">
        <h2 id="modalTitle">Ajouter un taux</h2>
        <form method="post" id="tauxForm">
            <input type="hidden" name="id" id="tauxId">
            <div class="admin-form-group">
                <label>Grade</label>
                <select name="grade_id" id="grade_id" required>
                    <?php foreach ($grades as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group">
                <label>Statut</label>
                <select name="statut_id" id="statut_id" required>
                    <?php foreach ($statuts as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group">
                <label>Taux horaire (FCFA)</label>
                <input type="number" step="100" name="taux_horaire" id="taux_horaire" required>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" name="ajouter" class="button" id="submitBtn">Ajouter</button>
                <button type="button" onclick="closeModal()" class="button button-outline">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('modalTitle').innerText = 'Ajouter un taux';
    document.getElementById('tauxId').value = '';
    document.getElementById('grade_id').value = '';
    document.getElementById('statut_id').value = '';
    document.getElementById('taux_horaire').value = '';
    document.getElementById('submitBtn').name = 'ajouter';
    document.getElementById('tauxForm').action = '';
    document.getElementById('tauxModal').style.display = 'flex';
}
function editTaux(id, taux) {
    // Récupérer les infos grade et statut depuis la ligne (on peut les passer en paramètres ou via AJAX)
    // Pour simplifier, on recharge la page avec un formulaire de modification inline, ou on utilise une requête AJAX.
    // Ici on utilise un formulaire direct avec champs non modifiables pour grade/statut ? Ou on les laisse modifiables.
    // Solution simple : on crée un formulaire de modification séparé.
    // On va plutôt utiliser une soumission directe avec l'ID et le nouveau taux.
    var newTaux = prompt("Nouveau taux horaire (FCFA/h) :", taux);
    if (newTaux !== null && !isNaN(newTaux)) {
        var form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="id" value="'+id+'"><input type="hidden" name="taux_horaire" value="'+newTaux+'"><input type="hidden" name="modifier" value="1">';
        document.body.appendChild(form);
        form.submit();
    }
}
function closeModal() {
    document.getElementById('tauxModal').style.display = 'none';
}
</script>
<?php include '../inc/admin_footer.php'; ?>
