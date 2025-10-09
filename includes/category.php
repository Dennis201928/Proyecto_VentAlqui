<?php
/**
 * Clase para manejo de categorías
 */

require_once __DIR__ . '/../config/database.php';

class Category {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Obtener todas las categorías con filtros
     */
    public function getCategories($tipo = null) {
        try {
            $query = "SELECT * FROM categorias WHERE activa = true";
            $params = [];
            
            if ($tipo) {
                $query .= " AND tipo = :tipo";
                $params[':tipo'] = $tipo;
            }
            
            $query .= " ORDER BY tipo, nombre";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener una categoría por ID
     */
    public function getCategoryById($id) {
        try {
            $query = "SELECT * FROM categorias WHERE id = :id AND activa = true";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Crear una nueva categoría
     */
    public function createCategory($data) {
        try {
            // Validar datos requeridos
            if (empty($data['nombre']) || empty($data['tipo'])) {
                return ['success' => false, 'message' => 'El nombre y tipo son obligatorios'];
            }

            // Verificar si ya existe una categoría con el mismo nombre y tipo
            $check_query = "SELECT id FROM categorias WHERE nombre = :nombre AND tipo = :tipo AND activa = true";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':nombre', $data['nombre']);
            $check_stmt->bindParam(':tipo', $data['tipo']);
            $check_stmt->execute();
            
            if ($check_stmt->fetch()) {
                return ['success' => false, 'message' => 'Ya existe una categoría con este nombre y tipo'];
            }

            $query = "INSERT INTO categorias (nombre, descripcion, tipo) 
                     VALUES (:nombre, :descripcion, :tipo)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':tipo', $data['tipo']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Categoría creada exitosamente', 'id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Error al crear categoría'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar una categoría
     */
    public function updateCategory($id, $data) {
        try {
            // Validar datos requeridos
            if (empty($data['nombre']) || empty($data['tipo'])) {
                return ['success' => false, 'message' => 'El nombre y tipo son obligatorios'];
            }

            // Verificar si ya existe otra categoría con el mismo nombre y tipo
            $check_query = "SELECT id FROM categorias WHERE nombre = :nombre AND tipo = :tipo AND id != :id AND activa = true";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':nombre', $data['nombre']);
            $check_stmt->bindParam(':tipo', $data['tipo']);
            $check_stmt->bindParam(':id', $id);
            $check_stmt->execute();
            
            if ($check_stmt->fetch()) {
                return ['success' => false, 'message' => 'Ya existe otra categoría con este nombre y tipo'];
            }

            $query = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion, tipo = :tipo 
                     WHERE id = :id AND activa = true";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':tipo', $data['tipo']);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Categoría actualizada exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar categoría'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar una categoría (soft delete)
     */
    public function deleteCategory($id) {
        try {
            // Verificar si hay productos asociados a esta categoría
            $check_query = "SELECT COUNT(*) as count FROM productos WHERE categoria_id = :id AND activo = true";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':id', $id);
            $check_stmt->execute();
            $result = $check_stmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'No se puede eliminar la categoría porque tiene productos asociados'];
            }

            $query = "UPDATE categorias SET activa = false WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Categoría eliminada exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar categoría'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener estadísticas de categorías
     */
    public function getCategoryStats() {
        try {
            $query = "SELECT c.id, c.nombre, c.tipo, COUNT(p.id) as total_productos
                     FROM categorias c
                     LEFT JOIN productos p ON c.id = p.categoria_id AND p.activo = true
                     WHERE c.activa = true
                     GROUP BY c.id, c.nombre, c.tipo
                     ORDER BY c.tipo, c.nombre";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }
}
?>
