<?php
/**
 * API para obtener fechas ocupadas de productos
 */

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

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Config.php';

use App\Models\Rental;

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
        
        // Limpiar fechas
        $fecha_desde = null;
        $fecha_hasta = null;
        
        if ($fecha_desde_raw) {
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $fecha_desde_raw, $matches)) {
                $fecha_desde = $matches[1];
            } else {
                $fecha_desde = $fecha_desde_raw;
            }
        }
        
        if ($fecha_hasta_raw) {
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $fecha_hasta_raw, $matches)) {
                $fecha_hasta = $matches[1];
            } else {
                $fecha_hasta = $fecha_hasta_raw;
            }
        }
        
        $booked_data = $rental->getBookedDates($product_id, $fecha_desde, $fecha_hasta);
        
        if (isset($booked_data['error'])) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $booked_data['error']]);
            exit();
        }
        
        $stock_disponible = isset($booked_data['stock_disponible']) ? (int)$booked_data['stock_disponible'] : 0;
        $alquileres = isset($booked_data['alquileres']) ? $booked_data['alquileres'] : [];
        
        // Mostrar alquileres activos en el calendario como información visual (no bloqueantes)
        // Solo se marcan como agotadas cuando realmente no hay stock disponible
        $formatted_dates = [];
        foreach ($alquileres as $booking) {
            if (!isset($booking['fecha_inicio']) || !isset($booking['fecha_fin'])) {
                continue;
            }
            
            if (isset($booking['estado']) && $booking['estado'] == 'cancelado') {
                continue;
            }
            
            try {
                $endDate = new DateTime($booking['fecha_fin']);
                $endDate->modify('+1 day');
                
                $estado_texto = '';
                switch ($booking['estado']) {
                    case 'pendiente':
                        $estado_texto = 'Pendiente';
                        $color = '#ffc107';
                        break;
                    case 'confirmado':
                        $estado_texto = 'Confirmado';
                        $color = '#17a2b8';
                        break;
                    case 'en_curso':
                        $estado_texto = 'En curso';
                        $color = '#28a745';
                        break;
                    default:
                        $estado_texto = 'Ocupado';
                        $color = '#6c757d';
                }
                
                // Mostrar alquileres como información visual (display: 'background' con opacidad)
                // No bloquean la selección si hay stock disponible
                $formatted_dates[] = [
                    'start' => $booking['fecha_inicio'],
                    'end' => $endDate->format('Y-m-d'),
                    'title' => 'Alquiler ' . $estado_texto . ' (Stock: ' . $stock_disponible . ')',
                    'color' => $color,
                    'display' => 'background',
                    'classNames' => ['rental-info'] // Clase para identificar que es solo información
                ];
            } catch (Exception $e) {
                continue;
            }
        }
        
        // Solo marcar fechas como completamente agotadas cuando el stock es 0
        // Esto bloquea la selección completamente
        if ($stock_disponible <= 0) {
            $fecha_inicio_rango = $fecha_desde ? new DateTime($fecha_desde) : new DateTime();
            $fecha_fin_rango = $fecha_hasta ? new DateTime($fecha_hasta) : (clone $fecha_inicio_rango)->modify('+2 years');
            $formatted_dates[] = [
                'start' => $fecha_inicio_rango->format('Y-m-d'),
                'end' => $fecha_fin_rango->modify('+1 day')->format('Y-m-d'),
                'title' => 'Sin stock disponible',
                'color' => '#dc3545',
                'display' => 'background',
                'classNames' => ['no-stock'] // Clase para identificar que está sin stock
            ];
        }
        
        
        ob_end_clean();
        echo json_encode([
            'success' => true, 
            'events' => $formatted_dates,
            'stock_disponible' => $stock_disponible
        ]);
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
