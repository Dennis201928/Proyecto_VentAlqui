<?php
namespace App\Middleware;

use App\Models\Auth;

/**
 * Middleware para verificar autenticación
 */
class AuthMiddleware {
    public function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $auth = new \App\Models\Auth();
        
        if (!$auth->isAuthenticated()) {
            $loginUrl = \App\Core\Config::SITE_URL . '/login';
            header('Location: ' . $loginUrl);
            exit();
        }

        return true;
    }
}

