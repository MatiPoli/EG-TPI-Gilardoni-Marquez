<?php 
require 'includes/sesiones.php';
require 'includes/conexion.php'; 
require 'includes/header.php'; 
?>

<div class="p-5 mb-4 bg-white rounded-3 shadow-sm border">
    <div class="container-fluid py-5 text-center">
        <h1 class="display-5 fw-bold">Bienvenido a ViajAir</h1>
        <p class="col-md-8 mx-auto fs-4">Encuentra y reserva tus vuelos al mejor precio.</p>
    </div>
</div>

<div class="card shadow-sm mx-auto mb-5" style="max-width: 800px;">
    <div class="card-body p-4">
        <h4 class="card-title mb-4">Búsqueda rápida</h4>
        <form action="/tpi/public/buscar_vuelos.php" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="origen" class="form-control" placeholder="Origen" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="destino" class="form-control" placeholder="Destino" required>
            </div>
            <div class="col-md-4">
                <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="col-12 text-end mt-4">
                <button type="submit" class="btn btn-primary px-5">Buscar</button>
            </div>
        </form>
    </div>
</div>

<?php require 'includes/footer.php'; ?>