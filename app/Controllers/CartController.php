<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Auth;
use App\Models\Cart;

/**
 * Controlador de carrito
 */
class CartController extends Controller {
    
    public function index() {
        $auth = new Auth();
        $cart = new Cart();
        
        $current_user = $auth->getCurrentUser();
        if (!$current_user) {
            $this->redirect('/login');
        }
        
        $cart_items = $cart->getCartItems($current_user['id']);
        $cart_total = $cart->getCartTotal($current_user['id']);
        
        $data = [
            'title' => 'Carrito de Compras',
            'current_user' => $current_user,
            'cart_items' => $cart_items,
            'cart_total' => $cart_total
        ];
        
        $this->view('cart/index', $data);
    }
    
    public function checkout() {
        $auth = new Auth();
        $cart = new Cart();
        $order = new \App\Models\Order();
        
        $current_user = $auth->getCurrentUser();
        if (!$current_user) {
            $this->redirect('/login');
        }
        
        $cart_items = $cart->getCartItems($current_user['id']);
        $cart_total = $cart->getCartTotal($current_user['id']);
        
        if (empty($cart_items) || isset($cart_items['error'])) {
            $this->redirect('/carrito');
        }
        
        if ($this->isPost()) {
            $metodo_pago = $this->post('metodo_pago');
            $direccion_entrega = $this->post('direccion_entrega');
            
            if (empty($metodo_pago)) {
                $data = [
                    'title' => 'Checkout',
                    'current_user' => $current_user,
                    'cart_items' => $cart_items,
                    'cart_total' => $cart_total,
                    'error' => 'Debe seleccionar un método de pago'
                ];
                $this->view('cart/checkout', $data);
                return;
            }
            
            $result = $order->createOrderFromCart($current_user['id'], $metodo_pago, $direccion_entrega);
            
            if ($result['success']) {
                $this->redirect('/pedido-exitoso/' . $result['venta_id']);
            } else {
                $data = [
                    'title' => 'Checkout',
                    'current_user' => $current_user,
                    'cart_items' => $cart_items,
                    'cart_total' => $cart_total,
                    'error' => $result['message']
                ];
                $this->view('cart/checkout', $data);
            }
            return;
        }
        
        $data = [
            'title' => 'Checkout',
            'current_user' => $current_user,
            'cart_items' => $cart_items,
            'cart_total' => $cart_total
        ];
        
        $this->view('cart/checkout', $data);
    }
}

