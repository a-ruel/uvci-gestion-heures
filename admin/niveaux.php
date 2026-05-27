<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Niveaux d'études";
include '../inc/admin_header.php';

// CRUD niveaux
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $code = trim($_POST['code']);
        $libelle = trim($_POST['libelle']);
        $pdo->prepare("INSERT INTO niveaux_etudes (code, libelle) VALUES (?, ?)")->execute([$code, $libelle]);
        header('Location: niveaux.php?msg=ajoute');
        exit;
    } elseif (isset($_POST['modifier'])) {
        $id = (int)$_POST['id'];
        $code = trim($_POST['code']);
        $libelle = trim($_POST['libelle']);
        $pdo->prepare("UPDATE niveaux_etudes SET code = ?, libelle = ? WHERE id = ?")->execute([$code, $libelle, $id]);
        header('Location: niveaux.php?msg=modifie');
        exit;
    } elseif (isset($_POST['supprimer'])) {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM niveaux_etudes WHERE id = ?")->execute([$id]);
        header('Location: niveaux.php?msg=supprime');
        exit;
    }
}

$niveaux = $pdo->query("SELECT * FROM niveaux_etudes ORDER BY code")->fetchAll();

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'ajoute') $message = "Niveau ajouté.";
    if ($_GET['msg'] == 'modifie') $message = "Niveau modifié.";
    if ($_GET['msg'] == 'supprime') $message = "Niveau supprimé.";
}
?>
<div class="admin-header-bar">
    <h1>Niveaux d'études</h1>
    <div style="display: flex; gap: 1rem;">
        <a href="export_niveaux_pdf.php" class="button">PDF</a>
        <a href="export_niveaux_excel.php" class="button">Excel</a>
        <button onclick="openAddModal()" class="button button-success">+ Nouveau</button>
    </div>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table class="admin-table">
    <thead><tr><th>ID</th><th>Code</th><th>Libellé</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($niveaux as $n): ?>
    <tr>
        <td><?= $n['id'] ?></td>
        <td><?= htmlspecialchars($n['code']) ?></td>
        <td><?= htmlspecialchars($n['libelle']) ?></td>
        <td style="display: flex; gap: 8px;">
            <button onclick="openEditModal(<?= $n['id'] ?>, '<?= htmlspecialchars($n['code']) ?>', '<?= htmlspecialchars($n['libelle']) ?>')" class="button button-small button-primary">Modifier</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce niveau ?')">
                <input type="hidden" name="id" value="<?= $n['id'] ?>">
                <button type="submit" name="supprimer" class="button button-small button-danger">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Modale d'ajout -->
<div id="addModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
    <h3>Ajouter un niveau</h3>
    <form method="post">
        <div class="admin-form-group"><label>Code (ex: L1)</label><input type="text" name="code" required></div>
        <div class="admin-form-group"><label>Libellé (ex: Licence 1)</label><input type="text" name="libelle" required></div>
        <button type="submit" name="ajouter" class="button">Enregistrer</button>
        <button type="button" onclick="closeModal('addModal')" class="button button-outline">Annuler</button>
    </form>
</div>

<!-- Modale d'édition -->
<div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:400px;">
    <h3>Modifier le niveau</h3>
    <form method="post">
        <input type="hidden" name="id" id="editId">
        <div class="admin-form-group"><label>Code</label><input type="text" name="code" id="editCode" required></div>
        <div class="admin-form-group"><label>Libellé</label><input type="text" name="libelle" id="editLibelle" required></div>
        <button type="submit" name="modifier" class="button">Enregistrer</button>
        <button type="button" onclick="closeModal('editModal')" class="button button-outline">Annuler</button>
    </form>
</div>

<script>
function openAddModal() { document.getElementById('addModal').style.display = 'block'; }
function openEditModal(id, code, libelle) {
    document.getElementById('editId').value = id;
    document.getElementById('editCode').value = code;
    document.getElementById('editLibelle').value = libelle;
    document.getElementById('editModal').style.display = 'block';
}
function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
window.onclick = function(event) {
    if (event.target == document.getElementById('addModal')) closeModal('addModal');
    if (event.target == document.getElementById('editModal')) closeModal('editModal');
}
</script>
<?php include '../inc/admin_footer.php'; ?>
