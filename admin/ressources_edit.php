<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Modifier une ressource";
include '../inc/admin_header.php';
include '../secretaire/ressources_edit.php';
include '../inc/admin_footer.php';
?>
