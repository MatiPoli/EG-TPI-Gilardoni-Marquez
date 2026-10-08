<?php
session_start();

function checkRole($allowedRole) {
    if (!isset($_SESSION['codUsuario']) || $_SESSION['tipoUsuario'] !== $allowedRole) {
        header("Location: /tpi/index.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['codUsuario']);
}
?>