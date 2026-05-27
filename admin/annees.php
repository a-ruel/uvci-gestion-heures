<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Années académiques";
include '../inc/admin_header.php';

// Ajout / modification / suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $libelle = trim($_POST['libelle']);
        $debut = $_POST['date_debut'];
        $fin = $_POST['date_fin'];
        $active = isset($_POST['est_active']) ? 1 : 0;
        if ($active) {
            $pdo->exec("UPDATE annees_academiques SET est_active = 0");
        }
        $stmt = $pdo->prepare("INSERT INTO annees_academiques (libelle, date_debut, date_fin, est_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$libelle, $debut, $fin, $active]);
        header('Location: annees.php?msg=ajoute');
        exit;
    } elseif (isset($_POST['modifier'])) {
        $id = (int)$_POST['id'];
        $libelle = trim($_POST['libelle']);
        $debut = $_POST['date_debut'];
        $fin = $_POST['date_fin'];
        $active = isset($_POST['est_active']) ? 1 : 0;
        if ($active) {
            $pdo->exec("UPDATE annees_academiques SET est_active = 0");
        }
        $stmt = $pdo->prepare("UPDATE annees_academiques SET libelle=?, date_debut=?, date_fin=?, est_active=? WHERE id=?");
        $stmt->execute([$libelle, $debut, $fin, $active, $id]);
        header('Location: annees.php?msg=modifie');
        exit;
    } elseif (isset($_POST['supprimer'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM annees_academiques WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: annees.php?msg=supprime');
        exit;
    }
}

$annees = $pdo->query("SELECT * FROM annees_academiques ORDER BY date_debut DESC")->fetchAll();
$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'ajoute') $message = "Année ajoutée avec succès.";
    if ($_GET['msg'] == 'modifie') $message = "Année modifiée avec succès.";
    if ($_GET['msg'] == 'supprime') $message = "Année supprimée.";
}
?>
<div class="admin-header-bar">
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="annees-card">
    <h2>Ajouter une année</h2>
    <form method="post" class="annees-form" style="max-width: 100%;">
        <div class="admin-form-group">
            <label>Libellé (ex: 2025-2026)</label>
            <input type="text" name="libelle" required>
        </div>
        <div class="admin-form-group">
            <label>Date début</label>
            <input type="date" name="date_debut" required>
        </div>
        <div class="admin-form-group">
            <label>Date fin</label>
            <input type="date" name="date_fin" required>
        </div>
        <div class="admin-form-group">
            <label><input type="checkbox" name="est_active"> Année active (une seule possible)</label>
        </div>
        <button type="submit" name="ajouter" class="button">Ajouter</button>
    </form>
</div>

<h2>Liste des années</h2>
<table class="admin-table annees-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Libellé</th>
            <th>Date début</th>
            <th>Date fin</th>
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($annees as $a): ?>
    <tr>
        <td><?= $a['id'] ?></td>
        <td><?= htmlspecialchars($a['libelle']) ?></td>
        <td><?= $a['date_debut'] ?></td>
        <td><?= $a['date_fin'] ?></td>
        <td><?= $a['est_active'] ? '✅ Oui' : '❌ Non' ?></td>
        <td>
            <button onclick="edit(<?= $a['id'] ?>, '<?= htmlspecialchars($a['libelle']) ?>', '<?= $a['date_debut'] ?>', '<?= $a['date_fin'] ?>', <?= $a['est_active'] ?>)" class="button button-small button-primary">Modifier</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette année ?')">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button type="submit" name="supprimer" class="button button-small button-danger">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Popup de modification -->
<script>
function edit(id, libelle, debut, fin, active) {
    var form = document.createElement('form');
    form.method = 'post';
    form.style.position = 'fixed';
    form.style.top = '50%';
    form.style.left = '50%';
    form.style.transform = 'translate(-50%, -50%)';
    form.style.backgroundColor = 'white';
    form.style.padding = '2rem';
    form.style.borderRadius = '24px';
    form.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
    form.style.zIndex = '1000';
    form.style.width = '400px';
    form.innerHTML = `
        <h3 style="margin-bottom:1rem">Modifier l'année</h3>
        <input type="hidden" name="id" value="${id}">
        <div style="margin-bottom:1rem">
            <label>Libellé</label>
            <input type="text" name="libelle" value="${libelle}" style="width:100%; padding:0.5rem; border-radius:12px; border:1px solid #ccc;">
        </div>
        <div style="margin-bottom:1rem">
            <label>Date début</label>
            <input type="date" name="date_debut" value="${debut}" style="width:100%; padding:0.5rem; border-radius:12px; border:1px solid #ccc;">
        </div>
        <div style="margin-bottom:1rem">
            <label>Date fin</label>
            <input type="date" name="date_fin" value="${fin}" style="width:100%; padding:0.5rem; border-radius:12px; border:1px solid #ccc;">
        </div>
        <div style="margin-bottom:1rem">
            <label><input type="checkbox" name="est_active" ${active ? 'checked' : ''}> Année active (une seule possible)</label>
        </div>
        <button type="submit" name="modifier" class="button">Enregistrer</button>
        <button type="button" onclick="this.closest('form').remove()" class="button button-outline">Annuler</button>
    `;
    document.body.appendChild(form);
}
</script>

<?php include '../inc/admin_footer.php'; ?>
