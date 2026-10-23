<?php
require '../includes/sesiones.php';
checkRole('usuario');
require '../includes/conexion.php';

$user_id = $_SESSION['codUsuario'];
$idVuelo = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
$cantidad = isset($_POST['cantidad']) && is_numeric($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

if ($idVuelo <= 0 || $cantidad <= 0) {
    header("Location: /tpi/public/buscar_vuelos.php");
    exit();
}

$conexion->begin_transaction();

try {
    $sqlVuelo = "SELECT v.asientosDisponibles, v.precioVuelo, v.fechaSalidaVuelo, p.descuentoPromocion
                 FROM VUELOS v
                 INNER JOIN AEROLINEAS a ON v.codAerolinea = a.codAerolinea
                 LEFT JOIN PROMOCIONES p ON a.codAerolinea = p.codAerolinea AND p.estadoPromocion = 'aprobada'
                 WHERE v.codVuelo = ? AND v.fechaSalidaVuelo >= CURDATE()
                 FOR UPDATE";

    $stmtVuelo = $conexion->prepare($sqlVuelo);
    $stmtVuelo->bind_param("i", $idVuelo);
    $stmtVuelo->execute();
    $resVuelo = $stmtVuelo->get_result();
    $vuelo = $resVuelo->fetch_assoc();

    if (!$vuelo || (int)$vuelo['asientosDisponibles'] < $cantidad) {
        $conexion->rollback();
        header("Location: /tpi/public/detalle_vuelo.php?id=$idVuelo&error_reserva=sin_stock");
        exit();
    }

    $precioUnitario = (float)$vuelo['precioVuelo'];
    $descuento = (float)($vuelo['descuentoPromocion'] ?? 0);
    if ($descuento > 0) {
        $precioUnitario = $precioUnitario * (1 - ($descuento / 100));
    }
    $precioTotal = $precioUnitario * $cantidad;

    $sqlRestar = "UPDATE VUELOS SET asientosDisponibles = asientosDisponibles - ? WHERE codVuelo = ?";
    $stmtRestar = $conexion->prepare($sqlRestar);
    $stmtRestar->bind_param("ii", $cantidad, $idVuelo);
    $stmtRestar->execute();

    $sqlReserva = "INSERT INTO RESERVAS (codUsuario, codVuelo, cantidadPasajes, fechaReserva, precioTotal, estadoReserva) 
                   VALUES (?, ?, ?, CURDATE(), ?, 'pendiente de pago')";
    $stmtReserva = $conexion->prepare($sqlReserva);
    $stmtReserva->bind_param("iiid", $user_id, $idVuelo, $cantidad, $precioTotal);
    $stmtReserva->execute();

    $conexion->commit();
    header("Location: /tpi/pasajero/reservas.php?reserva_creada=1");
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    header("Location: /tpi/public/buscar_vuelos.php?error_reserva=transaccion");
    exit();
}