<?php 
require 'includes/sesiones.php';
require 'includes/conexion.php'; 
require 'includes/header.php'; 
?>

<div class="container mt-4 mb-5">

    <div id="carruselInicio" class="carousel slide mb-4 shadow-sm rounded overflow-hidden position-relative" data-bs-ride="carousel">
        
        <div class="carrousel-text position-absolute top-50 start-50 translate-middle text-center p-4 bg-white bg-opacity-75 rounded shadow">
            <h1 class="display-4 fw-bold mb-2">
                Bienvenido a 
                <span style="color: #060b26;">Viaj</span><span style="color: #ff6600;">A</span><span style="color: #060b26;">ir</span>
            </h1>
            <p class="lead mb-0 text-dark fw-semibold">Encuentra y reserva tus vuelos al mejor precio.</p>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="/tpi/assets/img/slide1.jpg" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="Avión volando">
            </div>
            <div class="carousel-item">
                <img src="/tpi/assets/img/slide2.jpg" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="Pasajeros en la terminal del aeropuerto">
            </div>
            <div class="carousel-item">
                <img src="/tpi/assets/img/slide3.jpg" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="Vista del cielo desde la ventanilla de un avión">
            </div>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#carruselInicio" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carruselInicio" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>
    
    <div class="card shadow-sm bg-secondary border-0 text-center mb-5 px-3 py-3">
        <p class="fs-5 mb-0">
            Somos la plataforma líder en comercialización y gestión de pasajes de avión de Rosario. 
            Con <strong class="fw-bold">ViajAir</strong> podés buscar destinos, aprovechar promociones exclusivas y gestionar tus reservas de forma rápida, accesible y segura.
        </p>
    </div>

    <div class="card shadow-sm mx-auto border-0 bg-secondary" style="max-width: 900px;">
        <div class="card-body p-4">
            <h2 class="h4 card-title mb-4 text-center text-primary fw-bold">Búsqueda rápida</h2>
            <form action="/tpi/public/buscar_vuelos.php" method="GET" class="row g-3">
                
                <div class="col-md-3">
                    <label for="origen" class="visually-hidden">Origen</label>
                    <input type="text" id="origen" name="origen" class="form-control" placeholder="Ciudad de Origen" required>
                </div>
                
                <div class="col-md-3">
                    <label for="destino" class="visually-hidden">Destino</label>
                    <input type="text" id="destino" name="destino" class="form-control" placeholder="Ciudad de Destino" required>
                </div>
                
                <div class="col-md-3">
                    <label for="fecha" class="visually-hidden">Fecha del vuelo</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" aria-label="Fecha del vuelo" required>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Buscar Vuelos</button>
                </div>
                
            </form>
        </div>
    </div>

</div>

<?php require 'includes/footer.php'; ?>