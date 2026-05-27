<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$id = (int)$_GET['id'];
if (!$id) die("ID manquant");
$stmt = $pdo->prepare("SELECT * FROM ressources WHERE id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) die("Ressource introuvable");
$page_title = "Modifier une ressource";
include '../inc/secretaire_header.php';
?>
<div class="admin-header-bar"><h1>Modifier une ressource</h1><a href="ressources.php" class="button button-outline">← Retour</a></div>
<form method="post" action="ressources.php" class="admin-form">
    <input type="hidden" name="id" value="<?= $r['id'] ?>">
    <div class="admin-form-group"><label>Titre</label><input type="text" name="titre" value="<?= htmlspecialchars($r['titre']) ?>" required></div>
    <div class="admin-form-group"><label>Type</label><select name="type_ressource"><?php $types = ['texte','video','document','quiz','activite_interactive','evaluation']; foreach($types as $t): ?><option <?= $r['type_ressource']==$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select></div>
    <div class="admin-form-group"><label>URL ou chemin</label><input type="text" name="url_fichier" value="<?= htmlspecialchars($r['url_fichier']) ?>"></div>
    <div class="admin-form-group"><label>Description</label><textarea name="description"><?= htmlspecialchars($r['description']) ?></textarea></div>
    <button type="submit" name="modifier" class="button">Enregistrer</button>
    <a href="ressources.php" class="button button-outline">Annuler</a>
</form>
<?php include '../inc/secretaire_footer.php'; ?>
