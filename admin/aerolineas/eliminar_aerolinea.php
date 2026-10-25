<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id && $id > 0) {
    $stmt = $conexion->prepare('DELETE FROM AEROLINEAS WHERE codAerolinea = ?');
    if (!$stmt) {
        die('No se pudo preparar la eliminación de la aerolínea.');
    }
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        die('No se pudo eliminar la aerolínea.');
    }
}

header('Location: abm_aerolineas.php');
exit();