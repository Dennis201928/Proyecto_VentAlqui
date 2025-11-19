<?php 
$current_page = 'admin';
$baseUrl = $baseUrl ?? '/Proyecto_VentAlqui/public';
?>
<style>
    .admin-main-content {
        background-color: #f8f9fa;
        min-height: 100vh;
        padding: 30px;
    }
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
</style>

<div class="admin-content-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="fas fa-box text-primary me-2"></i>Gestión de Productos
                    </h2>
                    <a href="<?php echo $baseUrl; ?>/admin/productos/create" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Agregar Producto
                    </a>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($products) && !isset($products['error'])): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Categoría</th>
                                            <th>Precio Venta</th>
                                            <th>Precio Alquiler</th>
                                            <th>Stock</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td><?php echo $product['id']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($product['nombre']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($product['categoria_nombre'] ?? 'N/A'); ?></td>
                                                <td>$<?php echo number_format($product['precio_venta'] ?? 0, 2); ?></td>
                                                <td>$<?php echo number_format($product['precio_alquiler_dia'] ?? 0, 2); ?>/día</td>
                                                <td>
                                                    <span class="badge <?php echo ($product['stock_disponible'] ?? 0) > 0 ? 'badge-success' : 'badge-danger'; ?>">
                                                        <?php echo $product['stock_disponible'] ?? 0; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo ($product['estado'] ?? 'disponible') === 'disponible' ? 'badge-success' : 'badge-secondary'; ?>">
                                                        <?php echo ucfirst($product['estado'] ?? 'disponible'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?php echo $baseUrl; ?>/admin/productos/edit/<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay productos disponibles.</p>
                            <a href="<?php echo $baseUrl; ?>/admin/productos/create" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Agregar Primer Producto
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
</div>
