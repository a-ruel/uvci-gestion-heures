<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$statuts = $pdo->query("SELECT * FROM statuts ORDER BY id")->fetchAll();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Nom');
$row = 2;
foreach ($statuts as $s) {
    $sheet->setCellValue('A'.$row, $s['id']);
    $sheet->setCellValue('B'.$row, $s['nom']);
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="statuts.xlsx"');
$writer->save('php://output');
