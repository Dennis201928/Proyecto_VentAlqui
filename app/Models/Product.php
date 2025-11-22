<?php
namespace App\Models;

use App\Core\Model;

/**
 * Clase para manejo de productos
 */
class Product extends Model {

    /**
     * Convertir array PHP a formato de array PostgreSQL
     */
    private function arrayToPostgresArray($array) {
        if (empty($array) || !is_array($array)) {
            return null;
        }
        
        $escaped_array = array_map(function($item) {
            return '"' . addslashes($item) . '"';
        }, $array);
        
        return '{' . implode(',', $escaped_array) . '}';
    }

    /**
     * Obtener todos los productos con filtros
     */
    public function getProducts($filters = []) {
        try {
            $query = "SELECT p.*, c.nombre as categoria_nombre, c.tipo as categoria_tipo 
                     FROM productos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.activo = true";
            
            $params = [];
            
            // Filtros
            if (isset($filters['categoria_id']) && !empty($filters['categoria_id'])) {
                $query .= " AND p.categoria_id = :categoria_id";
                $params[':categoria_id'] = $filters['categoria_id'];
            }
            
            if (isset($filters['tipo']) && !empty($filters['tipo'])) {
                $query .= " AND c.tipo = :tipo";
                $params[':tipo'] = $filters['tipo'];
            }
            
            if (isset($filters['estado']) && !empty($filters['estado'])) {
                $query .= " AND p.estado = :estado";
                $params[':estado'] = $filters['estado'];
            }
            
            if (isset($filters['search']) && !empty($filters['search'])) {
                $query .= " AND (p.nombre ILIKE :search OR p.descripcion ILIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            if (isset($filters['precio_min']) && !empty($filters['precio_min'])) {
                $query .= " AND p.precio_venta >= :precio_min";
                $params[':precio_min'] = $filters['precio_min'];
            }
            
            if (isset($filters['precio_max']) && !empty($filters['precio_max'])) {
                $query .= " AND p.precio_venta <= :precio_max";
                $params[':precio_max'] = $filters['precio_max'];
            }
            
            // Ordenamiento
            $order_by = 'p.fecha_creacion DESC';
            if (isset($filters['order_by'])) {
                switch ($filters['order_by']) {
                    case 'precio_asc':
                        $order_by = 'p.precio_venta ASC';
                        break;
                    case 'precio_desc':
                        $order_by = 'p.precio_venta DESC';
                        break;
                    case 'nombre_asc':
                        $order_by = 'p.nombre ASC';
                        break;
                    case 'nombre_desc':
                        $order_by = 'p.nombre DESC';
                        break;
                }
            }
            $query .= " ORDER BY " . $order_by;
            
            // Paginación
            if (isset($filters['limit']) && isset($filters['offset'])) {
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = (int)$filters['limit'];
                $params[':offset'] = (int)$filters['offset'];
            }
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener un producto por ID
     */
    public function getProductById($id) {
        try {
            $query = "SELECT p.*, c.nombre as categoria_nombre, c.tipo as categoria_tipo 
                     FROM productos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.id = :id AND p.activo = true";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Crear un nuevo producto
     */
    public function createProduct($data) {
        try {
            $query = "INSERT INTO productos (nombre, descripcion, categoria_id, precio_venta, precio_alquiler_dia, 
                     stock_disponible, stock_minimo, imagen_principal, imagenes_adicionales, especificaciones, estado) 
                     VALUES (:nombre, :descripcion, :categoria_id, :precio_venta, :precio_alquiler_dia, 
                     :stock_disponible, :stock_minimo, :imagen_principal, :imagenes_adicionales, :especificaciones, :estado)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':categoria_id', $data['categoria_id']);
            $stmt->bindParam(':precio_venta', $data['precio_venta']);
            $stmt->bindParam(':precio_alquiler_dia', $data['precio_alquiler_dia']);
            $stmt->bindParam(':stock_disponible', $data['stock_disponible']);
            $stmt->bindParam(':stock_minimo', $data['stock_minimo']);
            $stmt->bindParam(':imagen_principal', $data['imagen_principal']);
            
            // Manejar array de imágenes adicionales
            $imagenes_adicionales = $this->arrayToPostgresArray($data['imagenes_adicionales'] ?? []);
            $stmt->bindParam(':imagenes_adicionales', $imagenes_adicionales);
            
            $stmt->bindParam(':especificaciones', $data['especificaciones']);
            $stmt->bindParam(':estado', $data['estado']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'id' => $this->conn->lastInsertId(), 'message' => 'Producto creado exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al crear producto'];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar un producto
     */
    public function updateProduct($id, $data) {
        try {
            $query = "UPDATE productos SET nombre = :nombre, descripcion = :descripcion, categoria_id = :categoria_id, 
                     precio_venta = :precio_venta, precio_alquiler_dia = :precio_alquiler_dia, 
                     stock_disponible = :stock_disponible, stock_minimo = :stock_minimo, 
                     imagen_principal = :imagen_principal, imagenes_adicionales = :imagenes_adicionales, 
                     especificaciones = :especificaciones, estado = :estado, 
                     fecha_actualizacion = CURRENT_TIMESTAMP 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':categoria_id', $data['categoria_id']);
            $stmt->bindParam(':precio_venta', $data['precio_venta']);
            $stmt->bindParam(':precio_alquiler_dia', $data['precio_alquiler_dia']);
            $stmt->bindParam(':stock_disponible', $data['stock_disponible']);
            $stmt->bindParam(':stock_minimo', $data['stock_minimo']);
            $stmt->bindParam(':imagen_principal', $data['imagen_principal']);
            
            // Buscar dentro de un array de imagenes la imagen que le pertenece
            $imagenes_adicionales = $this->arrayToPostgresArray($data['imagenes_adicionales'] ?? []);
            $stmt->bindParam(':imagenes_adicionales', $imagenes_adicionales);
            
            $stmt->bindParam(':especificaciones', $data['especificaciones']);
            $stmt->bindParam(':estado', $data['estado']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Producto actualizado exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar producto'];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar un producto (soft delete)
     */
    public function deleteProduct($id) {
        try {
            $query = "UPDATE productos SET activo = false, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Producto eliminado exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar producto'];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener categorías
     */
    public function getCategories($tipo = null) {
        try {
            $query = "SELECT * FROM categorias WHERE activa = true";
            $params = [];
            
            if ($tipo) {
                $query .= " AND tipo = :tipo";
                $params[':tipo'] = $tipo;
            }
            
            $query .= " ORDER BY nombre";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener productos relacionados
     */
    public function getRelatedProducts($product_id, $categoria_id, $limit = 4) {
        try {
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                     FROM productos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.categoria_id = :categoria_id AND p.id != :product_id AND p.activo = true 
                     ORDER BY RANDOM() 
                     LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':categoria_id', $categoria_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Buscar productos
     */
    public function searchProducts($search_term, $filters = []) {
        $filters['search'] = $search_term;
        return $this->getProducts($filters);
    }

    /**
     * Obtener productos destacados
     */
    public function getFeaturedProducts($limit = 8) {
        try {
            $query = "SELECT p.*, c.nombre as categoria_nombre, c.tipo as categoria_tipo 
                     FROM productos p 
                     LEFT JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.activo = true AND p.estado = 'disponible' 
                     ORDER BY p.fecha_creacion DESC 
                     LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Verificar disponibilidad para alquiler considerando stock
     */
    public function checkAvailability($product_id, $fecha_inicio, $fecha_fin) {
        try {
            // Obtener stock disponible del producto
            $product_info = $this->getProductById($product_id);
            if (!$product_info || !isset($product_info['stock_disponible'])) {
                return false;
            }
            
            $stock_disponible = (int)$product_info['stock_disponible'];
            
            // Si el stock es mayor a 0 se puede seleccionar cualquier día
            return $stock_disponible > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
?>
