<?php
require_once 'inc/config.php';

$username = 'admin';
$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe_hash = ? WHERE nom_utilisateur = ?");
if ($stmt->execute([$hash, $username])) {
    echo "Mot de passe de l'utilisateur admin mis à jour avec succès.<br>";
    echo "Nouveau hash : $hash<br>";
    echo "Vous pouvez maintenant vous connecter avec identifiant <strong>admin</strong> et mot de passe <strong>admin123</strong>.";
} else {
    echo "Erreur lors de la mise à jour.";
}
?>
