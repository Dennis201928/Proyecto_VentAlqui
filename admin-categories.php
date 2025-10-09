<?php
/**
 * Panel de Administración - Gestión de Categorías
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/auth.php';
require_once 'includes/category.php';

$auth = new Auth();
$category = new Category();

// Verificar que el usuario sea administrador
$auth->requireAdmin();

$current_user = $auth->getCurrentUser();
$message = '';
$error = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'create_category') {
        try {
            // Validar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo = $_POST['tipo'] ?? '';
            
            // Validaciones básicas
            if (empty($nombre) || empty($tipo)) {
                throw new Exception('Los campos nombre y tipo son obligatorios');
            }
            
            // Crear categoría
            $category_data = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'tipo' => $tipo
            ];
            
            $result = $category->createCategory($category_data);
            
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    if ($action == 'update_category') {
        try {
            $id = (int)($_POST['category_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo = $_POST['tipo'] ?? '';
            
            if ($id <= 0 || empty($nombre) || empty($tipo)) {
                throw new Exception('Todos los campos son obligatorios');
            }
            
            $category_data = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'tipo' => $tipo
            ];
            
            $result = $category->updateCategory($id, $category_data);
            
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    if ($action == 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        
        if ($id > 0) {
            $result = $category->deleteCategory($id);
            
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Obtener categorías
$categorias = $category->getCategories();
$stats = $category->getCategoryStats();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - AlquiVenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .category-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .badge-tipo {
            font-size: 0.8em;
        }
        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h4 class="text-white mb-4">
                            <i class="fas fa-tools me-2"></i>Admin Panel
                        </h4>
                        <div class="text-white-50 mb-3">
                            <small>Bienvenido, <?php echo htmlspecialchars($current_user['nombre']); ?></small>
                        </div>
                    </div>
                    <nav class="nav flex-column px-3">
                        <a class="nav-link" href="admin.php">
                            <i class="fas fa-plus-circle me-2"></i>Agregar Producto
                        </a>
                        <a class="nav-link" href="admin-products.php">
                            <i class="fas fa-list me-2"></i>Gestionar Productos
                        </a>
                        <a class="nav-link active" href="admin-categories.php">
                            <i class="fas fa-tags me-2"></i>Gestionar Categorías
                        </a>
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-2"></i>Volver al Sitio
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-tags me-2"></i>Gestión de Categorías</h2>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus me-1"></i>Agregar Categoría
                        </button>
                    </div>

                    <!-- Mensajes -->
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Estadísticas -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-cogs me-2"></i>Maquinaria
                                    </h5>
                                    <h3 class="text-primary">
                                        <?php echo count(array_filter($categorias, function($cat) { return $cat['tipo'] == 'maquinaria'; })); ?>
                                    </h3>
                                    <p class="text-muted">Categorías</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title text-success">
                                        <i class="fas fa-cube me-2"></i>Materiales
                                    </h5>
                                    <h3 class="text-success">
                                        <?php echo count(array_filter($categorias, function($cat) { return $cat['tipo'] == 'material'; })); ?>
                                    </h3>
                                    <p class="text-muted">Categorías</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Categorías -->
                    <div class="row">
                        <?php if (is_array($categorias) && !empty($categorias)): ?>
                            <?php foreach ($categorias as $cat): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card category-card h-100">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($cat['nombre']); ?></h5>
                                                <span class="badge <?php echo $cat['tipo'] == 'maquinaria' ? 'bg-primary' : 'bg-success'; ?> badge-tipo">
                                                    <?php echo ucfirst($cat['tipo']); ?>
                                                </span>
                                            </div>
                                            
                                            <p class="card-text text-muted small mb-3">
                                                <?php echo htmlspecialchars($cat['descripcion'] ?: 'Sin descripción'); ?>
                                            </p>
                                            
                                            <div class="mt-auto">
                                                <div class="btn-group w-100" role="group">
                                                    <button type="button" class="btn btn-outline-warning btn-sm btn-action" 
                                                            onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['nombre']); ?>', '<?php echo htmlspecialchars($cat['descripcion']); ?>', '<?php echo $cat['tipo']; ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm btn-action" 
                                                            onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['nombre']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="card text-center py-5">
                                    <div class="card-body">
                                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No hay categorías registradas</h5>
                                        <p class="text-muted">Agrega tu primera categoría para comenzar a organizar los productos.</p>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                            <i class="fas fa-plus me-1"></i>Agregar Primera Categoría
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Categoría -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_category">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Categoría *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required
                                   placeholder="Ej: Excavadoras, Granito, etc.">
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
                                      placeholder="Describe brevemente esta categoría"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Categoría -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_category">
                        <input type="hidden" name="category_id" id="editCategoryId">
                        
                        <div class="mb-3">
                            <label for="editNombre" class="form-label">Nombre de la Categoría *</label>
                            <input type="text" class="form-control" id="editNombre" name="nombre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editTipo" class="form-label">Tipo *</label>
                            <select class="form-select" id="editTipo" name="tipo" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="maquinaria">Maquinaria</option>
                                <option value="material">Materiales Pétreos</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editDescripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="editDescripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Actualizar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminación -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar la categoría <strong id="categoryName"></strong>?</p>
                    <p class="text-danger small">Esta acción no se puede deshacer y solo se permitirá si no hay productos asociados.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" id="deleteCategoryId">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(id, nombre, descripcion, tipo) {
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editNombre').value = nombre;
            document.getElementById('editDescripcion').value = descripcion;
            document.getElementById('editTipo').value = tipo;
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }

        function deleteCategory(id, nombre) {
            document.getElementById('categoryName').textContent = nombre;
            document.getElementById('deleteCategoryId').value = id;
            new bootstrap.Modal(document.getElementById('deleteCategoryModal')).show();
        }
    </script>
</body>
</html>
