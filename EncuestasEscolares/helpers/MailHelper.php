<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailHelper {
    public static function enviarCorreoOTP($para, $nombre, $codigo) {
        $config = require __DIR__ . '/../config/mail.php';
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $config['port'];

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($para, $nombre);

            $mail->isHTML(true);
            $mail->Subject = 'Tu código de acceso - Encuestas Estudiantiles';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Hola, $nombre 👋</h2>
                    <p>Recibimos una solicitud para iniciar sesión en el sistema de <strong>Encuestas Estudiantiles</strong>.</p>
                    <p>Tu código temporal es:</p>
                    <p style='font-size: 24px; font-weight: bold; color: #1a237e;'>$codigo</p>
                    <p>Este código es válido por <strong>10 minutos</strong> y solo puede usarse una vez.</p>
                    <hr>
                    <p style='font-size: 13px; color: #777;'>Si tú no solicitaste este acceso, ignora este mensaje o contáctanos de inmediato.</p>
                    <p style='font-size: 13px; color: #777;'>Gracias por usar nuestro sistema.</p>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: {$mail->ErrorInfo}");
            return false;
        }
    }

    public static function enviarCorreoDesbloqueo($para, $nombre, $token) {
        $config = require __DIR__ . '/../config/mail.php';
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $config['port'];

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($para, $nombre);

            $mail->isHTML(true);
            $mail->Subject = 'Desbloqueo de IP - Encuestas Estudiantiles';

            $mail->Body = "
                <h3>Hola $nombre</h3>
                <p>Recibimos una solicitud para desbloquear tu acceso al sistema de encuestas estudiantiles.</p>
                <p>Tu token de desbloqueo es:</p>
                <p style='font-size: 20px; font-weight: bold;'>$token</p>
                <p>Este token es válido por 30 minutos.</p>
                <p>Si tú no solicitaste esto, ignora este mensaje o contacta con el administrador.</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo de desbloqueo: {$mail->ErrorInfo}");
            return false;
        }
    }
}
