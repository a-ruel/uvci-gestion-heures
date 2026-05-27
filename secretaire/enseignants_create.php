<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Ajouter un enseignant";
include '../inc/secretaire_header.php';

$erreurs = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $grade_id = (int)$_POST['grade_id'];
    $statut_id = (int)$_POST['statut_id'];
    $departement_id = (int)$_POST['departement_id'];
    $taux_horaire = (float)$_POST['taux_horaire'];
    $date_embauche = $_POST['date_embauche'] ?: null;

    $old = compact('nom', 'prenom', 'email', 'telephone', 'grade_id', 'statut_id', 'departement_id', 'taux_horaire', 'date_embauche');

    if (empty($nom)) $erreurs[] = "Le nom est obligatoire.";
    if (empty($prenom)) $erreurs[] = "Le prénom est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "L'email est invalide.";
    if ($taux_horaire <= 0) $erreurs[] = "Le taux horaire doit être supérieur à 0.";

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("INSERT INTO enseignants (nom, prenom, email, telephone, grade_id, statut_id, departement_id, taux_horaire, date_embauche) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$nom, $prenom, $email, $telephone, $grade_id, $statut_id, $departement_id, $taux_horaire, $date_embauche]);
        header('Location: enseignants.php?msg=ajoute');
        exit;
    }
}

$departements = getDepartements($pdo);
$grades = getGrades($pdo);
$statuts = getStatuts($pdo);
?>
<div class="page-header">
    <h1>Ajouter un enseignant</h1>
    <a href="enseignants.php" class="button button-outline">← Retour à la liste</a>
</div>
<?php if ($erreurs): ?>
    <div class="alert error">
        <ul>
            <?php foreach ($erreurs as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<form method="post" class="admin-form">
    <div class="admin-form-group">
        <label>Nom <span class="required">*</span></label>
        <input type="text" name="nom" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required>
    </div>
    <div class="admin-form-group">
        <label>Prénom <span class="required">*</span></label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" required>
    </div>
    <div class="admin-form-group">
        <label>Email <span class="required">*</span></label>
        <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
    </div>
    <div class="admin-form-group">
        <label>Téléphone</label>
        <input type="text" name="telephone" value="<?= htmlspecialchars($old['telephone'] ?? '') ?>">
    </div>
    <div class="admin-form-group">
        <label>Grade <span class="required">*</span></label>
        <select name="grade_id" required>
            <?php foreach ($grades as $g): ?>
                <option value="<?= $g['id'] ?>" <?= (($old['grade_id'] ?? '') == $g['id']) ? 'selected' : '' ?>><?= htmlspecialchars($g['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Statut <span class="required">*</span></label>
        <select name="statut_id" required>
            <?php foreach ($statuts as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (($old['statut_id'] ?? '') == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Département <span class="required">*</span></label>
        <select name="departement_id" required>
            <?php foreach ($departements as $d): ?>
                <option value="<?= $d['id'] ?>" <?= (($old['departement_id'] ?? '') == $d['id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Taux horaire (FCFA) <span class="required">*</span></label>
        <input type="number" step="100" name="taux_horaire" value="<?= htmlspecialchars($old['taux_horaire'] ?? '') ?>" required>
    </div>
    <div class="admin-form-group">
        <label>Date d'embauche</label>
        <input type="date" name="date_embauche" value="<?= htmlspecialchars($old['date_embauche'] ?? '') ?>">
    </div>
    <div class="form-actions">
        <button type="submit" class="button">Enregistrer</button>
        <a href="enseignants.php" class="button button-outline">Annuler</a>
    </div>
</form>
<?php include '../inc/secretaire_footer.php'; ?>
