<?php
require '../includes/sesiones.php';
checkRole('administrador');
require '../includes/conexion.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$name = '';
$iata = '';
$desc = '';
$country = '';

if ($id) {
    $stmt = $conexion->prepare("SELECT * FROM AEROLINEAS WHERE codAerolinea = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    
    if ($data) {
        $name = $data['nombreAerolinea'];
        $iata = $data['codigoIATA'];
        $desc = $data['descripcionAerolinea'];
        $country = $data['codPais'];
    } else {
        header("Location: abm_aerolineas.php");
        exit();
    }
}

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
                <li class="breadcrumb-item"><a href="abm_aerolineas.php">Aerolíneas</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $id ? 'Editar' : 'Nueva'; ?></li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><?php echo $id ? 'Modificar Aerolínea' : 'Registrar Nueva Aerolínea'; ?></h5>
                <a href="abm_aerolineas.php" class="btn btn-outline-light btn-sm">Volver</a>
            </div>
            <div class="card-body p-4">
                <form action="procesar_aerolinea.php" method="POST" class="row g-3">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <div class="col-md-8">
                        <label class="form-label">Nombre de la Aerolínea</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Código IATA</label>
                        <input type="text" name="iata" class="form-control" value="<?php echo htmlspecialchars($iata); ?>" required maxlength="3">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Código del País</label>
                        <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($country); ?>" required maxlength="3">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="desc" class="form-control" rows="3" required><?php echo htmlspecialchars($desc); ?></textarea>
                    </div>
                    <div class="col-12 mt-4 text-center">
                        <button type="submit" class="btn btn-primary px-5">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>