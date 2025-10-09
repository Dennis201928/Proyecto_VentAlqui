<?php
/**
 * Sistema de autenticación y manejo de sesiones
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once 'security.php';

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Obtener conexión a la base de datos
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Registrar un nuevo usuario con validaciones de seguridad
     */
    public function register($nombre, $apellido, $email, $password, $telefono = null, $direccion = null) {
        try {
            // Validaciones de seguridad
            $rules = [
                'nombre' => ['required' => true, 'min_length' => 2, 'max_length' => 100],
                'apellido' => ['required' => true, 'min_length' => 2, 'max_length' => 100],
                'email' => ['required' => true, 'type' => 'email', 'max_length' => 150],
                'password' => ['required' => true, 'type' => 'password'],
                'telefono' => ['max_length' => 20],
                'direccion' => ['max_length' => 500]
            ];
            
            $data = compact('nombre', 'apellido', 'email', 'password', 'telefono', 'direccion');
            $validation = Security::validateAndSanitize($data, $rules);
            
            if (!empty($validation['errors'])) {
                return ['success' => false, 'message' => implode('<br>', $validation['errors'])];
            }
            
            $sanitized = $validation['data'];
            
            // Verificar si el email ya existe
            $query = "SELECT id FROM usuarios WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $sanitized['email']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'El email ya está registrado'];
            }

            // Hash de la contraseña
            $password_hash = password_hash($sanitized['password'], PASSWORD_DEFAULT);

            // Insertar nuevo usuario
            $query = "INSERT INTO usuarios (nombre, apellido, email, password_hash, telefono, direccion) 
                     VALUES (:nombre, :apellido, :email, :password_hash, :telefono, :direccion)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $sanitized['nombre']);
            $stmt->bindParam(':apellido', $sanitized['apellido']);
            $stmt->bindParam(':email', $sanitized['email']);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':telefono', $sanitized['telefono']);
            $stmt->bindParam(':direccion', $sanitized['direccion']);

            if ($stmt->execute()) {
                // Log del evento
                Security::logSecurityEvent('user_registered', [
                    'email' => $sanitized['email'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al registrar usuario'];
            }
        } catch (PDOException $e) {
            Security::logSecurityEvent('registration_error', [
                'error' => $e->getMessage(),
                'email' => $email ?? 'unknown'
            ]);
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Iniciar sesión con validaciones de seguridad
     */
    public function login($email, $password) {
        try {
            // Validar entrada
            if (!Security::validateEmail($email)) {
                return ['success' => false, 'message' => 'Email no válido'];
            }
            
            // Verificar intentos de fuerza bruta
            if (!Security::checkBruteForce($email)) {
                Security::logSecurityEvent('brute_force_blocked', ['email' => $email]);
                return ['success' => false, 'message' => 'Demasiados intentos fallidos. Intenta más tarde.'];
            }
            
            $query = "SELECT id, nombre, apellido, email, password_hash, tipo_usuario, activo 
                     FROM usuarios WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                
                if (!$user['activo']) {
                    Security::recordBruteForceAttempt($email);
                    return ['success' => false, 'message' => 'Cuenta desactivada'];
                }

                if (password_verify($password, $user['password_hash'])) {
                    // Limpiar intentos de fuerza bruta
                    Security::clearBruteForceAttempts($email);
                    
                    // Actualizar último acceso
                    $update_query = "UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id";
                    $update_stmt = $this->conn->prepare($update_query);
                    $update_stmt->bindParam(':id', $user['id']);
                    $update_stmt->execute();

                    // Crear sesión segura
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['last_activity'] = time();
                    
                    // Regenerar ID de sesión
                    session_regenerate_id(true);

                    // Log del evento
                    Security::logSecurityEvent('user_login', [
                        'user_id' => $user['id'],
                        'email' => $email,
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);

                    return ['success' => true, 'message' => 'Inicio de sesión exitoso', 'user' => $user];
                } else {
                    Security::recordBruteForceAttempt($email);
                    Security::logSecurityEvent('failed_login', ['email' => $email]);
                    return ['success' => false, 'message' => 'Contraseña incorrecta'];
                }
            } else {
                Security::recordBruteForceAttempt($email);
                Security::logSecurityEvent('failed_login', ['email' => $email]);
                return ['success' => false, 'message' => 'Email no encontrado'];
            }
        } catch (PDOException $e) {
            Security::logSecurityEvent('login_error', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada exitosamente'];
    }

    /**
     * Verificar si el usuario está logueado
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
    }

    /**
     * Verificar si la sesión ha expirado
     */
    public function isSessionExpired() {
        if (!$this->isLoggedIn()) {
            return true;
        }

        $session_lifetime = Config::SESSION_LIFETIME;
        return (time() - $_SESSION['login_time']) > $session_lifetime;
    }

    /**
     * Obtener información del usuario actual
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn() || $this->isSessionExpired()) {
            return null;
        }

        try {
            $query = "SELECT id, nombre, apellido, email, telefono, direccion, tipo_usuario 
                     FROM usuarios WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin() {
        return $this->isLoggedIn() && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
    }

    /**
     * Requerir autenticación
     */
    public function requireAuth() {
        if (!$this->isLoggedIn() || $this->isSessionExpired()) {
            header('Location: login.php');
            exit();
        }
    }

    /**
     * Requerir permisos de administrador
     */
    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Acceso denegado';
            exit();
        }
    }

    /**
     * Actualizar perfil de usuario
     */
    public function updateProfile($id, $nombre, $apellido, $telefono, $direccion) {
        try {
            $query = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, 
                     telefono = :telefono, direccion = :direccion 
                     WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                // Actualizar sesión
                $_SESSION['user_name'] = $nombre . ' ' . $apellido;
                return ['success' => true, 'message' => 'Perfil actualizado exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar perfil'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword($id, $current_password, $new_password) {
        try {
            // Verificar contraseña actual
            $query = "SELECT password_hash FROM usuarios WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $user = $stmt->fetch();

            if (!password_verify($current_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Contraseña actual incorrecta'];
            }

            // Actualizar contraseña
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET password_hash = :password_hash WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password_hash', $new_password_hash);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar contraseña'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Generar token de recuperación de contraseña
     */
    public function generatePasswordRecoveryToken($email) {
        try {
            // Verificar si el email existe
            $query = "SELECT id, nombre, apellido FROM usuarios WHERE email = :email AND activo = true";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                return ['success' => false, 'message' => 'Email no encontrado o cuenta inactiva'];
            }
            
            $user = $stmt->fetch();
            
            // Limpiar tokens anteriores del usuario
            $delete_query = "DELETE FROM password_recovery_tokens WHERE usuario_id = :usuario_id";
            $delete_stmt = $this->conn->prepare($delete_query);
            $delete_stmt->bindParam(':usuario_id', $user['id']);
            $delete_stmt->execute();
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + (60 * 60)); // 1 hora de validez
            
            // Guardar token en la base de datos
            $insert_query = "INSERT INTO password_recovery_tokens (usuario_id, token, expires_at, ip_address, user_agent) 
                           VALUES (:usuario_id, :token, :expires_at, :ip_address, :user_agent)";
            $insert_stmt = $this->conn->prepare($insert_query);
            $insert_stmt->bindParam(':usuario_id', $user['id']);
            $insert_stmt->bindParam(':token', $token);
            $insert_stmt->bindParam(':expires_at', $expires_at);
            $insert_stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $insert_stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
            
            if ($insert_stmt->execute()) {
                // Log del evento
                Security::logSecurityEvent('password_recovery_requested', [
                    'user_id' => $user['id'],
                    'email' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                return [
                    'success' => true, 
                    'message' => 'Token de recuperación generado exitosamente',
                    'token' => $token,
                    'user' => $user
                ];
            } else {
                return ['success' => false, 'message' => 'Error al generar token de recuperación'];
            }
        } catch (PDOException $e) {
            Security::logSecurityEvent('password_recovery_error', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Verificar token de recuperación de contraseña
     */
    public function verifyPasswordRecoveryToken($token) {
        try {
            $query = "SELECT prt.*, u.id as user_id, u.nombre, u.apellido, u.email 
                     FROM password_recovery_tokens prt 
                     JOIN usuarios u ON prt.usuario_id = u.id 
                     WHERE prt.token = :token AND prt.used = false AND prt.expires_at > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                return ['success' => true, 'data' => $stmt->fetch()];
            } else {
                return ['success' => false, 'message' => 'Token inválido o expirado'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Cambiar contraseña usando token de recuperación
     */
    public function resetPasswordWithToken($token, $new_password) {
        try {
            // Verificar token
            $token_verification = $this->verifyPasswordRecoveryToken($token);
            if (!$token_verification['success']) {
                return $token_verification;
            }
            
            $token_data = $token_verification['data'];
            
            // Validar nueva contraseña
            $rules = ['password' => ['required' => true, 'type' => 'password']];
            $validation = Security::validateAndSanitize(['password' => $new_password], $rules);
            
            if (!empty($validation['errors'])) {
                return ['success' => false, 'message' => implode('<br>', $validation['errors'])];
            }
            
            // Actualizar contraseña
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE usuarios SET password_hash = :password_hash WHERE id = :id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':password_hash', $new_password_hash);
            $update_stmt->bindParam(':id', $token_data['user_id']);
            
            if ($update_stmt->execute()) {
                // Marcar token como usado
                $mark_used_query = "UPDATE password_recovery_tokens SET used = true WHERE id = :id";
                $mark_used_stmt = $this->conn->prepare($mark_used_query);
                $mark_used_stmt->bindParam(':id', $token_data['id']);
                $mark_used_stmt->execute();
                
                // Log del evento
                Security::logSecurityEvent('password_reset_completed', [
                    'user_id' => $token_data['user_id'],
                    'email' => $token_data['email'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                return ['success' => true, 'message' => 'Contraseña restablecida exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar contraseña'];
            }
        } catch (PDOException $e) {
            Security::logSecurityEvent('password_reset_error', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Limpiar tokens expirados
     */
    public function cleanExpiredTokens() {
        try {
            $query = "DELETE FROM password_recovery_tokens WHERE expires_at < NOW() OR used = true";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Tokens expirados limpiados'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al limpiar tokens: ' . $e->getMessage()];
        }
    }
}

// Funciones de utilidad
function isLoggedIn() {
    $auth = new Auth();
    return $auth->isLoggedIn();
}

function requireAuth() {
    $auth = new Auth();
    $auth->requireAuth();
}

function requireAdmin() {
    $auth = new Auth();
    $auth->requireAdmin();
}

function getCurrentUser() {
    $auth = new Auth();
    return $auth->getCurrentUser();
}
?>
