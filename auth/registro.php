<?php
require '../includes/sesiones.php';
require '../includes/conexion.php';
require '../includes/mailer.php';
checkLoggedIn(true);
$title = "Registro | ViajAir";
require '../includes/header.php'; 

$message = '';
$message_type = '';
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = is_string($_POST['nombre'] ?? null) ? trim($_POST['nombre']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $phone = is_string($_POST['telefono'] ?? null) ? trim($_POST['telefono']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $password_confirm = is_string($_POST['password_confirm'] ?? null) ? $_POST['password_confirm'] : '';
    $account_type = 'usuario';

    if ($name === '' || mb_strlen($name, 'UTF-8') > 100) {
        $message = "Ingresá un nombre de hasta 100 caracteres.";
        $message_type = "danger";
    } elseif ($email === '' || mb_strlen($email, 'UTF-8') > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Ingresá un correo electrónico válido de hasta 100 caracteres.";
        $message_type = "danger";
    } elseif ($phone === '' || !preg_match('/^[0-9+\s().-]{6,20}$/', $phone)) {
        $message = "Ingresá un teléfono válido de entre 6 y 20 caracteres.";
        $message_type = "danger";
    } elseif (strlen($password) < 8 || strlen($password) > 8) {
        $message = "La contraseña debe tener exactamente 8 caracteres.";
        $message_type = "danger";
    } elseif ($password !== $password_confirm) {
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
                $validation_link = "$app_url/tpi//auth/validar.php?id=$new_id&hash=$hash"; 

                $subject = "Valida tu cuenta en ViajAir";

                $body = "
                    <html>
                    <head>
                        <link href='https://cdn.jsdelivr.net/npm/bootswatch@5.3.2/dist/zephyr/bootstrap.min.css' rel='stylesheet'>
                        <title>Activación de cuenta - ViajAir</title>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <meta charset='UTF-8'>
                    </head>
                    <body style='margin: 0; font-family: Arial, sans-serif;'>
                        <div style='background-color: #3459e6; padding: 10px 20px;'>
                            <img src='https://i.imgur.com/d7TXzck.png' height='46' alt='ViajAir'>
                        </div>

                        <div style='max-width: 600px; margin: 30px auto; padding: 0 20px;'>
                            <h1 style='margin-bottom: 24px; text-align: center; color: #212529;'>
                                ¡Bienvenido a ViajAir!
                            </h1>

                            <p style='font-size: 16px; color: #212529;'>
                                Gracias por registrarte. Para activar tu cuenta, haz clic en el siguiente enlace:
                            </p>

                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='$validation_link'
                                style='display: inline-block; padding: 12px 24px; background-color: #3459e6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                                    Validar mi cuenta
                                </a>
                            </div>

                            <p style='font-size: 14px; color: #6c757d;'>
                                Si no creaste una cuenta en ViajAir, puedes ignorar este correo.
                            </p>
                        </div>
                    </body>
                    </html>
                ";
                
                $headers  = "MIME-Version: 1.0\r\n"; 
                $headers .= "Content-type: text/html; charset=utf-8\r\n"; 
                $headers .= "From: ViajAir <no-reply@viajair.com>\r\n"; 

                $correo_enviado = enviarCorreo($email, $name, $subject, $body);

                if ($correo_enviado) {
                    $message = "Registro exitoso. Revisá tu correo para activar tu cuenta.";
                    $message_type = "success";
                } else {
                    $message = "Te registraste correctamente, pero no se pudo enviar el correo de activación.";
                    $message_type = "warning";
                }
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
        <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page">Registro</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm mt-3">
            <div class="card-body p-4">
                <h1 class="h4 card-title text-center mb-4">Formulario de registro</h1>
                
                <?php if($message): ?>
                    <div id="registro-mensaje" class="alert alert-<?= htmlspecialchars($message_type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> p-2 text-center" role="alert" aria-live="assertive"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form action="registro.php" method="POST" class="row g-3">
                    <div class="col-12">
                        <label for="nombre" class="form-label">Nombre completo <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="text" id="nombre" name="nombre" class="form-control" maxlength="100" autocomplete="name" value="<?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required <?= $message ? 'aria-describedby="registro-mensaje"' : ''; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" maxlength="20" pattern="[0-9+(). -]{6,20}" inputmode="tel" autocomplete="tel" value="<?= htmlspecialchars($phone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required <?= $message ? 'aria-describedby="registro-mensaje"' : ''; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Correo electrónico <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="email" id="email" name="email" class="form-control" maxlength="100" autocomplete="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required <?= $message ? 'aria-describedby="registro-mensaje"' : ''; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Contraseña <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required minlength="8" maxlength="8" <?= $message ? 'aria-describedby="registro-mensaje"' : ''; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirm" class="form-label">Repetir contraseña <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" autocomplete="new-password" required minlength="8" maxlength="8" <?= $message ? 'aria-describedby="registro-mensaje"' : ''; ?>>
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