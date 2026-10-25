<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id && $id > 0) {
    $stmt = $conexion->prepare('DELETE FROM NOVEDADES WHERE codNovedad = ?');
    if (!$stmt) {
        die('No se pudo preparar la eliminación de la novedad.');
    }
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        die('No se pudo eliminar la novedad.');
    }
}

header('Location: abm_novedades.php');
exit();