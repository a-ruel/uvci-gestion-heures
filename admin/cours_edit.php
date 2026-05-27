<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Modifier un cours";
include '../inc/admin_header.php';
include '../secretaire/cours_edit.php';
include '../inc/admin_footer.php';
?>
