<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$logs = $pdo->query("SELECT l.*, u.nom_utilisateur as user_nom 
                     FROM logs l 
                     LEFT JOIN utilisateurs u ON l.utilisateur_id = u.id 
                     ORDER BY l.date_creation DESC")->fetchAll();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Date');
$sheet->setCellValue('B1', 'Utilisateur');
$sheet->setCellValue('C1', 'Action');
$sheet->setCellValue('D1', 'Détails');
$sheet->setCellValue('E1', 'IP');
$row = 2;
foreach ($logs as $log) {
    $sheet->setCellValue('A'.$row, $log['date_creation']);
    $sheet->setCellValue('B'.$row, ($log['user_nom'] ?? 'Système') . ' (ID ' . $log['utilisateur_id'] . ')');
    $sheet->setCellValue('C'.$row, $log['action']);
    $sheet->setCellValue('D'.$row, $log['details']);
    $sheet->setCellValue('E'.$row, $log['ip']);
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="logs.xlsx"');
$writer->save('php://output');
