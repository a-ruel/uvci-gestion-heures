<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$no_layout = true;
$page_title = "Gestion des cours";
include '../inc/admin_header.php';
include '../secretaire/cours.php';
include '../inc/admin_footer.php';
?>
