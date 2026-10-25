<?php
require '../../includes/sesiones.php';
checkRole('ceo');
require '../../includes/conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conexion->prepare("DELETE FROM VUELOS WHERE codVuelo = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: abm_vuelos.php");
exit();
?>