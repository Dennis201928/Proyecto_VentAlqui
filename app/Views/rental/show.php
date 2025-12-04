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
                <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl; ?>/alquiler">Alquiler</a>
                <span class="breadcrumb-item active">Agendar Alquiler</span>
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
                <h3 class="mb-4"><i class="fas fa-calendar-alt mr-2"></i>Selecciona las Fechas</h3>
                
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Instrucciones:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Haz clic en una fecha para seleccionar el inicio del alquiler</li>
                        <li>Haz clic en otra fecha para establecer el fin del alquiler</li>
                        <li>Puedes seleccionar cualquier fecha siempre que haya stock disponible</li>
                        <li>Las fechas marcadas en <span style="background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 4px;">rojo</span> indican que no hay stock disponible</li>
                        <li>Los colores amarillo, azul y verde muestran alquileres existentes pero no bloquean la selección si hay stock</li>
                    </ul>
                </div>
                
                <div id="calendar"></div>
                
                <div class="selected-dates" id="selected-dates" style="display: none;">
                    <h5><i class="fas fa-check-circle text-success mr-2"></i>Fechas Seleccionadas:</h5>
                    <p class="mb-1"><strong>Fecha Inicio:</strong> <span id="fecha-inicio-display"></span></p>
                    <p class="mb-1"><strong>Fecha Fin:</strong> <span id="fecha-fin-display"></span></p>
                    <p class="mb-0"><strong>Días:</strong> <span id="dias-display"></span></p>
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
                    <strong>Precio por día:</strong> 
                    <?php if (!empty($product['precio_alquiler_dia']) && $product['precio_alquiler_dia'] > 0): ?>
                        <h5 class="text-primary">$<?php echo number_format((float)$product['precio_alquiler_dia'], 2); ?></h5>
                    <?php else: ?>
                        <h5 class="text-warning">Consultar precio</h5>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <strong>Stock disponible:</strong> <?php echo (int)$product['stock_disponible']; ?>
                </div>
            </div>
            
            <div class="price-calculator" id="price-calculator" style="display: none;">
                <h5 class="mb-3"><i class="fas fa-calculator mr-2"></i>Resumen del Alquiler</h5>
                <?php if (!empty($product['precio_alquiler_dia']) && $product['precio_alquiler_dia'] > 0): ?>
                <div class="mb-2">
                    <span>Precio por día:</span>
                    <span class="float-right">$<?php echo number_format((float)$product['precio_alquiler_dia'], 2); ?></span>
                </div>
                <?php else: ?>
                <div class="mb-2">
                    <span class="text-warning">Precio a consultar</span>
                </div>
                <?php endif; ?>
                <div class="mb-2">
                    <span>Días seleccionados:</span>
                    <span class="float-right" id="dias-calc">0</span>
                </div>
                <hr style="border-color: rgba(255,255,255,0.3);">
                <div class="mb-3">
                    <!-- <h4 class="mb-0">
                        <span>Total:</span>
                        <span class="float-right" id="total-calc">$0.00</span>
                    </h4> -->
                </div>
                <button class="btn btn-light btn-block btn-lg" id="btn-reservar" onclick="confirmarReserva()">
                    <i class="fas fa-check mr-2"></i>Confirmar Reserva
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Content End -->

<script>
    let selectedStart = null;
    let selectedEnd = null;
    const productId = <?php echo $product_id; ?>;
    const precioDia = <?php echo (float)($product['precio_alquiler_dia'] ?? 0); ?>;
    const bookedDates = [];
    let stockDisponible = 0;
    
    <?php if (empty($product['precio_alquiler_dia']) || $product['precio_alquiler_dia'] <= 0): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning';
        alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i><strong>Atención:</strong> Este producto no tiene precio de alquiler configurado. Por favor, contacta con nosotros para más información.';
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
            events: function(fetchInfo, successCallback, failureCallback) {
                fetch(`/Proyecto_VentAlqui/api/rental-dates.php?producto_id=${productId}&fecha_desde=${fetchInfo.startStr}&fecha_hasta=${fetchInfo.endStr}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error HTTP: ' + response.status);
                        }
                        return response.text();
                    })
                    .then(text => {
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                bookedDates.length = 0;
                                stockDisponible = data.stock_disponible || 0;
                                
                                // Guardar todos los eventos para referencia
                                // Los eventos con classNames 'no-stock' indican que no hay stock disponible
                                if (data.events && Array.isArray(data.events)) {
                                    bookedDates.length = 0; // Limpiar array anterior
                                    data.events.forEach(event => {
                                        if (event.start && event.end) {
                                            bookedDates.push({
                                                start: event.start,
                                                end: event.end,
                                                classNames: event.classNames || []
                                            });
                                        }
                                    });
                                }
                                successCallback(data.events || []);
                            } else {
                                successCallback([]);
                            }
                        } catch (e) {
                            successCallback([]);
                        }
                    })
                    .catch(error => {
                        successCallback([]);
                    });
            },
            select: function(selectInfo) {
                const startDate = selectInfo.startStr;
                const endDate = selectInfo.endStr;
                
                // Solo verificar si el stock es 0 (completamente agotado)
                // Si hay stock disponible, se permite seleccionar incluso si hay alquileres en esas fechas
                const isNoStock = bookedDates.some(booking => {
                    // Verificar si es un evento de "sin stock"
                    return booking.classNames && booking.classNames.includes('no-stock');
                });
                
                if (isNoStock || stockDisponible <= 0) {
                    alert('No hay stock disponible para este producto. Por favor, selecciona otro producto o contacta con nosotros.');
                    calendar.unselect();
                    return;
                }
                
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const start = new Date(startDate);
                start.setHours(0, 0, 0, 0);
                
                if (start < today) {
                    alert('No puedes seleccionar fechas pasadas.');
                    calendar.unselect();
                    return;
                }
                
                selectedStart = startDate;
                selectedEnd = endDate;
                
                updateSelectedDates(startDate, endDate);
                calendar.unselect();
            },
            dateClick: function(info) {
                const clickedDate = info.dateStr;
                

                const isNoStock = bookedDates.some(booking => {
                    // Verificar si es un evento de "sin stock"
                    return booking.classNames && booking.classNames.includes('no-stock');
                });
                
                if (isNoStock || stockDisponible <= 0) {
                    alert('No hay stock disponible para este producto.');
                    return;
                }
                
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const clicked = new Date(clickedDate);
                clicked.setHours(0, 0, 0, 0);
                
                if (clicked < today) {
                    alert('No puedes seleccionar fechas pasadas.');
                    return;
                }
                
                if (!selectedStart) {
                    selectedStart = clickedDate;
                    selectedEnd = clickedDate;
                    updateSelectedDates(clickedDate, clickedDate);
                } else if (!selectedEnd || selectedEnd === selectedStart) {
                    const start = new Date(selectedStart);
                    const end = new Date(clickedDate);
                    
                    if (end < start) {
                        selectedEnd = selectedStart;
                        selectedStart = clickedDate;
                    } else {
                        selectedEnd = clickedDate;
                    }
                    
                    updateSelectedDates(selectedStart, selectedEnd);
                } else {
                    selectedStart = clickedDate;
                    selectedEnd = clickedDate;
                    updateSelectedDates(clickedDate, clickedDate);
                }
            }
        });
        
        calendar.render();
    });

    function updateSelectedDates(start, end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        // Actualizar fechas seleccionadas
        const fechaInicioDisplay = document.getElementById('fecha-inicio-display');
        const fechaFinDisplay = document.getElementById('fecha-fin-display');
        const diasDisplay = document.getElementById('dias-display');
        const selectedDates = document.getElementById('selected-dates');
        
        if (fechaInicioDisplay) fechaInicioDisplay.textContent = formatDate(startDate);
        if (fechaFinDisplay) fechaFinDisplay.textContent = formatDate(endDate);
        if (diasDisplay) diasDisplay.textContent = diffDays + ' día(s)';
        if (selectedDates) selectedDates.style.display = 'block';
        
        // Actualizar calculadora de precio
        const diasCalc = document.getElementById('dias-calc');
        const totalCalc = document.getElementById('total-calc');
        const priceCalculator = document.getElementById('price-calculator');
        
        if (diasCalc) diasCalc.textContent = diffDays;
        
        if (totalCalc) {
            if (precioDia > 0) {
                const total = precioDia * diffDays;
                totalCalc.textContent = '$' + total.toFixed(2);
            } else {
                totalCalc.textContent = 'Consultar';
            }
        }
        
        if (priceCalculator) priceCalculator.style.display = 'block';
    }

    function formatDate(date) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('es-ES', options);
    }

    function confirmarReserva() {
        if (!selectedStart || !selectedEnd) {
            alert('Por favor, selecciona las fechas de alquiler.');
            return;
        }
        
        if (precioDia <= 0) {
            if (!confirm('Este producto no tiene precio configurado. La reserva se creará como consulta. ¿Deseas continuar?')) {
                return;
            }
        } else {
            if (!confirm('¿Confirmas la reserva para las fechas seleccionadas?')) {
                return;
            }
        }
        
        fetch('/Proyecto_VentAlqui/api/rentals.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                producto_id: productId,
                fecha_inicio: selectedStart,
                fecha_fin: selectedEnd
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
                alert('¡Reserva creada exitosamente!');
                window.location.href = '<?php echo $baseUrl; ?>/mis-alquileres';
            } else {
                alert('Error: ' + (data.message || 'No se pudo crear la reserva'));
            }
        })
        .catch(error => {
            alert('Error al crear la reserva. Por favor, intenta nuevamente.');
        });
    }
</script>

