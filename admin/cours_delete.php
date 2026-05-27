<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
// On redirige vers la suppression dans le fichier secrétaire (si le lien vient de la liste)
if (isset($_GET['id'])) {
    header("Location: ../secretaire/cours.php?delete={$_GET['id']}");
    exit;
}
header("Location: cours_scolarite.php");
exit;
?>
