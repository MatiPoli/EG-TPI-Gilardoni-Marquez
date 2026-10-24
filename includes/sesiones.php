<?php
session_start();

function checkRole($allowedRole) {
    if (!isset($_SESSION['codUsuario'])) {
        header("Location: /tpi/auth/login.php");
        exit();
    }

    if ($allowedRole === 'usuario') {
        return;
    }

    if ($_SESSION['tipoUsuario'] !== $allowedRole) {
        header("Location: /tpi/index.php");
        exit();
    }
}

function checkLoggedIn($redirect = false) {
    if (isLoggedIn() === $redirect) {
        header("Location: /tpi/index.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['codUsuario']);
}

$env = parse_ini_file(__DIR__ . '/../.env');
$app_url = rtrim($env['APP_URL'], '/');
?>