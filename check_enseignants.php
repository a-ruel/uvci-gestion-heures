<?php
require_once 'inc/config.php';
$stmt = $pdo->query("SELECT COUNT(*) FROM enseignants");
$count = $stmt->fetchColumn();
echo "Nombre d'enseignants dans la base : $count<br>";
if ($count == 0) {
    echo "La table enseignants est vide. Vous devez ajouter des enseignants.<br>";
    echo "<a href='secretaire/enseignants_create.php'>Ajouter un enseignant</a>";
} else {
    echo "Liste des enseignants :<ul>";
    $stmt = $pdo->query("SELECT id, nom, prenom, email FROM enseignants");
    foreach ($stmt as $e) {
        echo "<li>ID {$e['id']} : {$e['nom']} {$e['prenom']} - {$e['email']}</li>";
    }
    echo "</ul>";
}
?>
