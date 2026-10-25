<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /tpi/admin/promociones/auditoria.php');
    exit();
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$accion = filter_input(INPUT_POST, 'accion', FILTER_UNSAFE_RAW);

if (!$id || $id < 1 || !is_string($accion) || !in_array($accion, ['aprobar', 'denegar'], true)) {
    header('Location: /tpi/admin/promociones/auditoria.php');
    exit();
}

$conexion->begin_transaction();
$stmtPromocion = $conexion->prepare(
    "SELECT codAerolinea
     FROM PROMOCIONES
     WHERE codPromocion = ? AND estadoPromocion = 'pendiente'
     FOR UPDATE"
);
if (!$stmtPromocion) {
    $conexion->rollback();
    die('No se pudo preparar la consulta de la promoción.');
}
$stmtPromocion->bind_param('i', $id);
if (!$stmtPromocion->execute()) {
    $conexion->rollback();
    die('No se pudo consultar la promoción.');
}
$promocion = $stmtPromocion->get_result()->fetch_assoc();

if (!$promocion) {
    $conexion->rollback();
    die('La promoción ya fue resuelta o no existe.');
}

$codAerolinea = (int) $promocion['codAerolinea'];
$nuevoEstado = $accion === 'aprobar' ? 'aprobada' : 'denegada';

if ($accion === 'aprobar') {
    $stmtRevocar = $conexion->prepare(
        "UPDATE PROMOCIONES
         SET estadoPromocion = 'denegada'
         WHERE codAerolinea = ? AND estadoPromocion = 'aprobada'"
    );
    if (!$stmtRevocar) {
        $conexion->rollback();
        die('No se pudo preparar la actualización de promociones anteriores.');
    }
    $stmtRevocar->bind_param('i', $codAerolinea);
    if (!$stmtRevocar->execute()) {
        $conexion->rollback();
        die('No se pudieron actualizar las promociones anteriores.');
    }
}

$stmtResolver = $conexion->prepare(
    "UPDATE PROMOCIONES
     SET estadoPromocion = ?
     WHERE codPromocion = ? AND estadoPromocion = 'pendiente'"
);
if (!$stmtResolver) {
    $conexion->rollback();
    die('No se pudo preparar la resolución de la promoción.');
}
$stmtResolver->bind_param('si', $nuevoEstado, $id);
if (!$stmtResolver->execute()) {
    $conexion->rollback();
    die('No se pudo resolver la promoción.');
}

if (!$conexion->commit()) {
    $conexion->rollback();
    die('No se pudo confirmar la resolución de la promoción.');
}

$resultado = $accion === 'aprobar' ? 'aprobada' : 'denegada';
header('Location: /tpi/admin/promociones/auditoria.php?resultado=' . $resultado);
exit();
