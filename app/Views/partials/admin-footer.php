<?php 
// Asegurar que baseUrl esté definido
if (!isset($baseUrl)) {
    $baseUrl = \App\Core\Config::SITE_URL;
}
?>
<footer class="admin-footer mt-auto">
    <div class="container-fluid bg-dark text-secondary py-3">
        <div class="row px-3">
            <div class="col-md-6">
                <p class="mb-0 text-secondary small">
                    &copy; <?php echo date('Y'); ?> <strong class="text-primary">AlquiVenta</strong>. Todos los derechos reservados.
                </p>
            </div>
            <div class="col-md-6 text-right">
                <p class="mb-0 text-secondary small">
                    <i class="fas fa-user-shield me-1"></i>
                    Panel de Administración
                    <?php if (isset($current_user) && $current_user): ?>
                        | <span class="text-white"><?php echo htmlspecialchars($current_user['nombre'] . ' ' . $current_user['apellido']); ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
    .admin-footer {
        width: 100%;
        margin-top: auto;
    }
    .admin-footer .container-fluid {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>

