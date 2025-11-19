<?php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Servicio simple para manejo de formularios de contacto
 * Envía correos electrónicos usando PHPMailer
 */
class SimpleContactService {
    
    /**
     * Envía un correo de contacto
     * 
     * @param string $name Nombre del remitente
     * @param string $email Correo del remitente
     * @param string $subject Asunto del mensaje
     * @param string $message Contenido del mensaje
     * @return bool True si se envió correctamente, false en caso contrario
     */
    public function sendContactEmail($name, $email, $subject, $message) {
        try {
            // Usar PHPMailer directamente
            return $this->sendWithPHPMailer($name, $email, $subject, $message);
            
        } catch (\Exception $e) {
            error_log("Error en SimpleContactService: " . $e->getMessage());
            return false;
        }
    }
    
    
    /**
     * Construye el cuerpo del mensaje de correo
     */
    private function buildEmailBody($name, $email, $subject, $message) {
        $body = "=== NUEVO MENSAJE DE CONTACTO ===\n\n";
        $body .= "Fecha: " . date('d/m/Y H:i:s') . "\n";
        $body .= "Nombre: " . $name . "\n";
        $body .= "Correo: " . $email . "\n";
        $body .= "Asunto: " . $subject . "\n\n";
        $body .= "Mensaje:\n";
        $body .= "----------------------------------------\n";
        $body .= $message . "\n";
        $body .= "----------------------------------------\n\n";
        $body .= "Este mensaje fue enviado desde el formulario de contacto de AlquiVenta.\n";
        
        return $body;
    }
    
    /**
     * Intenta enviar el correo usando PHPMailer
     */
    private function sendWithPHPMailer($name, $email, $subject, $message) {
        // Cargar autoloader de Composer si no está cargado
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            } else {
                error_log("PHPMailer no está disponible. Asegúrate de que esté instalado via Composer.");
                return false;
            }
        }
        
        $mail = null;
        try {
            require_once __DIR__ . '/../../config/email.php';
            
            $mail = new PHPMailer(true);
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = \EmailConfig::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = \EmailConfig::SMTP_USERNAME;
            $mail->Password = \EmailConfig::SMTP_PASSWORD;
            
            // Configurar SMTPSecure según el puerto
            // Para puerto 587 usar 'tls', para puerto 465 usar 'ssl'
            if (\EmailConfig::SMTP_PORT == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
            } elseif (\EmailConfig::SMTP_PORT == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 'ssl'
            } else {
                $mail->SMTPSecure = \EmailConfig::SMTP_SECURE ? PHPMailer::ENCRYPTION_STARTTLS : false;
            }
            
            $mail->Port = \EmailConfig::SMTP_PORT;
            $mail->CharSet = \EmailConfig::CHARSET;
            
            // Configuración adicional para Gmail
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Remitentes
            // IMPORTANTE: Gmail requiere que el FROM sea el mismo que el SMTP_USERNAME
            // o un alias verificado. Usamos SMTP_USERNAME como FROM para evitar problemas.
            $mail->setFrom(\EmailConfig::SMTP_USERNAME, \EmailConfig::FROM_NAME);
            $mail->addReplyTo($email, $name);
            $mail->addAddress(\EmailConfig::CONTACT_EMAIL);
            
            // Contenido
            $mail->isHTML(false);
            $mail->Subject = "Nuevo mensaje de contacto: " . $subject;
            $mail->Body = $this->buildEmailBody($name, $email, $subject, $message);
            
            // Enviar correo
            $mail->send();
            
            return true;
            
        } catch (Exception $e) {
            $errorMsg = "Error con PHPMailer: " . $e->getMessage();
            if ($mail && !empty($mail->ErrorInfo)) {
                $errorMsg .= " | ErrorInfo: " . $mail->ErrorInfo;
            }
            error_log($errorMsg);
            return false;
        }
    }
    
    /**
     * Guarda el mensaje en un archivo de log (como respaldo)
     */
    public function saveContactMessage($name, $email, $subject, $message) {
        // Ruta correcta: desde app/Helpers/ vamos a la raíz del proyecto
        $logFile = __DIR__ . '/../../logs/contact_messages.log';
        $logDir = dirname($logFile);
        
        // Crear directorio de logs si no existe
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = date('Y-m-d H:i:s') . " | " . $name . " | " . $email . " | " . $subject . " | " . $message . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
?>
