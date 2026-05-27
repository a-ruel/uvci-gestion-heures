<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Ajouter une ressource";
include '../inc/secretaire_header.php';
?>
<div class="admin-header-bar"><h1>Ajouter une ressource</h1><a href="ressources.php" class="button button-outline">← Retour</a></div>
<form method="post" action="ressources.php" class="admin-form">
    <div class="admin-form-group"><label>Titre</label><input type="text" name="titre" required></div>
    <div class="admin-form-group"><label>Type</label><select name="type_ressource"><option>texte</option><option>video</option><option>document</option><option>quiz</option><option>activite_interactive</option><option>evaluation</option></select></div>
    <div class="admin-form-group"><label>URL ou chemin</label><input type="text" name="url_fichier"></div>
    <div class="admin-form-group"><label>Description</label><textarea name="description"></textarea></div>
    <button type="submit" name="ajouter" class="button">Enregistrer</button>
    <a href="ressources.php" class="button button-outline">Annuler</a>
</form>
<?php include '../inc/secretaire_footer.php'; ?>
