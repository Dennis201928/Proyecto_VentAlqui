<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Config;
use App\Helpers\Security;

/**
 * Modelo de autenticación
 */
class Auth extends Model {
    
    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Registrar un nuevo usuario
     */
    public function register($nombre, $apellido, $email, $password, $telefono = null, $direccion = null) {
        try {
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

            $password_hash = password_hash($sanitized['password'], PASSWORD_DEFAULT);

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

                // ✅ (Tu código original) Log de registro
                Security::logSecurityEvent('user_registered', [
                    'email' => $sanitized['email'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                // ✅ (AGREGADO) Enviar correo con datos del usuario (SIN contraseña)
                try {
                    $nombreCompleto = trim($sanitized['nombre'] . ' ' . $sanitized['apellido']);

                    $userData = [
                        'nombre'   => $sanitized['nombre'],
                        'apellido' => $sanitized['apellido'],
                        'email'    => $sanitized['email'],
                        'telefono' => $sanitized['telefono'] ?? null,
                        'direccion'=> $sanitized['direccion'] ?? null,
                    ];

                    $userService = new \App\Helpers\UserService();

                    // 1) Bienvenida al usuario (opcional, si lo quieres)
                    $userService->sendRegisterWelcomeEmail($nombreCompleto, $sanitized['email']);

                    // 2) Info al admin con datos del usuario registrado (sin contraseña)
                    $userService->sendRegisterInfoToAdmin($userData);

                } catch (\Exception $e) {
                    // No romper el registro si el correo falla
                    error_log("Error al enviar correos de registro: " . $e->getMessage());
                }
                // ✅ (FIN AGREGADO)

                return ['success' => true, 'message' => 'Usuario registrado exitosamente'];

            } else {
                return ['success' => false, 'message' => 'Error al registrar usuario'];
            }

        } catch (\PDOException $e) {
            Security::logSecurityEvent('registration_error', [
                'error' => $e->getMessage(),
                'email' => $email ?? 'unknown'
            ]);
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Iniciar sesión
     */
    public function login($email, $password) {
        try {
            if (!Security::validateEmail($email)) {
                return ['success' => false, 'message' => 'Email no válido'];
            }
            
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
                    Security::clearBruteForceAttempts($email);
                    
                    $update_query = "UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id";
                    $update_stmt = $this->conn->prepare($update_query);
                    $update_stmt->bindParam(':id', $user['id']);
                    $update_stmt->execute();

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['last_activity'] = time();
                    
                    session_regenerate_id(true);

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
        } catch (\PDOException $e) {
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
     * Verificar si el usuario está autenticado
     */
    public function isAuthenticated() {
        return $this->isLoggedIn() && !$this->isSessionExpired();
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
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            $query = "SELECT id, nombre, apellido, email, telefono, direccion, tipo_usuario 
                     FROM usuarios WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();

            return $stmt->fetch();
        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin() {
        return $this->isAuthenticated() && isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
    }

    /**
     * Requerir autenticación
     */
    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            header('Location: /login');
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
}
