<?php
require_once 'inc/auth.php';
if (estConnecte()) {
    redirigerSelonRole();
} else {
    header('Location: login.php');
}
exit;
?>
