<?php $current_page = 'home'; ?>
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
                        <img class="position-absolute w-100 h-100" src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/img/reference/carousel-1.jpg" style="object-fit: cover;">
                        <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                            <div class="p-3" style="max-width: 700px;">
                                <h1 class="display-4 text-white mb-3 animate__animated animate__fadeInDown">Alquiler De Maquinaria</h1>
                                <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Alquiler de Maquinaria Pesada y Venta de Materiales Pétreos</p>
                                <a class="btn btn-outline-light py-2 px-4 mt-3 animate__animated animate__fadeInUp" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/alquiler">Ver</a>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item position-relative" style="height: 430px;">
                        <img class="position-absolute w-100 h-100" src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/img/reference/carousel-2.jpg" style="object-fit: cover;">
                        <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                            <div class="p-3" style="max-width: 700px;">
                                <h1 class="display-4 text-white mb-3 animate__animated animate__fadeInDown">Venta de Materiales Pétreos</h1>
                                <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Materiales de alta calidad para construcción</p>
                                <a class="btn btn-outline-light py-2 px-4 mt-3 animate__animated animate__fadeInUp" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/venta">Ver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="product-offer mb-30" style="height: 200px;">
                <img class="img-fluid" src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/img/reference/offer-1.jpg" alt="">
                <div class="offer-text">
                    <h3 class="text-white mb-3">Oferta Especial</h3>
                    <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/alquiler" class="btn btn-primary">Alquilar</a>
                </div>
            </div>
            <div class="product-offer mb-30" style="height: 200px;">
                <img class="img-fluid" src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/img/reference/offer-2.jpg" alt="">
                <div class="offer-text">
                    <h3 class="text-white mb-3">Oferta Especial</h3>
                    <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/venta" class="btn btn-primary">Comprar</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Carousel End -->

<!-- Mensaje de Éxito -->
<?php if (!empty($success_message)): ?>
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
    <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4">
        <span class="bg-secondary pr-3">Alquiler de Maquinaria</span>
    </h2>
    <div class="row px-xl-5">
        <?php if (is_array($maquinaria_products) && !empty($maquinaria_products)): ?>
            <?php foreach ($maquinaria_products as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                    <div class="product-item bg-light mb-4">
                        <div class="product-img position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="<?php echo \App\Helpers\ImageHelper::getImageUrl($product['imagen_principal'] ?? '', $baseUrl ?? null); ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/producto/<?php echo $product['id']; ?>">
                                    <i class="fa fa-shopping-cart"></i>
                                </a>
                                <?php if ($current_user && $current_user['tipo_usuario'] === 'admin'): ?>
                                    <a class="btn btn-outline-dark btn-square" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/admin/productos/edit/<?php echo $product['id']; ?>" title="Editar producto">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <h6 class="text-truncate"><?php echo htmlspecialchars($product['nombre']); ?></h6>
                            <?php if (!empty($product['descripcion'])): ?>
                                <div class="product-description">
                                    <?php echo htmlspecialchars(substr($product['descripcion'], 0, 100)) . (strlen($product['descripcion']) > 100 ? '...' : ''); ?>
                                </div>
                            <?php endif; ?>
                            <div class="mb-2">
                                <span class="stock-badge">
                                    <i class="fas fa-box"></i>Stock: <?php echo $product['stock_disponible']; ?>
                                </span>
                            </div>
                            <?php if ($product['precio_alquiler_dia']): ?>
                                <h5 class="text-primary">$<?php echo number_format($product['precio_alquiler_dia'], 2); ?>/día</h5>
                            <?php endif; ?>
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
    <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4">
        <span class="bg-secondary pr-3">Venta de Materiales Pétreos</span>
    </h2>
    <div class="row px-xl-5">
        <?php if (is_array($materiales_products) && !empty($materiales_products)): ?>
            <?php foreach ($materiales_products as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                    <div class="product-item bg-light mb-4">
                        <div class="product-img position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="<?php echo \App\Helpers\ImageHelper::getImageUrl($product['imagen_principal'] ?? '', $baseUrl ?? null); ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/producto/<?php echo $product['id']; ?>">
                                    <i class="fa fa-shopping-cart"></i>
                                </a>
                                <?php if ($current_user && $current_user['tipo_usuario'] === 'admin'): ?>
                                    <a class="btn btn-outline-dark btn-square" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/admin/productos/edit/<?php echo $product['id']; ?>" title="Editar producto">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <h6 class="text-truncate"><?php echo htmlspecialchars($product['nombre']); ?></h6>
                            <?php if (!empty($product['descripcion'])): ?>
                                <div class="product-description">
                                    <?php echo htmlspecialchars(substr($product['descripcion'], 0, 100)) . (strlen($product['descripcion']) > 100 ? '...' : ''); ?>
                                </div>
                            <?php endif; ?>
                            <div class="mb-2">
                                <span class="stock-badge">
                                    <i class="fas fa-box"></i>Stock: <?php echo $product['stock_disponible']; ?>
                                </span>
                            </div>
                            <?php if ($product['precio_venta']): ?>
                                <h5 class="text-primary">$<?php echo number_format($product['precio_venta'], 2); ?></h5>
                            <?php endif; ?>
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
                <img class="img-fluid" src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/img/reference/offer-1.jpg" alt="">
                <div class="offer-text">
                    <h3 class="text-white mb-3">Precios Especiales</h3>
                    <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/venta" class="btn btn-primary">Comprar</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="product-offer mb-30" style="height: 300px;">
                <img class="img-fluid" src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/img/reference/offer-2.jpg" alt="">
                <div class="offer-text">
                    <h3 class="text-white mb-3">Precios Especiales</h3>
                    <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/alquiler" class="btn btn-primary">Alquilar</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Offer End -->
