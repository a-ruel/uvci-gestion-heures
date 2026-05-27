<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Saisie d'une activité";
include '../inc/admin_header.php';
include '../secretaire/activites_saisie.php';
include '../inc/admin_footer.php';
?>
