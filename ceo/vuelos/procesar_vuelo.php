<?php
require '../../includes/sesiones.php';
checkRole('ceo');
require '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $origen = strtoupper($_POST['origen']);
    $destino = strtoupper($_POST['destino']);
    $fecha = $_POST['fechaSalidaVuelo'];
    $hora = $_POST['horaSalidaVuelo'];
    $precio = str_replace(',', '.', str_replace('.', '', $_POST['precioVuelo']));
    $asientosDisponibles = (int) $_POST['asientosDisponibles'];
    $codAerolinea = (int) $_SESSION['codAerolinea'];

    if ($id === 0) {
        $stmt = $conexion->prepare("INSERT INTO VUELOS (origenVuelo, destinoVuelo, fechaSalidaVuelo, horaSalidaVuelo, precioVuelo, asientosDisponibles, codAerolinea) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssii", $origen, $destino, $fecha, $hora, $precio, $asientosDisponibles, $codAerolinea);
    } else {
        $stmt = $conexion->prepare("UPDATE VUELOS SET origenVuelo = ?, destinoVuelo = ?, fechaSalidaVuelo = ?, horaSalidaVuelo = ?, precioVuelo = ?, asientosDisponibles = ? WHERE codVuelo = ? AND codAerolinea = ?");
        $stmt->bind_param("sssssiii", $origen, $destino, $fecha, $hora, $precio, $asientosDisponibles, $id, $codAerolinea);
    }
    
    $stmt->execute();
}

header("Location: abm_vuelos.php");
exit();

?>