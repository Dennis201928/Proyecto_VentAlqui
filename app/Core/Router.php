<?php
namespace App\Core;

/**
 * Sistema de routing
 */
class Router {
    private $routes = [];
    private $middleware = [];

    /**
     * Registrar una ruta GET
     */
    public function get($path, $handler, $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Registrar una ruta POST
     */
    public function post($path, $handler, $middleware = []) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Registrar una ruta PUT
     */
    public function put($path, $handler, $middleware = []) {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Registrar una ruta DELETE
     */
    public function delete($path, $handler, $middleware = []) {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Agregar una ruta
     */
    private function addRoute($method, $path, $handler, $middleware = []) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Ejecutar el router
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();
        

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertToRegex($route['path']);
            
            if (preg_match($pattern, $uri, $matches)) {
                // Ejecutar middleware
                if (!empty($route['middleware'])) {
                    foreach ($route['middleware'] as $middlewareName) {
                        if (!$this->executeMiddleware($middlewareName)) {
                            // El middleware ya manejó la respuesta (redirect o error)
                            return;
                        }
                    }
                }

                // Extraer parámetros de la URL
                array_shift($matches);
                $params = array_values($matches);

                // Ejecutar el handler
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // Ruta no encontrada
        http_response_code(404);
        echo "404 - Página no encontrada";
    }

    /**
     * Convertir patrón de ruta a regex
     */
    private function convertToRegex($path) {
        // Reemplazar :param con regex
        $pattern = preg_replace('/:(\w+)/', '([^/]+)', $path);
        // Escapar barras
        $pattern = str_replace('/', '\/', $pattern);
        // Agregar delimitadores
        return '/^' . $pattern . '$/';
    }

    /**
     * Obtener URI de la petición
     */
    private function getUri() {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remover query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remover base path si existe
        $basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_NAME']));
        $basePath = str_replace('\\', '/', $basePath);
        
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Ejecutar middleware
     */
    private function executeMiddleware($middlewareName) {
        // Middleware especiales
        if ($middlewareName === 'auth') {
            $middlewareName = 'Auth';
        } elseif ($middlewareName === 'admin') {
            $middlewareName = 'Admin';
        }
        
        $middlewareClass = "App\\Middleware\\" . ucfirst($middlewareName) . "Middleware";
        
        if (class_exists($middlewareClass)) {
            $middleware = new $middlewareClass();
            return $middleware->handle();
        }

        return true;
    }

    /**
     * Ejecutar handler de la ruta
     */
    private function executeHandler($handler, $params = []) {
        try {
            if (is_array($handler) && count($handler) === 2) {
                list($controllerClass, $method) = $handler;
                
                if (is_string($controllerClass) && strpos($controllerClass, '\\') === false) {
                    $controllerClass = "App\\Controllers\\" . $controllerClass;
                }
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    
                    if (method_exists($controller, $method)) {
                        call_user_func_array([$controller, $method], $params);
                        return;
                    } else {
                        throw new \Exception("Método '{$method}' no existe en {$controllerClass}");
                    }
                } else {
                    throw new \Exception("Clase '{$controllerClass}' no existe");
                }
            }

            elseif (is_string($handler) && strpos($handler, '@') !== false) {
                list($controllerName, $method) = explode('@', $handler);
                $controllerClass = "App\\Controllers\\" . $controllerName;
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    
                    if (method_exists($controller, $method)) {
                        call_user_func_array([$controller, $method], $params);
                        return;
                    } else {
                        throw new \Exception("Método '{$method}' no existe en {$controllerClass}");
                    }
                } else {
                    throw new \Exception("Clase '{$controllerClass}' no existe");
                }
            }

            elseif (is_callable($handler)) {
                call_user_func_array($handler, $params);
                return;
            }
            
            throw new \Exception("Handler inválido: " . gettype($handler));
        } catch (\Exception $e) {
            // Log del error
            error_log("Error en executeHandler: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
            
            http_response_code(500);
            echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Error</title></head><body>";
            echo "<h1>500 - Error interno del servidor</h1>";
            echo "<p>Ha ocurrido un error. Por favor, intente más tarde.</p>";
            echo "</body></html>";
        } catch (\Error $e) {
            // Capturar errores fatales también
            error_log("Fatal error en executeHandler: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
            
            http_response_code(500);
            echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Error Fatal</title></head><body>";
            echo "<h1>500 - Error Fatal</h1>";
            echo "<p>Ha ocurrido un error. Por favor, intente más tarde.</p>";
            echo "</body></html>";
        }
    }
}

