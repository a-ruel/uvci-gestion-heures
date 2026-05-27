<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Gestion des séquences";
include '../inc/admin_header.php';
// On vérifie que le paramètre cours_id est passé, sinon on affiche un message et on liste les cours
if (isset($_GET['cours_id'])) {
    include '../secretaire/sequences.php';
} else {
    echo "<p>Veuillez sélectionner un cours dans la liste ci-dessous pour gérer ses séquences.</p>";
    include '../secretaire/cours.php';
}
include '../inc/admin_footer.php';
?>
