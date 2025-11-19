<!-- Footer Start -->
<footer class="mt-auto w-100">
<div class="container-fluid bg-dark text-secondary pt-5 px-0">
    <div class="row px-xl-5 pt-5">
        <div class="col-lg-4 col-md-12 mb-5 pr-3 pr-xl-5">
            <h5 class="text-secondary text-uppercase mb-4">Contáctanos</h5>
            <p class="mb-4">Somos especialistas en venta y alquiler de maquinaria pesada y materiales pétreos de alta calidad.</p>
            <p class="mb-2"><i class="fa fa-map-marker-alt text-primary mr-3"></i>123 Street, New York, USA</p>
            <p class="mb-2"><i class="fa fa-envelope text-primary mr-3"></i>info@alquivent.com</p>
            <p class="mb-0"><i class="fa fa-phone-alt text-primary mr-3"></i>+593 345 67890</p>
        </div>
        <div class="col-lg-8 col-md-12">
            <div class="row">
                <div class="col-md-4 mb-5">
                    <h5 class="text-secondary text-uppercase mb-4">Navegación</h5>
                    <div class="d-flex flex-column justify-content-start">
                        <a class="text-secondary mb-2" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/"><i class="fa fa-angle-right mr-2"></i>Inicio</a>
                        <a class="text-secondary mb-2" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/venta"><i class="fa fa-angle-right mr-2"></i>Venta</a>
                        <a class="text-secondary mb-2" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/alquiler"><i class="fa fa-angle-right mr-2"></i>Alquiler</a>
                        <a class="text-secondary mb-2" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/contacto"><i class="fa fa-angle-right mr-2"></i>Contacto</a>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <h5 class="text-secondary text-uppercase mb-4">Mi Cuenta</h5>
                    <div class="d-flex flex-column justify-content-start">
                        <?php if (isset($current_user) && $current_user): ?>
                            <a class="text-secondary mb-2" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/mi-perfil"><i class="fa fa-angle-right mr-2"></i>Mi Perfil</a>
                            <?php if (!isset($current_user['tipo_usuario']) || $current_user['tipo_usuario'] !== 'admin'): ?>
                                <a class="text-secondary mb-2" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/mis-alquileres"><i class="fa fa-angle-right mr-2"></i>Mis Alquileres</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a class="text-secondary mb-2" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/login"><i class="fa fa-angle-right mr-2"></i>Iniciar Sesión</a>
                            <a class="text-secondary mb-2" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/register"><i class="fa fa-angle-right mr-2"></i>Registrarse</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
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
                &copy; <a class="text-primary" href="#">AlquiVenta</a>. Todos los derechos reservados.
            </p>
        </div>
        <div class="col-md-6 px-xl-0 text-center text-md-right">
            <img class="img-fluid" src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/img/reference/payments.png" alt="">
        </div>
    </div>
</div>
</footer>
<!-- Footer End -->

<!-- Back to Top -->
<a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>

