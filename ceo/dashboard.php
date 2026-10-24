<?php
require '../includes/sesiones.php';
checkRole('ceo');
require '../includes/conexion.php';

$airline_id = $_SESSION['codAerolinea'];

$query_airline = "SELECT nombreAerolinea, codigoIATA, descripcionAerolinea, codPais FROM AEROLINEAS WHERE codAerolinea = ?";
$stmt_airline = $conexion->prepare($query_airline);
$stmt_airline->bind_param("i", $airline_id);
$stmt_airline->execute();
$airline_info = $stmt_airline->get_result()->fetch_assoc();

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
                
                <h1 class="h3 fw-bold mb-1 text-dark">Bienvenido al Panel de Aerolínea</h1>
                <p class="text-muted mb-4">Gestión comercial y operativa de tu empresa.</p>

                <?php if ($airline_info): ?>
                    <div class="card bg-light border-0 shadow-sm p-3 mb-4 text-start">
                        <div class="align-items-center justify-content-between border-bottom pb-2 mb-2">
                            <span class="h5 fw-bold text-primary mb-0 me-2"><?= htmlspecialchars($airline_info['nombreAerolinea']); ?></span>
                            <span class="badge bg-secondary text-dark border">IATA: <?= htmlspecialchars($airline_info['codigoIATA']); ?></span>
                            <span class="badge bg-secondary text-dark border">País: <?= htmlspecialchars($airline_info['codPais']); ?></span>
                        </div>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($airline_info['descripcionAerolinea']); ?></p>
                    </div>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded bg-light h-100">
                            <h2 class="h6 text-muted text-uppercase fw-bold mb-1">Vuelos Totales</h2>
                            <span class="display-6 fw-bold text-primary"><?= $total_flights; ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded bg-light h-100">
                            <h2 class="h6 text-muted text-uppercase fw-bold mb-1">Promociones</h2>
                            <span class="display-6 fw-bold text-warning"><?= $total_promos; ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded bg-light h-100">
                            <h2 class="h6 text-muted text-uppercase fw-bold mb-1">Ventas Confirmadas</h2>
                            <span class="display-6 fw-bold text-success"><?= $total_sales; ?></span>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="text-start">
                    <h2 class="h5 fw-bold text-dark mb-2">Informes y Reportes Estadísticos</h2>
                    <p class="text-muted small mb-3">Métricas consolidadas para la toma de decisiones.</p>

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