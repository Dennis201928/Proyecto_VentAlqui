<?php
/**
 * Punto de entrada único de la aplicación
 */

// Configuración de errores (desactivar display_errors en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar configuración
require_once __DIR__ . '/../app/Core/Config.php';

// Cargar helpers
require_once __DIR__ . '/../app/Helpers/Security.php';

// Establecer headers de seguridad
use App\Helpers\Security;
Security::setSecurityHeaders();

// Crear router
use App\Core\Router;
$router = new Router();

// Cargar rutas
require_once __DIR__ . '/../config/routes.php';

// Ejecutar router con manejo de errores
try {
    $router->dispatch();
} catch (\Exception $e) {
    // Log del error
    error_log("Error en la aplicación: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    
    // Mostrar error genérico al usuario
    http_response_code(500);
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Error</title></head><body>";
    echo "<h1>Error en la aplicación</h1>";
    echo "<p>Ha ocurrido un error. Por favor, intente más tarde.</p>";
    echo "</body></html>";
}

