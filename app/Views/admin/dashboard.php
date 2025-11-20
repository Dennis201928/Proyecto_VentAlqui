<?php 
$current_page = 'admin';
$baseUrl = $baseUrl ?? '/Proyecto_VentAlqui/public';
?>
<style>
    .stat-card {
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s;
        border: none;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .stat-icon {
        font-size: 3rem;
        opacity: 0.3;
    }
</style>

<div class="admin-content-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="fas fa-tachometer-alt text-primary me-2"></i>Panel de Administración
                    </h2>
                    <div>
                        <span class="text-muted">Bienvenido, </span>
                        <strong><?php echo htmlspecialchars($current_user['nombre'] . ' ' . $current_user['apellido']); ?></strong>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Total Productos</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_products'] ?? 0; ?></h3>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Productos Activos</h6>
                                        <h3 class="mb-0"><?php echo $stats['active_products'] ?? 0; ?></h3>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Categorías</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_categories'] ?? 0; ?></h3>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-2">Alquileres</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_rentals'] ?? 0; ?></h3>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-bolt text-primary me-2"></i>Acciones Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="<?php echo $baseUrl; ?>/admin/productos" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-box me-2"></i>Gestionar Productos
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="<?php echo $baseUrl; ?>/admin/categorias" class="btn btn-outline-success w-100">
                                            <i class="fas fa-tags me-2"></i>Gestionar Categorías
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="<?php echo $baseUrl; ?>/admin/calendario-alquileres" class="btn btn-outline-info w-100">
                                            <i class="fas fa-calendar-alt me-2"></i>Calendario Alquileres
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="<?php echo $baseUrl; ?>/admin/calendario-ventas" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-calendar-check me-2"></i>Calendario Ventas
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="<?php echo $baseUrl; ?>/" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-home me-2"></i>Ir al Sitio
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Información del Sistema</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-server text-primary me-2"></i>
                                        <strong>Versión PHP:</strong> <?php echo phpversion(); ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-database text-primary me-2"></i>
                                        <strong>Base de Datos:</strong> PostgreSQL
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-calendar text-primary me-2"></i>
                                        <strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-user-shield text-primary me-2"></i>Tu Cuenta</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-user text-primary me-2"></i>
                                        <strong>Nombre:</strong> <?php echo htmlspecialchars($current_user['nombre'] . ' ' . $current_user['apellido']); ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        <strong>Email:</strong> <?php echo htmlspecialchars($current_user['email']); ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-shield-alt text-primary me-2"></i>
                                        <strong>Rol:</strong> <span class="badge bg-warning">Administrador</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</div>
