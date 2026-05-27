<?php
require_once 'inc/config.php';
$stmt = $pdo->prepare("INSERT INTO enseignants (nom, prenom, email, grade_id, statut_id, departement_id, taux_horaire) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute(['Dupont', 'Jean', 'jean.dupont@uvci.ci', 1, 1, 1, 5000]);
echo "Enseignant ajouté avec succès. <a href='secretaire/enseignants.php'>Voir la liste</a>";
?>
