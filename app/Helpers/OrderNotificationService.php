<?php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Core\EmailConfig;

class OrderNotificationService {
    
    /**
     * Envía un correo de notificación cuando un pedido se marca como enviado
     * 
     * @param string $usuario_nombre Nombre completo del usuario
     * @param string $usuario_email Correo del usuario
     * @param int $order_id ID del pedido
     * @param array $productos Lista de productos en el pedido
     * @param float $total Total del pedido
     * @param string $direccion_entrega Dirección de entrega (opcional)
     * @return bool True si se envió correctamente, false en caso contrario
     */
    public function sendOrderShippedNotification($usuario_nombre, $usuario_email, $order_id, $productos, $total, $direccion_entrega = null) {
        try {
            return $this->sendWithPHPMailer($usuario_nombre, $usuario_email, $order_id, $productos, $total, $direccion_entrega);
            
        } catch (\Exception $e) {
            error_log("Error en OrderNotificationService: " . $e->getMessage());
            return false;
        }
    }
    
    
    /**
     * Construye el cuerpo del mensaje de correo de notificación de envío
     */
    private function buildEmailBody($usuario_nombre, $usuario_email, $order_id, $productos, $total, $direccion_entrega) {
        $fecha_notificacion = date('d/m/Y H:i:s');
        
        $body = "¡Hola " . $usuario_nombre . "!\n\n";
        $body .= "Nos complace informarte que tu pedido #" . $order_id . " ya está en camino.\n\n";
        
        $body .= "=== DETALLES DE TU PEDIDO ===\n\n";
        $body .= "Número de pedido: #" . $order_id . "\n";
        $body .= "Fecha de notificación: " . $fecha_notificacion . "\n\n";
        
        $body .= "PRODUCTOS:\n";
        $body .= "----------------------------------------\n";
        if (is_array($productos)) {
            foreach ($productos as $producto) {
                $nombre = is_array($producto) ? ($producto['producto_nombre'] ?? $producto['nombre'] ?? 'Producto') : $producto;
                $cantidad = is_array($producto) ? ($producto['cantidad'] ?? 1) : 1;
                $body .= "- " . $nombre . " (Cantidad: " . $cantidad . ")\n";
            }
        } else {
            $body .= "- " . $productos . "\n";
        }
        $body .= "\n";
        
        if ($direccion_entrega) {
            $body .= "DIRECCIÓN DE ENTREGA:\n";
            $body .= "----------------------------------------\n";
            $body .= $direccion_entrega . "\n\n";
        }
        
        $body .= "Total del pedido: $" . number_format($total, 2, '.', ',') . "\n\n";
        
        $body .= "Tu pedido ha sido enviado y debería llegar pronto. Te mantendremos informado sobre el estado de tu entrega.\n\n";
        $body .= "Si tienes alguna pregunta o necesitas asistencia, no dudes en contactarnos.\n\n";
        $body .= "Gracias por tu compra,\n";
        $body .= "El equipo de AlquiVenta\n";
        
        return $body;
    }
    
    /**
     * Intenta enviar el correo usando PHPMailer
     */
    private function sendWithPHPMailer($usuario_nombre, $usuario_email, $order_id, $productos, $total, $direccion_entrega) {
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
            $mail->addAddress($usuario_email, $usuario_nombre);
            
            // Contenido
            $mail->isHTML(false);
            $mail->Subject = "Tu pedido #" . $order_id . " está en camino - AlquiVenta";
            $mail->Body = $this->buildEmailBody($usuario_nombre, $usuario_email, $order_id, $productos, $total, $direccion_entrega);
            
            // Enviar correo
            $mail->send();
            
            return true;
            
        } catch (Exception $e) {
            $errorMsg = "Error con PHPMailer en OrderNotificationService: " . $e->getMessage();
            if ($mail && !empty($mail->ErrorInfo)) {
                $errorMsg .= " | ErrorInfo: " . $mail->ErrorInfo;
            }
            error_log($errorMsg);
            return false;
        }
    }
    
    /**
     * Envía un correo de notificación al administrador cuando se crea una nueva venta
     * 
     * @param string $usuario_nombre Nombre completo del usuario
     * @param string $usuario_email Correo del usuario
     * @param int $order_id ID del pedido
     * @param array $productos Lista de productos en el pedido
     * @param float $total Total del pedido
     * @param string $metodo_pago Método de pago utilizado
     * @param string $direccion_entrega Dirección de entrega (opcional)
     * @return bool True si se envió correctamente, false en caso contrario
     */
    public function sendNewOrderNotificationToAdmin($usuario_nombre, $usuario_email, $order_id, $productos, $total, $metodo_pago, $direccion_entrega = null) {
        try {
            return $this->sendNewOrderWithPHPMailer($usuario_nombre, $usuario_email, $order_id, $productos, $total, $metodo_pago, $direccion_entrega);
            
        } catch (\Exception $e) {
            error_log("Error en OrderNotificationService (notificación de nueva venta): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Construye el cuerpo del mensaje de correo de notificación de nueva venta para el administrador
     */
    private function buildNewOrderEmailBody($usuario_nombre, $usuario_email, $order_id, $productos, $total, $metodo_pago, $direccion_entrega) {
        $fecha_notificacion = date('d/m/Y H:i:s');
        
        $body = "=== NUEVA VENTA REALIZADA ===\n\n";
        $body .= "Fecha de notificación: " . $fecha_notificacion . "\n\n";
        
        $body .= "INFORMACIÓN DEL CLIENTE:\n";
        $body .= "----------------------------------------\n";
        $body .= "Nombre: " . $usuario_nombre . "\n";
        $body .= "Correo: " . $usuario_email . "\n\n";
        
        $body .= "INFORMACIÓN DE LA VENTA:\n";
        $body .= "----------------------------------------\n";
        $body .= "Número de pedido: #" . $order_id . "\n";
        $body .= "Método de pago: " . $metodo_pago . "\n";
        if ($direccion_entrega) {
            $body .= "Dirección de entrega: " . $direccion_entrega . "\n";
        }
        $body .= "\n";
        
        $body .= "PRODUCTOS:\n";
        $body .= "----------------------------------------\n";
        if (is_array($productos)) {
            foreach ($productos as $producto) {
                $nombre = is_array($producto) ? ($producto['producto_nombre'] ?? $producto['nombre'] ?? 'Producto') : $producto;
                $cantidad = is_array($producto) ? ($producto['cantidad'] ?? 1) : 1;
                $precio_unitario = is_array($producto) ? ($producto['precio_unitario'] ?? 0) : 0;
                $subtotal = is_array($producto) ? ($producto['subtotal'] ?? 0) : 0;
                $body .= "- " . $nombre . " (Cantidad: " . $cantidad . ", Precio unitario: $" . number_format($precio_unitario, 2, '.', ',') . ", Subtotal: $" . number_format($subtotal, 2, '.', ',') . ")\n";
            }
        } else {
            $body .= "- " . $productos . "\n";
        }
        $body .= "\n";
        
        $body .= "Total del pedido: $" . number_format($total, 2, '.', ',') . "\n\n";
        
        $body .= "Este correo fue generado automáticamente cuando un cliente realizó una nueva compra.\n";
        $body .= "Por favor, revisa el sistema para más detalles y procesa el pedido.\n";
        
        return $body;
    }
    
    /**
     * Intenta enviar el correo de nueva venta al administrador usando PHPMailer
     */
    private function sendNewOrderWithPHPMailer($usuario_nombre, $usuario_email, $order_id, $productos, $total, $metodo_pago, $direccion_entrega) {
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
            $mail->Subject = "Nueva venta realizada - Pedido #" . $order_id . " - AlquiVenta";
            $mail->Body = $this->buildNewOrderEmailBody($usuario_nombre, $usuario_email, $order_id, $productos, $total, $metodo_pago, $direccion_entrega);
            
            // Enviar correo
            $mail->send();
            
            return true;
            
        } catch (Exception $e) {
            $errorMsg = "Error con PHPMailer en OrderNotificationService (nueva venta): " . $e->getMessage();
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
    public function saveOrderNotification($usuario_nombre, $usuario_email, $order_id, $productos, $total) {
        $logFile = __DIR__ . '/../../logs/order_notifications.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $fecha_notificacion = date('Y-m-d H:i:s');
        $productos_str = '';
        if (is_array($productos)) {
            $productos_nombres = [];
            foreach ($productos as $producto) {
                if (is_array($producto)) {
                    $productos_nombres[] = $producto['producto_nombre'] ?? $producto['nombre'] ?? 'Producto';
                } else {
                    $productos_nombres[] = $producto;
                }
            }
            $productos_str = implode(', ', $productos_nombres);
        } else {
            $productos_str = $productos;
        }
        $logEntry = $fecha_notificacion . " | Pedido #" . $order_id . " | " . $usuario_nombre . " | " . 
                   $usuario_email . " | " . $productos_str . " | Total: $" . $total . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
?>

