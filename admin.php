<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/auth.php';
require_once 'includes/product.php';
require_once 'includes/image-upload.php';

$auth = new Auth();
$product = new Product();
$imageUpload = new ImageUpload();

$auth->requireAdmin();

$current_user = $auth->getCurrentUser();
$message = '';
$error = '';

$categorias_maquinaria = $product->getCategories('maquinaria');
$categorias_material = $product->getCategories('material');

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'create_product') {
        try {
            // Validar datos
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $categoria_id = (int)($_POST['categoria_id'] ?? 0);
            $stock_disponible = (int)($_POST['stock_disponible'] ?? 0);
            $estado = $_POST['estado'] ?? 'disponible';
            
            // Validaciones básicas
            if (empty($nombre) || $categoria_id <= 0) {
                throw new Exception('Los campos nombre y categoría son obligatorios');
            }
            
            // Procesar imagen principal
            $imagen_principal = '';
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] == 0) {
                $imagen_principal = $imageUpload->uploadImage($_FILES['imagen_principal'], 'principal');
            }
            
            
            // Crear producto
            $product_data = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'categoria_id' => $categoria_id,
                'precio_venta' => 0,
                'precio_alquiler_dia' => 0,
                'stock_disponible' => $stock_disponible,
                'stock_minimo' => 0,
                'imagen_principal' => $imagen_principal,
                'imagenes_adicionales' => [],
                'especificaciones' => '{}',
                'estado' => $estado
            ];
            
            $result = $product->createProduct($product_data);
            
            if ($result['success']) {
                header('Location: index.php?success=product_created');
                exit();
            } else {
                $error = $result['message'];
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - AlquiVenta</title>
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
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin: 5px;
        }
        .specification-row {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
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
                        <a class="nav-link" href="admin-products.php">
                            <i class="fas fa-list me-2"></i>Gestionar Productos
                        </a>
                        <a class="nav-link" href="admin-categories.php">
                            <i class="fas fa-tags me-2"></i>Gestionar Categorías
                        </a>
                        <a class="nav-link active" href="admin.php">
                            <i class="fas fa-plus-circle me-2"></i>Agregar Producto
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
                        <h2><i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Producto</h2>
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

                    <!-- Formulario -->
                    <div class="card">
                        <div class="card-body p-4">
                            <form method="POST" enctype="multipart/form-data" id="productForm">
                                <input type="hidden" name="action" value="create_product">
                                
                                <!-- Información Básica -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-info-circle me-2"></i>Información Básica
                                        </h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nombre" class="form-label">Nombre del Producto *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required
                                               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="categoria_id" class="form-label">Categoría *</label>
                                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                                            <option value="">Seleccionar categoría</option>
                                            <optgroup label="Maquinaria">
                                                <?php foreach ($categorias_maquinaria as $cat): ?>
                                                    <option value="<?php echo $cat['id']; ?>" 
                                                            <?php echo (($_POST['categoria_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <optgroup label="Materiales Pétreos">
                                                <?php foreach ($categorias_material as $cat): ?>
                                                    <option value="<?php echo $cat['id']; ?>" 
                                                            <?php echo (($_POST['categoria_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                                  placeholder="Describe las características y usos del producto"><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <!-- Stock y Estado -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-boxes me-2"></i>Stock y Estado
                                        </h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stock_disponible" class="form-label">Stock Disponible</label>
                                        <input type="number" class="form-control" id="stock_disponible" name="stock_disponible" 
                                               min="0" value="<?php echo htmlspecialchars($_POST['stock_disponible'] ?? '0'); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="disponible" <?php echo (($_POST['estado'] ?? '') == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                                            <option value="mantenimiento" <?php echo (($_POST['estado'] ?? '') == 'mantenimiento') ? 'selected' : ''; ?>>En Mantenimiento</option>
                                            <option value="alquilado" <?php echo (($_POST['estado'] ?? '') == 'alquilado') ? 'selected' : ''; ?>>Alquilado</option>
                                            <option value="vendido" <?php echo (($_POST['estado'] ?? '') == 'vendido') ? 'selected' : ''; ?>>Vendido</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Imagen Principal -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-image me-2"></i>Imagen Principal
                                        </h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="imagen_principal" class="form-label">Imagen del Producto</label>
                                        <input type="file" class="form-control" id="imagen_principal" name="imagen_principal" 
                                               accept="image/*" onchange="previewImage(this, 'preview_principal')">
                                        <div id="preview_principal" class="mt-2"></div>
                                        <small class="text-muted">Formatos permitidos: JPG, PNG, GIF, WebP. Máximo 5MB</small>
                                    </div>
                                </div>


                                <!-- Botones -->
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                            <i class="fas fa-undo me-1"></i>Limpiar
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Guardar Producto
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview de imagen principal
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }


        // Limpiar formulario
        function resetForm() {
            document.getElementById('productForm').reset();
            document.getElementById('preview_principal').innerHTML = '';
        }

        // Validación del formulario
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const categoria = document.getElementById('categoria_id').value;
            
            if (!nombre || !categoria) {
                e.preventDefault();
                alert('Por favor completa todos los campos obligatorios correctamente.');
                return false;
            }
        });
    </script>
</body>
</html>
