<?php
require '../includes/sesiones.php';
checkRole('usuario');
require '../includes/conexion.php';

$user_id = $_SESSION['codUsuario'];

$query = "SELECT r.codReserva, r.cantidadPasajes, r.precioTotal, r.estadoReserva, 
                 v.origenVuelo, v.destinoVuelo, v.fechaSalidaVuelo, v.horaSalidaVuelo,
                 TIMESTAMPDIFF(HOUR, NOW(), CONCAT(v.fechaSalidaVuelo, ' ', v.horaSalidaVuelo)) AS horas_restantes
          FROM RESERVAS r 
          JOIN VUELOS v ON r.codVuelo = v.codVuelo 
          WHERE r.codUsuario = ? 
          ORDER BY v.fechaSalidaVuelo ASC";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$title = "Mis Reservas | ViajAir";
require '../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'reservas';
    require 'sidebar_pasajero.php'; 
    ?>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="/tpi/pasajero/perfil.php">Mi Perfil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Mis Reservas</li>
            </ol>
        </nav>

        <?php if (isset($_GET['reserva_creada'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Reserva registrada con éxito!</strong> Tenés tu cupo asegurado en estado pendiente de pago.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar notificación"></button>
            </div>
        <?php elseif (isset($_GET['pago_exitoso'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Pago completado!</strong> Tu reserva pasó a estado confirmada.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar notificación"></button>
            </div>
        <?php elseif (isset($_GET['error_pago'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error al procesar el pago:</strong> La reserva no se encuentra pendiente o no pertenece a tu cuenta.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar notificación"></button>
            </div>
        <?php elseif (isset($_GET['cancelada'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong>Reserva cancelada.</strong> Los asientos fueron liberados nuevamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar notificación"></button>
            </div>
        <?php elseif (isset($_GET['error_cancelacion']) && $_GET['error_cancelacion'] === 'plazo'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>No es posible cancelar:</strong> Faltan menos de 72 horas para la salida del vuelo según las políticas del servicio.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar notificación"></button>
            </div>
        <?php elseif (isset($_GET['error_cancelacion']) && $_GET['error_cancelacion'] === 'transaccion'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error del sistema:</strong> No se pudo actualizar el estado de la reserva. Por favor, intentá de nuevo.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar notificación"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h1 class="h5 mb-0 text-white">Reservas del Usuario</h1>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Reserva</th>
                                <th>Vuelo (Origen - Destino)</th>
                                <th>Fecha de Salida</th>
                                <th>Pasajes</th>
                                <th>Precio Total</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $row['codReserva']; ?></td>
                                        <td>
                                            <span><?php echo htmlspecialchars($row['origenVuelo']); ?></span>
                                            <span aria-hidden="true"> &rarr; </span>
                                            <span class="visually-hidden"> hacia </span>
                                            <span><?php echo htmlspecialchars($row['destinoVuelo']); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($row['fechaSalidaVuelo'])) . ' ' . substr($row['horaSalidaVuelo'], 0, 5) . ' hs'; ?></td>
                                        <td class="text-center"><?php echo $row['cantidadPasajes']; ?></td>
                                        <td class="fw-bold text-primary">$<?php echo number_format($row['precioTotal'], 2, ',', '.'); ?></td>
                                        <td class="text-center align-middle">
                                            <?php if ($row['estadoReserva'] == 'confirmada'): ?>
                                                <span class="badge bg-success">Confirmada</span>
                                            <?php elseif ($row['estadoReserva'] == 'cancelada'): ?>
                                                <span class="badge bg-danger">Cancelada</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['estadoReserva'] == 'pendiente de pago'): ?>
                                                <a href="pagar_reserva.php?id=<?php echo $row['codReserva']; ?>" class="btn btn-sm btn-success fw-bold">Pagar</a>
                                            <?php endif; ?>
                                            
                                            <?php if ($row['estadoReserva'] != 'cancelada' && (int)$row['horas_restantes'] >= 72): ?>
                                                <a href="cancelar_reserva.php?id=<?php echo $row['codReserva']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas cancelar esta reserva?');">Cancelar</a>
                                            <?php endif; ?>

                                            <?php if ($row['estadoReserva'] == 'confirmada'): ?>
                                                <a href="imprimir_ticket.php?id=<?php echo $row['codReserva']; ?>" class="btn btn-sm btn-secondary" target="_blank">Imprimir</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No tienes reservas registradas.</td>
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