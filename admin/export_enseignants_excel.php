<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'CODE');
$sheet->setCellValue('B1', 'NOM');
$sheet->setCellValue('C1', 'PRÉNOM');
$sheet->setCellValue('D1', 'EMAIL');
$sheet->setCellValue('E1', 'GRADE');
$sheet->setCellValue('F1', 'STATUT');
$sheet->setCellValue('G1', 'DÉPARTEMENT');
$sheet->setCellValue('H1', 'TAUX HORAIRE (FCFA)');
$row = 2;
foreach ($enseignants as $e) {
    $sheet->setCellValue('A'.$row, $e['id']);
    $sheet->setCellValue('B'.$row, $e['nom']);
    $sheet->setCellValue('C'.$row, $e['prenom']);
    $sheet->setCellValue('D'.$row, $e['email']);
    $sheet->setCellValue('E'.$row, $e['grade_nom']);
    $sheet->setCellValue('F'.$row, $e['statut_nom']);
    $sheet->setCellValue('G'.$row, $e['departement_nom']);
    $sheet->setCellValue('H'.$row, $e['taux_horaire']);
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="enseignants.xlsx"');
$writer->save('php://output');
