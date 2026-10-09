<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $text = $_POST['text'];
    $pub_date = $_POST['pub_date'];
    $exp_date = $_POST['exp_date'];
    $today = date('Y-m-d');

    if (empty($id) && strtotime($pub_date) < strtotime($today)) {
        header("Location: form_novedad.php?error=past_date");
        exit();
    }

    if (strtotime($exp_date) <= strtotime($pub_date)) {
        header("Location: form_novedad.php?id=$id&error=invalid_exp");
        exit();
    }

    if (empty($id)) {
        $stmt = $conexion->prepare("INSERT INTO NOVEDADES (textoNovedad, fechaPublicacionNovedad, fechaExpiracionNovedad) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $text, $pub_date, $exp_date);
    } else {
        $stmt = $conexion->prepare("UPDATE NOVEDADES SET textoNovedad = ?, fechaPublicacionNovedad = ?, fechaExpiracionNovedad = ? WHERE codNovedad = ?");
        $stmt->bind_param("sssi", $text, $pub_date, $exp_date, $id);
    }
    
    $stmt->execute();
}
header("Location: abm_novedades.php");
exit();
?>