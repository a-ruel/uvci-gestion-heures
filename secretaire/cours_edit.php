<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Modifier un cours";
include '../inc/secretaire_header.php';

$id = (int)$_GET['id'];
if (!$id) die("ID manquant");
$stmt = $pdo->prepare("SELECT * FROM cours WHERE id = ?");
$stmt->execute([$id]);
$cours = $stmt->fetch();
if (!$cours) die("Cours introuvable");

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $intitule = trim($_POST['intitule']);
    $filiere = trim($_POST['filiere']);
    $niveau_id = (int)$_POST['niveau_id'];
    $semestre = (int)$_POST['semestre'];
    $heures_total = (int)$_POST['heures_total'];
    $credits = (int)$_POST['credits'];
    $departement_id = $_POST['departement_id'] ? (int)$_POST['departement_id'] : null;
    $annee_id = $_POST['annee_academique_id'] ? (int)$_POST['annee_academique_id'] : null;

    if (empty($intitule)) $erreurs[] = "Intitulé obligatoire";
    if (empty($erreurs)) {
        $stmt = $pdo->prepare("UPDATE cours SET intitule=?, filiere=?, niveau_id=?, semestre=?, heures_total=?, credits=?, departement_id=?, annee_academique_id=? WHERE id=?");
        $stmt->execute([$intitule, $filiere, $niveau_id, $semestre, $heures_total, $credits, $departement_id, $annee_id, $id]);
        header('Location: cours.php?msg=modifie');
        exit;
    }
}
$niveaux = getNiveaux($pdo);
$departements = getDepartements($pdo);
$annees = getAnneesAcademiques($pdo);
?>
<div class="page-header">
    <h1>Modifier un cours</h1>
    <a href="cours.php" class="button button-outline">← Retour</a>
</div>
<?php if ($erreurs): ?><div class="alert error"><?= implode('<br>', $erreurs) ?></div><?php endif; ?>
<form method="post" class="admin-form">
    <div class="admin-form-group"><label>Intitulé</label><input type="text" name="intitule" value="<?= htmlspecialchars($cours['intitule']) ?>" required></div>
    <div class="admin-form-group"><label>Filière</label><input type="text" name="filiere" value="<?= htmlspecialchars($cours['filiere']) ?>" required></div>
    <div class="admin-form-group"><label>Niveau</label><select name="niveau_id"><?php foreach ($niveaux as $n): ?><option value="<?= $n['id'] ?>" <?= $n['id']==$cours['niveau_id']?'selected':'' ?>><?= htmlspecialchars($n['code']) ?></option><?php endforeach; ?></select></div>
    <div class="admin-form-group"><label>Semestre</label><select name="semestre"><?php for($i=1;$i<=6;$i++) echo "<option value='$i'".($cours['semestre']==$i?"selected":"").">S$i</option>"; ?></select></div>
    <div class="admin-form-group"><label>Heures total</label><input type="number" name="heures_total" value="<?= $cours['heures_total'] ?>"></div>
    <div class="admin-form-group"><label>Crédits</label><input type="number" name="credits" value="<?= $cours['credits'] ?>"></div>
    <div class="admin-form-group"><label>Département</label><select name="departement_id"><option value="">-- Optionnel --</option><?php foreach ($departements as $d): ?><option value="<?= $d['id'] ?>" <?= $d['id']==$cours['departement_id']?'selected':'' ?>><?= htmlspecialchars($d['nom']) ?></option><?php endforeach; ?></select></div>
    <div class="admin-form-group"><label>Année académique</label><select name="annee_academique_id"><option value="">-- Optionnel --</option><?php foreach ($annees as $a): ?><option value="<?= $a['id'] ?>" <?= $a['id']==$cours['annee_academique_id']?'selected':'' ?>><?= htmlspecialchars($a['libelle']) ?></option><?php endforeach; ?></select></div>
    <button type="submit" class="button">Enregistrer</button> <a href="cours.php" class="button button-outline">Annuler</a>
</form>
<?php include '../inc/secretaire_footer.php'; ?>
