<?php
use App\Core\Config;
$baseUrl = Config::SITE_URL;
?>

<!-- Success Message Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h2 class="text-success mb-3">¡Pedido Realizado Exitosamente!</h2>
                <p class="lead mb-4">Gracias por su compra. Su pedido ha sido procesado correctamente.</p>
                <p class="text-muted">Número de pedido: <strong>#<?php echo $order_details['order']['id']; ?></strong></p>
            </div>
        </div>
    </div>
</div>
<!-- Success Message End -->

<!-- Order Details Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Detalles del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Fecha del Pedido:</strong><br>
                            <?php echo date('d/m/Y H:i', strtotime($order_details['order']['fecha_creacion'])); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Estado:</strong><br>
                            <span class="badge badge-info"><?php echo ucfirst($order_details['order']['estado']); ?></span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Método de Pago:</strong><br>
                            <?php echo ucfirst($order_details['order']['metodo_pago']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Total:</strong><br>
                            $<?php echo number_format($order_details['order']['total'], 2); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($order_details['order']['direccion_entrega'])): ?>
                        <div class="mb-3">
                            <strong>Dirección de Entrega:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order_details['order']['direccion_entrega'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Productos</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($order_details['details'] as $detail): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?php echo htmlspecialchars($detail['producto_nombre']); ?></strong><br>
                                <small class="text-muted">Cantidad: <?php echo $detail['cantidad']; ?></small>
                            </div>
                            <div class="text-right">
                                $<?php echo number_format($detail['subtotal'], 2); ?>
                            </div>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong>$<?php echo number_format($order_details['order']['total'], 2); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Order Details End -->

<!-- Actions Start -->
<div class="container-fluid py-5">
    <div class="row px-xl-5">
        <div class="col-12 text-center">
            <a href="<?php echo $baseUrl; ?>/" class="btn btn-primary btn-lg mr-3">Ver Productos</a>
            <a href="<?php echo $baseUrl; ?>/" class="btn btn-secondary btn-lg">Continuar Comprando</a>
        </div>
    </div>
</div>
<!-- Actions End -->

