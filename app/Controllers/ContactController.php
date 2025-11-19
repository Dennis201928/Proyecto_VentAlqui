<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Auth;

/**
 * Controlador de contacto
 */
class ContactController extends Controller {
    
    public function index() {
        $auth = new Auth();
        $contactService = new \App\Helpers\SimpleContactService();
        
        $error = '';
        $success = '';
        
        if ($this->isPost()) {
            $nombre = trim($this->post('nombre', ''));
            $email = trim($this->post('email', ''));
            $mensaje = trim($this->post('mensaje', ''));
            
            if (empty($nombre) || empty($email) || empty($mensaje)) {
                $error = 'Todos los campos son requeridos';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Por favor ingresa un correo electrónico válido';
            } else {

                $contactService->saveContactMessage($nombre, $email, 'Contacto desde web', $mensaje);
                
                if ($contactService->sendContactEmail($nombre, $email, 'Contacto desde web', $mensaje)) {
                    $success = 'Mensaje enviado exitosamente. Te contactaremos pronto.';
                } else {
                    $error = 'Error al enviar el mensaje. Por favor intenta de nuevo o contacta directamente a info@alquivent.com';
                }
            }
        }
        
        $data = [
            'title' => 'Contacto - AlquiVenta',
            'current_page' => 'contacto',
            'current_user' => $auth->getCurrentUser(),
            'error' => $error,
            'success' => $success
        ];
        
        $this->view('contact/index', $data);
    }
}

