<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Gestion des ressources";
include '../inc/secretaire_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $titre = trim($_POST['titre']);
        $type = $_POST['type_ressource'];
        $url = trim($_POST['url_fichier']);
        $desc = $_POST['description'];
        $stmt = $pdo->prepare("INSERT INTO ressources (titre, type_ressource, url_fichier, description) VALUES (?,?,?,?)");
        $stmt->execute([$titre, $type, $url, $desc]);
        header('Location: ressources.php?msg=ajoute');
        exit;
    } elseif (isset($_POST['modifier'])) {
        $id = (int)$_POST['id'];
        $titre = trim($_POST['titre']);
        $type = $_POST['type_ressource'];
        $url = trim($_POST['url_fichier']);
        $desc = $_POST['description'];
        $stmt = $pdo->prepare("UPDATE ressources SET titre=?, type_ressource=?, url_fichier=?, description=? WHERE id=?");
        $stmt->execute([$titre, $type, $url, $desc, $id]);
        header('Location: ressources.php?msg=modifie');
        exit;
    } elseif (isset($_POST['supprimer'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM ressources WHERE id=?");
        $stmt->execute([$id]);
        header('Location: ressources.php?msg=supprime');
        exit;
    } elseif (isset($_POST['lier_sequence'])) {
        $ressource_id = (int)$_POST['ressource_id'];
        $sequence_id = (int)$_POST['sequence_id'];
        $stmt = $pdo->prepare("INSERT IGNORE INTO ressources_sequences (ressource_id, sequence_id) VALUES (?,?)");
        $stmt->execute([$ressource_id, $sequence_id]);
        header('Location: ressources.php?msg=lie');
        exit;
    } elseif (isset($_POST['dissocier'])) {
        $ressource_id = (int)$_POST['ressource_id'];
        $sequence_id = (int)$_POST['sequence_id'];
        $stmt = $pdo->prepare("DELETE FROM ressources_sequences WHERE ressource_id=? AND sequence_id=?");
        $stmt->execute([$ressource_id, $sequence_id]);
        header('Location: ressources.php?msg=lie');
        exit;
    }
}

$ressources = $pdo->query("SELECT * FROM ressources ORDER BY id DESC")->fetchAll();
$sequences = $pdo->query("SELECT s.id, s.titre, c.intitule as cours FROM sequences s JOIN cours c ON s.cours_id = c.id ORDER BY c.intitule, s.numero_sequence")->fetchAll();

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'ajoute') $message = "Ressource ajoutée.";
    if ($_GET['msg'] == 'modifie') $message = "Ressource modifiée.";
    if ($_GET['msg'] == 'supprime') $message = "Ressource supprimée.";
    if ($_GET['msg'] == 'lie') $message = "Lien modifié.";
}
?>
<div class="page-header">
    <h1>Gestion des ressources pédagogiques</h1>
    <button onclick="openAddModal()" class="button button-success">+ Nouvelle ressource</button>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<table class="admin-table">
    <thead><tr><th>ID</th><th>Titre</th><th>Type</th><th>URL</th><th>Description</th><th>Séquences liées</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($ressources as $r): ?>
    <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['titre']) ?></td>
        <td><?= htmlspecialchars($r['type_ressource']) ?></td>
        <td><?= htmlspecialchars($r['url_fichier']) ?></td>
        <td><?= htmlspecialchars($r['description']) ?></td>
        <td>
            <?php
            $stmt = $pdo->prepare("SELECT s.id, s.titre FROM ressources_sequences rs JOIN sequences s ON rs.sequence_id = s.id WHERE rs.ressource_id = ?");
            $stmt->execute([$r['id']]);
            $liens = $stmt->fetchAll();
            foreach ($liens as $l) {
                echo "<span style='background:#f1f5f9; padding:2px 8px; margin:2px; display:inline-block;'>"
                     . htmlspecialchars($l['titre']) . 
                     " <form method='post' style='display:inline;'><input type='hidden' name='ressource_id' value='{$r['id']}'><input type='hidden' name='sequence_id' value='{$l['id']}'><button type='submit' name='dissocier' style='background:none; border:none; color:red; cursor:pointer;'>x</button></form></span>";
            }
            ?>
            <form method="post" style="margin-top: 5px;">
                <input type="hidden" name="ressource_id" value="<?= $r['id'] ?>">
                <select name="sequence_id" style="padding: 4px; border-radius: 12px;">
                    <option value="">-- Lier à une séquence --</option>
                    <?php foreach ($sequences as $seq): ?>
                        <option value="<?= $seq['id'] ?>"><?= htmlspecialchars($seq['cours'] . ' - ' . $seq['titre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="lier_sequence" class="button button-small">Lier</button>
            </form>
        </td>
        <td style="white-space: nowrap;">
            <button onclick="openEditModal(<?= $r['id'] ?>, '<?= htmlspecialchars($r['titre']) ?>', '<?= htmlspecialchars($r['type_ressource']) ?>', '<?= htmlspecialchars($r['url_fichier']) ?>', '<?= htmlspecialchars($r['description']) ?>')" class="button button-small button-primary">Modifier</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette ressource ?')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" name="supprimer" class="button button-small button-danger">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
<tr>
<div id="addModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:450px;">
    <h3>Ajouter une ressource</h3>
    <form method="post">
        <div class="admin-form-group"><label>Titre</label><input type="text" name="titre" required></div>
        <div class="admin-form-group"><label>Type</label><select name="type_ressource"><option>texte</option><option>video</option><option>document</option><option>quiz</option><option>activite_interactive</option><option>evaluation</option></select></div>
        <div class="admin-form-group"><label>URL ou chemin</label><input type="text" name="url_fichier"></div>
        <div class="admin-form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
        <button type="submit" name="ajouter" class="button">Ajouter</button>
        <button type="button" onclick="closeAddModal()" class="button button-outline">Annuler</button>
    </form>
</div>
<div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:2rem; border-radius:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:1000; width:450px;">
    <h3>Modifier la ressource</h3>
    <form method="post">
        <input type="hidden" name="id" id="editId">
        <div class="admin-form-group"><label>Titre</label><input type="text" name="titre" id="editTitre" required></div>
        <div class="admin-form-group"><label>Type</label><select name="type_ressource" id="editType"></select></div>
        <div class="admin-form-group"><label>URL ou chemin</label><input type="text" name="url_fichier" id="editUrl"></div>
        <div class="admin-form-group"><label>Description</label><textarea name="description" id="editDesc" rows="2"></textarea></div>
        <button type="submit" name="modifier" class="button">Enregistrer</button>
        <button type="button" onclick="closeEditModal()" class="button button-outline">Annuler</button>
    </form>
</div>
<script>
function openAddModal() { document.getElementById('addModal').style.display = 'block'; }
function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }
function openEditModal(id, titre, type, url, desc) {
    document.getElementById('editId').value = id;
    document.getElementById('editTitre').value = titre;
    document.getElementById('editUrl').value = url;
    document.getElementById('editDesc').value = desc;
    let typeSelect = document.getElementById('editType');
    typeSelect.innerHTML = `
        <option value="texte" ${type=='texte'?'selected':''}>texte</option>
        <option value="video" ${type=='video'?'selected':''}>video</option>
        <option value="document" ${type=='document'?'selected':''}>document</option>
        <option value="quiz" ${type=='quiz'?'selected':''}>quiz</option>
        <option value="activite_interactive" ${type=='activite_interactive'?'selected':''}>activite_interactive</option>
        <option value="evaluation" ${type=='evaluation'?'selected':''}>evaluation</option>`;
    document.getElementById('editModal').style.display = 'block';
}
function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
window.onclick = function(event) {
    if (event.target == document.getElementById('addModal')) closeAddModal();
    if (event.target == document.getElementById('editModal')) closeEditModal();
}
</script>
<?php include '../inc/secretaire_footer.php'; ?>
