<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conexion->prepare("DELETE FROM AEROLINEAS WHERE codAerolinea = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: abm_aerolineas.php");
exit();
?>