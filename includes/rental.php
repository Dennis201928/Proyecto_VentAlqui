<?php
/**
 * Sistema de alquileres
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/product.php';

class Rental {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Crear un nuevo alquiler
     */
    public function createRental($user_id, $product_id, $fecha_inicio, $fecha_fin, $observaciones = null) {
        try {
            // Verificar disponibilidad
            $product = new Product();
            if (!$product->checkAvailability($product_id, $fecha_inicio, $fecha_fin)) {
                return ['success' => false, 'message' => 'El producto no está disponible en las fechas seleccionadas'];
            }

            // Obtener información del producto
            $product_info = $product->getProductById($product_id);
            if (!$product_info) {
                return ['success' => false, 'message' => 'Producto no encontrado'];
            }

            // Calcular total
            $dias = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / (60 * 60 * 24);
            if ($dias <= 0) {
                return ['success' => false, 'message' => 'El rango de fechas no es válido'];
            }
            
            $precio_dia = isset($product_info['precio_alquiler_dia']) && $product_info['precio_alquiler_dia'] > 0 
                         ? $product_info['precio_alquiler_dia'] 
                         : 0;
            $total = $precio_dia * $dias;

            // Crear alquiler
            $query = "INSERT INTO alquileres (usuario_id, producto_id, fecha_inicio, fecha_fin, precio_dia, total, observaciones) 
                     VALUES (:usuario_id, :producto_id, :fecha_inicio, :fecha_fin, :precio_dia, :total, :observaciones)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $user_id);
            $stmt->bindParam(':producto_id', $product_id);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':precio_dia', $precio_dia);
            $stmt->bindParam(':total', $total);
            $stmt->bindParam(':observaciones', $observaciones);

            if ($stmt->execute()) {
                $rental_id = $this->conn->lastInsertId();
                return ['success' => true, 'id' => $rental_id, 'message' => 'Alquiler creado exitosamente', 'total' => $total];
            } else {
                return ['success' => false, 'message' => 'Error al crear alquiler'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener alquileres de un usuario
     */
    public function getUserRentals($user_id, $estado = null) {
        try {
            $query = "SELECT a.*, p.nombre as producto_nombre, p.imagen_principal, c.nombre as categoria_nombre
                     FROM alquileres a 
                     JOIN productos p ON a.producto_id = p.id 
                     LEFT JOIN categorias c ON p.categoria_id = c.id
                     WHERE a.usuario_id = :usuario_id";
            
            $params = [':usuario_id' => $user_id];
            
            if ($estado) {
                $query .= " AND a.estado = :estado";
                $params[':estado'] = $estado;
            }
            
            $query .= " ORDER BY a.fecha_creacion DESC";
            
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
     * Obtener todos los alquileres (admin)
     */
    public function getAllRentals($filters = []) {
        try {
            $query = "SELECT a.*, p.nombre as producto_nombre, p.imagen_principal, c.nombre as categoria_nombre,
                     u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.email as usuario_email
                     FROM alquileres a 
                     JOIN productos p ON a.producto_id = p.id 
                     LEFT JOIN categorias c ON p.categoria_id = c.id
                     JOIN usuarios u ON a.usuario_id = u.id
                     WHERE 1=1";
            
            $params = [];
            
            if (isset($filters['estado'])) {
                $query .= " AND a.estado = :estado";
                $params[':estado'] = $filters['estado'];
            }
            
            if (isset($filters['fecha_desde'])) {
                $query .= " AND a.fecha_inicio >= :fecha_desde";
                $params[':fecha_desde'] = $filters['fecha_desde'];
            }
            
            if (isset($filters['fecha_hasta'])) {
                $query .= " AND a.fecha_fin <= :fecha_hasta";
                $params[':fecha_hasta'] = $filters['fecha_hasta'];
            }
            
            if (isset($filters['usuario_id'])) {
                $query .= " AND a.usuario_id = :usuario_id";
                $params[':usuario_id'] = $filters['usuario_id'];
            }
            
            $query .= " ORDER BY a.fecha_creacion DESC";
            
            if (isset($filters['limit'])) {
                $query .= " LIMIT :limit";
                $params[':limit'] = (int)$filters['limit'];
            }
            
            if (isset($filters['offset'])) {
                $query .= " OFFSET :offset";
                $params[':offset'] = (int)$filters['offset'];
            }
            
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
     * Obtener un alquiler por ID
     */
    public function getRentalById($id) {
        try {
            $query = "SELECT a.*, p.nombre as producto_nombre, p.descripcion as producto_descripcion, 
                     p.imagen_principal, c.nombre as categoria_nombre,
                     u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.email as usuario_email,
                     u.telefono as usuario_telefono, u.direccion as usuario_direccion
                     FROM alquileres a 
                     JOIN productos p ON a.producto_id = p.id 
                     LEFT JOIN categorias c ON p.categoria_id = c.id
                     JOIN usuarios u ON a.usuario_id = u.id
                     WHERE a.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar estado de un alquiler
     */
    public function updateRentalStatus($id, $estado, $observaciones = null) {
        try {
            $query = "UPDATE alquileres SET estado = :estado, observaciones = :observaciones, 
                     fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':observaciones', $observaciones);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Estado actualizado exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar estado'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Cancelar alquiler
     */
    public function cancelRental($id, $user_id) {
        try {
            // Verificar que el alquiler pertenece al usuario
            $query = "SELECT estado FROM alquileres WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':usuario_id', $user_id);
            $stmt->execute();
            
            $rental = $stmt->fetch();
            if (!$rental) {
                return ['success' => false, 'message' => 'Alquiler no encontrado'];
            }
            
            if ($rental['estado'] == 'finalizado') {
                return ['success' => false, 'message' => 'No se puede cancelar un alquiler finalizado'];
            }
            
            $result = $this->updateRentalStatus($id, 'cancelado', 'Cancelado por el usuario');
            return $result;
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener estadísticas de alquileres
     */
    public function getRentalStats($filters = []) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_alquileres,
                        COUNT(CASE WHEN estado = 'confirmado' THEN 1 END) as alquileres_confirmados,
                        COUNT(CASE WHEN estado = 'en_curso' THEN 1 END) as alquileres_en_curso,
                        COUNT(CASE WHEN estado = 'finalizado' THEN 1 END) as alquileres_finalizados,
                        COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) as alquileres_cancelados,
                        SUM(CASE WHEN estado IN ('confirmado', 'en_curso', 'finalizado') THEN total ELSE 0 END) as ingresos_totales
                     FROM alquileres";
            
            $params = [];
            
            if (isset($filters['fecha_desde'])) {
                $query .= " WHERE fecha_creacion >= :fecha_desde";
                $params[':fecha_desde'] = $filters['fecha_desde'];
            }
            
            if (isset($filters['fecha_hasta'])) {
                $query .= (isset($filters['fecha_desde']) ? " AND" : " WHERE") . " fecha_creacion <= :fecha_hasta";
                $params[':fecha_hasta'] = $filters['fecha_hasta'];
            }
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener productos más alquilados
     */
    public function getMostRentedProducts($limit = 10) {
        try {
            $query = "SELECT p.id, p.nombre, p.imagen_principal, c.nombre as categoria_nombre,
                     COUNT(a.id) as total_alquileres,
                     SUM(a.total) as ingresos_totales
                     FROM productos p
                     LEFT JOIN alquileres a ON p.id = a.producto_id
                     LEFT JOIN categorias c ON p.categoria_id = c.id
                     WHERE p.activo = true
                     GROUP BY p.id, p.nombre, p.imagen_principal, c.nombre
                     ORDER BY total_alquileres DESC
                     LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Verificar disponibilidad en un rango de fechas
     */
    public function checkAvailabilityInRange($product_id, $fecha_inicio, $fecha_fin) {
        try {
            $query = "SELECT COUNT(*) as count FROM alquileres 
                     WHERE producto_id = :product_id 
                     AND estado IN ('confirmado', 'en_curso') 
                     AND ((fecha_inicio <= :fecha_fin AND fecha_fin >= :fecha_inicio))";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result['count'] == 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtener fechas ocupadas de un producto para el calendario
     */
    public function getBookedDates($product_id, $fecha_desde = null, $fecha_hasta = null) {
        try {
            // Verificar que la conexión existe
            if (!$this->conn) {
                $this->db = new Database();
                $this->conn = $this->db->getConnection();
            }
            
            if (!$this->conn) {
                return ['error' => 'No se pudo conectar a la base de datos'];
            }
            
            $query = "SELECT fecha_inicio, fecha_fin, estado 
                     FROM alquileres 
                     WHERE producto_id = :product_id 
                     AND estado IN ('pendiente', 'confirmado', 'en_curso')";
            
            $params = [':product_id' => $product_id];
            
            if ($fecha_desde) {
                $query .= " AND fecha_fin >= :fecha_desde";
                $params[':fecha_desde'] = $fecha_desde;
            }
            
            if ($fecha_hasta) {
                $query .= " AND fecha_inicio <= :fecha_hasta";
                $params[':fecha_hasta'] = $fecha_hasta;
            }
            
            $query .= " ORDER BY fecha_inicio ASC";
            
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['error' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener alquileres con filtros avanzados para admin (categoría, producto, nombre)
     */
    public function getAllRentalsWithFilters($filters = []) {
        try {
            $query = "SELECT a.*, p.id as producto_id, p.nombre as producto_nombre, p.imagen_principal, 
                     c.id as categoria_id, c.nombre as categoria_nombre,
                     u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.email as usuario_email
                     FROM alquileres a 
                     JOIN productos p ON a.producto_id = p.id 
                     LEFT JOIN categorias c ON p.categoria_id = c.id
                     JOIN usuarios u ON a.usuario_id = u.id
                     WHERE 1=1";
            
            $params = [];
            
            if (isset($filters['estado']) && !empty($filters['estado'])) {
                $query .= " AND a.estado = :estado";
                $params[':estado'] = $filters['estado'];
            }
            
            if (isset($filters['categoria_id']) && !empty($filters['categoria_id'])) {
                $query .= " AND c.id = :categoria_id";
                $params[':categoria_id'] = $filters['categoria_id'];
            }
            
            if (isset($filters['producto_id']) && !empty($filters['producto_id'])) {
                $query .= " AND p.id = :producto_id";
                $params[':producto_id'] = $filters['producto_id'];
            }
            
            if (isset($filters['producto_nombre']) && !empty($filters['producto_nombre'])) {
                $query .= " AND p.nombre ILIKE :producto_nombre";
                $params[':producto_nombre'] = '%' . $filters['producto_nombre'] . '%';
            }
            
            if (isset($filters['fecha_desde']) && !empty($filters['fecha_desde'])) {
                $query .= " AND a.fecha_inicio >= :fecha_desde";
                $params[':fecha_desde'] = $filters['fecha_desde'];
            }
            
            if (isset($filters['fecha_hasta']) && !empty($filters['fecha_hasta'])) {
                $query .= " AND a.fecha_fin <= :fecha_hasta";
                $params[':fecha_hasta'] = $filters['fecha_hasta'];
            }
            
            if (isset($filters['usuario_id']) && !empty($filters['usuario_id'])) {
                $query .= " AND a.usuario_id = :usuario_id";
                $params[':usuario_id'] = $filters['usuario_id'];
            }
            
            $query .= " ORDER BY a.fecha_creacion DESC";
            
            if (isset($filters['limit'])) {
                $query .= " LIMIT :limit";
                $params[':limit'] = (int)$filters['limit'];
            }
            
            if (isset($filters['offset'])) {
                $query .= " OFFSET :offset";
                $params[':offset'] = (int)$filters['offset'];
            }
            
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
}
?>
