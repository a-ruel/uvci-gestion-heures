<?php
require_once 'inc/config.php';

echo "<h2>Diagnostic base de données</h2>";

if ($pdo) {
    echo "Connexion à la base de données réussie.<br>";
} else {
    echo "Échec de la connexion.<br>";
    exit;
}

$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
$count = $stmt->fetchColumn();
echo "Nombre d'utilisateurs : $count<br><br>";

$stmt = $pdo->query("SELECT id, nom_utilisateur, role, actif FROM utilisateurs");
$users = $stmt->fetchAll();
echo "<table border='1'><tr><th>ID</th><th>Nom</th><th>Rôle</th><th>Actif</th></tr>";
foreach ($users as $u) {
    echo "<tr><td>{$u['id']}</td><td>{$u['nom_utilisateur']}</td><td>{$u['role']}</td><td>".($u['actif']?'Oui':'Non')."</td></tr>";
}
echo "</table>";

$stmt = $pdo->prepare("SELECT mot_de_passe_hash FROM utilisateurs WHERE nom_utilisateur = 'admin'");
$stmt->execute();
$hash = $stmt->fetchColumn();
if ($hash) {
    echo "<br>Hash admin : $hash<br>";
    $test = password_verify('admin123', $hash);
    echo "Vérification 'admin123' : " . ($test ? 'VALIDE' : 'INVALIDE') . "<br>";
} else {
    echo "<br>L'utilisateur admin n'existe pas.";
}
?>
