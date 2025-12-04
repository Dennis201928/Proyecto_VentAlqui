<?php $current_page = 'contacto'; ?>
<div class="container mt-4">
    <h2>Contáctanos</h2>
    <div class="row">
        <div class="col-md-6">
            <h4>Información de Contacto</h4>
            <p><i class="fa fa-map-marker-alt text-primary mr-3"></i>123 Street, New York, USA</p>
            <p><i class="fa fa-envelope text-primary mr-3"></i>info@alquivent.com</p>
            <p><i class="fa fa-phone-alt text-primary mr-3"></i>+593 345 67890</p>
            <!-- Estilos del botón "Cómo llegar" -->
<style>
  .btn-map {
    border: 0;
    border-radius: 9999px;
    padding: .6rem 1rem;
    font-weight: 600;
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
    background: linear-gradient(135deg, #007bff, #00b4ff);
    color: #fff !important;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
  }
  .btn-map:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(0,0,0,.12);
    opacity: .95;
  }
  .btn-map i { font-size: 1rem; }
</style>

<div class="row mt-4">
  <div class="col-12">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
      <h4 class="mb-0">Ubícanos en el mapa</h4>
      <a
        class="btn-map mt-2 mt-md-0"
        target="_blank"
        rel="noopener"
        href="https://www.google.com/maps/dir/?api=1&destination=-0.2107252,-78.4410895">
        <i class="fa fa-location-arrow"></i>
        Cómo llegar
      </a>
    </div>
  </div>
</div>

        </div>
        <div class="col-md-6">
            <h4>Envíanos un Mensaje</h4>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <form method="POST" action="<?php echo \App\Core\Config::SITE_URL; ?>/contacto">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="mensaje" class="form-label">Mensaje</label>
                    <textarea class="form-control" id="mensaje" name="mensaje" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
            </form>
        </div>
    </div>
</div>

