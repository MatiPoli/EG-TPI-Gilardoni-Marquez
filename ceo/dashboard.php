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
                <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
                <li class="breadcrumb-item"><a href="/tpi/ceo/dashboard.php">Panel CEO</a></li>
                <li class="breadcrumb-item active" aria-current="page">General</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-body p-4 text-center">
                <h4 class="card-title mb-4">Bienvenido al Panel de Aerolínea</h4>
                <p class="lead">Gestión exclusiva de vuelos y métricas de tu empresa.</p>
                <div class="row mt-5">
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>Vuelos Activos</h5>
                            <h2 class="text-primary"><?php echo $total_flights; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>Promociones</h5>
                            <h2 class="text-warning"><?php echo $total_promos; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <h5>Reservas Confirmadas</h5>
                            <h2 class="text-success"><?php echo $total_sales; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>