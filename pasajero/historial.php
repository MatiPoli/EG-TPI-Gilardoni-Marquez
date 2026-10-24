<?php
require '../includes/sesiones.php';
checkRole('usuario');
require '../includes/conexion.php';

$user_id = $_SESSION['codUsuario'];

$query = "SELECT r.codReserva, r.cantidadPasajes, r.precioTotal, r.estadoReserva, r.fechaReserva,
                 v.origenVuelo, v.destinoVuelo, v.fechaSalidaVuelo, v.horaSalidaVuelo,
                 a.nombreAerolinea, a.codigoIATA
          FROM RESERVAS r
          INNER JOIN VUELOS v ON r.codVuelo = v.codVuelo
          INNER JOIN AEROLINEAS a ON v.codAerolinea = a.codAerolinea
          WHERE r.codUsuario = ? 
            AND (r.estadoReserva IN ('confirmada', 'cancelada') 
                 OR CONCAT(v.fechaSalidaVuelo, ' ', v.horaSalidaVuelo) < NOW())
          ORDER BY r.fechaReserva DESC, v.fechaSalidaVuelo DESC";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$title = "Historial de Compras | ViajAir";
require '../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'historial';
    require 'sidebar_pasajero.php'; 
    ?>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/pasajero/perfil.php">Mi Perfil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Historial de Compras</li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h1 class="h5 mb-0 text-white">Historial de Compras y Operaciones Pasadas</h1>
            </div>
            
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nro. Operación</th>
                                <th>Fecha Reserva</th>
                                <th>Aerolínea</th>
                                <th>Itinerario (Ruta)</th>
                                <th>Fecha Vuelo</th>
                                <th>Pasajes</th>
                                <th>Total Pagado</th>
                                <th>Estado</th>
                                <th class="text-center">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $row['codReserva']; ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['fechaReserva'])); ?></td>
                                        <td>
                                            <span class="badge bg-secondary text-dark border">
                                                <?= htmlspecialchars($row['nombreAerolinea']); ?> (<?= htmlspecialchars($row['codigoIATA']); ?>)
                                            </span>
                                        </td>
                                        <td>
                                            <span><?= htmlspecialchars($row['origenVuelo']); ?></span>
                                            <span aria-hidden="true"> &rarr; </span>
                                            <span class="visually-hidden"> hacia </span>
                                            <span><?= htmlspecialchars($row['destinoVuelo']); ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($row['fechaSalidaVuelo'])) . ' ' . substr($row['horaSalidaVuelo'], 0, 5) . ' hs'; ?></td>
                                        <td class="text-center"><?= $row['cantidadPasajes']; ?></td>
                                        <td class="fw-bold text-dark">$<?= number_format($row['precioTotal'], 2, ',', '.'); ?></td>
                                        <td class="text-center align-middle">
                                            <?php if ($row['estadoReserva'] === 'confirmada'): ?>
                                                <span class="badge bg-success">Confirmada</span>
                                            <?php elseif ($row['estadoReserva'] === 'cancelada'): ?>
                                                <span class="badge bg-danger">Cancelada</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pendiente Vencida</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['estadoReserva'] === 'confirmada'): ?>
                                                <a href="/tpi/pasajero/imprimir_ticket.php?id=<?= $row['codReserva']; ?>" 
                                                   class="btn btn-sm btn-outline-dark" 
                                                   title="Imprimir comprobante oficial de la compra #<?= $row['codReserva']; ?>">
                                                    Imprimir
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">&mdash;</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No tenés compras ni viajes pasados registrados en tu cuenta.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require '../includes/footer.php'; ?>