<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Config;

/**
 * Sistema de carrito de compras
 */
class Cart extends Model {

    /**
     * Agregar producto al carrito
     */
    public function addToCart($user_id, $product_id, $cantidad = 1, $tipo = 'venta', $fecha_inicio = null, $fecha_fin = null) {
        try {
            // Verificar si el producto ya está en el carrito
            $query = "SELECT id, cantidad FROM carrito 
                     WHERE usuario_id = :usuario_id AND producto_id = :producto_id 
                     AND tipo = :tipo AND fecha_inicio = :fecha_inicio AND fecha_fin = :fecha_fin";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $user_id);
            $stmt->bindParam(':producto_id', $product_id);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->execute();
            
            $existing_item = $stmt->fetch();
            
            if ($existing_item) {
                // Actualizar cantidad (puede ser decimal para kilogramos)
                $new_quantity = (float)$existing_item['cantidad'] + (float)$cantidad;
                $query = "UPDATE carrito SET cantidad = :cantidad WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':cantidad', $new_quantity, \PDO::PARAM_STR);
                $stmt->bindParam(':id', $existing_item['id']);
                $stmt->execute();
            } else {
                // Agregar nuevo item (cantidad puede ser decimal para kilogramos)
                $query = "INSERT INTO carrito (usuario_id, producto_id, cantidad, tipo, fecha_inicio, fecha_fin) 
                         VALUES (:usuario_id, :producto_id, :cantidad, :tipo, :fecha_inicio, :fecha_fin)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':usuario_id', $user_id);
                $stmt->bindParam(':producto_id', $product_id);
                $stmt->bindValue(':cantidad', (float)$cantidad, \PDO::PARAM_STR);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
                $stmt->execute();
            }
            
            return ['success' => true, 'message' => 'Producto agregado al carrito'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener items del carrito
     */
    public function getCartItems($user_id) {
        try {
            $query = "SELECT c.*, p.nombre, p.precio_venta, p.precio_alquiler_dia, p.precio_por_kg, p.imagen_principal, 
                     p.stock_disponible, p.tipo_venta, cat.nombre as categoria_nombre, cat.tipo as categoria_tipo
                     FROM carrito c 
                     JOIN productos p ON c.producto_id = p.id 
                     LEFT JOIN categorias cat ON p.categoria_id = cat.id
                     WHERE c.usuario_id = :usuario_id 
                     ORDER BY c.fecha_agregado DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return ['error' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar cantidad de un item
     */
    public function updateQuantity($user_id, $item_id, $cantidad) {
        try {
            if ($cantidad <= 0) {
                return $this->removeFromCart($user_id, $item_id);
            }
            
            // Asegurar que cantidad sea tratada como decimal (puede ser para kilogramos)
            $cantidad = (float)$cantidad;
            
            $query = "UPDATE carrito SET cantidad = :cantidad 
                     WHERE id = :item_id AND usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':cantidad', $cantidad, \PDO::PARAM_STR);
            $stmt->bindParam(':item_id', $item_id);
            $stmt->bindParam(':usuario_id', $user_id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Cantidad actualizada'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar cantidad'];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar item del carrito
     */
    public function removeFromCart($user_id, $item_id) {
        try {
            $query = "DELETE FROM carrito WHERE id = :item_id AND usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':item_id', $item_id);
            $stmt->bindParam(':usuario_id', $user_id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Producto eliminado del carrito'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar producto'];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Limpiar carrito
     */
    public function clearCart($user_id) {
        try {
            $query = "DELETE FROM carrito WHERE usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $user_id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Carrito limpiado'];
            } else {
                return ['success' => false, 'message' => 'Error al limpiar carrito'];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener total del carrito
     */
    public function getCartTotal($user_id) {
        try {
            $items = $this->getCartItems($user_id);
            if (isset($items['error'])) {
                return $items;
            }
            
            $total = 0;
            $subtotal = 0;
            $impuestos = 0;
            
            foreach ($items as $item) {
                if ($item['tipo'] == 'alquiler') {
                    $precio = $item['precio_alquiler_dia'];
                    $item_total = $precio * $item['cantidad'];
                    
                    if ($item['fecha_inicio'] && $item['fecha_fin']) {
                        $dias = (strtotime($item['fecha_fin']) - strtotime($item['fecha_inicio'])) / (60 * 60 * 24);
                        $item_total = $precio * $item['cantidad'] * $dias;
                    }
                } elseif ($item['tipo'] == 'venta') {
                    // Verificar si es venta por kilogramos usando tipo_venta
                    $tipo_venta = $item['tipo_venta'] ?? 'stock';
                    if ($tipo_venta === 'kilogramos') {
                        // Para venta por kilogramos, usar precio_por_kg
                        $precio = $item['precio_por_kg'];
                        // La cantidad en el carrito para kg se almacena como decimal
                        $cantidad_kg = (float)$item['cantidad'];
                        $item_total = $precio * $cantidad_kg;
                    } else {
                        // Para venta por stock, usar precio_venta
                        $precio = $item['precio_venta'];
                        $item_total = $precio * $item['cantidad'];
                    }
                } else {
                    $item_total = 0;
                }
                
                $subtotal += $item_total;
            }
            
            $impuestos = $subtotal * Config::TAX_RATE;
            $total = $subtotal + $impuestos;
            
            return [
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total,
                'items_count' => count($items)
            ];
        } catch (Exception $e) {
            return ['error' => 'Error al calcular total: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener cantidad de items en el carrito
     */
    public function getCartCount($user_id) {
        try {
            $query = "SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $user_id);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result['total'] ?: 0;
        } catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Verificar disponibilidad de productos en el carrito
     */
    public function checkAvailability($user_id) {
        try {
            $items = $this->getCartItems($user_id);
            if (isset($items['error'])) {
                return $items;
            }
            
            $unavailable_items = [];
            
            foreach ($items as $item) {
                if ($item['tipo'] == 'alquiler' && $item['fecha_inicio'] && $item['fecha_fin']) {
                    $product = new Product();
                    $available = $product->checkAvailability($item['producto_id'], $item['fecha_inicio'], $item['fecha_fin']);
                    if (!$available) {
                        $unavailable_items[] = $item;
                    }
                } elseif ($item['tipo'] == 'venta') {
                    // Obtener información completa del producto desde la base de datos
                    $product = new Product();
                    $product_info = $product->getProductById($item['producto_id']);
                    
                    if (!$product_info) {
                        $unavailable_items[] = $item;
                        continue;
                    }
                    
                    // Verificar si el producto se vende por kilogramos
                    $tipo_venta = $product_info['tipo_venta'] ?? null;
                    $tiene_precio_kg = !empty($product_info['precio_por_kg']) && (float)$product_info['precio_por_kg'] > 0;
                    $stock_disponible = (int)($product_info['stock_disponible'] ?? 0);
                    
                    // Determinar si es venta por kilogramos:
                    // 1. Si tipo_venta es 'kilogramos' (prioridad)
                    // 2. O si tiene precio_por_kg y no tiene stock (fallback para productos antiguos sin tipo_venta)
                    $es_por_kg = ($tipo_venta === 'kilogramos') || 
                                ($tiene_precio_kg && $stock_disponible == 0);
                    
                    if ($es_por_kg) {
                        // Los productos vendidos por kilogramos no tienen restricción de stock
                        continue;
                    }
                    
                    // Para productos vendidos por stock, verificar disponibilidad
                    if (isset($product_info['stock_disponible'])) {
                        $stock_disponible = (int)$product_info['stock_disponible'];
                        if ($stock_disponible < $item['cantidad']) {
                            $unavailable_items[] = $item;
                        }
                    } else {
                        $unavailable_items[] = $item;
                    }
                }
            }
            
            return $unavailable_items;
        } catch (\Exception $e) {
            return ['error' => 'Error al verificar disponibilidad: ' . $e->getMessage()];
        }
    }
}
?>
