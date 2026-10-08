<?php
require '../includes/conexion.php';
require '../includes/header.php';

$message = "Enlace no válido.";
$message_type = "danger";

if (isset($_GET['id']) && isset($_GET['hash'])) {
    $id = $_GET['id'];
    $hash = $_GET['hash'];

    $stmt = $conexion->prepare("SELECT emailUsuario, estadoUsuario FROM USUARIOS WHERE codUsuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $real_hash = md5($user['emailUsuario'] . $id);
        
        if ($hash === $real_hash) {
            if ($user['estadoUsuario'] === 'pendiente') {
                $stmt_update = $conexion->prepare("UPDATE USUARIOS SET estadoUsuario = 'activo' WHERE codUsuario = ?");
                $stmt_update->bind_param("i", $id);
                if ($stmt_update->execute()) {
                    $message = "Cuenta validada exitosamente.";
                    $message_type = "success";
                } else {
                    $message = "Ocurrió un error al activar tu cuenta.";
                }
            } else {
                $message = "Esta cuenta ya ha sido validada previamente.";
                $message_type = "info";
            }
        }
    }
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-5 text-center">
                <h4 class="mb-4">Validación de Cuenta</h4>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
                <a href="/tpi/auth/login.php" class="btn btn-primary mt-3">Ir al Login</a>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>