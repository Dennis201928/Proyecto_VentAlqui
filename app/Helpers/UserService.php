<?php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Core\EmailConfig;

/**
 * Servicio para envío de correos con PHPMailer (Contacto + Registro)
 */
class UserService {

    /**
     * ✅ Envía un correo de contacto al ADMIN (EmailConfig::CONTACT_EMAIL)
     * NOTA: En tu código original usabas $subject sin existir. Aquí ya está corregido.
     */
    public function sendContactUser($name, $email, $phone, $address) {
        try {
            $subject = "Nuevo mensaje de contacto - AlquiVenta";
            $body = $this->buildContactEmailBody($name, $email, $phone, $address);

            return $this->sendWithPHPMailer([
                'from_email' => EmailConfig::SMTP_USERNAME,
                'from_name'  => EmailConfig::FROM_NAME,
                'replyto_email' => $email,
                'replyto_name'  => $name,
                'to_email'   => EmailConfig::CONTACT_EMAIL,
                'to_name'    => 'Administrador',
                'subject'    => $subject,
                'body'       => $body,
                'is_html'    => false
            ]);

        } catch (\Exception $e) {
            error_log("Error en UserService::sendContactUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ Envía correo de bienvenida/confirmación al usuario (registro)
     */
    public function sendRegisterWelcomeEmail($name, $email) {
        try {
            $subject = "¡Registro exitoso - AlquiVenta!";
            $body = $this->buildRegisterEmailBodyHtml($name);

            return $this->sendWithPHPMailer([
                'from_email' => EmailConfig::SMTP_USERNAME,
                'from_name'  => EmailConfig::FROM_NAME,
                'replyto_email' => EmailConfig::SMTP_USERNAME,
                'replyto_name'  => EmailConfig::FROM_NAME,
                'to_email'   => $email,
                'to_name'    => $name,
                'subject'    => $subject,
                'body'       => $body,
                'is_html'    => true
            ]);

        } catch (\Exception $e) {
            error_log("Error en UserService::sendRegisterWelcomeEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ Envía correo al ADMIN con los datos del usuario registrado (SIN contraseña)
     */
    public function sendRegisterInfoToAdmin(array $userData) {
        try {
            $subject = "Nuevo usuario registrado - AlquiVenta";
            $body = $this->buildRegisterAdminEmailBodyHtml($userData);

            return $this->sendWithPHPMailer([
                'from_email' => EmailConfig::SMTP_USERNAME,
                'from_name'  => EmailConfig::FROM_NAME,
                'replyto_email' => EmailConfig::SMTP_USERNAME,
                'replyto_name'  => EmailConfig::FROM_NAME,
                'to_email'   => EmailConfig::CONTACT_EMAIL, // correo del admin
                'to_name'    => 'Administrador',
                'subject'    => $subject,
                'body'       => $body,
                'is_html'    => true
            ]);

        } catch (\Exception $e) {
            error_log("Error en UserService::sendRegisterInfoToAdmin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * (Opcional) Guarda mensaje en archivo como respaldo
     */
    public function saveContactMessage($name, $email, $phone, $address) {
        $logFile = __DIR__ . '/../../logs/contact_messages.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logEntry = date('Y-m-d H:i:s') . " | " . $name . " | " . $email . " | " . $phone . " | " . $address . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * -------------------------
     * Construcción de mensajes
     * -------------------------
     */

    private function buildContactEmailBody($name, $email, $phone, $address) {
        $body  = "=== NUEVO MENSAJE DE CONTACTO ===\n\n";
        $body .= "Fecha: " . date('d/m/Y H:i:s') . "\n";
        $body .= "Nombre: " . $name . "\n";
        $body .= "Correo: " . $email . "\n";
        $body .= "Teléfono: " . $phone . "\n";
        $body .= "Dirección: " . $address . "\n\n";
        return $body;
    }

    private function buildRegisterEmailBodyHtml($name) {
        $safeName = htmlspecialchars($name ?? 'Usuario', ENT_QUOTES, 'UTF-8');

        return '
        <div style="font-family: Arial, sans-serif; max-width: 640px; margin: 0 auto; line-height: 1.5;">
            <h2 style="margin: 0 0 12px;">¡Bienvenido/a ' . $safeName . '!</h2>
            <p style="margin: 0 0 10px;">Tu registro en <b>AlquiVenta</b> se realizó correctamente.</p>
            <p style="margin: 0 0 10px;">Ya puedes iniciar sesión y acceder a la plataforma.</p>
            <hr style="border:none;border-top:1px solid #e5e5e5;margin:16px 0;">
            <p style="margin: 0; color:#666; font-size: 13px;">
                Si no realizaste este registro, puedes ignorar este correo.
            </p>
        </div>';
    }

    private function buildRegisterAdminEmailBodyHtml(array $userData) {
        $nombre   = htmlspecialchars($userData['nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $apellido = htmlspecialchars($userData['apellido'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $email    = htmlspecialchars($userData['email'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $telefono = htmlspecialchars($userData['telefono'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $direccion= htmlspecialchars($userData['direccion'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $fecha    = date('d/m/Y H:i:s');

        return '
        <div style="font-family: Arial, sans-serif; max-width: 720px; margin: 0 auto; line-height: 1.5;">
            <h2 style="margin: 0 0 12px;">Nuevo usuario registrado</h2>
            <p style="margin: 0 0 14px;">Se ha registrado un nuevo usuario en <b>AlquiVenta</b> con los siguientes datos:</p>

            <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width:100%;">
                <tr><th align="left" style="background:#f5f5f5;">Nombre</th><td>' . $nombre . '</td></tr>
                <tr><th align="left" style="background:#f5f5f5;">Apellido</th><td>' . $apellido . '</td></tr>
                <tr><th align="left" style="background:#f5f5f5;">Email</th><td>' . $email . '</td></tr>
                <tr><th align="left" style="background:#f5f5f5;">Teléfono</th><td>' . $telefono . '</td></tr>
                <tr><th align="left" style="background:#f5f5f5;">Dirección</th><td>' . $direccion . '</td></tr>
                <tr><th align="left" style="background:#f5f5f5;">Fecha de registro</th><td>' . $fecha . '</td></tr>
            </table>

            <p style="margin: 14px 0 0; color:#666; font-size: 13px;">
                Nota: Por seguridad, la contraseña no se envía por correo.
            </p>
        </div>';
    }

    /**
     * -------------------------
     * Envío con PHPMailer (SMTP)
     * -------------------------
     * $payload = [
     *   from_email, from_name,
     *   replyto_email, replyto_name,
     *   to_email, to_name,
     *   subject, body,
     *   is_html
     * ]
     */
    private function sendWithPHPMailer(array $payload) {
        // Asegurar autoload (si no está cargado ya)
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            } else {
                error_log("PHPMailer no está disponible. Asegúrate de instalarlo con Composer.");
                return false;
            }
        }

        $mail = new PHPMailer(true);

        try {
            // SMTP
            $mail->isSMTP();
            $mail->Host       = EmailConfig::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = EmailConfig::SMTP_USERNAME;
            $mail->Password   = EmailConfig::SMTP_PASSWORD;
            $mail->Port       = EmailConfig::SMTP_PORT;
            $mail->CharSet    = EmailConfig::CHARSET;

            // TLS/SSL según puerto
            if ((int)EmailConfig::SMTP_PORT === 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ((int)EmailConfig::SMTP_PORT === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }

            // Opciones SSL (útil en localhost / algunos hosts)
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];

            // Remitentes
            $mail->setFrom($payload['from_email'], $payload['from_name']);
            $mail->addReplyTo($payload['replyto_email'], $payload['replyto_name']);
            $mail->addAddress($payload['to_email'], $payload['to_name']);

            // Contenido
            $mail->isHTML((bool)$payload['is_html']);
            $mail->Subject = $payload['subject'];
            $mail->Body    = $payload['body'];

            $mail->send();
            return true;

        } catch (Exception $e) {
            $msg = "Error con PHPMailer: " . $e->getMessage();
            if (!empty($mail->ErrorInfo)) {
                $msg .= " | ErrorInfo: " . $mail->ErrorInfo;
            }
            error_log($msg);
            return false;
        }
    }
}
