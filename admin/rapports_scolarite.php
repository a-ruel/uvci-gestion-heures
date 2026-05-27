<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$no_layout = true;
$page_title = "rapports_scolarite";
include '../inc/admin_header.php';
include '../secretaire/rapports.php';
include '../inc/admin_footer.php';
?>
