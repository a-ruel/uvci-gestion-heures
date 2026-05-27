<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$users = $pdo->query("SELECT id, nom_utilisateur, role, actif, dernier_acces FROM utilisateurs ORDER BY id")->fetchAll();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Email');
$sheet->setCellValue('C1', 'Rôle');
$sheet->setCellValue('D1', 'Actif');
$sheet->setCellValue('E1', 'Dernier accès');
$row = 2;
foreach ($users as $u) {
    $sheet->setCellValue('A'.$row, $u['id']);
    $sheet->setCellValue('B'.$row, $u['nom_utilisateur']);
    $sheet->setCellValue('C'.$row, strtoupper($u['role']));
    $sheet->setCellValue('D'.$row, $u['actif'] ? 'Oui' : 'Non');
    $sheet->setCellValue('E'.$row, $u['dernier_acces'] ?: 'Jamais');
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="utilisateurs.xlsx"');
$writer->save('php://output');
