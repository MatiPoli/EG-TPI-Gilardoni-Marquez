<?php
require '../../includes/sesiones.php';
checkRole('ceo');
require '../../includes/conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$codAerolinea = (int) $_SESSION['codAerolinea'];

if ($id && $id > 0) {
    $stmt = $conexion->prepare(
        'DELETE FROM PROMOCIONES WHERE codPromocion = ? AND codAerolinea = ?'
    );
    if (!$stmt) {
        die('No se pudo preparar la eliminación de la promoción.');
    }
    $stmt->bind_param('ii', $id, $codAerolinea);
    if (!$stmt->execute()) {
        die('No se pudo eliminar la promoción.');
    }
}

header('Location: abm_promociones.php');
exit();
