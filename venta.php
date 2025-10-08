<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';
require_once 'includes/security.php';
Security::setSecurityHeaders();

$auth = new Auth();
$productSrv = new Product();
$current_user = $auth->getCurrentUser();

/**
 * Filtros básicos para la tienda de venta (materiales).
 * availability: 'in_stock' (disponible), 'out_of_stock' (no disponible)
 */
$filters = [
    'tipo'       => 'material',
    'limit'      => isset($_GET['limit']) ? (int)$_GET['limit'] : 24,
    'q'          => isset($_GET['q']) ? trim($_GET['q']) : null,
    'categoria'  => isset($_GET['categoria']) ? (int)$_GET['categoria'] : null,
    'availability' => isset($_GET['availability']) ? $_GET['availability'] : null,
];

$categories = $productSrv->getCategories();
$materiales_products = $productSrv->getProducts($filters);

// Mensajes de éxito
$success_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'product_created': $success_message = 'Producto creado exitosamente'; break;
        case 'product_updated': $success_message = 'Producto actualizado exitosamente'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo Config::SITE_NAME; ?> - Venta de Materiales Pétreos</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Venta y alquiler de maquinaria pesada y materiales pétreos" name="keywords">
    <meta content="Sistema de venta y alquiler de maquinaria pesada y materiales pétreos" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">  

    <!-- Font Awesome (v5 para coincidir con la plantilla) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <!-- Estilos personalizados -->
    <style>
        .product-item {transition:.3s;border-radius:10px;overflow:hidden}
        .product-item:hover {transform:translateY(-5px);box-shadow:0 10px 25px rgba(0,0,0,.1)}
        .product-description{color:#6c757d;font-size:.85rem;line-height:1.4;margin:8px 0;min-height:2.4rem;
            display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .stock-badge{background:linear-gradient(45deg,#17a2b8,#138496);color:#fff;padding:4px 8px;border-radius:15px;
            font-size:.75rem;font-weight:500;display:inline-flex;align-items:center;gap:6px}
        .product-action{display:flex;flex-wrap:wrap;gap:6px;justify-content:center}
        .btn-square{width:40px;height:40px;display:flex;align-items:center;justify-content:center}
        .breadcrumb a { text-decoration: none; }
        .filter-label { font-weight: 600; }
    </style>
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid">
        <div class="row bg-secondary py-1 px-xl-5">
            <div class="col-lg-6 d-none d-lg-block">
                <div class="d-inline-flex align-items-center h-100">
                    <a class="text-body mr-3" href="about.php">Acerca de</a>
                    <a class="text-body mr-3" href="contact.php">Contáctanos</a>
                    <a class="text-body mr-3" href="help.php">Ayuda</a>
                    <a class="text-body mr-3" href="faq.php">FAQs</a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                            <?php echo $current_user ? htmlspecialchars($current_user['nombre'].' '.$current_user['apellido']) : 'Mi Cuenta'; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?php if ($current_user): ?>
                                <a class="dropdown-item" href="profile.php">Mi Perfil</a>
                                <a class="dropdown-item" href="my-orders.php">Mis Pedidos</a>
                                <a class="dropdown-item" href="my-rentals.php">Mis Alquileres</a>
                                <?php if ($current_user['tipo_usuario'] === 'admin'): ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-primary" href="admin.php"><i class="fas fa-tools mr-2"></i>Panel de Administración</a>
                                    <a class="dropdown-item text-primary" href="admin-products.php"><i class="fas fa-list mr-2"></i>Gestionar Productos</a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">Cerrar Sesión</a>
                            <?php else: ?>
                                <a class="dropdown-item" href="login.php">Iniciar Sesión</a>
                                <a class="dropdown-item" href="login.php">Registrarse</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="btn-group mx-2">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">USD</button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <button class="dropdown-item" type="button">EUR</button>
                            <button class="dropdown-item" type="button">GBP</button>
                            <button class="dropdown-item" type="button">CAD</button>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">ES</button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <button class="dropdown-item" type="button">EN</button>
                            <button class="dropdown-item" type="button">FR</button>
                            <button class="dropdown-item" type="button">PT</button>
                        </div>
                    </div>
                </div>
                <div class="d-inline-flex align-items-center d-block d-lg-none">
                    <a href="favorites.php" class="btn px-0 ml-2">
                        <i class="fas fa-heart text-dark"></i>
                        <span class="badge text-dark border border-dark rounded-circle" style="padding-bottom:2px;">0</span>
                    </a>
                    <a href="cart.php" class="btn px-0 ml-2">
                        <i class="fas fa-shopping-cart text-dark"></i>
                        <span class="badge text-dark border border-dark rounded-circle" id="cart-count" style="padding-bottom:2px;">0</span>
                    </a>
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
                <form action="shop.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>" placeholder="Buscar productos">
                        <div class="input-group-append">
                            <button class="input-group-text bg-transparent text-primary" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
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
            <div class="col-lg-3 d-none d-lg-block">
                <a class="btn d-flex align-items-center justify-content-between bg-primary w-100" data-toggle="collapse"
                   href="#navbar-vertical" style="height:65px;padding:0 30px;">
                    <h6 class="text-dark m-0"><i class="fa fa-bars mr-2"></i>Maquinarias y Materiales Pétreos</h6>
                    <i class="fa fa-angle-down text-dark"></i>
                </a>
                <nav class="collapse position-absolute navbar navbar-vertical navbar-light align-items-start p-0 bg-light"
                     id="navbar-vertical" style="width:calc(100% - 30px); z-index:999;">
                    <div class="navbar-nav w-100">
                        <div class="nav-item dropdown dropright">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Materiales Pétreos
                                <i class="fa fa-angle-right float-right mt-1"></i></a>
                            <div class="dropdown-menu position-absolute rounded-0 border-0 m-0">
                                <?php foreach ($categories as $cat): if ($cat['tipo']==='material'): ?>
                                    <a href="shop.php?categoria=<?php echo (int)$cat['id']; ?>" class="dropdown-item">
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </a>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                        <?php foreach ($categories as $cat): if ($cat['tipo']==='maquinaria'): ?>
                            <a href="alquiler.php?categoria=<?php echo (int)$cat['id']; ?>" class="nav-item nav-link">
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </a>
                        <?php endif; endforeach; ?>
                    </div>
                </nav>
            </div>

            <div class="col-lg-9">
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
                            <a href="shop.php" class="nav-item nav-link active">Venta</a>
                            <a href="alquiler.php" class="nav-item nav-link">Alquiler</a>
                            <a href="contact.php" class="nav-item nav-link">Contáctanos</a>
                            <?php if ($current_user && $current_user['tipo_usuario']==='admin'): ?>
                                <a href="admin.php" class="nav-item nav-link text-warning"><i class="fas fa-tools mr-1"></i>Admin</a>
                            <?php endif; ?>
                        </div>
                        <div class="navbar-nav ml-auto py-0 d-none d-lg-block">
                            <a href="favorites.php" class="btn px-0">
                                <i class="fas fa-heart text-primary"></i>
                                <span class="badge text-secondary border border-secondary rounded-circle" style="padding-bottom:2px;">0</span>
                            </a>
                            <a href="cart.php" class="btn px-0 ml-3">
                                <i class="fas fa-shopping-cart text-primary"></i>
                                <span class="badge text-secondary border border-secondary rounded-circle" id="cart-count-header" style="padding-bottom:2px;">0</span>
                            </a>
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
                    <a class="breadcrumb-item text-dark" href="shop.php">Venta</a>
                    <span class="breadcrumb-item active">Materiales Pétreos</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Mensaje de Éxito -->
    <?php if ($success_message): ?>
        <div class="container-fluid pt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Shop Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <!-- Shop Sidebar Start -->
            <div class="col-lg-3 col-md-4">
                <!-- Único filtro: Disponibilidad -->
                <h5 class="section-title position-relative text-uppercase mb-3">
                    <span class="bg-secondary pr-3">Disponibilidad</span>
                </h5>
                <div class="bg-light p-4 mb-30">
                    <form action="shop.php" method="GET">
                        <?php if (!empty($filters['q'])): ?>
                            <input type="hidden" name="q" value="<?php echo htmlspecialchars($filters['q']); ?>">
                        <?php endif; ?>
                        <?php if (!empty($filters['categoria'])): ?>
                            <input type="hidden" name="categoria" value="<?php echo (int)$filters['categoria']; ?>">
                        <?php endif; ?>

                        <?php
                        $availability = $filters['availability'] ?? '';
                        ?>
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" id="av-all" name="availability" value="" <?php echo $availability===''?'checked':''; ?>>
                            <label class="custom-control-label" for="av-all">Todos</label>
                            <span class="badge border font-weight-normal">—</span>
                        </div>
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" id="av-in" name="availability" value="in_stock" <?php echo $availability==='in_stock'?'checked':''; ?>>
                            <label class="custom-control-label" for="av-in">Disponible</label>
                            <span class="badge border font-weight-normal">—</span>
                        </div>
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" id="av-out" name="availability" value="out_of_stock" <?php echo $availability==='out_of_stock'?'checked':''; ?>>
                            <label class="custom-control-label" for="av-out">No disponible</label>
                            <span class="badge border font-weight-normal">—</span>
                        </div>

                        <button class="btn btn-primary btn-block" type="submit">
                            <i class="fa fa-filter mr-1"></i>Aplicar
                        </button>
                    </form>
                </div>
            </div>
            <!-- Shop Sidebar End -->

            <!-- Shop Product Start -->
            <div class="col-lg-9 col-md-8">
                <div class="row pb-3">
                    <div class="col-12 pb-1">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <button class="btn btn-sm btn-light" title="Cuadrícula"><i class="fa fa-th-large"></i></button>
                                <button class="btn btn-sm btn-light ml-2" title="Lista"><i class="fa fa-bars"></i></button>
                            </div>
                            <div class="ml-2">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">Mostrar</button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="?limit=12">12</a>
                                        <a class="dropdown-item" href="?limit=24">24</a>
                                        <a class="dropdown-item" href="?limit=48">48</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (is_array($materiales_products) && !empty($materiales_products)): ?>
                        <?php foreach ($materiales_products as $p): ?>
                            <div class="col-lg-4 col-md-6 col-sm-6 pb-1">
                                <div class="product-item bg-light mb-4">
                                    <div class="product-img position-relative overflow-hidden">
                                        <img class="img-fluid w-100"
                                             src="<?php echo !empty($p['imagen_principal']) ? htmlspecialchars($p['imagen_principal']) : 'img/product-1.jpg'; ?>"
                                             alt="<?php echo htmlspecialchars($p['nombre'] ?? 'Producto'); ?>">
                                        <div class="product-action">
                                            <a class="btn btn-outline-dark btn-square" href="javascript:void(0)" onclick="addToCart(<?php echo (int)$p['id']; ?>, 'material')">
                                                <i class="fa fa-shopping-cart"></i>
                                            </a>
                                            <a class="btn btn-outline-dark btn-square" href="javascript:void(0)" onclick="addToFavorites(<?php echo (int)$p['id']; ?>)">
                                                <i class="far fa-heart"></i>
                                            </a>
                                            <a class="btn btn-outline-dark btn-square" href="product-detail.php?id=<?php echo (int)$p['id']; ?>">
                                                <i class="fa fa-search"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="text-center py-4">
                                        <a class="h6 text-decoration-none text-truncate" href="product-detail.php?id=<?php echo (int)$p['id']; ?>">
                                            <?php echo htmlspecialchars($p['nombre'] ?? 'Producto'); ?>
                                        </a>

                                        <?php if (!empty($p['descripcion'])): ?>
                                            <div class="product-description">
                                                <?php
                                                $desc = strip_tags($p['descripcion']);
                                                echo htmlspecialchars(mb_strimwidth($desc, 0, 110, '...'));
                                                ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex align-items-center justify-content-center mt-2">
                                            <?php if (isset($p['precio'])): ?>
                                                <h5>$<?php echo number_format((float)$p['precio'], 2); ?></h5>
                                            <?php else: ?>
                                                <h6 class="text-muted">Precio a consultar</h6>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-2">
                                            <span class="stock-badge">
                                                <i class="fas fa-box"></i>
                                                Stock: <?php echo (int)($p['stock_disponible'] ?? 0); ?>
                                            </span>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-center mb-1">
                                            <small class="fa fa-star text-primary mr-1"></small>
                                            <small class="fa fa-star text-primary mr-1"></small>
                                            <small class="fa fa-star text-primary mr-1"></small>
                                            <small class="fa fa-star text-primary mr-1"></small>
                                            <small class="fa fa-star text-primary mr-1"></small>
                                            <small>(<?php echo (int)($p['reviews_count'] ?? 0); ?>)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-center mb-4">No hay productos disponibles con los criterios seleccionados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Shop Product End -->
        </div>
    </div>
    <!-- Shop End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-secondary mt-5 pt-5">
        <div class="row px-xl-5 pt-5">
            <div class="col-lg-4 col-md-12 mb-5 pr-3 pr-xl-5">
                <h5 class="text-secondary text-uppercase mb-4">Contáctanos</h5>
                <p class="mb-4">Somos especialistas en venta y alquiler de maquinaria pesada y materiales pétreos de alta calidad.</p>
                <p class="mb-2"><i class="fa fa-map-marker-alt text-primary mr-3"></i>123 Street, New York, USA</p>
                <p class="mb-2"><i class="fa fa-envelope text-primary mr-3"></i>info@alquivent.com</p>
                <p class="mb-0"><i class="fa fa-phone-alt text-primary mr-3"></i>+012 345 67890</p>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="row">
                    <div class="col-md-4 mb-5">
                        <h5 class="text-secondary text-uppercase mb-4">Navegación</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-secondary mb-2" href="index.php"><i class="fa fa-angle-right mr-2"></i>Inicio</a>
                            <a class="text-secondary mb-2" href="shop.php"><i class="fa fa-angle-right mr-2"></i>Venta</a>
                            <a class="text-secondary mb-2" href="alquiler.php"><i class="fa fa-angle-right mr-2"></i>Alquiler</a>
                            <a class="text-secondary mb-2" href="cart.php"><i class="fa fa-angle-right mr-2"></i>Carrito</a>
                            <a class="text-secondary mb-2" href="contact.php"><i class="fa fa-angle-right mr-2"></i>Contacto</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <h5 class="text-secondary text-uppercase mb-4">Mi Cuenta</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <?php if ($current_user): ?>
                                <a class="text-secondary mb-2" href="profile.php"><i class="fa fa-angle-right mr-2"></i>Mi Perfil</a>
                                <a class="text-secondary mb-2" href="my-orders.php"><i class="fa fa-angle-right mr-2"></i>Mis Pedidos</a>
                                <a class="text-secondary mb-2" href="my-rentals.php"><i class="fa fa-angle-right mr-2"></i>Mis Alquileres</a>
                            <?php else: ?>
                                <a class="text-secondary mb-2" href="login.php"><i class="fa fa-angle-right mr-2"></i>Iniciar Sesión</a>
                                <a class="text-secondary mb-2" href="login.php"><i class="fa fa-angle-right mr-2"></i>Registrarse</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <h5 class="text-secondary text-uppercase mb-4">Newsletter</h5>
                        <p>Suscríbete para recibir ofertas especiales</p>
                        <form action="newsletter.php" method="POST">
                            <div class="input-group">
                                <input type="email" class="form-control" name="email" placeholder="Tu Email" required>
                                <div class="input-group-append">
                                    <button class="btn btn-primary">Suscribirse</button>
                                </div>
                            </div>
                        </form>
                        <h6 class="text-secondary text-uppercase mt-4 mb-3">Síguenos</h6>
                        <div class="d-flex">
                            <a class="btn btn-primary btn-square mr-2" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-primary btn-square mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-primary btn-square mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-primary btn-square" href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row border-top mx-xl-5 py-4" style="border-color: rgba(256, 256, 256, .1) !important;">
            <div class="col-md-6 px-xl-0">
                <p class="mb-md-0 text-center text-md-left text-secondary">
                    &copy; <a class="text-primary" href="#"><?php echo Config::SITE_NAME; ?></a>. Todos los derechos reservados.
                </p>
            </div>
            <div class="col-md-6 px-xl-0 text-center text-md-right">
                <img class="img-fluid" src="img/payments.png" alt="">
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
    // Actualizar contador del carrito (si hay usuario)
    function updateCartCount() {
        <?php if ($current_user): ?>
        $.get('api/cart.php', function(response) {
            if (response && response.success && response.total) {
                $('#cart-count, #cart-count-header').text(response.total.items_count || 0);
            }
        });
        <?php endif; ?>
    }

    // Agregar al carrito
    function addToCart(productId, tipo) {
        <?php if ($current_user): ?>
        $.post('api/cart.php', { producto_id: productId, tipo: tipo, cantidad: 1 }, function(response) {
            if (response && response.success) {
                updateCartCount();
                alert('Producto agregado al carrito');
            } else {
                alert('Error: ' + (response && response.message ? response.message : 'No se pudo agregar.'));
            }
        }, 'json');
        <?php else: ?>
        alert('Debe iniciar sesión para agregar productos al carrito');
        <?php endif; ?>
    }

    // Agregar a favoritos (placeholder)
    function addToFavorites(productId) {
        <?php if ($current_user): ?>
        alert('Funcionalidad de favoritos próximamente');
        <?php else: ?>
        alert('Debe iniciar sesión para agregar a favoritos');
        <?php endif; ?>
    }

    $(document).ready(function(){ updateCartCount(); });
    </script>
</body>
</html>