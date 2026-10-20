<?php
require '../includes/sesiones.php';
require '../includes/conexion.php'; 

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

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
        
        $recovery_link = "http://localhost/tpi/auth/restablecer.php?token=" . urlencode($token); 

        $subject = "Recuperación de Contraseña - ViajAir";
        $body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2>ViajAir</h2>
            <p>Hola <strong>" . htmlspecialchars($user['nombreUsuario']) . "</strong>,</p>
            <p>Recibimos una solicitud para restablecer tu contraseña. Hacé clic en el siguiente enlace para continuar:</p>
            <p style='margin: 20px 0;'>
                <a href='$recovery_link' style='background-color: #0d6efd; color: white; padding: 10px 18px; text-decoration: none; border-radius: 4px; font-weight: bold;'>
                    Restablecer mi contraseña
                </a>
            </p>
            <p><small>Si no realizaste esta solicitud, podés ignorar este correo de forma segura.</small></p>
        </body>
        </html>
        ";
        
        $headers  = "MIME-Version: 1.0\r\n"; 
        $headers .= "Content-type: text/html; charset=utf-8\r\n"; 
        $headers .= "From: Soporte ViajAir <soporte@viajair.com>\r\n"; 

        @mail($email, $subject, $body, $headers); 

        $message = "Si el correo ingresado se encuentra registrado, recibirás un enlace con las instrucciones para restablecer tu clave.";
        $message_type = "success";
    } else {
        $message = "Si el correo ingresado se encuentra registrado, recibirás un enlace con las instrucciones para restablecer tu clave.";
        $message_type = "info";
    }
}

$title = "Recuperar Contraseña | ViajAir";
require '../includes/header.php'; 
?>


<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page">Recuperación de Contraseña</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h1 class="h5 mb-0 text-white">Recuperación de Contraseña</h1>
            </div>

            <div class="card-body p-4 p-md-5">
                
                <p class="text-muted text-center mb-4">
                    Ingresá la dirección de correo electrónico asociada a tu cuenta en ViajAir. Te enviaremos un enlace seguro para que puedas generar una nueva contraseña.
                </p>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> text-center p-3 mb-4" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="/tpi/auth/recuperar_pass.php" method="POST">
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold text-dark">
                            Correo Electrónico <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <input type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                placeholder="nombre@ejemplo.com" 
                                required 
                                aria-required="true"
                                aria-describedby="email-ayuda">
                        <div id="email-ayuda" class="form-text small">
                            Debe ser la misma casilla de correo con la que te diste de alta.
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold py-2">
                            Enviar Enlace de Recuperación
                        </button>
                        <a href="/tpi/auth/login.php" class="btn btn-outline-secondary">
                            Volver al Inicio de Sesión
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<?php require '../includes/footer.php'; ?>