<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Sauvegarde";
include '../inc/admin_header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sauvegarder'])) {
    // Génération du dump SQL
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "DROP TABLE IF EXISTS `$table`;\n";
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        echo $create['Create Table'] . ";\n\n";
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll();
        foreach ($rows as $row) {
            $cols = array_keys($row);
            $vals = array_map(function($v) use ($pdo) { return $pdo->quote($v); }, array_values($row));
            echo "INSERT INTO `$table` (`" . implode("`,`", $cols) . "`) VALUES (" . implode(",", $vals) . ");\n";
        }
        echo "\n";
    }
    exit;
}
?>
<div class="admin-header-bar">
    <h1>Sauvegarde de la base de données</h1>
</div>
<?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<p>Cliquez sur le bouton ci-dessous pour télécharger un fichier SQL contenant la sauvegarde complète de la base.</p>
<form method="post">
    <button type="submit" name="sauvegarder" class="button button-success">Télécharger la sauvegarde</button>
</form>
<?php include '../inc/admin_footer.php'; ?>
