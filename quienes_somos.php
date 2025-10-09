<?php
require_once 'includes/auth.php';
require_once 'includes/security.php';
Security::setSecurityHeaders();

$auth = new Auth();
$current_user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo Config::SITE_NAME; ?> - Quiénes Somos</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Quiénes somos, historia, proyectos, galería" name="keywords">
    <meta content="Conoce la historia de nuestra empresa y revisa la galería de proyectos realizados" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome (v6 como en tu código) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <!-- Estilos propios de esta página -->
    <style>
        /* Encabezado de página */
        .page-header {
            background: #f5f5f5;
            padding: 60px 0;
        }
        /* Historia */
        .about-block {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,.05);
        }
        .about-list li {
            margin-bottom: .5rem;
        }
        /* Galería */
        .gallery-item {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            transition: .3s;
            box-shadow: 0 6px 16px rgba(0,0,0,.06);
        }
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0,0,0,.1);
        }
        .gallery-item img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
        }
        .gallery-caption {
            padding: 14px 16px;
        }
        .gallery-caption h6 {
            margin: 0 0 6px;
            font-weight: 700;
            color: #333;
        }
        .gallery-caption p {
            margin: 0;
            color: #6c757d;
            font-size: .95rem;
            line-height: 1.4;
        }
        /* Breadcrumb */
        .breadcrumb a { text-decoration: none; }
    </style>
</head>
<body>
    <!-- Topbar Start -->
    <div class="container-fluid">
        <div class="row bg-secondary py-1 px-xl-5">
            <div class="col-lg-6 d-none d-lg-block">
                <div class="d-inline-flex align-items-center h-100">
                    <a class="text-body mr-3" href="quienes-somos.php">Acerca de</a>
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
                                    <a class="dropdown-item text-primary" href="admin.php"><i class="fas fa-tools me-2"></i>Panel de Administración</a>
                                    <a class="dropdown-item text-primary" href="admin-products.php"><i class="fas fa-list me-2"></i>Gestionar Productos</a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">Cerrar Sesión</a>
                            <?php else: ?>
                                <a class="dropdown-item" href="login.php">Iniciar Sesión</a>
                                <a class="dropdown-item" href="register.php">Registrarse</a>
                            <?php endif; ?>
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
            <div class="col-lg-4 col-6 text-left"></div>
            <div class="col-lg-4 col-6 text-right">
                <p class="m-0">Contáctanos</p>
                <h5 class="m-0">+012 345 6789</h5>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar Start (sin menú vertical; a todo el ancho) -->
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
                            <a href="quienes-somos.php" class="nav-item nav-link active">Quiénes Somos</a>
                            <a href="contact.php" class="nav-item nav-link">Contáctanos</a>
                            <?php if ($current_user && $current_user['tipo_usuario'] === 'admin'): ?>
                                <a href="admin.php" class="nav-item nav-link text-warning"><i class="fas fa-tools me-1"></i>Admin</a>
                            <?php endif; ?>
                        </div>
                        <div class="navbar-nav ml-auto py-0 d-none d-lg-block">
                            <a href="favorites.php" class="btn px-0">
                                <i class="fas fa-heart text-primary"></i>
                                <span class="badge text-secondary border border-secondary rounded-circle" style="padding-bottom: 2px;">0</span>
                            </a>
                            <a href="cart.php" class="btn px-0 ml-3">
                                <i class="fas fa-shopping-cart text-primary"></i>
                                <span class="badge text-secondary border border-secondary rounded-circle" id="cart-count-header" style="padding-bottom: 2px;">0</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row px-xl-5">
                <div class="col-12">
                    <nav class="breadcrumb bg-light mb-3">
                        <a class="breadcrumb-item text-dark" href="index.php">Inicio</a>
                        <span class="breadcrumb-item active">Quiénes Somos</span>
                    </nav>
                    <h1 class="display-5 mb-0">Quiénes Somos</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Historia de la Empresa -->
    <div class="container my-5">
        <div class="row px-xl-5">
            <div class="col-12">
                <div class="about-block p-4 p-md-5">
                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h2 class="text-uppercase mb-3">Nuestra Historia</h2>
                            <p class="mb-3">
                                Nacimos en <strong>2010</strong> con una idea clara: facilitar el acceso a maquinaria pesada y
                                materiales pétreos de <strong>alta calidad</strong> para proyectos de construcción en todo el país.
                                Con el paso de los años, hemos crecido de la mano de nuestros clientes, integrando nuevas
                                tecnologías, optimizando procesos logísticos y expandiendo nuestro catálogo.
                            </p>
                            <p class="mb-3">
                                Hoy somos un aliado estratégico para empresas y constructoras, con un enfoque en
                                <strong>seguridad</strong>, <strong>cumplimiento</strong> y <strong>excelencia operativa</strong>.
                                Nuestro equipo técnico garantiza el mantenimiento adecuado de la maquinaria y un soporte
                                cercano durante todo el proyecto.
                            </p>
                            <ul class="about-list list-unstyled mb-0">
                                <li><i class="fas fa-check text-primary mr-2"></i>Más de 1.000 entregas exitosas.</li>
                                <li><i class="fas fa-check text-primary mr-2"></i>Flota propia y verificada.</li>
                                <li><i class="fas fa-check text-primary mr-2"></i>Asesoría técnica certificada.</li>
                                <li><i class="fas fa-check text-primary mr-2"></i>Compromiso ambiental y de seguridad.</li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <img src="img/reference/carousel-1.jpg" class="img-fluid rounded" alt="Nuestra empresa en obra">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Galería de Proyectos -->
    <div class="container mb-5">
        <div class="row px-xl-5">
            <div class="col-12">
                <h2 class="section-title position-relative text-uppercase mb-4">
                    <span class="bg-secondary pr-3">Galería de Proyectos</span>
                </h2>
            </div>

            <!-- Item 1 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="gallery-item">
                    <img src="img/galeria/proyecto-1.jpg" alt="Movimiento de tierras - Proyecto Vía Norte">
                    <div class="gallery-caption">
                        <h6>Movimiento de tierras – Vía Norte</h6>
                        <p>Excavación y nivelación de terreno para la ampliación de la vía. Coordinación de flota y
                           control de calidad del material removido.</p>
                    </div>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="gallery-item">
                    <img src="img/galeria/proyecto-2.jpg" alt="Suministro de agregados para hormigón">
                    <div class="gallery-caption">
                        <h6>Suministro de agregados</h6>
                        <p>Entrega programada de arena y grava para planta de hormigón, con certificación de granulometría.</p>
                    </div>
                </div>
            </div>

            <!-- Item 3 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="gallery-item">
                    <img src="img/galeria/proyecto-3.jpg" alt="Compactación de subrasante en plataforma industrial">
                    <div class="gallery-caption">
                        <h6>Compactación de subrasante</h6>
                        <p>Uso de compactadores y rodillos para alcanzar densidades óptimas en plataforma industrial.</p>
                    </div>
                </div>
            </div>

            <!-- Item 4 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="gallery-item">
                    <img src="img/galeria/proyecto-4.jpg" alt="Estabilización de taludes">
                    <div class="gallery-caption">
                        <h6>Estabilización de taludes</h6>
                        <p>Conformación y protección de taludes con mezcla seleccionada y control de pendientes.</p>
                    </div>
                </div>
            </div>

            <!-- Item 5 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="gallery-item">
                    <img src="img/galeria/proyecto-5.jpg" alt="Carguío y transporte en cantera">
                    <div class="gallery-caption">
                        <h6>Carguío y transporte</h6>
                        <p>Operación continua en cantera, optimizando ciclos de cargadores y volquetas.</p>
                    </div>
                </div>
            </div>

            <!-- Item 6 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="gallery-item">
                    <img src="img/galeria/proyecto-6.jpg" alt="Base granular para pavimento urbano">
                    <div class="gallery-caption">
                        <h6>Base granular para pavimento</h6>
                        <p>Producción y extendido de material base cumpliendo especificaciones municipales.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA / Cierre -->
    <div class="container mb-5">
        <div class="row px-xl-5">
            <div class="col-12">
                <div class="about-block p-4 p-md-5 text-center">
                    <h3 class="mb-3">¿Listo para tu próximo proyecto?</h3>
                    <p class="mb-4">Ponte en contacto con nuestro equipo para recibir una cotización y asesoría técnica.</p>
                    <a href="contact.php" class="btn btn-primary px-4"><i class="fa fa-envelope mr-2"></i>Contáctanos</a>
                </div>
            </div>
        </div>
    </div>

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
                            <a class="text-secondary mb-2" href="venta.php"><i class="fa fa-angle-right mr-2"></i>Venta</a>
                            <a class="text-secondary mb-2" href="alquiler.php"><i class="fa fa-angle-right mr-2"></i>Alquiler</a>
                            <a class="text-secondary mb-2" href="quienes-somos.php"><i class="fa fa-angle-right mr-2"></i>Quiénes Somos</a>
                            <a class="text-secondary" href="contact.php"><i class="fa fa-angle-right mr-2"></i>Contacto</a>
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
                <img class="img-fluid" src="img/reference/payments.png" alt="">
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
</body>
</html>
