<?php
require '../includes/sesiones.php';
checkRole('ceo');
require '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 col-lg-2">
        <div class="list-group shadow-sm mb-4">
            <a href="/tpi/ceo/dashboard.php" class="list-group-item list-group-item-action active">Panel General</a>
            <a href="/tpi/ceo/abm_vuelos.php" class="list-group-item list-group-item-action">Gestión de Vuelos</a>
            <a href="/tpi/ceo/abm_promociones.php" class="list-group-item list-group-item-action">Promociones</a>
            <a href="/tpi/ceo/reportes.php" class="list-group-item list-group-item-action">Reportes</a>
        </div>
    </div>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
                <li class="breadcrumb-item"><a href="#">Panel CEO</a></li>
                <li class="breadcrumb-item active" aria-current="page">General</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-body p-4 text-center">
                <h4 class="card-title mb-4">Bienvenido al Panel de Aerolínea</h4>
                <p class="lead">Desde aquí podrás gestionar tus vuelos, promociones y visualizar las estadísticas de tu empresa.</p>
                <div class="row mt-5">
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>Vuelos Activos</h5>
                            <h2 class="text-primary">0</h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>Promociones</h5>
                            <h2 class="text-primary">0</h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>Reservas Mensuales</h5>
                            <h2 class="text-primary">0</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>