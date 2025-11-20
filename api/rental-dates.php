<?php
/**
 * API para obtener fechas ocupadas de productos
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit();
}

// Cargar configuración
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Config.php';

// Usar las clases del nuevo sistema MVC
use App\Models\Rental;

// Incluir archivos necesarios
try {
    $rental = new Rental();
    
    if (!$rental) {
        throw new Exception('No se pudo inicializar Rental');
    }
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de inicialización: ' . $e->getMessage()]);
    exit();
}

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method == 'GET') {
        $product_id = isset($_GET['producto_id']) ? (int)$_GET['producto_id'] : null;
        $fecha_desde_raw = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null;
        $fecha_hasta_raw = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null;
        
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
            exit();
        }
        
        // Limpiar fechas - pueden venir en formato ISO con timezone
        $fecha_desde = null;
        $fecha_hasta = null;
        
        if ($fecha_desde_raw) {
            // Extraer solo la parte de fecha (Y-m-d) si viene en formato ISO
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $fecha_desde_raw, $matches)) {
                $fecha_desde = $matches[1];
            } else {
                $fecha_desde = $fecha_desde_raw;
            }
        }
        
        if ($fecha_hasta_raw) {
            // Extraer solo la parte de fecha (Y-m-d) si viene en formato ISO
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $fecha_hasta_raw, $matches)) {
                $fecha_hasta = $matches[1];
            } else {
                $fecha_hasta = $fecha_hasta_raw;
            }
        }
        
        $booked_dates = $rental->getBookedDates($product_id, $fecha_desde, $fecha_hasta);
        
        if (isset($booked_dates['error'])) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $booked_dates['error']]);
            exit();
        }
        
        // Formatear fechas para el calendario
        $formatted_dates = [];
        if (is_array($booked_dates)) {
            foreach ($booked_dates as $booking) {
                if (!isset($booking['fecha_inicio']) || !isset($booking['fecha_fin'])) {
                    continue;
                }
                
                // FullCalendar necesita que la fecha fin sea exclusiva (un día después)
                try {
                    $endDate = new DateTime($booking['fecha_fin']);
                    $endDate->modify('+1 day');
                    
                    $formatted_dates[] = [
                        'start' => $booking['fecha_inicio'],
                        'end' => $endDate->format('Y-m-d'),
                        'title' => 'Ocupado',
                        'color' => '#dc3545',
                        'display' => 'background'
                    ];
                } catch (Exception $e) {
                    // Ignorar fechas inválidas
                    continue;
                }
            }
        }
        
        ob_end_clean();
        echo json_encode(['success' => true, 'events' => $formatted_dates]);
    } else {
        ob_end_clean();
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    error_log('API rental-dates.php error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
} catch (Error $e) {
    ob_end_clean();
    http_response_code(500);
    error_log('API rental-dates.php fatal error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>

