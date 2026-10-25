<?php
require '../includes/sesiones.php';
checkRole('administrador');
require '../includes/conexion.php';

$title = 'Panel de Administración | ViajAir';

$query_airlines = "SELECT COUNT(*) as total FROM AEROLINEAS";
$result_airlines = $conexion->query($query_airlines);
$total_airlines = $result_airlines->fetch_assoc()['total'];

$query_promos = "SELECT COUNT(*) as total FROM PROMOCIONES WHERE estadoPromocion = 'pendiente'";
$result_promos = $conexion->query($query_promos);
$total_promos = $result_promos->fetch_assoc()['total'];

$query_users = "SELECT COUNT(*) as total FROM USUARIOS";
$result_users = $conexion->query($query_users);
$total_users = $result_users->fetch_assoc()['total'];

require '../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'dashboard';
    require '../admin/sidebar_admin.php'; 
    ?>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">General</li>
            </ol>
        </nav>

        <div class="card shadow-sm mb-5">
            <div class="card-body p-4 text-center">
                <h1 class="h3 card-title mb-4">Panel de Administración Central</h1>
                <p class="lead">Supervisa toda la plataforma: aerolíneas, validaciones de CEOs y estadísticas globales.</p>
                <div class="row mt-5">
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h2 class="h6 text-dark">Aerolíneas</h2>
                            <h2 class="text-primary"><?= $total_airlines; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h2 class="h6 text-dark">Promociones pendientes</h2>
                            <h2 class="text-warning"><?= $total_promos; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h2 class="h6 text-dark">Usuarios totales</h2>
                            <h2 class="text-success"><?= $total_users; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>