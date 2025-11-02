<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';
require_once 'includes/security.php';
Security::setSecurityHeaders();

$auth = new Auth();
$productSrv = new Product();
$current_user = $auth->getCurrentUser();

$filters = [
    'tipo'         => 'maquinaria',
    'limit'        => isset($_GET['limit']) ? (int)$_GET['limit'] : 24,
    'search'       => isset($_GET['q']) ? trim($_GET['q']) : null,
    'categoria_id' => isset($_GET['categoria']) ? (int)$_GET['categoria'] : null,
    'estado'       => isset($_GET['estado']) ? $_GET['estado'] : null,
    'order_by'     => isset($_GET['order_by']) ? $_GET['order_by'] : null,
];

$categories = $productSrv->getCategories();
$maquinaria_products = $productSrv->getProducts($filters);

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
    <title><?php echo Config::SITE_NAME; ?> - Alquiler de Maquinaria</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Alquiler de maquinaria pesada y venta de materiales pétreos" name="keywords">
    <meta content="Sistema de alquiler de maquinaria pesada y venta de materiales pétreos" name="description">

    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">  
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">

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
                    
                </div>
                <div class="d-inline-flex align-items-center d-block d-lg-none">
                    <a href="favorites.php" class="btn px-0 ml-2">
                        <i class="fas fa-heart text-dark"></i>
                        <span class="badge text-dark border border-dark rounded-circle" style="padding-bottom:2px;">0</span>
                    </a>
                    <?php if ($current_user): ?>
                        <?php if ($current_user['tipo_usuario'] === 'admin'): ?>
                            <a href="admin-rental-calendar.php" class="btn px-0 ml-2" title="Calendario de Alquileres">
                                <i class="fas fa-calendar-alt text-dark"></i>
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn px-0 ml-2" onclick="verMiCalendario()" title="Mis Alquileres">
                                <i class="fas fa-calendar-alt text-dark"></i>
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn px-0 ml-2" title="Ver Calendario">
                            <i class="fas fa-calendar-alt text-dark"></i>
                        </a>
                    <?php endif; ?>
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
                <form action="alquiler.php" method="GET">
                    <!-- <div class="input-group">
                        <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>" placeholder="Buscar maquinaria">
                        <div class="input-group-append">
                            <button class="input-group-text bg-transparent text-primary" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div> -->
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
                            <?php if ($current_user && $current_user['tipo_usuario']==='admin'): ?>
                                <a href="admin.php" class="nav-item nav-link text-warning"><i class="fas fa-tools mr-1"></i>Admin</a>
                            <?php endif; ?>
                        </div>
                        <div class="navbar-nav ml-auto py-0 d-none d-lg-block">
                            <a href="favorites.php" class="btn px-0">
                                <i class="fas fa-heart text-primary"></i>
                                <span class="badge text-secondary border border-secondary rounded-circle" style="padding-bottom:2px;">99</span>
                            </a>
                            <?php if ($current_user): ?>
                                <?php if ($current_user['tipo_usuario'] === 'admin'): ?>
                                    <a href="admin-rental-calendar.php" class="btn px-0 ml-3" title="Calendario de Alquileres">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn px-0 ml-3" onclick="verMiCalendario()" title="Mis Alquileres">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="btn px-0 ml-3" title="Ver Calendario">
                                    <i class="fas fa-calendar-alt text-primary"></i>
                                </a>
                            <?php endif; ?>
                            <a href="cart.php" class="btn px-0 ml-3">
                                <i class="fas fa-shopping-cart text-primary"></i>
                                <span class="badge text-secondary border border-secondary rounded-circle" id="cart-count-header" style="padding-bottom:2px;">+100</span>
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
                    <a class="breadcrumb-item text-dark" href="alquiler.php">Alquiler</a>
                    <span class="breadcrumb-item active">Maquinaria</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4">
            <span class="bg-secondary pr-3">Alquiler de Maquinaria</span>
        </h2>
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
                <form action="alquiler.php" method="GET" id="filterForm">
                    <!-- Búsqueda -->
                    <h5 class="section-title position-relative text-uppercase mb-3">
                        <span class="bg-secondary pr-3">Búsqueda</span>
                    </h5>
                    <div class="bg-light p-4 mb-30">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="q" placeholder="Buscar maquinaria..." 
                                   value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categorías -->
                    <h5 class="section-title position-relative text-uppercase mb-3">
                        <span class="bg-secondary pr-3">Categorías</span>
                    </h5>
                    <div class="bg-light p-4 mb-30">
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" id="cat-all" name="categoria" value="" 
                                   <?php echo empty($filters['categoria_id']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="cat-all">Todas las categorías</label>
                        </div>
                        <?php foreach ($categories as $cat): ?>
                            <?php if ($cat['tipo'] === 'maquinaria'): ?>
                                <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                                    <input type="radio" class="custom-control-input" id="cat-<?php echo $cat['id']; ?>" 
                                           name="categoria" value="<?php echo $cat['id']; ?>"
                                           <?php echo ($filters['categoria_id'] == $cat['id']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="cat-<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </label>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Estado -->
                    <h5 class="section-title position-relative text-uppercase mb-3">
                        <span class="bg-secondary pr-3">Estado</span>
                    </h5>
                    <div class="bg-light p-4 mb-30">
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" id="estado-all" name="estado" value="" 
                                   <?php echo empty($filters['estado']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="estado-all">Todos los estados</label>
                        </div>
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" id="estado-disponible" name="estado" value="disponible" 
                                   <?php echo ($filters['estado'] === 'disponible') ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="estado-disponible">Disponible</label>
                        </div>
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" id="estado-mantenimiento" name="estado" value="mantenimiento" 
                                   <?php echo ($filters['estado'] === 'mantenimiento') ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="estado-mantenimiento">En Mantenimiento</label>
                        </div>
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" id="estado-alquilado" name="estado" value="alquilado" 
                                   <?php echo ($filters['estado'] === 'alquilado') ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="estado-alquilado">Alquilado</label>
                        </div>
                    </div>

                    <!-- Ordenar por -->
                    <h5 class="section-title position-relative text-uppercase mb-3">
                        <span class="bg-secondary pr-3">Ordenar por</span>
                    </h5>
                    <div class="bg-light p-4 mb-30">
                        <select class="form-control" name="order_by">
                            <option value="">Orden por defecto</option>
                            <option value="nombre_asc" <?php echo ($filters['order_by'] === 'nombre_asc') ? 'selected' : ''; ?>>Nombre: A-Z</option>
                            <option value="nombre_desc" <?php echo ($filters['order_by'] === 'nombre_desc') ? 'selected' : ''; ?>>Nombre: Z-A</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="bg-light p-4 mb-30">
                        <button class="btn btn-primary btn-block mb-2" type="submit">
                            <i class="fa fa-filter mr-1"></i>Aplicar Filtros
                        </button>
                        <a href="alquiler.php" class="btn btn-outline-secondary btn-block">
                            <i class="fa fa-refresh mr-1"></i>Limpiar Filtros
                        </a>
                    </div>
                </form>
            </div>
            <!-- Shop Sidebar End -->

            <!-- Shop Product Start -->
            <div class="col-lg-9 col-md-8">
                <div class="row pb-3">
                    <div class="col-12 pb-1">
                        <!-- Información de filtros aplicados -->
                        <?php 
                        $filtros_aplicados = [];
                        if (!empty($filters['search'])) $filtros_aplicados[] = "Búsqueda: " . htmlspecialchars($filters['search']);
                        if (!empty($filters['categoria_id'])) {
                            $cat_nombre = '';
                            foreach ($categories as $cat) {
                                if ($cat['id'] == $filters['categoria_id']) {
                                    $cat_nombre = $cat['nombre'];
                                    break;
                                }
                            }
                            $filtros_aplicados[] = "Categoría: " . $cat_nombre;
                        }
                        if (!empty($filters['estado'])) {
                            $estados = ['disponible' => 'Disponible', 'mantenimiento' => 'En Mantenimiento', 'alquilado' => 'Alquilado'];
                            $filtros_aplicados[] = "Estado: " . ($estados[$filters['estado']] ?? $filters['estado']);
                        }
                        ?>
                        
                        <?php if (!empty($filtros_aplicados)): ?>
                            <div class="alert alert-info mb-3">
                                <strong>Filtros aplicados:</strong> <?php echo implode(' | ', $filtros_aplicados); ?>
                                <a href="alquiler.php" class="btn btn-sm btn-outline-secondary ml-2">
                                    <i class="fa fa-times"></i> Limpiar
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <h6 class="mb-0">
                                    Mostrando <?php echo count($maquinaria_products); ?> producto(s)
                                    <?php if (!empty($filters['search'])): ?>
                                        para "<?php echo htmlspecialchars($filters['search']); ?>"
                                    <?php endif; ?>
                                </h6>
                            </div>
                            <div class="ml-2">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">Mostrar</button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['limit' => 12])); ?>">12</a>
                                        <a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['limit' => 24])); ?>">24</a>
                                        <a class="dropdown-item" href="?<?php echo http_build_query(array_merge($_GET, ['limit' => 48])); ?>">48</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (is_array($maquinaria_products) && !empty($maquinaria_products)): ?>
                        <?php foreach ($maquinaria_products as $p): ?>
                            <div class="col-lg-4 col-md-6 col-sm-6 pb-1">
                                <div class="product-item bg-light mb-4">
                                    <div class="product-img position-relative overflow-hidden">
                                        <img class="img-fluid w-100"
                                             src="<?php echo !empty($p['imagen_principal']) ? htmlspecialchars($p['imagen_principal']) : 'img/product-1.jpg'; ?>"
                                             alt="<?php echo htmlspecialchars($p['nombre'] ?? 'Maquinaria'); ?>">
                                        <div class="product-action">
                                            <!-- <a class="btn btn-outline-dark btn-square" href="javascript:void(0)" onclick="addToCart(<?php echo (int)$p['id']; ?>, 'maquinaria')"
                                               title="Alquilar">
                                                <i class="fa fa-shopping-cart"></i>
                                            </a> -->
                                            <a class="btn btn-outline-dark btn-square" href="javascript:void(0)" onclick="addToFavorites(<?php echo (int)$p['id']; ?>)"
                                               title="Favoritos">
                                                <i class="far fa-heart"></i>
                                            </a>
                                            <!-- <a class="btn btn-outline-dark btn-square" href="product-detail.php?id=<?php echo (int)$p['id']; ?>"
                                               title="Ver detalles">
                                                <i class="fa fa-search"></i>
                                            </a> -->
                                            <?php if ($current_user && $current_user['tipo_usuario']==='admin'): ?>
                                                <a class="btn btn-outline-dark btn-square" href="edit-product.php?id=<?php echo (int)$p['id']; ?>"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-center py-4">
                                        <h6 class="text-truncate">
                                            <?php echo htmlspecialchars($p['nombre'] ?? 'Maquinaria'); ?>
                                        </h6>

                                        <?php if (!empty($p['descripcion'])): ?>
                                            <div class="product-description">
                                                <?php
                                                $desc = strip_tags($p['descripcion']);
                                                echo htmlspecialchars(mb_strimwidth($desc, 0, 110, '...'));
                                                ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex align-items-center justify-content-center mt-2">
                                            <?php
                                            if (isset($p['precio_alquiler_dia']) && $p['precio_alquiler_dia'] > 0) {
                                                echo '<h5>$'.number_format((float)$p['precio_alquiler_dia'], 2).'</h5><h6 class="text-muted ml-2">/día</h6>';
                                            } elseif (isset($p['tarifa_diaria']) && $p['tarifa_diaria'] !== null) {
                                                echo '<h5>$'.number_format((float)$p['tarifa_diaria'], 2).'</h5><h6 class="text-muted ml-2">/día</h6>';
                                            } elseif (isset($p['precio'])) {
                                                echo '<h5>$'.number_format((float)$p['precio'], 2).'</h5><h6 class="text-muted ml-2">/día</h6>';
                                            } else {
                                                echo '<h6 class="text-muted">Tarifa a consultar</h6>';
                                            }
                                            ?>
                                        </div>

                                        <div class="mb-2">
                                            <span class="stock-badge">
                                                <i class="fas fa-box"></i>
                                                Disponibles: <?php echo (int)($p['stock_disponible'] ?? 0); ?>
                                            </span>
                                        </div>

                                        <?php 
                                        $tiene_precio_alquiler = isset($p['precio_alquiler_dia']) && 
                                                                 ($p['precio_alquiler_dia'] > 0 || 
                                                                  (isset($p['tarifa_diaria']) && $p['tarifa_diaria'] > 0) ||
                                                                  (isset($p['precio']) && $p['precio'] > 0));
                                        
                                        $es_maquinaria = isset($p['categoria_tipo']) && $p['categoria_tipo'] === 'maquinaria';
                                        
                                        if ($tiene_precio_alquiler || $es_maquinaria): 
                                        ?>
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-info btn-block btn-sm mb-2" 
                                                        onclick="verFechasOcupadas(<?php echo (int)$p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['nombre'])); ?>')">
                                                    <i class="fas fa-calendar-check mr-2"></i>Ver Fechas Ocupadas
                                                </button>
                                                
                                                <?php if ($current_user): ?>
                                                    <a href="product-rental.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-primary btn-block">
                                                        <i class="fas fa-calendar-alt mr-2"></i>Agendar Alquiler
                                                    </a>
                                                <?php else: ?>
                                                    <a href="login.php?redirect=<?php echo urlencode('product-rental.php?id='.$p['id']); ?>" class="btn btn-primary btn-block">
                                                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión para Agendar
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

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
                            <p class="text-center mb-4">No hay maquinaria disponible con los criterios seleccionados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Shop Product End -->
        </div>
    </div>
    <!-- Shop End -->
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
                            <a class="text-secondary mb-2" href="venta.php"><i class="fa fa-angle-right mr-2"></i>Venta</a>
                            <a class="text-secondary mb-2" href="alquiler.php"><i class="fa fa-angle-right mr-2"></i>Alquiler</a>
                            <a class="text-secondary mb-2" href="contact.php"><i class="fa fa-angle-right mr-2"></i>Contacto</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <h5 class="text-secondary text-uppercase mb-4">Quienes somos?</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <?php if ($current_user): ?>
                                <a class="text-secondary mb-2" href="profile.php"><i class="fa fa-angle-right mr-2"></i>Descubrir</a>
                                
                            <?php else: ?>
                                <a class="text-secondary mb-2" href="login.php"><i class="fa fa-angle-right mr-2"></i>Iniciar Sesión</a>
                                <a class="text-secondary mb-2" href="register.php"><i class="fa fa-angle-right mr-2"></i>Registrarse</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <!-- <h5 class="text-secondary text-uppercase mb-4">Newsletter</h5>
                        <p>Suscríbete para recibir ofertas especiales</p>
                        <form action="newsletter.php" method="POST">
                            <div class="input-group">
                                <input type="email" class="form-control" name="email" placeholder="Tu Email" required>
                                <div class="input-group-append">
                                    <button class="btn btn-primary">Suscribirse</button>
                                </div>
                            </div>
                        </form> -->
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
                <img class="img-fluid" src="img/reference/payments.png" alt="">
            </div>
        </div>
    </div>

    <a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>

    <div class="modal fade" id="miCalendarioModal" tabindex="-1" role="dialog" aria-labelledby="miCalendarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="miCalendarioModalLabel">
                        <i class="fas fa-calendar-check mr-2"></i>Mis Alquileres
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="calendar-mis-alquileres" class="mb-3"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Leyenda:</strong> Las fechas marcadas muestran tus alquileres activos. Haz clic en un evento para ver más detalles.
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="my-rentals.php" class="btn btn-primary">
                        <i class="fas fa-list mr-2"></i>Ver Lista Completa
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="fechasOcupadasModal" tabindex="-1" role="dialog" aria-labelledby="fechasOcupadasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fechasOcupadasModalLabel">
                        <i class="fas fa-calendar-check mr-2"></i>Fechas Ocupadas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="producto-nombre-modal" class="mb-3">
                        <h6 class="text-primary"></h6>
                    </div>
                    <div id="calendar-ocupadas" class="mb-3"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Nota:</strong> Las fechas marcadas en <span style="background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 4px;">rojo</span> indican que el producto ya está reservado para esas fechas.
                    </div>
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
    let calendarOcupadas = null;
    let calendarMisAlquileres = null;
    let currentProductId = null;

    function verFechasOcupadas(productId, nombreProducto) {
        $('#producto-nombre-modal h6').text(nombreProducto);
        currentProductId = productId;
        
        const loadEvents = function(fetchInfo, successCallback, failureCallback) {
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
        };
        
        if (calendarOcupadas) {
            calendarOcupadas.destroy();
            calendarOcupadas = null;
        }
        
        var calendarEl = document.getElementById('calendar-ocupadas');
        calendarOcupadas = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            firstDay: 1,
            height: 'auto',
            validRange: {
                start: new Date().toISOString().split('T')[0]
            },
            events: loadEvents
        });
        
        calendarOcupadas.render();
        $('#fechasOcupadasModal').modal('show');
    }

    function verMiCalendario() {
        <?php if (!$current_user): ?>
            window.location.href = 'login.php';
            return;
        <?php endif; ?>
        
        $('#miCalendarioModal').modal('show');
        
        const loadMyRentals = function(fetchInfo, successCallback, failureCallback) {
            fetch(`api/rentals.php?fecha_desde=${fetchInfo.startStr}&fecha_hasta=${fetchInfo.endStr}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const events = data.data.map(rental => {
                            const startDate = new Date(rental.fecha_inicio);
                            const endDate = new Date(rental.fecha_fin);
                            endDate.setDate(endDate.getDate() + 1);
                            
                            const estados = {
                                'pendiente': { color: '#ffc107', title: 'Pendiente' },
                                'confirmado': { color: '#28a745', title: 'Confirmado' },
                                'en_curso': { color: '#007bff', title: 'En Curso' },
                                'finalizado': { color: '#6c757d', title: 'Finalizado' },
                                'cancelado': { color: '#dc3545', title: 'Cancelado' }
                            };
                            
                            const estadoInfo = estados[rental.estado] || { color: '#6c757d', title: rental.estado };
                            
                            return {
                                id: rental.id,
                                title: rental.producto_nombre,
                                start: startDate.toISOString().split('T')[0],
                                end: endDate.toISOString().split('T')[0],
                                color: estadoInfo.color,
                                extendedProps: {
                                    rental: rental
                                }
                            };
                        });
                        successCallback(events);
                    } else {
                        successCallback([]);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    failureCallback();
                });
        };
        
        if (calendarMisAlquileres) {
            calendarMisAlquileres.destroy();
            calendarMisAlquileres = null;
        }
        
        var calendarEl = document.getElementById('calendar-mis-alquileres');
        calendarMisAlquileres = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            firstDay: 1,
            height: 'auto',
            events: loadMyRentals,
            eventClick: function(info) {
                const rental = info.event.extendedProps.rental;
                const fechaInicio = new Date(rental.fecha_inicio).toLocaleDateString('es-ES');
                const fechaFin = new Date(rental.fecha_fin).toLocaleDateString('es-ES');
                const estados = {
                    'pendiente': 'Pendiente',
                    'confirmado': 'Confirmado',
                    'en_curso': 'En Curso',
                    'finalizado': 'Finalizado',
                    'cancelado': 'Cancelado'
                };
                
                alert(`Alquiler: ${rental.producto_nombre}\n` +
                      `Fechas: ${fechaInicio} - ${fechaFin}\n` +
                      `Estado: ${estados[rental.estado] || rental.estado}\n` +
                      `Total: $${parseFloat(rental.total).toFixed(2)}`);
            }
        });
        
        calendarMisAlquileres.render();
    }

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

    // Agregar al carrito (alquiler)
    function addToCart(productId, tipo) {
        <?php if ($current_user): ?>
        $.post('api/cart.php', { producto_id: productId, tipo: tipo, cantidad: 1 }, function(response) {
            if (response && response.success) {
                updateCartCount();
                alert('Maquinaria agregada al carrito de alquiler');
            } else {
                alert('Error: ' + (response && response.message ? response.message : 'No se pudo agregar.'));
            }
        }, 'json');
        <?php else: ?>
        alert('Debe iniciar sesión para alquilar maquinaria');
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

    $(document).ready(function(){ 
        updateCartCount(); 
        
        // Auto-submit form cuando cambian los filtros
        $('#filterForm input[type="radio"], #filterForm select').change(function() {
            $('#filterForm').submit();
        });
        
        // Auto-submit form cuando se presiona Enter en el campo de búsqueda
        $('#filterForm input[name="q"]').keypress(function(e) {
            if (e.which == 13) {
                $('#filterForm').submit();
            }
        });
    });
    </script>
</body>
</html>
