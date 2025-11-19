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
}

