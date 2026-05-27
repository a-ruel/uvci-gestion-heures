<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$niveaux = $pdo->query("SELECT * FROM niveaux_etudes ORDER BY code")->fetchAll();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Code');
$sheet->setCellValue('C1', 'Libellé');
$row = 2;
foreach ($niveaux as $n) {
    $sheet->setCellValue('A'.$row, $n['id']);
    $sheet->setCellValue('B'.$row, $n['code']);
    $sheet->setCellValue('C'.$row, $n['libelle']);
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="niveaux.xlsx"');
$writer->save('php://output');
