<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$grades = $pdo->query("SELECT * FROM grades ORDER BY id")->fetchAll();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Nom');
$row = 2;
foreach ($grades as $g) {
    $sheet->setCellValue('A'.$row, $g['id']);
    $sheet->setCellValue('B'.$row, $g['nom']);
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="grades.xlsx"');
$writer->save('php://output');
