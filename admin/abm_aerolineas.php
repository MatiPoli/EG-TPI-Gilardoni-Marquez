<?php
require '../includes/sesiones.php';
checkRole('administrador');
require '../includes/conexion.php';

$query = "SELECT * FROM AEROLINEAS";
$result = $conexion->query($query);

require '../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'aerolineas';
    require '../admin/sidebar_admin.php'; 
    ?>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
                <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Administración</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gestión de Aerolíneas</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">Listado de Aerolíneas</h5>
                <a href="form_aerolinea.php" class="btn btn-light btn-sm fw-bold">Nueva Aerolínea</a>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Código IATA</th>
                                <th>País</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['codAerolinea']; ?></td>
                                        <td><?php echo htmlspecialchars($row['nombreAerolinea']); ?></td>
                                        <td><?php echo htmlspecialchars($row['codigoIATA']); ?></td>
                                        <td><?php echo htmlspecialchars($row['codPais']); ?></td>
                                        <td class="text-center">
                                            <a href="form_aerolinea.php?id=<?php echo $row['codAerolinea']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                            <a href="eliminar_aerolinea.php?id=<?php echo $row['codAerolinea']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar esta aerolínea?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay aerolíneas registradas.</td>
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