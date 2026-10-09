<?php
require '../../includes/sesiones.php';
checkRole('administrador');
require '../../includes/conexion.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$text = '';
$pub_date = date('Y-m-d');
$exp_date = '';
$error_message = '';

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'past_date') {
        $error_message = "La fecha de publicación no puede ser anterior a hoy.";
    } elseif ($_GET['error'] === 'invalid_exp') {
        $error_message = "La fecha de expiración debe ser mayor a la fecha de publicación.";
    }
}

if ($id) {
    $stmt = $conexion->prepare("SELECT * FROM NOVEDADES WHERE codNovedad = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    
    if ($data) {
        $text = $data['textoNovedad'];
        $pub_date = $data['fechaPublicacionNovedad'];
        $exp_date = $data['fechaExpiracionNovedad'];
    } else {
        header("Location: abm_novedades.php");
        exit();
    }
}

require '../../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'novedades';
    require '../sidebar_admin.php'; 
    ?>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
                <li class="breadcrumb-item"><a href="/tpi/admin/dashboard.php">Sección Admin</a></li>
                <li class="breadcrumb-item"><a href="abm_novedades.php">Novedades</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $id ? 'Editar' : 'Nueva'; ?></li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><?php echo $id ? 'Modificar Novedad' : 'Registrar Nueva Novedad'; ?></h5>
                <a href="abm_novedades.php" class="btn btn-outline-light btn-sm">Volver</a>
            </div>
            <div class="card-body p-4">
                
                <?php if($error_message): ?>
                    <div class="alert alert-danger p-2 text-center"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form action="procesar_novedad.php" method="POST" class="row g-3">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <div class="col-12">
                        <label class="form-label">Texto de la Novedad</label>
                        <textarea name="text" class="form-control" rows="3" required maxlength="200"><?php echo htmlspecialchars($text); ?></textarea>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Publicación</label>
                        <input type="date" name="pub_date" class="form-control" value="<?php echo htmlspecialchars($pub_date); ?>" min="<?php echo empty($id) ? date('Y-m-d') : ''; ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Expiración</label>
                        <input type="date" name="exp_date" class="form-control" value="<?php echo htmlspecialchars($exp_date); ?>" min="<?php echo empty($id) ? date('Y-m-d', strtotime('+1 day')) : ''; ?>" required>
                    </div>
                    
                    <div class="col-12 mt-4 text-center">
                        <button type="submit" class="btn btn-primary px-5">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require '../../includes/footer.php'; ?>