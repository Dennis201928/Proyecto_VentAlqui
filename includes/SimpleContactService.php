<?php
/**
 * Servicio simple para manejo de formularios de contacto
 * Envía correos electrónicos usando la función mail() de PHP
 */

require_once __DIR__ . '/../config/email.php';

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
            // Configurar headers del correo
            $headers = $this->buildHeaders($name, $email);
            
            // Crear el asunto del correo
            $emailSubject = "Nuevo mensaje de contacto: " . $subject;
            
            // Crear el cuerpo del mensaje
            $emailBody = $this->buildEmailBody($name, $email, $subject, $message);
            
            // Intentar enviar el correo
            if (mail(EmailConfig::CONTACT_EMAIL, $emailSubject, $emailBody, $headers)) {
                return true;
            } else {
                // Si falla con mail(), intentar con PHPMailer si está disponible
                return $this->sendWithPHPMailer($name, $email, $subject, $message);
            }
            
        } catch (Exception $e) {
            error_log("Error en SimpleContactService: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Construye los headers del correo
     */
    private function buildHeaders($name, $email) {
        $headers = "From: " . EmailConfig::FROM_NAME . " <" . EmailConfig::FROM_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . $name . " <" . $email . ">\r\n";
        $headers .= "Content-Type: " . EmailConfig::MAIL_TYPE . "; charset=" . EmailConfig::CHARSET . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        return $headers;
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
     * Intenta enviar el correo usando PHPMailer (si está disponible)
     */
    private function sendWithPHPMailer($name, $email, $subject, $message) {
        // Verificar si PHPMailer está disponible
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return false;
        }
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host = EmailConfig::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = EmailConfig::SMTP_USERNAME;
            $mail->Password = EmailConfig::SMTP_PASSWORD;
            $mail->SMTPSecure = EmailConfig::SMTP_SECURE ? 'tls' : false;
            $mail->Port = EmailConfig::SMTP_PORT;
            $mail->CharSet = EmailConfig::CHARSET;
            
            // Remitentes
            $mail->setFrom(EmailConfig::FROM_EMAIL, EmailConfig::FROM_NAME);
            $mail->addReplyTo($email, $name);
            $mail->addAddress(EmailConfig::CONTACT_EMAIL);
            
            // Contenido
            $mail->isHTML(false);
            $mail->Subject = "Nuevo mensaje de contacto: " . $subject;
            $mail->Body = $this->buildEmailBody($name, $email, $subject, $message);
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Error con PHPMailer: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Guarda el mensaje en un archivo de log (como respaldo)
     */
    public function saveContactMessage($name, $email, $subject, $message) {
        $logFile = __DIR__ . '/../logs/contact_messages.log';
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
