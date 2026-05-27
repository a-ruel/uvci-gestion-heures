<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$no_layout = true;
$page_title = "activites_validation_scolarite";
include '../inc/admin_header.php';
include '../secretaire/activites_validation.php';
include '../inc/admin_footer.php';
?>
