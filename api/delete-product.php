<?php
/**
 * API para eliminar productos
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/product.php';

header('Content-Type: application/json');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Verificar que el usuario esté logueado y sea admin
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requieren permisos de administrador']);
    exit();
}

// Obtener el ID del producto
$product_id = $_POST['id'] ?? null;

if (!$product_id || !is_numeric($product_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
    exit();
}

try {
    $product = new Product();
    
    // Eliminar el producto
    $result = $product->deleteProduct($product_id);
    
    // Debug: Log del resultado
    error_log("Resultado deleteProduct: " . print_r($result, true));
    
    if (isset($result['success']) && ($result['success'] === true || $result['success'] === 1)) {
        echo json_encode($result);
    } else {
        http_response_code(500);
        echo json_encode($result ?: ['success' => false, 'message' => 'Error desconocido']);
    }
    
} catch (Exception $e) {
    error_log("Error al eliminar producto: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>
