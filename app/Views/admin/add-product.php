<?php 
use App\Core\Config;
$baseUrl = $baseUrl ?? Config::SITE_URL;
?>
<style>
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

<div class="admin-content-wrapper">
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($message) && $message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data" id="productForm" action="<?php echo $baseUrl; ?>/admin/productos" onsubmit="return validarFormulario()">

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
                        <select class="form-control" id="categoria_id" name="categoria_id" required onchange="actualizarTipoVenta()">
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
                                <optgroup label="Maquinaria" id="optgroup-maquinaria">
                                    <?php foreach ($categorias_maquinaria as $cat): ?>
                                        <?php if (is_array($cat) && isset($cat['id'])): ?>
                                            <option value="<?php echo $cat['id']; ?>" data-tipo="maquinaria">
                                                <?php echo htmlspecialchars($cat['nombre'] ?? ''); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                            <?php if (!empty($categorias_material) && is_array($categorias_material)): ?>
                                <optgroup label="Materiales Pétreos" id="optgroup-material">
                                    <?php foreach ($categorias_material as $cat): ?>
                                        <?php if (is_array($cat) && isset($cat['id'])): ?>
                                            <option value="<?php echo $cat['id']; ?>" data-tipo="material">
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
                    <!-- Dropdown para tipo de venta (solo para Materiales Pétreos) -->
                    <div class="col-md-6 mb-3" id="tipo-venta-container" style="display: none;">
                        <label for="tipo_venta">Tipo de Venta *</label>
                        <select class="form-control" id="tipo_venta" name="tipo_venta" onchange="mostrarCamposVenta()">
                            <option value="">Seleccionar tipo</option>
                            <option value="stock">Por Stock (Unidades)</option>
                            <option value="kilogramos">Por Kilogramos</option>
                        </select>
                        <small class="text-muted">Selecciona cómo se venderá este producto</small>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="descripcion" >Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                  placeholder="Describe las características y usos del producto"></textarea>
                    </div>
                </div>

                <!-- Precios -->
                <div class="row mb-4" id="precios-container" style="display: none;">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-dollar-sign me-2"></i>Precios
                        </h5>
                    </div>
                    <!-- Campos para venta por stock -->
                    <div id="precios-stock-container" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="precio_venta">Precio de Venta por Unidad *</label>
                            <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                   step="0.01" min="0" placeholder="0.00">
                            <small class="text-muted">Precio por unidad cuando se vende por stock</small>
                        </div>
                    </div>
                    <!-- Campos para venta por kilogramos -->
                    <div id="precios-kg-container" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="precio_por_kg">Precio por Kilogramo *</label>
                            <input type="number" class="form-control" id="precio_por_kg" name="precio_por_kg" 
                                   step="0.01" min="0" placeholder="0.00">
                            <small class="text-muted">Precio por kilogramo cuando se vende por peso</small>
                        </div>
                    </div>
                    <!-- Precio de alquiler (solo para maquinaria) -->
                    <div id="precio-alquiler-container" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="precio_alquiler_dia">Precio de Alquiler por Día</label>
                            <input type="number" class="form-control" id="precio_alquiler_dia" name="precio_alquiler_dia" 
                                   step="0.01" min="0" placeholder="0.00">
                            <small class="text-muted">Dejar en 0 si solo es para venta</small>
                        </div>
                    </div>
                </div>

                <!-- Stock y Estado -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-boxes me-2"></i>Stock y Estado
                        </h5>
                    </div>
                    <!-- Stock solo para venta por stock o maquinaria -->
                    <div class="col-md-6 mb-3" id="stock-container">
                        <label for="stock_disponible">Stock Disponible</label>
                        <input type="number" class="form-control" id="stock_disponible" name="stock_disponible" 
                               min="0" value="0">
                        <small class="text-muted" id="stock-help-text">Cantidad disponible en inventario</small>
                    </div>
                    <div class="col-md-6 mb-3" id="stock-minimo-container">
                        <label for="stock_minimo">Stock Mínimo</label>
                        <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                               min="0" value="0">
                        <small class="text-muted">Cantidad mínima antes de alerta</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado">Estado</label>
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
                    <div class="col-12 text-end">
                        <a href="<?php echo $baseUrl; ?>/admin/productos" class="btn btn-secondary me-2">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Producto
                        </button>
                    </div>
                </div>
            </form>
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

// Función para actualizar el tipo de venta según la categoría seleccionada
function actualizarTipoVenta() {
    const categoriaSelect = document.getElementById('categoria_id');
    const tipoVentaContainer = document.getElementById('tipo-venta-container');
    const preciosContainer = document.getElementById('precios-container');
    const precioAlquilerContainer = document.getElementById('precio-alquiler-container');
    const stockContainer = document.getElementById('stock-container');
    const stockMinimoContainer = document.getElementById('stock-minimo-container');
    const tipoVentaSelect = document.getElementById('tipo_venta');
    
    const selectedOption = categoriaSelect.options[categoriaSelect.selectedIndex];
    const tipoCategoria = selectedOption ? selectedOption.getAttribute('data-tipo') : null;
    
    // Ocultar todo primero
    tipoVentaContainer.style.display = 'none';
    preciosContainer.style.display = 'none';
    precioAlquilerContainer.style.display = 'none';
    document.getElementById('precios-stock-container').style.display = 'none';
    document.getElementById('precios-kg-container').style.display = 'none';
    
    // Resetear valores
    if (tipoVentaSelect) {
        tipoVentaSelect.value = '';
    }
    document.getElementById('precio_venta').value = '';
    document.getElementById('precio_por_kg').value = '';
    document.getElementById('precio_alquiler_dia').value = '';
    
    if (tipoCategoria === 'material') {
        // Mostrar dropdown de tipo de venta para Materiales Pétreos
        tipoVentaContainer.style.display = 'block';
        stockContainer.style.display = 'block';
        stockMinimoContainer.style.display = 'block';
        document.getElementById('stock-help-text').textContent = 'Cantidad disponible en inventario';
    } else if (tipoCategoria === 'maquinaria') {
        // Para maquinaria, mostrar precio de alquiler y stock
        preciosContainer.style.display = 'block';
        precioAlquilerContainer.style.display = 'block';
        stockContainer.style.display = 'block';
        stockMinimoContainer.style.display = 'block';
        document.getElementById('stock-help-text').textContent = 'Cantidad disponible en inventario';
    } else {
        // Si no hay categoría seleccionada, ocultar todo
        stockContainer.style.display = 'block';
        stockMinimoContainer.style.display = 'block';
    }
}

// Función para mostrar campos según el tipo de venta seleccionado
function mostrarCamposVenta() {
    const tipoVenta = document.getElementById('tipo_venta').value;
    const preciosContainer = document.getElementById('precios-container');
    const preciosStockContainer = document.getElementById('precios-stock-container');
    const preciosKgContainer = document.getElementById('precios-kg-container');
    const stockContainer = document.getElementById('stock-container');
    const stockMinimoContainer = document.getElementById('stock-minimo-container');
    
    // Mostrar contenedor de precios
    preciosContainer.style.display = 'block';
    
    if (tipoVenta === 'stock') {
        // Mostrar campos para venta por stock
        preciosStockContainer.style.display = 'block';
        preciosKgContainer.style.display = 'none';
        stockContainer.style.display = 'block';
        stockMinimoContainer.style.display = 'block';
        document.getElementById('stock-help-text').textContent = 'Cantidad disponible en inventario (unidades)';
        
        // Hacer requerido precio_venta
        document.getElementById('precio_venta').required = true;
        document.getElementById('precio_por_kg').required = false;
    } else if (tipoVenta === 'kilogramos') {
        // Mostrar campos para venta por kilogramos
        preciosStockContainer.style.display = 'none';
        preciosKgContainer.style.display = 'block';
        stockContainer.style.display = 'none';
        stockMinimoContainer.style.display = 'none';
        
        // Hacer requerido precio_por_kg
        document.getElementById('precio_por_kg').required = true;
        document.getElementById('precio_venta').required = false;
    } else {
        // Ocultar todo si no hay selección
        preciosStockContainer.style.display = 'none';
        preciosKgContainer.style.display = 'none';
        stockContainer.style.display = 'block';
        stockMinimoContainer.style.display = 'block';
    }
}

// Función para validar el formulario antes de enviar
function validarFormulario() {
    const categoriaSelect = document.getElementById('categoria_id');
    const selectedOption = categoriaSelect.options[categoriaSelect.selectedIndex];
    const tipoCategoria = selectedOption ? selectedOption.getAttribute('data-tipo') : null;
    const tipoVenta = document.getElementById('tipo_venta').value;
    
    if (tipoCategoria === 'material') {
        if (!tipoVenta) {
            alert('Por favor, selecciona el tipo de venta (Stock o Kilogramos)');
            document.getElementById('tipo_venta').focus();
            return false;
        }
        
        if (tipoVenta === 'stock') {
            const precioVenta = parseFloat(document.getElementById('precio_venta').value);
            if (!precioVenta || precioVenta <= 0) {
                alert('El precio de venta por unidad es obligatorio cuando se vende por stock');
                document.getElementById('precio_venta').focus();
                return false;
            }
        } else if (tipoVenta === 'kilogramos') {
            const precioPorKg = parseFloat(document.getElementById('precio_por_kg').value);
            if (!precioPorKg || precioPorKg <= 0) {
                alert('El precio por kilogramo es obligatorio cuando se vende por kilogramos');
                document.getElementById('precio_por_kg').focus();
                return false;
            }
        }
    }
    
    return true;
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarTipoVenta();
});
</script>
