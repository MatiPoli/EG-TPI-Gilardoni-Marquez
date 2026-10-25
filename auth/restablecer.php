<?php
require '../includes/sesiones.php';
require '../includes/conexion.php';
require '../includes/header.php'; 

$message = '';
$message_type = '';
$is_valid_token = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conexion->prepare("SELECT codUsuario FROM USUARIOS WHERE tokenRecuperacion = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $is_valid_token = true;
        $user = $result->fetch_assoc();
        $user_id = $user['codUsuario'];
    } else {
        $message = "El enlace de recuperación es inválido o ya expiró.";
        $message_type = "danger";
    }
} else {
    header("Location: /tpi/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
    $password = is_string($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = is_string($_POST['password_confirm'] ?? null) ? $_POST['password_confirm'] : '';
    $id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if (!$id) {
        $message = 'La solicitud no es válida.';
        $message_type = 'danger';
    } elseif (strlen($password) < 8 || strlen($password) > 8) {
        $message = 'La contraseña debe tener exactamente 8 caracteres.';
        $message_type = 'danger';
    } elseif ($password !== $password_confirm) {
        $message = "Las contraseñas no coinciden.";
        $message_type = "danger";
        $is_valid_token = true; 
    } else {
        $hashed_password = md5($password);
        
        $stmt_update = $conexion->prepare("UPDATE USUARIOS SET claveUsuario = ?, tokenRecuperacion = NULL WHERE codUsuario = ?");
        $stmt_update->bind_param("si", $hashed_password, $id);
        
        if ($stmt_update->execute()) {
            $message = "Contraseña actualizada exitosamente.";
            $message_type = "success";
            $is_valid_token = false; 
        } else {
            $message = "Ocurrió un error al intentar cambiar la contraseña.";
            $message_type = "danger";
        }
    }
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page">Nueva Contraseña</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm mt-3">
            <div class="card-body p-4">
                <h1 class="h4 card-title text-center mb-4">Formulario de nueva contraseña</h1>
                
                <?php if($message): ?>
                    <div id="restablecer-mensaje" class="alert alert-<?= htmlspecialchars($message_type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> p-2 text-center" role="alert" aria-live="assertive"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                    <?php if(!$is_valid_token && $message_type === 'success'): ?>
                        <div class="text-center mt-3">
                            <a href="/tpi/auth/login.php" class="btn btn-primary">Ir al Login</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($is_valid_token): ?>
                    <form action="restablecer.php?token=<?= $token; ?>" method="POST">
                        <input type="hidden" name="user_id" value="<?= $user_id; ?>">
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required minlength="8" maxlength="8" aria-describedby="password-ayuda">
                            <div id="password-ayuda" class="form-text">La contraseña debe tener exactamente 8 caracteres.</div>
                        </div>
                        <div class="mb-4">
                            <label for="password_confirm" class="form-label">Repetir contraseña <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                            <input type="password" id="password_confirm" name="password_confirm" class="form-control" autocomplete="new-password" required minlength="8" maxlength="8" aria-describedby="password-confirm-ayuda">
                            <div id="password-confirm-ayuda" class="form-text">Repetí la contraseña ingresada.</div>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>