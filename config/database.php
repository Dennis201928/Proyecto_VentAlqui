<?php
/**
 * Configuración de la base de datos PostgreSQL
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'venta_alquiler_db';
    private $username = 'postgres';
    private $password = '123456';
    private $port = '5432';
    private $conn;

    /**
     * Obtener conexión a la base de datos
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }
}

/**
 * Configuración general del sistema
 */
class Config {
    const SITE_NAME = 'AlquiVenta';
    const SITE_URL = 'http://localhost/Proyecto_VentAlqui';
    const ADMIN_EMAIL = 'admin@alquivent.com';
    const CURRENCY = 'USD';
    const TAX_RATE = 0.19;
    const MIN_RENTAL_DAYS = 1;
    const MAX_RENTAL_DAYS = 365;
    
    // Configuración de sesiones
    const SESSION_LIFETIME = 3600; // 1 hora
    
    // Configuración de archivos
    const UPLOAD_PATH = 'uploads/';
    const MAX_FILE_SIZE = 5242880; // 5MB
    
    // Configuración de paginación
    const PRODUCTS_PER_PAGE = 12;
    const ORDERS_PER_PAGE = 10;
}
?>
