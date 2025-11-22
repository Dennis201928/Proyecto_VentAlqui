<?php
use App\Core\Config;
$baseUrl = Config::SITE_URL;
?>
<style>
    .filters-panel {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .calendar-container {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .sale-event {
        cursor: pointer;
    }
    .fc-event-title {
        font-weight: 600;
    }
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .legend-item {
        display: inline-block;
        margin-right: 20px;
        margin-bottom: 10px;
    }
    .legend-color {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 4px;
        margin-right: 5px;
        vertical-align: middle;
    }
</style>

<div class="admin-content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calendar-alt text-primary me-2"></i>Calendario de Ventas
        </h2>
    </div>

    <!-- Filtros -->
    <div class="row px-xl-5 mb-4">
        <div class="col-12">
            <div class="filters-panel">
                <h5 class="mb-3"><i class="fas fa-filter mr-2"></i>Filtros</h5>
                <form method="GET" action="<?php echo $baseUrl; ?>/admin/calendario-ventas" id="filtersForm">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="categoria_id">Categoría</label>
                            <select class="form-control" id="categoria_id" name="categoria_id">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo (($filters['categoria_id'] ?? null) == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="producto_id">Producto</label>
                            <select class="form-control" id="producto_id" name="producto_id">
                                <option value="">Todos los productos</option>
                                <?php foreach ($products as $prod): ?>
                                    <option value="<?php echo $prod['id']; ?>" 
                                            <?php echo (($filters['producto_id'] ?? null) == $prod['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prod['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="producto_nombre">Nombre del Producto</label>
                            <input type="text" class="form-control" id="producto_nombre" name="producto_nombre" 
                                   value="<?php echo htmlspecialchars($filters['producto_nombre'] ?? ''); ?>" 
                                   placeholder="Buscar por nombre...">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="estado">Estado</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" <?php echo (($filters['estado'] ?? '') == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="confirmada" <?php echo (($filters['estado'] ?? '') == 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                <option value="enviada" <?php echo (($filters['estado'] ?? '') == 'enviada') ? 'selected' : ''; ?>>Enviada</option>
                                <option value="entregada" <?php echo (($filters['estado'] ?? '') == 'entregada') ? 'selected' : ''; ?>>Entregada</option>
                                <option value="cancelada" <?php echo (($filters['estado'] ?? '') == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-2"></i>Aplicar Filtros
                            </button>
                            <a href="<?php echo $baseUrl; ?>/admin/calendario-ventas" class="btn btn-outline-secondary">
                                <i class="fas fa-times mr-2"></i>Limpiar Filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="row px-xl-5 mb-4">
        <div class="col-12">
            <div class="legend-item">
                <span class="legend-color" style="background-color: #ffc107;"></span>
                <span>Pendiente</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background-color: #28a745;"></span>
                <span>Confirmada</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background-color: #007bff;"></span>
                <span>Enviada</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background-color: #17a2b8;"></span>
                <span>Entregada</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background-color: #dc3545;"></span>
                <span>Cancelada</span>
            </div>
        </div>
    </div>

    <!-- Calendario -->
    <div class="row px-xl-5">
        <div class="col-12">
            <div class="calendar-container">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>
<!-- Content End -->

<!-- Modal para detalles de la venta -->
<div class="modal fade" id="saleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Venta</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="saleModalBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer" id="saleModalFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let calendar;
    let currentSale = null;
    
    const sales = <?php echo json_encode($sales); ?>;
    const saleColors = {
        'pendiente': '#ffc107',
        'confirmada': '#28a745',
        'enviada': '#007bff',
        'entregada': '#17a2b8',
        'cancelada': '#dc3545'
    };

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        const calendarEvents = sales.map(sale => {
            const fechaCreacion = new Date(sale.fecha_creacion);
            const fechaStr = fechaCreacion.toISOString().split('T')[0];
            
            return {
                id: sale.id,
                title: 'Venta #' + sale.id + ' - ' + (sale.productos_nombres || 'Productos'),
                start: fechaStr,
                end: fechaStr,
                color: saleColors[sale.estado] || '#6c757d',
                extendedProps: {
                    sale: sale
                }
            };
        });
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            firstDay: 1,
            events: calendarEvents,
            eventClick: function(info) {
                const sale = info.event.extendedProps.sale;
                currentSale = sale;
                showSaleDetails(sale);
            },
            eventDisplay: 'block',
            height: 'auto'
        });
        
        calendar.render();
    });

    function showSaleDetails(sale) {
        const modalBody = document.getElementById('saleModalBody');
        const modalFooter = document.getElementById('saleModalFooter');
        
        const estados = {
            'pendiente': 'Pendiente',
            'confirmada': 'Confirmada',
            'enviada': 'Enviada',
            'entregada': 'Entregada',
            'cancelada': 'Cancelada'
        };
        
        const fechaCreacion = new Date(sale.fecha_creacion).toLocaleDateString('es-ES');
        const fechaActualizacion = sale.fecha_actualizacion ? new Date(sale.fecha_actualizacion).toLocaleDateString('es-ES') : 'N/A';
        
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>ID de Venta:</strong></h6>
                    <p>#${sale.id}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Estado:</strong></h6>
                    <p><span class="badge badge-${getBadgeClass(sale.estado)}" id="saleEstadoBadge">${estados[sale.estado] || sale.estado}</span></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Cliente:</strong></h6>
                    <p>${sale.usuario_nombre} ${sale.usuario_apellido}</p>
                    <p class="text-muted small">${sale.usuario_email}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Productos:</strong></h6>
                    <p>${sale.productos_nombres || 'N/A'}</p>
                    ${sale.total_items ? `<p class="text-muted small">${sale.total_items} item(s)</p>` : ''}
                </div>
            </div>
            <div class="row">
                // <div class="col-md-6">
                //     <h6><strong>Total:</strong></h6>
                //     <p class="h5 text-primary">$${parseFloat(sale.total).toFixed(2)}</p>
                // </div>
                <div class="col-md-6">
                    <h6><strong>Método de Pago:</strong></h6>
                    <p>${sale.metodo_pago || 'N/A'}</p>
                </div>
            </div>
            ${sale.impuestos ? `
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Impuestos:</strong></h6>
                    <p>$${parseFloat(sale.impuestos).toFixed(2)}</p>
                </div>
            </div>
            ` : ''}
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Fecha de Creación:</strong></h6>
                    <p class="text-muted">${fechaCreacion}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Fecha de Actualización:</strong></h6>
                    <p class="text-muted">${fechaActualizacion}</p>
                </div>
            </div>
            ${sale.direccion_entrega ? `
            <div class="row">
                <div class="col-12">
                    <h6><strong>Dirección de Entrega:</strong></h6>
                    <p>${sale.direccion_entrega}</p>
                </div>
            </div>
            ` : ''}
        `;
        
        let footerButtons = '';
        
        if (sale.estado === 'pendiente') {
            footerButtons = `
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="cancelarVenta(${sale.id})">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="confirmarVenta(${sale.id})">
                    <i class="fas fa-check mr-2"></i>Confirmar
                </button>
            `;
        } else if (sale.estado === 'confirmada') {
            footerButtons = `
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="marcarEnviada(${sale.id})">
                    <i class="fas fa-shipping-fast mr-2"></i>Marcar como Enviada
                </button>
            `;
        } else if (sale.estado === 'enviada') {
            footerButtons = `
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-info" onclick="marcarEntregada(${sale.id})">
                    <i class="fas fa-check-circle mr-2"></i>Marcar como Entregada
                </button>
            `;
        } else {
            footerButtons = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>';
        }
        
        modalFooter.innerHTML = footerButtons;
        $('#saleModal').modal('show');
    }

    function getBadgeClass(estado) {
        const classes = {
            'pendiente': 'warning',
            'confirmada': 'success',
            'enviada': 'primary',
            'entregada': 'info',
            'cancelada': 'danger'
        };
        return classes[estado] || 'secondary';
    }

    function confirmarVenta(saleId) {
        if (!confirm('¿Estás seguro de que deseas confirmar esta venta?')) return;
        updateSaleStatus(saleId, 'confirmada');
    }

    function cancelarVenta(saleId) {
        const motivo = prompt('¿Cuál es el motivo de la cancelación? (opcional)');
        if (!confirm('¿Estás seguro de que deseas cancelar esta venta?')) return;
        updateSaleStatus(saleId, 'cancelada');
    }

    function marcarEnviada(saleId) {
        if (!confirm('¿Deseas marcar esta venta como enviada? Esto indicará que el pedido ha sido enviado al cliente.')) return;
        updateSaleStatus(saleId, 'enviada');
    }

    function marcarEntregada(saleId) {
        if (!confirm('¿Deseas marcar esta venta como entregada? Esto indicará que el pedido ha sido entregado al cliente.')) return;
        updateSaleStatus(saleId, 'entregada');
    }

    function updateSaleStatus(saleId, estado) {
        const footer = document.getElementById('saleModalFooter');
        footer.innerHTML = `<button type="button" class="btn btn-secondary" data-dismiss="modal" disabled>Cerrar</button><span class="ml-2"><i class="fas fa-spinner fa-spin"></i> Procesando...</span>`;
        
        fetch('/Proyecto_VentAlqui/api/orders.php/' + saleId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado: estado }),
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try { 
                        const jsonData = JSON.parse(text);
                        return jsonData;
                    } catch (e) { 
                        throw new Error('Error HTTP: ' + response.status + ' - ' + text.substring(0, 200)); 
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (currentSale) {
                    currentSale.estado = estado;
                }
                $('#saleModal').modal('hide');
                alert('Estado actualizado exitosamente');
                window.location.reload();
            } else {
                const errorMsg = data.message || 'No se pudo actualizar el estado de la venta';
                alert('Error: ' + errorMsg);
                restoreFooterButtons(saleId);
            }
        })
        .catch(error => {
            alert('Error al actualizar el estado de la venta: ' + error.message);
            restoreFooterButtons(saleId);
        });
    }

    function restoreFooterButtons(saleId) {
        const footer = document.getElementById('saleModalFooter');
        if (!currentSale) {
            footer.innerHTML = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>';
            return;
        }
        const estado = currentSale.estado;
        let footerButtons = '';
        if (estado === 'pendiente') {
            footerButtons = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button><button type="button" class="btn btn-danger" onclick="cancelarVenta(${saleId})"><i class="fas fa-times mr-2"></i>Cancelar</button><button type="button" class="btn btn-success" onclick="confirmarVenta(${saleId})"><i class="fas fa-check mr-2"></i>Confirmar</button>`;
        } else if (estado === 'confirmada') {
            footerButtons = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button><button type="button" class="btn btn-primary" onclick="marcarEnviada(${saleId})"><i class="fas fa-shipping-fast mr-2"></i>Marcar como Enviada</button>`;
        } else if (estado === 'enviada') {
            footerButtons = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button><button type="button" class="btn btn-info" onclick="marcarEntregada(${saleId})"><i class="fas fa-check-circle mr-2"></i>Marcar como Entregada</button>`;
        } else {
            footerButtons = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>';
        }
        footer.innerHTML = footerButtons;
    }

    document.getElementById('categoria_id').addEventListener('change', function() {
        const categoriaId = this.value;
        if (categoriaId) {
            document.getElementById('filtersForm').submit();
        }
    });
</script>
