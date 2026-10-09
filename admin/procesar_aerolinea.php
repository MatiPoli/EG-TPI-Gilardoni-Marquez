<?php
require '../includes/sesiones.php';
checkRole('administrador');
require '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $iata = strtoupper($_POST['iata']);
    $desc = $_POST['desc'];
    $country = strtoupper($_POST['country']);

    if (empty($id)) {
        $stmt = $conexion->prepare("INSERT INTO AEROLINEAS (nombreAerolinea, codigoIATA, descripcionAerolinea, codPais) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $iata, $desc, $country);
    } else {
        $stmt = $conexion->prepare("UPDATE AEROLINEAS SET nombreAerolinea = ?, codigoIATA = ?, descripcionAerolinea = ?, codPais = ? WHERE codAerolinea = ?");
        $stmt->bind_param("ssssi", $name, $iata, $desc, $country, $id);
    }
    
    $stmt->execute();
}

header("Location: abm_aerolineas.php");
exit();

?>