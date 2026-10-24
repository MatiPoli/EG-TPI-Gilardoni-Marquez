<?php
require '../includes/sesiones.php';
checkRole('usuario');
require '../includes/conexion.php';

$user_id = $_SESSION['codUsuario'];
$idReserva = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idReserva <= 0) {
    header("Location: /tpi/pasajero/reservas.php");
    exit();
}

$sql = "SELECT r.codReserva, r.fechaReserva, r.cantidadPasajes, r.precioTotal, r.estadoReserva,
               u.nombreUsuario, u.emailUsuario, u.telefonoUsuario,
               v.codVuelo, v.origenVuelo, v.destinoVuelo, v.fechaSalidaVuelo, v.horaSalidaVuelo,
               a.nombreAerolinea, a.codigoIATA
        FROM RESERVAS r
        INNER JOIN USUARIOS u ON r.codUsuario = u.codUsuario
        INNER JOIN VUELOS v ON r.codVuelo = v.codVuelo
        INNER JOIN AEROLINEAS a ON v.codAerolinea = a.codAerolinea
        WHERE r.codReserva = ? AND r.codUsuario = ? AND r.estadoReserva = 'confirmada'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $idReserva, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$ticket = $res->fetch_assoc();

if (!$ticket) {
    header("Location: /tpi/pasajero/reservas.php");
    exit();
}

$title = "Comprobante de Reserva #" . $ticket['codReserva'] . " | ViajAir";
require '../includes/header.php';
?>

<div class="container mb-5">

    <div class="d-print-none row g-3 align-items-center justify-content-between mb-4">
        <div class="col-12 col-md-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="/tpi/pasajero/perfil.php">Mi Perfil</a></li>
                    <li class="breadcrumb-item"><a href="/tpi/pasajero/reservas.php">Mis Reservas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Imprimir Comprobante</li>
                </ol>
            </nav>
        </div>

        <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-dark fw-bold flex-grow-1 flex-md-grow-0" onclick="window.print();">
                Imprimir Comprobante
            </button>
            <a href="/tpi/pasajero/reservas.php" class="btn btn-outline-secondary flex-grow-1 flex-md-grow-0">
                Volver
            </a>
        </div>
    </div>

    <div class="card ticket-box shadow-sm border-dark">
        <div class="card-body p-4 p-md-5">

            <div class="row align-items-center pb-3 border-bottom border-dark border-2">
                <div class="col-6">
                    <h1 class="h3 fw-bold mb-0 text-black">ViajAir</h1>
                    <small class="text-uppercase text-muted">Comprobante Oficial de Pasaje Aéreo</small>
                </div>
                <div class="col-6 text-end">
                    <p class="mb-0 fw-bold fs-5 text-black">RESERVA #<?php echo $ticket['codReserva']; ?></p>
                    <small class="text-muted">Fecha de emisión: <?php echo date('d/m/Y', strtotime($ticket['fechaReserva'])); ?></small>
                </div>
            </div>

            <div class="row my-4">
                <div class="col-12">
                    <h2 class="h6 fw-bold text-uppercase border-bottom border-dark pb-1 text-black">Datos del Pasajero Titular</h2>
                    <div class="row pt-2">
                        <div class="col-6">
                            <p class="mb-1"><strong>Nombre y Apellido:</strong> <?php echo htmlspecialchars($ticket['nombreUsuario']); ?></p>
                            <p class="mb-1"><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($ticket['emailUsuario']); ?></p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($ticket['telefonoUsuario']); ?></p>
                            <p class="mb-1"><strong>Estado del Pasaje:</strong> CONFIRMADO / PAGADO</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h6 fw-bold text-uppercase border-bottom border-dark pb-1 text-black">Detalle del Vuelo</h2>
                    <table class="table table-bordered border-dark mt-2 mb-0">
                        <caption class="visually-hidden">Datos de itinerario del vuelo</caption>
                        <tbody>
                            <tr>
                                <th scope="row" class="w-30 bg-light text-black">Aerolínea</th>
                                <td><?php echo htmlspecialchars($ticket['nombreAerolinea']); ?> (<?php echo htmlspecialchars($ticket['codigoIATA']); ?>)</td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-light text-black">Código de Vuelo</th>
                                <td>#<?php echo $ticket['codVuelo']; ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-light text-black">Itinerario</th>
                                <td><?php echo htmlspecialchars($ticket['origenVuelo']); ?> &rarr; <?php echo htmlspecialchars($ticket['destinoVuelo']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-light text-black">Fecha de Salida</th>
                                <td><?php echo date('d/m/Y', strtotime($ticket['fechaSalidaVuelo'])); ?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-light text-black">Hora de Salida</th>
                                <td><?php echo substr($ticket['horaSalidaVuelo'], 0, 5); ?> hs (Hora local GMT-3)</td>
                            </tr>
                            <tr>
                                <th scope="row" class="bg-light text-black">Cantidad de Asientos</th>
                                <td><strong><?php echo $ticket['cantidadPasajes']; ?></strong> pasaje(s)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row border-top border-dark border-2 pt-3 align-items-center">
                <div class="col-6">
                    <small class="d-block text-muted">Presentarse en el aeropuerto con este comprobante y documento de identidad vigente con anticipación.</small>
                    <small class="d-block text-muted">ViajAir - Rosario, Argentina.</small>
                </div>
                <div class="col-6 text-end">
                    <small class="text-uppercase text-muted d-block">Total Abonado</small>
                    <span class="h3 fw-bold text-black mb-0 d-block">$ <?php echo number_format($ticket['precioTotal'], 2, ',', '.'); ?></span>
                    <small class="text-muted">Pesos Argentinos (Tasas e impuestos incluidos)</small>
                </div>
            </div>

        </div>
    </div>

</div>

<?php require '../includes/footer.php'; ?>