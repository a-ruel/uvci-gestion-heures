<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Gestion des enseignants";
include '../inc/admin_header.php';
include '../secretaire/enseignants_delete.php';
include '../inc/admin_footer.php';
?>
