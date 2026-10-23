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

$conexion->begin_transaction();

try {
    $sql = "SELECT r.codReserva, r.cantidadPasajes, r.estadoReserva, r.codVuelo,
                   v.fechaSalidaVuelo, v.horaSalidaVuelo,
                   TIMESTAMPDIFF(HOUR, NOW(), CONCAT(v.fechaSalidaVuelo, ' ', v.horaSalidaVuelo)) AS horas_restantes
            FROM RESERVAS r
            INNER JOIN VUELOS v ON r.codVuelo = v.codVuelo
            WHERE r.codReserva = ? AND r.codUsuario = ?
            FOR UPDATE";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $idReserva, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $reserva = $res->fetch_assoc();

    if (!$reserva || $reserva['estadoReserva'] === 'cancelada') {
        $conexion->rollback();
        header("Location: /tpi/pasajero/reservas.php");
        exit();
    }

    if ((int)$reserva['horas_restantes'] < 72) {
        $conexion->rollback();
        header("Location: /tpi/pasajero/reservas.php?error_cancelacion=plazo");
        exit();
    }

    $codVuelo = $reserva['codVuelo'];
    $cantidad = $reserva['cantidadPasajes'];

    $sqlDevolver = "UPDATE VUELOS SET asientosDisponibles = asientosDisponibles + ? WHERE codVuelo = ?";
    $stmtDevolver = $conexion->prepare($sqlDevolver);
    $stmtDevolver->bind_param("ii", $cantidad, $codVuelo);
    $stmtDevolver->execute();

    $sqlCancelar = "UPDATE RESERVAS SET estadoReserva = 'cancelada' WHERE codReserva = ?";
    $stmtCancelar = $conexion->prepare($sqlCancelar);
    $stmtCancelar->bind_param("i", $idReserva);
    $stmtCancelar->execute();

    $conexion->commit();
    header("Location: /tpi/pasajero/reservas.php?cancelada=1");
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    header("Location: /tpi/pasajero/reservas.php?error_cancelacion=transaccion");
    exit();
}