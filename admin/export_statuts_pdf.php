<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$statuts = $pdo->query("SELECT * FROM statuts ORDER BY id")->fetchAll();
$html = '<h1>Liste des statuts</h1><table border="1" cellpadding="5"><thead><tr><th>ID</th><th>Nom</th></tr></thead><tbody>';
foreach ($statuts as $s) {
    $html .= '<tr><td>' . $s['id'] . '</td><td>' . htmlspecialchars($s['nom']) . '</td></tr>';
}
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("statuts.pdf", ['Attachment' => true]);
