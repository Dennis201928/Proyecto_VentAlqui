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
    .btn-group .btn {
        margin-right: 5px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    .table td {
        vertical-align: middle;
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
                                            <th>Precio/Tipo</th>
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
                                                <td>
                                                    <?php 
                                                    $categoria_tipo = $product['categoria_tipo'] ?? '';
                                                    if ($categoria_tipo === 'maquinaria'): 
                                                        if (!empty($product['precio_alquiler_dia']) && $product['precio_alquiler_dia'] > 0):
                                                    ?>
                                                        <span class="badge badge-info">$<?php echo number_format($product['precio_alquiler_dia'], 2); ?>/día</span>
                                                    <?php 
                                                        else: 
                                                    ?>
                                                        <span class="badge badge-secondary">Sin precio</span>
                                                    <?php 
                                                        endif;
                                                    elseif ($categoria_tipo === 'material'):
                                                        $tiene_precio_venta = !empty($product['precio_venta']) && $product['precio_venta'] > 0;
                                                        $tiene_precio_kg = !empty($product['precio_por_kg']) && $product['precio_por_kg'] > 0;
                                                        if ($tiene_precio_kg && !$tiene_precio_venta):
                                                    ?>
                                                        <span class="badge badge-warning">$<?php echo number_format($product['precio_por_kg'], 2); ?>/KG</span>
                                                    <?php elseif ($tiene_precio_venta): ?>
                                                        <span class="badge badge-success">$<?php echo number_format($product['precio_venta'], 2); ?>/unidad</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Sin precio</span>
                                                    <?php 
                                                        endif;
                                                    else: 
                                                    ?>
                                                        <span class="badge badge-secondary">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $stock_disponible = $product['stock_disponible'] ?? 0;
                                                    $tiene_precio_kg = !empty($product['precio_por_kg']) && $product['precio_por_kg'] > 0;
                                                    $categoria_tipo = $product['categoria_tipo'] ?? '';
                                                    
                                                    if ($categoria_tipo === 'material' && $tiene_precio_kg && $stock_disponible == 0):
                                                    ?>
                                                        <span class="badge badge-warning">Venta por KG</span>
                                                    <?php else: ?>
                                                        <span class="badge <?php echo $stock_disponible > 0 ? 'badge-success' : 'badge-danger'; ?>">
                                                            <?php echo $stock_disponible; ?> unidades
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo ($product['estado'] ?? 'disponible') === 'disponible' ? 'badge-success' : 'badge-secondary'; ?>">
                                                        <?php echo ucfirst($product['estado'] ?? 'disponible'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                    <a href="<?php echo $baseUrl; ?>/admin/productos/edit/<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </a>
                                                        <a href="<?php echo $baseUrl; ?>/admin/productos/delete/<?php echo $product['id']; ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirmDelete('<?php echo htmlspecialchars($product['nombre'], ENT_QUOTES); ?>', <?php echo $product['id']; ?>)">
                                                            <i class="fas fa-trash"></i> Eliminar
                                                        </a>
                                                    </div>
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

<script>
function confirmDelete(nombre, id) {
    if (confirm('¿Estás seguro de que deseas eliminar el producto "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
        return true;
    }
    return false;
}
</script>
