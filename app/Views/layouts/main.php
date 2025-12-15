<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo isset($title) ? $title : 'AlquiVenta - Venta y Alquiler de Maquinaria'; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Venta y alquiler de maquinaria pesada y materiales pétreos" name="keywords">
    <meta content="Sistema de venta y alquiler de maquinaria pesada y materiales pétreos" name="description">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Libraries Stylesheet -->
    <link href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/lib/animate/animate.min.css" rel="stylesheet">
    <link href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    
    <!-- Customized Bootstrap Stylesheet -->
    <link href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Chatbot CSS -->
    <link href="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/css/chatbot.css" rel="stylesheet">
    
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
            width: 100%;
        }
        .main-content {
            flex: 1;
            width: 100%;
        }
        footer {
            width: 100%;
            margin: 0;
            padding: 0;
            flex-shrink: 0;
        }
        footer .container-fluid {
            width: 100%;
            margin: 0;
            padding-left: 0;
            padding-right: 0;
            max-width: 100%;
        }
        .product-item {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .product-description {
            color: #6c757d;
            font-size: 0.85rem;
            line-height: 1.4;
            margin: 8px 0;
            min-height: 2.4rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .stock-badge {
            background: linear-gradient(45deg, #17a2b8, #138496);
            color: white;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>
    <?php 
    // Incluir header
    if (file_exists(__DIR__ . '/../partials/header.php')) {
        include __DIR__ . '/../partials/header.php';
    }
    ?>
    
    <div class="main-content">
        <?php echo $content ?? ''; ?>
    </div>
    
    <?php 
    // Incluir footer
    if (file_exists(__DIR__ . '/../partials/footer.php')) {
        include __DIR__ . '/../partials/footer.php';
    }
    ?>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/lib/easing/easing.min.js"></script>
    <script src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/es.js"></script>
    
    <script> window.baseUrl = '<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>';</script>
    <script src="<?php echo $baseUrl ?? '/Proyecto_VentAlqui/public'; ?>/assets/js/chatbot.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

