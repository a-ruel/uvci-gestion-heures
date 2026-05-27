<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Logs système";
include '../inc/admin_header.php';

// Vider les logs
if (isset($_GET['clear']) && $_GET['clear'] == 1) {
    $pdo->exec("DELETE FROM logs");
    header("Location: logs.php?msg=cleared");
    exit;
}

// Pagination
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total = $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
$totalPages = ceil($total / $limit);

$stmt = $pdo->prepare("SELECT l.*, u.nom_utilisateur as user_nom 
                       FROM logs l 
                       LEFT JOIN utilisateurs u ON l.utilisateur_id = u.id 
                       ORDER BY l.date_creation DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

$message = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'cleared') {
    $message = "Logs vidés avec succès.";
}
?>
<div class="admin-header-bar">
    <h1>Logs système</h1>
    <div style="display: flex; gap: 1rem;">
        <a href="export_logs_pdf.php" class="button">PDF</a>
        <a href="export_logs_excel.php" class="button">Excel</a>
        <a href="?clear=1" class="button button-danger" onclick="return confirm('Vider tous les logs ?')">Vider les logs</a>
    </div>
</div>
<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table class="admin-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Utilisateur</th>
            <th>Action</th>
            <th>Détails</th>
            <th>IP</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($logs) === 0): ?>
        <tr><td colspan="5">Aucun log enregistré.<?php else: ?>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['date_creation']) ?></td>
            <td><?= htmlspecialchars($log['user_nom'] ?? 'Système') ?> (ID <?= $log['utilisateur_id'] ?>)</td>
            <td><?= htmlspecialchars($log['action']) ?></td>
            <td><?= nl2br(htmlspecialchars($log['details'] ?? '')) ?></td>
            <td><?= htmlspecialchars($log['ip']) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<!-- Pagination -->
<div style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: center;">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>" class="button button-small">Précédent</a>
    <?php endif; ?>
    <span>Page <?= $page ?> sur <?= $totalPages ?></span>
    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>" class="button button-small">Suivant</a>
    <?php endif; ?>
</div>

<?php include '../inc/admin_footer.php'; ?>
