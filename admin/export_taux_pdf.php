<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$stmt = $pdo->query("SELECT t.taux_horaire, g.nom as grade, s.nom as statut
                     FROM taux_horaires t
                     JOIN grades g ON t.grade_id = g.id
                     JOIN statuts s ON t.statut_id = s.id
                     ORDER BY g.nom, s.nom");
$taux = $stmt->fetchAll();

$html = '<h1>Grille des taux horaires</h1>';
$html .= '<table border="1" cellpadding="5" style="border-collapse:collapse; width:100%">';
$html .= '<thead><tr><th>Grade</th><th>Statut</th><th>Taux horaire (FCFA/h)</th></tr></thead><tbody>';
foreach ($taux as $t) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($t['grade']) . '</td>';
    $html .= '<td>' . htmlspecialchars($t['statut']) . '</td>';
    $html .= '<td>' . number_format($t['taux_horaire'], 0) . ' FCFA/h</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("taux_horaires.pdf", ['Attachment' => true]);
