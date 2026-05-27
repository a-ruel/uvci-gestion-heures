<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

// Récupérer les filtres
$search = $_GET['search'] ?? '';
$departement_id = $_GET['departement_id'] ?? '';
$grade_id = $_GET['grade_id'] ?? '';
$statut_id = $_GET['statut_id'] ?? '';

$sql = "SELECT e.id, e.nom, e.prenom, e.email, e.taux_horaire,
               g.nom as grade_nom, s.nom as statut_nom, d.nom as departement_nom
        FROM enseignants e
        LEFT JOIN grades g ON e.grade_id = g.id
        LEFT JOIN statuts s ON e.statut_id = s.id
        LEFT JOIN departements d ON e.departement_id = d.id
        WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (e.nom LIKE :search OR e.prenom LIKE :search OR e.email LIKE :search)"; $params['search'] = "%$search%"; }
if ($departement_id) { $sql .= " AND e.departement_id = :departement_id"; $params['departement_id'] = $departement_id; }
if ($grade_id) { $sql .= " AND e.grade_id = :grade_id"; $params['grade_id'] = $grade_id; }
if ($statut_id) { $sql .= " AND e.statut_id = :statut_id"; $params['statut_id'] = $statut_id; }
$sql .= " ORDER BY e.nom, e.prenom";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$enseignants = $stmt->fetchAll();

$html = '<h1>Liste des enseignants</h1>';
$html .= '<table border="1" cellpadding="5"><thead><tr><th>CODE</th><th>NOM</th><th>PRÉNOM</th><th>GRADE</th><th>STATUT</th><th>DÉPARTEMENT</th><th>TAUX HORAIRE</th></tr></thead><tbody>';
foreach ($enseignants as $e) {
    $html .= '<tr>';
    $html .= '<td>'.$e['id'].'</td>';
    $html .= '<td>'.htmlspecialchars($e['nom']).'</td>';
    $html .= '<td>'.htmlspecialchars($e['prenom']).'</td>';
    $html .= '<td>'.htmlspecialchars($e['grade_nom']).'</td>';
    $html .= '<td>'.htmlspecialchars($e['statut_nom']).'</td>';
    $html .= '<td>'.htmlspecialchars($e['departement_nom']).'</td>';
    $html .= '<td>'.number_format($e['taux_horaire'], 0).' FCFA</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("enseignants.pdf", ['Attachment' => true]);
