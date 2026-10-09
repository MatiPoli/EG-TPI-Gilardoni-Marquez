<?php
require '../includes/sesiones.php';
checkLoggedIn(true);
require '../includes/conexion.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conexion->prepare("SELECT codUsuario, nombreUsuario, tipoUsuario, claveUsuario, estadoUsuario FROM USUARIOS WHERE emailUsuario = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
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

                if ($user['tipoUsuario'] == 'administrador') {
                    header("Location: /tpi/admin/dashboard.php");
                } elseif ($user['tipoUsuario'] == 'ceo') {
                    header("Location: /tpi/ceo/dashboard.php");
                } else {
                    header("Location: /tpi/pasajero/reservas.php");
                }
                exit();
            }
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "El correo no está registrado.";
    }
}

require '../includes/header.php'; 
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/tpi/index.php">Portada</a></li>
        <li class="breadcrumb-item active" aria-current="page">Inicio de Sesión</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm mt-3">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4">Formulario de inicio de sesión</h4>
                
                <?php if($error): ?>
                    <div class="alert alert-danger p-2 text-center"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Correo electrónico" required>
                    </div>
                    <div class="mb-4">
                        <input type="password" class="form-control" name="password" placeholder="Contraseña" required maxlength="8">
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