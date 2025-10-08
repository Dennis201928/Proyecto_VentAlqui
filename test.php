<?php
// Archivo de prueba para diagnosticar problemas
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico del Sistema</h1>";

// Verificar PHP
echo "<h2>1. Verificación de PHP</h2>";
echo "Versión de PHP: " . phpversion() . "<br>";
echo "Extensiones cargadas: " . implode(', ', get_loaded_extensions()) . "<br>";

// Verificar PDO
echo "<h2>2. Verificación de PDO</h2>";
if (extension_loaded('pdo')) {
    echo "✅ PDO está cargado<br>";
    if (extension_loaded('pdo_pgsql')) {
        echo "✅ PDO PostgreSQL está cargado<br>";
    } else {
        echo "❌ PDO PostgreSQL NO está cargado<br>";
    }
} else {
    echo "❌ PDO NO está cargado<br>";
}

// Verificar archivos
echo "<h2>3. Verificación de archivos</h2>";
$files = [
    'config/database.php',
    'includes/auth.php',
    'includes/product.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file NO existe<br>";
    }
}

// Verificar conexión a base de datos
echo "<h2>4. Verificación de base de datos</h2>";
try {
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✅ Conexión a base de datos exitosa<br>";
        
        // Verificar tablas
        $tables = ['usuarios', 'productos', 'categorias', 'pedidos', 'pedido_detalles'];
        foreach ($tables as $table) {
            $stmt = $conn->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = '$table')");
            $exists = $stmt->fetchColumn();
            if ($exists) {
                echo "✅ Tabla $table existe<br>";
            } else {
                echo "❌ Tabla $table NO existe<br>";
            }
        }
    } else {
        echo "❌ No se pudo conectar a la base de datos<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Información del servidor</h2>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
?>
