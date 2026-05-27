<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

// Heures par département
$departements = $pdo->query("SELECT d.nom, COALESCE(SUM(ae.heures_calculees), 0) as total
                             FROM departements d
                             LEFT JOIN enseignants e ON e.departement_id = d.id
                             LEFT JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                             GROUP BY d.id ORDER BY total DESC")->fetchAll();
// Top 5 enseignants
$top = $pdo->query("SELECT CONCAT(e.nom, ' ', e.prenom) as nom, SUM(ae.heures_calculees) as total
                    FROM enseignants e JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                    GROUP BY e.id ORDER BY total DESC LIMIT 5")->fetchAll();

$html = '<h1>Statistiques</h1>';
$html .= '<h2>Heures par département</h2><table border="1"><thead><tr><th>Département</th><th>Heures</th></tr></thead><tbody>';
foreach ($departements as $d) $html .= '<tr><td>' . htmlspecialchars($d['nom']) . '</td><td>' . number_format($d['total'], 2) . ' h</td></tr>';
$html .= '</tbody></table>';
$html .= '<h2>Top 5 enseignants</h2><table border="1"><thead><tr><th>Enseignant</th><th>Heures</th></tr></thead><tbody>';
foreach ($top as $t) $html .= '<tr><td>' . htmlspecialchars($t['nom']) . '</td><td>' . number_format($t['total'], 2) . ' h</td></tr>';
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("statistiques.pdf", ['Attachment' => true]);
