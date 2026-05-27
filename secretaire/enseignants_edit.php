<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Modifier un enseignant";
include '../inc/secretaire_header.php';

$id = (int)$_GET['id'];
if (!$id) die("ID manquant.");

$stmt = $pdo->prepare("SELECT * FROM enseignants WHERE id = ?");
$stmt->execute([$id]);
$enseignant = $stmt->fetch();
if (!$enseignant) die("Enseignant introuvable.");

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $grade_id = (int)$_POST['grade_id'];
    $statut_id = (int)$_POST['statut_id'];
    $departement_id = (int)$_POST['departement_id'];
    $taux_horaire = (float)$_POST['taux_horaire'];
    $date_embauche = $_POST['date_embauche'] ?: null;

    if (empty($nom)) $erreurs[] = "Nom obligatoire";
    if (empty($prenom)) $erreurs[] = "Prénom obligatoire";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide";
    if ($taux_horaire <= 0) $erreurs[] = "Taux horaire > 0";

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("UPDATE enseignants SET nom=?, prenom=?, email=?, telephone=?, grade_id=?, statut_id=?, departement_id=?, taux_horaire=?, date_embauche=? WHERE id=?");
        $stmt->execute([$nom, $prenom, $email, $telephone, $grade_id, $statut_id, $departement_id, $taux_horaire, $date_embauche, $id]);
        header('Location: enseignants.php?msg=modifie');
        exit;
    }
}

$departements = getDepartements($pdo);
$grades = getGrades($pdo);
$statuts = getStatuts($pdo);
?>
<div class="page-header">
    <h1>Modifier un enseignant</h1>
    <a href="enseignants.php" class="button button-outline">← Retour</a>
</div>
<?php if ($erreurs): ?>
    <div class="alert error"><?= implode('<br>', $erreurs) ?></div>
<?php endif; ?>
<form method="post" class="admin-form">
    <div class="admin-form-group"><label>Nom</label><input type="text" name="nom" value="<?= htmlspecialchars($enseignant['nom']) ?>" required></div>
    <div class="admin-form-group"><label>Prénom</label><input type="text" name="prenom" value="<?= htmlspecialchars($enseignant['prenom']) ?>" required></div>
    <div class="admin-form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($enseignant['email']) ?>" required></div>
    <div class="admin-form-group"><label>Téléphone</label><input type="text" name="telephone" value="<?= htmlspecialchars($enseignant['telephone']) ?>"></div>
    <div class="admin-form-group"><label>Grade</label><select name="grade_id"><?php foreach ($grades as $g): ?><option value="<?= $g['id'] ?>" <?= $g['id']==$enseignant['grade_id']?'selected':'' ?>><?= htmlspecialchars($g['nom']) ?></option><?php endforeach; ?></select></div>
    <div class="admin-form-group"><label>Statut</label><select name="statut_id"><?php foreach ($statuts as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$enseignant['statut_id']?'selected':'' ?>><?= htmlspecialchars($s['nom']) ?></option><?php endforeach; ?></select></div>
    <div class="admin-form-group"><label>Département</label><select name="departement_id"><?php foreach ($departements as $d): ?><option value="<?= $d['id'] ?>" <?= $d['id']==$enseignant['departement_id']?'selected':'' ?>><?= htmlspecialchars($d['nom']) ?></option><?php endforeach; ?></select></div>
    <div class="admin-form-group"><label>Taux horaire (FCFA)</label><input type="number" step="100" name="taux_horaire" value="<?= $enseignant['taux_horaire'] ?>" required></div>
    <div class="admin-form-group"><label>Date d'embauche</label><input type="date" name="date_embauche" value="<?= $enseignant['date_embauche'] ?>"></div>
    <div><button type="submit" class="button">Enregistrer</button> <a href="enseignants.php" class="button button-outline">Annuler</a></div>
</form>
<?php include '../inc/secretaire_footer.php'; ?>
