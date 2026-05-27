<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$niveaux = $pdo->query("SELECT * FROM niveaux_etudes ORDER BY code")->fetchAll();
$html = '<h1>Liste des niveaux d\'études</h1><table border="1" cellpadding="5"><thead><tr><th>ID</th><th>Code</th><th>Libellé</th></tr></thead><tbody>';
foreach ($niveaux as $n) {
    $html .= '<tr><td>' . $n['id'] . '</td><td>' . htmlspecialchars($n['code']) . '</td><td>' . htmlspecialchars($n['libelle']) . '</td></tr>';
}
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("niveaux.pdf", ['Attachment' => true]);
