<?php
/**
 * API REST para productos
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

require_once '../includes/product.php';
require_once '../includes/auth.php';

$product = new Product();
$auth = new Auth();

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Obtener ID del producto si está en la URL
$product_id = null;
if (isset($path_parts[2]) && is_numeric($path_parts[2])) {
    $product_id = (int)$path_parts[2];
}

// Obtener parámetros de consulta
$query_params = $_GET;

try {
    switch ($method) {
        case 'GET':
            if ($product_id) {
                // Obtener un producto específico
                $result = $product->getProductById($product_id);
                if (isset($result['error'])) {
                    http_response_code(500);
                    echo json_encode($result);
                } elseif ($result) {
                    echo json_encode(['success' => true, 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                }
            } else {
                // Obtener lista de productos
                $filters = [];
                
                // Aplicar filtros desde query parameters
                if (isset($query_params['categoria_id'])) {
                    $filters['categoria_id'] = $query_params['categoria_id'];
                }
                if (isset($query_params['tipo'])) {
                    $filters['tipo'] = $query_params['tipo'];
                }
                if (isset($query_params['estado'])) {
                    $filters['estado'] = $query_params['estado'];
                }
                if (isset($query_params['search'])) {
                    $filters['search'] = $query_params['search'];
                }
                if (isset($query_params['precio_min'])) {
                    $filters['precio_min'] = $query_params['precio_min'];
                }
                if (isset($query_params['precio_max'])) {
                    $filters['precio_max'] = $query_params['precio_max'];
                }
                if (isset($query_params['order_by'])) {
                    $filters['order_by'] = $query_params['order_by'];
                }
                if (isset($query_params['limit'])) {
                    $filters['limit'] = $query_params['limit'];
                }
                if (isset($query_params['offset'])) {
                    $filters['offset'] = $query_params['offset'];
                }
                
                $result = $product->getProducts($filters);
                if (isset($result['error'])) {
                    http_response_code(500);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => true, 'data' => $result]);
                }
            }
            break;
            
        case 'POST':
            // Crear nuevo producto (requiere autenticación de admin)
            if (!$auth->isAdmin()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
                break;
            }
            
            // Validar datos requeridos
            $required_fields = ['nombre', 'categoria_id', 'precio_venta'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Campo requerido: $field"]);
                    exit();
                }
            }
            
            $result = $product->createProduct($input);
            if ($result['success']) {
                http_response_code(201);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'PUT':
            // Actualizar producto (requiere autenticación de admin)
            if (!$auth->isAdmin()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                break;
            }
            
            if (!$product_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
                break;
            }
            
            $result = $product->updateProduct($product_id, $input);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'DELETE':
            // Eliminar producto (requiere autenticación de admin)
            if (!$auth->isAdmin()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                break;
            }
            
            if (!$product_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
                break;
            }
            
            $result = $product->deleteProduct($product_id);
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
