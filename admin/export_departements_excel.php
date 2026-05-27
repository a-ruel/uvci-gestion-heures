<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$departements = $pdo->query("SELECT * FROM departements ORDER BY nom")->fetchAll();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Nom');
$sheet->setCellValue('C1', 'Description');
$row = 2;
foreach ($departements as $d) {
    $sheet->setCellValue('A'.$row, $d['id']);
    $sheet->setCellValue('B'.$row, $d['nom']);
    $sheet->setCellValue('C'.$row, $d['description']);
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="departements.xlsx"');
$writer->save('php://output');
