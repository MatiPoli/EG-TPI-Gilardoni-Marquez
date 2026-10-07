<?php 
require 'includes/conexion.php'; 

require 'includes/header.php'; 
?>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="display-4">Bienvenido a ViajAir</h1>
        <p class="lead">Encuentra y reserva tus vuelos al mejor precio.</p>
    </div>

    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h5 class="card-title text-center mb-4">Buscar Vuelos</h5>
            <form action="#" method="GET">
                <div class="mb-3">
                    <input type="text" class="form-control" name="origen" placeholder="Ej: Buenos Aires">
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="destino" placeholder="Ej: Madrid">
                </div>
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </form>
        </div>
    </div>
</div>

<?php 
require 'includes/footer.php'; 
?>