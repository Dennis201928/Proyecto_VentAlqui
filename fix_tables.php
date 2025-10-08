<?php
/**
 * Script para crear las tablas faltantes
 */

require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        die("❌ No se pudo conectar a la base de datos");
    }
    
    echo "<h1>🔧 Creando tablas faltantes</h1>";
    
    // Crear tabla pedidos
    $sql_pedidos = "
    CREATE TABLE IF NOT EXISTS pedidos (
        id SERIAL PRIMARY KEY,
        usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
        total DECIMAL(10,2) NOT NULL,
        estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'procesando', 'enviado', 'entregado', 'cancelado')),
        metodo_pago VARCHAR(50) DEFAULT 'efectivo',
        direccion_entrega TEXT,
        notas TEXT,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $conn->exec($sql_pedidos);
    echo "✅ Tabla 'pedidos' creada exitosamente<br>";
    
    // Crear tabla pedido_detalles
    $sql_pedido_detalles = "
    CREATE TABLE IF NOT EXISTS pedido_detalles (
        id SERIAL PRIMARY KEY,
        pedido_id INTEGER REFERENCES pedidos(id) ON DELETE CASCADE,
        producto_id INTEGER REFERENCES productos(id) ON DELETE CASCADE,
        cantidad INTEGER NOT NULL DEFAULT 1,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        tipo VARCHAR(20) DEFAULT 'venta' CHECK (tipo IN ('venta', 'alquiler')),
        fecha_inicio DATE, -- Para alquileres
        fecha_fin DATE, -- Para alquileres
        dias_alquiler INTEGER, -- Para alquileres
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $conn->exec($sql_pedido_detalles);
    echo "✅ Tabla 'pedido_detalles' creada exitosamente<br>";
    
    // Crear índices para mejorar el rendimiento
    $indices = [
        "CREATE INDEX IF NOT EXISTS idx_pedidos_usuario ON pedidos(usuario_id);",
        "CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado);",
        "CREATE INDEX IF NOT EXISTS idx_pedidos_fecha ON pedidos(fecha_creacion);",
        "CREATE INDEX IF NOT EXISTS idx_pedido_detalles_pedido ON pedido_detalles(pedido_id);",
        "CREATE INDEX IF NOT EXISTS idx_pedido_detalles_producto ON pedido_detalles(producto_id);"
    ];
    
    foreach ($indices as $indice) {
        $conn->exec($indice);
    }
    echo "✅ Índices creados exitosamente<br>";
    
    // Verificar que las tablas existen
    $tables = ['pedidos', 'pedido_detalles'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = '$table')");
        $exists = $stmt->fetchColumn();
        if ($exists) {
            echo "✅ Verificación: Tabla '$table' existe<br>";
        } else {
            echo "❌ Error: Tabla '$table' NO existe<br>";
        }
    }
    
    echo "<br><h2>🎉 ¡Instalación completada!</h2>";
    echo "<p>El sistema está listo para usar. <a href='index.php'>Haz clic aquí</a> para acceder al sistema.</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
