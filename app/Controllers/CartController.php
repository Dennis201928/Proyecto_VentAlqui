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
            
            // Validar dirección solo si es transferencia bancaria
            if ($metodo_pago === 'transferencia' && empty($direccion_entrega)) {
                $data = [
                    'title' => 'Checkout',
                    'current_user' => $current_user,
                    'cart_items' => $cart_items,
                    'cart_total' => $cart_total,
                    'error' => 'Debe ingresar la dirección de entrega para transferencia bancaria'
                ];
                $this->view('cart/checkout', $data);
                return;
            }
            
            // Validar y subir comprobante si es transferencia bancaria
            $comprobante_path = null;
            if ($metodo_pago === 'transferencia') {
                if (!isset($_FILES['comprobante_pago']) || $_FILES['comprobante_pago']['error'] !== UPLOAD_ERR_OK) {
                    $data = [
                        'title' => 'Checkout',
                        'current_user' => $current_user,
                        'cart_items' => $cart_items,
                        'cart_total' => $cart_total,
                        'error' => 'Debe subir el comprobante de pago para transferencia bancaria'
                    ];
                    $this->view('cart/checkout', $data);
                    return;
                }
                
                // Validar y subir el comprobante
                $comprobante_result = $this->uploadComprobante($_FILES['comprobante_pago']);
                if (!$comprobante_result['success']) {
                    $data = [
                        'title' => 'Checkout',
                        'current_user' => $current_user,
                        'cart_items' => $cart_items,
                        'cart_total' => $cart_total,
                        'error' => $comprobante_result['message']
                    ];
                    $this->view('cart/checkout', $data);
                    return;
                }
                $comprobante_path = $comprobante_result['path'];
            }
            
            $result = $order->createOrderFromCart($current_user['id'], $metodo_pago, $direccion_entrega, $comprobante_path);
            
            if ($result['success']) {
                $this->redirect('/pedido-exitoso/' . $result['venta_id']);
            } else {
                // Si falló, eliminar el comprobante subido
                if ($comprobante_path && file_exists($comprobante_path)) {
                    @unlink($comprobante_path);
                }
                
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
    
    public function remove($item_id) {
        $auth = new Auth();
        $cart = new Cart();
        
        $current_user = $auth->getCurrentUser();
        if (!$current_user) {
            $this->redirect('/login');
        }
        
        $result = $cart->removeFromCart($current_user['id'], $item_id);
        
        if ($result['success']) {
            $this->redirect('/carrito?message=' . urlencode('Producto eliminado del carrito'));
        } else {
            $this->redirect('/carrito?error=' . urlencode($result['message']));
        }
    }
    
    /**
     * Subir comprobante de pago
     */
    private function uploadComprobante($file) {
        // Validar error de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
                UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo'
            ];
            return [
                'success' => false,
                'message' => $error_messages[$file['error']] ?? 'Error desconocido al subir el archivo'
            ];
        }
        
        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
            return [
                'success' => false,
                'message' => 'Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, GIF, WebP) y PDF'
            ];
        }
        
        // Validar tamaño (máximo 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            return [
                'success' => false,
                'message' => 'El archivo es demasiado grande. Máximo 5MB'
            ];
        }
        
        // Crear directorio de comprobantes si no existe
        $project_root = dirname(dirname(__DIR__));
        $upload_dir = $project_root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'comprobantes' . DIRECTORY_SEPARATOR;
        
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                return [
                    'success' => false,
                    'message' => 'Error al crear el directorio de comprobantes'
                ];
            }
        }
        
        // Generar nombre único
        $filename = 'comprobante_' . time() . '_' . uniqid() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => false,
                'message' => 'Error al guardar el archivo'
            ];
        }
        
        // Retornar ruta relativa desde public
        $relative_path = 'assets/comprobantes/' . $filename;
        
        return [
            'success' => true,
            'path' => $relative_path
        ];
    }
}

