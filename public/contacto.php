<?php
require '../includes/sesiones.php';
require '../includes/conexion.php';
require_once '../includes/mailer.php';

if (isLoggedIn()){
    $stmt = $conexion->prepare("SELECT emailUsuario FROM USUARIOS where codUsuario = ?;");
    $stmt->bind_param("i", $_SESSION['codUsuario']);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();
}


$alerta = null;
if (isset($_GET['consulta_enviada']) && $_GET['consulta_enviada'] === '1') {
    $alerta = [
        'tipo' => 'success',
        'mensaje' => 'El correo se envió correctamente.'
    ];
}

$errores = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $email = isset($_POST['email']) && is_string($_POST['email'])
    ? trim($_POST['email'])
    : '';
    $consulta = isset($_POST['consulta']) && is_string($_POST['consulta'])
    ? trim($_POST['consulta'])
    : '';

    if ($email === ''){
        $errores['email']= 'La dirección de correo es obligatoria.';
    } elseif (mb_strlen($email, 'UTF-8') > 100) {
        $errores['email'] = 'El email no puede superar los 100 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El formato del email no es válido.';
    }

    if ($consulta === '') {
        $errores['consulta'] = 'La consulta es obligatoria.';
    } elseif (mb_strlen($consulta, 'UTF-8') > 2000) {
        $errores['consulta'] = 'La consulta no puede superar los 2000 caracteres.';
    }

    
    if(empty($errores)){
        $stmt = $conexion->prepare("SELECT emailUsuario FROM USUARIOS where tipoUsuario = 'administrador';");
        $stmt->execute();
        $stmt->bind_result($emailAdmin);
        $stmt->fetch();
        $stmt->close();    
        
        $asunto = "Nueva Consulta";

        $headersmail = "MIME-Version: 1.0\r\n";
        $headersmail .= "Content-type: text/html; charset=utf-8\r\n";
        $headersmail .= "From: Contacto ViajAir <contacto@viajair.com>\r\n";

        $emailSeguro = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $consultaSegura = nl2br(
        htmlspecialchars($consulta, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );

        $cuerpomail = "
        <html>
        <head>
            <link href='https://cdn.jsdelivr.net/npm/bootswatch@5.3.2/dist/zephyr/bootstrap.min.css' rel='stylesheet'>
            <title>Nueva Consulta de Usuario</title>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <meta charset='UTF-8'>
        </head>
        <body style='margin: 0;'>
            <div style='background-color: #3459e6;'>
                <img src='https://i.imgur.com/d7TXzck.png' height='46'>
            </div>
            <div class='container mt-2 mb-5 row justify-content-center'>
                <div class='col-md-10 col-lg-8'>
                    <h1 class='mb-4 text-center text-black'>¡Hola! Tenes una nueva consulta</h1>
                    <div>
                        <h2 class='mb-2'>". $emailSeguro . " pregunta:</h2>
                    </div>
                    <div>
                        <p>". $consultaSegura   ."</p>        
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        if (enviarCorreo($emailAdmin, 'Administrador', $asunto, $cuerpomail)) {
            header('Location: /tpi/public/contacto.php?consulta_enviada=1');
            exit;
        } else {
            $alerta = [
                'tipo' => 'danger',
                'mensaje' => 'No se pudo enviar el correo. Intentá nuevamente.'
            ];
        }
    }
}

$pagina = 'Contacto';
$title = $pagina . " | ViajAir";
require '../includes/header.php';
?>

<div class="container mt-2 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/tpi/index.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contacto</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <h1 class="mb-4 text-center text-black">Contacto</h1>

            <div >
                <?php if ($alerta !== null): ?>
                    <div class="alert mt-4 alert-<?= htmlspecialchars($alerta['tipo']) ?>" role="alert">
                        <?= htmlspecialchars($alerta['mensaje']) ?>
                    </div>
                <?php endif; ?>
                <form action="/tpi/public/contacto.php" method="POST">
                    <p>Los campos marcados con <span aria-hidden="true">*</span> son obligatorios.</p>
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Tu dirección de correo: <span aria-hidden="true">*</span>
                        </label>
                        <input 
                            type="email"
                            class="form-control"
                            id="email" name="email"
                            <?= isset($errores['email']) ? 'aria-describedby="emailError" aria-invalid="true"' : '' ?>
                            placeholder="nombre@ejemplo.com"
                            required 
                            value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            maxlength="100"
                            >
                        <?php if(isset($errores['email'])): ?>
                            <div id="emailError" class="text-danger">
                                <?= htmlspecialchars($errores['email']) ?>
                            </div>
                        <?php endif;?>
                    </div>
                    <div class="mb-3">
                        <label for="consulta" class="form-label">
                            Tu consulta: <span aria-hidden="true">*</span>
                        </label>
                        <textarea
                            class="form-control"
                            id="consulta"
                            name="consulta"
                            rows="5"
                            required
                            maxlength="2000"
                            ><?= htmlspecialchars($consulta ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php if(isset($errores['consulta'])): ?>
                            <div class="text-danger">
                                <?= htmlspecialchars($errores['consulta']) ?>
                            </div>
                        <?php endif;?>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                    
                </form>
                
                <h2 class="my-4">
                    Antes de contactarnos...
                </h2>
                <p>
                    Te recomendamos que leas las <a href="/tpi/public/faq.php">Preguntas Frecuentes</a>, donde vas a encontrar respuestas a consultas que otros usuarios también tuvieron.
                </p>
                <p>
                    Si no encontraste la respuesta que buscabas, escribinos y te responderemos a la brevedad.
                </p>
            </div>
        </div>
    </div>
</div>

<?php 

require '../includes/footer.php'; 
?>