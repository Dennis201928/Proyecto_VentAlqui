<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Helpers\RentalNotificationService;

/**
 * Sistema de alquileres
 */
class Rental extends Model {

    /**
     * Crear un nuevo alquiler
     */
    public function createRental($user_id, $product_id, $fecha_inicio, $fecha_fin, $observaciones = null) {
        try {
            $this->conn->beginTransaction();
            
            // Verificar disponibilidad
            $product = new \App\Models\Product();
            if (!$product->checkAvailability($product_id, $fecha_inicio, $fecha_fin)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'El producto no está disponible en las fechas seleccionadas'];
            }

            // Obtener información del producto
            $product_info = $product->getProductById($product_id);
            if (!$product_info) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Producto no encontrado'];
            }

            // Calcular total
            $dias = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / (60 * 60 * 24);
            if ($dias <= 0) {
                $this->conn->rollBack();
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

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Error al crear alquiler'];
            }
            
            $rental_id = $this->conn->lastInsertId();
            
            // Reducir stock del producto
            $query = "UPDATE productos SET stock_disponible = stock_disponible - 1 
                     WHERE id = :producto_id AND stock_disponible > 0";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':producto_id', $product_id);
            
            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Error al actualizar stock'];
            }
            
            $this->conn->commit();
            
            // Enviar correo de notificación de agendamiento
            try {
                // Obtener información del usuario
                $query_user = "SELECT nombre, apellido, email FROM usuarios WHERE id = :user_id";
                $stmt_user = $this->conn->prepare($query_user);
                $stmt_user->bindParam(':user_id', $user_id);
                $stmt_user->execute();
                $user_info = $stmt_user->fetch();
                
                if ($user_info) {
                    $usuario_nombre = $user_info['nombre'] . ' ' . $user_info['apellido'];
                    $usuario_email = $user_info['email'];
                    $producto_nombre = $product_info['nombre'];
                    
                    // Enviar correo de notificación
                    $notificationService = new RentalNotificationService();
                    $notificationService->sendRentalNotification(
                        $usuario_nombre,
                        $usuario_email,
                        $producto_nombre,
                        $fecha_inicio,
                        $fecha_fin,
                        (int)$dias
                    );
                    
                    // Guardar en log como respaldo
                    $notificationService->saveRentalNotification(
                        $usuario_nombre,
                        $usuario_email,
                        $producto_nombre,
                        $fecha_inicio,
                        $fecha_fin,
                        (int)$dias
                    );
                }
            } catch (\Exception $e) {
                // No fallar la creación del alquiler si el correo falla
                error_log("Error al enviar notificación de alquiler: " . $e->getMessage());
            }
            
            return ['success' => true, 'id' => $rental_id, 'message' => 'Alquiler creado exitosamente', 'total' => $total];
        } catch (\PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
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
        } catch (\PDOException $e) {
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
        } catch (\PDOException $e) {
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
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar estado de un alquiler
     */
    public function updateRentalStatus($id, $estado, $observaciones = null) {
        try {
            $this->conn->beginTransaction();
            
            // Obtener información del alquiler antes de actualizar
            $query = "SELECT producto_id, estado as estado_anterior FROM alquileres WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $rental = $stmt->fetch();
            
            if (!$rental) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Alquiler no encontrado'];
            }
            
            $producto_id = $rental['producto_id'];
            $estado_anterior = $rental['estado_anterior'];
            
            // Actualizar estado
            $query = "UPDATE alquileres SET estado = :estado, observaciones = :observaciones, 
                     fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':observaciones', $observaciones);
            $stmt->bindParam(':id', $id);
            
            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Error al actualizar estado'];
            }
            
            // Manejar stock según cambio de estado
            // Si se cancela desde cualquier estado activo, restaurar stock
            if ($estado == 'cancelado' && in_array($estado_anterior, ['pendiente', 'confirmado', 'en_curso'])) {
                $query = "UPDATE productos SET stock_disponible = stock_disponible + 1 
                         WHERE id = :producto_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':producto_id', $producto_id);
                $stmt->execute();
            }
            // Si se finaliza desde cualquier estado activo, restaurar stock
            // El producto vuelve al inventario cuando el alquiler termina
            elseif ($estado == 'finalizado' && in_array($estado_anterior, ['pendiente', 'confirmado', 'en_curso'])) {
                $query = "UPDATE productos SET stock_disponible = stock_disponible + 1 
                         WHERE id = :producto_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':producto_id', $producto_id);
                $stmt->execute();
            }
            // Si se confirma desde pendiente, el stock ya fue reducido al crear
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Estado actualizado exitosamente'];
        } catch (\PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Cancelar alquiler
     */
    public function cancelRental($id, $user_id) {
        try {
            // Verificar que el alquiler pertenece al usuario
            $query = "SELECT estado, producto_id FROM alquileres WHERE id = :id AND usuario_id = :usuario_id";
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
            
            // Usar updateRentalStatus que maneja la restauración del stock
            $result = $this->updateRentalStatus($id, 'cancelado', 'Cancelado por el usuario');
            return $result;
        } catch (\PDOException $e) {
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
        } catch (\PDOException $e) {
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
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Verificar disponibilidad en un rango de fechas considerando stock
     */
    public function checkAvailabilityInRange($product_id, $fecha_inicio, $fecha_fin) {
        try {
            // Usar el método del modelo Product que ya considera stock
            $product = new \App\Models\Product();
            return $product->checkAvailability($product_id, $fecha_inicio, $fecha_fin);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Obtener fechas ocupadas de un producto para el calendario
     * Retorna información sobre alquileres y stock disponible
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
            
            // Obtener stock disponible del producto
            $product = new \App\Models\Product();
            $product_info = $product->getProductById($product_id);
            $stock_disponible = isset($product_info['stock_disponible']) ? (int)$product_info['stock_disponible'] : 0;
            
            // Obtener alquileres activos
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
            
            $alquileres = $stmt->fetchAll();
            
            // Agregar información de stock a cada alquiler
            foreach ($alquileres as &$alquiler) {
                $alquiler['stock_disponible'] = $stock_disponible;
            }
            
            return [
                'alquileres' => $alquileres,
                'stock_disponible' => $stock_disponible
            ];
        } catch (\PDOException $e) {
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
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }
}
?>
