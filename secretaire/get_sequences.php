<?php
require_once '../inc/config.php';
header('Content-Type: application/json');
$cours_id = (int)$_GET['cours_id'];
if ($cours_id <= 0) {
    echo json_encode([]);
    exit;
}
$stmt = $pdo->prepare("SELECT id, numero_sequence, titre FROM sequences WHERE cours_id = ? ORDER BY numero_sequence");
$stmt->execute([$cours_id]);
$sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($sequences);
