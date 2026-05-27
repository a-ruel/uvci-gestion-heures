<?php
require_once '../inc/config.php';
header('Content-Type: application/json');
$enseignant_id = (int)$_GET['enseignant_id'];
if (!$enseignant_id) exit(json_encode([]));
$stmt = $pdo->prepare("SELECT a.date_realisation, s.titre, a.heures_calculees, c.intitule as cours
                       FROM activites_enseignants a
                       JOIN sequences s ON a.sequence_id = s.id
                       JOIN cours c ON s.cours_id = c.id
                       WHERE a.enseignant_id = ? AND a.valide = 1
                       ORDER BY a.date_realisation");
$stmt->execute([$enseignant_id]);
$activites = $stmt->fetchAll();
$events = [];
foreach ($activites as $a) {
    $events[] = [
        'title' => $a['cours'] . ' - ' . $a['titre'] . ' (' . number_format($a['heures_calculees'], 2) . ' h)',
        'start' => $a['date_realisation'],
        'allDay' => true
    ];
}
echo json_encode($events);
?>
