<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$no_layout = true;
$page_title = "Gestion des séquences";
include '../inc/admin_header.php';

$cours_id = isset($_GET['cours_id']) ? (int)$_GET['cours_id'] : 0;
if ($cours_id > 0) {
    // Inclure la gestion des séquences pour le cours choisi
    include '../secretaire/sequences.php';
} else {
    // Afficher la liste des cours pour sélection
    $cours = $pdo->query("SELECT id, intitule FROM cours ORDER BY intitule")->fetchAll();
    ?>
    <div class="page-header">
        <h1>Séquences</h1>
    </div>
    <p>Sélectionnez un cours pour gérer ses séquences :</p>
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Intitulé</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($cours as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['intitule']) ?></td>
            <td><a href="?cours_id=<?= $c['id'] ?>" class="button button-small">Gérer les séquences</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}
include '../inc/admin_footer.php';
?>
