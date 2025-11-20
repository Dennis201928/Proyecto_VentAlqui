<?php 
// Asegurar que baseUrl esté definido
if (!isset($baseUrl)) {
    $baseUrl = \App\Core\Config::SITE_URL;
}

$current_route = $_SERVER['REQUEST_URI'] ?? '';
$active_page = '';

// Determinar qué página está activa
if (strpos($current_route, '/admin/productos') !== false) {
    $active_page = 'productos';
} elseif (strpos($current_route, '/admin/categorias') !== false) {
    $active_page = 'categorias';
} elseif (strpos($current_route, '/admin/calendario-alquileres') !== false) {
    $active_page = 'calendario-alquileres';
} elseif (strpos($current_route, '/admin/calendario-ventas') !== false) {
    $active_page = 'calendario-ventas';
} else {
    $active_page = 'dashboard';
}
?>
<style>
    .admin-sidebar {
        min-height: calc(100vh - 60px);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: sticky;
        top: 0;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    .admin-sidebar .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 20px;
    }
    .admin-sidebar .sidebar-header h4 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 600;
    }
    .admin-sidebar .sidebar-header .logo-icon {
        font-size: 1.5rem;
        margin-right: 10px;
    }
    .admin-sidebar .nav {
        padding: 0 15px;
    }
    .admin-sidebar .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 12px 20px;
        border-radius: 8px;
        margin: 5px 0;
        transition: all 0.3s;
        text-decoration: none;
        display: flex;
        align-items: center;
        font-weight: 500;
    }
    .admin-sidebar .nav-link i {
        width: 20px;
        text-align: center;
        margin-right: 12px;
    }
    .admin-sidebar .nav-link:hover {
        color: white;
        background: rgba(255,255,255,0.15);
        transform: translateX(5px);
    }
    .admin-sidebar .nav-link.active {
        color: white;
        background: rgba(255,255,255,0.2);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        font-weight: 600;
    }
    .admin-sidebar .divider {
        height: 1px;
        background: rgba(255,255,255,0.2);
        margin: 20px 15px;
    }
    .admin-sidebar .user-info {
        padding: 15px 20px;
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: auto;
    }
    .admin-sidebar .user-info .user-name {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .admin-sidebar .user-info .user-role {
        font-size: 0.75rem;
        opacity: 0.8;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>

<div class="admin-sidebar text-white d-flex flex-column">
    <div class="sidebar-header">
        <h4>
            <i class="fas fa-tools logo-icon"></i>Admin Panel
        </h4>
    </div>
    
    <nav class="nav flex-column flex-grow-1">
        <a class="nav-link <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/admin">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a class="nav-link <?php echo $active_page === 'productos' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/admin/productos">
            <i class="fas fa-box"></i>
            <span>Productos</span>
        </a>
        <a class="nav-link <?php echo $active_page === 'categorias' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/admin/categorias">
            <i class="fas fa-tags"></i>
            <span>Categorías</span>
        </a>
        <a class="nav-link <?php echo $active_page === 'calendario-alquileres' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/admin/calendario-alquileres">
            <i class="fas fa-calendar-alt"></i>
            <span>Calendario Alquileres</span>
        </a>
        <a class="nav-link <?php echo $active_page === 'calendario-ventas' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/admin/calendario-ventas">
            <i class="fas fa-calendar-check"></i>
            <span>Calendario Ventas</span>
        </a>
        
        <div class="divider"></div>
        
        <a class="nav-link" href="<?php echo $baseUrl; ?>/">
            <i class="fas fa-home"></i>
            <span>Volver al Sitio</span>
        </a>
        <a class="nav-link" href="<?php echo $baseUrl; ?>/logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </nav>
    
    <?php if (isset($current_user) && $current_user): ?>
    <div class="user-info">
        <div class="user-name">
            <i class="fas fa-user-circle me-2"></i>
            <?php echo htmlspecialchars($current_user['nombre'] . ' ' . $current_user['apellido']); ?>
        </div>
        <div class="user-role">
            <i class="fas fa-shield-alt me-1"></i>Administrador
        </div>
    </div>
    <?php endif; ?>
</div>

