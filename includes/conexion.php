<?php
$env = parse_ini_file(__DIR__ . '/../.env');

$conexion = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);

if ($conexion->connect_error) {
    die("Error: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>