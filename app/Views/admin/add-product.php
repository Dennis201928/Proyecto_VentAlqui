<?php 
use App\Core\Config;
$baseUrl = $baseUrl ?? Config::SITE_URL;
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
    .form-control, select.form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s;
        line-height: 1.5;
        height: auto;
        min-height: 45px;
    }
    select.form-control {
        padding: 12px 35px 12px 15px;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        overflow: visible;
        text-overflow: clip;
    }
    select.form-control option {
        padding: 10px;
        line-height: 1.5;
        white-space: normal;
        word-wrap: break-word;
    }
    select.form-control option:disabled {
        color: #6c757d;
        font-style: italic;
    }
    input[type="file"].form-control {
        padding: 10px 12px;
        line-height: 1.5;
        height: auto;
        min-height: 45px;
        overflow: visible;
        white-space: normal;
        word-wrap: break-word;
    }
    input[type="file"].form-control::-webkit-file-upload-button {
        padding: 8px 16px;
        margin-right: 12px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        line-height: 1.5;
    }
    input[type="file"].form-control::-webkit-file-upload-button:hover {
        background: #5568d3;
    }
    .form-control:focus, select.form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        outline: none;
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
        max-width: 200px;
        max-height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin: 10px 0;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <?php $viewInstance->partial('admin-sidebar'); ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="admin-main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Producto</h2>
                    <a href="<?php echo $baseUrl; ?>/admin/productos" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver a Lista
                    </a>
                </div>

                <!-- Mensajes -->
                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($message) && $message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data" id="productForm" action="<?php echo $baseUrl; ?>/admin/productos">

                            <!-- Información Básica -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Información Básica
                                    </h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nombre">Nombre del Producto *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required
                                           placeholder="Ej: Excavadora CAT 320">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="categoria_id" >Categoría *</label>
                                    <select class="form-control" id="categoria_id" name="categoria_id" required>
                                        <option value="" disabled selected>Seleccionar categoría</option>
                                        <?php 
                                        $categorias_maquinaria = $categorias_maquinaria ?? [];
                                        $categorias_material = $categorias_material ?? [];
                                        
                                        // Verificar si hay error
                                        if (isset($categorias_maquinaria['error'])) {
                                            $categorias_maquinaria = [];
                                        }
                                        if (isset($categorias_material['error'])) {
                                            $categorias_material = [];
                                        }
                                        
                                        if (!empty($categorias_maquinaria) && is_array($categorias_maquinaria)): ?>
                                            <optgroup label="Maquinaria">
                                                <?php foreach ($categorias_maquinaria as $cat): ?>
                                                    <?php if (is_array($cat) && isset($cat['id'])): ?>
                                                        <option value="<?php echo $cat['id']; ?>">
                                                            <?php echo htmlspecialchars($cat['nombre'] ?? ''); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endif; ?>
                                        <?php if (!empty($categorias_material) && is_array($categorias_material)): ?>
                                            <optgroup label="Materiales Pétreos">
                                                <?php foreach ($categorias_material as $cat): ?>
                                                    <?php if (is_array($cat) && isset($cat['id'])): ?>
                                                        <option value="<?php echo $cat['id']; ?>">
                                                            <?php echo htmlspecialchars($cat['nombre'] ?? ''); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (empty($categorias_maquinaria) && empty($categorias_material)): ?>
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            No hay categorías disponibles. <a href="<?php echo $baseUrl; ?>/admin/categorias">Crear categorías primero</a>.
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="descripcion" >Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                              placeholder="Describe las características y usos del producto"></textarea>
                                </div>
                            </div>

                            <!-- Precios -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-dollar-sign me-2"></i>Precios
                                    </h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="precio_venta" >Precio de Venta</label>
                                    <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                           step="0.01" min="0" placeholder="0.00">
                                    <small class="text-muted">Dejar en 0 si solo es para alquiler</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="precio_alquiler_dia" >Precio de Alquiler por Día</label>
                                    <input type="number" class="form-control" id="precio_alquiler_dia" name="precio_alquiler_dia" 
                                           step="0.01" min="0" placeholder="0.00">
                                    <small class="text-muted">Dejar en 0 si solo es para venta</small>
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
                                    <label for="stock_disponible" >Stock Disponible</label>
                                    <input type="number" class="form-control" id="stock_disponible" name="stock_disponible" 
                                           min="0" value="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stock_minimo" >Stock Mínimo</label>
                                    <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                                           min="0" value="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="estado" >Estado</label>
                                    <select class="form-control" id="estado" name="estado">
                                        <option value="" disabled selected>Seleccionar estado</option>
                                        <option value="disponible">Disponible</option>
                                        <option value="mantenimiento">En Mantenimiento</option>
                                        <option value="alquilado">Alquilado</option>
                                        <option value="vendido">Vendido</option>
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
                                    <label for="imagen_principal" >Imagen del Producto *</label>
                                    <input type="file" class="form-control" id="imagen_principal" name="imagen_principal" 
                                           accept="image/*" onchange="previewImage(this, 'preview_principal')" required>
                                    <small class="text-muted">Formatos permitidos: JPG, PNG, GIF, WebP (máx. 5MB)</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label >Vista Previa</label>
                                    <div id="preview_principal" class="mt-2"></div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end">
                                        <a href="<?php echo $baseUrl; ?>/admin/productos" class="btn btn-secondary mr-2">
                                            <i class="fas fa-times me-1"></i>Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Guardar Producto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
</div>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById(previewId);
            preview.innerHTML = '<img src="' + e.target.result + '" class="image-preview" alt="Vista previa">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
