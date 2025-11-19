<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Auth;
use App\Models\Rental;

/**
 * Controlador de alquileres
 */
class RentalController extends Controller {
    
    public function index() {
        $auth = new Auth();
        $rental = new Rental();
        
        $current_user = $auth->getCurrentUser();
        if (!$current_user) {
            $this->redirect('/login');
        }
        
        $estado = $this->get('estado');
        $rentals = $rental->getUserRentals($current_user['id'], $estado);
        
        if (isset($rentals['error'])) {
            $rentals = [];
        }
        
        $data = [
            'title' => 'Mis Alquileres',
            'current_user' => $current_user,
            'rentals' => $rentals,
            'estado' => $estado
        ];
        
        $this->view('rental/index', $data);
    }
    
    public function showRental($id) {
        $auth = new Auth();
        $product = new \App\Models\Product();
        $rental = new Rental();
        
        $current_user = $auth->getCurrentUser();
        if (!$current_user) {
            $this->redirect('/login');
        }
        
        $product_data = $product->getProductById($id);
        if (!$product_data || isset($product_data['error'])) {
            $this->redirect('/alquiler');
        }
        
        $es_maquinaria = isset($product_data['categoria_tipo']) && $product_data['categoria_tipo'] === 'maquinaria';
        if (!$es_maquinaria && (empty($product_data['precio_alquiler_dia']) || $product_data['precio_alquiler_dia'] <= 0)) {
            $this->redirect('/alquiler?error=producto_no_alquilable');
        }
        
        if (empty($product_data['precio_alquiler_dia']) || $product_data['precio_alquiler_dia'] <= 0) {
            $product_data['precio_alquiler_dia'] = 0;
        }
        
        $data = [
            'title' => 'Agendar Alquiler',
            'current_user' => $current_user,
            'product' => $product_data,
            'product_id' => $id
        ];
        
        $this->view('rental/show', $data);
    }
}

