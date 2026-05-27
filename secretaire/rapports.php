<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php';
require_once '../vendor/autoload.php';

$type_rapport = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'html';

function getDonnees($pdo, $type) {
    switch ($type) {
        case 'etat_global':
            return $pdo->query("SELECT CONCAT(e.nom, ' ', e.prenom) as enseignant, SUM(ae.heures_calculees) as total_heures
                                FROM enseignants e
                                LEFT JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                                GROUP BY e.id ORDER BY total_heures DESC")->fetchAll();
        case 'paiements':
            return $pdo->query("SELECT CONCAT(e.nom, ' ', e.prenom) as enseignant, e.taux_horaire,
                                       SUM(ae.heures_calculees) as total_heures,
                                       SUM(ae.heures_calculees) * e.taux_horaire as montant
                                FROM enseignants e
                                LEFT JOIN activites_enseignants ae ON e.id = ae.enseignant_id AND ae.valide = 1
                                GROUP BY e.id ORDER BY e.nom")->fetchAll();
        case 'statistiques':
            return $pdo->query("SELECT t.nom as type_activite, COUNT(*) as nb, SUM(ae.heures_calculees) as total_heures
                                FROM activites_enseignants ae
                                JOIN types_activite t ON ae.type_activite_id = t.id
                                WHERE ae.valide = 1
                                GROUP BY t.id")->fetchAll();
        default:
            return [];
    }
}

if ($type_rapport && $format !== 'html') {
    $donnees = getDonnees($pdo, $type_rapport);
    if ($format === 'pdf') {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Rapport</title>';
        $html .= '<style>body{font-family: DejaVu Sans, sans-serif;} table{width:100%; border-collapse:collapse;} th,td{border:1px solid #ddd; padding:8px;} th{background:#f2f2f2;}</style>';
        $html .= '</head><body>';
        $html .= '<h1>Rapport ' . htmlspecialchars($type_rapport) . '</h1>';
        $html .= '<tr><thead>';
        if ($type_rapport == 'etat_global') {
            $html .= '<th>Enseignant</th><th>Total heures validées</th>';
        } elseif ($type_rapport == 'paiements') {
            $html .= '<th>Enseignant</th><th>Taux horaire (FCFA/h)</th><th>Heures</th><th>Montant (FCFA)</th>';
        } elseif ($type_rapport == 'statistiques') {
            $html .= '<th>Type activité</th><th>Nombre</th><th>Heures totales</th>';
        }
        $html .= '</thead><tbody>';
        foreach ($donnees as $row) {
            $html .= '<tr>';
            if ($type_rapport == 'etat_global') {
                $html .= '<td>' . htmlspecialchars($row['enseignant']) . '</td>';
                $html .= '<td>' . number_format($row['total_heures'], 2) . ' h</td>';
            } elseif ($type_rapport == 'paiements') {
                $html .= '<td>' . htmlspecialchars($row['enseignant']) . '</td>';
                $html .= '<td>' . number_format($row['taux_horaire'], 0) . '</td>';
                $html .= '<td>' . number_format($row['total_heures'], 2) . ' h</td>';
                $html .= '<td>' . number_format($row['montant'], 0) . ' FCFA</td>';
            } elseif ($type_rapport == 'statistiques') {
                $html .= '<td>' . htmlspecialchars($row['type_activite']) . '</td>';
                $html .= '<td>' . $row['nb'] . '</td>';
                $html .= '<td>' . number_format($row['total_heures'], 2) . ' h</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';
        if (ob_get_level()) ob_end_clean();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("rapport_{$type_rapport}.pdf", ['Attachment' => true]);
        exit;
    } elseif ($format === 'excel') {
        if (ob_get_level()) ob_end_clean();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        if ($type_rapport == 'etat_global') {
            $sheet->setCellValue('A1', 'Enseignant');
            $sheet->setCellValue('B1', 'Total heures validées');
            $row = 2;
            foreach ($donnees as $d) {
                $sheet->setCellValue('A'.$row, $d['enseignant']);
                $sheet->setCellValue('B'.$row, $d['total_heures']);
                $row++;
            }
        } elseif ($type_rapport == 'paiements') {
            $sheet->setCellValue('A1', 'Enseignant');
            $sheet->setCellValue('B1', 'Taux horaire (FCFA/h)');
            $sheet->setCellValue('C1', 'Heures');
            $sheet->setCellValue('D1', 'Montant (FCFA)');
            $row = 2;
            foreach ($donnees as $d) {
                $sheet->setCellValue('A'.$row, $d['enseignant']);
                $sheet->setCellValue('B'.$row, $d['taux_horaire']);
                $sheet->setCellValue('C'.$row, $d['total_heures']);
                $sheet->setCellValue('D'.$row, $d['montant']);
                $row++;
            }
        } elseif ($type_rapport == 'statistiques') {
            $sheet->setCellValue('A1', 'Type activité');
            $sheet->setCellValue('B1', 'Nombre');
            $sheet->setCellValue('C1', 'Heures totales');
            $row = 2;
            foreach ($donnees as $d) {
                $sheet->setCellValue('A'.$row, $d['type_activite']);
                $sheet->setCellValue('B'.$row, $d['nb']);
                $sheet->setCellValue('C'.$row, $d['total_heures']);
                $row++;
            }
        }
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="rapport_'.$type_rapport.'.xlsx"');
        $writer->save('php://output');
        exit;
    }
}

// Affichage HTML
include '../inc/secretaire_header.php';
?>
<div class="page-header">
    <h1>Génération de rapports</h1>
</div>
<form method="get" class="admin-form" style="max-width: 500px;">
    <div class="admin-form-group">
        <label>Type de rapport</label>
        <select name="type" required>
            <option value="etat_global">État global des heures</option>
            <option value="paiements">État des paiements</option>
            <option value="statistiques">Statistiques pédagogiques</option>
        </select>
    </div>
    <div class="admin-form-group">
        <label>Format</label>
        <select name="format" required>
            <option value="html">HTML (affichage)</option>
            <option value="pdf">PDF</option>
            <option value="excel">Excel</option>
        </select>
    </div>
    <button type="submit" class="button">Générer</button>
</form>
<?php if ($type_rapport && $format == 'html'): ?>
    <h2>Rapport : <?= htmlspecialchars($type_rapport) ?></h2>
    <table class="admin-table">
        <thead>
        <tr>
            <?php if ($type_rapport == 'etat_global'): ?>
                <th>Enseignant</th><th>Total heures validées</th>
            <?php elseif ($type_rapport == 'paiements'): ?>
                <th>Enseignant</th><th>Taux horaire (FCFA/h)</th><th>Heures</th><th>Montant (FCFA)</th>
            <?php elseif ($type_rapport == 'statistiques'): ?>
                <th>Type activité</th><th>Nombre</th><th>Heures totales</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php $donnees = getDonnees($pdo, $type_rapport); ?>
        <?php foreach ($donnees as $row): ?>
            <tr>
                <?php if ($type_rapport == 'etat_global'): ?>
                    <td><?= htmlspecialchars($row['enseignant']) ?></td>
                    <td><?= number_format($row['total_heures'], 2) ?> h</td>
                <?php elseif ($type_rapport == 'paiements'): ?>
                    <td><?= htmlspecialchars($row['enseignant']) ?></td>
                    <td><?= number_format($row['taux_horaire'], 0) ?> FCFA/h</td>
                    <td><?= number_format($row['total_heures'], 2) ?> h</td>
                    <td><?= number_format($row['montant'], 0) ?> FCFA</td>
                <?php elseif ($type_rapport == 'statistiques'): ?>
                    <td><?= htmlspecialchars($row['type_activite']) ?></td>
                    <td><?= $row['nb'] ?></td>
                    <td><?= number_format($row['total_heures'], 2) ?> h</td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include '../inc/secretaire_footer.php'; ?>
