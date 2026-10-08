<?php
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
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $id = $_POST['user_id'];

    if ($password !== $password_confirm) {
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
        <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
        <li class="breadcrumb-item active" aria-current="page">Nueva Contraseña</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm mt-3">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4">Formulario de nueva contraseña</h4>
                
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> p-2 text-center"><?php echo $message; ?></div>
                    <?php if(!$is_valid_token && $message_type === 'success'): ?>
                        <div class="text-center mt-3">
                            <a href="/tpi/auth/login.php" class="btn btn-primary">Ir al Login</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($is_valid_token): ?>
                    <form action="restablecer.php?token=<?php echo $token; ?>" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Nueva Contraseña" required maxlength="8">
                        </div>
                        <div class="mb-4">
                            <input type="password" name="password_confirm" class="form-control" placeholder="Repetir Contraseña" required maxlength="8">
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