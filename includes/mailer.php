<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
function enviarCorreo($destinatario, $nombreDestinatario, $asunto, $cuerpoHtml) {
    $envPath = dirname(__DIR__) . '/.env';
    $env = file_exists($envPath) ? parse_ini_file($envPath) : [];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $env['MAIL_HOST'] ?? 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['MAIL_USER'] ?? '';
        $mail->Password   = $env['MAIL_PASS'] ?? '';
        $mail->Port       = (int)($env['MAIL_PORT'] ?? 2525);
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';

        $remitenteCorreo = $env['MAIL_FROM'] ?? 'no-reply@viajair.com';
        $remitenteNombre = $env['MAIL_FROM_NAME'] ?? 'ViajAir';

        $mail->setFrom($remitenteCorreo, $remitenteNombre);
        $mail->addAddress($destinatario, $nombreDestinatario);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpoHtml;
        $mail->AltBody = strip_tags($cuerpoHtml);

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>