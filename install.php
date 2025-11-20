<?php
/**
 * Script de instalación del sistema
 */

// Verificar si ya está instalado
if (file_exists('config/installed.txt')) {
    die('El sistema ya está instalado. Elimine el archivo config/installed.txt para reinstalar.');
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($step) {
        case 1:
            // Configuración de base de datos
            $host = $_POST['host'];
            $port = $_POST['port'];
            $dbname = $_POST['dbname'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
                $conn = new PDO($dsn, $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Actualizar configuración en app/Core/Database.php
                $database_content = "<?php
namespace App\\Core;

/**
 * Clase para manejo de conexión a la base de datos
 */
class Database {
    private \$host = '$host';
    private \$db_name = '$dbname';
    private \$username = '$username';
    private \$password = '$password';
    private \$port = '$port';
    private \$conn;

    /**
     * Obtener conexión a la base de datos
     */
    public function getConnection() {
        \$this->conn = null;

        try {
            \$dsn = \"pgsql:host=\" . \$this->host . \";port=\" . \$this->port . \";dbname=\" . \$this->db_name;
            \$this->conn = new \\PDO(\$dsn, \$this->username, \$this->password);
            \$this->conn->setAttribute(\\PDO::ATTR_ERRMODE, \\PDO::ERRMODE_EXCEPTION);
            \$this->conn->setAttribute(\\PDO::ATTR_DEFAULT_FETCH_MODE, \\PDO::FETCH_ASSOC);
        } catch(\\PDOException \$exception) {
            error_log(\"Error de conexión a la base de datos: \" . \$exception->getMessage());
            throw new \\Exception(\"Error de conexión a la base de datos\");
        }

        return \$this->conn;
    }

    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        \$this->conn = null;
    }
}
";
                
                file_put_contents('app/Core/Database.php', $database_content);
                header('Location: install.php?step=2');
                exit();
            } catch (PDOException $e) {
                $error = 'Error de conexión: ' . $e->getMessage();
            }
            break;
            
        case 2:
            // Crear tablas
            // Cargar autoloader y clases con namespace
            require_once __DIR__ . '/vendor/autoload.php';
            $db = new \App\Core\Database();
            $conn = $db->getConnection();
            
            if (!$conn) {
                $error = 'No se pudo conectar a la base de datos';
                break;
            }
            
            $sql = file_get_contents('database/schema.sql');
            $statements = explode(';', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        $conn->exec($statement);
                    } catch (PDOException $e) {
                        // Ignorar errores de tablas que ya existen
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            $error .= $e->getMessage() . '<br>';
                        }
                    }
                }
            }
            
            if (empty($error)) {
                header('Location: install.php?step=3');
                exit();
            }
            break;
            
        case 3:
            // Crear directorios y finalizar
            $directories = ['uploads', 'uploads/products', 'uploads/users', 'logs'];
            foreach ($directories as $dir) {
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
            
            // Crear archivo de instalación completada
            file_put_contents('config/installed.txt', date('Y-m-d H:i:s'));
            
            $success = 'Instalación completada exitosamente. Puede acceder al sistema.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Instalación - <?php echo Config::SITE_NAME; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .install-container { max-width: 600px; margin: 50px auto; }
        .step { display: none; }
        .step.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-container">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h3><i class="fas fa-cog mr-2"></i>Instalación del Sistema</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <div class="text-center">
                            <a href="index.php" class="btn btn-primary btn-lg">Ir al Sistema</a>
                        </div>
                    <?php else: ?>
                        <!-- Paso 1: Configuración de Base de Datos -->
                        <div class="step <?php echo $step == 1 ? 'active' : ''; ?>">
                            <h4>Paso 1: Configuración de Base de Datos</h4>
                            <p>Configure la conexión a PostgreSQL:</p>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Host</label>
                                    <input type="text" class="form-control" name="host" value="localhost" required>
                                </div>
                                <div class="form-group">
                                    <label>Puerto</label>
                                    <input type="number" class="form-control" name="port" value="5432" required>
                                </div>
                                <div class="form-group">
                                    <label>Nombre de la Base de Datos</label>
                                    <input type="text" class="form-control" name="dbname" value="venta_alquiler_db" required>
                                </div>
                                <div class="form-group">
                                    <label>Usuario</label>
                                    <input type="text" class="form-control" name="username" value="postgres" required>
                                </div>
                                <div class="form-group">
                                    <label>Contraseña</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Continuar</button>
                            </form>
                        </div>
                        
                        <!-- Paso 2: Crear Tablas -->
                        <div class="step <?php echo $step == 2 ? 'active' : ''; ?>">
                            <h4>Paso 2: Crear Tablas</h4>
                            <p>Se crearán las tablas necesarias en la base de datos:</p>
                            <ul>
                                <li>usuarios</li>
                                <li>categorias</li>
                                <li>productos</li>
                                <li>alquileres</li>
                                <li>ventas</li>
                                <li>venta_detalles</li>
                                <li>carrito</li>
                                <li>favoritos</li>
                                <li>contactos</li>
                                <li>configuracion</li>
                            </ul>
                            <form method="POST">
                                <button type="submit" class="btn btn-primary">Crear Tablas</button>
                            </form>
                        </div>
                        
                        <!-- Paso 3: Finalizar -->
                        <div class="step <?php echo $step == 3 ? 'active' : ''; ?>">
                            <h4>Paso 3: Finalizar Instalación</h4>
                            <p>Se crearán los directorios necesarios y se finalizará la instalación:</p>
                            <ul>
                                <li>uploads/ - Para imágenes de productos</li>
                                <li>uploads/products/ - Imágenes de productos</li>
                                <li>uploads/users/ - Imágenes de usuarios</li>
                                <li>logs/ - Archivos de log</li>
                            </ul>
                            <form method="POST">
                                <button type="submit" class="btn btn-success">Finalizar Instalación</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
