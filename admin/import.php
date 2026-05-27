<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Import CSV";
include '../inc/admin_header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier'])) {
    $type = $_POST['type'] ?? 'enseignants';
    $file = $_FILES['fichier']['tmp_name'];
    if (($handle = fopen($file, 'r')) !== false) {
        $lignes = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($lignes == 0) { $lignes++; continue; }
            if ($type == 'enseignants') {
                $stmt = $pdo->prepare("INSERT INTO enseignants (nom, prenom, email, grade_id, statut_id, departement_id, taux_horaire) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]]);
            } elseif ($type == 'cours') {
                $stmt = $pdo->prepare("INSERT INTO cours (intitule, filiere, niveau_id, semestre, heures_total, credits, departement_id, annee_academique_id) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7]]);
            }
            $lignes++;
        }
        fclose($handle);
        $message = "Import terminé : " . ($lignes-1) . " lignes traitées.";
    } else {
        $message = "Erreur d'ouverture du fichier.";
    }
}
?>
<div class="admin-header-bar">
    <h1>Import CSV</h1>
</div>
<?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="admin-form">
    <div class="admin-form-group"><label>Type</label><select name="type"><option value="enseignants">Enseignants</option><option value="cours">Cours</option></select></div>
    <div class="admin-form-group"><label>Fichier CSV (avec entêtes)</label><input type="file" name="fichier" accept=".csv" required></div>
    <button type="submit" class="button">Importer</button>
</form>
<?php include '../inc/admin_footer.php'; ?>
