<?php
require_once 'inc/config.php';
$stmt = $pdo->query("SELECT id, nom, prenom FROM enseignants");
echo "<pre>";
print_r($stmt->fetchAll());
echo "</pre>";
