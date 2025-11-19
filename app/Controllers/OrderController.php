<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Auth;
use App\Models\Order;

/**
 * Controlador de pedidos
 */
class OrderController extends Controller {
    
    public function success($id) {
        $auth = new Auth();
        $order = new Order();
        
        $current_user = $auth->getCurrentUser();
        if (!$current_user) {
            $this->redirect('/login');
        }
        
        $order_details = $order->getOrderDetails($id);
        
        if (!$order_details || isset($order_details['error'])) {
            $this->redirect('/');
        }
        
        // Verificar que el pedido pertenece al usuario
        if ($order_details['order']['usuario_id'] != $current_user['id']) {
            $this->redirect('/');
        }
        
        $data = [
            'title' => 'Pedido Exitoso',
            'current_user' => $current_user,
            'order_details' => $order_details
        ];
        
        $this->view('order/success', $data);
    }
}

