<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: solicitudes.php');
    exit();
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$codAerolinea = filter_input(INPUT_POST, 'codAerolinea', FILTER_VALIDATE_INT);
$accion = filter_input(INPUT_POST, 'accion', FILTER_UNSAFE_RAW);

if (!$id || $id < 1 || !is_string($accion) || !in_array($accion, ['aprobar', 'rechazar'], true)) {
    header('Location: solicitudes.php');
    exit();
}

if ($accion === 'aprobar' && (!$codAerolinea || $codAerolinea < 1)) {
    header('Location: solicitudes.php?resultado=error');
    exit();
}

$conexion->begin_transaction();
$stmtUsuario = $conexion->prepare(
    "SELECT codUsuario
     FROM USUARIOS
     WHERE codUsuario = ? AND tipoUsuario = 'ceo' AND estadoUsuario = 'pendiente'
     FOR UPDATE"
);
if (!$stmtUsuario) {
    $conexion->rollback();
    die('No se pudo preparar la consulta de la solicitud.');
}
$stmtUsuario->bind_param('i', $id);
if (!$stmtUsuario->execute() || !$stmtUsuario->get_result()->fetch_assoc()) {
    $conexion->rollback();
    die('La solicitud ya fue resuelta o no existe.');
}

if ($accion === 'aprobar') {
    $stmtAerolinea = $conexion->prepare('SELECT codAerolinea FROM AEROLINEAS WHERE codAerolinea = ?');
    if (!$stmtAerolinea) {
        $conexion->rollback();
        die('No se pudo preparar la validación de la aerolínea.');
    }
    $stmtAerolinea->bind_param('i', $codAerolinea);
    if (!$stmtAerolinea->execute() || !$stmtAerolinea->get_result()->fetch_assoc()) {
        $conexion->rollback();
        die('La aerolínea seleccionada no existe.');
    }

    $stmtActualizar = $conexion->prepare(
        "UPDATE USUARIOS
         SET estadoUsuario = 'activo', codAerolinea = ?
         WHERE codUsuario = ? AND estadoUsuario = 'pendiente'"
    );
    if (!$stmtActualizar) {
        $conexion->rollback();
        die('No se pudo preparar la aprobación de la solicitud CEO.');
    }
    $stmtActualizar->bind_param('ii', $codAerolinea, $id);
} else {
    $stmtActualizar = $conexion->prepare(
        "UPDATE USUARIOS
         SET estadoUsuario = 'suspendido', codAerolinea = NULL
         WHERE codUsuario = ? AND estadoUsuario = 'pendiente'"
    );
    if (!$stmtActualizar) {
        $conexion->rollback();
        die('No se pudo preparar el rechazo de la solicitud CEO.');
    }
    $stmtActualizar->bind_param('i', $id);
}
if (!$stmtActualizar->execute()) {
    $conexion->rollback();
    die('No se pudo resolver la solicitud CEO.');
}

if (!$conexion->commit()) {
    $conexion->rollback();
    die('No se pudo confirmar la resolución de la solicitud.');
}

header('Location: solicitudes.php?resultado=' . ($accion === 'aprobar' ? 'aprobada' : 'rechazada'));
exit();
