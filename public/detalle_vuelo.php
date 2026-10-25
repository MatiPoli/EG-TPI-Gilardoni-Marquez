<?php 
require '../includes/sesiones.php';
require '../includes/conexion.php'; 

$idVuelo = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$vuelo = null;

if ($idVuelo > 0) {
    $sql = "SELECT v.*, a.nombreAerolinea, a.descripcionAerolinea, a.codigoIATA, a.codPais,
                   p.descuentoPromocion, p.descripcionPromocion
            FROM VUELOS v
            INNER JOIN AEROLINEAS a ON v.codAerolinea = a.codAerolinea
            LEFT JOIN PROMOCIONES p ON a.codAerolinea = p.codAerolinea AND p.estadoPromocion = 'aprobada'
            WHERE v.codVuelo = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idVuelo);
    $stmt->execute();
    $result = $stmt->get_result();
    $vuelo = $result->fetch_assoc();
}

if ($vuelo) {
    $precioOriginal = (float)$vuelo['precioVuelo'];
    $descuento = (float)($vuelo['descuentoPromocion'] ?? 0);
    $tienePromo = $descuento > 0;
    $montoDescuento = $tienePromo ? $precioOriginal * ($descuento / 100) : 0;
    $precioFinal = $precioOriginal - $montoDescuento;
    $maxPasajes = min(10, (int)$vuelo['asientosDisponibles']);
    $title = "Detalle de Vuelo | ViajAir";
} else {
    $title = "Vuelo no encontrado | ViajAir";
}

require '../includes/header.php'; 
?>

<div class="container mb-5">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/tpi/public/buscar_vuelos.php">Buscar Vuelos</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $vuelo ? "Detalle de Vuelo #" . $vuelo['codVuelo'] : "Error"; ?>
            </li>
        </ol>
    </nav>

    <?php if (isset($_GET['error_reserva']) && $_GET['error_reserva'] === 'sin_stock'): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <strong>No se pudo realizar la reserva:</strong> No hay suficientes asientos disponibles para la cantidad solicitada.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar notificación"></button>
        </div>
    <?php endif; ?>

    <?php if ($vuelo): ?>
        <div class="row g-4">
            
            <section class="col-12 col-lg-8" aria-labelledby="titulo-detalle">
                <div class="card shadow-sm border-0 p-4 bg-white h-100">
                    
                    <header class="border-bottom pb-3 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-secondary text-dark border"><?= htmlspecialchars($vuelo['nombreAerolinea']); ?> (<?= htmlspecialchars($vuelo['codigoIATA']); ?>)</span>
                            <?php if ($tienePromo): ?>
                                <span class="badge bg-success">
                                    <span class="visually-hidden">Promoción aprobada del </span>
                                    <?= $descuento; ?>% OFF
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <h1 id="titulo-detalle" class="h3 fw-bold text-dark mb-1">
                            <?= htmlspecialchars($vuelo['origenVuelo']); ?> hacia <?= htmlspecialchars($vuelo['destinoVuelo']); ?>
                        </h1>
                        <p class="text-muted mb-0">Vuelo Código Oficial #<?= $vuelo['codVuelo']; ?></p>
                    </header>

                    <h2 class="h5 fw-bold text-primary mb-3">Información del Itinerario</h2>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <caption class="visually-hidden">Datos de itinerario y cupo del vuelo</caption>
                            <tbody>
                                <tr>
                                    <th scope="row" class="bg-light w-35 text-dark fw-semibold">Ciudad de Origen</th>
                                    <td><?= htmlspecialchars($vuelo['origenVuelo']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="bg-light text-dark fw-semibold">Ciudad de Destino</th>
                                    <td><?= htmlspecialchars($vuelo['destinoVuelo']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="bg-light text-dark fw-semibold">Fecha de Partida</th>
                                    <td><?= date('d/m/Y', strtotime($vuelo['fechaSalidaVuelo'])); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="bg-light text-dark fw-semibold">Hora de Salida</th>
                                    <td><?= substr($vuelo['horaSalidaVuelo'], 0, 5); ?> hs (Hora local GMT-3)</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="bg-light text-dark fw-semibold">Disponibilidad Actual</th>
                                    <td>
                                        <span class="badge bg-primary fs-6"><?= $vuelo['asientosDisponibles']; ?> asientos libres</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($tienePromo): ?>
                        <h2 class="h5 fw-bold text-primary mb-2">Promoción Aplicada</h2>
                        <p class="text-muted mb-4">
                            <?= htmlspecialchars($vuelo['descripcionPromocion']); ?> 
                            (<strong><?= $descuento; ?>% de descuento</strong> incluido en el precio final).
                        </p>
                    <?php endif; ?>

                    <h2 class="h5 fw-bold text-primary mb-2">Políticas del Servicio</h2>
                    <ul class="text-muted small ps-3 mb-0">
                        <li>Podés cancelar tu reserva sin penalidad hasta <strong>72 horas antes</strong> del horario de salida.</li>
                        <li>La reserva se mantendrá en estado <em>"pendiente de pago"</em> hasta registrar el abono.</li>
                    </ul>

                </div>
            </section>

            <aside class="col-12 col-lg-4" aria-labelledby="resumen-tarifa">
                <div class="card shadow-sm border-0 p-4 bg-secondary position-sticky" style="top: 2rem;">
                    <h2 id="resumen-tarifa" class="h5 fw-bold text-primary border-bottom pb-2 mb-3">Datos Resumen</h2>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-dark">Tarifa por pasaje:</span>
                        <span class="fw-semibold <?= $tienePromo ? 'text-decoration-line-through text-muted' : 'text-dark'; ?>">
                            $ <?= number_format($precioOriginal, 2, ',', '.'); ?>
                        </span>
                    </div>

                    <?php if ($tienePromo): ?>
                        <div class="d-flex justify-content-between text-success mb-2">
                            <span>Descuento (<?= $descuento; ?>%):</span>
                            <span>- $ <?= number_format($montoDescuento, 2, ',', '.'); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between text-muted small mb-3">
                        <span>Impuestos y tasas:</span>
                        <span>Incluidos</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-baseline mb-4">
                        <span class="h6 fw-bold text-dark mb-0">Precio Unitario:</span>
                        <span class="h3 fw-bold text-primary mb-0">$ <?= number_format($precioFinal, 2, ',', '.'); ?></span>
                    </div>

                    <form action="/tpi/pasajero/procesar_reserva.php" method="POST" class="d-grid gap-3">
                        <input type="hidden" name="id" value="<?= $vuelo['codVuelo']; ?>">

                        <div>
                            <label for="cantidad" class="form-label fw-semibold text-dark small mb-1">
                                Seleccionar cantidad de pasajes:
                            </label>
                            <input type="number" 
                                id="cantidad" 
                                name="cantidad" 
                                class="form-control text-center" 
                                value="1" 
                                min="1" 
                                max="<?= $maxPasajes; ?>" 
                                required>
                            <div class="form-text small text-muted text-center">Máximo <?= $maxPasajes; ?> asientos por operación.</div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg fw-bold">
                            Reservar Vuelo
                        </button>
                        
                        <a href="/tpi/public/buscar_vuelos.php" class="btn btn-outline-dark" role="button">
                            Volver al catálogo
                        </a>
                    </form>

                </div>
            </aside>

        </div>

    <?php else: ?>
        <div class="row justify-content-center my-4">
            <div class="col-md-8 col-lg-6">
                <div class="alert alert-danger shadow-sm text-center p-4 border-0">
                    <h1 class="h4 fw-bold mb-2">Vuelo no encontrado</h1>
                    <p class="mb-3 text-muted">El vuelo solicitado no existe, fue cancelado o ya no cuenta con disponibilidad.</p>
                    <a href="/tpi/public/buscar_vuelos.php" class="btn btn-primary fw-bold">
                        Volver al catálogo de vuelos
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require '../includes/footer.php'; ?>