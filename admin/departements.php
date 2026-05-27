<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Départements";
include '../inc/admin_header.php';

// CRUD départements
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $nom = trim($_POST['nom']);
        $desc = $_POST['description'];
        $pdo->prepare("INSERT INTO departements (nom, description) VALUES (?, ?)")->execute([$nom, $desc]);
        header('Location: departements.php?msg=ajoute');
        exit;
    } elseif (isset($_POST['modifier'])) {
        $id = (int)$_POST['id'];
        $nom = trim($_POST['nom']);
        $desc = $_POST['description'];
        $pdo->prepare("UPDATE departements SET nom=?, description=? WHERE id=?")->execute([$nom, $desc, $id]);
        header('Location: departements.php?msg=modifie');
        exit;
    } elseif (isset($_POST['supprimer'])) {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM departements WHERE id=?")->execute([$id]);
        header('Location: departements.php?msg=supprime');
        exit;
    }
}

$departements = $pdo->query("SELECT * FROM departements ORDER BY nom")->fetchAll();
$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'ajoute') $message = "Département ajouté.";
    if ($_GET['msg'] == 'modifie') $message = "Département modifié.";
    if ($_GET['msg'] == 'supprime') $message = "Département supprimé.";
}
?>
<div class="admin-header-bar">
    <div style="display: flex; gap: 1rem;">
        <a href="export_departements_pdf.php" class="button">PDF</a>
        <a href="export_departements_excel.php" class="button">Excel</a>
        <button onclick="openAddModal()" class="button button-success">+ Nouveau</button>
    </div>
</div>
<?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<table class="admin-table">
    <thead><tr><th>ID</th><th>Nom</th><th>Description</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($departements as $d): ?>
    <tr>
        <td><?= $d['id'] ?></td>
        <td><?= htmlspecialchars($d['nom']) ?></td>
        <td><?= htmlspecialchars($d['description']) ?></td>
        <td style="display: flex; gap: 8px;">
            <button onclick="openEditModal(<?= $d['id'] ?>, '<?= htmlspecialchars($d['nom']) ?>', '<?= htmlspecialchars($d['description']) ?>')" class="button button-small button-primary">Modifier</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce département ?')">
                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                <button type="submit" name="supprimer" class="button button-small button-danger">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Modale d'ajout -->
<div id="addModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
    <h3>Ajouter un département</h3>
    <form method="post">
        <div class="admin-form-group"><label>Nom</label><input type="text" name="nom" required></div>
        <div class="admin-form-group"><label>Description</label><textarea name="description"></textarea></div>
        <button type="submit" name="ajouter" class="button">Enregistrer</button>
        <button type="button" onclick="closeModal('addModal')" class="button button-outline">Annuler</button>
    </form>
</div>

<!-- Modale d'édition -->
<div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
    <h3>Modifier le département</h3>
    <form method="post">
        <input type="hidden" name="id" id="editId">
        <div class="admin-form-group"><label>Nom</label><input type="text" name="nom" id="editNom" required></div>
        <div class="admin-form-group"><label>Description</label><textarea name="description" id="editDesc"></textarea></div>
        <button type="submit" name="modifier" class="button">Enregistrer</button>
        <button type="button" onclick="closeModal('editModal')" class="button button-outline">Annuler</button>
    </form>
</div>

<script>
function openAddModal() { document.getElementById('addModal').style.display = 'block'; }
function openEditModal(id, nom, desc) {
    document.getElementById('editId').value = id;
    document.getElementById('editNom').value = nom;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editModal').style.display = 'block';
}
function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
window.onclick = function(event) {
    if (event.target == document.getElementById('addModal')) closeModal('addModal');
    if (event.target == document.getElementById('editModal')) closeModal('editModal');
}
</script>
<?php include '../inc/admin_footer.php'; ?>
