<?php $current_page = 'quienes-somos'; ?>
<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/">Inicio</a>
                <span class="breadcrumb-item active">Quiénes Somos</span>
            </nav>
        </div>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- About Start -->
<div class="container-fluid py-5">
    <div class="row px-xl-5">
        <div class="col-lg-12">
            <div class="bg-light p-30 mb-5">
                <h2 class="mb-4">Quiénes Somos</h2>

                <!-- Historia / Misión / Visión -->
                <div class="row mb-4">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h4 class="mb-3">Nuestra Historia</h4>
                        <p class="mb-3">
                            AlquiVenta nace con el objetivo de facilitar el acceso a maquinaria pesada
                            y materiales pétreos de alta calidad para la construcción. A lo largo de los años
                            hemos consolidado relaciones con proveedores y clientes, optimizando logística y
                            mantenimiento para asegurar disponibilidad real y entregas puntuales.
                        </p>
                        <p class="mb-0">
                            Hoy acompañamos a constructoras, contratistas y municipios, aportando equipos confiables,
                            stock verificado y soporte técnico especializado durante cada proyecto.
                        </p>
                    </div>

                    <div class="col-lg-6">
                        <div class="border rounded p-3 mb-3">
                            <h4 class="mb-2">Nuestra Misión</h4>
                            <p class="mb-0">
                                Proveer soluciones integrales de <strong>alquiler de maquinaria</strong> y
                                <strong>venta de materiales</strong> con estándares de calidad, seguridad y
                                sostenibilidad, maximizando el éxito operativo de nuestros clientes.
                            </p>
                        </div>
                        <div class="border rounded p-3">
                            <h4 class="mb-2">Nuestra Visión</h4>
                            <p class="mb-0">
                                Ser referentes a nivel nacional por la <strong>excelencia en servicio</strong>, la
                                <strong>confiabilidad del inventario</strong> y la <strong>innovación</strong> en
                                procesos de abastecimiento y gestión de flota.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Proyectos (Galería con imagen + descripción) -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3">Proyectos Realizados</h4>
                    </div>

                    <!-- Proyecto 1 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/img/galeria/proyecto-1.jpg"
                                 class="card-img-top" alt="Movimiento de tierras - Vía Norte">
                            <div class="card-body">
                                <h5 class="card-title">Movimiento de Tierras – Vía Norte</h5>
                                <p class="card-text mb-0">
                                    Excavación, perfilado y nivelación con excavadoras y volquetas coordinadas.
                                    Control de calidad del material removido y disposición autorizada.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Proyecto 2 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/img/galeria/proyecto-2.jpg"
                                 class="card-img-top" alt="Suministro de agregados para hormigón">
                            <div class="card-body">
                                <h5 class="card-title">Suministro de Agregados</h5>
                                <p class="card-text mb-0">
                                    Entrega programada de arena y grava para planta de hormigón con certificación
                                    de granulometría y trazabilidad de lotes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Proyecto 3 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/img/galeria/proyecto-3.jpg"
                                 class="card-img-top" alt="Compactación de subrasante">
                            <div class="card-body">
                                <h5 class="card-title">Compactación de Subrasante</h5>
                                <p class="card-text mb-0">
                                    Compactadores y rodillos para alcanzar densidades óptimas en plataforma
                                    industrial, con informes de laboratorio.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Proyecto 4 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/img/galeria/proyecto-4.jpg"
                                 class="card-img-top" alt="Estabilización de taludes">
                            <div class="card-body">
                                <h5 class="card-title">Estabilización de Taludes</h5>
                                <p class="card-text mb-0">
                                    Conformación y protección de taludes con mezcla seleccionada y control de pendientes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Proyecto 5 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/img/galeria/proyecto-5.jpg"
                                 class="card-img-top" alt="Carguío y transporte en cantera">
                            <div class="card-body">
                                <h5 class="card-title">Carguío y Transporte</h5>
                                <p class="card-text mb-0">
                                    Operación continua en cantera, optimizando ciclos de cargadores frontales y volquetas.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Proyecto 6 -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/img/galeria/proyecto-6.jpg"
                                 class="card-img-top" alt="Base granular para pavimento">
                            <div class="card-body">
                                <h5 class="card-title">Base Granular para Pavimento</h5>
                                <p class="card-text mb-0">
                                    Producción y extendido de material base cumpliendo especificaciones municipales.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <h4 class="mb-3">¿Listo para tu próximo proyecto?</h4>
                        <p class="mb-4">Escríbenos para recibir una cotización y asesoría técnica.</p>
                        <a href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/contacto" class="btn btn-primary btn-lg">
                            <i class="fas fa-envelope mr-2"></i>Contáctanos
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- About End -->


