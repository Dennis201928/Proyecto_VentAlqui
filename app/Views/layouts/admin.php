<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo isset($title) ? $title : 'Admin Panel - AlquiVenta'; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap 4 CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">
    
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: hidden;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .admin-wrapper {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 60px);
        }
        .admin-main-content {
            flex: 1;
            background-color: #f8f9fa;
            padding: 30px;
            overflow-y: auto;
        }
        .admin-content-wrapper {
            width: 100%;
        }
        .admin-sidebar {
            width: 250px;
            flex-shrink: 0;
        }
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                position: fixed;
                z-index: 1000;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
            .admin-main-content {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Asegurar que baseUrl esté definido
    if (!isset($baseUrl)) {
        $baseUrl = \App\Core\Config::SITE_URL;
    }
    ?>
    
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <?php 
            if (file_exists(__DIR__ . '/../partials/admin-sidebar.php')) {
                include __DIR__ . '/../partials/admin-sidebar.php';
            }
            ?>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main-content">
            <?php echo $content ?? ''; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <?php 
    if (file_exists(__DIR__ . '/../partials/admin-footer.php')) {
        include __DIR__ . '/../partials/admin-footer.php';
    }
    ?>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/es.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

