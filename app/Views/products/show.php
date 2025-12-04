<?php $current_page = 'producto'; ?>
<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/">Inicio</a>
                <?php if ($product['categoria_tipo'] == 'maquinaria'): ?>
                    <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/alquiler">Alquiler</a>
                <?php else: ?>
                    <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/venta">Venta</a>
                <?php endif; ?>
                <span class="breadcrumb-item active"><?php echo htmlspecialchars($product['nombre']); ?></span>
            </nav>
        </div>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- Shop Detail Start -->
<div class="container-fluid py-5">
    <div class="row px-xl-5">
        <div class="col-lg-5 pb-5">
            <div id="product-carousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner border">
                    <div class="carousel-item active">
                        <img class="w-100 h-100" src="<?php echo \App\Helpers\ImageHelper::getImageUrl($product['imagen_principal'] ?? '', $baseUrl ?? null); ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                    </div>
                    <?php if (!empty($product['imagenes_adicionales']) && is_array($product['imagenes_adicionales'])): ?>
                        <?php foreach ($product['imagenes_adicionales'] as $img): ?>
                            <div class="carousel-item">
                                <img class="w-100 h-100" src="<?php echo \App\Helpers\ImageHelper::getImageUrl($img, $baseUrl ?? null); ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a class="carousel-control-prev" href="#product-carousel" data-slide="prev">
                    <i class="fa fa-2x fa-angle-left text-dark"></i>
                </a>
                <a class="carousel-control-next" href="#product-carousel" data-slide="next">
                    <i class="fa fa-2x fa-angle-right text-dark"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-7 pb-5">
            <h3 class="font-weight-semi-bold"><?php echo htmlspecialchars($product['nombre']); ?></h3>
            <div class="d-flex mb-3">
                <div class="text-primary mr-2">
                    <small class="fas fa-star"></small>
                    <small class="fas fa-star"></small>
                    <small class="fas fa-star"></small>
                    <small class="fas fa-star"></small>
                    <small class="fas fa-star"></small>
                </div>
                <small class="pt-1">(50 Reseñas)</small>
            </div>
            
            <?php if ($product['categoria_tipo'] == 'maquinaria'): ?>
                <?php if (!empty($product['precio_alquiler_dia']) && $product['precio_alquiler_dia'] > 0): ?>
                    <h3 class="font-weight-semi-bold mb-4">$<?php echo number_format($product['precio_alquiler_dia'], 2); ?>/día</h3>
                <?php else: ?>
                    <h3 class="font-weight-semi-bold mb-4 text-muted">Consultar precio</h3>
                <?php endif; ?>
            <?php else: ?>
                <?php if (!empty($product['precio_venta']) && $product['precio_venta'] > 0): ?>
                    <h3 class="font-weight-semi-bold mb-4">$<?php echo number_format($product['precio_venta'], 2); ?></h3>
                <?php else: ?>
                    <h3 class="font-weight-semi-bold mb-4 text-muted">Consultar precio</h3>
                <?php endif; ?>
            <?php endif; ?>
            
            <p class="mb-4"><?php echo nl2br(htmlspecialchars($product['descripcion'] ?? 'Sin descripción disponible.')); ?></p>
            
            <div class="d-flex align-items-center mb-4 pt-2">
                <div class="input-group quantity mr-3" style="width: 130px;">
                    <div class="input-group-btn">
                        <button class="btn btn-primary btn-minus">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                    <input type="text" class="form-control bg-secondary text-center" value="1" id="quantity">
                    <div class="input-group-btn">
                        <button class="btn btn-primary btn-plus">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <?php if ($product['categoria_tipo'] == 'maquinaria'): ?>
                    <?php if ($current_user): ?>
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/alquiler/<?php echo $product['id']; ?>" class="btn btn-primary px-3">
                            <i class="fa fa-calendar mr-1"></i> Agendar Alquiler
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/login" class="btn btn-primary px-3">
                            <i class="fa fa-sign-in-alt mr-1"></i> Inicia Sesión para Alquilar
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($current_user): ?>
                        <a href="#" onclick="irAVenta(<?php echo $product['id']; ?>); return false;" class="btn btn-primary px-3">
                            <i class="fa fa-calendar mr-1"></i> Agendar Venta
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/login" class="btn btn-primary px-3">
                            <i class="fa fa-sign-in-alt mr-1"></i> Inicia Sesión para Comprar
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="d-flex pt-2">
                <strong class="text-dark mr-2">Stock disponible:</strong>
                <span class="badge badge-<?php echo $product['stock_disponible'] > 0 ? 'success' : 'danger'; ?>">
                    <?php echo $product['stock_disponible']; ?> unidades
                </span>
            </div>
            
            <?php if (!empty($product['especificaciones'])): ?>
                <div class="d-flex pt-2">
                    <strong class="text-dark mr-2">Especificaciones:</strong>
                </div>
                <div class="pt-2">
                    <?php 
                    $specs = is_string($product['especificaciones']) ? json_decode($product['especificaciones'], true) : $product['especificaciones'];
                    if (is_array($specs)): 
                    ?>
                        <ul class="list-unstyled">
                            <?php foreach ($specs as $key => $value): ?>
                                <li><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted"><?php echo htmlspecialchars($product['especificaciones']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($related_products) && is_array($related_products)): ?>
        <div class="row px-xl-5">
            <div class="col">
                <div class="bg-light p-30">
                    <h4 class="mb-4">Productos Relacionados</h4>
                    <div class="owl-carousel related-carousel">
                        <?php foreach ($related_products as $related): ?>
                            <div class="product-item bg-light">
                                <div class="product-img position-relative overflow-hidden">
                                    <img class="img-fluid w-100" src="<?php echo \App\Helpers\ImageHelper::getImageUrl($related['imagen_principal'] ?? '', $baseUrl ?? null); ?>" alt="<?php echo htmlspecialchars($related['nombre']); ?>">
                                    <div class="product-action">
                                        <a class="btn btn-outline-dark btn-square" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/producto/<?php echo $related['id']; ?>">
                                            <i class="fa fa-shopping-cart"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="text-center py-4">
                                    <a class="h6 text-decoration-none text-truncate" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/producto/<?php echo $related['id']; ?>"><?php echo htmlspecialchars($related['nombre']); ?></a>
                                    <div class="d-flex align-items-center justify-content-center mt-2">
                                        <?php if ($related['categoria_tipo'] == 'maquinaria' && !empty($related['precio_alquiler_dia'])): ?>
                                            <h6>$<?php echo number_format($related['precio_alquiler_dia'], 2); ?>/día</h6>
                                        <?php elseif (!empty($related['precio_venta'])): ?>
                                            <h6>$<?php echo number_format($related['precio_venta'], 2); ?></h6>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<!-- Shop Detail End -->

<script>
function addToCart(productId, quantity) {
    fetch('<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            producto_id: productId,
            cantidad: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Producto agregado al carrito');
            // Actualizar contador del carrito si existe
            if (document.getElementById('cart-count-header')) {
                document.getElementById('cart-count-header').textContent = data.cart_count || 0;
            }
        } else {
            alert('Error: ' + (data.message || 'No se pudo agregar el producto'));
        }
    })
    .catch(error => {
        alert('Error al agregar el producto al carrito');
    });
}

function irAVenta(productId) {
    const quantity = document.getElementById('quantity').value || 1;
    const baseUrl = '<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>';
    window.location.href = baseUrl + '/venta/' + productId + '?cantidad=' + encodeURIComponent(quantity);
}
</script>

