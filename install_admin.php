<?php
require_once 'inc/config.php';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE nom_utilisateur = 'admin'");
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo "L'utilisateur admin existe déjà. Essayez de vous connecter avec admin/admin123.<br>";
    echo "Si la connexion échoue, supprimez cet utilisateur dans phpMyAdmin et relancez ce script.";
} else {
    $username = 'admin';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe_hash, role, actif) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $hash, 'admin', 1])) {
        echo "Utilisateur admin créé avec succès.<br>";
        echo "Identifiant : <strong>admin</strong><br>";
        echo "Mot de passe : <strong>admin123</strong><br>";
        echo "Vous pouvez maintenant vous connecter sur <a href='/login.php'>login.php</a>.";
    } else {
        echo "Erreur lors de la création de l'utilisateur.";
    }
}
?>
