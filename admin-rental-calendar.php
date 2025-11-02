<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';
require_once 'includes/rental.php';
require_once 'includes/security.php';
Security::setSecurityHeaders();

$auth = new Auth();
$productSrv = new Product();
$rentalSrv = new Rental();
$current_user = $auth->getCurrentUser();
$auth->requireAdmin();

$filters = [
    'categoria_id' => isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : null,
    'producto_id' => isset($_GET['producto_id']) ? (int)$_GET['producto_id'] : null,
    'producto_nombre' => isset($_GET['producto_nombre']) ? trim($_GET['producto_nombre']) : null,
    'estado' => isset($_GET['estado']) ? $_GET['estado'] : null
];

$categories = $productSrv->getCategories();
$products = [];

if ($filters['categoria_id']) {
    $products = $productSrv->getProducts(['categoria_id' => $filters['categoria_id']]);
} else {
    $products = $productSrv->getProducts(['limit' => 100]);
}

$rentals = $rentalSrv->getAllRentalsWithFilters($filters);
if (isset($rentals['error'])) {
    $rentals = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo Config::SITE_NAME; ?> - Calendario de Alquileres (Admin)</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Calendario de alquileres para administradores" name="keywords">
    <meta content="Visualiza y gestiona todos los alquileres" name="description">

    <link href="img/favicon.ico" rel="icon">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">  

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">

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
</head>

<body>
    <div class="container-fluid">
        <div class="row bg-secondary py-1 px-xl-5">
            <div class="col-lg-6 d-none d-lg-block">
                <div class="d-inline-flex align-items-center h-100">
                    <a class="text-body mr-3" href="admin.php">Panel Admin</a>
                    <a class="text-body mr-3" href="index.php">Inicio</a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                            <?php echo htmlspecialchars($current_user['nombre'].' '.$current_user['apellido']); ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="admin.php"><i class="fas fa-tools mr-2"></i>Panel de Administración</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="index.php">Ir al Sitio</a>
                            <a class="dropdown-item" href="logout.php">Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row align-items-center bg-light py-3 px-xl-5 d-none d-lg-flex">
            <div class="col-lg-4">
                <a href="index.php" class="text-decoration-none">
                    <span class="h1 text-uppercase text-primary bg-dark px-2">Alqui</span>
                    <span class="h1 text-uppercase text-dark bg-primary px-2 ml-n1">Venta</span>
                </a>
            </div>
        </div>
    </div>


    <div class="container-fluid bg-dark mb-30">
        <div class="row px-xl-5">
            <div class="col-12 px-0">
                <nav class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-0">
                    <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                        <div class="navbar-nav mr-auto py-0">
                            <a href="admin.php" class="nav-item nav-link">Panel Admin</a>
                            <a href="admin-products.php" class="nav-item nav-link">Productos</a>
                            <a href="admin-rental-calendar.php" class="nav-item nav-link active">Calendario Alquileres</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>


    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="admin.php">Panel Admin</a>
                    <span class="breadcrumb-item active">Calendario de Alquileres</span>
                </nav>
            </div>
        </div>
    </div>


    <div class="container-fluid py-5">
        <div class="row px-xl-5">
            <div class="col-12">
                <h2 class="section-title position-relative text-uppercase mb-4">
                    <span class="bg-secondary pr-3">Calendario de Alquileres</span>
                </h2>
            </div>
        </div>


        <div class="row px-xl-5 mb-4">
            <div class="col-12">
                <div class="filters-panel">
                    <h5 class="mb-3"><i class="fas fa-filter mr-2"></i>Filtros</h5>
                    <form method="GET" action="admin-rental-calendar.php" id="filtersForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="categoria_id">Categoría</label>
                                <select class="form-control" id="categoria_id" name="categoria_id">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                                <?php echo ($filters['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
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
                                                <?php echo ($filters['producto_id'] == $prod['id']) ? 'selected' : ''; ?>>
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
                                    <option value="pendiente" <?php echo ($filters['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="confirmado" <?php echo ($filters['estado'] == 'confirmado') ? 'selected' : ''; ?>>Confirmado</option>
                                    <option value="en_curso" <?php echo ($filters['estado'] == 'en_curso') ? 'selected' : ''; ?>>En Curso</option>
                                    <option value="finalizado" <?php echo ($filters['estado'] == 'finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                                    <option value="cancelado" <?php echo ($filters['estado'] == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-2"></i>Aplicar Filtros
                                </button>
                                <a href="admin-rental-calendar.php" class="btn btn-outline-secondary">
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

    <!-- Footer -->
    <div class="container-fluid bg-dark text-secondary mt-5 pt-5">
        <div class="row border-top mx-xl-5 py-4" style="border-color: rgba(256, 256, 256, .1) !important;">
            <div class="col-md-6 px-xl-0">
                <p class="mb-md-0 text-center text-md-left text-secondary">
                    &copy; <a class="text-primary" href="#"><?php echo Config::SITE_NAME; ?></a>. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>

    <!-- Modal para detalles del alquiler -->
    <div class="modal fade" id="rentalModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/es.js"></script>

    <script>
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
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                firstDay: 1, // Lunes
                events: calendarEvents,
                eventClick: function(info) {
                    const rental = info.event.extendedProps.rental;
                    showRentalDetails(rental);
                },
                eventDisplay: 'block',
                height: 'auto'
            });
            
            calendar.render();
        });

        function showRentalDetails(rental) {
            const modalBody = document.getElementById('rentalModalBody');
            
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
                        <p><span class="badge badge-${getBadgeClass(rental.estado)}">${estados[rental.estado] || rental.estado}</span></p>
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

        document.getElementById('categoria_id').addEventListener('change', function() {
            const categoriaId = this.value;
            if (categoriaId) {
                const form = document.getElementById('filtersForm');
                form.submit();
            }
        });
    </script>
</body>
</html>

