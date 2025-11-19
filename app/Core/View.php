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

        // Si hay layout, incluirlo
        if ($layout) {
            $layoutPath = $this->basePath . '/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                // Asegurar que current_user esté definido para el layout
                if (!isset($data['current_user'])) {
                    $auth = new \App\Models\Auth();
                    $data['current_user'] = $auth->getCurrentUser();
                }
                // Asegurar que baseUrl esté definido
                if (!isset($data['baseUrl'])) {
                    $data['baseUrl'] = \App\Core\Config::SITE_URL;
                }
                // Agregar el contenido de la vista a los datos
                $data['content'] = $content;
                // Extraer variables nuevamente para el layout (incluyendo baseUrl, current_user y content)
                extract($data);
                
                // Incluir el layout con el contenido
                include $layoutPath;
            } else {
                // Layout no encontrado, mostrar solo el contenido
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
        // Combinar datos actuales con los pasados (los pasados tienen prioridad)
        $partialData = array_merge($this->currentData, $data);
        
        // Asegurar que baseUrl esté definido
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

