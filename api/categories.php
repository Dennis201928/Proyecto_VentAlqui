<?php
/**
 * API REST para categorías
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
$query_params = $_GET;

try {
    switch ($method) {
        case 'GET':
            // Obtener categorías
            $tipo = isset($query_params['tipo']) ? $query_params['tipo'] : null;
            $result = $product->getCategories($tipo);
            
            if (isset($result['error'])) {
                http_response_code(500);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => true, 'data' => $result]);
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
