<?php
require '../../includes/sesiones.php';
checkRole('ceo');
require '../../includes/conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$codAerolinea = (int) $_SESSION['codAerolinea'];

if ($id && $id > 0) {
    $stmt = $conexion->prepare(
        'DELETE FROM VUELOS WHERE codVuelo = ? AND codAerolinea = ?'
    );
    if (!$stmt) {
        die('No se pudo preparar la eliminación del vuelo.');
    }
    $stmt->bind_param('ii', $id, $codAerolinea);
    if (!$stmt->execute()) {
        die('No se pudo eliminar el vuelo.');
    }
}

header('Location: abm_vuelos.php');
exit();
