<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Auth;

/**
 * Controlador de usuario
 */
class UserController extends Controller {
    
    public function profile() {
        $auth = new Auth();
        $current_user = $auth->getCurrentUser();
        
        if (!$current_user) {
            $this->redirect('/login');
        }
        
        $data = [
            'title' => 'Mi Perfil',
            'current_user' => $current_user
        ];
        
        $this->view('user/profile', $data);
    }
}

