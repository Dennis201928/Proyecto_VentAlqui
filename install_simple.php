<?php
/**
 * Instalador simplificado del sistema
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si ya está instalado
if (file_exists('config/installed.txt')) {
    echo "<h1>✅ Sistema ya instalado</h1>";
    echo "<p>El sistema ya está instalado. <a href='index.php'>Haz clic aquí</a> para acceder.</p>";
    echo "<p>Si quieres reinstalar, elimina el archivo <code>config/installed.txt</code></p>";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Usar la configuración existente
        require_once 'config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        
        if (!$conn) {
            throw new Exception("No se pudo conectar a la base de datos");
        }
        
        // Leer el esquema SQL
        $schema = file_get_contents('database/schema.sql');
        
        // Dividir en comandos individuales
        $commands = explode(';', $schema);
        
        foreach ($commands as $command) {
            $command = trim($command);
            if (!empty($command) && !preg_match('/^--/', $command)) {
                try {
                    $conn->exec($command);
                } catch (PDOException $e) {
                    // Ignorar errores de tablas que ya existen
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "Advertencia: " . $e->getMessage() . "<br>";
                    }
                }
            }
        }
        
        // Crear las tablas faltantes
        $fix_script = file_get_contents('fix_tables.php');
        $fix_script = str_replace('<?php', '', $fix_script);
        $fix_script = str_replace('?>', '', $fix_script);
        eval($fix_script);
        
        // Marcar como instalado
        file_put_contents('config/installed.txt', date('Y-m-d H:i:s'));
        
        $success = "¡Instalación completada exitosamente!";
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - AlquiVenta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .btn-block {
            width: 100%;
            text-align: center;
        }
        .info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #004085;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Instalador de AlquiVenta</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>✅ Éxito:</strong> <?php echo htmlspecialchars($success); ?>
            </div>
            <div class="text-center">
                <a href="index.php" class="btn btn-block">🚀 Acceder al Sistema</a>
            </div>
        <?php else: ?>
            <div class="info">
                <strong>ℹ️ Información:</strong> Este instalador configurará la base de datos y creará todas las tablas necesarias para el sistema.
            </div>
            
            <div class="step">
                <h3>📋 Configuración actual:</h3>
                <ul>
                    <li><strong>Base de datos:</strong> PostgreSQL</li>
                    <li><strong>Host:</strong> localhost</li>
                    <li><strong>Puerto:</strong> 5432</li>
                    <li><strong>Base de datos:</strong> venta_alquiler_db</li>
                    <li><strong>Usuario:</strong> postgres</li>
                </ul>
            </div>
            
            <form method="POST">
                <div class="text-center">
                    <button type="submit" class="btn btn-block">🚀 Instalar Sistema</button>
                </div>
            </form>
            
            <div class="text-center" style="margin-top: 20px;">
                <a href="test.php" class="btn" style="background: #6c757d;">🔍 Ver Diagnóstico</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
