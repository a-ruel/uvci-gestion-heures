<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
verifierAccesPage(['admin']);
$page_title = "Gestion des séquences";
include '../inc/admin_header.php';
include '../secretaire/sequences_edit.php';
include '../inc/admin_footer.php';
?>
