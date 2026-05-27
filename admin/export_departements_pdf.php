<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$departements = $pdo->query("SELECT * FROM departements ORDER BY nom")->fetchAll();
$html = '<h1>Liste des départements</h1><table border="1" cellpadding="5"><thead><tr><th>ID</th><th>Nom</th><th>Description</th></tr></thead><tbody>';
foreach ($departements as $d) {
    $html .= '<tr><td>' . $d['id'] . '</td><td>' . htmlspecialchars($d['nom']) . '</td><td>' . htmlspecialchars($d['description']) . '</td></tr>';
}
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("departements.pdf", ['Attachment' => true]);
