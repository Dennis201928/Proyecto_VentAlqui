<?php $current_page = 'venta'; ?>
<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/">Inicio</a>
                <span class="breadcrumb-item active">Venta de Materiales</span>
            </nav>
        </div>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- Shop Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <!-- Sidebar Start -->
        <div class="col-lg-3 col-md-4">
            <!-- Category Start -->
            <div class="border-bottom mb-4 pb-4">
                <h5 class="font-weight-semi-bold mb-4">Filtrar por Categoría</h5>
                <form method="GET" action="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/venta">
                    <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
                        <input type="checkbox" class="custom-control-input" id="category-all" name="categoria_id" value="" <?php echo empty($_GET['categoria_id']) ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <label class="custom-control-label" for="category-all">Todas las Categorías</label>
                    </div>
                    <?php if (is_array($categories) && !empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
                                <input type="checkbox" class="custom-control-input" id="category-<?php echo $category['id']; ?>" name="categoria_id" value="<?php echo $category['id']; ?>" <?php echo (isset($_GET['categoria_id']) && $_GET['categoria_id'] == $category['id']) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <label class="custom-control-label" for="category-<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['nombre']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </form>
            </div>
            <!-- Category End -->
        </div>
        <!-- Sidebar End -->

        <!-- Shop Product Start -->
        <div class="col-lg-9 col-md-8">
            <div class="row pb-3">
                <div class="col-12 pb-1">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <button class="btn btn-sm btn-light"><i class="fa fa-th-large"></i></button>
                            <button class="btn btn-sm btn-light ml-2"><i class="fa fa-bars"></i></button>
                        </div>
                        <div class="ml-2">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">Ordenar por</button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="?sort=nombre">Nombre</a>
                                    <a class="dropdown-item" href="?sort=precio_asc">Precio: Menor a Mayor</a>
                                    <a class="dropdown-item" href="?sort=precio_desc">Precio: Mayor a Menor</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (is_array($products) && !empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6 col-sm-6 pb-1">
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
                                    <a class="h6 text-decoration-none text-truncate" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/producto/<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['nombre']); ?></a>
                                    <div class="d-flex align-items-center justify-content-center mt-2">
                                        <?php if (!empty($product['precio_venta']) && $product['precio_venta'] > 0): ?>
                                            <h6>$<?php echo number_format($product['precio_venta'], 2); ?></h6>
                                        <?php else: ?>
                                            <h6 class="text-muted">Consultar precio</h6>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                        <small class="fa fa-star text-primary mr-1"></small>
                                        <small class="fa fa-star text-primary mr-1"></small>
                                        <small class="fa fa-star text-primary mr-1"></small>
                                        <small class="fa fa-star text-primary mr-1"></small>
                                        <small class="fa fa-star text-primary mr-1"></small>
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge badge-info">
                                            <i class="fas fa-box"></i> Stock: <?php echo $product['stock_disponible']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay productos disponibles en este momento.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Shop Product End -->
    </div>
</div>
<!-- Shop End -->

