<?php
require '../includes/sesiones.php';
checkRole('usuario');
require '../includes/conexion.php';

$user_id = $_SESSION['codUsuario'];
$idReserva = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idReserva <= 0) {
    header("Location: /tpi/pasajero/reservas.php");
    exit();
}

$sql = "UPDATE RESERVAS 
        SET estadoReserva = 'confirmada' 
        WHERE codReserva = ? AND codUsuario = ? AND estadoReserva = 'pendiente de pago'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $idReserva, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header("Location: /tpi/pasajero/reservas.php?pago_exitoso=1");
} else {
    header("Location: /tpi/pasajero/reservas.php?error_pago=1");
}
exit();