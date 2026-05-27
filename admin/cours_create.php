<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Créer un cours";
include '../inc/admin_header.php';
include '../secretaire/cours_create.php';
include '../inc/admin_footer.php';
?>
