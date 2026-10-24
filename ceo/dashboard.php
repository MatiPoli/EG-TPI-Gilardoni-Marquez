<?php
require '../includes/sesiones.php';
checkRole('ceo');
require '../includes/conexion.php';

$airline_id = $_SESSION['codAerolinea'];

$query_flights = "SELECT COUNT(*) as total FROM VUELOS WHERE codAerolinea = ?";
$stmt_flights = $conexion->prepare($query_flights);
$stmt_flights->bind_param("i", $airline_id);
$stmt_flights->execute();
$total_flights = $stmt_flights->get_result()->fetch_assoc()['total'];

$query_promos = "SELECT COUNT(*) as total FROM PROMOCIONES WHERE codAerolinea = ?";
$stmt_promos = $conexion->prepare($query_promos);
$stmt_promos->bind_param("i", $airline_id);
$stmt_promos->execute();
$total_promos = $stmt_promos->get_result()->fetch_assoc()['total'];

$query_sales = "SELECT COUNT(*) as total FROM RESERVAS r JOIN VUELOS v ON r.codVuelo = v.codVuelo WHERE v.codAerolinea = ? AND r.estadoReserva = 'confirmada'";
$stmt_sales = $conexion->prepare($query_sales);
$stmt_sales->bind_param("i", $airline_id);
$stmt_sales->execute();
$total_sales = $stmt_sales->get_result()->fetch_assoc()['total'];

$title = "Panel CEO | ViajAir";
require '../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'dashboard';
    require 'sidebar_ceo.php'; 
    ?>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Panel CEO</li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 text-center">
                <h1 class="h3 fw-bold mb-2 text-dark">Bienvenido al Panel de Aerolínea</h1>
                <p class="lead text-muted">Gestión exclusiva de vuelos, promociones y reportes comerciales de tu empresa.</p>
                
                <div class="row mt-4 g-3">
                    <div class="col-12 col-md-4">
                        <div class="p-4 border rounded bg-light h-100">
                            <h2 class="h6 text-muted text-uppercase fw-bold">Vuelos Totales</h2>
                            <span class="display-6 fw-bold text-primary"><?= $total_flights; ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-4 border rounded bg-light h-100">
                            <h2 class="h6 text-muted text-uppercase fw-bold">Promociones</h2>
                            <span class="display-6 fw-bold text-warning"><?= $total_promos; ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-4 border rounded bg-light h-100">
                            <h2 class="h6 text-muted text-uppercase fw-bold">Ventas Confirmadas</h2>
                            <span class="display-6 fw-bold text-success"><?= $total_sales; ?></span>
                        </div>
                    </div>
                </div>

                <hr class="my-5">

                <div class="text-start">
                    <h2 class="h5 fw-bold text-dark mb-3">Informes y Reportes Estadísticos</h2>
                    <p class="text-muted small mb-4">Accedé a los reportes consolidados para la toma de decisiones estratégicas de la aerolínea.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="card border p-3 h-100">
                                <h3 class="h6 fw-bold text-primary mb-2">Reporte de Flujo Monetario y Ventas</h3>
                                <p class="text-muted small mb-3">Auditoría de ingresos totales facturados, reservas abonadas y rendimiento comercial.</p>
                                <a href="/tpi/ceo/reporte_ventas.php" class="btn btn-outline-primary fw-bold mt-auto">
                                    Ver Reporte de Ventas
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="card border p-3 h-100">
                                <h3 class="h6 fw-bold text-primary mb-2">Reporte de Ocupación de Vuelos</h3>
                                <p class="text-muted small mb-3">Métricas de porcentaje de asientos vendidos vs. cupos libres por ruta y fecha.</p>
                                <a href="/tpi/ceo/reporte_ocupacion.php" class="btn btn-outline-primary fw-bold mt-auto">
                                    Ver Reporte de Ocupación
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>