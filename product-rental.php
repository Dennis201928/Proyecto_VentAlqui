<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';
require_once 'includes/security.php';
Security::setSecurityHeaders();

$auth = new Auth();
$productSrv = new Product();
$current_user = $auth->getCurrentUser();

if (!$current_user) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header('Location: alquiler.php');
    exit();
}

$product = $productSrv->getProductById($product_id);
if (!$product || isset($product['error'])) {
    header('Location: alquiler.php');
    exit();
}


$es_maquinaria = isset($product['categoria_tipo']) && $product['categoria_tipo'] === 'maquinaria';
if (!$es_maquinaria && (empty($product['precio_alquiler_dia']) || $product['precio_alquiler_dia'] <= 0)) {
    header('Location: alquiler.php?error=producto_no_alquilable');
    exit();
}

if (empty($product['precio_alquiler_dia']) || $product['precio_alquiler_dia'] <= 0) {
    $product['precio_alquiler_dia'] = 0; // Se calculará después o se mostrará "consulta"
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo Config::SITE_NAME; ?> - Agendar Alquiler</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Agendar alquiler de maquinaria" name="keywords">
    <meta content="Selecciona las fechas para alquilar el producto" name="description">

    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">  
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">

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
        .alert-custom {
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid">
        <div class="row bg-secondary py-1 px-xl-5">
            <div class="col-lg-6 d-none d-lg-block">
                <div class="d-inline-flex align-items-center h-100">
                    <a class="text-body mr-3" href="quienes_somos.php">Acerca de</a>
                    <a class="text-body mr-3" href="contact.php">Contáctanos</a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                            <?php echo htmlspecialchars($current_user['nombre'].' '.$current_user['apellido']); ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="profile.php">Mi Perfil</a>
                            <a class="dropdown-item" href="my-orders.php">Mis Pedidos</a>
                            <a class="dropdown-item" href="my-rentals.php">Mis Alquileres</a>
                            <?php if ($current_user['tipo_usuario'] === 'admin'): ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-primary" href="admin.php"><i class="fas fa-tools mr-2"></i>Panel de Administración</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
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
            <div class="col-lg-4 col-6 text-left">
                <form action="alquiler.php" method="GET">
                </form>
            </div>
            <div class="col-lg-4 col-6 text-right">
                <p class="m-0">Contáctanos</p>
                <h5 class="m-0">+012 345 6789</h5>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar Start -->
    <div class="container-fluid bg-dark mb-30">
        <div class="row px-xl-5">
            <div class="col-12 px-0">
                <nav class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-0">
                    <a href="index.php" class="text-decoration-none d-block d-lg-none">
                        <span class="h1 text-uppercase text-dark bg-light px-2">Alqui</span>
                        <span class="h1 text-uppercase text-light bg-primary px-2 ml-n1">Venta</span>
                    </a>
                    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                        <div class="navbar-nav mr-auto py-0">
                            <a href="index.php" class="nav-item nav-link">Inicio</a>
                            <a href="venta.php" class="nav-item nav-link">Venta</a>
                            <a href="alquiler.php" class="nav-item nav-link active">Alquiler</a>
                            <a href="quienes_somos.php" class="nav-item nav-link">Quiénes Somos</a>
                            <a href="contact.php" class="nav-item nav-link">Contáctanos</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="index.php">Inicio</a>
                    <a class="breadcrumb-item text-dark" href="alquiler.php">Alquiler</a>
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
                            <li>Las fechas marcadas en <span style="background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 4px;">rojo</span> están ocupadas y no puedes seleccionarlas</li>
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
                        <img src="<?php echo htmlspecialchars($product['imagen_principal']); ?>" 
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
                        <h4 class="mb-0">
                            <span>Total:</span>
                            <span class="float-right" id="total-calc">$0.00</span>
                        </h4>
                    </div>
                    <button class="btn btn-light btn-block btn-lg" id="btn-reservar" onclick="confirmarReserva()">
                        <i class="fas fa-check mr-2"></i>Confirmar Reserva
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Content End -->

    <!-- Footer -->
    <div class="container-fluid bg-dark text-secondary mt-5 pt-5">
        <div class="row px-xl-5 pt-5">
            <div class="col-lg-4 col-md-12 mb-5 pr-3 pr-xl-5">
                <h5 class="text-secondary text-uppercase mb-4">Contáctanos</h5>
                <p class="mb-4">Somos especialistas en venta y alquiler de maquinaria pesada y materiales pétreos de alta calidad.</p>
                <p class="mb-2"><i class="fa fa-map-marker-alt text-primary mr-3"></i>123 Street, New York, USA</p>
                <p class="mb-2"><i class="fa fa-envelope text-primary mr-3"></i>info@alquivent.com</p>
                <p class="mb-0"><i class="fa fa-phone-alt text-primary mr-3"></i>+012 345 67890</p>
            </div>
        </div>
        <div class="row border-top mx-xl-5 py-4" style="border-color: rgba(256, 256, 256, .1) !important;">
            <div class="col-md-6 px-xl-0">
                <p class="mb-md-0 text-center text-md-left text-secondary">
                    &copy; <a class="text-primary" href="#"><?php echo Config::SITE_NAME; ?></a>. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/es.js"></script>

    <script>
        let selectedStart = null;
        let selectedEnd = null;
        const productId = <?php echo $product_id; ?>;
        const precioDia = <?php echo (float)($product['precio_alquiler_dia'] ?? 0); ?>;
        const bookedDates = [];
        
        <?php if (empty($product['precio_alquiler_dia']) || $product['precio_alquiler_dia'] <= 0): ?>
        // Mostrar alerta si no tiene precio configurado
        document.addEventListener('DOMContentLoaded', function() {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning';
            alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i><strong>Atención:</strong> Este producto no tiene precio de alquiler configurado. Por favor, contacta con nosotros para más información.';
            document.querySelector('.calendar-container').insertBefore(alertDiv, document.getElementById('calendar'));
        });
        <?php endif; ?>

        // Inicializar calendario
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
                    start: new Date().toISOString().split('T')[0] // Solo permitir fechas futuras
                },
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                firstDay: 1, // Lunes
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch(`api/rental-dates.php?producto_id=${productId}&fecha_desde=${fetchInfo.startStr}&fecha_hasta=${fetchInfo.endStr}`)
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
                                    if (data.events && Array.isArray(data.events)) {
                                        data.events.forEach(event => {
                                            if (event.start && event.end) {
                                                bookedDates.push({
                                                    start: event.start,
                                                    end: event.end
                                                });
                                            }
                                        });
                                    }
                                    successCallback(data.events || []);
                                } else {
                                    console.warn('API response:', data.message || 'Error desconocido');
                                    successCallback([]);
                                }
                            } catch (e) {
                                console.error('Error parsing JSON:', e, 'Response:', text);
                                successCallback([]);
                            }
                        })
                        .catch(error => {
                            console.error('Error loading dates:', error);
                            successCallback([]);
                        });
                },
                select: function(selectInfo) {
                    const startDate = selectInfo.startStr;
                    const endDate = selectInfo.endStr;
                    
                    // Verificar que no esté en fechas ocupadas
                    const isBooked = bookedDates.some(booking => {
                        const bookingStart = new Date(booking.start);
                        const bookingEnd = new Date(booking.end);
                        const selectedStart = new Date(startDate);
                        const selectedEnd = new Date(endDate);
                        
                        return (selectedStart <= bookingEnd && selectedEnd >= bookingStart);
                    });
                    
                    if (isBooked) {
                        alert('Las fechas seleccionadas están ocupadas. Por favor, selecciona otras fechas.');
                        calendar.unselect();
                        return;
                    }
                    
                    // Verificar que la fecha de inicio no sea anterior a hoy
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
                    
                    // Verificar si está ocupada
                    const isBooked = bookedDates.some(booking => {
                        const bookingStart = new Date(booking.start);
                        const bookingEnd = new Date(booking.end);
                        const clicked = new Date(clickedDate);
                        
                        return (clicked >= bookingStart && clicked <= bookingEnd);
                    });
                    
                    if (isBooked) {
                        alert('Esta fecha está ocupada.');
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
                        
                        // Verificar disponibilidad del rango
                        const isBooked = bookedDates.some(booking => {
                            const bookingStart = new Date(booking.start);
                            const bookingEnd = new Date(booking.end);
                            const rangeStart = new Date(selectedStart);
                            const rangeEnd = new Date(selectedEnd);
                            
                            return (rangeStart <= bookingEnd && rangeEnd >= bookingStart);
                        });
                        
                        if (isBooked) {
                            alert('El rango seleccionado incluye fechas ocupadas.');
                            selectedStart = clickedDate;
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
            
            // Calcular días
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            // Actualizar display
            document.getElementById('fecha-inicio-display').textContent = formatDate(startDate);
            document.getElementById('fecha-fin-display').textContent = formatDate(endDate);
            document.getElementById('dias-display').textContent = diffDays + ' día(s)';
            
            // Mostrar sección de fechas seleccionadas
            document.getElementById('selected-dates').style.display = 'block';
            
            // Actualizar calculadora de precio
            document.getElementById('dias-calc').textContent = diffDays;
            
            if (precioDia > 0) {
                const total = precioDia * diffDays;
                document.getElementById('total-calc').textContent = '$' + total.toFixed(2);
            } else {
                document.getElementById('total-calc').textContent = 'Consultar';
            }
            
            // Mostrar calculadora
            document.getElementById('price-calculator').style.display = 'block';
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
            
            // Crear alquiler para la renta del usuario :)
            fetch('api/rentals.php', {
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
                    window.location.href = 'my-rentals.php';
                } else {
                    alert('Error: ' + (data.message || 'No se pudo crear la reserva'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear la reserva. Por favor, intenta nuevamente.');
            });
        }
    </script>
</body>
</html>

