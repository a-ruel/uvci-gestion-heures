<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$no_layout = true;
$page_title = "charges_contractuelles";
include '../inc/admin_header.php';
include '../secretaire/charges_contractuelles.php';
include '../inc/admin_footer.php';
?>
