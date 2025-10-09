<?php
/**
 * Panel de Administración - Lista de Productos
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/auth.php';
require_once 'includes/product.php';

$auth = new Auth();
$product = new Product();

// Verificar que el usuario sea administrador
$auth->requireAdmin();

$current_user = $auth->getCurrentUser();
$message = '';
$error = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    if ($action == 'delete' && $product_id > 0) {
        $result = $product->deleteProduct($product_id);
        if ($result['success']) {
            $message = 'Producto eliminado exitosamente';
        } else {
            $error = $result['message'];
        }
    }
}

// Obtener productos
$filters = [];
if (isset($_GET['tipo'])) {
    $filters['tipo'] = $_GET['tipo'];
}
if (isset($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

$productos = $product->getProducts($filters);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - AlquiVenta</title>
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
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .badge-status {
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
                        <a class="nav-link" href="admin-categories.php">
                            <i class="fas fa-tags me-2"></i>Gestionar Categorías
                        </a>
                        <a class="nav-link active" href="admin-products.php">
                            <i class="fas fa-list me-2"></i>Gestionar Productos
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
                        <h2><i class="fas fa-list me-2"></i>Gestión de Productos</h2>
                        <a href="admin.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Agregar Producto
                        </a>
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

                    <!-- Filtros -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label for="tipo" class="form-label">Tipo de Producto</label>
                                    <select class="form-select" id="tipo" name="tipo">
                                        <option value="">Todos los tipos</option>
                                        <option value="maquinaria" <?php echo (($_GET['tipo'] ?? '') == 'maquinaria') ? 'selected' : ''; ?>>Maquinaria</option>
                                        <option value="material" <?php echo (($_GET['tipo'] ?? '') == 'material') ? 'selected' : ''; ?>>Materiales Pétreos</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="search" class="form-label">Buscar</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Buscar por nombre o descripción"
                                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i>Filtrar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Productos -->
                    <div class="row">
                        <?php if (is_array($productos) && !empty($productos)): ?>
                            <?php foreach ($productos as $prod): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card product-card h-100">
                                        <div class="position-relative">
                                            <?php if ($prod['imagen_principal']): ?>
                                                <img src="<?php echo htmlspecialchars($prod['imagen_principal']); ?>" 
                                                     class="product-image" alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
                                            <?php else: ?>
                                                <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-image fa-3x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <?php
                                                $badge_class = 'bg-secondary';
                                                switch ($prod['estado']) {
                                                    case 'disponible':
                                                        $badge_class = 'bg-success';
                                                        break;
                                                    case 'alquilado':
                                                        $badge_class = 'bg-warning';
                                                        break;
                                                    case 'mantenimiento':
                                                        $badge_class = 'bg-info';
                                                        break;
                                                    case 'vendido':
                                                        $badge_class = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?> badge-status">
                                                    <?php echo ucfirst($prod['estado']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title"><?php echo htmlspecialchars($prod['nombre']); ?></h5>
                                            <p class="card-text text-muted small">
                                                <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($prod['categoria_nombre']); ?>
                                                <br>
                                                <i class="fas fa-cube me-1"></i><?php echo ucfirst($prod['categoria_tipo']); ?>
                                            </p>
                                            <p class="card-text">
                                                <?php if ($prod['precio_venta']): ?>
                                                    <strong>Venta: $<?php echo number_format($prod['precio_venta'], 2); ?></strong>
                                                <?php endif; ?>
                                                <?php if ($prod['precio_alquiler_dia']): ?>
                                                    <br><small class="text-muted">Alquiler: $<?php echo number_format($prod['precio_alquiler_dia'], 2); ?>/día</small>
                                                <?php endif; ?>
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fas fa-boxes me-1"></i>Stock: <?php echo $prod['stock_disponible']; ?>
                                                    <?php if ($prod['stock_minimo'] > 0): ?>
                                                        (Mín: <?php echo $prod['stock_minimo']; ?>)
                                                    <?php endif; ?>
                                                </small>
                                            </p>
                                            <div class="mt-auto">
                                                <div class="btn-group w-100" role="group">
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-action" 
                                                            onclick="viewProduct(<?php echo $prod['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning btn-sm btn-action" 
                                                            onclick="editProduct(<?php echo $prod['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm btn-action" 
                                                            onclick="deleteProduct(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['nombre']); ?>')">
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
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No se encontraron productos</h5>
                                        <p class="text-muted">Intenta ajustar los filtros o agrega un nuevo producto.</p>
                                        <a href="admin.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>Agregar Primer Producto
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el producto <strong id="productName"></strong>?</p>
                    <p class="text-danger small">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" id="deleteProductId">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewProduct(id) {
            // Implementar vista de producto
            alert('Función de vista en desarrollo. ID: ' + id);
        }

        function editProduct(id) {
            window.location.href = 'edit-product.php?id=' + id;
        }

        function deleteProduct(id, name) {
            document.getElementById('productName').textContent = name;
            document.getElementById('deleteProductId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
