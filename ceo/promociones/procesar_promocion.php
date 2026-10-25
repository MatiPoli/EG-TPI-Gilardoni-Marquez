<?php
require '../../includes/sesiones.php';
checkRole('ceo');
require '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: abm_promociones.php');
    exit();
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$id = ($id && $id > 0) ? $id : 0;
$descripcion = isset($_POST['descripcionPromocion']) && is_string($_POST['descripcionPromocion'])
    ? trim($_POST['descripcionPromocion']) : '';
$descuento = isset($_POST['descuentoPromocion']) && is_string($_POST['descuentoPromocion'])
    ? trim(str_replace(',', '.', $_POST['descuentoPromocion'])) : '';
$codAerolinea = (int) $_SESSION['codAerolinea'];

if ($descripcion === '' || mb_strlen($descripcion, 'UTF-8') > 200
    || $descuento === '' || !is_numeric($descuento)
    || (float) $descuento < 1 || (float) $descuento > 100) {
    header('Location: form_promocion.php' . ($id ? '?id=' . $id : ''));
    exit();
}

if ($id > 0) {
    $stmtEstado = $conexion->prepare(
        'SELECT estadoPromocion FROM PROMOCIONES WHERE codPromocion = ? AND codAerolinea = ?'
    );
    if (!$stmtEstado) {
        die('No se pudo verificar el estado de la promoción.');
    }
    $stmtEstado->bind_param('ii', $id, $codAerolinea);
    if (!$stmtEstado->execute()) {
        die('No se pudo verificar el estado de la promoción.');
    }
    $promocion = $stmtEstado->get_result()->fetch_assoc();
    if (!$promocion || $promocion['estadoPromocion'] !== 'pendiente') {
        header('Location: abm_promociones.php');
        exit();
    }
}

if ($id === 0) {
    $stmt = $conexion->prepare(
        "INSERT INTO PROMOCIONES (descripcionPromocion, descuentoPromocion, codAerolinea, estadoPromocion)
         VALUES (?, ?, ?, 'pendiente')"
    );
    if (!$stmt) {
        die('No se pudo preparar el alta de la promoción.');
    }
    $stmt->bind_param('sdi', $descripcion, $descuento, $codAerolinea);
} else {
    $stmt = $conexion->prepare(
        "UPDATE PROMOCIONES
         SET descripcionPromocion = ?, descuentoPromocion = ?, estadoPromocion = 'pendiente'
         WHERE codPromocion = ? AND codAerolinea = ? AND estadoPromocion = 'pendiente'"
    );
    if (!$stmt) {
        die('No se pudo preparar la modificación de la promoción.');
    }
    $stmt->bind_param('sdii', $descripcion, $descuento, $id, $codAerolinea);
}

if (!$stmt->execute()) {
    die('No se pudo guardar la promoción.');
}

header('Location: abm_promociones.php');
exit();
