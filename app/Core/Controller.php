<?php
namespace App\Core;

/**
 * Clase base para todos los controladores
 */
abstract class Controller {
    protected $view;

    public function __construct() {
        $this->view = new View();
    }

    /**
     * Renderizar una vista
     */
    protected function view($view, $data = [], $layout = 'main') {
        $this->view->render($view, $data, $layout);
    }

    /**
     * Incluir una vista parcial
     */
    protected function partial($partial, $data = []) {
        $this->view->partial($partial, $data);
    }

    /**
     * Redirigir a una URL
     */
    protected function redirect($url) {
        if (strpos($url, 'http') !== 0) {
            $url = \App\Core\Config::SITE_URL . $url;
        }
        header("Location: {$url}");
        exit();
    }

    /**
     * Respuesta JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Obtener POST
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtener GET
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}

