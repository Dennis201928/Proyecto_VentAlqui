<?php
require_once 'includes/auth.php';
require_once 'includes/rental.php';
require_once 'includes/security.php';
Security::setSecurityHeaders();

$auth = new Auth();
$rentalSrv = new Rental();
$current_user = $auth->getCurrentUser();

if (!$current_user) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener alquileres del usuario
$estado = isset($_GET['estado']) ? $_GET['estado'] : null;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $current_user['id'];
$rentals = $rentalSrv->getUserRentals($user_id, $estado);
if (isset($rentals['error'])) {
    $rentals = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo Config::SITE_NAME; ?> - Mis Alquileres</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Mis alquileres" name="keywords">
    <meta content="Visualiza tus alquileres" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">  

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        .rental-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid">
        <div class="row bg-secondary py-1 px-xl-5">
            <div class="col-lg-6 d-none d-lg-block">
                <div class="d-inline-flex align-items-center h-100">
                    <a class="text-body mr-3" href="index.php">Inicio</a>
                    <a class="text-body mr-3" href="alquiler.php">Alquiler</a>
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
                            <a class="dropdown-item active" href="my-rentals.php">Mis Alquileres</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
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
                            <a href="alquiler.php" class="nav-item nav-link">Alquiler</a>
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
                    <span class="breadcrumb-item active">Mis Alquileres</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Content Start -->
    <div class="container-fluid py-5">
        <div class="row px-xl-5">
            <div class="col-12">
                <h2 class="section-title position-relative text-uppercase mb-4">
                    <span class="bg-secondary pr-3">Mis Alquileres</span>
                </h2>

                <!-- Filtros -->
                <div class="mb-4">
                    <a href="my-rentals.php" class="btn btn-sm <?php echo !$estado ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        Todos
                    </a>
                    <a href="my-rentals.php?estado=pendiente" class="btn btn-sm <?php echo $estado == 'pendiente' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                        Pendientes
                    </a>
                    <a href="my-rentals.php?estado=confirmado" class="btn btn-sm <?php echo $estado == 'confirmado' ? 'btn-success' : 'btn-outline-success'; ?>">
                        Confirmados
                    </a>
                    <a href="my-rentals.php?estado=en_curso" class="btn btn-sm <?php echo $estado == 'en_curso' ? 'btn-info' : 'btn-outline-info'; ?>">
                        En Curso
                    </a>
                    <a href="my-rentals.php?estado=finalizado" class="btn btn-sm <?php echo $estado == 'finalizado' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                        Finalizados
                    </a>
                </div>

                <!-- Lista de Alquileres -->
                <?php if (empty($rentals)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>No tienes alquileres registrados.
                        <a href="alquiler.php" class="btn btn-primary btn-sm ml-2">Ver Productos</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($rentals as $rental): ?>
                            <div class="col-md-6 mb-4">
                                <div class="rental-card bg-light">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($rental['producto_nombre']); ?></h5>
                                            <p class="text-muted mb-0">
                                                <small><?php echo htmlspecialchars($rental['categoria_nombre'] ?? 'N/A'); ?></small>
                                            </p>
                                        </div>
                                        <?php
                                        $estados = [
                                            'pendiente' => ['class' => 'warning', 'text' => 'Pendiente'],
                                            'confirmado' => ['class' => 'success', 'text' => 'Confirmado'],
                                            'en_curso' => ['class' => 'info', 'text' => 'En Curso'],
                                            'finalizado' => ['class' => 'secondary', 'text' => 'Finalizado'],
                                            'cancelado' => ['class' => 'danger', 'text' => 'Cancelado']
                                        ];
                                        $estadoInfo = $estados[$rental['estado']] ?? ['class' => 'secondary', 'text' => $rental['estado']];
                                        ?>
                                        <span class="badge status-badge badge-<?php echo $estadoInfo['class']; ?>">
                                            <?php echo $estadoInfo['text']; ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($rental['imagen_principal'])): ?>
                                        <img src="<?php echo htmlspecialchars($rental['imagen_principal']); ?>" 
                                             alt="<?php echo htmlspecialchars($rental['producto_nombre']); ?>" 
                                             class="img-fluid rounded mb-3" style="max-height: 150px; width: 100%; object-fit: cover;">
                                    <?php endif; ?>

                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <strong>Fecha Inicio:</strong><br>
                                            <span class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($rental['fecha_inicio'])); ?>
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Fecha Fin:</strong><br>
                                            <span class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($rental['fecha_fin'])); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <strong>Precio por día:</strong><br>
                                            <span class="text-muted">$<?php echo number_format((float)$rental['precio_dia'], 2); ?></span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Total:</strong><br>
                                            <span class="h5 text-primary mb-0">$<?php echo number_format((float)$rental['total'], 2); ?></span>
                                        </div>
                                    </div>

                                    <?php if (!empty($rental['observaciones'])): ?>
                                        <div class="mb-2">
                                            <strong>Observaciones:</strong><br>
                                            <span class="text-muted"><?php echo htmlspecialchars($rental['observaciones']); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-3">
                                        <small class="text-muted">
                                            Creado: <?php echo date('d/m/Y H:i', strtotime($rental['fecha_creacion'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>

