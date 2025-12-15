<div class="container mt-4">
    <h2>Carrito de Compras</h2>
    <?php if (empty($cart_items) || (isset($cart_items['error']))): ?>
        <div class="alert alert-info">
            <p>Tu carrito está vacío.</p>
            <a href="venta" class="btn btn-primary">Ver Productos</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <?php 
                        $tiene_precio_kg = !empty($item['precio_por_kg']) && $item['precio_por_kg'] > 0;
                        $stock_disponible = (int)($item['stock_disponible'] ?? 0);
                        $es_por_kg = ($item['tipo'] == 'venta' && $tiene_precio_kg && $stock_disponible == 0) || 
                                    ($item['tipo'] == 'venta' && $tiene_precio_kg && !empty($item['precio_por_kg']));
                        
                        if ($item['tipo'] == 'alquiler') {
                            $precio = $item['precio_alquiler_dia'] ?? 0;
                            $cantidad_display = $item['cantidad'];
                            $unidad = 'unidad(es)';
                            if ($item['fecha_inicio'] && $item['fecha_fin']) {
                                $dias = (strtotime($item['fecha_fin']) - strtotime($item['fecha_inicio'])) / (60 * 60 * 24);
                                $subtotal = $precio * $item['cantidad'] * $dias;
                            } else {
                                $subtotal = $precio * $item['cantidad'];
                            }
                        } elseif ($es_por_kg) {
                            $precio = $item['precio_por_kg'] ?? 0;
                            $cantidad_display = number_format((float)$item['cantidad'], 3, '.', '');
                            $unidad = 'KG';
                            $subtotal = $precio * (float)$item['cantidad'];
                        } else {
                            $precio = $item['precio_venta'] ?? 0;
                            $cantidad_display = $item['cantidad'];
                            $unidad = 'unidad(es)';
                            $subtotal = $precio * $item['cantidad'];
                        }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td><?php echo $cantidad_display; ?> <?php echo $unidad; ?></td>
                            <td>$<?php echo number_format($precio, 2); ?><?php echo $es_por_kg ? '/KG' : ($item['tipo'] == 'alquiler' ? '/día' : ''); ?></td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <a href="carrito/remove/<?php echo $item['id']; ?>" class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-right mt-3">
            <a href="checkout" class="btn btn-primary">Proceder al Pago</a>
        </div>
    <?php endif; ?>
</div>

