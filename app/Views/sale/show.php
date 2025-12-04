<?php
use App\Core\Config;
$baseUrl = Config::SITE_URL;
?>
<style>
    .calendar-container {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .product-info-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .selected-dates {
        background: #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
    }
    .price-calculator {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }
    .fc-daygrid-day.fc-day-disabled {
        background-color: #f8d7da !important;
        opacity: 0.6;
    }
</style>

<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl; ?>/">Inicio</a>
                <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl; ?>/venta">Venta</a>
                <span class="breadcrumb-item active">Agendar Venta</span>
            </nav>
        </div>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- Content Start -->
<div class="container-fluid py-5">
    <div class="row px-xl-5">
        <div class="col-lg-8">
            <div class="calendar-container">
                <h3 class="mb-4"><i class="fas fa-calendar-alt mr-2"></i>Selecciona la Fecha de Entrega</h3>
                
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Instrucciones:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Haz clic en una fecha para seleccionar la fecha de entrega deseada</li>
                        <li>Puedes seleccionar una fecha futura para programar la entrega</li>
                        <li>Las fechas pasadas no están disponibles</li>
                    </ul>
                </div>
                
                <div id="calendar"></div>
                
                <div class="selected-dates" id="selected-dates" style="display: none;">
                    <h5><i class="fas fa-check-circle text-success mr-2"></i>Fecha Seleccionada:</h5>
                    <p class="mb-2"><strong>Fecha de Entrega:</strong> <span id="fecha-entrega-display"></span></p>
                    <div class="alert alert-info mb-0 py-2">
                        <i class="fas fa-info-circle mr-2"></i><strong>Próximo paso:</strong> Revisa el resumen a la derecha y haz clic en "Agendar Venta" para confirmar.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="product-info-card mb-4">
                <h4 class="mb-3"><?php echo htmlspecialchars($product['nombre']); ?></h4>
                
                <?php if (!empty($product['imagen_principal'])): ?>
                    <img src="<?php echo htmlspecialchars(\App\Helpers\ImageHelper::getImageUrl($product['imagen_principal'] ?? '', $baseUrl ?? null)); ?>" 
                         alt="<?php echo htmlspecialchars($product['nombre']); ?>" 
                         class="img-fluid rounded mb-3">
                <?php endif; ?>
                
                <?php if (!empty($product['descripcion'])): ?>
                    <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($product['descripcion'])); ?></p>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong>Categoría:</strong> <?php echo htmlspecialchars($product['categoria_nombre'] ?? 'N/A'); ?>
                </div>
                
                <div class="mb-3">
                    <strong>Precio:</strong> 
                    <?php if (!empty($product['precio_venta']) && $product['precio_venta'] > 0): ?>
                        <h5 class="text-primary">$<?php echo number_format((float)$product['precio_venta'], 2); ?></h5>
                    <?php else: ?>
                        <h5 class="text-warning">Consultar precio</h5>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <strong>Stock disponible:</strong> 
                    <span class="badge badge-<?php echo $product['stock_disponible'] > 0 ? 'success' : 'danger'; ?>">
                        <?php echo (int)$product['stock_disponible']; ?> unidades
                    </span>
                </div>
            </div>
            
            <div class="product-info-card">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Cantidad</span>
                    </div>
                    <input type="number" class="form-control" id="quantity" value="<?php echo isset($cantidad) ? (int)$cantidad : 1; ?>" min="1" max="<?php echo (int)$product['stock_disponible']; ?>">
                </div>
                
                <div class="price-calculator" id="price-calculator" style="display: none;">
                    <h5 class="mb-3"><i class="fas fa-calculator mr-2"></i>Resumen de la Venta</h5>
                    <?php if (!empty($product['precio_venta']) && $product['precio_venta'] > 0): ?>
                    <div class="mb-2">
                        <span>Precio unitario:</span>
                        <span class="float-right">$<?php echo number_format((float)$product['precio_venta'], 2); ?></span>
                    </div>
                    <div class="mb-2">
                        <span>Cantidad:</span>
                        <span class="float-right" id="cantidad-calc"><?php echo isset($cantidad) ? (int)$cantidad : 1; ?></span>
                    </div>
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    <div class="mb-3">
                        <!-- <h4 class="mb-0">
                            <span>Total:</span>
                            <span class="float-right" id="total-calc">$<?php echo number_format((float)$product['precio_venta'], 2); ?></span>
                        </h4> -->
                    </div>
                    <?php else: ?>
                    <div class="mb-2">
                        <span>Cantidad:</span>
                        <span class="float-right" id="cantidad-calc"><?php echo isset($cantidad) ? (int)$cantidad : 1; ?></span>
                    </div>
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    <div class="mb-3">
                        <h5 class="mb-0 text-warning">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span>Precio a consultar</span>
                        </h5>
                        <p class="small mb-0 mt-2">Nos contactaremos contigo para acordar el precio</p>
                    </div>
                    <?php endif; ?>
                    <button class="btn btn-light btn-block btn-lg" id="btn-agendar" onclick="agendarVenta()" style="font-weight: bold; font-size: 1.1rem;">
                        <i class="fas fa-check mr-2"></i>Agendar Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Content End -->

<script>
    let selectedDate = null;
    const productId = <?php echo $product_id; ?>;
    const precioVenta = <?php echo (float)($product['precio_venta'] ?? 0); ?>;
    const stockDisponible = <?php echo (int)($product['stock_disponible'] ?? 0); ?>;
    const cantidadInicial = <?php echo isset($cantidad) ? (int)$cantidad : 1; ?>;
    
    // Inicializar la cantidad en el resumen al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const cantidadCalc = document.getElementById('cantidad-calc');
        if (cantidadCalc) {
            cantidadCalc.textContent = cantidadInicial;
        }
    });
    
    <?php if (empty($product['precio_venta']) || $product['precio_venta'] <= 0): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-info';
        alertDiv.innerHTML = '<i class="fas fa-info-circle mr-2"></i><strong>Información:</strong> Este producto no tiene precio de venta configurado. Puedes agendar la venta y nos contactaremos contigo para acordar el precio.';
        document.querySelector('.calendar-container').insertBefore(alertDiv, document.getElementById('calendar'));
    });
    <?php endif; ?>

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            validRange: {
                start: new Date().toISOString().split('T')[0]
            },
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            firstDay: 1,
            select: function(selectInfo) {
                const selectedDateStr = selectInfo.startStr;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selected = new Date(selectedDateStr);
                selected.setHours(0, 0, 0, 0);
                
                if (selected < today) {
                    alert('No puedes seleccionar fechas pasadas.');
                    calendar.unselect();
                    return;
                }
                
                selectedDate = selectedDateStr;
                updateSelectedDate(selectedDateStr);
                calendar.unselect();
            },
            dateClick: function(info) {
                const clickedDate = info.dateStr;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const clicked = new Date(clickedDate);
                clicked.setHours(0, 0, 0, 0);
                
                if (clicked < today) {
                    alert('No puedes seleccionar fechas pasadas.');
                    return;
                }
                
                // Limpiar selección anterior visualmente
                if (selectedDate) {
                    const prevDateEl = calendarEl.querySelector(`[data-date="${selectedDate}"]`);
                    if (prevDateEl) {
                        prevDateEl.classList.remove('fc-day-selected');
                        prevDateEl.style.backgroundColor = '';
                    }
                }
                
                selectedDate = clickedDate;
                updateSelectedDate(clickedDate);
                
                // Resaltar fecha seleccionada visualmente
                const dateEl = info.dayEl;
                if (dateEl) {
                    dateEl.classList.add('fc-day-selected');
                    dateEl.style.backgroundColor = '#b3d7ff';
                    dateEl.style.border = '2px solid #007bff';
                }
            }
        });
        
        calendar.render();
        
        // Actualizar precio cuando cambia la cantidad
        document.getElementById('quantity').addEventListener('change', function() {
            if (selectedDate) {
                updatePrice();
            }
        });
    });

    function updateSelectedDate(date) {
        if (!date) return;
        
        const dateObj = new Date(date);
        document.getElementById('fecha-entrega-display').textContent = formatDate(dateObj);
        document.getElementById('selected-dates').style.display = 'block';
        updatePrice();
        
        // Mostrar el resumen de precio
        const priceCalculator = document.getElementById('price-calculator');
        if (priceCalculator) {
            priceCalculator.style.display = 'block';
        }
        
        // Mostrar el botón principal de agendar
        const btnAgendarContainer = document.getElementById('btn-agendar-container');
        if (btnAgendarContainer) {
            btnAgendarContainer.style.display = 'block';
        }
        
        // Asegurar que el botón esté habilitado
        const btnAgendar = document.getElementById('btn-agendar');
        if (btnAgendar) {
            btnAgendar.disabled = false;
        }
        
        const btnAgendarMain = document.getElementById('btn-agendar-main');
        if (btnAgendarMain) {
            btnAgendarMain.disabled = false;
        }
    }

    function updatePrice() {
        const cantidad = parseInt(document.getElementById('quantity').value) || 1;
        
        if (cantidad > stockDisponible) {
            alert('La cantidad seleccionada excede el stock disponible.');
            document.getElementById('quantity').value = stockDisponible;
            return;
        }
        
        const cantidadCalc = document.getElementById('cantidad-calc');
        if (cantidadCalc) {
            cantidadCalc.textContent = cantidad;
        }
        
        const totalCalc = document.getElementById('total-calc');
        if (totalCalc) {
            if (precioVenta > 0) {
                const total = precioVenta * cantidad;
                totalCalc.textContent = '$' + total.toFixed(2);
            } else {
                // Si no hay precio, el mensaje ya está en el HTML
                // Solo actualizamos la cantidad
            }
        }
    }

    function formatDate(date) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('es-ES', options);
    }

    function agendarVenta() {
        if (!selectedDate) {
            alert('Por favor, selecciona la fecha de entrega.');
            return;
        }
        
        const cantidad = parseInt(document.getElementById('quantity').value) || 1;
        
        if (cantidad <= 0) {
            alert('La cantidad debe ser mayor a 0.');
            return;
        }
        
        if (cantidad > stockDisponible) {
            alert('La cantidad seleccionada excede el stock disponible.');
            return;
        }
        
        if (precioVenta <= 0) {
            if (!confirm('Este producto no tiene precio configurado. La venta se agendará y nos contactaremos contigo para acordar el precio. ¿Deseas continuar?')) {
                return;
            }
        } else {
            if (!confirm('¿Confirmas agendar la venta para la fecha seleccionada?')) {
                return;
            }
        }
        
        // Agregar al carrito con las fechas (igual que alquiler pero usando carrito)
        fetch('/Proyecto_VentAlqui/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                producto_id: productId,
                cantidad: cantidad,
                tipo: 'venta',
                fecha_inicio: selectedDate,
                fecha_fin: selectedDate
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Error HTTP: ' + response.status);
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('¡Venta agendada exitosamente! El producto ha sido agregado a tu carrito.');
                window.location.href = '<?php echo $baseUrl; ?>/carrito';
            } else {
                alert('Error: ' + (data.message || 'No se pudo agendar la venta'));
            }
        })
        .catch(error => {
            alert('Error al agendar la venta. Por favor, intenta nuevamente.');
            console.error('Error:', error);
        });
    }
</script>
