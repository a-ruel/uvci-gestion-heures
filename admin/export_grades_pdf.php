<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$grades = $pdo->query("SELECT * FROM grades ORDER BY id")->fetchAll();
$html = '<h1>Liste des grades</h1><table border="1" cellpadding="5"><thead><tr><th>ID</th><th>Nom</th></tr></thead><tbody>';
foreach ($grades as $g) {
    $html .= '<tr><td>' . $g['id'] . '</td><td>' . htmlspecialchars($g['nom']) . '</td></tr>';
}
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("grades.pdf", ['Attachment' => true]);
