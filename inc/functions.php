<?php
function formaterHeures($heures) {
    return number_format($heures, 2, ',', ' ') . ' h';
}

function getDepartements($pdo) {
    $stmt = $pdo->query("SELECT id, nom FROM departements ORDER BY nom");
    return $stmt->fetchAll();
}

function getGrades($pdo) {
    $stmt = $pdo->query("SELECT id, nom FROM grades ORDER BY nom");
    return $stmt->fetchAll();
}

function getStatuts($pdo) {
    $stmt = $pdo->query("SELECT id, nom FROM statuts ORDER BY nom");
    return $stmt->fetchAll();
}

function getNiveaux($pdo) {
    $stmt = $pdo->query("SELECT id, code, libelle FROM niveaux_etudes ORDER BY code");
    return $stmt->fetchAll();
}

function getAnneesAcademiques($pdo) {
    $stmt = $pdo->query("SELECT id, libelle FROM annees_academiques ORDER BY date_debut DESC");
    return $stmt->fetchAll();
}
?>
