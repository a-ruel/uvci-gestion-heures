<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Gestion des séquences";
include '../inc/secretaire_header.php';

$cours_id = (int)$_GET['cours_id'];
if (!$cours_id) die("Cours non spécifié.");

$stmt = $pdo->prepare("SELECT intitule FROM cours WHERE id = ?");
$stmt->execute([$cours_id]);
$cours = $stmt->fetch();
if (!$cours) die("Cours introuvable.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter'])) {
        $num = (int)$_POST['numero'];
        $titre = trim($_POST['titre']);
        $desc = $_POST['description'];
        $duree = (float)$_POST['duree'];
        $stmt = $pdo->prepare("INSERT INTO sequences (cours_id, numero_sequence, titre, description, duree_estimee_heures) VALUES (?,?,?,?,?)");
        $stmt->execute([$cours_id, $num, $titre, $desc, $duree]);
        header("Location: sequences.php?cours_id=$cours_id&msg=ajoute");
        exit;
    } elseif (isset($_POST['modifier'])) {
        $id = (int)$_POST['id'];
        $num = (int)$_POST['numero'];
        $titre = trim($_POST['titre']);
        $desc = $_POST['description'];
        $duree = (float)$_POST['duree'];
        $stmt = $pdo->prepare("UPDATE sequences SET numero_sequence=?, titre=?, description=?, duree_estimee_heures=? WHERE id=? AND cours_id=?");
        $stmt->execute([$num, $titre, $desc, $duree, $id, $cours_id]);
        header("Location: sequences.php?cours_id=$cours_id&msg=modifie");
        exit;
    } elseif (isset($_POST['supprimer'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM sequences WHERE id=? AND cours_id=?");
        $stmt->execute([$id, $cours_id]);
        header("Location: sequences.php?cours_id=$cours_id&msg=supprime");
        exit;
    }
}

$sequences = $pdo->prepare("SELECT * FROM sequences WHERE cours_id = ? ORDER BY numero_sequence");
$sequences->execute([$cours_id]);
$sequences = $sequences->fetchAll();

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'ajoute') $message = "Séquence ajoutée.";
    if ($_GET['msg'] == 'modifie') $message = "Séquence modifiée.";
    if ($_GET['msg'] == 'supprime') $message = "Séquence supprimée.";
}
?>
<div class="page-header">
    <h1>Séquences du cours : <?= htmlspecialchars($cours['intitule']) ?></h1>
    <a href="cours.php" class="button button-outline">← Retour aux cours</a>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<div class="card">
    <h2>Ajouter une séquence</h2>
    <form method="post" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;">
        <div><label>Numéro</label><input type="number" name="numero" placeholder="Ex: 1" required style="border-radius:30px; padding:0.5rem;"></div>
        <div><label>Titre</label><input type="text" name="titre" placeholder="Titre" required style="border-radius:30px; padding:0.5rem;"></div>
        <div><label>Description</label><input type="text" name="description" placeholder="Description (optionnel)" style="border-radius:30px; padding:0.5rem;"></div>
        <div><label>Durée (h)</label><input type="number" step="0.5" name="duree" placeholder="Heures" style="border-radius:30px; padding:0.5rem;"></div>
        <div><button type="submit" name="ajouter" class="button">Ajouter</button></div>
    </form>
</div>
<h2>Liste des séquences</h2>
<table class="admin-table">
    <thead><tr><th>Numéro</th><th>Titre</th><th>Description</th><th>Durée (h)</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (count($sequences) === 0): ?>
        <tr><td colspan="5">Aucune séquence pour ce cours.<?php else: ?>
        <?php foreach ($sequences as $s): ?>
        <form method="post" style="display: contents;">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <tr>
                <td><input type="number" name="numero" value="<?= $s['numero_sequence'] ?>" style="border-radius:30px; padding:0.3rem 0.5rem;"></td>
                <td><input type="text" name="titre" value="<?= htmlspecialchars($s['titre']) ?>" style="border-radius:30px; padding:0.3rem 0.5rem;"></td>
                <td><input type="text" name="description" value="<?= htmlspecialchars($s['description']) ?>" style="border-radius:30px; padding:0.3rem 0.5rem;"></td>
                <td><input type="number" step="0.5" name="duree" value="<?= $s['duree_estimee_heures'] ?>" style="border-radius:30px; padding:0.3rem 0.5rem;"></td>
                <td style="white-space: nowrap;">
                    <button type="submit" name="modifier" class="button button-small">Modifier</button>
                    <button type="submit" name="supprimer" class="button button-small button-danger" onclick="return confirm('Supprimer cette séquence ?')">Supprimer</button>
                </td>
            </tr>
        </form>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
<?php include '../inc/secretaire_footer.php'; ?>
