<?php
require '../includes/sesiones.php';
require '../includes/conexion.php';

if (isLoggedIn()){
    $stmt = $conexion->prepare("SELECT emailUsuario FROM usuarios where codUsuario = ?;");
    $stmt->bind_param("i", $_SESSION['codUsuario']);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();
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
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="emailContacto" class="form-label">Tu dirección de correo:</label>
                        <input type="email" class="form-control" id="email" name="email" aria-describedby="emailContacto" placeholder="nombre@ejemplo.com" required value="<?php echo $email ?? ''?>">
                    </div>
                    <div class="mb-3">
                        <label for="consulta" class="form-label">Tu consulta:</label>
                        <textarea class="form-control" id="consulta" name="consulta" rows="5" required></textarea>
                    </div>
                    <div class="center-content">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
if(!isset($_SERVER['REQUEST_METHOD'])){
    exit('El formulario no fue enviado.');
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $campos = ['email', 'consulta'];
    $faltantes = [];
    foreach($campos as $campo){
        if(!isset($_POST[$campo]) || !is_string($_POST[$campo]) || trim($_POST[$campo]) ===''){
            $faltantes[] = $campo;
        }
    }
    
    if ($faltantes){
        exit('Faltan datos o son inválidos: '. implode(', ', $faltantes));
    }

    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('El formato del email no es válido.');
    }

    $consulta = trim($_POST['consulta']);
    

    $stmt = $conexion->prepare("SELECT emailUsuario FROM usuarios where tipoUsuario = 'administrador';");
    $stmt->execute();
    $stmt->bind_result($emailAdmin);
    $stmt->fetch();
    $stmt->close();    
    
    $asunto = "Nueva Consulta";

    $headersmail = "MIME-Version: 1.0\r\n";
    $headersmail .= "Content-type: text/html; charset=utf-8\r\n";
    $headersmail .= "From: Contacto ViajAir <contacto@viajair.com>\r\n";

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
            <img src='/tpi/assets/img/logo.png' height='46'>
        </div>
        <div class='container mt-2 mb-5 row justify-content-center'>
            <div class='col-md-10 col-lg-8'>
                <h1 class='mb-4 text-center text-black'>Hola! Tenes una nueva consulta</h1>
                <div>
                    <h2 class='mb-2'>". $email . " pregunta:</h2>
                </div>
                <div>
                    <p>". $consulta."</p>        
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    //TODO: Reemplazar por modal
    if (mail($emailAdmin, $asunto, $cuerpomail, $headersmail)) {
        echo "El correo se envió correctamente.";
    } else {
        echo "Error al enviar el correo.";
    }
}
require '../includes/footer.php'; 
?>