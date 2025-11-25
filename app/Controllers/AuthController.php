<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Auth;
use App\Helpers\Security;

/**
 * Controlador de autenticación
 */
class AuthController extends Controller {
    
    public function showLogin() {
        $auth = new Auth();
        
        if ($auth->isAuthenticated()) {
            $this->redirect('/');
        }
        
        $data = [
            'title' => 'Iniciar Sesión - AlquiVenta',
            'error' => $_GET['error'] ?? '',
            'success' => $_GET['success'] ?? '',
            'current_user' => null
        ];
        
        $this->view('auth/login', $data, null);
    }
    
    public function login() {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }
        
        $auth = new Auth();
        $email = trim($this->post('email', ''));
        $password = $this->post('password', '');
        
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            $this->redirect('/?success=login');
        } else {
            $this->redirect('/login?error=' . urlencode($result['message']));
        }
    }
    
    public function showRegister() {
        $auth = new Auth();
        
        if ($auth->isAuthenticated()) {
            $this->redirect('/');
        }
        
        $data = [
            'title' => 'Registro - AlquiVenta',
            'error' => $_GET['error'] ?? '',
            'success' => $_GET['success'] ?? '',
            'current_user' => null
        ];
        
        $this->view('auth/register', $data, null);
    }
    
    public function register() {
        if (!$this->isPost()) {
            $this->redirect('/register');
        }
        
        $auth = new Auth();
        $nombre = trim($this->post('nombre', ''));
        $apellido = trim($this->post('apellido', ''));
        $email = trim($this->post('email', ''));
        $password = $this->post('password', '');
        $telefono = trim($this->post('telefono', ''));
        $direccion = trim($this->post('direccion', ''));
        
        $result = $auth->register($nombre, $apellido, $email, $password, $telefono, $direccion);
        
        if ($result['success']) {
            $this->redirect('/?success=registration_complete');
        } else {
            $this->redirect('/register?error=' . urlencode($result['message']));
        }
    }
    
    public function logout() {
        $auth = new Auth();
        $auth->logout();
        $this->redirect('/login');
    }
    
    public function showForgotPassword() {
        $auth = new Auth();
        
        if ($auth->isAuthenticated()) {
            $this->redirect('/');
        }
        
        $data = [
            'title' => 'Recuperar Contraseña',
            'error' => $_GET['error'] ?? '',
            'success' => $_GET['success'] ?? '',
            'current_user' => null
        ];
        
        $this->view('auth/forgot-password', $data, null);
    }
    
    public function forgotPassword() {
        if (!$this->isPost()) {
            $this->redirect('/recuperar-contrasena');
        }
        
        $auth = new Auth();
        $email = trim($this->post('email', ''));
        $new_password = $this->post('new_password', '');
        $confirm_password = $this->post('confirm_password', '');
        
        if (empty($email)) {
            $this->redirect('/recuperar-contrasena?error=' . urlencode('El email es requerido'));
        }
        
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $this->redirect('/recuperar-contrasena?error=' . urlencode('Las contraseñas no coinciden'));
            }
            
            if (strlen($new_password) < 8) {
                $this->redirect('/recuperar-contrasena?error=' . urlencode('La contraseña debe tener al menos 8 caracteres'));
            }
            
            $conn = $auth->getConnection();
            $query = "SELECT id FROM usuarios WHERE email = :email AND activo = true";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE usuarios SET password_hash = :password_hash WHERE id = :id";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bindParam(':password_hash', $new_password_hash);
                $update_stmt->bindParam(':id', $user['id']);
                
                if ($update_stmt->execute()) {
                    $this->redirect('/login?success=' . urlencode('Contraseña restablecida exitosamente'));
                } else {
                    $this->redirect('/recuperar-contrasena?error=' . urlencode('Error al actualizar la contraseña'));
                }
            } else {
                $this->redirect('/recuperar-contrasena?error=' . urlencode('Email no encontrado o cuenta inactiva'));
            }
        } else {
            $this->redirect('/recuperar-contrasena?email=' . urlencode($email));
        }
    }
}

