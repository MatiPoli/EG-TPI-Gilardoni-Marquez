<?php
require '../includes/conexion.php';
checkLoggedIn(true);
require '../includes/header.php'; 

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['nombre'];
    $email = $_POST['email'];
    $phone = $_POST['telefono'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $account_type = 'ceo';

    if ($password !== $password_confirm) {
        $message = "Las contraseñas no coinciden.";
        $message_type = "danger";
    } else {
        $stmt_check = $conexion->prepare("SELECT codUsuario FROM USUARIOS WHERE emailUsuario = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows > 0) {
            $message = "El correo ingresado ya está registrado.";
            $message_type = "danger";
        } else {
            $hashed_password = md5($password);
            
            $stmt_insert = $conexion->prepare("INSERT INTO USUARIOS (nombreUsuario, claveUsuario, tipoUsuario, emailUsuario, telefonoUsuario) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $name, $hashed_password, $account_type, $email, $phone);
            
            if ($stmt_insert->execute()) {
                $message = "Solicitud enviada. Un administrador aprobará tu cuenta.";
                $message_type = "warning";
            } else {
                $message = "Error al procesar la solicitud.";
                $message_type = "danger";
            }
        }
    }
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
        <li class="breadcrumb-item active" aria-current="page">Registro CEO</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm mt-3">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4">Formulario de registro CEO</h4>
                
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> p-2 text-center"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="registro_ceo.php" method="POST" class="row g-3">
                    <div class="col-12">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre del Representante" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="telefono" class="form-control" placeholder="Teléfono Corporativo" required>
                    </div>
                    <div class="col-md-6">
                        <input type="email" name="email" class="form-control" placeholder="Correo Electrónico" required>
                    </div>
                    <div class="col-md-6">
                        <input type="password" name="password" class="form-control" placeholder="Contraseña" required maxlength="8">
                    </div>
                    <div class="col-md-6">
                        <input type="password" name="password_confirm" class="form-control" placeholder="Repetir contraseña" required maxlength="8">
                    </div>
                    <div class="col-12 mt-4 text-center">
                        <button type="submit" class="btn btn-primary px-5">Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>