<?php
function ajouterLog($pdo, $action, $details = null) {
    $userId = $_SESSION['user_id'] ?? null;
    $userNom = $_SESSION['nom_utilisateur'] ?? 'Anonyme';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $pdo->prepare("INSERT INTO logs (action, utilisateur_id, utilisateur_nom, details, ip) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$action, $userId, $userNom, $details, $ip]);
}
?>
