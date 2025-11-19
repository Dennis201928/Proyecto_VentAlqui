<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Auth;

/**
 * Controlador principal
 */
class HomeController extends Controller {
    
    public function index() {
        $product = new Product();
        $auth = new Auth();
        
        $data = [
            'title' => 'AlquiVenta - Inicio',
            'current_page' => 'home',
            'featured_products' => $product->getFeaturedProducts(8),
            'categories' => $product->getCategories(),
            'maquinaria_products' => $product->getProducts(['tipo' => 'maquinaria', 'limit' => 7]),
            'materiales_products' => $product->getProducts(['tipo' => 'material', 'limit' => 4]),
            'current_user' => $auth->getCurrentUser(),
            'success_message' => $this->getSuccessMessage()
        ];
        
        $this->view('home/index', $data);
    }
    
    public function quienesSomos() {
        $auth = new Auth();
        $data = [
            'title' => 'Quiénes Somos - AlquiVenta',
            'current_page' => 'quienes-somos',
            'current_user' => $auth->getCurrentUser()
        ];
        $this->view('home/quienes-somos', $data);
    }
    
    private function getSuccessMessage() {
        if (isset($_GET['success'])) {
            switch ($_GET['success']) {
                case 'product_created':
                    return 'Producto creado exitosamente';
                case 'product_updated':
                    return 'Producto actualizado exitosamente';
                case 'registration_complete':
                    return '¡Registro completado exitosamente! Ya puedes iniciar sesión.';
                default:
                    return '';
            }
        }
        return '';
    }
}

