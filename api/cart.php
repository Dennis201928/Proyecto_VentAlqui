<?php
/**
 * API REST para carrito de compras
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar configuración
require_once __DIR__ . '/../app/Core/Config.php';

// Usar las clases del nuevo sistema MVC
use App\Models\Cart;
use App\Models\Auth;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart = new Cart();
$auth = new Auth();

// Verificar autenticación
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Obtener items del carrito
            $items = $cart->getCartItems($user_id);
            if (isset($items['error'])) {
                http_response_code(500);
                echo json_encode($items);
            } else {
                $total = $cart->getCartTotal($user_id);
                echo json_encode([
                    'success' => true, 
                    'data' => $items,
                    'total' => $total
                ]);
            }
            break;
            
        case 'POST':
            // Agregar producto al carrito
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['producto_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
                break;
            }
            
            $producto_id = $input['producto_id'];
            $cantidad = isset($input['cantidad']) ? $input['cantidad'] : 1;
            $tipo = isset($input['tipo']) ? $input['tipo'] : 'venta';
            $fecha_inicio = isset($input['fecha_inicio']) ? $input['fecha_inicio'] : null;
            $fecha_fin = isset($input['fecha_fin']) ? $input['fecha_fin'] : null;
            
            $result = $cart->addToCart($user_id, $producto_id, $cantidad, $tipo, $fecha_inicio, $fecha_fin);
            if ($result['success']) {
                http_response_code(201);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'PUT':
            // Actualizar cantidad de un item
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['item_id']) || !isset($input['cantidad'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de item y cantidad requeridos']);
                break;
            }
            
            $result = $cart->updateQuantity($user_id, $input['item_id'], $input['cantidad']);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'DELETE':
            // Eliminar item del carrito o limpiar carrito
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['item_id'])) {
                // Eliminar item específico
                $result = $cart->removeFromCart($user_id, $input['item_id']);
            } else {
                // Limpiar carrito completo
                $result = $cart->clearCart($user_id);
            }
            
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
