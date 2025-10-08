<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';
require_once 'includes/security.php';
Security::setSecurityHeaders();

$auth = new Auth();
$product = new Product();

$featured_products = $product->getFeaturedProducts(8);
$categories = $product->getCategories();
$maquinaria_products = $product->getProducts(['tipo' => 'maquinaria', 'limit' => 7]);
$materiales_products = $product->getProducts(['tipo' => 'material', 'limit' => 4]);
$current_user = $auth->getCurrentUser();

$success_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'product_created':
            $success_message = 'Producto creado exitosamente';
            break;
        case 'product_updated':
            $success_message = 'Producto actualizado exitosamente';
            break;
        case 'registration_complete':
            $success_message = '¡Registro completado exitosamente! Ya puedes iniciar sesión.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title><?php echo Config::SITE_NAME; ?> - Venta y Alquiler de Maquinaria</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Venta y alquiler de maquinaria pesada y materiales pétreos" name="keywords">
    <meta content="Sistema de venta y alquiler de maquinaria pesada y materiales pétreos" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    
    <!-- Estilos personalizados para las tarjetas de productos -->
    <style>
        .product-item {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-description {
            color: #6c757d;
            font-size: 0.85rem;
            line-height: 1.4;
            margin: 8px 0;
            min-height: 2.4rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .stock-badge {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .stock-badge i {
            font-size: 0.7rem;
        }
        
        .product-item .text-center {
            padding: 1.5rem 1rem;
        }
        
        .product-item .h6 {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .product-item .h5 {
            color: #007bff;
            font-weight: 700;
        }
        
        
        .product-action {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: center;
        }
        
        .product-action .btn-square {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
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
                            <?php echo $current_user ? $current_user['nombre'] . ' ' . $current_user['apellido'] : 'Mi Cuenta'; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?php if ($current_user): ?>
                                <a class="dropdown-item" href="profile.php">Mi Perfil</a>
                                <a class="dropdown-item" href="my-orders.php">Mis Pedidos</a>
                                <a class="dropdown-item" href="my-rentals.php">Mis Alquileres</a>
                                <?php if ($current_user['tipo_usuario'] === 'admin'): ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-primary" href="admin.php">
                                        <i class="fas fa-tools me-2"></i>Panel de Administración
                                    </a>
                                    <a class="dropdown-item text-primary" href="admin-products.php">
                                        <i class="fas fa-list me-2"></i>Gestionar Productos
                                    </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">Cerrar Sesión</a>
                            <?php else: ?>
                                <a class="dropdown-item" href="login.php">Iniciar Sesión</a>
                                <a class="dropdown-item" href="register.php">Registrarse</a>
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
                        <span class="badge text-dark border border-dark rounded-circle" style="padding-bottom: 2px;">0</span>
                    </a>
                    <a href="cart.php" class="btn px-0 ml-2">
                        <i class="fas fa-shopping-cart text-dark"></i>
                        <span class="badge text-dark border border-dark rounded-circle" id="cart-count" style="padding-bottom: 2px;">0</span>
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
                <form action="search.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" placeholder="Buscar productos">
                        <div class="input-group-append">
                            <span class="input-group-text bg-transparent text-primary">
                                <i class="fa fa-search"></i>
                            </span>
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
                    href="#navbar-vertical" style="height: 65px; padding: 0 30px;">
                    <h6 class="text-dark m-0"><i class="fa fa-bars mr-2"></i>Maquinarias y Materiales Pétreos</h6>
                    <i class="fa fa-angle-down text-dark"></i>
                </a>
                <nav class="collapse position-absolute navbar navbar-vertical navbar-light align-items-start p-0 bg-light"
                    id="navbar-vertical" style="width: calc(100% - 30px); z-index: 999;">
                    <div class="navbar-nav w-100">
                        <div class="nav-item dropdown dropright">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Materiales Pétreos <i
                                    class="fa fa-angle-right float-right mt-1"></i></a>
                            <div class="dropdown-menu position-absolute rounded-0 border-0 m-0">
                                <?php foreach ($categories as $category): ?>
                                    <?php if ($category['tipo'] == 'material'): ?>
                                        <a href="index.php?categoria=<?php echo $category['id']; ?>" class="dropdown-item"><?php echo $category['nombre']; ?></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php foreach ($categories as $category): ?>
                            <?php if ($category['tipo'] == 'maquinaria'): ?>
                                <a href="alquiler.php?categoria=<?php echo $category['id']; ?>" class="nav-item nav-link"><?php echo $category['nombre']; ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
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
                            <a href="index.php" class="nav-item nav-link active">Inicio</a>
                            <a href="index.php" class="nav-item nav-link">Venta</a>
                            <a href="alquiler.php" class="nav-item nav-link">Alquiler</a>
                            <a href="contact.php" class="nav-item nav-link">Contáctanos</a>
                            <?php if ($current_user && $current_user['tipo_usuario'] === 'admin'): ?>
                                <a href="admin.php" class="nav-item nav-link text-warning">
                                    <i class="fas fa-tools me-1"></i>Admin
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="navbar-nav ml-auto py-0 d-none d-lg-block">
                            <a href="favorites.php" class="btn px-0">
                                <i class="fas fa-heart text-primary"></i>
                                <span class="badge text-secondary border border-secondary rounded-circle"
                                    style="padding-bottom: 2px;">0</span>
                            </a>
                            <a href="cart.php" class="btn px-0 ml-3">
                                <i class="fas fa-shopping-cart text-primary"></i>
                                <span class="badge text-secondary border border-secondary rounded-circle" id="cart-count-header"
                                    style="padding-bottom: 2px;">0</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Carousel Start -->
    <div class="container-fluid mb-3">
        <div class="row px-xl-5">
            <div class="col-lg-8">
                <div id="header-carousel" class="carousel slide carousel-fade mb-30 mb-lg-0" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#header-carousel" data-slide-to="0" class="active"></li>
                        <li data-target="#header-carousel" data-slide-to="1"></li>
                    </ol>
                    <div class="carousel-inner">
                        <div class="carousel-item position-relative active" style="height: 430px;">
                            <img class="position-absolute w-100 h-100" src="img/carousel-1.jpg"
                                style="object-fit: cover;">
                            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3" style="max-width: 700px;">
                                    <h1 class="display-4 text-white mb-3 animate__animated animate__fadeInDown">Alquiler
                                        De Maquinaria</h1>
                                    <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Alquiler de Maquinaria
                                        Pesada y Venta de Materiales Pétreos</p>
                                    <a class="btn btn-outline-light py-2 px-4 mt-3 animate__animated animate__fadeInUp"
                                        href="alquiler.php">Ver</a>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item position-relative" style="height: 430px;">
                            <img class="position-absolute w-100 h-100" src="img/carousel-2.jpg"
                                style="object-fit: cover;">
                            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3" style="max-width: 700px;">
                                    <h1 class="display-4 text-white mb-3 animate__animated animate__fadeInDown">Venta de
                                        Materiales Pétreos</h1>
                                    <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Materiales de alta calidad para construcción</p>
                                    <a class="btn btn-outline-light py-2 px-4 mt-3 animate__animated animate__fadeInUp"
                                        href="index.php">Ver</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="product-offer mb-30" style="height: 200px;">
                    <img class="img-fluid" src="img/offer-1.jpg" alt="">
                    <div class="offer-text">
                        <h3 class="text-white mb-3">Oferta Especial</h3>
                        <a href="alquiler.php" class="btn btn-primary">Alquilar</a>
                    </div>
                </div>
                <div class="product-offer mb-30" style="height: 200px;">
                    <img class="img-fluid" src="img/offer-2.jpg" alt="">
                    <div class="offer-text">
                        <h3 class="text-white mb-3">Oferta Especial</h3>
                        <a href="index.php" class="btn btn-primary">Comprar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->

    <!-- Mensaje de Éxito -->
    <?php if ($success_message): ?>
        <div class="container-fluid pt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Featured Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5 pb-3">
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-check text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">Los Mejores Servicios</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-shipping-fast text-primary m-0 mr-2"></h1>
                    <h5 class="font-weight-semi-bold m-0">Entrega Rápida</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fas fa-exchange-alt text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">Garantía</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-phone-volume text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">24/7 Soporte</h5>
                </div>
            </div>
        </div>
    </div>
    <!-- Featured End -->

    <!-- Alquiler de Maquinaria Start -->
    <div class="container-fluid pt-5 pb-3">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span
                class="bg-secondary pr-3">Alquiler de Maquinaria</span></h2>
        <div class="row px-xl-5">
            <?php if (is_array($maquinaria_products) && !empty($maquinaria_products)): ?>
                <?php foreach ($maquinaria_products as $product): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100" src="<?php echo $product['imagen_principal'] ?: 'img/product-1.jpg'; ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href="#" onclick="addToFavorites(<?php echo $product['id']; ?>)">
                                        <i class="far fa-heart"></i>
                                    </a>
                                    <a class="btn btn-outline-dark btn-square" href="product-detail.php?id=<?php echo $product['id']; ?>">
                                        <i class="fa fa-search"></i>
                                    </a>
                                    <?php if ($current_user && $current_user['tipo_usuario'] === 'admin'): ?>
                                        <a class="btn btn-outline-dark btn-square" href="edit-product.php?id=<?php echo $product['id']; ?>" title="Editar producto">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a class="btn btn-outline-danger btn-square" href="#" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['nombre']); ?>')" title="Eliminar producto">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none text-truncate" href="product-detail.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['nombre']); ?>
                                </a>
                                
                                <!-- Descripción -->
                                <?php if (!empty($product['descripcion'])): ?>
                                    <div class="product-description">
                                        <?php echo htmlspecialchars(substr($product['descripcion'], 0, 100)) . (strlen($product['descripcion']) > 100 ? '...' : ''); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Stock -->
                                <div class="mb-2">
                                    <span class="stock-badge">
                                        <i class="fas fa-box"></i>Stock: <?php echo $product['stock_disponible']; ?>
                                    </span>
                                </div>
                                
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small>(99)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">No hay productos de maquinaria disponibles en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Alquiler de Maquinaria End -->

    <!-- Venta de Materiales Pétreos Start -->
    <div class="container-fluid pt-5 pb-3">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span
                class="bg-secondary pr-3">Venta de Materiales Pétreos</span></h2>
        <div class="row px-xl-5">
            <?php if (is_array($materiales_products) && !empty($materiales_products)): ?>
                <?php foreach ($materiales_products as $product): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100" src="<?php echo $product['imagen_principal'] ?: 'img/product-1.jpg'; ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href="#" onclick="addToFavorites(<?php echo $product['id']; ?>)">
                                        <i class="far fa-heart"></i>
                                    </a>
                                    <a class="btn btn-outline-dark btn-square" href="product-detail.php?id=<?php echo $product['id']; ?>">
                                        <i class="fa fa-search"></i>
                                    </a>
                                    <?php if ($current_user && $current_user['tipo_usuario'] === 'admin'): ?>
                                        <a class="btn btn-outline-dark btn-square" href="edit-product.php?id=<?php echo $product['id']; ?>" title="Editar producto">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a class="btn btn-outline-danger btn-square" href="#" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['nombre']); ?>')" title="Eliminar producto">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none text-truncate" href="product-detail.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['nombre']); ?>
                                </a>
                                
                                <!-- Descripción -->
                                <?php if (!empty($product['descripcion'])): ?>
                                    <div class="product-description">
                                        <?php echo htmlspecialchars(substr($product['descripcion'], 0, 100)) . (strlen($product['descripcion']) > 100 ? '...' : ''); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Stock -->
                                <div class="mb-2">
                                    <span class="stock-badge">
                                        <i class="fas fa-box"></i>Stock: <?php echo $product['stock_disponible']; ?>
                                    </span>
                                </div>
                                
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small>(99)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">No hay productos de materiales disponibles en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Venta de Materiales Pétreos End -->

    <!-- Offer Start -->
    <div class="container-fluid pt-5 pb-3">
        <div class="row px-xl-5">
            <div class="col-md-6">
                <div class="product-offer mb-30" style="height: 300px;">
                    <img class="img-fluid" src="img/offer-1.jpg" alt="">
                    <div class="offer-text">
                        <h3 class="text-white mb-3">Precios Especiales</h3>
                        <a href="index.php" class="btn btn-primary">Comprar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="product-offer mb-30" style="height: 300px;">
                    <img class="img-fluid" src="img/offer-2.jpg" alt="">
                    <div class="offer-text">
                        <h3 class="text-white mb-3">Precios Especiales</h3>
                        <a href="alquiler.php" class="btn btn-primary">Alquilar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Offer End -->

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
                            <a class="text-secondary mb-2" href="index.php"><i class="fa fa-angle-right mr-2"></i>Venta</a>
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
                                <a class="text-secondary mb-2" href="register.php"><i class="fa fa-angle-right mr-2"></i>Registrarse</a>
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
    // Actualizar contador del carrito
    function updateCartCount() {
        <?php if ($current_user): ?>
        $.get('api/cart.php', function(response) {
            if (response.success) {
                $('#cart-count, #cart-count-header').text(response.total.items_count || 0);
            }
        });
        <?php endif; ?>
    }
    
    // Agregar al carrito
    function addToCart(productId, tipo) {
        <?php if ($current_user): ?>
        $.post('api/cart.php', {
            producto_id: productId,
            tipo: tipo,
            cantidad: 1
        }, function(response) {
            if (response.success) {
                alert('Producto agregado al carrito');
                updateCartCount();
            } else {
                alert('Error: ' + response.message);
            }
        });
        <?php else: ?>
        alert('Debe iniciar sesión para agregar productos al carrito');
        <?php endif; ?>
    }
    
    // Agregar a favoritos
    function addToFavorites(productId) {
        <?php if ($current_user): ?>
        // Implementar funcionalidad de favoritos
        alert('Funcionalidad de favoritos próximamente');
        <?php else: ?>
        alert('Debe iniciar sesión para agregar a favoritos');
        <?php endif; ?>
    }
    
    // Eliminar producto
    function deleteProduct(productId, productName) {
        if (confirm('¿Estás seguro de que quieres eliminar el producto "' + productName + '"?\n\nEsta acción no se puede deshacer.')) {
            console.log('Enviando petición de eliminación para producto ID:', productId);
            
            $.post('api/delete-product.php', {
                id: productId
            }, function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response && response.success) {
                    alert('Producto eliminado exitosamente');
                    location.reload();
                } else {
                    alert('Error: ' + (response ? response.message : 'Respuesta inválida del servidor'));
                }
            }).fail(function(xhr, status, error) {
                console.log('Error en la petición:', xhr.responseText);
                alert('Error al eliminar el producto: ' + xhr.responseText);
            });
        }
    }
    
    // Actualizar contador al cargar la página
    $(document).ready(function() {
        updateCartCount();
        
        $('.dropdown-menu a').on('click', function(e) {
            window.location.href = $(this).attr('href');
        });
        
        $('a[onclick*="addToFavorites"]').on('click', function(e) {
            e.preventDefault();
            var productId = $(this).attr('onclick').match(/\d+/)[0];
            addToFavorites(productId);
        });
    });
    </script>
</body>

</html>
