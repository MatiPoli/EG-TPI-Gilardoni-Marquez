<?php
require '../includes/sesiones.php';
require '../includes/conexion.php';
checkLoggedIn(true);
$title = "Registro CEO | ViajAir";
require '../includes/header.php'; 

$message = '';
$message_type = '';
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW);
    $email = filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW);
    $phone = filter_input(INPUT_POST, 'telefono', FILTER_UNSAFE_RAW);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password_confirm = filter_input(INPUT_POST, 'password_confirm', FILTER_UNSAFE_RAW);
    $name = is_string($name) ? trim($name) : '';
    $email = is_string($email) ? trim($email) : '';
    $phone = is_string($phone) ? trim($phone) : '';
    $password = is_string($password) ? $password : '';
    $password_confirm = is_string($password_confirm) ? $password_confirm : '';
    $account_type = 'ceo';

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
            
            $stmt_insert = $conexion->prepare("INSERT INTO USUARIOS (nombreUsuario, claveUsuario, tipoUsuario, estadoUsuario, codAerolinea, emailUsuario, telefonoUsuario) VALUES (?, ?, ?, 'pendiente', NULL, ?, ?)");
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
        <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page">Registro CEO</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm mt-3">
            <div class="card-body p-4">
                <h1 class="h4 card-title text-center mb-4">Formulario de registro CEO</h1>
                
                <?php if($message): ?>
                    <div id="registro-ceo-mensaje" class="alert alert-<?= htmlspecialchars($message_type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> p-2 text-center" role="alert" aria-live="assertive"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form action="registro_ceo.php" method="POST" class="row g-3">
                    <div class="col-12">
                        <label for="nombre" class="form-label">Nombre y apellido <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="text" id="nombre" name="nombre" class="form-control" maxlength="100" autocomplete="name" value="<?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required aria-required="true" <?= $message ? 'aria-describedby="registro-ceo-mensaje"' : ''; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono corporativo <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" maxlength="20" pattern="[0-9+(). -]{6,20}" inputmode="tel" autocomplete="tel" value="<?= htmlspecialchars($phone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required aria-required="true" <?= $message ? 'aria-describedby="registro-ceo-mensaje"' : ''; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Correo electrónico <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="email" id="email" name="email" class="form-control" maxlength="100" autocomplete="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required aria-required="true" <?= $message ? 'aria-describedby="registro-ceo-mensaje"' : ''; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Contraseña <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required aria-required="true" minlength="8" maxlength="8" <?= $message ? 'aria-describedby="registro-ceo-mensaje"' : ''; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirm" class="form-label">Repetir contraseña <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" autocomplete="new-password" required aria-required="true" minlength="8" maxlength="8" <?= $message ? 'aria-describedby="registro-ceo-mensaje"' : ''; ?>>
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