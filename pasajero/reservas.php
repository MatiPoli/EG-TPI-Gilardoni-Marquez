<?php
require '../includes/sesiones.php';
checkRole('usuario');
require '../includes/conexion.php';

$user_id = $_SESSION['codUsuario'];

$query = "SELECT r.codReserva, r.estadoReserva, v.origenVuelo, v.destinoVuelo, v.fechaSalidaVuelo, v.precioVuelo 
          FROM RESERVAS r 
          JOIN VUELOS v ON r.codVuelo = v.codVuelo 
          WHERE r.codUsuario = ? 
          ORDER BY v.fechaSalidaVuelo ASC";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

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
                                <th>Precio</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['codReserva']; ?></td>
                                        <td><?php echo htmlspecialchars($row['origenVuelo'] . ' - ' . $row['destinoVuelo']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fechaSalidaVuelo']); ?></td>
                                        <td>$<?php echo number_format($row['precioVuelo'], 2); ?></td>
                                        <td>
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
                                                <a href="pagar_reserva.php?id=<?php echo $row['codReserva']; ?>" class="btn btn-sm btn-success">Pagar</a>
                                            <?php endif; ?>
                                            
                                            <?php if ($row['estadoReserva'] != 'cancelada'): ?>
                                                <a href="cancelar_reserva.php?id=<?php echo $row['codReserva']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas cancelar esta reserva?');">Cancelar</a>
                                            <?php endif; ?>

                                            <?php if ($row['estadoReserva'] == 'confirmada'): ?>
                                                <a href="imprimir_ticket.php?id=<?php echo $row['codReserva']; ?>" class="btn btn-sm btn-secondary" target="_blank">Imprimir</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No tienes reservas registradas.</td>
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