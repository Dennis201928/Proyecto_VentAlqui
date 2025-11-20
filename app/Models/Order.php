<?php
namespace App\Models;

use App\Core\Model;
use Exception;

/**
 * Sistema de pedidos y ventas
 */
class Order extends Model {
    protected $table = 'ventas'; // Tabla asociada al modelo

    public function __construct() {
        parent::__construct();
    }

    /**
     * Crear una nueva venta desde el carrito
     */
    public function createOrderFromCart($user_id, $metodo_pago, $direccion_entrega = null, $comprobante_pago = null) {
        try {
            $this->conn->beginTransaction();

            // Obtener items del carrito
            $cart = new \App\Models\Cart();
            $cart_items = $cart->getCartItems($user_id);
            
            if (empty($cart_items) || isset($cart_items['error'])) {
                throw new Exception('Carrito vacío o error al obtener items');
            }

            // Verificar disponibilidad
            $unavailable_items = $cart->checkAvailability($user_id);
            if (!empty($unavailable_items)) {
                throw new Exception('Algunos productos no están disponibles');
            }

            // Calcular totales
            $total_info = $cart->getCartTotal($user_id);
            if (isset($total_info['error'])) {
                throw new Exception('Error al calcular total');
            }

            // Crear venta
            $query = "INSERT INTO ventas (usuario_id, total, impuestos, metodo_pago, direccion_entrega, comprobante_pago) 
                     VALUES (:usuario_id, :total, :impuestos, :metodo_pago, :direccion_entrega, :comprobante_pago)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $user_id);
            $stmt->bindParam(':total', $total_info['total']);
            $stmt->bindParam(':impuestos', $total_info['impuestos']);
            $stmt->bindParam(':metodo_pago', $metodo_pago);
            $stmt->bindParam(':direccion_entrega', $direccion_entrega);
            $stmt->bindParam(':comprobante_pago', $comprobante_pago);
            $stmt->execute();

            $venta_id = $this->conn->lastInsertId();

            // Crear detalles de venta
            foreach ($cart_items as $item) {
                $precio = ($item['tipo'] == 'alquiler') ? $item['precio_alquiler_dia'] : $item['precio_venta'];
                $subtotal = $precio * $item['cantidad'];
                
                if ($item['tipo'] == 'alquiler' && $item['fecha_inicio'] && $item['fecha_fin']) {
                    $dias = (strtotime($item['fecha_fin']) - strtotime($item['fecha_inicio'])) / (60 * 60 * 24);
                    $subtotal = $precio * $item['cantidad'] * $dias;
                }

                $query = "INSERT INTO venta_detalles (venta_id, producto_id, cantidad, precio_unitario, subtotal) 
                         VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':venta_id', $venta_id);
                $stmt->bindParam(':producto_id', $item['producto_id']);
                $stmt->bindParam(':cantidad', $item['cantidad']);
                $stmt->bindParam(':precio_unitario', $precio);
                $stmt->bindParam(':subtotal', $subtotal);
                $stmt->execute();

                // Actualizar stock para ventas
                if ($item['tipo'] == 'venta') {
                    $query = "UPDATE productos SET stock_disponible = stock_disponible - :cantidad 
                             WHERE id = :producto_id";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':cantidad', $item['cantidad']);
                    $stmt->bindParam(':producto_id', $item['producto_id']);
                    $stmt->execute();
                }
            }

            // Limpiar carrito
            $cart->clearCart($user_id);

            $this->conn->commit();
            return ['success' => true, 'venta_id' => $venta_id, 'message' => 'Pedido creado exitosamente'];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtener pedidos de un usuario
     */
    public function getUserOrders($user_id, $estado = null) {
        try {
            $query = "SELECT v.*, 
                     COUNT(vd.id) as total_items,
                     STRING_AGG(p.nombre, ', ') as productos
                     FROM ventas v 
                     LEFT JOIN venta_detalles vd ON v.id = vd.venta_id
                     LEFT JOIN productos p ON vd.producto_id = p.id
                     WHERE v.usuario_id = :usuario_id";
            
            $params = [':usuario_id' => $user_id];
            
            if ($estado) {
                $query .= " AND v.estado = :estado";
                $params[':estado'] = $estado;
            }
            
            $query .= " GROUP BY v.id ORDER BY v.fecha_creacion DESC";
            
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
     * Obtener todos los pedidos (admin)
     */
    public function getAllOrders($filters = []) {
        try {
            $query = "SELECT v.*, 
                     u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.email as usuario_email,
                     COUNT(vd.id) as total_items
                     FROM ventas v 
                     JOIN usuarios u ON v.usuario_id = u.id
                     LEFT JOIN venta_detalles vd ON v.id = vd.venta_id
                     WHERE 1=1";
            
            $params = [];
            
            if (isset($filters['estado'])) {
                $query .= " AND v.estado = :estado";
                $params[':estado'] = $filters['estado'];
            }
            
            if (isset($filters['fecha_desde'])) {
                $query .= " AND v.fecha_creacion >= :fecha_desde";
                $params[':fecha_desde'] = $filters['fecha_desde'];
            }
            
            if (isset($filters['fecha_hasta'])) {
                $query .= " AND v.fecha_creacion <= :fecha_hasta";
                $params[':fecha_hasta'] = $filters['fecha_hasta'];
            }
            
            if (isset($filters['usuario_id'])) {
                $query .= " AND v.usuario_id = :usuario_id";
                $params[':usuario_id'] = $filters['usuario_id'];
            }
            
            $query .= " GROUP BY v.id, u.nombre, u.apellido, u.email ORDER BY v.fecha_creacion DESC";
            
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
     * Obtener detalles de un pedido
     */
    public function getOrderDetails($order_id) {
        try {
            // Obtener información de la venta
            $query = "SELECT v.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido, 
                     u.email as usuario_email, u.telefono as usuario_telefono
                     FROM ventas v 
                     JOIN usuarios u ON v.usuario_id = u.id
                     WHERE v.id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();
            $order = $stmt->fetch();

            if (!$order) {
                return null;
            }

            // Obtener detalles de productos
            $query = "SELECT vd.*, p.nombre as producto_nombre, p.imagen_principal, c.nombre as categoria_nombre
                     FROM venta_detalles vd 
                     JOIN productos p ON vd.producto_id = p.id
                     LEFT JOIN categorias c ON p.categoria_id = c.id
                     WHERE vd.venta_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();
            $details = $stmt->fetchAll();

            return [
                'order' => $order,
                'details' => $details
            ];
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar estado de un pedido
     */
    public function updateOrderStatus($order_id, $estado) {
        try {
            $query = "UPDATE ventas SET estado = :estado, fecha_actualizacion = CURRENT_TIMESTAMP 
                     WHERE id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':order_id', $order_id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Estado actualizado exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar estado'];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener estadísticas de ventas
     */
    public function getSalesStats($filters = []) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_ventas,
                        COUNT(CASE WHEN estado = 'confirmada' THEN 1 END) as ventas_confirmadas,
                        COUNT(CASE WHEN estado = 'enviada' THEN 1 END) as ventas_enviadas,
                        COUNT(CASE WHEN estado = 'entregada' THEN 1 END) as ventas_entregadas,
                        COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as ventas_canceladas,
                        SUM(CASE WHEN estado IN ('confirmada', 'enviada', 'entregada') THEN total ELSE 0 END) as ingresos_totales,
                        AVG(CASE WHEN estado IN ('confirmada', 'enviada', 'entregada') THEN total ELSE NULL END) as promedio_venta
                     FROM ventas";
            
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
     * Obtener productos más vendidos
     */
    public function getBestSellingProducts($limit = 10) {
        try {
            $query = "SELECT p.id, p.nombre, p.imagen_principal, c.nombre as categoria_nombre,
                     SUM(vd.cantidad) as total_vendido,
                     SUM(vd.subtotal) as ingresos_totales
                     FROM productos p
                     JOIN venta_detalles vd ON p.id = vd.producto_id
                     JOIN ventas v ON vd.venta_id = v.id
                     LEFT JOIN categorias c ON p.categoria_id = c.id
                     WHERE p.activo = true AND v.estado IN ('confirmada', 'enviada', 'entregada')
                     GROUP BY p.id, p.nombre, p.imagen_principal, c.nombre
                     ORDER BY total_vendido DESC
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
     * Obtener ventas con filtros avanzados para admin (categoría, producto, nombre)
     */
    public function getAllOrdersWithFilters($filters = []) {
        try {
            $query = "SELECT v.*, 
                     u.nombre as usuario_nombre, u.apellido as usuario_apellido, u.email as usuario_email,
                     STRING_AGG(DISTINCT p.nombre, ', ') as productos_nombres,
                     STRING_AGG(DISTINCT c.nombre, ', ') as categorias_nombres,
                     COUNT(DISTINCT vd.id) as total_items
                     FROM ventas v 
                     JOIN usuarios u ON v.usuario_id = u.id
                     LEFT JOIN venta_detalles vd ON v.id = vd.venta_id
                     LEFT JOIN productos p ON vd.producto_id = p.id
                     LEFT JOIN categorias c ON p.categoria_id = c.id
                     WHERE 1=1";
            
            $params = [];
            
            if (isset($filters['estado']) && !empty($filters['estado'])) {
                $query .= " AND v.estado = :estado";
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
                $query .= " AND DATE(v.fecha_creacion) >= :fecha_desde";
                $params[':fecha_desde'] = $filters['fecha_desde'];
            }
            
            if (isset($filters['fecha_hasta']) && !empty($filters['fecha_hasta'])) {
                $query .= " AND DATE(v.fecha_creacion) <= :fecha_hasta";
                $params[':fecha_hasta'] = $filters['fecha_hasta'];
            }
            
            if (isset($filters['usuario_id']) && !empty($filters['usuario_id'])) {
                $query .= " AND v.usuario_id = :usuario_id";
                $params[':usuario_id'] = $filters['usuario_id'];
            }
            
            $query .= " GROUP BY v.id, u.nombre, u.apellido, u.email ORDER BY v.fecha_creacion DESC";
            
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
