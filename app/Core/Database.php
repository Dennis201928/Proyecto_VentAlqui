<?php
namespace App\Core;

/**
 * Clase para manejo de conexión a la base de datos
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
            $this->conn = new \PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch(\PDOException $exception) {
            error_log("Error de conexión a la base de datos: " . $exception->getMessage());
            throw new \Exception("Error de conexión a la base de datos");
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

