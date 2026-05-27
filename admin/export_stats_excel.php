<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Heures par département');
$sheet->setCellValue('A1', 'Département');
$sheet->setCellValue('B1', 'Heures');
$departements = $pdo->query("SELECT d.nom, COALESCE(SUM(ae.heures_calculees), 0) as total
                             FROM departements d
                             LEFT JOIN enseignants e ON e.departement_id = d.id
                             LEFT JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                             GROUP BY d.id ORDER BY total DESC")->fetchAll();
$row = 2;
foreach ($departements as $d) {
    $sheet->setCellValue('A'.$row, $d['nom']);
    $sheet->setCellValue('B'.$row, $d['total']);
    $row++;
}
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Top 5 enseignants');
$sheet2->setCellValue('A1', 'Enseignant');
$sheet2->setCellValue('B1', 'Heures');
$top = $pdo->query("SELECT CONCAT(e.nom, ' ', e.prenom) as nom, SUM(ae.heures_calculees) as total
                    FROM enseignants e JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                    GROUP BY e.id ORDER BY total DESC LIMIT 5")->fetchAll();
$row = 2;
foreach ($top as $t) {
    $sheet2->setCellValue('A'.$row, $t['nom']);
    $sheet2->setCellValue('B'.$row, $t['total']);
    $row++;
}
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="statistiques.xlsx"');
$writer->save('php://output');
