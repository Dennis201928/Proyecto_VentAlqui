<?php
require_once 'includes/auth.php';
require_once 'includes/order.php';

$auth = new Auth();
$order = new Order();

// Verificar autenticación
$auth->requireAuth();
$user_id = $_SESSION['user_id'];

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    header('Location: index.php');
    exit();
}

// Obtener detalles del pedido
$order_details = $order->getOrderDetails($order_id);

if (!$order_details || isset($order_details['error'])) {
    header('Location: index.php');
    exit();
}

// Verificar que el pedido pertenece al usuario
if ($order_details['order']['usuario_id'] != $user_id) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pedido Exitoso - <?php echo Config::SITE_NAME; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

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
                            <a href="index.php" class="nav-item nav-link">Contáctanos</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Success Message Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-success mb-3">¡Pedido Realizado Exitosamente!</h2>
                    <p class="lead mb-4">Gracias por su compra. Su pedido ha sido procesado correctamente.</p>
                    <p class="text-muted">Número de pedido: <strong>#<?php echo $order_details['order']['id']; ?></strong></p>
                </div>
            </div>
        </div>
    </div>
    <!-- Success Message End -->

    <!-- Order Details Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Detalles del Pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Fecha del Pedido:</strong><br>
                                <?php echo date('d/m/Y H:i', strtotime($order_details['order']['fecha_creacion'])); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Estado:</strong><br>
                                <span class="badge badge-info"><?php echo ucfirst($order_details['order']['estado']); ?></span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Método de Pago:</strong><br>
                                <?php echo ucfirst($order_details['order']['metodo_pago']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Total:</strong><br>
                                $<?php echo number_format($order_details['order']['total'], 2); ?>
                            </div>
                        </div>
                        
                        <?php if ($order_details['order']['direccion_entrega']): ?>
                            <div class="mb-3">
                                <strong>Dirección de Entrega:</strong><br>
                                <?php echo nl2br(htmlspecialchars($order_details['order']['direccion_entrega'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Productos</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($order_details['details'] as $detail): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($detail['producto_nombre']); ?></strong><br>
                                    <small class="text-muted">Cantidad: <?php echo $detail['cantidad']; ?></small>
                                </div>
                                <div class="text-right">
                                    $<?php echo number_format($detail['subtotal'], 2); ?>
                                </div>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                        
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong>$<?php echo number_format($order_details['order']['total'], 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Order Details End -->

    <!-- Actions Start -->
    <div class="container-fluid py-5">
        <div class="row px-xl-5">
            <div class="col-12 text-center">
                <a href="index.php" class="btn btn-primary btn-lg mr-3">Ver Productos</a>
                <a href="index.php" class="btn btn-secondary btn-lg">Continuar Comprando</a>
            </div>
        </div>
    </div>
    <!-- Actions End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-secondary mt-5 pt-5">
        <div class="row px-xl-5 pt-5">
            <div class="col-12 text-center">
                <p class="mb-0">&copy; 2024 AlquiVenta. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>