<?php
require '../includes/sesiones.php';
checkRole('administrador');
require '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 col-lg-2">
        <div class="list-group shadow-sm mb-4">
            <a href="/tpi/admin/dashboard.php" class="list-group-item list-group-item-action active">Panel General</a>
            <a href="/tpi/admin/abm_aerolineas.php" class="list-group-item list-group-item-action">Aerolíneas y CEOs</a>
            <a href="/tpi/admin/auditoria.php" class="list-group-item list-group-item-action">Auditar Promos</a>
            <a href="/tpi/admin/abm_novedades.php" class="list-group-item list-group-item-action">Novedades</a>
            <a href="/tpi/admin/reportes.php" class="list-group-item list-group-item-action">Reportes Globales</a>
        </div>
    </div>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
                <li class="breadcrumb-item"><a href="#">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">General</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-body p-4 text-center">
                <h4 class="card-title mb-4">Panel de Administración Central</h4>
                <p class="lead">Supervisa toda la plataforma: aerolíneas, validaciones de CEOs y estadísticas globales.</p>
                <div class="row mt-5">
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>Aerolíneas</h5>
                            <h2 class="text-primary">0</h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>Promos Pendientes</h5>
                            <h2 class="text-warning">0</h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>Usuarios Totales</h5>
                            <h2 class="text-success">0</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>