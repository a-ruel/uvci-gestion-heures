<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$logs = $pdo->query("SELECT l.*, u.nom_utilisateur as user_nom 
                     FROM logs l 
                     LEFT JOIN utilisateurs u ON l.utilisateur_id = u.id 
                     ORDER BY l.date_creation DESC")->fetchAll();
$html = '<h1>Logs système</h1><table border="1" cellpadding="5"><thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Détails</th><th>IP</th></tr></thead><tbody>';
foreach ($logs as $log) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($log['date_creation']) . '</td>';
    $html .= '<td>' . htmlspecialchars($log['user_nom'] ?? 'Système') . ' (ID ' . $log['utilisateur_id'] . ')</td>';
    $html .= '<td>' . htmlspecialchars($log['action']) . '</td>';
    $html .= '<td>' . nl2br(htmlspecialchars($log['details'] ?? '')) . '</td>';
    $html .= '<td>' . htmlspecialchars($log['ip']) . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("logs.pdf", ['Attachment' => true]);
