<?php
require_once __DIR__ . '/config.php';

function estConnecte() {
    return isset($_SESSION['user_id']);
}

function obtenirRole() {
    return $_SESSION['role'] ?? null;
}

function redirigerSelonRole() {
    if (!estConnecte()) {
        header('Location: login.php');
        exit;
    }
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($role === 'secretaire') {
        header('Location: secretaire/dashboard.php');
    } elseif ($role === 'enseignant') {
        header('Location: enseignant/dashboard.php');
    } else {
        header('Location: login.php');
    }
    exit;
}

function verifierAccesPage($rolesAutorises) {
    if (!estConnecte()) {
        header('Location: login.php');
        exit;
    }
    if (!in_array($_SESSION['role'], $rolesAutorises)) {
        die("Accès interdit.");
    }
}
?>
