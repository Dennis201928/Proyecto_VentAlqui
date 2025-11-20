<?php
/**
 * API para obtener fechas de ventas
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
use App\Models\Order;

// Incluir archivos necesarios
try {
    $order = new Order();
    
    if (!$order) {
        throw new Exception('No se pudo inicializar Order');
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
        
        $filters = [];
        if ($product_id) {
            $filters['producto_id'] = $product_id;
        }
        if ($fecha_desde) {
            $filters['fecha_desde'] = $fecha_desde;
        }
        if ($fecha_hasta) {
            $filters['fecha_hasta'] = $fecha_hasta;
        }
        
        $orders = $order->getAllOrdersWithFilters($filters);
        
        if (isset($orders['error'])) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $orders['error']]);
            exit();
        }
        
        // Formatear fechas para el calendario
        // Las ventas se muestran en la fecha de creación
        $formatted_dates = [];
        if (is_array($orders)) {
            foreach ($orders as $sale) {
                if (!isset($sale['fecha_creacion'])) {
                    continue;
                }
                
                // Extraer solo la fecha (sin hora)
                try {
                    $fecha_creacion = new DateTime($sale['fecha_creacion']);
                    $fecha_str = $fecha_creacion->format('Y-m-d');
                    
                    // Determinar color según estado
                    $color = '#6c757d'; // gris por defecto
                    switch ($sale['estado']) {
                        case 'pendiente':
                            $color = '#ffc107'; // amarillo
                            break;
                        case 'confirmada':
                            $color = '#28a745'; // verde
                            break;
                        case 'enviada':
                            $color = '#007bff'; // azul
                            break;
                        case 'entregada':
                            $color = '#17a2b8'; // cyan
                            break;
                        case 'cancelada':
                            $color = '#dc3545'; // rojo
                            break;
                    }
                    
                    $formatted_dates[] = [
                        'start' => $fecha_str,
                        'end' => $fecha_str,
                        'title' => 'Venta #' . $sale['id'] . ' - ' . ($sale['productos_nombres'] ?? 'Productos'),
                        'color' => $color,
                        'display' => 'background',
                        'extendedProps' => [
                            'order_id' => $sale['id'],
                            'estado' => $sale['estado']
                        ]
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
    error_log('API order-dates.php error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
} catch (Error $e) {
    ob_end_clean();
    http_response_code(500);
    error_log('API order-dates.php fatal error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>

