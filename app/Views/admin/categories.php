<?php 
$current_page = 'admin';
$baseUrl = $baseUrl ?? '/Proyecto_VentAlqui/public';
?>
<style>
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .sidebar .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 12px 20px;
        border-radius: 8px;
        margin: 2px 0;
        transition: all 0.3s;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        color: white;
        background: rgba(255,255,255,0.1);
        transform: translateX(5px);
    }
    .main-content {
        background-color: #f8f9fa;
        min-height: 100vh;
        padding: 30px;
    }
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .badge-tipo {
        font-size: 0.85rem;
        padding: 6px 12px;
    }
</style>

<div class="admin-content-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-tags text-primary me-2"></i>Gestión de Categorías
                    </h2>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">
                        <i class="fas fa-plus me-1"></i>Agregar Categoría
                    </button>
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

                <!-- Lista de Categorías -->
                <div class="card">
                    <div class="card-body">
                        <?php if (is_array($categories) && !empty($categories) && !isset($categories['error'])): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Tipo</th>
                                            <th>Descripción</th>
                                            <th>Fecha Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $maquinaria_cats = [];
                                        $material_cats = [];
                                        foreach ($categories as $cat) {
                                            if ($cat['tipo'] == 'maquinaria') {
                                                $maquinaria_cats[] = $cat;
                                            } else {
                                                $material_cats[] = $cat;
                                            }
                                        }
                                        ?>
                                        
                                        <?php if (!empty($maquinaria_cats)): ?>
                                            <tr class="table-info">
                                                <td colspan="6"><strong>Maquinaria</strong></td>
                                            </tr>
                                            <?php foreach ($maquinaria_cats as $cat): ?>
                                                <tr>
                                                    <td><?php echo $cat['id']; ?></td>
                                                    <td><strong><?php echo htmlspecialchars($cat['nombre']); ?></strong></td>
                                                    <td>
                                                        <span class="badge badge-tipo bg-primary">Maquinaria</span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($cat['descripcion'] ?? 'Sin descripción'); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($cat['fecha_creacion'])); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning" 
                                                                onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['nombre']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($material_cats)): ?>
                                            <tr class="table-warning">
                                                <td colspan="6"><strong>Materiales Pétreos</strong></td>
                                            </tr>
                                            <?php foreach ($material_cats as $cat): ?>
                                                <tr>
                                                    <td><?php echo $cat['id']; ?></td>
                                                    <td><strong><?php echo htmlspecialchars($cat['nombre']); ?></strong></td>
                                                    <td>
                                                        <span class="badge badge-tipo bg-success">Material</span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($cat['descripcion'] ?? 'Sin descripción'); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($cat['fecha_creacion'])); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning" 
                                                                onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['nombre']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay categorías registradas. Agrega una nueva categoría para comenzar.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar/Editar Categoría -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Agregar Categoría
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="categoryForm" method="POST" action="<?php echo $baseUrl; ?>/admin/categorias">
                <div class="modal-body">
                    <input type="hidden" id="category_id" name="category_id" value="">
                    <input type="hidden" id="action" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required
                               placeholder="Ej: Excavadoras">
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo *</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="maquinaria">Maquinaria</option>
                            <option value="material">Materiales Pétreos</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                  placeholder="Descripción de la categoría"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('category_id').value = category.id;
    document.getElementById('action').value = 'update';
    document.getElementById('nombre').value = category.nombre;
    document.getElementById('tipo').value = category.tipo;
    document.getElementById('descripcion').value = category.descripcion || '';
    document.getElementById('addCategoryModalLabel').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Categoría';
    
    $('#addCategoryModal').modal('show');
}

function deleteCategory(id, nombre) {
    if (confirm('¿Estás seguro de que deseas eliminar la categoría "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
        // Crear formulario para eliminar
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo $baseUrl; ?>/admin/categorias';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        form.appendChild(actionInput);
        
        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'category_id';
        idInput.value = id;
        form.appendChild(idInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Resetear modal al cerrar
$('#addCategoryModal').on('hidden.bs.modal', function () {
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    document.getElementById('action').value = 'create';
    document.getElementById('addCategoryModalLabel').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Agregar Categoría';
});
</script>
