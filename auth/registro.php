<?php
require '../includes/conexion.php'; 
require '../includes/header.php'; 

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['nombre'];
    $email = $_POST['email'];
    $phone = $_POST['telefono'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $account_type = 'usuario';

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
                $new_id = $conexion->insert_id;
                $hash = md5($email . $new_id);
                $validation_link = "http://localhost/tpi/auth/validar.php?id=$new_id&hash=$hash"; 

                $subject = "Valida tu cuenta en ViajAir";
                $body = "
                <html>
                <body>
                    <h2>ViajAir</h2>
                    <p>Para activar tu cuenta, haz clic en el siguiente enlace:</p>
                    <p><a href='$validation_link'>Validar mi cuenta</a></p>
                </body>
                </html>
                ";
                
                $headers  = "MIME-Version: 1.0\r\n"; 
                $headers .= "Content-type: text/html; charset=utf-8\r\n"; 
                $headers .= "From: ViajAir <no-reply@viajair.com>\r\n"; 

                @mail($email, $subject, $body, $headers); 

                $message = "Registro exitoso. Revisa tu correo.";
                $message_type = "success";
            } else {
                $message = "Error al registrar. Intenta nuevamente.";
                $message_type = "danger";
            }
        }
    }
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
        <li class="breadcrumb-item active" aria-current="page">Registro</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm mt-3">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4">Formulario de registro</h4>
                
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> p-2 text-center"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="registro.php" method="POST" class="row g-3">
                    <div class="col-12">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre completo" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="telefono" class="form-control" placeholder="Teléfono" required>
                    </div>
                    <div class="col-md-6">
                        <input type="email" name="email" class="form-control" placeholder="Correo electrónico" required>
                    </div>
                    <div class="col-md-6">
                        <input type="password" name="password" class="form-control" placeholder="Contraseña" required maxlength="8">
                    </div>
                    <div class="col-md-6">
                        <input type="password" name="password_confirm" class="form-control" placeholder="Repetir contraseña" required maxlength="8">
                    </div>
                    <div class="col-12 mt-4 text-center">
                        <button type="submit" class="btn btn-primary px-5">Registrarme</button>
                    </div>
                </form>
                
                <hr class="my-4">
                <div class="text-center">
                    <a href="/tpi/auth/registro_ceo.php" class="text-decoration-none">Registro para CEO</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>