<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Grades";
include '../inc/admin_header.php';

// CRUD grades
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $nom = trim($_POST['nom']);
        $pdo->prepare("INSERT INTO grades (nom) VALUES (?)")->execute([$nom]);
        header('Location: grades.php?msg=ajoute');
        exit;
    } elseif (isset($_POST['modifier'])) {
        $id = (int)$_POST['id'];
        $nom = trim($_POST['nom']);
        $pdo->prepare("UPDATE grades SET nom = ? WHERE id = ?")->execute([$nom, $id]);
        header('Location: grades.php?msg=modifie');
        exit;
    } elseif (isset($_POST['supprimer'])) {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM grades WHERE id = ?")->execute([$id]);
        header('Location: grades.php?msg=supprime');
        exit;
    }
}

$grades = $pdo->query("SELECT * FROM grades ORDER BY id")->fetchAll();

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'ajoute') $message = "Grade ajouté.";
    if ($_GET['msg'] == 'modifie') $message = "Grade modifié.";
    if ($_GET['msg'] == 'supprime') $message = "Grade supprimé.";
}
?>
<div class="admin-header-bar">
    <h1>Grades</h1>
    <div style="display: flex; gap: 1rem;">
        <a href="export_grades_pdf.php" class="button">PDF</a>
        <a href="export_grades_excel.php" class="button">Excel</a>
        <button onclick="openAddModal()" class="button button-success">+ Nouveau</button>
    </div>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table class="admin-table">
    <thead><tr><th>ID</th><th>Nom</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($grades as $g): ?>
    <tr>
        <td><?= $g['id'] ?></td>
        <td><?= htmlspecialchars($g['nom']) ?></td>
        <td style="display: flex; gap: 8px;">
            <button onclick="openEditModal(<?= $g['id'] ?>, '<?= htmlspecialchars($g['nom']) ?>')" class="button button-small button-primary">Modifier</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce grade ?')">
                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                <button type="submit" name="supprimer" class="button button-small button-danger">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Modale d'ajout -->
<div id="addModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
    <h3>Ajouter un grade</h3>
    <form method="post">
        <div class="admin-form-group"><label>Nom</label><input type="text" name="nom" required></div>
        <button type="submit" name="ajouter" class="button">Enregistrer</button>
        <button type="button" onclick="closeModal('addModal')" class="button button-outline">Annuler</button>
    </form>
</div>

<!-- Modale d'édition -->
<div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
    <h3>Modifier le grade</h3>
    <form method="post">
        <input type="hidden" name="id" id="editId">
        <div class="admin-form-group"><label>Nom</label><input type="text" name="nom" id="editNom" required></div>
        <button type="submit" name="modifier" class="button">Enregistrer</button>
        <button type="button" onclick="closeModal('editModal')" class="button button-outline">Annuler</button>
    </form>
</div>

<script>
function openAddModal() { document.getElementById('addModal').style.display = 'block'; }
function openEditModal(id, nom) {
    document.getElementById('editId').value = id;
    document.getElementById('editNom').value = nom;
    document.getElementById('editModal').style.display = 'block';
}
function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
window.onclick = function(event) {
    if (event.target == document.getElementById('addModal')) closeModal('addModal');
    if (event.target == document.getElementById('editModal')) closeModal('editModal');
}
</script>
<?php include '../inc/admin_footer.php'; ?>
