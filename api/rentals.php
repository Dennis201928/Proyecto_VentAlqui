<?php
/**
 * API REST para alquileres
 */

// Desactivar errores que puedan interferir con el JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/rental.php';
require_once '../includes/auth.php';

$rental = new Rental();
$auth = new Auth();

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Obtener ID del alquiler si está en la URL
$rental_id = null;
if (isset($path_parts[2]) && is_numeric($path_parts[2])) {
    $rental_id = (int)$path_parts[2];
}

// Obtener parámetros de consulta
$query_params = $_GET;

try {
    switch ($method) {
        case 'GET':
            if ($rental_id) {
                // Obtener un alquiler específico
                $result = $rental->getRentalById($rental_id);
                if (isset($result['error'])) {
                    http_response_code(500);
                    echo json_encode($result);
                } elseif ($result) {
                    echo json_encode(['success' => true, 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Alquiler no encontrado']);
                }
            } else {
                // Obtener lista de alquileres
                if ($auth->isAdmin()) {
                    // Admin puede ver todos los alquileres
                    $filters = [];
                    if (isset($query_params['estado'])) {
                        $filters['estado'] = $query_params['estado'];
                    }
                    if (isset($query_params['fecha_desde'])) {
                        $filters['fecha_desde'] = $query_params['fecha_desde'];
                    }
                    if (isset($query_params['fecha_hasta'])) {
                        $filters['fecha_hasta'] = $query_params['fecha_hasta'];
                    }
                    if (isset($query_params['usuario_id'])) {
                        $filters['usuario_id'] = $query_params['usuario_id'];
                    }
                    if (isset($query_params['limit'])) {
                        $filters['limit'] = $query_params['limit'];
                    }
                    if (isset($query_params['offset'])) {
                        $filters['offset'] = $query_params['offset'];
                    }
                    
                    $result = $rental->getAllRentals($filters);
                } else {
                    // Usuario normal solo ve sus alquileres
                    if (!$auth->isLoggedIn()) {
                        http_response_code(401);
                        echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
                        break;
                    }
                    
                    $estado = isset($query_params['estado']) ? $query_params['estado'] : null;
                    $result = $rental->getUserRentals($_SESSION['user_id'], $estado);
                }
                
                if (isset($result['error'])) {
                    http_response_code(500);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => true, 'data' => $result]);
                }
            }
            break;
            
        case 'POST':
            // Crear nuevo alquiler
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
                break;
            }
            
            // Validar datos requeridos
            $required_fields = ['producto_id', 'fecha_inicio', 'fecha_fin'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Campo requerido: $field"]);
                    exit();
                }
            }
            
            $observaciones = isset($input['observaciones']) ? $input['observaciones'] : null;
            $result = $rental->createRental($_SESSION['user_id'], $input['producto_id'], 
                                          $input['fecha_inicio'], $input['fecha_fin'], $observaciones);
            
            if ($result['success']) {
                http_response_code(201);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'PUT':
            // Actualizar estado de alquiler (solo admin)
            if (!$auth->isAdmin()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                break;
            }
            
            if (!$rental_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de alquiler requerido']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['estado'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Estado requerido']);
                break;
            }
            
            $observaciones = isset($input['observaciones']) ? $input['observaciones'] : null;
            $result = $rental->updateRentalStatus($rental_id, $input['estado'], $observaciones);
            
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'DELETE':
            // Cancelar alquiler
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
                break;
            }
            
            if (!$rental_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de alquiler requerido']);
                break;
            }
            
            $result = $rental->cancelRental($rental_id, $_SESSION['user_id']);
            
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>
