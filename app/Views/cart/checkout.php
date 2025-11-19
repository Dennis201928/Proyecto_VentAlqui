<?php
use App\Core\Config;
$baseUrl = Config::SITE_URL;
?>

<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl; ?>/">Inicio</a>
                <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl; ?>/carrito">Carrito</a>
                <span class="breadcrumb-item active">Checkout</span>
            </nav>
        </div>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- Checkout Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-lg-8">
            <div class="mb-4">
                <h4 class="font-weight-semi-bold mb-4">Información de Facturación</h4>
                
                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo $baseUrl; ?>/checkout">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nombre</label>
                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($current_user['nombre']); ?>" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Apellido</label>
                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($current_user['apellido']); ?>" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Email</label>
                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($current_user['email']); ?>" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Teléfono</label>
                            <input class="form-control" type="text" value="<?php echo htmlspecialchars($current_user['telefono'] ?? ''); ?>" readonly>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Dirección de Entrega</label>
                            <textarea class="form-control" name="direccion_entrega" rows="3" required><?php echo htmlspecialchars($current_user['direccion'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Método de Pago</label>
                            <select class="form-control" name="metodo_pago" required>
                                <option value="">Seleccionar método de pago</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                                <option value="tarjeta">Tarjeta de Crédito</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Confirmar Pedido</button>
                        <a href="<?php echo $baseUrl; ?>/carrito" class="btn btn-secondary btn-lg ml-2">Volver al Carrito</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-secondary mb-5">
                <div class="card-header bg-secondary border-0">
                    <h4 class="font-weight-semi-bold m-0">Resumen del Pedido</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="font-weight-medium">Productos</h6>
                        <h6 class="font-weight-medium"><?php echo $cart_total['items_count'] ?? 0; ?></h6>
                    </div>
                    
                    <?php if (!empty($cart_items) && !isset($cart_items['error'])): ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between">
                                <p><?php echo htmlspecialchars($item['nombre']); ?> x<?php echo $item['cantidad']; ?></p>
                                <p>
                                    <?php 
                                    $precio = ($item['tipo'] == 'alquiler') ? $item['precio_alquiler_dia'] : $item['precio_venta'];
                                    $subtotal = $precio * $item['cantidad'];
                                    
                                    if ($item['tipo'] == 'alquiler' && isset($item['fecha_inicio']) && isset($item['fecha_fin'])) {
                                        $dias = (strtotime($item['fecha_fin']) - strtotime($item['fecha_inicio'])) / (60 * 60 * 24);
                                        $subtotal = $precio * $item['cantidad'] * $dias;
                                    }
                                    
                                    echo '$' . number_format($subtotal, 2);
                                    ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <hr class="mt-0">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="font-weight-medium">Subtotal</h6>
                        <h6 class="font-weight-medium">$<?php echo number_format($cart_total['subtotal'] ?? 0, 2); ?></h6>
                    </div>
                    <div class="d-flex justify-content-between">
                        <h6 class="font-weight-medium">Impuestos</h6>
                        <h6 class="font-weight-medium">$<?php echo number_format($cart_total['impuestos'] ?? 0, 2); ?></h6>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h6 class="font-weight-medium">Total</h6>
                        <h6 class="font-weight-medium">$<?php echo number_format($cart_total['total'] ?? 0, 2); ?></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Checkout End -->

