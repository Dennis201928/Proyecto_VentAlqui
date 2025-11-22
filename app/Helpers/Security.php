<?php
namespace App\Helpers;

/**
 * Clase de seguridad y validación
 */
class Security {
    
    /**
     * Sanitizar entrada de datos
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar contraseña
     */
    public static function validatePassword($password) {
        return strlen($password) >= 6 && 
               preg_match('/[a-zA-Z]/', $password) && 
               preg_match('/\d/', $password);
    }
    
    /**
     * Generar token CSRF
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verificar token CSRF
     */
    public static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Prevenir ataques de fuerza bruta
     */
    public static function checkBruteForce($identifier, $max_attempts = 5, $time_window = 300) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $key = 'brute_force_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => 0];
        }
        
        $data = $_SESSION[$key];
        $time_since_last = time() - $data['last_attempt'];
        
        if ($time_since_last > $time_window) {
            $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => 0];
            return true;
        }
        
        return $data['attempts'] < $max_attempts;
    }
    
    /**
     * Registrar intento de fuerza bruta
     */
    public static function recordBruteForceAttempt($identifier) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $key = 'brute_force_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => 0];
        }
        
        $_SESSION[$key]['attempts']++;
        $_SESSION[$key]['last_attempt'] = time();
    }
    
    /**
     * Limpiar intentos de fuerza bruta
     */
    public static function clearBruteForceAttempts($identifier) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $key = 'brute_force_' . md5($identifier);
        unset($_SESSION[$key]);
    }
    
    /**
     * Validar y limpiar datos de entrada
     */
    public static function validateAndSanitize($data, $rules) {
        $errors = [];
        $sanitized = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            // Sanitizar
            $sanitized[$field] = self::sanitizeInput($value);
            
            // Validar
            if (isset($rule['required']) && $rule['required'] && empty($sanitized[$field])) {
                $errors[] = "El campo {$field} es requerido";
                continue;
            }
            
            if (!empty($sanitized[$field])) {
                if (isset($rule['min_length']) && strlen($sanitized[$field]) < $rule['min_length']) {
                    $errors[] = "El campo {$field} debe tener al menos {$rule['min_length']} caracteres";
                }
                
                if (isset($rule['max_length']) && strlen($sanitized[$field]) > $rule['max_length']) {
                    $errors[] = "El campo {$field} no puede tener más de {$rule['max_length']} caracteres";
                }
                
                if (isset($rule['type'])) {
                    switch ($rule['type']) {
                        case 'email':
                            if (!self::validateEmail($sanitized[$field])) {
                                $errors[] = "El campo {$field} debe ser un email válido";
                            }
                            break;
                        case 'password':
                            if (!self::validatePassword($sanitized[$field])) {
                                $errors[] = "El campo {$field} debe tener al menos 6 caracteres, una letra y un número";
                            }
                            break;
                        case 'numeric':
                            if (!is_numeric($sanitized[$field])) {
                                $errors[] = "El campo {$field} debe ser numérico";
                            }
                            break;
                    }
                }
            }
        }
        
        return ['errors' => $errors, 'data' => $sanitized];
    }
    
    /**
     * Headers de seguridad
     */
    public static function setSecurityHeaders() {
        // Prevenir clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevenir MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        header("Content-Security-Policy: " .
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://code.jquery.com https://stackpath.bootstrapcdn.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://stackpath.bootstrapcdn.com https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
            "font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self' https://stackpath.bootstrapcdn.com https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
            "frame-src 'none';");
    }
    
    /**
     * Log de seguridad
     */
    public static function logSecurityEvent($event, $details = []) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'event' => $event,
            'details' => $details
        ];
        
        $log_file = __DIR__ . '/../../logs/security.log';
        $log_dir = dirname($log_file);
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    }
}

