<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$activites = $pdo->query("SELECT a.id, CONCAT(e.nom,' ',e.prenom) as enseignant, t.nom as type, a.niveau_complexite, a.date_realisation, a.heures_calculees, a.valide FROM activites_enseignants a JOIN enseignants e ON a.enseignant_id = e.id JOIN types_activite t ON a.type_activite_id = t.id ORDER BY a.date_realisation DESC")->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Enseignant');
$sheet->setCellValue('C1', 'Type');
$sheet->setCellValue('D1', 'Niveau');
$sheet->setCellValue('E1', 'Date');
$sheet->setCellValue('F1', 'Heures');
$sheet->setCellValue('G1', 'Validée');
$row = 2;
foreach ($activites as $a) {
    $sheet->setCellValue('A'.$row, $a['id']);
    $sheet->setCellValue('B'.$row, $a['enseignant']);
    $sheet->setCellValue('C'.$row, $a['type']);
    $sheet->setCellValue('D'.$row, $a['niveau_complexite']);
    $sheet->setCellValue('E'.$row, $a['date_realisation']);
    $sheet->setCellValue('F'.$row, $a['heures_calculees']);
    $sheet->setCellValue('G'.$row, $a['valide'] ? 'Oui' : 'Non');
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="activites.xlsx"');
$writer->save('php://output');
