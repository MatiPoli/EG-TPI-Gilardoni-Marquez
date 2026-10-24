<?php
require '../includes/sesiones.php';
require '../includes/conexion.php'; 
require '../includes/mailer.php';
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
        
        $recovery_link = "$app_url/tpi/auth/restablecer.php?token=" . urlencode($token);

        $subject = "Recuperación de Contraseña - ViajAir";
        $body = "
            <html>
            <head>
                <link href='https://cdn.jsdelivr.net/npm/bootswatch@5.3.2/dist/zephyr/bootstrap.min.css' rel='stylesheet'>
                <title>Restablecer contraseña - ViajAir</title>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <meta charset='UTF-8'>
            </head>
            <body style='margin: 0; font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='background-color: #3459e6; padding: 10px 20px;'>
                    <img src='https://i.imgur.com/d7TXzck.png' height='46' alt='ViajAir'>
                </div>

                <div style='max-width: 600px; margin: 30px auto; padding: 0 20px;'>
                    <h1 style='margin-bottom: 24px; text-align: center; color: #212529;'>
                        Restablecer contraseña
                    </h1>

                    <p>Hola <strong>" . htmlspecialchars($user['nombreUsuario'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>

                    <p>
                        Recibimos una solicitud para restablecer tu contraseña.
                        Hacé clic en el siguiente botón para continuar:
                    </p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . htmlspecialchars($recovery_link, ENT_QUOTES, 'UTF-8') . "'
                        style='display: inline-block; padding: 12px 24px; background-color: #3459e6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                            Restablecer mi contraseña
                        </a>
                    </div>

                    <p style='font-size: 14px; color: #6c757d;'>
                        Si no realizaste esta solicitud, podés ignorar este correo de forma segura.
                    </p>
                </div>
            </body>
            </html>
        ";
        
        $headers  = "MIME-Version: 1.0\r\n"; 
        $headers .= "Content-type: text/html; charset=utf-8\r\n"; 
        $headers .= "From: Soporte ViajAir <soporte@viajair.com>\r\n"; 

        enviarCorreo($email, $user['nombreUsuario'], $subject, $body);

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
                    <div class="alert alert-<?= $message_type; ?> text-center p-3 mb-4" role="alert">
                        <?= $message; ?>
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