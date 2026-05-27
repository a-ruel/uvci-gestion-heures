<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['secretaire', 'admin']);
$page_title = "Saisie d'une activité";
include '../inc/secretaire_header.php';

$enseignants = $pdo->query("SELECT id, nom, prenom FROM enseignants ORDER BY nom")->fetchAll();
$cours = $pdo->query("SELECT id, intitule FROM cours ORDER BY intitule")->fetchAll();
$types = $pdo->query("SELECT id, nom FROM types_activite")->fetchAll();

$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enseignant_id = (int)$_POST['enseignant_id'];
    $cours_id = (int)$_POST['cours_id'];
    $sequence_id = (int)$_POST['sequence_id'];
    $type_id = (int)$_POST['type_activite_id'];
    $niveau = (int)$_POST['niveau_complexite'];
    $date = $_POST['date_realisation'];

    $stmt = $pdo->prepare("SELECT coefficient_horaire FROM facteurs_complexite WHERE type_activite_id = ? AND niveau = ?");
    $stmt->execute([$type_id, $niveau]);
    $coeff = $stmt->fetchColumn();
    $heures = $coeff ?: 0;

    if (!$enseignant_id) $erreurs[] = "Enseignant requis";
    if (!$sequence_id) $erreurs[] = "Séquence requise";
    if ($heures == 0) $erreurs[] = "Coefficient horaire non trouvé";

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("INSERT INTO activites_enseignants (enseignant_id, sequence_id, type_activite_id, niveau_complexite, date_realisation, heures_calculees) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$enseignant_id, $sequence_id, $type_id, $niveau, $date, $heures]);
        $success = true;
    }
}
?>
<div class="page-header">
    <h1>Saisie d'une activité pédagogique</h1>
    <a href="activites_validation.php" class="button">Gérer les validations</a>
</div>
<?php if ($success): ?>
    <div class="alert success">Activité enregistrée (en attente de validation).</div>
<?php endif; ?>
<?php if ($erreurs): ?>
    <div class="alert error"><?= implode('<br>', $erreurs) ?></div>
<?php endif; ?>
<form method="post" class="admin-form">
    <div class="admin-form-group">
        <label>Enseignant</label>
        <select name="enseignant_id" id="enseignant_id" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($enseignants as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Cours</label>
        <select name="cours_id" id="cours_id" required>
            <option value="">-- Choisir un cours --</option>
            <?php foreach ($cours as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['intitule']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Séquence</label>
        <select name="sequence_id" id="sequence_id" required>
            <option value="">-- Choisissez d'abord un cours --</option>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Type d'activité</label>
        <select name="type_activite_id" required>
            <?php foreach ($types as $t): ?>
                <option value="<?= $t['id'] ?>"><?= ucfirst($t['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Niveau de complexité</label>
        <select name="niveau_complexite" required>
            <option value="1">Niveau 1 (simple)</option>
            <option value="2">Niveau 2 (moyen)</option>
            <option value="3">Niveau 3 (complexe)</option>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Date de réalisation</label>
        <input type="date" name="date_realisation" value="<?= date('Y-m-d') ?>" required>
    </div>
    <button type="submit" class="button">Enregistrer l'activité</button>
</form>
<script>
document.getElementById('cours_id').addEventListener('change', function() {
    let coursId = this.value;
    let seqSelect = document.getElementById('sequence_id');
    if (!coursId) {
        seqSelect.innerHTML = '<option value="">-- Choisissez d\'abord un cours --</option>';
        return;
    }
    fetch('get_sequences.php?cours_id=' + coursId)
        .then(response => response.json())
        .then(data => {
            seqSelect.innerHTML = '<option value="">-- Choisir une séquence --</option>';
            data.forEach(seq => {
                let option = document.createElement('option');
                option.value = seq.id;
                option.textContent = seq.numero_sequence + ' - ' + seq.titre;
                seqSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erreur:', error);
            seqSelect.innerHTML = '<option value="">Erreur de chargement</option>';
        });
});
</script>
<?php include '../inc/secretaire_footer.php'; ?>
