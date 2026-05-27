<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
// Rediriger vers la suppression dans le fichier secrétaire (si le lien vient de la liste)
if (isset($_GET['id'])) {
    header("Location: ../secretaire/ressources.php?delete={$_GET['id']}");
    exit;
}
header("Location: ressources_scolarite.php");
exit;
?>
