<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Validation des activités";
include '../inc/admin_header.php';
// On inclut le fichier du secrétaire, mais on lui passe une variable pour qu'il n'affiche pas de header en trop
$no_layout = true;
include '../secretaire/activites_validation.php';
include '../inc/admin_footer.php';
?>
