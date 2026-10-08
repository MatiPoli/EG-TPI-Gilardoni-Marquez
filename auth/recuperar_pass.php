<?php
require '../includes/conexion.php'; 
require '../includes/header.php'; 

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $conexion->prepare("SELECT codUsuario, nombreUsuario FROM USUARIOS WHERE emailUsuario = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        $token = bin2hex(random_bytes(32));
        $id = $user['codUsuario'];

        $stmt_update = $conexion->prepare("UPDATE USUARIOS SET tokenRecuperacion = ? WHERE codUsuario = ?");
        $stmt_update->bind_param("si", $token, $id);
        $stmt_update->execute();
        
        $recovery_link = "http://localhost/tpi/auth/restablecer.php?token=$token"; 

        $subject = "Recuperación de Contraseña - ViajAir";
        $body = "
        <html>
        <body>
            <h2>ViajAir</h2>
            <p>Para cambiar tu contraseña, haz clic en el siguiente enlace:</p>
            <p><a href='$recovery_link'>Cambiar contraseña</a></p>
        </body>
        </html>
        ";
        
        $headers  = "MIME-Version: 1.0\r\n"; 
        $headers .= "Content-type: text/html; charset=utf-8\r\n"; 
        $headers .= "From: Soporte ViajAir <soporte@viajair.com>\r\n"; 

        @mail($email, $subject, $body, $headers); 

        $message = "Correo enviado con instrucciones.";
        $message_type = "success";
    } else {
        $message = "Si el correo es válido, recibirás un enlace.";
        $message_type = "info";
    }
}
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
        <li class="breadcrumb-item active" aria-current="page">Recuperación de Contraseña</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm mt-3">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4">Formulario de recuperación de contraseña</h4>
                
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> p-2 text-center"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="recuperar_pass.php" method="POST">
                    <div class="mb-4">
                        <input type="email" name="email" class="form-control" placeholder="Correo electrónico" required>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>