<?php
namespace App\Middleware;

use App\Models\Auth;

/**
 * Middleware para verificar que el usuario es administrador
 */
class AdminMiddleware {
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

        if (!$auth->isAdmin()) {
            http_response_code(403);
            echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Acceso Denegado</title></head><body>";
            echo "<h1>403 - Acceso Denegado</h1>";
            echo "<p>Se requieren permisos de administrador.</p>";
            echo "<a href='" . \App\Core\Config::SITE_URL . "'>Volver al inicio</a>";
            echo "</body></html>";
            exit();
        }

        return true;
    }
}

