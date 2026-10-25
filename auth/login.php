<?php
require '../includes/sesiones.php';
checkLoggedIn(true);
require '../includes/conexion.php'; 

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo electrónico válido.';
    } elseif ($password === '' || strlen($password) > 8) {
        $error = 'Ingresa una contraseña válida de hasta 8 caracteres.';
    } else {
        $stmt = $conexion->prepare("SELECT codUsuario, nombreUsuario, tipoUsuario, claveUsuario, estadoUsuario, codAerolinea FROM USUARIOS WHERE emailUsuario = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    if (isset($result) && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        $hashed_password = md5($password);
        
        if ($hashed_password === $user['claveUsuario']) {
            if ($user['estadoUsuario'] === 'pendiente') {
                if ($user['tipoUsuario'] === 'ceo') {
                    $error = "Cuenta pendiente de aprobación.";
                } else {
                    $error = "Debes validar tu correo electrónico.";
                }
            } elseif ($user['estadoUsuario'] === 'suspendido') {
                $error = "Esta cuenta ha sido suspendida.";
            } else {
                $_SESSION['codUsuario'] = $user['codUsuario'];
                $_SESSION['nombreUsuario'] = $user['nombreUsuario'];
                $_SESSION['tipoUsuario'] = $user['tipoUsuario'];
                
                if (isset($user['codAerolinea'])) {
                    $_SESSION['codAerolinea'] = $user['codAerolinea'];
                }

                if ($user['tipoUsuario'] == 'administrador') {
                    header("Location: /tpi/admin/dashboard.php");
                } elseif ($user['tipoUsuario'] == 'ceo') {
                    header("Location: /tpi/ceo/dashboard.php");
                } else {
                    header("Location: /tpi/pasajero/perfil.php");
                }
                exit();
            }
        } else {
            $error = "ContraseÃ±a incorrecta.";
        }
    } else {
        $error = "El correo no estÃ¡ registrado.";
    }
}

require '../includes/header.php'; 
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page">Inicio de Sesión</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm mt-3">
            <div class="card-body p-4">
                <h1 class="h4 card-title text-center mb-4">Formulario de inicio de sesión</h1>
                
                <?php if($error): ?>
                    <div id="login-error" class="alert alert-danger p-2 text-center" role="alert" aria-live="assertive"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="email" id="email" class="form-control" name="email" maxlength="100" autocomplete="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required <?= $error ? 'aria-describedby="login-error"' : ''; ?>>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">(campo obligatorio)</span></label>
                        <input type="password" id="password" class="form-control" name="password" autocomplete="current-password" required maxlength="8" <?= $error ? 'aria-describedby="login-error"' : ''; ?>>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Ingresar</button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <a href="/tpi/auth/recuperar_pass.php" class="text-decoration-none d-block mb-2">Recuperar contraseña</a>
                    <a href="/tpi/auth/registro.php" class="text-decoration-none d-block">Registrarse</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>