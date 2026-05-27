<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Liste des activités";
include '../inc/admin_header.php';
include '../secretaire/activites.php';
include '../inc/admin_footer.php';
?>
