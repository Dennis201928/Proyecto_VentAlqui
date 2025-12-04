<!-- Topbar Start -->
<div class="container-fluid">
    <div class="row bg-secondary py-1 px-xl-5">
        <div class="col-lg-6 d-none d-lg-block">
            <div class="d-inline-flex align-items-center h-100">
                <a class="text-body mr-3" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/quienes-somos">Acerca de</a>
                <a class="text-body mr-3" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/contacto">Contáctanos</a>
            </div>
        </div>
        <div class="col-lg-6 text-center text-lg-right">
            <div class="d-inline-flex align-items-center">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                        <?php echo (isset($current_user) && $current_user) ? htmlspecialchars($current_user['nombre'] . ' ' . $current_user['apellido']) : 'Mi Cuenta'; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <?php if (isset($current_user) && $current_user): ?>
                            <a class="dropdown-item" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/mi-perfil">Mi Perfil</a>
                            <?php if (!isset($current_user['tipo_usuario']) || $current_user['tipo_usuario'] !== 'admin'): ?>
                                <a class="dropdown-item" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/mis-alquileres">Mis Alquileres</a>
                            <?php endif; ?>
                            <?php if (isset($current_user) && $current_user && $current_user['tipo_usuario'] === 'admin'): ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-primary" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/admin">
                                    <i class="fas fa-tools me-2"></i>Panel de Administración
                                </a>
                                <a class="dropdown-item text-primary" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/admin/productos">
                                    <i class="fas fa-list me-2"></i>Gestionar Productos
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/logout">Cerrar Sesión</a>
                        <?php else: ?>
                            <a class="dropdown-item" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/login">Iniciar Sesión</a>
                            <a class="dropdown-item" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/register">Registrarse</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <div class="row align-items-center bg-light py-3 px-xl-5 d-none d-lg-flex">
  <div class="col-lg-4">
    <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/" class="text-decoration-none">
      <span class="h1 text-uppercase text-primary bg-dark px-2">Alqui</span>
      <span class="h1 text-uppercase text-dark bg-primary px-2 ml-n1">Venta</span>
    </a>
  </div>

  <!-- Contáctanos empujado a la derecha -->
  <div class="col-lg-4 d-flex align-items-center ml-auto justify-content-end text-right">
    <div>
      <p class="m-0">Contáctanos</p>
      <h5 class="m-0">+012 345 6789</h5>
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
                <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/" class="text-decoration-none d-block d-lg-none">
                    <span class="h1 text-uppercase text-dark bg-light px-2">Alqui</span>
                    <span class="h1 text-uppercase text-light bg-primary px-2 ml-n1">Venta</span>
                </a>
                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                    <div class="navbar-nav mr-auto py-0">
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/" class="nav-item nav-link <?php echo (isset($current_page) && $current_page === 'home') ? 'active' : ''; ?>">Inicio</a>
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/venta" class="nav-item nav-link <?php echo (isset($current_page) && $current_page === 'venta') ? 'active' : ''; ?>">Venta</a>
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/alquiler" class="nav-item nav-link <?php echo (isset($current_page) && $current_page === 'alquiler') ? 'active' : ''; ?>">Alquiler</a>
                        <?php if (isset($current_user) && $current_user && (!isset($current_user['tipo_usuario']) || $current_user['tipo_usuario'] !== 'admin')): ?>
                            <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/mis-alquileres" class="nav-item nav-link <?php echo (isset($current_page) && $current_page === 'mis-alquileres') ? 'active' : ''; ?>">Mis Alquileres</a>
                        <?php endif; ?>
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/quienes-somos" class="nav-item nav-link <?php echo (isset($current_page) && $current_page === 'quienes-somos') ? 'active' : ''; ?>">Quiénes Somos</a>
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/contacto" class="nav-item nav-link <?php echo (isset($current_page) && $current_page === 'contacto') ? 'active' : ''; ?>">Contáctanos</a>
                        <?php if (isset($current_user) && $current_user && $current_user['tipo_usuario'] === 'admin'): ?>
                            <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/admin" class="nav-item nav-link text-warning">
                                <i class="fas fa-tools me-1"></i>Administrador
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="navbar-nav ml-auto py-0 d-none d-lg-block">
                        <?php if (isset($current_user) && $current_user && (!isset($current_user['tipo_usuario']) || $current_user['tipo_usuario'] !== 'admin')): ?>
                            <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/mis-alquileres" class="btn btn-outline-light btn-sm ml-3">Mis Alquileres</a>
                        <?php endif; ?>
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/carrito" class="btn px-0 ml-3">
                            <i class="fas fa-shopping-cart text-primary"></i>
                            <span class="badge text-secondary border border-secondary rounded-circle" id="cart-count-header" style="padding-bottom: 2px;"></span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</div>
<!-- Navbar End -->

