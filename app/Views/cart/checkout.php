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
                
                <form method="POST" action="<?php echo $baseUrl; ?>/checkout" enctype="multipart/form-data">
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
                            <label>Método de Pago</label>
                            <select class="form-control" name="metodo_pago" id="metodo_pago" required>
                                <option value="">Seleccionar método de pago</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                            </select>
                        </div>
                        
                        <!-- Campo de dirección (solo para transferencia) -->
                        <div class="col-md-12 form-group" id="direccion_field">
                            <label>Dirección de Entrega <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="direccion_entrega" id="direccion_entrega" rows="3"><?php echo htmlspecialchars($current_user['direccion'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Mensaje para efectivo -->
                        <div class="col-md-12" id="mensaje_efectivo" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> <strong>Pago en Efectivo:</strong> Deberás retirar tu pedido en el local presentando la nota de venta que recibirás por email.
                            </div>
                        </div>
                        
                        <!-- Campo para comprobante de transferencia -->
                        <div class="col-md-12 form-group" id="comprobante_field" style="display: none;">
                            <label>Comprobante de Pago <span class="text-danger">*</span></label>
                            <input type="file" class="form-control-file" name="comprobante_pago" id="comprobante_pago" accept="image/*,.pdf">
                            <small class="form-text text-muted">Sube una imagen o PDF del comprobante de transferencia bancaria. Formatos permitidos: JPG, PNG, PDF (máx. 5MB)</small>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Confirmar Pedido</button>
                        <a href="<?php echo $baseUrl; ?>/carrito" class="btn btn-secondary btn-lg ml-2">Volver al Carrito</a>
                    </div>
                </form>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const metodoPago = document.getElementById('metodo_pago');
                    const mensajeEfectivo = document.getElementById('mensaje_efectivo');
                    const comprobanteField = document.getElementById('comprobante_field');
                    const comprobanteInput = document.getElementById('comprobante_pago');
                    const direccionField = document.getElementById('direccion_field');
                    const direccionInput = document.getElementById('direccion_entrega');
                    
                    function updateFields() {
                        if (metodoPago.value === 'efectivo') {
                            // Ocultar dirección y comprobante, mostrar mensaje efectivo
                            direccionField.style.display = 'none';
                            direccionInput.removeAttribute('required');
                            mensajeEfectivo.style.display = 'block';
                            comprobanteField.style.display = 'none';
                            comprobanteInput.removeAttribute('required');
                        } else if (metodoPago.value === 'transferencia') {
                            // Mostrar dirección y comprobante, ocultar mensaje efectivo
                            direccionField.style.display = 'block';
                            direccionInput.setAttribute('required', 'required');
                            mensajeEfectivo.style.display = 'none';
                            comprobanteField.style.display = 'block';
                            comprobanteInput.setAttribute('required', 'required');
                        } else {
                            // Ocultar todo
                            direccionField.style.display = 'none';
                            direccionInput.removeAttribute('required');
                            mensajeEfectivo.style.display = 'none';
                            comprobanteField.style.display = 'none';
                            comprobanteInput.removeAttribute('required');
                        }
                    }
                    
                    // Ocultar dirección inicialmente
                    direccionField.style.display = 'none';
                    direccionInput.removeAttribute('required');
                    
                    metodoPago.addEventListener('change', updateFields);
                });
                </script>
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
                            <?php 
                            if ($item['tipo'] == 'alquiler') {
                                $precio = $item['precio_alquiler_dia'] ?? 0;
                                $cantidad_display = $item['cantidad'];
                                $unidad = '';
                                if ($item['fecha_inicio'] && $item['fecha_fin']) {
                                    $dias = (strtotime($item['fecha_fin']) - strtotime($item['fecha_inicio'])) / (60 * 60 * 24);
                                    $subtotal = $precio * $item['cantidad'] * $dias;
                                    $cantidad_display = $item['cantidad'] . ' x ' . $dias . ' días';
                                } else {
                                    $subtotal = $precio * $item['cantidad'];
                                }
                            } elseif ($item['tipo'] == 'venta') {
                                $tipo_venta = $item['tipo_venta'] ?? 'stock';
                                if ($tipo_venta === 'kilogramos') {
                                    $precio = $item['precio_por_kg'] ?? 0;
                                    $cantidad_display = number_format((float)$item['cantidad'], 3, '.', '') . ' KG';
                                    $unidad = '';
                                    $subtotal = $precio * (float)$item['cantidad'];
                                } else {
                                    $precio = $item['precio_venta'] ?? 0;
                                    $cantidad_display = $item['cantidad'];
                                    $unidad = '';
                                    $subtotal = $precio * $item['cantidad'];
                                }
                            } else {
                                $precio = 0;
                                $cantidad_display = $item['cantidad'];
                                $unidad = '';
                                $subtotal = 0;
                            }
                            ?>
                            <div class="d-flex justify-content-between">
                                <p><?php echo htmlspecialchars($item['nombre']); ?> x<?php echo $cantidad_display; ?><?php echo $unidad; ?></p>
                                <p>$<?php echo number_format($subtotal, 2); ?></p>
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

