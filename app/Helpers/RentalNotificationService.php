<?php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Core\EmailConfig;

class RentalNotificationService {
    
    /**
     * Envía un correo de notificación cuando se agenda un alquiler
     * 
     * @param string $usuario_nombre Nombre completo del usuario
     * @param string $usuario_email Correo del usuario
     * @param string $producto_nombre Nombre de la máquina/producto
     * @param string $fecha_inicio Fecha de inicio del alquiler
     * @param string $fecha_fin Fecha de fin del alquiler
     * @param int $dias Cantidad de días que requiere la máquina
     * @return bool True si se envió correctamente, false en caso contrario
     */
    public function sendRentalNotification($usuario_nombre, $usuario_email, $producto_nombre, $fecha_inicio, $fecha_fin, $dias) {
        try {
            return $this->sendWithPHPMailer($usuario_nombre, $usuario_email, $producto_nombre, $fecha_inicio, $fecha_fin, $dias);
            
        } catch (\Exception $e) {
            error_log("Error en RentalNotificationService: " . $e->getMessage());
            return false;
        }
    }
    
    
    /**
     * Construye el cuerpo del mensaje de correo de notificación de agendamiento
     */
    private function buildEmailBody($usuario_nombre, $usuario_email, $producto_nombre, $fecha_inicio, $fecha_fin, $dias) {
        $fecha_agendamiento = date('d/m/Y H:i:s');
        
        $body = "=== NUEVA FECHA AGENDADA - ALQUILER ===\n\n";
        $body .= "Fecha de notificación: " . $fecha_agendamiento . "\n\n";
        $body .= "INFORMACIÓN DEL CLIENTE:\n";
        $body .= "----------------------------------------\n";
        $body .= "Nombre: " . $usuario_nombre . "\n";
        $body .= "Correo: " . $usuario_email . "\n\n";
        
        $body .= "INFORMACIÓN DEL ALQUILER:\n";
        $body .= "----------------------------------------\n";
        $body .= "Máquina/Producto: " . $producto_nombre . "\n";
        $body .= "Días requeridos: " . $dias . " día(s)\n";
        $body .= "Fecha de inicio: " . date('d/m/Y', strtotime($fecha_inicio)) . "\n";
        $body .= "Fecha de fin: " . date('d/m/Y', strtotime($fecha_fin)) . "\n\n";
        
        $body .= "Este correo fue generado automáticamente cuando un cliente agendó una fecha de alquiler.\n";
        $body .= "Por favor, revisa el sistema para más detalles.\n";
        
        return $body;
    }
    
    /**
     * Intenta enviar el correo usando PHPMailer
     */
    private function sendWithPHPMailer($usuario_nombre, $usuario_email, $producto_nombre, $fecha_inicio, $fecha_fin, $dias) {
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
            $mail = new PHPMailer(true);
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = EmailConfig::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = EmailConfig::SMTP_USERNAME;
            $mail->Password = EmailConfig::SMTP_PASSWORD;
            
            // Configurar SMTPSecure según el puerto
            if (EmailConfig::SMTP_PORT == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
            } elseif (EmailConfig::SMTP_PORT == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 'ssl'
            } else {
                $mail->SMTPSecure = EmailConfig::SMTP_SECURE ? PHPMailer::ENCRYPTION_STARTTLS : false;
            }
            
            $mail->Port = EmailConfig::SMTP_PORT;
            $mail->CharSet = EmailConfig::CHARSET;
            
            // Configuración adicional para Gmail
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Remitentes
            $mail->setFrom(EmailConfig::SMTP_USERNAME, EmailConfig::FROM_NAME);
            $mail->addReplyTo($usuario_email, $usuario_nombre);
            $mail->addAddress(EmailConfig::CONTACT_EMAIL);
            
            // Contenido
            $mail->isHTML(false);
            $mail->Subject = "Nueva fecha agendada - Alquiler: " . $producto_nombre;
            $mail->Body = $this->buildEmailBody($usuario_nombre, $usuario_email, $producto_nombre, $fecha_inicio, $fecha_fin, $dias);
            
            // Enviar correo
            $mail->send();
            
            return true;
            
        } catch (Exception $e) {
            $errorMsg = "Error con PHPMailer en RentalNotificationService: " . $e->getMessage();
            if ($mail && !empty($mail->ErrorInfo)) {
                $errorMsg .= " | ErrorInfo: " . $mail->ErrorInfo;
            }
            error_log($errorMsg);
            return false;
        }
    }
    
    /**
     * Guarda la notificación en un archivo de log (como respaldo)
     */
    public function saveRentalNotification($usuario_nombre, $usuario_email, $producto_nombre, $fecha_inicio, $fecha_fin, $dias) {
        $logFile = __DIR__ . '/../../logs/rental_notifications.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $fecha_agendamiento = date('Y-m-d H:i:s');
        $logEntry = $fecha_agendamiento . " | " . $usuario_nombre . " | " . $usuario_email . " | " . 
                   $producto_nombre . " | " . $fecha_inicio . " | " . $fecha_fin . " | " . $dias . " días\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
?>
