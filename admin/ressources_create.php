<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Créer une ressource";
include '../inc/admin_header.php';
include '../secretaire/ressources_create.php';
include '../inc/admin_footer.php';
?>
