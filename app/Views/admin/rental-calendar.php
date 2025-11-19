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
    .rental-event {
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
                        <i class="fas fa-calendar-alt text-primary me-2"></i>Calendario de Alquileres
                    </h2>
                </div>

    <!-- Filtros -->
    <div class="row px-xl-5 mb-4">
        <div class="col-12">
            <div class="filters-panel">
                <h5 class="mb-3"><i class="fas fa-filter mr-2"></i>Filtros</h5>
                <form method="GET" action="<?php echo $baseUrl; ?>/admin/calendario-alquileres" id="filtersForm">
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
                                <option value="confirmado" <?php echo (($filters['estado'] ?? '') == 'confirmado') ? 'selected' : ''; ?>>Confirmado</option>
                                <option value="en_curso" <?php echo (($filters['estado'] ?? '') == 'en_curso') ? 'selected' : ''; ?>>En Curso</option>
                                <option value="finalizado" <?php echo (($filters['estado'] ?? '') == 'finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                                <option value="cancelado" <?php echo (($filters['estado'] ?? '') == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-2"></i>Aplicar Filtros
                            </button>
                            <a href="<?php echo $baseUrl; ?>/admin/calendario-alquileres" class="btn btn-outline-secondary">
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
                <span>Confirmado</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background-color: #007bff;"></span>
                <span>En Curso</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background-color: #6c757d;"></span>
                <span>Finalizado</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background-color: #dc3545;"></span>
                <span>Cancelado</span>
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

<!-- Modal para detalles del alquiler -->
<div class="modal fade" id="rentalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Alquiler</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="rentalModalBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer" id="rentalModalFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let calendar;
    let currentRental = null;
    
    const rentals = <?php echo json_encode($rentals); ?>;
    const rentalColors = {
        'pendiente': '#ffc107',
        'confirmado': '#28a745',
        'en_curso': '#007bff',
        'finalizado': '#6c757d',
        'cancelado': '#dc3545'
    };

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        const calendarEvents = rentals.map(rental => {
            const startDate = new Date(rental.fecha_inicio);
            const endDate = new Date(rental.fecha_fin);
            endDate.setDate(endDate.getDate() + 1);
            
            return {
                id: rental.id,
                title: rental.producto_nombre + ' - ' + rental.usuario_nombre + ' ' + rental.usuario_apellido,
                start: startDate.toISOString().split('T')[0],
                end: endDate.toISOString().split('T')[0],
                color: rentalColors[rental.estado] || '#6c757d',
                extendedProps: {
                    rental: rental
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
                const rental = info.event.extendedProps.rental;
                currentRental = rental;
                showRentalDetails(rental);
            },
            eventDisplay: 'block',
            height: 'auto'
        });
        
        calendar.render();
    });

    function showRentalDetails(rental) {
        const modalBody = document.getElementById('rentalModalBody');
        const modalFooter = document.getElementById('rentalModalFooter');
        
        const estados = {
            'pendiente': 'Pendiente',
            'confirmado': 'Confirmado',
            'en_curso': 'En Curso',
            'finalizado': 'Finalizado',
            'cancelado': 'Cancelado'
        };
        
        const fechaInicio = new Date(rental.fecha_inicio).toLocaleDateString('es-ES');
        const fechaFin = new Date(rental.fecha_fin).toLocaleDateString('es-ES');
        const fechaCreacion = new Date(rental.fecha_creacion).toLocaleDateString('es-ES');
        
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Producto:</strong></h6>
                    <p>${rental.producto_nombre}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Categoría:</strong></h6>
                    <p>${rental.categoria_nombre || 'N/A'}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Cliente:</strong></h6>
                    <p>${rental.usuario_nombre} ${rental.usuario_apellido}</p>
                    <p class="text-muted small">${rental.usuario_email}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Estado:</strong></h6>
                    <p><span class="badge badge-${getBadgeClass(rental.estado)}" id="rentalEstadoBadge">${estados[rental.estado] || rental.estado}</span></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Fecha Inicio:</strong></h6>
                    <p>${fechaInicio}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Fecha Fin:</strong></h6>
                    <p>${fechaFin}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Precio por día:</strong></h6>
                    <p>$${parseFloat(rental.precio_dia).toFixed(2)}</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Total:</strong></h6>
                    <p class="h5 text-primary">$${parseFloat(rental.total).toFixed(2)}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h6><strong>Fecha de Creación:</strong></h6>
                    <p class="text-muted">${fechaCreacion}</p>
                </div>
            </div>
            ${rental.observaciones ? `
            <div class="row">
                <div class="col-12">
                    <h6><strong>Observaciones:</strong></h6>
                    <p>${rental.observaciones}</p>
                </div>
            </div>
            ` : ''}
        `;
        
        let footerButtons = '';
        
        if (rental.estado === 'pendiente') {
            footerButtons = `
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="rechazarAlquiler(${rental.id})">
                    <i class="fas fa-times mr-2"></i>Rechazar
                </button>
                <button type="button" class="btn btn-success" onclick="confirmarAlquiler(${rental.id})">
                    <i class="fas fa-check mr-2"></i>Confirmar
                </button>
            `;
        } else if (rental.estado === 'confirmado') {
            footerButtons = `
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="marcarEnCurso(${rental.id})">
                    <i class="fas fa-play mr-2"></i>Marcar en Curso
                </button>
            `;
        } else if (rental.estado === 'en_curso') {
            footerButtons = `
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-info" onclick="finalizarAlquiler(${rental.id})">
                    <i class="fas fa-check-circle mr-2"></i>Finalizar
                </button>
            `;
        } else {
            footerButtons = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>';
        }
        
        modalFooter.innerHTML = footerButtons;
        $('#rentalModal').modal('show');
    }

    function getBadgeClass(estado) {
        const classes = {
            'pendiente': 'warning',
            'confirmado': 'success',
            'en_curso': 'primary',
            'finalizado': 'secondary',
            'cancelado': 'danger'
        };
        return classes[estado] || 'secondary';
    }

    function confirmarAlquiler(rentalId) {
        if (!confirm('¿Estás seguro de que deseas confirmar este alquiler?')) return;
        updateRentalStatus(rentalId, 'confirmado', 'Alquiler confirmado por el administrador');
    }

    function rechazarAlquiler(rentalId) {
        const motivo = prompt('¿Cuál es el motivo del rechazo? (opcional)');
        const observaciones = motivo ? 'Rechazado por el administrador. Motivo: ' + motivo : 'Rechazado por el administrador';
        if (!confirm('¿Estás seguro de que deseas rechazar este alquiler?')) return;
        updateRentalStatus(rentalId, 'cancelado', observaciones);
    }

    function marcarEnCurso(rentalId) {
        if (!confirm('¿Deseas marcar este alquiler como "En Curso"? Esto indicará que el producto ya fue entregado al cliente.')) return;
        updateRentalStatus(rentalId, 'en_curso', 'Alquiler marcado como en curso por el administrador');
    }

    function finalizarAlquiler(rentalId) {
        if (!confirm('¿Deseas finalizar este alquiler? Esto indicará que el producto ya fue devuelto y el alquiler ha concluido.')) return;
        updateRentalStatus(rentalId, 'finalizado', 'Alquiler finalizado por el administrador');
    }

    function updateRentalStatus(rentalId, estado, observaciones) {
        const footer = document.getElementById('rentalModalFooter');
        footer.innerHTML = `<button type="button" class="btn btn-secondary" data-dismiss="modal" disabled>Cerrar</button><span class="ml-2"><i class="fas fa-spinner fa-spin"></i> Procesando...</span>`;
        
        fetch('/Proyecto_VentAlqui/api/rentals.php/' + rentalId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado: estado, observaciones: observaciones }),
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
                if (currentRental) {
                    currentRental.estado = estado;
                    currentRental.observaciones = observaciones;
                }
                $('#rentalModal').modal('hide');
                alert('Estado actualizado exitosamente');
                window.location.reload();
            } else {
                const errorMsg = data.message || 'No se pudo actualizar el estado del alquiler';
                alert('Error: ' + errorMsg);
                restoreFooterButtons(rentalId);
            }
        })
        .catch(error => {
            alert('Error al actualizar el estado del alquiler: ' + error.message);
            restoreFooterButtons(rentalId);
        });
    }

    function restoreFooterButtons(rentalId) {
        const footer = document.getElementById('rentalModalFooter');
        if (!currentRental) {
            footer.innerHTML = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>';
            return;
        }
        const estado = currentRental.estado;
        let footerButtons = '';
        if (estado === 'pendiente') {
            footerButtons = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button><button type="button" class="btn btn-danger" onclick="rechazarAlquiler(${rentalId})"><i class="fas fa-times mr-2"></i>Rechazar</button><button type="button" class="btn btn-success" onclick="confirmarAlquiler(${rentalId})"><i class="fas fa-check mr-2"></i>Confirmar</button>`;
        } else if (estado === 'confirmado') {
            footerButtons = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button><button type="button" class="btn btn-primary" onclick="marcarEnCurso(${rentalId})"><i class="fas fa-play mr-2"></i>Marcar en Curso</button>`;
        } else if (estado === 'en_curso') {
            footerButtons = `<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button><button type="button" class="btn btn-info" onclick="finalizarAlquiler(${rentalId})"><i class="fas fa-check-circle mr-2"></i>Finalizar</button>`;
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
