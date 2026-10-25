<?php
require '../includes/sesiones.php';
checkRole('usuario');
require '../includes/conexion.php';

$user_id = $_SESSION['codUsuario'];
$mensaje = '';
$tipoMensaje = '';
$nombreFormulario = '';
$telefonoFormulario = '';
$esActualizacion = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $esActualizacion = true;
    $nombre = is_string($_POST['nombre'] ?? null) ? trim($_POST['nombre']) : '';
    $telefono = is_string($_POST['telefono'] ?? null) ? trim($_POST['telefono']) : '';
    $nombreFormulario = $nombre;
    $telefonoFormulario = $telefono;

    if ($nombre === '' || mb_strlen($nombre, 'UTF-8') > 100) {
        $mensaje = "Ingresá un nombre de hasta 100 caracteres.";
        $tipoMensaje = "danger";
    } elseif ($telefono === '' || !preg_match('/^[0-9+\s().-]{6,20}$/', $telefono)) {
        $mensaje = "Ingresá un teléfono válido de entre 6 y 20 caracteres.";
        $tipoMensaje = "danger";
    } else {
        $sqlUpdate = "UPDATE USUARIOS SET nombreUsuario = ?, telefonoUsuario = ? WHERE codUsuario = ?";
        $stmtUpdate = $conexion->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssi", $nombre, $telefono, $user_id);

        if ($stmtUpdate->execute()) {
            $_SESSION['nombreUsuario'] = $nombre;
            $mensaje = "Los datos de tu perfil se actualizaron correctamente.";
            $tipoMensaje = "success";
        } else {
            $mensaje = "Ocurrió un error al intentar guardar los cambios.";
            $tipoMensaje = "danger";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar_cuenta') {
    $sqlBaja = "UPDATE USUARIOS SET estadoUsuario = 'suspendido' WHERE codUsuario = ?";
    $stmtBaja = $conexion->prepare($sqlBaja);
    $stmtBaja->bind_param("i", $user_id);
    
    if ($stmtBaja->execute()) {
        session_unset();
        session_destroy();
        header("Location: /tpi/index.php?cuenta_eliminada=1");
        exit();
    } else {
        $mensaje = "No se pudo procesar la baja de tu cuenta. Intentá nuevamente más tarde.";
        $tipoMensaje = "danger";
    }
}

$sqlUser = "SELECT nombreUsuario, emailUsuario, telefonoUsuario FROM USUARIOS WHERE codUsuario = ?";
$stmtUser = $conexion->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();

if (!$esActualizacion) {
    $nombreFormulario = $user['nombreUsuario'];
    $telefonoFormulario = $user['telefonoUsuario'];
}

$title = "Mi Perfil | ViajAir";
require '../includes/header.php';
?>

<div class="row">
    
    <?php 
    $active_page = 'perfil';
    require 'sidebar_pasajero.php'; 
    ?>
    
    <div class="col-md-9 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Mi Perfil</li>
            </ol>
        </nav>
        

        <?php if (!empty($mensaje)): ?>
            <div id="perfil-mensaje" class="alert alert-<?= htmlspecialchars($tipoMensaje, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> alert-dismissible fade show" role="alert" aria-live="assertive">
                <?= htmlspecialchars($mensaje, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar notificación"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h1 class="h5 mb-0 text-white">Datos de Mi Perfil</h1>
            </div>
            <div class="card-body p-4">
                <form action="/tpi/pasajero/perfil.php" method="POST">
                    <input type="hidden" name="accion" value="actualizar">

                    <fieldset>
                        <legend class="visually-hidden">Datos de usuario</legend>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold text-dark">
                                Correo Electrónico
                            </label>
                            <input type="email" class="form-control bg-light text-dark" id="email" 
                                value="<?= htmlspecialchars($user['emailUsuario']); ?>" 
                                readonly aria-describedby="email-bloqueado-ayuda">
                            <div id="email-bloqueado-ayuda" class="form-text text-muted">
                                El correo electrónico no puede ser modificado ya que identifica tu cuenta.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold text-dark">
                                Nombre y Apellido <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($nombreFormulario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   maxlength="100" autocomplete="name" required <?= $mensaje ? 'aria-describedby="perfil-mensaje"' : ''; ?>>
                        </div>


                        <div class="mb-4">
                            <label for="telefono" class="form-label fw-semibold text-dark">
                                Teléfono de contacto <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span>
                            </label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   value="<?= htmlspecialchars($telefonoFormulario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                                   maxlength="20" pattern="[0-9+(). -]{6,20}" inputmode="tel" autocomplete="tel" required <?= $mensaje ? 'aria-describedby="perfil-mensaje"' : ''; ?>>
                        </div>
                    </fieldset>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                Guardar Modificaciones
                            </button>
                            <a href="/tpi/pasajero/perfil.php" class="btn btn-outline-secondary">
                                Descartar Modificaciones
                            </a>
                            <a href="/tpi/auth/recuperar_pass.php" class="btn btn-outline-secondary">
                                Cambiar Contraseña
                            </a>
                        </div>

                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalBajaCuenta">
                            Eliminar Cuenta
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBajaCuenta" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h2 class="modal-title h5 mb-0" id="modalLabel">Confirmar baja de cuenta</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar modal"></button>
            </div>
            <div class="modal-body p-4">
                <p>¿Estás seguro de que deseás dar de baja tu cuenta en <strong>ViajAir</strong>?</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="/tpi/pasajero/perfil.php" method="POST">
                    <input type="hidden" name="accion" value="eliminar_cuenta">
                    <button type="submit" class="btn btn-danger fw-bold">Sí, Dar de Baja</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>