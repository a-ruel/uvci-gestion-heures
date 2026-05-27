<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$activites = $pdo->query("SELECT a.id, CONCAT(e.nom,' ',e.prenom) as enseignant, t.nom as type, a.niveau_complexite, a.date_realisation, a.heures_calculees, a.valide FROM activites_enseignants a JOIN enseignants e ON a.enseignant_id = e.id JOIN types_activite t ON a.type_activite_id = t.id ORDER BY a.date_realisation DESC")->fetchAll();

$html = '<h1>Liste des activités pédagogiques</h1><table border="1" cellpadding="5"><thead><tr><th>ID</th><th>Enseignant</th><th>Type</th><th>Niveau</th><th>Date</th><th>Heures</th><th>Validée</th></tr></thead><tbody>';
foreach ($activites as $a) {
    $html .= '<tr>';
    $html .= '<td>' . $a['id'] . '</td>';
    $html .= '<td>' . htmlspecialchars($a['enseignant']) . '</td>';
    $html .= '<td>' . htmlspecialchars($a['type']) . '</td>';
    $html .= '<td>' . $a['niveau_complexite'] . '</td>';
    $html .= '<td>' . $a['date_realisation'] . '</td>';
    $html .= '<td>' . number_format($a['heures_calculees'], 2) . ' h</td>';
    $html .= '<td>' . ($a['valide'] ? 'Oui' : 'Non') . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("activites.pdf", ['Attachment' => true]);
