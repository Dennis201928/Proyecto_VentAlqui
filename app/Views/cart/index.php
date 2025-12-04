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
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td><?php echo $item['cantidad']; ?></td>
                            <td>$<?php echo number_format($item['precio_venta'] ?? $item['precio_alquiler_dia'] ?? 0, 2); ?></td>
                            <td>$<?php echo number_format(($item['precio_venta'] ?? $item['precio_alquiler_dia'] ?? 0) * $item['cantidad'], 2); ?></td>
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

