<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Auth;

/**
 * Controlador de productos
 */
class ProductController extends Controller {
    
    public function venta() {
        $product = new Product();
        $auth = new Auth();
        
        $filters = ['tipo' => 'material'];
        if (isset($_GET['categoria_id'])) {
            $filters['categoria_id'] = $_GET['categoria_id'];
        }
        if (isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        $data = [
            'title' => 'Venta de Materiales - AlquiVenta',
            'current_page' => 'venta',
            'products' => $product->getProducts($filters),
            'categories' => $product->getCategories('material'),
            'current_user' => $auth->getCurrentUser()
        ];
        
        $this->view('products/venta', $data);
    }
    
    public function alquiler() {
        $product = new Product();
        $auth = new Auth();
        
        $filters = ['tipo' => 'maquinaria'];
        if (isset($_GET['categoria_id'])) {
            $filters['categoria_id'] = $_GET['categoria_id'];
        }
        if (isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        $data = [
            'title' => 'Alquiler de Maquinaria - AlquiVenta',
            'current_page' => 'alquiler',
            'products' => $product->getProducts($filters),
            'categories' => $product->getCategories('maquinaria'),
            'current_user' => $auth->getCurrentUser()
        ];
        
        $this->view('products/alquiler', $data);
    }
    
    public function show($id) {
        $product = new Product();
        $auth = new Auth();
        
        $producto = $product->getProductById($id);
        
        if (!$producto) {
            http_response_code(404);
            echo "404 - Producto no encontrado";
            exit();
        }
        
        $data = [
            'title' => htmlspecialchars($producto['nombre']) . ' - AlquiVenta',
            'product' => $producto,
            'related_products' => $product->getRelatedProducts($id, $producto['categoria_id']),
            'current_user' => $auth->getCurrentUser()
        ];
        
        $this->view('products/show', $data);
    }
    
    public function showSale($id) {
        $auth = new Auth();
        $product = new Product();
        
        $current_user = $auth->getCurrentUser();
        if (!$current_user) {
            $this->redirect('/login');
        }
        
        $product_data = $product->getProductById($id);
        if (!$product_data || isset($product_data['error'])) {
            $this->redirect('/venta');
        }
        
        // Verificar que sea un producto de venta (no maquinaria)
        $es_material = isset($product_data['categoria_tipo']) && $product_data['categoria_tipo'] === 'material';
        if (!$es_material) {
            $this->redirect('/producto/' . $id);
        }
        
        if (empty($product_data['precio_venta']) || $product_data['precio_venta'] <= 0) {
            $product_data['precio_venta'] = 0;
        }
        
        // Obtener la cantidad del parámetro GET, validar y establecer valor por defecto
        $cantidad = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 1;
        if ($cantidad < 1) {
            $cantidad = 1;
        }
        // Limitar la cantidad al stock disponible
        $stock_disponible = (int)($product_data['stock_disponible'] ?? 0);
        if ($cantidad > $stock_disponible && $stock_disponible > 0) {
            $cantidad = $stock_disponible;
        }
        
        $data = [
            'title' => 'Agendar Venta',
            'current_user' => $current_user,
            'product' => $product_data,
            'product_id' => $id,
            'cantidad' => $cantidad
        ];
        
        $this->view('sale/show', $data);
    }
}

