<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$stmt = $pdo->query("SELECT g.nom as grade, s.nom as statut, t.taux_horaire
                     FROM taux_horaires t
                     JOIN grades g ON t.grade_id = g.id
                     JOIN statuts s ON t.statut_id = s.id
                     ORDER BY g.nom, s.nom");
$taux = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Grade');
$sheet->setCellValue('B1', 'Statut');
$sheet->setCellValue('C1', 'Taux horaire (FCFA/h)');
$row = 2;
foreach ($taux as $t) {
    $sheet->setCellValue('A'.$row, $t['grade']);
    $sheet->setCellValue('B'.$row, $t['statut']);
    $sheet->setCellValue('C'.$row, $t['taux_horaire']);
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="taux_horaires.xlsx"');
$writer->save('php://output');
