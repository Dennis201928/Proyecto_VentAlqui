<?php 
$baseUrl = $baseUrl ?? '/Proyecto_VentAlqui/public';
?>
<style>
    .rental-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        margin-bottom: 20px;
    }
    .rental-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    .rental-status {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .status-pendiente {
        background-color: #ffc107;
        color: #000;
    }
    .status-confirmado {
        background-color: #17a2b8;
        color: #fff;
    }
    .status-en-curso {
        background-color: #28a745;
        color: #fff;
    }
    .status-finalizado {
        background-color: #6c757d;
        color: #fff;
    }
    .status-cancelado {
        background-color: #dc3545;
        color: #fff;
    }
    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
    }
    .filter-buttons {
        margin-bottom: 30px;
    }
    .filter-buttons .btn {
        margin-right: 10px;
        margin-bottom: 10px;
    }
</style>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">
                <i class="fas fa-calendar-alt text-primary me-2"></i>Mis Alquileres
            </h2>
            <p class="text-muted">Gestiona y visualiza todos tus alquileres de maquinaria y materiales</p>
        </div>
    </div>

    <!-- Filtros por Estado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-buttons">
                <a href="<?php echo $baseUrl; ?>/mis-alquileres" 
                   class="btn <?php echo empty($estado) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    <i class="fas fa-list me-1"></i>Todos
                </a>
                <a href="<?php echo $baseUrl; ?>/mis-alquileres?estado=pendiente" 
                   class="btn <?php echo ($estado === 'pendiente') ? 'btn-warning' : 'btn-outline-warning'; ?>">
                    <i class="fas fa-clock me-1"></i>Pendientes
                </a>
                <a href="<?php echo $baseUrl; ?>/mis-alquileres?estado=confirmado" 
                   class="btn <?php echo ($estado === 'confirmado') ? 'btn-info' : 'btn-outline-info'; ?>">
                    <i class="fas fa-check-circle me-1"></i>Confirmados
                </a>
                <a href="<?php echo $baseUrl; ?>/mis-alquileres?estado=en_curso" 
                   class="btn <?php echo ($estado === 'en_curso') ? 'btn-success' : 'btn-outline-success'; ?>">
                    <i class="fas fa-play-circle me-1"></i>En Curso
                </a>
                <a href="<?php echo $baseUrl; ?>/mis-alquileres?estado=finalizado" 
                   class="btn <?php echo ($estado === 'finalizado') ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                    <i class="fas fa-check-double me-1"></i>Finalizados
                </a>
                <a href="<?php echo $baseUrl; ?>/mis-alquileres?estado=cancelado" 
                   class="btn <?php echo ($estado === 'cancelado') ? 'btn-danger' : 'btn-outline-danger'; ?>">
                    <i class="fas fa-times-circle me-1"></i>Cancelados
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($rentals) || (isset($rentals['error']))): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-inbox fa-3x mb-3 text-primary"></i>
                    <h4>No tienes alquileres registrados</h4>
                    <p class="mb-4">Comienza a alquilar maquinaria y materiales para tus proyectos</p>
                    <a href="<?php echo $baseUrl; ?>/alquiler" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i>Ver Productos Disponibles
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($rentals as $rental): ?>
                <?php
                $estado_class = 'status-' . str_replace('_', '-', $rental['estado'] ?? 'pendiente');
                $estado_text = ucfirst(str_replace('_', ' ', $rental['estado'] ?? 'Pendiente'));
                $fecha_inicio = date('d/m/Y', strtotime($rental['fecha_inicio']));
                $fecha_fin = date('d/m/Y', strtotime($rental['fecha_fin']));
                $dias = (strtotime($rental['fecha_fin']) - strtotime($rental['fecha_inicio'])) / (60 * 60 * 24);
                $imagen_url = \App\Helpers\ImageHelper::getImageUrl($rental['imagen_principal'] ?? '', $baseUrl ?? null);
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card rental-card h-100">
                        <?php if (!empty($rental['imagen_principal'])): ?>
                            <img src="<?php echo htmlspecialchars($imagen_url); ?>" 
                                 class="product-image" 
                                 alt="<?php echo htmlspecialchars($rental['producto_nombre'] ?? 'Producto'); ?>">
                        <?php else: ?>
                            <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($rental['producto_nombre'] ?? 'N/A'); ?>
                            </h5>
                            
                            <?php if (!empty($rental['categoria_nombre'])): ?>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($rental['categoria_nombre']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <span class="rental-status <?php echo $estado_class; ?>">
                                    <?php echo htmlspecialchars($estado_text); ?>
                                </span>
                            </div>
                            
                            <div class="rental-details mb-3">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-check text-primary me-2"></i>
                                    <strong>Inicio:</strong> <?php echo $fecha_inicio; ?>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-calendar-times text-danger me-2"></i>
                                    <strong>Fin:</strong> <?php echo $fecha_fin; ?>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-clock text-info me-2"></i>
                                    <strong>Duración:</strong> <?php echo $dias; ?> día<?php echo $dias != 1 ? 's' : ''; ?>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-dollar-sign text-success me-2"></i>
                                    <strong>Total:</strong> 
                                    <span class="h5 text-success mb-0">
                                        $<?php echo number_format($rental['total'] ?? 0, 2); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!empty($rental['observaciones'])): ?>
                                <div class="alert alert-light small mb-0">
                                    <strong>Observaciones:</strong><br>
                                    <?php echo htmlspecialchars($rental['observaciones']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($rental['fecha_creacion'] ?? 'now')); ?>
                                </small>
                                <?php if (in_array($rental['estado'] ?? '', ['pendiente', 'confirmado'])): ?>
                                    <a href="<?php echo $baseUrl; ?>/alquiler/<?php echo $rental['producto_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Ver Detalles
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
