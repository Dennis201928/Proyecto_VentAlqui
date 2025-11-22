<?php
namespace App\Core;

/**
 * Motor de vistas
 */
class View {
    private $basePath;
    private $currentData = [];

    public function __construct($basePath = null) {
        $this->basePath = $basePath ?: __DIR__ . '/../Views';
    }

    /**
     * Renderizar una vista
     */
    public function render($view, $data = [], $layout = 'main') {
        // Agregar baseUrl a los datos para las vistas
        if (!isset($data['baseUrl'])) {
            $data['baseUrl'] = \App\Core\Config::SITE_URL;
        }
        
        // Hacer que $viewInstance esté disponible en las vistas para llamar a partial()
        $data['viewInstance'] = $this;
        
        // Guardar una referencia a los datos para usar en partial()
        $this->currentData = $data;
        
        // Extraer variables del array $data
        extract($data);

        // Iniciar buffer de salida
        ob_start();

        // Incluir la vista
        $viewPath = $this->basePath . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("Vista no encontrada: {$viewPath}");
        }

        include $viewPath;
        $content = ob_get_clean();

        if ($layout) {
            $layoutPath = $this->basePath . '/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                if (!isset($data['current_user'])) {
                    $auth = new \App\Models\Auth();
                    $data['current_user'] = $auth->getCurrentUser();
                }
                if (!isset($data['baseUrl'])) {
                    $data['baseUrl'] = \App\Core\Config::SITE_URL;
                }
                $data['content'] = $content;
                extract($data);
                include $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Incluir una vista parcial
     */
    public function partial($partial, $data = []) {
        $partialData = array_merge($this->currentData, $data);
        
        if (!isset($partialData['baseUrl'])) {
            $partialData['baseUrl'] = \App\Core\Config::SITE_URL;
        }
        
        extract($partialData);
        $partialPath = $this->basePath . '/partials/' . $partial . '.php';
        
        if (file_exists($partialPath)) {
            include $partialPath;
        } else {
            throw new \Exception("Vista parcial no encontrada: {$partialPath}");
        }
    }
}

