<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$no_layout = true;
$page_title = "ressources_scolarite";
include '../inc/admin_header.php';
include '../secretaire/ressources.php';
include '../inc/admin_footer.php';
?>
