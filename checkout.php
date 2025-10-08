<?php
require_once 'includes/auth.php';
require_once 'includes/cart.php';
require_once 'includes/order.php';

$auth = new Auth();
$cart = new Cart();
$order = new Order();

// Verificar autenticación
$auth->requireAuth();
$user_id = $_SESSION['user_id'];

$error_message = '';
$success_message = '';

// Obtener información del usuario
$current_user = $auth->getCurrentUser();

// Obtener items del carrito
$cart_items = $cart->getCartItems($user_id);
$cart_total = $cart->getCartTotal($user_id);

// Procesar checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metodo_pago = $_POST['metodo_pago'];
    $direccion_entrega = $_POST['direccion_entrega'];
    
    if (empty($metodo_pago)) {
        $error_message = 'Debe seleccionar un método de pago';
    } else {
        $result = $order->createOrderFromCart($user_id, $metodo_pago, $direccion_entrega);
        
        if ($result['success']) {
            $success_message = $result['message'];
            header('Location: order-success.php?id=' . $result['venta_id']);
            exit();
        } else {
            $error_message = $result['message'];
        }
    }
}

// Si no hay items en el carrito, redirigir
if (empty($cart_items) || isset($cart_items['error'])) {
    header('Location: cart.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Checkout - <?php echo Config::SITE_NAME; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
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
                            <?php echo $current_user['nombre'] . ' ' . $current_user['apellido']; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="profile.php">Mi Perfil</a>
                            <a class="dropdown-item" href="my-orders.php">Mis Pedidos</a>
                            <a class="dropdown-item" href="my-rentals.php">Mis Alquileres</a>
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
            <div class="col-lg-3 d-none d-lg-block">
                <a href="index.php" class="text-decoration-none">
                    <span class="h1 text-uppercase text-primary bg-dark px-2">Alqui</span>
                    <span class="h1 text-uppercase text-dark bg-primary px-2 ml-n1">Venta</span>
                </a>
            </div>
            <div class="col-lg-9">
                <nav class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-0">
                    <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                        <div class="navbar-nav mr-auto py-0">
                            <a href="index.php" class="nav-item nav-link">Inicio</a>
                            <a href="index.php" class="nav-item nav-link">Venta</a>
                            <a href="alquiler.php" class="nav-item nav-link">Alquiler</a>
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
                    <a class="breadcrumb-item text-dark" href="cart.php">Carrito</a>
                    <span class="breadcrumb-item active">Checkout</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Checkout Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-lg-8">
                <div class="mb-4">
                    <h4 class="font-weight-semi-bold mb-4">Información de Facturación</h4>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nombre</label>
                                <input class="form-control" type="text" value="<?php echo htmlspecialchars($current_user['nombre']); ?>" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Apellido</label>
                                <input class="form-control" type="text" value="<?php echo htmlspecialchars($current_user['apellido']); ?>" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Email</label>
                                <input class="form-control" type="text" value="<?php echo htmlspecialchars($current_user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Teléfono</label>
                                <input class="form-control" type="text" value="<?php echo htmlspecialchars($current_user['telefono']); ?>" readonly>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Dirección de Entrega</label>
                                <textarea class="form-control" name="direccion_entrega" rows="3" required><?php echo htmlspecialchars($current_user['direccion']); ?></textarea>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Método de Pago</label>
                                <select class="form-control" name="metodo_pago" required>
                                    <option value="">Seleccionar método de pago</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia Bancaria</option>
                                    <option value="tarjeta">Tarjeta de Crédito</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Confirmar Pedido</button>
                            <a href="cart.php" class="btn btn-secondary btn-lg ml-2">Volver al Carrito</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-secondary mb-5">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0">Resumen del Pedido</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h6 class="font-weight-medium">Productos</h6>
                            <h6 class="font-weight-medium"><?php echo $cart_total['items_count']; ?></h6>
                        </div>
                        
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between">
                                <p><?php echo htmlspecialchars($item['nombre']); ?> x<?php echo $item['cantidad']; ?></p>
                                <p>
                                    <?php 
                                    $precio = ($item['tipo'] == 'alquiler') ? $item['precio_alquiler_dia'] : $item['precio_venta'];
                                    $subtotal = $precio * $item['cantidad'];
                                    
                                    if ($item['tipo'] == 'alquiler' && $item['fecha_inicio'] && $item['fecha_fin']) {
                                        $dias = (strtotime($item['fecha_fin']) - strtotime($item['fecha_inicio'])) / (60 * 60 * 24);
                                        $subtotal = $precio * $item['cantidad'] * $dias;
                                    }
                                    
                                    echo '$' . number_format($subtotal, 2);
                                    ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr class="mt-0">
                        <div class="d-flex justify-content-between mb-3">
                            <h6 class="font-weight-medium">Subtotal</h6>
                            <h6 class="font-weight-medium">$<?php echo number_format($cart_total['subtotal'], 2); ?></h6>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h6 class="font-weight-medium">Impuestos</h6>
                            <h6 class="font-weight-medium">$<?php echo number_format($cart_total['impuestos'], 2); ?></h6>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h6 class="font-weight-medium">Total</h6>
                            <h6 class="font-weight-medium">$<?php echo number_format($cart_total['total'], 2); ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Checkout End -->

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
        </div>
    </div>
    <!-- Footer End -->

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
